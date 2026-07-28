<?php

namespace Modules\Catalog\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\Business\App\Models\BusinessSettings;
use Modules\Catalog\App\Actions\DeactivateProductAction;
use Modules\Catalog\App\Actions\DeleteProductAction;
use Modules\Catalog\App\Actions\SaveProductAction;
use Modules\Catalog\App\Domain\CatalogAuthorization;
use Modules\Catalog\App\Http\Requests\ProductRequest;
use Modules\Catalog\App\Models\Category;
use Modules\Catalog\App\Models\Product;
use Modules\Catalog\App\Models\TaxRate;
use Modules\Identity\App\Domain\Tenancy\TenantContext;

final class ProductController extends Controller
{
    public function __construct(private readonly TenantContext $c, private readonly CatalogAuthorization $a) {}

    public function index(): View
    {
        $this->allow('products.view');

        return view('catalog::products.index', ['products' => Product::with(['category', 'taxRate'])->orderBy('name')->get(), 'canCreate' => $this->can('products.create')]);
    }

    public function create(): View
    {
        $this->allow('products.create');
        /** @var BusinessSettings $s */
        $s = BusinessSettings::query()->firstOrFail();

        return view('catalog::products.form', ['product' => new Product(['track_inventory' => true, 'low_stock_threshold' => $s->low_stock_threshold, 'allow_negative_stock' => $s->allow_negative_stock]), 'categories' => Category::query()->orderBy('name')->get(), 'taxRates' => $this->rates()]);
    }

    public function store(ProductRequest $r, SaveProductAction $x): RedirectResponse
    {
        $p = $x->create($r->user(), $this->c->tenant(), $r->validated());

        return redirect()->route('catalog.products.edit', $p)->with('status', 'تم إنشاء المنتج.');
    }

    public function edit(Product $product): View
    {
        $this->allow('products.update');

        return view('catalog::products.form', ['product' => $product, 'categories' => Category::query()->orderBy('name')->get(), 'taxRates' => $this->rates()]);
    }

    public function update(ProductRequest $r, Product $product, SaveProductAction $x): RedirectResponse
    {
        $x->update($r->user(), $this->c->tenant(), $product, $r->validated());

        return back()->with('status', 'تم حفظ المنتج.');
    }

    public function deactivate(Product $product, DeactivateProductAction $x): RedirectResponse
    {
        $x->execute(request()->user(), $this->c->tenant(), $product);

        return back()->with('status', 'تم تعطيل المنتج.');
    }

    public function destroy(Product $product, DeleteProductAction $x): RedirectResponse
    {
        $x->execute(request()->user(), $this->c->tenant(), $product);

        return redirect()->route('catalog.products.index')->with('status', 'تم حذف المنتج.');
    }

    private function rates()
    {
        return TaxRate::query()->where('status', 'active')->whereDate('effective_from', '<=', today())->where(fn ($q) => $q->whereNull('effective_to')->orWhereDate('effective_to', '>', today()))->orderBy('name')->get();
    }

    private function can(string $p): bool
    {
        return $this->a->allows(request()->user(), $this->c->tenant(), $p);
    }

    private function allow(string $p): void
    {
        abort_unless($this->can($p), 403);
    }
}
