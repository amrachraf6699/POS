<?php

namespace Tests\Feature\Identity;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Modules\Identity\App\Actions\CreateInvitationAction;
use Modules\Identity\App\Models\Invitation;
use Modules\Identity\App\Models\Membership;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;
use Tests\TestCase;

class InvitationAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_a_hashed_manager_invitation(): void
    {
        [$owner, $tenant] = $this->ownerAndTenant();

        $result = app(CreateInvitationAction::class)->execute($owner, $tenant, ' Staff@Example.COM ');

        $this->assertSame('staff@example.com', $result->invitation->email);
        $this->assertSame(Membership::ROLE_MANAGER, $result->invitation->role);
        $this->assertNotSame($result->plainToken, $result->invitation->token_hash);
        $this->assertSame(hash('sha256', $result->plainToken), $result->invitation->token_hash);
        $this->assertTrue($result->invitation->isPending());
    }

    public function test_manager_can_create_an_invitation_but_inactive_or_unauthorized_users_cannot(): void
    {
        [$owner, $tenant] = $this->ownerAndTenant();
        /** @var User $manager */
        $manager = User::factory()->create();
        Membership::factory()->create([
            'user_id' => $manager->getKey(),
            'tenant_id' => $tenant->getKey(),
            'role' => Membership::ROLE_MANAGER,
        ]);

        app(CreateInvitationAction::class)->execute($manager, $tenant, 'manager@example.com');

        /** @var User $outsider */
        $outsider = User::factory()->create();
        $this->expectException(ValidationException::class);
        app(CreateInvitationAction::class)->execute($outsider, $tenant, 'outsider@example.com');
    }

    public function test_existing_pending_invitation_is_revoked_before_replacement(): void
    {
        [$owner, $tenant] = $this->ownerAndTenant();

        $first = app(CreateInvitationAction::class)->execute($owner, $tenant, 'staff@example.com');
        $second = app(CreateInvitationAction::class)->execute($owner, $tenant, 'STAFF@example.com');

        $this->assertSame(Invitation::STATUS_REVOKED, $first->invitation->fresh()->status);
        $this->assertSame(Invitation::STATUS_PENDING, $second->invitation->fresh()->status);
        $this->assertSame(2, Invitation::query()->count());
    }

    /** @return array{0: User, 1: Tenant} */
    private function ownerAndTenant(): array
    {
        /** @var User $owner */
        $owner = User::factory()->create();
        /** @var Tenant $tenant */
        $tenant = Tenant::factory()->create();
        Membership::factory()->create([
            'user_id' => $owner->getKey(),
            'tenant_id' => $tenant->getKey(),
            'role' => Membership::ROLE_OWNER,
        ]);

        return [$owner, $tenant];
    }
}
