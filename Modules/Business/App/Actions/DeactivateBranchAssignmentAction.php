<?php

namespace Modules\Business\App\Actions;

use Illuminate\Auth\Access\AuthorizationException;
use Modules\Business\App\Domain\Branches\BranchAuthorization;
use Modules\Business\App\Models\Branch;
use Modules\Business\App\Models\BranchAssignment;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;

final class DeactivateBranchAssignmentAction
{
    public function __construct(private readonly BranchAuthorization $authorization) {}

    public function execute(User $actor, Tenant $tenant, Branch $branch, User $assignedUser): void
    {
        if (! $this->authorization->canManage($actor, $tenant)) {
            throw new AuthorizationException('You are not authorized to manage branch assignments.');
        }

        BranchAssignment::query()
            ->where('branch_id', $branch->getKey())
            ->where('user_id', $assignedUser->getKey())
            ->first()?->update(['status' => BranchAssignment::STATUS_INACTIVE]);
    }
}
