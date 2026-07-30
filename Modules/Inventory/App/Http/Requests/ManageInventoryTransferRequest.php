<?php

namespace Modules\Inventory\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Identity\App\Domain\Authorization\TenantAuthorization;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Models\User;

class ManageInventoryTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User && app(TenantAuthorization::class)->allows($user, app(TenantContext::class)->tenant(), 'inventory.transfer');
    }

    public function rules(): array
    {
        return [];
    }
}
