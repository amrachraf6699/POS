<?php

namespace Modules\Business\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Business\App\Domain\Branches\BranchAuthorization;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Models\User;

class UpdateBranchAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $context = app(TenantContext::class);

        return $user instanceof User && $context->hasTenant()
            && app(BranchAuthorization::class)->canManage($user, $context->tenant());
    }

    public function rules(): array
    {
        return ['status' => ['required', 'in:active,inactive']];
    }
}
