<?php

namespace Modules\Business\App\Domain\Onboarding;

use Illuminate\Support\Facades\DB;
use Modules\Business\App\Models\Branch;
use Modules\Business\App\Models\TenantOnboarding;
use Modules\Identity\App\Domain\Tenancy\TenantContext;

final class OnboardingService
{
    public function __construct(private readonly TenantContext $context) {}

    public function state(): TenantOnboarding
    {
        $tenantId = $this->context->id();
        /** @var TenantOnboarding $state */
        $state = TenantOnboarding::query()->firstOrCreate(['tenant_id' => $tenantId]);

        return $state;
    }

    public function nextStep(): string
    {
        $state = $this->state();
        if ($state->settings_completed_at === null) {
            return 'settings';
        }
        if (! $this->hasActiveBranch()) {
            return 'branch';
        }
        if ($state->staff_setup_completed_at === null) {
            return 'staff';
        }

        return 'complete';
    }

    public function isComplete(): bool
    {
        return $this->nextStep() === 'complete';
    }

    public function markSettingsCompleted(): void
    {
        DB::transaction(function (): void {
            $state = $this->state();
            $state->forceFill(['settings_completed_at' => now()])->save();
        });
    }

    public function markFirstBranch(Branch $branch): void
    {
        DB::transaction(function () use ($branch): void {
            $state = $this->state();
            $state->forceFill(['first_branch_id' => $branch->getKey()])->save();
        });
    }

    public function markStaffSetupCompleted(): void
    {
        DB::transaction(function (): void {
            $state = $this->state();
            $state->forceFill(['staff_setup_completed_at' => now()])->save();
            $this->completeIfReady($state);
        });
    }

    public function completeIfReady(?TenantOnboarding $state = null): void
    {
        $state ??= $this->state();
        if ($state->settings_completed_at !== null && $this->hasActiveBranch() && $state->staff_setup_completed_at !== null) {
            $state->forceFill(['completed_at' => now()])->save();
        }
    }

    private function hasActiveBranch(): bool
    {
        return Branch::query()->where('status', Branch::STATUS_ACTIVE)->exists();
    }
}
