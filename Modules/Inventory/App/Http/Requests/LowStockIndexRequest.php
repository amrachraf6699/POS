<?php

namespace Modules\Inventory\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Identity\App\Domain\Authorization\TenantAuthorization;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Models\User;

final class LowStockIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User
            && app(TenantAuthorization::class)->allows($user, app(TenantContext::class)->tenant(), 'inventory.view');
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'branch' => [
                'nullable',
                'integer',
                'min:1',
                Rule::exists('branches', 'id')->where(fn ($query) => $query
                    ->where('tenant_id', app(TenantContext::class)->id())
                    ->where('status', 'active')),
            ],
        ];
    }
}
