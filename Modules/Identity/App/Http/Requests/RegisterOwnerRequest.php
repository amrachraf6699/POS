<?php

namespace Modules\Identity\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Modules\Identity\App\Data\RegisterOwnerData;

class RegisterOwnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ! $this->user();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'tenant_name' => ['required', 'string', 'max:255'],
            'tenant_slug' => ['nullable', 'string', 'max:100', 'alpha_dash', 'unique:tenants,slug'],
        ];
    }

    public function toData(): RegisterOwnerData
    {
        $validated = $this->validated();

        return new RegisterOwnerData(
            name: trim($validated['name']),
            email: trim(strtolower($validated['email'])),
            password: $validated['password'],
            tenantName: trim($validated['tenant_name']),
            tenantSlug: $validated['tenant_slug'] ?? null,
        );
    }
}
