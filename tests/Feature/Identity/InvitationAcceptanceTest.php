<?php

namespace Tests\Feature\Identity;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Modules\Identity\App\Actions\CreateInvitationAction;
use Modules\Identity\App\Models\Invitation;
use Modules\Identity\App\Models\Membership;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;
use Tests\TestCase;

class InvitationAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_recipient_can_create_an_account_and_accept_once(): void
    {
        [$owner, $tenant] = $this->ownerAndTenant();
        $result = app(CreateInvitationAction::class)->execute($owner, $tenant, 'new-manager@example.com');
        $url = URL::temporarySignedRoute('invitations.accept', $result->invitation->expires_at, [
            'invitation' => $result->invitation,
            'token' => $result->plainToken,
        ]);

        $this->get($url)->assertOk()->assertSee('إنشاء حساب للانضمام');
        $this->post($url, [
            '_token' => csrf_token(),
            'name' => 'New Manager',
            'password' => 'password-123',
            'password_confirmation' => 'password-123',
        ])->assertRedirect('/home');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'new-manager@example.com']);
        $this->assertDatabaseHas('memberships', ['tenant_id' => $tenant->getKey(), 'role' => Membership::ROLE_MANAGER]);
        $this->assertDatabaseHas('invitations', ['id' => $result->invitation->getKey(), 'status' => Invitation::STATUS_ACCEPTED]);
        $this->assertSame($tenant->getKey(), session('current_tenant_id'));
    }

    public function test_existing_recipient_must_match_the_invited_account(): void
    {
        [$owner, $tenant] = $this->ownerAndTenant();
        /** @var User $recipient */
        $recipient = User::factory()->create(['email' => 'existing@example.com']);
        $result = app(CreateInvitationAction::class)->execute($owner, $tenant, $recipient->email);
        $url = URL::temporarySignedRoute('invitations.accept', $result->invitation->expires_at, [
            'invitation' => $result->invitation,
            'token' => $result->plainToken,
        ]);

        $this->actingAs($recipient)->get($url)->assertOk()->assertSee('دعوة جديدة');
        $this->actingAs($recipient)->post($url, ['_token' => csrf_token()])->assertRedirect('/home');
        $this->assertDatabaseHas('memberships', ['user_id' => $recipient->getKey(), 'tenant_id' => $tenant->getKey(), 'status' => Membership::STATUS_ACTIVE]);
    }

    public function test_expired_or_invalid_invitation_cannot_be_accepted(): void
    {
        [$owner, $tenant] = $this->ownerAndTenant();
        $result = app(CreateInvitationAction::class)->execute($owner, $tenant, 'expired@example.com');
        $result->invitation->update(['expires_at' => now()->subMinute()]);

        $url = URL::temporarySignedRoute('invitations.accept', now()->addMinute(), [
            'invitation' => $result->invitation,
            'token' => $result->plainToken,
        ]);

        $this->get($url)->assertSessionHasErrors('invitation');
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('memberships', 1);
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
