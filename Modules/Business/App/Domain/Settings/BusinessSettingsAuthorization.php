<?php

namespace Modules\Business\App\Domain\Settings;

use Modules\Identity\App\Domain\Authorization\TenantAuthorization;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;

final class BusinessSettingsAuthorization
{
    public function __construct(private readonly TenantAuthorization $authorization) {}

    public function canManage(User $user, Tenant $tenant): bool
    {
        return $this->authorization->allows($user, $tenant, 'settings.update');
    }
}
