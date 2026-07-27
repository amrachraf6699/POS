<?php

namespace Modules\Identity\App\Domain\Authorization;

use Illuminate\Validation\ValidationException;
use Modules\Identity\App\Models\Membership;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class TenantRoleService
{
    public function __construct(private readonly PermissionRegistrar $registrar) {}

    public function provision(Tenant $tenant): void
    {
        $this->registrar->forgetCachedPermissions();

        foreach (PermissionCatalog::all() as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach (Membership::roles() as $name) {
            /** @var Role $role */
            $role = Role::query()->firstOrCreate(['tenant_id' => $tenant->getKey(), 'name' => $name, 'guard_name' => 'web']);
            $role->syncPermissions(PermissionCatalog::permissionsFor($name));
        }

        $this->registrar->forgetCachedPermissions();
    }

    public function assign(User $user, Tenant $tenant, string $role): void
    {
        if (! in_array($role, Membership::roles(), true)) {
            throw ValidationException::withMessages(['role' => 'The selected role is invalid.']);
        }

        $this->provision($tenant);
        $this->registrar->setPermissionsTeamId($tenant->getKey());
        /** @var Role $tenantRole */
        $tenantRole = Role::query()->where('tenant_id', $tenant->getKey())->where('name', $role)->where('guard_name', 'web')->firstOrFail();
        $user->syncRoles([$tenantRole]);
        $this->registrar->forgetCachedPermissions();
    }
}
