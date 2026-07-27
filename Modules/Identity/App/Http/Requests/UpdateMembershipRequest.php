<?php

namespace Modules\Identity\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Identity\App\Domain\Authorization\TenantAuthorization;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Models\Membership;
use Modules\Identity\App\Models\User;

final class UpdateMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User && app(TenantContext::class)->hasTenant() && app(TenantAuthorization::class)->allows($user, app(TenantContext::class)->tenant(), 'users.update');
    }

    public function rules(): array
    {
        return [
            'role' => ['required', 'string', Rule::in(Membership::roles())],
            'status' => ['required', 'string', Rule::in([Membership::STATUS_ACTIVE, Membership::STATUS_INACTIVE])],
        ];
    }
}
