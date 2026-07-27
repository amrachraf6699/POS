<?php

namespace Modules\Business\App\Actions;

use Illuminate\Auth\Access\AuthorizationException;
use Modules\Business\App\Domain\Branches\BranchAuthorization;
use Modules\Business\App\Domain\Onboarding\OnboardingService;
use Modules\Business\App\Models\Branch;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;

final class CreateBranchAction
{
    public function __construct(
        private readonly BranchAuthorization $authorization,
        private readonly OnboardingService $onboarding,
    ) {}

    public function execute(User $actor, Tenant $tenant, array $attributes): Branch
    {
        if (! $this->authorization->canManage($actor, $tenant)) {
            throw new AuthorizationException('You are not authorized to manage branches.');
        }

        $attributes['code'] = strtoupper(trim((string) $attributes['code']));
        $attributes['status'] = Branch::STATUS_ACTIVE;

        /** @var Branch $branch */
        $branch = Branch::query()->create($attributes);
        $this->onboarding->markFirstBranch($branch);

        return $branch;
    }
}
