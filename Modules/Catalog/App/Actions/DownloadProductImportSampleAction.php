<?php

namespace Modules\Catalog\App\Actions;

use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Modules\Catalog\App\Domain\CatalogAuthorization;
use Modules\Catalog\App\Models\Category;
use Modules\Catalog\App\Models\TaxRate;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class DownloadProductImportSampleAction
{
    public function __construct(private readonly CatalogAuthorization $authorization) {}

    public function execute(User $user, Tenant $tenant): StreamedResponse
    {
        if (! $this->authorization->allows($user, $tenant, 'products.import')) {
            throw new AuthorizationException;
        }

        $category = Category::query()->orderBy('name')->first();
        if (! $category instanceof Category) {
            throw (new ModelNotFoundException)->setModel(Category::class);
        }

        $taxRate = TaxRate::query()
            ->where('status', TaxRate::STATUS_ACTIVE)
            ->whereDate('effective_from', '<=', CarbonImmutable::today(config('app.timezone')))
            ->where(fn ($query) => $query->whereNull('effective_to')->orWhereDate('effective_to', '>', CarbonImmutable::today(config('app.timezone'))))
            ->orderBy('name')
            ->first();
        if (! $taxRate instanceof TaxRate) {
            throw (new ModelNotFoundException)->setModel(TaxRate::class);
        }

        return response()->streamDownload(function () use ($category, $taxRate): void {
            $output = fopen('php://output', 'wb');
            if ($output === false) {
                return;
            }

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ImportProductsFromCsvAction::HEADER);
            fputcsv($output, [
                'منتج تجريبي',
                $this->safeText($category->name),
                $this->safeText($taxRate->name),
                '', '', 'وصف اختياري', '0', '100', '1', '0', '0', 'active',
            ]);
            fclose($output);
        }, 'catalog-product-import-sample.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function safeText(string $value): string
    {
        return preg_match('/^[=+\-@\t\r]/', $value) === 1 ? "\t{$value}" : $value;
    }
}
