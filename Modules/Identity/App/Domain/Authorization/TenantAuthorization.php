<?php

namespace Modules\Identity\App\Domain\Authorization;

use Modules\Identity\App\Models\Membership;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

final class TenantAuthorization
{
    public function __construct(private readonly PermissionRegistrar $registrar) {}

    public function allows(User $user, Tenant $tenant, string $permission): bool
    {
        if (! $user->isActive() || ! $tenant->isActive() || ! $this->membership($user, $tenant)?->isActive()) {
            return false;
        }

        $this->forTenant($tenant);
        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');

        if (! Permission::query()->where('name', $permission)->where('guard_name', 'web')->exists()) {
            return false;
        }

        return $user->can($permission);
    }

    public function membership(User $user, Tenant $tenant): ?Membership
    {
        return Membership::query()->where('tenant_id', $tenant->getKey())->where('user_id', $user->getKey())->first();
    }

    public function forTenant(Tenant $tenant): void
    {
        $this->registrar->setPermissionsTeamId($tenant->getKey());
    }
}
