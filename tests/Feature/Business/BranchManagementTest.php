<?php

namespace Tests\Feature\Business;

use App\Http\Middleware\VerifyCsrfToken;
use Modules\Business\App\Models\Branch;
use Modules\Business\App\Models\BranchAssignment;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Models\Membership;
use Modules\Identity\App\Models\User;
use Tests\Support\Tenancy\TenantIsolationTestCase;

class BranchManagementTest extends TenantIsolationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    public function test_owner_can_create_update_and_view_an_arabic_branch_screen(): void
    {
        [$owner, $tenant, $membership] = $this->makeMembership();

        $response = $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->get('/tenant/branches/create');

        $response->assertOk()->assertSee('dir="rtl"', false)->assertSee('إضافة فرع');

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->post('/tenant/branches', [
                'name' => 'الفرع الرئيسي', 'code' => ' main-01 ', 'city' => 'Cairo',
                'country_code' => 'eg', 'timezone' => 'Africa/Cairo', '_token' => csrf_token(),
            ])->assertRedirect();

        $this->assertDatabaseHas('branches', [
            'tenant_id' => $tenant->getKey(), 'name' => 'الفرع الرئيسي', 'code' => 'MAIN-01',
            'status' => Branch::STATUS_ACTIVE,
        ]);

        app(TenantContext::class)->set($tenant, $membership);
        $branch = Branch::query()->firstOrFail();
        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->put('/tenant/branches/'.$branch->getKey(), [
                'name' => 'الفرع المحدث', 'code' => 'MAIN-02', 'timezone' => 'Africa/Cairo', 'country_code' => 'EG',
            ])->assertRedirect();

        /** @var Branch $updated */
        $updated = $branch->fresh();
        $this->assertSame('الفرع المحدث', $updated->name);
    }

    public function test_codes_are_unique_per_tenant_but_can_repeat_across_tenants(): void
    {
        [$owner, $tenant] = $this->makeMembership();
        $session = ['current_tenant_id' => $tenant->getKey()];
        $payload = ['name' => 'First', 'code' => 'MAIN', 'timezone' => 'Africa/Cairo', 'country_code' => 'EG', '_token' => csrf_token()];

        $this->actingAs($owner)->withSession($session)->post('/tenant/branches', $payload)->assertRedirect();
        $this->actingAs($owner)->withSession($session)->post('/tenant/branches', $payload)->assertSessionHasErrors('code');

        [$secondOwner, $secondTenant] = $this->makeMembership();
        $this->actingAs($secondOwner)->withSession(['current_tenant_id' => $secondTenant->getKey()])
            ->post('/tenant/branches', $payload)->assertRedirect();
        $this->assertSame(2, Branch::query()->withoutGlobalScopes()->where('code', 'MAIN')->count());
    }

    public function test_manager_access_depends_on_active_assignment_and_inactive_users_cannot_manage(): void
    {
        [$owner, $tenant, $ownerMembership] = $this->makeMembership();
        /** @var User $manager */
        $manager = User::factory()->create();
        Membership::factory()->create([
            'tenant_id' => $tenant->getKey(), 'user_id' => $manager->getKey(), 'role' => Membership::ROLE_MANAGER,
        ]);
        app(TenantContext::class)->set($tenant, $ownerMembership);
        $branch = Branch::factory()->create(['name' => 'Managed', 'code' => 'MANAGED']);
        /** @phpstan-ignore-next-line dynamic Eloquent scope */
        $this->assertCount(1, Branch::query()->accessibleTo($owner)->get());
        /** @phpstan-ignore-next-line dynamic Eloquent scope */
        $this->assertCount(0, Branch::query()->accessibleTo($manager)->get());

        $this->actingAs($manager)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->post('/tenant/branches/'.$branch->getKey().'/assignments', ['user_id' => $manager->getKey(), '_token' => csrf_token()])
            ->assertRedirect();

        app(TenantContext::class)->set($tenant, Membership::query()->where('user_id', $manager->getKey())->firstOrFail());
        /** @phpstan-ignore-next-line dynamic Eloquent scope */
        $this->assertCount(1, Branch::query()->accessibleTo($manager)->get());

        BranchAssignment::query()->where('user_id', $manager->getKey())->update(['status' => BranchAssignment::STATUS_INACTIVE]);
        /** @phpstan-ignore-next-line dynamic Eloquent scope */
        $this->assertCount(0, Branch::query()->accessibleTo($manager)->get());

        $manager->update(['status' => User::STATUS_INACTIVE]);
        $this->actingAs($manager)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->get('/tenant/branches')->assertForbidden();

        $this->assertTrue($owner->isActive());
    }

    public function test_cross_tenant_branch_binding_returns_not_found(): void
    {
        [$firstOwner, $firstTenant, $firstMembership] = $this->makeMembership();
        [$secondOwner, $secondTenant, $secondMembership] = $this->makeMembership();
        app(TenantContext::class)->set($firstTenant, $firstMembership);
        $branch = Branch::factory()->create(['code' => 'PRIVATE']);
        app(TenantContext::class)->set($secondTenant, $secondMembership);

        $this->actingAs($secondOwner)->withSession(['current_tenant_id' => $secondTenant->getKey()])
            ->get('/tenant/branches/'.$branch->getKey().'/edit')->assertNotFound();
    }

    public function test_final_active_branch_cannot_be_deactivated_and_history_is_preserved(): void
    {
        [$owner, $tenant, $membership] = $this->makeMembership();
        app(TenantContext::class)->set($tenant, $membership);
        $branch = Branch::factory()->create(['code' => 'ONLY']);

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->post('/tenant/branches/'.$branch->getKey().'/deactivate', ['_token' => csrf_token()])->assertSessionHasErrors('branch');
        /** @var Branch $current */
        $current = $branch->fresh();
        $this->assertSame(Branch::STATUS_ACTIVE, $current->status);

        $second = Branch::factory()->create(['code' => 'SECOND']);
        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->post('/tenant/branches/'.$second->getKey().'/deactivate', ['_token' => csrf_token()])->assertRedirect();
        $this->assertDatabaseHas('branches', ['id' => $second->getKey(), 'status' => Branch::STATUS_INACTIVE]);
    }

    public function test_inactive_branch_cannot_receive_an_assignment(): void
    {
        [$owner, $tenant, $membership] = $this->makeMembership();
        /** @var User $manager */
        $manager = User::factory()->create();
        Membership::factory()->create(['tenant_id' => $tenant->getKey(), 'user_id' => $manager->getKey(), 'role' => Membership::ROLE_MANAGER]);
        app(TenantContext::class)->set($tenant, $membership);
        $branch = Branch::factory()->create(['status' => Branch::STATUS_INACTIVE]);

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->post('/tenant/branches/'.$branch->getKey().'/assignments', ['user_id' => $manager->getKey(), '_token' => csrf_token()])
            ->assertSessionHasErrors('branch');
        $this->assertDatabaseMissing('branch_user', ['branch_id' => $branch->getKey(), 'user_id' => $manager->getKey()]);
    }
}
