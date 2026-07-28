<?php

namespace Modules\Catalog\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Catalog\App\Domain\CatalogAuthorization;
use Modules\Catalog\App\Models\Product;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Models\User;

final class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();
        $c = app(TenantContext::class);

        return $u instanceof User && $c->hasTenant() && app(CatalogAuthorization::class)->allows($u, $c->tenant(), $this->isMethod('post') ? 'products.create' : 'products.update');
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['name' => trim((string) $this->input('name')), 'sku' => ($v = trim((string) $this->input('sku'))) === '' ? null : $v, 'barcode' => ($v = trim((string) $this->input('barcode'))) === '' ? null : $v]);
    }

    public function rules(): array
    {
        $p = $this->route('product');
        $id = $p instanceof Product ? $p->getKey() : null;
        $tenant = app(TenantContext::class)->id();

        return ['name' => ['required', 'string', 'max:255'], 'category_id' => ['required', Rule::exists('categories', 'id')->where('tenant_id', $tenant)], 'tax_rate_id' => ['required', Rule::exists('tax_rates', 'id')->where('tenant_id', $tenant)->where('status', 'active')], 'sku' => ['nullable', 'string', 'max:100', Rule::unique('products', 'sku')->where('tenant_id', $tenant)->ignore($id)], 'barcode' => ['nullable', 'string', 'max:100', Rule::unique('products', 'barcode')->where('tenant_id', $tenant)->ignore($id)], 'description' => ['nullable', 'string', 'max:2000'], 'cost_price_minor' => ['required', 'integer', 'min:0'], 'selling_price_minor' => ['required', 'integer', 'min:0'], 'track_inventory' => ['required', 'boolean'], 'low_stock_threshold' => ['required', 'integer', 'min:0'], 'allow_negative_stock' => ['required', 'boolean']];
    }
}
