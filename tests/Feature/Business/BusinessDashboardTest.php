<?php

namespace Tests\Feature\Business;

use Modules\Business\App\Models\Branch;
use Modules\Business\App\Models\BranchAssignment;
use Modules\Identity\App\Models\Membership;
use Modules\Identity\App\Models\User;
use Tests\Support\Tenancy\TenantIsolationTestCase;

class BusinessDashboardTest extends TenantIsolationTestCase
{
    public function test_active_members_can_open_dashboard_and_owner_sees_management_metrics(): void
    {
        [$owner, $tenant, $membership] = $this->makeMembership();
        $this->establishContext($tenant, $membership);
        Branch::factory()->create(['code' => 'MAIN']);
        Branch::factory()->create(['code' => 'CLOSED', 'status' => Branch::STATUS_INACTIVE]);
        $response = $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])->get('/tenant/dashboard');
        $response->assertOk()->assertSee('مرحباً بك')->assertSee('إعدادات النشاط التجاري')->assertSee('إضافة فرع جديد')->assertSee('دعوة مستخدم')->assertSee('قريباً');
    }

    public function test_manager_and_ordinary_members_see_only_their_accessible_branch_data(): void
    {
        [$owner, $tenant, $ownerMembership] = $this->makeMembership();
        /** @var User $manager */ $manager = User::factory()->create();
        $managerMembership = Membership::factory()->create(['tenant_id' => $tenant->getKey(), 'user_id' => $manager->getKey(), 'role' => Membership::ROLE_MANAGER]);
        /** @var User $member */ $member = User::factory()->create();
        $memberMembership = Membership::factory()->create(['tenant_id' => $tenant->getKey(), 'user_id' => $member->getKey(), 'role' => 'staff']);
        $this->establishContext($tenant, $ownerMembership);
        $assigned = Branch::factory()->create(['code' => 'ASSIGNED']);
        Branch::factory()->create(['code' => 'UNASSIGNED']);
        BranchAssignment::factory()->create(['branch_id' => $assigned->getKey(), 'user_id' => $manager->getKey(), 'tenant_id' => $tenant->getKey()]);
        $this->establishContext($tenant, $managerMembership);
        $managerResponse = $this->actingAs($manager)->withSession(['current_tenant_id' => $tenant->getKey()])->get('/tenant/dashboard');
        $managerResponse->assertOk()->assertSee('إعدادات النشاط التجاري')->assertSee('إضافة فرع جديد')->assertSee('دعوة مستخدم');
        $this->establishContext($tenant, $memberMembership);
        $memberResponse = $this->actingAs($member)->withSession(['current_tenant_id' => $tenant->getKey()])->get('/tenant/dashboard');
        $memberResponse->assertOk()->assertDontSee('إعدادات النشاط التجاري')->assertDontSee('دعوة مستخدم')->assertSee('عرض الفروع المتاحة');
        $this->actingAs($member)->withSession(['current_tenant_id' => $tenant->getKey()])->get('/tenant/branches')->assertOk()->assertDontSee('إضافة فرع');
        $this->assertTrue($owner->isActive());
    }

    public function test_home_is_a_compatibility_redirect_to_the_tenant_dashboard(): void
    {
        [$owner, $tenant] = $this->makeMembership();
        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])->get('/home')->assertRedirect(route('business.dashboard'));
    }

    public function test_dashboard_is_tenant_isolated(): void
    {
        [$owner, $tenant] = $this->makeMembership();
        [$otherOwner, $otherTenant, $otherMembership] = $this->makeMembership();
        $this->establishContext($otherTenant, $otherMembership);
        Branch::factory()->create(['name' => 'Other Tenant Branch', 'code' => 'OTHER']);
        $response = $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])->get('/tenant/dashboard');
        $response->assertOk()->assertDontSee('Other Tenant Branch');
        $this->assertTrue($otherOwner->isActive());
    }

    private function establishContext($tenant, $membership): void
    {
        app(\Modules\Identity\App\Domain\Tenancy\TenantContext::class)->set($tenant, $membership);
    }
}
