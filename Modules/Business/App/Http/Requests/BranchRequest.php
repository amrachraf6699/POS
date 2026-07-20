<?php

namespace Modules\Business\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Business\App\Domain\Branches\BranchAuthorization;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Models\User;

class BranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $context = app(TenantContext::class);

        return $user instanceof User && $context->hasTenant()
            && app(BranchAuthorization::class)->canManage($user, $context->tenant());
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper(trim((string) $this->input('code'))),
            'country_code' => strtoupper(trim((string) $this->input('country_code', 'EG'))),
        ]);
    }

    public function rules(): array
    {
        $branch = $this->route('branch');
        $ignore = $branch instanceof \Modules\Business\App\Models\Branch ? $branch->getKey() : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required', 'string', 'max:50', 'regex:/^[A-Z0-9][A-Z0-9_-]{0,49}$/',
                Rule::unique('branches', 'code')->where(fn ($query) => $query->where('tenant_id', app(TenantContext::class)->id()))->ignore($ignore),
            ],
            'phone' => ['nullable', 'string', 'max:64'],
            'email' => ['nullable', 'email', 'max:255'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:30'],
            'country_code' => ['required', 'regex:/^[A-Z]{2}$/'],
            'timezone' => ['required', 'timezone'],
        ];
    }
}
