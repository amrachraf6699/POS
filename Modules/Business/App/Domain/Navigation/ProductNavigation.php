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

    /** @return array{items: array<int, array{label: string, url: string, patterns: array<int, string>}>, future: array<int, string>, tenants: \Illuminate\Database\Eloquent\Collection<int, \Modules\Identity\App\Models\Tenant>} */
    public function build(?User $user): array
    {
        if (! $user instanceof User || ! $this->context->hasTenant()) {
            return ['items' => [], 'future' => $this->futureItems(), 'tenants' => new \Illuminate\Database\Eloquent\Collection];
        }

        $tenant = $this->context->tenant();
        $canManage = $this->branchAuthorization->canManage($user, $tenant);
        $items = [
            ['label' => 'لوحة التحكم', 'url' => route('business.dashboard'), 'patterns' => ['business.dashboard', 'home']],
            ['label' => $canManage ? 'الفروع وتعيينات الفريق' : 'الفروع المتاحة', 'url' => route('business.branches.index'), 'patterns' => ['business.branches.*']],
        ];

        if ($this->settingsAuthorization->canManage($user, $tenant)) {
            $items[] = ['label' => 'إعدادات النشاط', 'url' => route('business.settings.edit'), 'patterns' => ['business.settings.*']];
            $items[] = ['label' => 'دعوات الفريق', 'url' => route('tenant.invitations.index'), 'patterns' => ['tenant.invitations.*']];
        }

        return [
            'items' => $items,
            'future' => $this->futureItems(),
            'tenants' => $user->accessibleTenants()->orderBy('name')->get(),
        ];
    }

    /** @return array<int, string> */
    private function futureItems(): array
    {
        return ['المنتجات والكتالوج', 'المخزون والتحويلات', 'نقطة البيع والمدفوعات', 'التقارير والتحليلات', 'الاشتراكات والفوترة'];
    }
}
