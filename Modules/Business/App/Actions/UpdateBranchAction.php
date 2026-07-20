<?php

namespace Modules\Business\App\Actions;

use Illuminate\Auth\Access\AuthorizationException;
use Modules\Business\App\Domain\Branches\BranchAuthorization;
use Modules\Business\App\Models\Branch;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;

final class UpdateBranchAction
{
    public function __construct(private readonly BranchAuthorization $authorization) {}

    public function execute(User $actor, Tenant $tenant, Branch $branch, array $attributes): Branch
    {
        if (! $this->authorization->canManage($actor, $tenant)) {
            throw new AuthorizationException('You are not authorized to manage branches.');
        }

        $attributes['code'] = strtoupper(trim((string) $attributes['code']));
        $branch->update($attributes);

        return $branch->refresh();
    }
}
