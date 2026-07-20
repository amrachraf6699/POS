<?php

namespace Tests\Feature\Identity;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Identity\App\Models\Membership;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;
use Tests\TestCase;

class UiLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_central_auth_and_selection_pages_do_not_render_the_tenant_sidebar(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertDontSee('product-sidebar', false)
            ->assertSee('lang="ar"', false)
            ->assertSee('dir="rtl"', false);

        $this->get('/register')
            ->assertOk()
            ->assertDontSee('product-sidebar', false);

        /** @var User $user */
        $user = User::factory()->create();
        /** @var Tenant $tenant */
        $tenant = Tenant::factory()->create();
        Membership::factory()->create([
            'user_id' => $user->getKey(),
            'tenant_id' => $tenant->getKey(),
        ]);

        $this->actingAs($user)->get('/tenant/select')
            ->assertOk()
            ->assertDontSee('product-sidebar', false)
            ->assertSee($tenant->name);
    }

    public function test_tenant_shell_places_switcher_in_the_navbar_and_lists_only_accessible_tenants(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Tenant $currentTenant */
        $currentTenant = Tenant::factory()->create(['name' => 'Current Business']);
        /** @var Tenant $otherTenant */
        $otherTenant = Tenant::factory()->create(['name' => 'Other Business']);
        /** @var Tenant $unrelatedTenant */
        $unrelatedTenant = Tenant::factory()->create(['name' => 'Hidden Business']);

        Membership::factory()->create(['user_id' => $user->getKey(), 'tenant_id' => $currentTenant->getKey()]);
        Membership::factory()->create(['user_id' => $user->getKey(), 'tenant_id' => $otherTenant->getKey()]);
        $response = $this->actingAs($user)
            ->withSession(['current_tenant_id' => $currentTenant->getKey()])
            ->get('/tenant/dashboard');

        $response->assertOk()
            ->assertSee('data-tenant-switcher-open', false)
            ->assertSee('id="tenant-switcher"', false)
            ->assertSee('role="dialog"', false)
            ->assertSee(route('tenant.selection.store', $currentTenant), false)
            ->assertSee($currentTenant->name)
            ->assertSee($otherTenant->name)
            ->assertDontSee($unrelatedTenant->name);
    }
}
