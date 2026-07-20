<?php

namespace Tests\Feature\Business;

use Modules\Identity\App\Models\Membership;
use Modules\Identity\App\Models\User;
use Tests\Support\Tenancy\TenantIsolationTestCase;

class ProductNavigationTest extends TenantIsolationTestCase
{
    public function test_owner_shell_links_current_product_surfaces_and_exposes_mobile_controls(): void
    {
        [$owner, $tenant] = $this->makeMembership();

        $response = $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])->get('/tenant/dashboard');

        $response->assertOk()
            ->assertSee(route('business.dashboard'))
            ->assertSee(route('business.settings.edit'))
            ->assertSee(route('business.branches.index'))
            ->assertSee(route('tenant.invitations.index'))
            ->assertSee(route('tenant.selection'))
            ->assertSee('data-mobile-toggle', false)
            ->assertSee('data-mobile-close', false)
            ->assertSee('boxicons.min.css', false)
            ->assertSee('bx bx-menu', false)
            ->assertSee('translate-x-full', false)
            ->assertSee('product-sidebar.is-open', false)
            ->assertSee('overflow-visible', false)
            ->assertDontSee('<svg', false)
            ->assertSee('قريباً');
    }

    public function test_ordinary_member_shell_hides_management_links_but_keeps_shared_navigation(): void
    {
        [$owner, $tenant] = $this->makeMembership();
        /** @var User $member */
        $member = User::factory()->create();
        Membership::factory()->create(['tenant_id' => $tenant->getKey(), 'user_id' => $member->getKey(), 'role' => 'staff']);

        $response = $this->actingAs($member)->withSession(['current_tenant_id' => $tenant->getKey()])->get('/tenant/dashboard');

        $response->assertOk()
            ->assertSee(route('business.dashboard'))
            ->assertSee(route('business.branches.index'))
            ->assertSee(route('tenant.selection'))
            ->assertDontSee(route('business.settings.edit'))
            ->assertDontSee(route('tenant.invitations.index'))
            ->assertDontSee('إعدادات النشاط')
            ->assertDontSee('دعوات الفريق');

        $this->assertTrue($owner->isActive());
    }

    public function test_existing_tenant_pages_share_dashboard_navigation(): void
    {
        [$owner, $tenant] = $this->makeMembership();
        $session = ['current_tenant_id' => $tenant->getKey()];

        $this->actingAs($owner)->withSession($session)->get('/tenant/settings/business')
            ->assertOk()->assertSee(route('business.dashboard'));
        $this->actingAs($owner)->withSession($session)->get('/tenant/invitations')
            ->assertOk()->assertSee(route('business.dashboard'));
        $this->actingAs($owner)->withSession($session)->get('/tenant/branches')
            ->assertOk()->assertSee(route('business.dashboard'));
    }
}
