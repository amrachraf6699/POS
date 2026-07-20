<?php

namespace Modules\Business\App\Domain\Branches;

use Modules\Identity\App\Models\Membership;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;

final class BranchAuthorization
{
    public function canManage(User $user, Tenant $tenant): bool
    {
        if (! $user->isActive() || ! $tenant->isActive()) {
            return false;
        }

        return Membership::query()
            ->where('user_id', $user->getKey())
            ->where('tenant_id', $tenant->getKey())
            ->where('status', Membership::STATUS_ACTIVE)
            ->whereIn('role', [Membership::ROLE_OWNER, Membership::ROLE_MANAGER])
            ->exists();
    }
}
