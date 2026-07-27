<?php

namespace Modules\Identity\App\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Identity\App\Domain\Authorization\TenantAuthorization;
use Modules\Identity\App\Domain\Authorization\TenantRoleService;
use Modules\Identity\App\Models\Membership;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;

final class UpdateMembershipAction
{
    public function __construct(private readonly TenantAuthorization $authorization, private readonly TenantRoleService $roles) {}

    public function execute(User $actor, Tenant $tenant, Membership $target, string $role, string $status): void
    {
        if (! in_array($role, Membership::roles(), true) || ! in_array($status, [Membership::STATUS_ACTIVE, Membership::STATUS_INACTIVE], true)) {
            throw ValidationException::withMessages(['membership' => 'The selected membership state is invalid.']);
        }

        DB::transaction(function () use ($actor, $tenant, $target, $role, $status): void {
            /** @var Membership $target */
            $target = Membership::query()->whereKey($target->getKey())->where('tenant_id', $tenant->getKey())->lockForUpdate()->firstOrFail();
            $actorMembership = Membership::query()->where('tenant_id', $tenant->getKey())->where('user_id', $actor->getKey())->lockForUpdate()->first();
            if ($actorMembership === null || ! $this->authorization->allows($actor, $tenant, 'users.update') || ! $this->canManage($actorMembership, $target, $role)) {
                throw ValidationException::withMessages(['membership' => 'You are not authorized to manage this membership.']);
            }

            $removesOwner = $target->isOwner() && $target->isActive() && ($role !== Membership::ROLE_OWNER || $status !== Membership::STATUS_ACTIVE);
            if ($removesOwner && Membership::query()->where('tenant_id', $tenant->getKey())->where('role', Membership::ROLE_OWNER)->where('status', Membership::STATUS_ACTIVE)->lockForUpdate()->count() < 2) {
                throw ValidationException::withMessages(['membership' => 'Each tenant must retain at least one active owner.']);
            }

            $target->update(['role' => $role, 'status' => $status]);
            $this->roles->assign($target->user, $tenant, $role);
        });
    }

    private function canManage(Membership $actor, Membership $target, string $newRole): bool
    {
        return $actor->isOwner() || (! $target->isOwner() && ! $target->isManager() && in_array($newRole, [Membership::ROLE_CASHIER, Membership::ROLE_INVENTORY_STAFF], true));
    }
}
