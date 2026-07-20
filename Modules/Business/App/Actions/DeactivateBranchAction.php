<?php

namespace Modules\Business\App\Actions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Modules\Business\App\Domain\Branches\BranchAuthorization;
use Modules\Business\App\Models\Branch;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;

final class DeactivateBranchAction
{
    public function __construct(private readonly BranchAuthorization $authorization) {}

    public function execute(User $actor, Tenant $tenant, Branch $branch): Branch
    {
        if (! $this->authorization->canManage($actor, $tenant)) {
            throw new AuthorizationException('You are not authorized to manage branches.');
        }

        if (! $branch->isActive()) {
            return $branch;
        }

        if (Branch::query()->where('status', Branch::STATUS_ACTIVE)->count() <= 1) {
            throw ValidationException::withMessages([
                'branch' => 'لا يمكن تعطيل الفرع النشط الأخير للنشاط التجاري.',
            ]);
        }

        $branch->update(['status' => Branch::STATUS_INACTIVE]);

        return $branch->refresh();
    }
}
