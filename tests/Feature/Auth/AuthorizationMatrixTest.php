<?php

namespace Tests\Feature\Auth;

use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Modules\Identity\App\Actions\AcceptInvitationAction;
use Modules\Identity\App\Actions\CreateInvitationAction;
use Modules\Identity\App\Domain\Invitations\InvitationTokenService;
use Modules\Identity\App\Models\Invitation;
use Modules\Identity\App\Models\Membership;
use Modules\Identity\App\Models\User;
use Modules\Identity\App\Notifications\InvitationNotification;
use Tests\Support\Tenancy\TenantIsolationTestCase;

class AuthorizationMatrixTest extends TenantIsolationTestCase
{
    public function test_active_manager_can_create_resend_and_revoke_invitations_over_http(): void
    {
        [$owner, $tenant] = $this->makeMembership();
        /** @var User $manager */
        $manager = User::factory()->create();
        Membership::factory()->create([
            'user_id' => $manager->getKey(),
            'tenant_id' => $tenant->getKey(),
            'role' => Membership::ROLE_MANAGER,
        ]);
        $this->assertTrue($owner->isActive());
        Notification::fake();

        $this->actingAs($manager)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->post('/tenant/invitations', ['_token' => csrf_token(), 'email' => 'manager-created@example.com'])
            ->assertRedirect();

        $invitation = Invitation::query()->where('email', 'manager-created@example.com')->firstOrFail();
        $this->actingAs($manager)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->post('/tenant/invitations/'.$invitation->getKey().'/resend', ['_token' => csrf_token()])
            ->assertRedirect();
        $replacement = Invitation::query()->where('status', Invitation::STATUS_PENDING)->where('email', 'manager-created@example.com')->latest()->firstOrFail();
        $this->actingAs($manager)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->post('/tenant/invitations/'.$replacement->getKey().'/revoke', ['_token' => csrf_token()])
            ->assertRedirect();
        $this->assertSame(Invitation::STATUS_REVOKED, $replacement->fresh()->getAttribute('status'));
        Notification::assertSentOnDemand(InvitationNotification::class);
    }

    public function test_inactive_membership_cannot_manage_invitations(): void
    {
        [$user, $tenant] = $this->makeMembership(membershipStatus: Membership::STATUS_INACTIVE);

        $this->actingAs($user)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->post('/tenant/invitations', ['_token' => csrf_token(), 'email' => 'blocked@example.com'])
            ->assertForbidden();
        $this->assertDatabaseCount('invitations', 0);
    }

    public function test_cross_tenant_resend_and_revoke_cannot_mutate_an_invitation(): void
    {
        [$ownerA, $tenantA] = $this->makeMembership();
        [$ownerB, $tenantB] = $this->makeMembership();
        $invitation = app(CreateInvitationAction::class)->execute($ownerB, $tenantB, 'tenant-b@example.com')->invitation;
        Notification::fake();

        $this->actingAs($ownerA)->withSession(['current_tenant_id' => $tenantA->getKey()])
            ->post('/tenant/invitations/'.$invitation->getKey().'/resend', ['_token' => csrf_token()])
            ->assertNotFound();
        $this->actingAs($ownerA)->withSession(['current_tenant_id' => $tenantA->getKey()])
            ->post('/tenant/invitations/'.$invitation->getKey().'/revoke', ['_token' => csrf_token()])
            ->assertNotFound();

        $this->assertSame(Invitation::STATUS_PENDING, $invitation->fresh()->getAttribute('status'));
        $this->assertSame(1, Invitation::query()->count());
    }

    public function test_replayed_or_invalid_acceptance_has_no_side_effects(): void
    {
        [$owner, $tenant] = $this->makeMembership();
        $result = app(CreateInvitationAction::class)->execute($owner, $tenant, 'replay@example.com');
        /** @var User $recipient */
        $recipient = User::factory()->create(['email' => 'replay@example.com']);
        app(AcceptInvitationAction::class)->execute($result->invitation, $result->plainToken, $recipient);

        try {
            app(AcceptInvitationAction::class)->execute($result->invitation, $result->plainToken, $recipient);
            $this->fail('A replayed invitation must be rejected.');
        } catch (ValidationException) {
            $this->assertSame(Invitation::STATUS_ACCEPTED, $result->invitation->fresh()->getAttribute('status'));
        }

        $invalid = app(CreateInvitationAction::class)->execute($owner, $tenant, 'invalid@example.com');
        /** @var User $invalidRecipient */
        $invalidRecipient = User::factory()->create(['email' => 'invalid@example.com']);
        $this->expectException(ValidationException::class);
        app(AcceptInvitationAction::class)->execute($invalid->invitation, 'invalid-token', $invalidRecipient);
        $this->assertSame(Invitation::STATUS_PENDING, $invalid->invitation->fresh()->getAttribute('status'));
    }

    public function test_existing_active_membership_rejects_acceptance_without_side_effects(): void
    {
        [$owner, $tenant] = $this->makeMembership();
        /** @var User $member */
        $member = User::factory()->create(['email' => 'member@example.com']);
        Membership::factory()->create(['user_id' => $member->getKey(), 'tenant_id' => $tenant->getKey(), 'role' => Membership::ROLE_MANAGER]);
        $token = app(InvitationTokenService::class)->issue();
        $invitation = Invitation::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'invited_by' => $owner->getKey(),
            'email' => $member->email,
            'token_hash' => $token['hash'],
        ]);

        $this->expectException(ValidationException::class);
        app(AcceptInvitationAction::class)->execute($invitation, $token['plain'], $member);
        $this->assertSame(Invitation::STATUS_PENDING, $invitation->fresh()->getAttribute('status'));
        $this->assertSame(2, Membership::query()->count());
    }
}
