<?php

namespace Tests\Feature\Identity;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Modules\Identity\App\Actions\AcceptInvitationAction;
use Modules\Identity\App\Actions\CreateInvitationAction;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Models\Invitation;
use Modules\Identity\App\Models\Membership;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;
use Modules\Identity\App\Notifications\InvitationNotification;
use Tests\TestCase;

class InvitationLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_management_routes_are_tenant_isolated_and_queue_the_invitation_notification(): void
    {
        [$owner, $tenant] = $this->ownerAndTenant();
        /** @var Tenant $otherTenant */
        $otherTenant = Tenant::factory()->create();
        /** @var User $otherOwner */
        $otherOwner = User::factory()->create();
        Membership::factory()->create(['user_id' => $otherOwner->getKey(), 'tenant_id' => $otherTenant->getKey()]);
        $otherInvitation = app(CreateInvitationAction::class)->execute($otherOwner, $otherTenant, 'other@example.com');
        Notification::fake();

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->post('/tenant/invitations', ['_token' => csrf_token(), 'email' => 'staff@example.com'])
            ->assertRedirect();

        Notification::assertSentOnDemand(InvitationNotification::class);
        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->get('/tenant/invitations')
            ->assertOk()
            ->assertSee('staff@example.com')
            ->assertDontSee($otherInvitation->invitation->email);
    }

    public function test_resend_revokes_the_old_invitation_and_revoke_is_idempotent(): void
    {
        [$owner, $tenant] = $this->ownerAndTenant();
        $first = app(CreateInvitationAction::class)->execute($owner, $tenant, 'staff@example.com');
        Notification::fake();

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->post('/tenant/invitations/'.$first->invitation->getKey().'/resend', ['_token' => csrf_token()])
            ->assertRedirect();

        $this->assertSame(Invitation::STATUS_REVOKED, $first->invitation->fresh()->getAttribute('status'));
        $this->assertSame(2, Invitation::query()->count());
        Notification::assertSentOnDemand(InvitationNotification::class);

        /** @var Invitation $newInvitation */
        $newInvitation = Invitation::query()->where('status', Invitation::STATUS_PENDING)->firstOrFail();
        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->post('/tenant/invitations/'.$newInvitation->getKey().'/revoke', ['_token' => csrf_token()])
            ->assertRedirect();
        $this->assertSame(Invitation::STATUS_REVOKED, $newInvitation->fresh()->getAttribute('status'));

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->post('/tenant/invitations/'.$newInvitation->getKey().'/revoke', ['_token' => csrf_token()])
            ->assertRedirect();
    }

    public function test_switching_clears_the_current_request_context_and_updates_the_session(): void
    {
        [$owner, $tenant] = $this->ownerAndTenant();
        /** @var Tenant $secondTenant */
        $secondTenant = Tenant::factory()->create();
        /** @var Membership $secondMembership */
        $secondMembership = Membership::factory()->create([
            'user_id' => $owner->getKey(),
            'tenant_id' => $secondTenant->getKey(),
            'role' => Membership::ROLE_MANAGER,
        ]);
        /** @var Membership $firstMembership */
        $firstMembership = Membership::query()->where('tenant_id', $tenant->getKey())->firstOrFail();
        app(TenantContext::class)->set($tenant, $firstMembership);

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->post('/tenant/select/'.$secondTenant->getKey(), ['_token' => csrf_token()])
            ->assertRedirect('/home')
            ->assertSessionHas('current_tenant_id', $secondTenant->getKey());

        $this->assertFalse(app(TenantContext::class)->hasTenant());
        $this->assertTrue($secondMembership->isActive());
    }

    public function test_revoked_invitation_cannot_be_accepted(): void
    {
        [$owner, $tenant] = $this->ownerAndTenant();
        $result = app(CreateInvitationAction::class)->execute($owner, $tenant, 'staff@example.com');
        $result->invitation->update(['status' => Invitation::STATUS_REVOKED, 'revoked_at' => now()]);

        /** @var User $recipient */
        $recipient = User::factory()->create(['email' => 'staff@example.com']);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(AcceptInvitationAction::class)->execute($result->invitation, $result->plainToken, $recipient);
    }

    /** @return array{0: User, 1: Tenant} */
    private function ownerAndTenant(): array
    {
        /** @var User $owner */
        $owner = User::factory()->create();
        /** @var Tenant $tenant */
        $tenant = Tenant::factory()->create();
        Membership::factory()->create(['user_id' => $owner->getKey(), 'tenant_id' => $tenant->getKey(), 'role' => Membership::ROLE_OWNER]);

        return [$owner, $tenant];
    }
}
