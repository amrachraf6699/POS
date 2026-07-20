<?php

namespace Modules\Business\App\Actions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Modules\Business\App\Domain\Branches\BranchAuthorization;
use Modules\Business\App\Models\Branch;
use Modules\Business\App\Models\BranchAssignment;
use Modules\Identity\App\Models\Membership;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;

final class AssignBranchUserAction
{
    public function __construct(private readonly BranchAuthorization $authorization) {}

    public function execute(User $actor, Tenant $tenant, Branch $branch, User $assignedUser): BranchAssignment
    {
        if (! $this->authorization->canManage($actor, $tenant)) {
            throw new AuthorizationException('You are not authorized to manage branch assignments.');
        }

        if (! $branch->isActive()) {
            throw ValidationException::withMessages(['branch' => 'لا يمكن تعيين مستخدم إلى فرع غير نشط.']);
        }

        if (! Membership::query()->where('tenant_id', $tenant->getKey())->where('user_id', $assignedUser->getKey())->exists()) {
            throw ValidationException::withMessages(['user_id' => 'المستخدم ليس عضواً في هذا النشاط التجاري.']);
        }

        /** @var BranchAssignment $assignment */
        $assignment = BranchAssignment::query()->firstOrNew([
            'branch_id' => $branch->getKey(),
            'user_id' => $assignedUser->getKey(),
        ]);
        $assignment->status = BranchAssignment::STATUS_ACTIVE;
        $assignment->save();

        /** @var BranchAssignment $refreshed */
        $refreshed = $assignment->refresh();

        return $refreshed;
    }
}
