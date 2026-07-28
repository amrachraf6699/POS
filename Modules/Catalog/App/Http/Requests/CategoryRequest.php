<?php

namespace Modules\Catalog\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Catalog\App\Domain\CatalogAuthorization;
use Modules\Catalog\App\Models\Category;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Models\User;

final class CategoryRequest extends FormRequest
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
        $category = $this->route('category');
        $ignore = $category instanceof Category ? $category->getKey() : null;

        return ['name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->where(fn ($query) => $query->where('tenant_id', app(TenantContext::class)->id()))->ignore($ignore)], 'description' => ['nullable', 'string', 'max:2000']];
    }
}
