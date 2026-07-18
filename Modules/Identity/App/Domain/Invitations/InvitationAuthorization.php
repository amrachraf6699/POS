<?php

namespace Modules\Identity\App\Domain\Invitations;

use Modules\Identity\App\Models\Membership;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;

final class InvitationAuthorization
{
    public function membershipFor(User $user, Tenant $tenant): ?Membership
    {
        if (! $user->isActive() || ! $tenant->isActive()) {
            return null;
        }

        return Membership::query()
            ->where('user_id', $user->getKey())
            ->where('tenant_id', $tenant->getKey())
            ->where('status', Membership::STATUS_ACTIVE)
            ->first();
    }

    public function canManage(User $user, Tenant $tenant): bool
    {
        return $this->membershipFor($user, $tenant)?->canManageInvitations() ?? false;
    }
}
