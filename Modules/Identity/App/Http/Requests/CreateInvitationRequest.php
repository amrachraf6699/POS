<?php

namespace Modules\Identity\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Identity\App\Domain\Invitations\InvitationAuthorization;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Models\User;

class CreateInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User
            && app(InvitationAuthorization::class)->canManage($user, app(TenantContext::class)->tenant());
    }

    public function rules(): array
    {
        return ['email' => ['required', 'email', 'max:255']];
    }
}
