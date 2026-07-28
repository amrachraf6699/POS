<?php

namespace Modules\Catalog\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Catalog\App\Domain\CatalogAuthorization;
use Modules\Catalog\App\Models\TaxRate;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Models\User;

final class TaxRateMetadataRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $context = app(TenantContext::class);

        return $user instanceof User && $context->hasTenant() && app(CatalogAuthorization::class)->canManage($user, $context->tenant());
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['name' => trim((string) $this->input('name'))]);
    }

    public function rules(): array
    {
        $taxRate = $this->route('taxRate');
        $ignore = $taxRate instanceof TaxRate ? $taxRate->getKey() : null;
        $effectiveFrom = $taxRate instanceof TaxRate ? $taxRate->effective_from->toDateString() : '';

        return ['name' => ['required', 'string', 'max:255', Rule::unique('tax_rates', 'name')->where(fn ($query) => $query->where('tenant_id', app(TenantContext::class)->id())->where('effective_from', $effectiveFrom))->ignore($ignore)], 'status' => ['required', Rule::in([TaxRate::STATUS_ACTIVE, TaxRate::STATUS_INACTIVE])]];
    }
}
