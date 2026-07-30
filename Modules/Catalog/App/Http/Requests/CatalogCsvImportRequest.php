<?php

namespace Modules\Catalog\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Catalog\App\Domain\CatalogAuthorization;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Models\User;

final class CatalogCsvImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $context = app(TenantContext::class);

        return $user instanceof User && $context->hasTenant() && app(CatalogAuthorization::class)->allows($user, $context->tenant(), 'products.import');
    }

    public function rules(): array
    {
        return ['catalog_csv' => ['required', 'file', 'mimes:csv,txt', 'max:1024']];
    }
}
