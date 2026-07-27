<?php

namespace Tests\Feature\Identity;

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
        $user = User::factory()->create();
        $first = Tenant::factory()->create();
        $second = Tenant::factory()->create();
        Membership::factory()->create(['user_id' => $user->id, 'tenant_id' => $first->id, 'role' => Membership::ROLE_CASHIER]);
        Membership::factory()->create(['user_id' => $user->id, 'tenant_id' => $second->id, 'role' => Membership::ROLE_INVENTORY_STAFF]);

        $authorization = app(TenantAuthorization::class);
        $this->assertFalse($authorization->allows($user, $first, 'inventory.adjust'));
        $this->assertTrue($authorization->allows($user, $second, 'inventory.adjust'));
        $this->assertFalse($authorization->allows($user, $first, 'sales.refund'));
    }

    public function test_final_active_owner_cannot_be_demoted_or_deactivated(): void
    {
        $owner = User::factory()->create();
        $tenant = Tenant::factory()->create();
        $membership = Membership::factory()->create(['user_id' => $owner->id, 'tenant_id' => $tenant->id, 'role' => Membership::ROLE_OWNER]);

        $this->expectException(ValidationException::class);
        app(UpdateMembershipAction::class)->execute($owner, $tenant, $membership, Membership::ROLE_MANAGER, Membership::STATUS_INACTIVE);
    }
}
