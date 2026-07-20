<?php

namespace Modules\Business\App\Domain\Dashboard;

use Modules\Identity\App\Models\Membership;
use Modules\Identity\App\Models\Tenant;

final class BusinessDashboardData
{
    public function __construct(
        public readonly Tenant $tenant,
        public readonly Membership $membership,
        public readonly int $accessibleBranchCount,
        public readonly int $visibleBranchCount,
        public readonly int $activeBranchCount,
        public readonly int $inactiveBranchCount,
        public readonly int $activeAssignmentCount,
        public readonly int $pendingInvitationCount,
        public readonly bool $settingsConfigured,
        public readonly bool $canManage,
    ) {}
}
