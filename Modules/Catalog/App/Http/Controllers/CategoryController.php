<?php

namespace Modules\Catalog\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\Catalog\App\Actions\CreateCategoryAction;
use Modules\Catalog\App\Actions\DeleteCategoryAction;
use Modules\Catalog\App\Actions\UpdateCategoryAction;
use Modules\Catalog\App\Domain\CatalogAuthorization;
use Modules\Catalog\App\Http\Requests\CategoryRequest;
use Modules\Catalog\App\Models\Category;
use Modules\Catalog\App\Models\TaxRate;
use Modules\Identity\App\Domain\Tenancy\TenantContext;

final class CategoryController extends Controller
{
    public function __construct(private readonly TenantContext $context, private readonly CatalogAuthorization $authorization) {}

    public function index(): View
    {
        $this->ensureCanManage();

        return view('catalog::index', ['categories' => Category::query()->orderBy('name')->get(), 'taxRates' => TaxRate::query()->orderByDesc('effective_from')->orderBy('name')->get()]);
    }

    public function create(): View
    {
        $this->ensureCanManage();

        return view('catalog::categories.form', ['category' => new Category]);
    }

    public function store(CategoryRequest $request, CreateCategoryAction $action): RedirectResponse
    {
        $category = $action->execute($request->user(), $this->context->tenant(), $request->validated());

        return redirect()->route('catalog.categories.edit', $category)->with('status', 'تم إنشاء الفئة بنجاح.');
    }

    public function edit(Category $category): View
    {
        $this->ensureCanManage();

        return view('catalog::categories.form', ['category' => $category]);
    }

    public function update(CategoryRequest $request, Category $category, UpdateCategoryAction $action): RedirectResponse
    {
        $action->execute($request->user(), $this->context->tenant(), $category, $request->validated());

        return back()->with('status', 'تم حفظ بيانات الفئة.');
    }

    public function destroy(Category $category, DeleteCategoryAction $action): RedirectResponse
    {
        $action->execute(request()->user(), $this->context->tenant(), $category);

        return redirect()->route('catalog.index')->with('status', 'تم حذف الفئة.');
    }

    private function ensureCanManage(): void
    {
        abort_unless($this->authorization->canManage(request()->user(), $this->context->tenant()), 403);
    }
}
