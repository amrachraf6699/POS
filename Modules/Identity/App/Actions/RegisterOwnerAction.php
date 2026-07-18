<?php

namespace Modules\Identity\App\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Identity\App\Data\RegisterOwnerData;
use Modules\Identity\App\Data\RegistrationResult;
use Modules\Identity\App\Models\Membership;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;

class RegisterOwnerAction
{
    public function execute(RegisterOwnerData $data): RegistrationResult
    {
        $result = DB::transaction(function () use ($data): RegistrationResult {
            $slug = $this->resolveSlug($data->tenantName, $data->tenantSlug);

            /** @var User $user */
            $user = User::query()->create([
                'name' => $data->name,
                'email' => strtolower(trim($data->email)),
                'password' => $data->password,
                'status' => User::STATUS_ACTIVE,
            ]);

            /** @var Tenant $tenant */
            $tenant = Tenant::query()->create([
                'name' => $data->tenantName,
                'slug' => $slug,
                'status' => Tenant::STATUS_ACTIVE,
            ]);

            /** @var Membership $membership */
            $membership = Membership::query()->create([
                'tenant_id' => $tenant->getKey(),
                'user_id' => $user->getKey(),
                'role' => Membership::ROLE_OWNER,
                'status' => Membership::STATUS_ACTIVE,
            ]);

            return new RegistrationResult($user, $tenant, $membership);
        });

        Auth::login($result->user);
        session(['current_tenant_id' => $result->tenant->getKey()]);

        return $result;
    }

    private function resolveSlug(string $tenantName, ?string $requestedSlug): string
    {
        if ($requestedSlug !== null && trim($requestedSlug) !== '') {
            $slug = Str::slug($requestedSlug);

            if (Tenant::query()->where('slug', $slug)->exists()) {
                throw ValidationException::withMessages([
                    'tenant_slug' => 'The selected tenant slug is already in use.',
                ]);
            }

            return $slug;
        }

        $base = Str::slug($tenantName) ?: 'tenant';
        $slug = $base;
        $suffix = 2;

        while (Tenant::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
