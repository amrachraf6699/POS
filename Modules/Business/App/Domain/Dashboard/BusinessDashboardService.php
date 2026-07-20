<?php

namespace Modules\Business\App\Domain\Dashboard;

use Modules\Business\App\Domain\Branches\BranchAuthorization;
use Modules\Business\App\Domain\Settings\BusinessSettingsAuthorization;
use Modules\Business\App\Models\Branch;
use Modules\Business\App\Models\BranchAssignment;
use Modules\Business\App\Models\BusinessSettings;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Models\Invitation;
use Modules\Identity\App\Models\User;

final class BusinessDashboardService
{
    public function __construct(
        private readonly TenantContext $context,
        private readonly BranchAuthorization $branchAuthorization,
        private readonly BusinessSettingsAuthorization $settingsAuthorization,
    ) {}

    public function summarize(User $user): BusinessDashboardData
    {
        $tenant = $this->context->tenant();
        $membership = $this->context->membership();
        $canManage = $this->branchAuthorization->canManage($user, $tenant);
        /** @phpstan-ignore-next-line dynamic Eloquent scope */
        $accessibleBranchCount = Branch::query()->accessibleTo($user)->count();

        $visibleBranchCount = $accessibleBranchCount;
        $activeBranchCount = $accessibleBranchCount;
        $inactiveBranchCount = 0;

        if ($canManage) {
            $visibleBranchCount = Branch::query()->count();
            $activeBranchCount = Branch::query()->where('status', Branch::STATUS_ACTIVE)->count();
            $inactiveBranchCount = Branch::query()->where('status', Branch::STATUS_INACTIVE)->count();
        }

        return new BusinessDashboardData(
            tenant: $tenant,
            membership: $membership,
            accessibleBranchCount: $accessibleBranchCount,
            visibleBranchCount: $visibleBranchCount,
            activeBranchCount: $activeBranchCount,
            inactiveBranchCount: $inactiveBranchCount,
            activeAssignmentCount: BranchAssignment::query()
                ->where('user_id', $user->getKey())
                ->where('status', BranchAssignment::STATUS_ACTIVE)
                ->count(),
            pendingInvitationCount: $canManage
                ? Invitation::query()->where('tenant_id', $tenant->getKey())->where('status', Invitation::STATUS_PENDING)->count()
                : 0,
            settingsConfigured: BusinessSettings::query()->exists(),
            canManage: $canManage && $this->settingsAuthorization->canManage($user, $tenant),
        );
    }
}
