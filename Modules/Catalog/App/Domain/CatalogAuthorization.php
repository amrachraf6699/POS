<?php

namespace Modules\Catalog\App\Domain;

use Modules\Identity\App\Domain\Authorization\TenantAuthorization;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;

final class CatalogAuthorization
{
    public function __construct(private readonly TenantAuthorization $authorization) {}

    public function canManage(User $user, Tenant $tenant): bool
    {
        return $this->authorization->allows($user, $tenant, 'business.update');
    }

    public function allows(User $user, Tenant $tenant, string $permission): bool
    {
        return $this->authorization->allows($user, $tenant, $permission);
    }
}
