<?php

namespace Modules\Catalog\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\Catalog\App\Actions\CreateTaxRateAction;
use Modules\Catalog\App\Actions\CreateTaxRateVersionAction;
use Modules\Catalog\App\Actions\DeactivateTaxRateAction;
use Modules\Catalog\App\Actions\UpdateTaxRateMetadataAction;
use Modules\Catalog\App\Domain\CatalogAuthorization;
use Modules\Catalog\App\Http\Requests\TaxRateMetadataRequest;
use Modules\Catalog\App\Http\Requests\TaxRateRequest;
use Modules\Catalog\App\Models\TaxRate;
use Modules\Identity\App\Domain\Tenancy\TenantContext;

final class TaxRateController extends Controller
{
    public function __construct(private readonly TenantContext $context, private readonly CatalogAuthorization $authorization) {}

    public function create(): View
    {
        $this->ensureCanManage();

        return view('catalog::tax-rates.create');
    }

    public function store(TaxRateRequest $request, CreateTaxRateAction $action): RedirectResponse
    {
        $taxRate = $action->execute($request->user(), $this->context->tenant(), $request->validated());

        return redirect()->route('catalog.tax-rates.edit', $taxRate)->with('status', 'تم إنشاء نسبة الضريبة بنجاح.');
    }

    public function edit(TaxRate $taxRate): View
    {
        $this->ensureCanManage();

        return view('catalog::tax-rates.edit', ['taxRate' => $taxRate]);
    }

    public function update(TaxRateMetadataRequest $request, TaxRate $taxRate, UpdateTaxRateMetadataAction $action): RedirectResponse
    {
        $action->execute($request->user(), $this->context->tenant(), $taxRate, $request->validated());

        return back()->with('status', 'تم حفظ تسمية وحالة نسبة الضريبة.');
    }

    public function storeVersion(TaxRateRequest $request, TaxRate $taxRate, CreateTaxRateVersionAction $action): RedirectResponse
    {
        $successor = $action->execute($request->user(), $this->context->tenant(), $taxRate, $request->validated());

        return redirect()->route('catalog.tax-rates.edit', $successor)->with('status', 'تم إنشاء الإصدار الجديد وحفظ حد الإصدار السابق.');
    }

    public function deactivate(TaxRate $taxRate, DeactivateTaxRateAction $action): RedirectResponse
    {
        $action->execute(request()->user(), $this->context->tenant(), $taxRate);

        return redirect()->route('catalog.index')->with('status', 'تم تعطيل نسبة الضريبة.');
    }

    private function ensureCanManage(): void
    {
        abort_unless($this->authorization->canManage(request()->user(), $this->context->tenant()), 403);
    }
}
