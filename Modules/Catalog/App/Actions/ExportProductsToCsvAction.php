<?php

namespace Modules\Catalog\App\Actions;

use Illuminate\Auth\Access\AuthorizationException;
use Modules\Catalog\App\Domain\CatalogAuthorization;
use Modules\Catalog\App\Models\Product;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ExportProductsToCsvAction
{
    public function __construct(private readonly CatalogAuthorization $authorization) {}

    public function execute(User $user, Tenant $tenant): StreamedResponse
    {
        if (! $this->authorization->allows($user, $tenant, 'products.export')) {
            throw new AuthorizationException;
        }

        return response()->streamDownload(function (): void {
            $output = fopen('php://output', 'wb');
            if ($output === false) {
                return;
            }
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ImportProductsFromCsvAction::HEADER);

            Product::query()->with(['category:id,name', 'taxRate:id,name'])->orderBy('id')->lazyById(100)->each(function (Product $product) use ($output): void {
                fputcsv($output, [
                    $this->safeText($product->name),
                    $this->safeText($product->category->name),
                    $this->safeText($product->taxRate->name),
                    $this->safeText($product->sku ?? ''),
                    $this->safeText($product->barcode ?? ''),
                    $this->safeText($product->description ?? ''),
                    $product->cost_price_minor,
                    $product->selling_price_minor,
                    $product->track_inventory ? '1' : '0',
                    $product->low_stock_threshold,
                    $product->allow_negative_stock ? '1' : '0',
                    $product->status,
                ]);
            });
            fclose($output);
        }, 'catalog-products-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function safeText(string $value): string
    {
        return preg_match('/^[=+\-@\t\r]/', $value) === 1 ? "\t{$value}" : $value;
    }
}
