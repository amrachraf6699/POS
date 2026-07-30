<?php

namespace Modules\Inventory\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Identity\App\Domain\Authorization\TenantAuthorization;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Models\User;
use Modules\Inventory\App\Domain\Enums\StockAdjustmentType;

final class PostStockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User
            && app(TenantAuthorization::class)->allows($user, app(TenantContext::class)->tenant(), 'inventory.adjust');
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer', 'min:1'],
            'type' => ['required', 'string', Rule::in(array_column(StockAdjustmentType::cases(), 'value'))],
            'reason' => ['required', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'min:1', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'idempotency_key' => ['required', 'uuid'],
        ];
    }
}
