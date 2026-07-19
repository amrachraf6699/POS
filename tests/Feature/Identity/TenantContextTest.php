<?php

namespace Tests\Feature\Identity;

use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Domain\Tenancy\TenantContextException;
use Modules\Identity\App\Models\Membership;
use Modules\Identity\App\Models\Tenant;
use Tests\Support\Models\TenantNote;
use Tests\Support\Tenancy\TenantIsolationTestCase;

class TenantContextTest extends TenantIsolationTestCase
{
    public function test_context_service_is_fail_closed_without_context(): void
    {
        $context = app(TenantContext::class);

        $this->assertFalse($context->hasTenant());
        $this->expectException(TenantContextException::class);
        $context->id();
    }

    public function test_tenant_middleware_resolves_valid_session_context(): void
    {
        [$user, $tenant] = $this->makeMembership();

        $this->actingAs($user)->withSession(['current_tenant_id' => $tenant->id])
            ->get('/__test/tenant-notes/1')
            ->assertNotFound();

        $this->assertTrue(app(TenantContext::class)->hasTenant());
        $this->assertSame($tenant->id, app(TenantContext::class)->id());
    }

    public function test_missing_or_invalid_context_redirects_to_selection_and_clears_stale_state(): void
    {
        [$user, $tenant] = $this->makeMembership();

        $this->actingAs($user)->get('/__test/tenant-notes/1')
            ->assertRedirect(route('tenant.selection'))
            ->assertSessionHas('url.intended', url('/__test/tenant-notes/1'));

        $this->actingAs($user)->withSession(['current_tenant_id' => $tenant->id + 999])
            ->get('/__test/tenant-notes/1')
            ->assertRedirect(route('tenant.selection'))
            ->assertSessionMissing('current_tenant_id');
    }

    public function test_inaccessible_current_tenant_is_rejected_and_users_without_access_get_forbidden(): void
    {
        [$user, $tenant] = $this->makeMembership();
        $tenant->update(['status' => Tenant::STATUS_SUSPENDED]);

        $this->actingAs($user)->withSession(['current_tenant_id' => $tenant->id])
            ->get('/__test/tenant-notes/1')
            ->assertForbidden();

        $tenant->update(['status' => Tenant::STATUS_ACTIVE]);
        Membership::query()->where('tenant_id', $tenant->id)->update(['status' => Membership::STATUS_INACTIVE]);

        $this->actingAs($user)->get('/__test/tenant-notes/1')->assertForbidden();
    }

    public function test_selection_lists_only_accessible_tenants_and_rejects_guessed_tenant(): void
    {
        [$user, $tenant] = $this->makeMembership();
        /** @var Tenant $otherTenant */
        $otherTenant = Tenant::factory()->create();

        $this->actingAs($user)->get(route('tenant.selection'))
            ->assertOk()
            ->assertSee($tenant->getAttribute('name'))
            ->assertDontSee($otherTenant->getAttribute('name'));

        $this->actingAs($user)->post(route('tenant.selection.store', $otherTenant), ['_token' => csrf_token()])
            ->assertForbidden();

        $this->actingAs($user)->post(route('tenant.selection.store', $tenant), ['_token' => csrf_token()])
            ->assertRedirect('/home')
            ->assertSessionHas('current_tenant_id', $tenant->id);
    }

    public function test_tenant_owned_models_assign_and_enforce_context(): void
    {
        [$user, $tenant] = $this->makeMembership();
        $otherTenant = Tenant::factory()->create();

        app(TenantContext::class)->set($tenant, Membership::query()->where('tenant_id', $tenant->id)->firstOrFail());
        /** @var TenantNote $note */
        $note = TenantNote::query()->create(['label' => 'private']);

        $this->assertSame($tenant->id, $note->getAttribute('tenant_id'));
        $this->assertCount(1, TenantNote::query()->get());

        $this->expectException(TenantContextException::class);
        $wrongTenantNote = new TenantNote(['label' => 'wrong']);
        $wrongTenantNote->setAttribute('tenant_id', $otherTenant->getKey());
        $wrongTenantNote->save();
    }

    public function test_tenant_owned_reads_updates_deletes_and_bindings_cannot_cross_tenants(): void
    {
        [$user, $tenant] = $this->makeMembership();
        /** @var Tenant $otherTenant */
        $otherTenant = Tenant::factory()->create();
        /** @var Membership $otherMembership */
        $otherMembership = Membership::factory()->create(['user_id' => $user->id, 'tenant_id' => $otherTenant->getKey()]);

        app(TenantContext::class)->set($tenant, Membership::query()->where('tenant_id', $tenant->id)->firstOrFail());
        /** @var TenantNote $note */
        $note = TenantNote::query()->create(['label' => 'private']);

        app(TenantContext::class)->set($otherTenant, $otherMembership);
        $this->assertNull(TenantNote::query()->find($note->getKey()));
        $this->assertFalse(TenantNote::query()->whereKey($note->getKey())->update(['label' => 'changed']) > 0);
        $this->assertSame(0, TenantNote::query()->whereKey($note->getKey())->delete());

        $this->actingAs($user)->withSession(['current_tenant_id' => $otherTenant->getKey()])
            ->get('/__test/tenant-notes/'.$note->getKey())
            ->assertNotFound();
    }

    public function test_tracker_remains_a_central_route_without_tenant_context(): void
    {
        $this->get('/__tracker')->assertOk()->assertSee('AI agent workspace');
    }
}
