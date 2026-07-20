<?php

namespace Modules\Business\App\Domain\Navigation;

use Modules\Business\App\Domain\Branches\BranchAuthorization;
use Modules\Business\App\Domain\Settings\BusinessSettingsAuthorization;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Models\User;

final class ProductNavigation
{
    public function __construct(
        private readonly TenantContext $context,
        private readonly BranchAuthorization $branchAuthorization,
        private readonly BusinessSettingsAuthorization $settingsAuthorization,
    ) {}

    /** @return array{items: array<int, array{label: string, url: string, patterns: array<int, string>, icon: string}>, future: array<int, array{label: string, icon: string}>, tenants: \Illuminate\Database\Eloquent\Collection<int, \Modules\Identity\App\Models\Tenant>} */
    public function build(?User $user): array
    {
        if (! $user instanceof User || ! $this->context->hasTenant()) {
            return ['items' => [], 'future' => $this->futureItems(), 'tenants' => new \Illuminate\Database\Eloquent\Collection];
        }

        $tenant = $this->context->tenant();
        $canManage = $this->branchAuthorization->canManage($user, $tenant);
        $items = [
            ['label' => 'لوحة التحكم', 'url' => route('business.dashboard'), 'patterns' => ['business.dashboard', 'home'], 'icon' => 'bx-grid-alt'],
            ['label' => $canManage ? 'الفروع وتعيينات الفريق' : 'الفروع المتاحة', 'url' => route('business.branches.index'), 'patterns' => ['business.branches.*'], 'icon' => 'bx-store'],
        ];

        if ($this->settingsAuthorization->canManage($user, $tenant)) {
            $items[] = ['label' => 'إعدادات النشاط', 'url' => route('business.settings.edit'), 'patterns' => ['business.settings.*'], 'icon' => 'bx-cog'];
            $items[] = ['label' => 'دعوات الفريق', 'url' => route('tenant.invitations.index'), 'patterns' => ['tenant.invitations.*'], 'icon' => 'bx-envelope'];
        }

        return [
            'items' => $items,
            'future' => $this->futureItems(),
            'tenants' => $user->accessibleTenants()->orderBy('name')->get(),
        ];
    }

    /** @return array<int, array{label: string, icon: string}> */
    private function futureItems(): array
    {
        return [
            ['label' => 'المنتجات والكتالوج', 'icon' => 'bx-package'],
            ['label' => 'المخزون والتحويلات', 'icon' => 'bx-transfer-alt'],
            ['label' => 'نقطة البيع والمدفوعات', 'icon' => 'bx-cart'],
            ['label' => 'التقارير والتحليلات', 'icon' => 'bx-bar-chart-alt-2'],
            ['label' => 'الاشتراكات والفوترة', 'icon' => 'bx-credit-card'],
        ];
    }
}
