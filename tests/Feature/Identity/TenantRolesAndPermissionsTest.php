<?php

namespace Tests\Feature\Identity;

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Modules\Identity\App\Actions\UpdateMembershipAction;
use Modules\Identity\App\Domain\Authorization\TenantAuthorization;
use Modules\Identity\App\Models\Membership;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;
use Tests\TestCase;

class TenantRolesAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_roles_are_tenant_scoped_and_cashiers_cannot_adjust_inventory(): void
    {
        /** @var User $user */ $user = User::factory()->create();
        /** @var Tenant $first */ $first = Tenant::factory()->create();
        /** @var Tenant $second */ $second = Tenant::factory()->create();
        Membership::factory()->create(['user_id' => $user->id, 'tenant_id' => $first->id, 'role' => Membership::ROLE_CASHIER]);
        Membership::factory()->create(['user_id' => $user->id, 'tenant_id' => $second->id, 'role' => Membership::ROLE_INVENTORY_STAFF]);

        $authorization = app(TenantAuthorization::class);
        $this->assertFalse($authorization->allows($user, $first, 'inventory.adjust'));
        $this->assertTrue($authorization->allows($user, $second, 'inventory.adjust'));
        $this->assertFalse($authorization->allows($user, $first, 'sales.refund'));
    }

    public function test_final_active_owner_cannot_be_demoted_or_deactivated(): void
    {
        /** @var User $owner */ $owner = User::factory()->create();
        /** @var Tenant $tenant */ $tenant = Tenant::factory()->create();
        /** @var Membership $membership */ $membership = Membership::factory()->create(['user_id' => $owner->id, 'tenant_id' => $tenant->id, 'role' => Membership::ROLE_OWNER]);

        $this->expectException(ValidationException::class);
        app(UpdateMembershipAction::class)->execute($owner, $tenant, $membership, Membership::ROLE_MANAGER, Membership::STATUS_INACTIVE);
    }

    public function test_staff_routes_enforce_tenant_hierarchy_and_direct_request_authorization(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);
        /** @var User $owner */
        $owner = User::factory()->create();
        /** @var Tenant $tenant */
        $tenant = Tenant::factory()->create();
        $ownerMembership = Membership::factory()->create(['user_id' => $owner->getKey(), 'tenant_id' => $tenant->getKey(), 'role' => Membership::ROLE_OWNER]);
        /** @var User $manager */
        $manager = User::factory()->create();
        Membership::factory()->create(['user_id' => $manager->getKey(), 'tenant_id' => $tenant->getKey(), 'role' => Membership::ROLE_MANAGER]);
        /** @var User $cashier */
        $cashier = User::factory()->create();
        $cashierMembership = Membership::factory()->create(['user_id' => $cashier->getKey(), 'tenant_id' => $tenant->getKey(), 'role' => Membership::ROLE_CASHIER]);

        $session = ['current_tenant_id' => $tenant->getKey()];
        $this->actingAs($manager)->withSession($session)->get(route('tenant.staff.index'))->assertOk()->assertSee($cashier->email);
        $this->actingAs($manager)->withSession($session)->patch(route('tenant.staff.update', $cashierMembership), ['role' => Membership::ROLE_INVENTORY_STAFF, 'status' => Membership::STATUS_ACTIVE])->assertRedirect();
        /** @var Membership $updatedCashier */ $updatedCashier = $cashierMembership->fresh();
        $this->assertSame(Membership::ROLE_INVENTORY_STAFF, $updatedCashier->role);

        $this->actingAs($manager)->withSession($session)->patch(route('tenant.staff.update', $ownerMembership), ['role' => Membership::ROLE_MANAGER, 'status' => Membership::STATUS_ACTIVE])->assertSessionHasErrors('membership');
        $this->actingAs($cashier)->withSession($session)->get(route('tenant.staff.index'))->assertForbidden();

        /** @var Tenant $otherTenant */
        $otherTenant = Tenant::factory()->create();
        $otherMembership = Membership::factory()->create(['tenant_id' => $otherTenant->getKey(), 'role' => Membership::ROLE_CASHIER]);
        $this->actingAs($owner)->withSession($session)->patch(route('tenant.staff.update', $otherMembership), ['role' => Membership::ROLE_INVENTORY_STAFF, 'status' => Membership::STATUS_ACTIVE])->assertNotFound();
    }
}
