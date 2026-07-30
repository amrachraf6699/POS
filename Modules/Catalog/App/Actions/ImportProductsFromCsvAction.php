<?php

namespace Modules\Catalog\App\Actions;

use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\App\Domain\CatalogAuthorization;
use Modules\Catalog\App\Domain\Csv\Data\CatalogCsvImportError;
use Modules\Catalog\App\Domain\Csv\Data\CatalogCsvImportResult;
use Modules\Catalog\App\Models\Category;
use Modules\Catalog\App\Models\Product;
use Modules\Catalog\App\Models\TaxRate;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;
use Throwable;

final class ImportProductsFromCsvAction
{
    /** @var array<int, string> */
    public const HEADER = [
        'name', 'category_name', 'tax_rate_name', 'sku', 'barcode', 'description',
        'cost_price_minor', 'selling_price_minor', 'track_inventory', 'low_stock_threshold',
        'allow_negative_stock', 'status',
    ];

    private const MAX_ROWS = 500;

    private const BATCH_SIZE = 100;

    public function __construct(private readonly CatalogAuthorization $authorization) {}

    public function execute(User $user, Tenant $tenant, UploadedFile $file): CatalogCsvImportResult
    {
        if (! $this->authorization->allows($user, $tenant, 'products.import')) {
            throw new AuthorizationException;
        }

        $handle = fopen($file->getRealPath(), 'rb');
        if ($handle === false) {
            return new CatalogCsvImportResult(0, 0, [new CatalogCsvImportError(0, 'file', 'تعذر قراءة ملف CSV.')]);
        }

        try {
            $header = fgetcsv($handle);
            if ($header === false) {
                return new CatalogCsvImportResult(0, 0, [new CatalogCsvImportError(1, 'file', 'ملف CSV فارغ.')]);
            }
            if ($this->normaliseHeader($header) !== self::HEADER) {
                return new CatalogCsvImportResult(0, 0, [new CatalogCsvImportError(1, 'header', 'رؤوس الأعمدة لا تطابق تنسيق CSV المعتمد.')]);
            }

            $rows = [];
            $line = 1;
            while (($values = fgetcsv($handle)) !== false) {
                $line++;
                if (count($rows) >= self::MAX_ROWS) {
                    return new CatalogCsvImportResult(count($rows), 0, [new CatalogCsvImportError($line, 'file', 'الحد الأقصى للاستيراد هو 500 صف في الملف الواحد.')]);
                }

                $rows[] = ['line' => $line, 'values' => $values];
            }
        } finally {
            fclose($handle);
        }

        if ($rows === []) {
            return new CatalogCsvImportResult(0, 0, [new CatalogCsvImportError(1, 'file', 'ملف CSV لا يحتوي على أي منتجات.')]);
        }

        $errors = [];
        $candidates = [];
        $seenSkus = [];
        $seenBarcodes = [];
        foreach ($rows as $row) {
            $values = $this->normaliseValues($row['values']);
            if (count($values) !== count(self::HEADER)) {
                $this->addError($errors, $row['line'], 'row', 'عدد الأعمدة في الصف غير صحيح.');

                continue;
            }

            /** @var array<string, string> $candidate */
            $candidate = array_combine(self::HEADER, $values);

            $candidates[$row['line']] = $candidate;
            $this->markFileDuplicate($errors, $seenSkus, $candidate['sku'], $row['line'], 'sku', 'رمز SKU مكرر داخل الملف.');
            $this->markFileDuplicate($errors, $seenBarcodes, $candidate['barcode'], $row['line'], 'barcode', 'الباركود مكرر داخل الملف.');
        }

        $existingSkus = $this->existingIdentifiers(array_column($candidates, 'sku'), 'sku');
        $existingBarcodes = $this->existingIdentifiers(array_column($candidates, 'barcode'), 'barcode');
        $today = CarbonImmutable::today(config('app.timezone'));
        $validRows = [];
        foreach ($candidates as $line => $candidate) {
            $attributes = $this->validateRow($candidate, $line, $errors, $existingSkus, $existingBarcodes, $today);
            if ($attributes !== null && ! isset($errors[$line])) {
                $validRows[] = ['line' => $line, 'attributes' => $attributes];
            }
        }

        $imported = 0;
        foreach (array_chunk($validRows, self::BATCH_SIZE) as $batch) {
            try {
                DB::transaction(function () use ($batch): void {
                    foreach ($batch as $row) {
                        Product::query()->create($row['attributes']);
                    }
                });
                $imported += count($batch);
            } catch (Throwable) {
                foreach ($batch as $row) {
                    $this->addError($errors, $row['line'], 'row', 'تعذر حفظ هذا الصف؛ لم يتم حفظ أي صف من مجموعته.');
                }
            }
        }

        ksort($errors);
        $flatErrors = [];
        foreach ($errors as $rowErrors) {
            foreach ($rowErrors as $error) {
                $flatErrors[] = $error;
            }
        }

        return new CatalogCsvImportResult(count($rows), $imported, $flatErrors);
    }

    /** @param array<int, string|null> $header
     *  @return array<int, string> */
    private function normaliseHeader(array $header): array
    {
        return array_map(static fn (?string $value): string => ltrim((string) $value, "\xEF\xBB\xBF"), $header);
    }

    /** @param array<int, string|null> $values
     *  @return array<int, string> */
    private function normaliseValues(array $values): array
    {
        return array_map(static fn (?string $value): string => trim((string) $value), $values);
    }

    /** @param array<int, array<int, CatalogCsvImportError>> $errors
     *  @param array<string, array<int, int>> $seen */
    private function markFileDuplicate(array &$errors, array &$seen, string $value, int $line, string $field, string $message): void
    {
        if ($value === '') {
            return;
        }

        foreach ($seen[$value] ?? [] as $duplicateLine) {
            $this->addError($errors, $duplicateLine, $field, $message);
            $this->addError($errors, $line, $field, $message);
        }
        $seen[$value][] = $line;
    }

    /** @param array<int, string> $values
     *  @return array<string, true> */
    private function existingIdentifiers(array $values, string $column): array
    {
        $values = array_values(array_unique(array_filter($values, static fn (string $value): bool => $value !== '')));
        if ($values === []) {
            return [];
        }

        $identifiers = [];
        foreach (Product::query()->withoutGlobalScope(SoftDeletingScope::class)->whereIn($column, $values)->pluck($column) as $value) {
            $identifiers[(string) $value] = true;
        }

        return $identifiers;
    }

    /** @param array<string, string> $candidate
     *  @param array<int, array<int, CatalogCsvImportError>> $errors
     *  @param array<string, true> $existingSkus
     *  @param array<string, true> $existingBarcodes
     *  @return array<string, int|string|bool|null>|null */
    private function validateRow(array $candidate, int $line, array &$errors, array $existingSkus, array $existingBarcodes, CarbonImmutable $today): ?array
    {
        if ($candidate['name'] === '') {
            $this->addError($errors, $line, 'name', 'اسم المنتج مطلوب.');
        } elseif (mb_strlen($candidate['name']) > 255) {
            $this->addError($errors, $line, 'name', 'اسم المنتج أطول من الحد المسموح.');
        }
        if (mb_strlen($candidate['sku']) > 100) {
            $this->addError($errors, $line, 'sku', 'رمز SKU أطول من الحد المسموح.');
        }
        if (mb_strlen($candidate['barcode']) > 100) {
            $this->addError($errors, $line, 'barcode', 'الباركود أطول من الحد المسموح.');
        }
        if (mb_strlen($candidate['description']) > 2000) {
            $this->addError($errors, $line, 'description', 'الوصف أطول من الحد المسموح.');
        }
        if ($candidate['sku'] !== '' && isset($existingSkus[$candidate['sku']])) {
            $this->addError($errors, $line, 'sku', 'رمز SKU مستخدم بالفعل في هذا النشاط.');
        }
        if ($candidate['barcode'] !== '' && isset($existingBarcodes[$candidate['barcode']])) {
            $this->addError($errors, $line, 'barcode', 'الباركود مستخدم بالفعل في هذا النشاط.');
        }

        /** @var Category|null $category */
        $category = Category::query()->where('name', $candidate['category_name'])->first();
        if (! $category instanceof Category) {
            $this->addError($errors, $line, 'category_name', 'الفئة غير متاحة في هذا النشاط.');
        }
        /** @var TaxRate|null $taxRate */
        $taxRate = TaxRate::query()
            ->where('name', $candidate['tax_rate_name'])
            ->where('status', TaxRate::STATUS_ACTIVE)
            ->whereDate('effective_from', '<=', $today)
            ->where(fn ($query) => $query->whereNull('effective_to')->orWhereDate('effective_to', '>', $today))
            ->orderByDesc('effective_from')
            ->first();
        if (! $taxRate instanceof TaxRate) {
            $this->addError($errors, $line, 'tax_rate_name', 'نسبة الضريبة غير نشطة أو غير سارية في هذا النشاط.');
        }

        $cost = $this->minorInteger($candidate['cost_price_minor'], $line, 'cost_price_minor', $errors);
        $selling = $this->minorInteger($candidate['selling_price_minor'], $line, 'selling_price_minor', $errors);
        $threshold = $this->minorInteger($candidate['low_stock_threshold'], $line, 'low_stock_threshold', $errors);
        $trackInventory = $this->binaryFlag($candidate['track_inventory'], $line, 'track_inventory', $errors);
        $allowNegativeStock = $this->binaryFlag($candidate['allow_negative_stock'], $line, 'allow_negative_stock', $errors);
        if (! in_array($candidate['status'], [Product::STATUS_ACTIVE, Product::STATUS_INACTIVE], true)) {
            $this->addError($errors, $line, 'status', 'حالة المنتج يجب أن تكون active أو inactive.');
        }
        if (isset($errors[$line]) || ! $category instanceof Category || ! $taxRate instanceof TaxRate || $cost === null || $selling === null || $threshold === null || $trackInventory === null || $allowNegativeStock === null) {
            return null;
        }

        return [
            'category_id' => $category->getKey(),
            'tax_rate_id' => $taxRate->getKey(),
            'name' => $candidate['name'],
            'sku' => $candidate['sku'] === '' ? null : $candidate['sku'],
            'barcode' => $candidate['barcode'] === '' ? null : $candidate['barcode'],
            'description' => $candidate['description'] === '' ? null : $candidate['description'],
            'cost_price_minor' => $cost,
            'selling_price_minor' => $selling,
            'track_inventory' => $trackInventory,
            'low_stock_threshold' => $threshold,
            'allow_negative_stock' => $allowNegativeStock,
            'status' => $candidate['status'],
        ];
    }

    /** @param array<int, array<int, CatalogCsvImportError>> $errors */
    private function minorInteger(string $value, int $line, string $field, array &$errors): ?int
    {
        if (! preg_match('/^\d+$/D', $value)) {
            $this->addError($errors, $line, $field, 'القيمة يجب أن تكون عدداً صحيحاً غير سالب بوحدة القرش.');

            return null;
        }
        $normalised = ltrim($value, '0');
        $normalised = $normalised === '' ? '0' : $normalised;
        if (strlen($normalised) > strlen((string) PHP_INT_MAX) || (strlen($normalised) === strlen((string) PHP_INT_MAX) && $normalised > (string) PHP_INT_MAX)) {
            $this->addError($errors, $line, $field, 'القيمة أكبر من الحد المسموح.');

            return null;
        }

        return (int) $normalised;
    }

    /** @param array<int, array<int, CatalogCsvImportError>> $errors */
    private function binaryFlag(string $value, int $line, string $field, array &$errors): ?bool
    {
        if (! in_array($value, ['0', '1'], true)) {
            $this->addError($errors, $line, $field, 'القيمة يجب أن تكون 0 أو 1.');

            return null;
        }

        return $value === '1';
    }

    /** @param array<int, array<int, CatalogCsvImportError>> $errors */
    private function addError(array &$errors, int $line, string $field, string $message): void
    {
        $errors[$line] ??= [];
        $errors[$line][] = new CatalogCsvImportError($line, $field, $message);
    }
}
