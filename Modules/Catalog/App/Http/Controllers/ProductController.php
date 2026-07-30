<?php

namespace Modules\Catalog\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\Business\App\Models\BusinessSettings;
use Modules\Catalog\App\Actions\DeactivateProductAction;
use Modules\Catalog\App\Actions\DeleteProductAction;
use Modules\Catalog\App\Actions\DownloadProductImportSampleAction;
use Modules\Catalog\App\Actions\ExportProductsToCsvAction;
use Modules\Catalog\App\Actions\ImportProductsFromCsvAction;
use Modules\Catalog\App\Actions\SaveProductAction;
use Modules\Catalog\App\Domain\CatalogAuthorization;
use Modules\Catalog\App\Http\Requests\CatalogCsvImportRequest;
use Modules\Catalog\App\Http\Requests\ProductRequest;
use Modules\Catalog\App\Models\Category;
use Modules\Catalog\App\Models\Product;
use Modules\Catalog\App\Models\TaxRate;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ProductController extends Controller
{
    public function __construct(private readonly TenantContext $context, private readonly CatalogAuthorization $authorization) {}

    public function index(): View
    {
        $this->allow('products.view');

        return view('catalog::products.index', [
            'products' => Product::with(['category', 'taxRate'])->orderBy('name')->get(),
            'canCreate' => $this->can('products.create'),
            'canUpdate' => $this->can('products.update'),
            'canImport' => $this->can('products.import'),
            'canExport' => $this->can('products.export'),
        ]);
    }

    public function create(): View
    {
        $this->allow('products.create');
        /** @var BusinessSettings $settings */
        $settings = BusinessSettings::query()->firstOrFail();

        return view('catalog::products.form', ['product' => new Product(['track_inventory' => true, 'low_stock_threshold' => $settings->low_stock_threshold, 'allow_negative_stock' => $settings->allow_negative_stock]), 'categories' => Category::query()->orderBy('name')->get(), 'taxRates' => $this->rates()]);
    }

    public function store(ProductRequest $request, SaveProductAction $action): RedirectResponse
    {
        $product = $action->create($request->user(), $this->context->tenant(), $request->validated());

        return redirect()->route('catalog.products.edit', $product)->with('status', 'تم إنشاء المنتج.');
    }

    public function edit(Product $product): View
    {
        $this->allow('products.update');

        return view('catalog::products.form', ['product' => $product, 'categories' => Category::query()->orderBy('name')->get(), 'taxRates' => $this->rates()]);
    }

    public function update(ProductRequest $request, Product $product, SaveProductAction $action): RedirectResponse
    {
        $action->update($request->user(), $this->context->tenant(), $product, $request->validated());

        return back()->with('status', 'تم حفظ المنتج.');
    }

    public function deactivate(Product $product, DeactivateProductAction $action): RedirectResponse
    {
        $action->execute(request()->user(), $this->context->tenant(), $product);

        return back()->with('status', 'تم تعطيل المنتج.');
    }

    public function destroy(Product $product, DeleteProductAction $action): RedirectResponse
    {
        $action->execute(request()->user(), $this->context->tenant(), $product);

        return redirect()->route('catalog.products.index')->with('status', 'تم حذف المنتج.');
    }

    public function importForm(): View
    {
        $this->allow('products.import');

        return view('catalog::products.import', ['sampleAvailable' => $this->hasImportSampleSources()]);
    }

    public function downloadImportSample(DownloadProductImportSampleAction $action): StreamedResponse
    {
        $this->allow('products.import');
        abort_unless($this->hasImportSampleSources(), 404);

        return $action->execute(request()->user(), $this->context->tenant());
    }

    public function import(CatalogCsvImportRequest $request, ImportProductsFromCsvAction $action): RedirectResponse
    {
        $result = $action->execute($request->user(), $this->context->tenant(), $request->file('catalog_csv'));

        return redirect()->route('catalog.products.import.form')
            ->with('status', "تم استيراد {$result->importedRows} منتجاً.")
            ->with('catalog_import_result', $result->toArray());
    }

    public function export(ExportProductsToCsvAction $action): StreamedResponse
    {
        return $action->execute(request()->user(), $this->context->tenant());
    }

    private function rates()
    {
        return TaxRate::query()->where('status', 'active')->whereDate('effective_from', '<=', today())->where(fn ($query) => $query->whereNull('effective_to')->orWhereDate('effective_to', '>', today()))->orderBy('name')->get();
    }

    private function hasImportSampleSources(): bool
    {
        return Category::query()->exists() && $this->rates()->isNotEmpty();
    }

    private function can(string $permission): bool
    {
        return $this->authorization->allows(request()->user(), $this->context->tenant(), $permission);
    }

    private function allow(string $permission): void
    {
        abort_unless($this->can($permission), 403);
    }
}
