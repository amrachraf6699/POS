<?php

namespace Tests\Feature\Identity;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Modules\Identity\App\Actions\RegisterOwnerAction;
use Modules\Identity\App\Data\RegisterOwnerData;
use Modules\Identity\App\Models\Membership;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_page_is_arabic_and_rtl(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('lang="ar"', false)
            ->assertSee('dir="rtl"', false)
            ->assertSee('إنشاء حساب المالك');
    }

    public function test_registration_creates_and_authenticates_an_owner_atomically(): void
    {
        $response = $this->post('/register', [
            'name' => 'Amr Achraf',
            'email' => 'owner@example.com',
            'password' => 'password-123',
            'password_confirmation' => 'password-123',
            'tenant_name' => 'Cairo Store',
        ]);

        $response->assertRedirect('/home');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'owner@example.com', 'status' => User::STATUS_ACTIVE]);
        $this->assertDatabaseHas('tenants', ['name' => 'Cairo Store', 'slug' => 'cairo-store', 'status' => Tenant::STATUS_ACTIVE]);
        $this->assertDatabaseHas('memberships', ['role' => Membership::ROLE_OWNER, 'status' => Membership::STATUS_ACTIVE]);
    }

    public function test_generated_slug_collision_is_suffixed_deterministically(): void
    {
        Tenant::factory()->create(['name' => 'Cairo Store', 'slug' => 'cairo-store']);

        $result = app(RegisterOwnerAction::class)->execute(new RegisterOwnerData(
            name: 'Second Owner',
            email: 'second@example.com',
            password: 'password-123',
            tenantName: 'Cairo Store',
        ));

        $this->assertSame('cairo-store-2', $result->tenant->slug);
    }

    public function test_duplicate_explicit_slug_rolls_back_registration(): void
    {
        Tenant::factory()->create(['slug' => 'fixed-store']);

        try {
            app(RegisterOwnerAction::class)->execute(new RegisterOwnerData(
                name: 'Owner',
                email: 'owner@example.com',
                password: 'password-123',
                tenantName: 'New Store',
                tenantSlug: 'fixed-store',
            ));
            $this->fail('A duplicate explicit slug must be rejected.');
        } catch (ValidationException) {
            $this->assertDatabaseMissing('users', ['email' => 'owner@example.com']);
        }

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('memberships', 0);
    }

    public function test_inactive_users_memberships_suspended_tenants_and_deleted_tenants_are_inaccessible(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Tenant $activeTenant */
        $activeTenant = Tenant::factory()->create();
        /** @var Membership $membership */
        $membership = Membership::factory()->create(['user_id' => $user->id, 'tenant_id' => $activeTenant->id]);

        $this->assertCount(1, $user->accessibleTenants()->get());

        $user->update(['status' => User::STATUS_INACTIVE]);
        $this->assertCount(0, $user->accessibleTenants()->get());

        $membership->update(['status' => Membership::STATUS_INACTIVE]);
        $user->update(['status' => User::STATUS_ACTIVE]);
        $this->assertCount(0, $user->accessibleTenants()->get());

        $membership->update(['status' => Membership::STATUS_ACTIVE]);
        $activeTenant->update(['status' => Tenant::STATUS_SUSPENDED]);
        $this->assertCount(0, $user->accessibleTenants()->get());

        $activeTenant->delete();
        $this->assertCount(0, $user->accessibleTenants()->get());
    }

    public function test_membership_pair_is_unique(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Tenant $tenant */
        $tenant = Tenant::factory()->create();
        Membership::factory()->create(['user_id' => $user->id, 'tenant_id' => $tenant->id]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Membership::factory()->create(['user_id' => $user->id, 'tenant_id' => $tenant->id]);
    }
}
