<?php

namespace Modules\Business\App\Domain\Settings;

use Modules\Identity\App\Models\Membership;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;

final class BusinessSettingsAuthorization
{
    public function canManage(User $user, Tenant $tenant): bool
    {
        if (! $user->isActive() || ! $tenant->isActive()) {
            return false;
        }

        $membership = Membership::query()
            ->where('user_id', $user->getKey())
            ->where('tenant_id', $tenant->getKey())
            ->where('status', Membership::STATUS_ACTIVE)
            ->first();

        return $membership?->canManageInvitations() ?? false;
    }
}
