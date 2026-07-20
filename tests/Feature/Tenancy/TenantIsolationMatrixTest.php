<?php

namespace Tests\Feature\Tenancy;

use Illuminate\Support\Facades\Schema;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Domain\Tenancy\TenantContextException;
use Modules\Identity\App\Models\Membership;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;
use Tests\Support\Models\TenantNote;
use Tests\Support\Tenancy\TenantIsolationTestCase;

class TenantIsolationMatrixTest extends TenantIsolationTestCase
{
    public function test_tenant_scoped_queries_fail_closed_without_context(): void
    {
        $this->expectException(TenantContextException::class);

        TenantNote::query()->get();
    }

    public function test_inactive_user_cannot_execute_a_tenant_owned_route(): void
    {
        [$user, $tenant] = $this->makeMembership(userStatus: User::STATUS_INACTIVE);

        $this->actingAs($user)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->get('/__test/tenant-notes/1')
            ->assertForbidden();
    }

    public function test_inactive_membership_cannot_execute_a_tenant_owned_route(): void
    {
        [$user, $tenant] = $this->makeMembership(membershipStatus: Membership::STATUS_INACTIVE);

        $this->actingAs($user)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->get('/__test/tenant-notes/1')
            ->assertForbidden();
    }

    public function test_soft_deleted_tenant_cannot_execute_a_tenant_owned_route(): void
    {
        [$user, $tenant] = $this->makeMembership();
        $tenant->delete();

        $this->actingAs($user)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->get('/__test/tenant-notes/1')
            ->assertForbidden();
    }

    public function test_context_is_rebuilt_for_the_selected_tenant_on_the_next_request(): void
    {
        [$user, $firstTenant, $firstMembership] = $this->makeMembership();
        /** @var Tenant $secondTenant */
        $secondTenant = Tenant::factory()->create();
        /** @var Membership $secondMembership */
        $secondMembership = Membership::factory()->create([
            'user_id' => $user->getKey(),
            'tenant_id' => $secondTenant->getKey(),
            'role' => Membership::ROLE_MANAGER,
        ]);

        app(TenantContext::class)->set($firstTenant, $firstMembership);
        $this->actingAs($user)->withSession(['current_tenant_id' => $firstTenant->getKey()])
            ->post('/tenant/select/'.$secondTenant->getKey(), ['_token' => csrf_token()])
            ->assertRedirect(route('business.dashboard'));

        $this->assertFalse(app(TenantContext::class)->hasTenant());

        $this->actingAs($user)->withSession(['current_tenant_id' => $secondTenant->getKey()])
            ->get('/__test/tenant-notes/1')
            ->assertNotFound();
        $this->assertSame($secondMembership->getKey(), app(TenantContext::class)->membership()->getKey());
    }

    public function test_central_routes_do_not_require_tenant_context(): void
    {
        $this->assertTrue(Schema::hasTable('tenant_notes'));
        $this->get('/register')->assertOk()->assertSee('dir="rtl"', false);
        $this->get('/__tracker')->assertOk()->assertSee('AI agent workspace');
    }
}
