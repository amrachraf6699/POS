<?php

namespace Modules\Identity\App\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Identity\App\Domain\Invitations\InvitationTokenService;
use Modules\Identity\App\Models\Invitation;
use Modules\Identity\App\Models\Membership;
use Modules\Identity\App\Models\User;

final class AcceptInvitationAction
{
    public function __construct(private readonly InvitationTokenService $tokens) {}

    public function execute(Invitation $invitation, string $plainToken, User $user): void
    {
        $this->assertEmailMatches($invitation, $user);

        DB::transaction(function () use ($invitation, $plainToken, $user): void {
            /** @var Invitation $invitation */
            /** @phpstan-ignore-next-line */
            $invitation = Invitation::query()->whereKey($invitation->getKey())->lockForUpdate()->firstOrFail();
            $this->assertUsable($invitation, $plainToken);

            $membership = Membership::query()
                ->where('tenant_id', $invitation->tenant_id)
                ->where('user_id', $user->getKey())
                ->lockForUpdate()
                ->first();

            if ($membership !== null && $membership->isActive()) {
                throw ValidationException::withMessages(['invitation' => 'You are already a member of this tenant.']);
            }

            if ($membership === null) {
                Membership::query()->create([
                    'tenant_id' => $invitation->tenant_id,
                    'user_id' => $user->getKey(),
                    'role' => Membership::ROLE_MANAGER,
                    'status' => Membership::STATUS_ACTIVE,
                ]);
            } else {
                $membership->update([
                    'role' => Membership::ROLE_MANAGER,
                    'status' => Membership::STATUS_ACTIVE,
                ]);
            }

            $invitation->update([
                'status' => Invitation::STATUS_ACCEPTED,
                'accepted_at' => now(),
            ]);
        });

        Auth::login($user);
        session(['current_tenant_id' => $invitation->tenant_id]);
    }

    public function executeForNewUser(Invitation $invitation, string $plainToken, string $name, string $password): User
    {
        return DB::transaction(function () use ($invitation, $plainToken, $name, $password): User {
            /** @var Invitation $invitation */
            /** @phpstan-ignore-next-line */
            $invitation = Invitation::query()->whereKey($invitation->getKey())->lockForUpdate()->firstOrFail();
            $this->assertUsable($invitation, $plainToken);

            if (User::query()->where('email', $invitation->email)->exists()) {
                throw ValidationException::withMessages(['email' => 'This email already has an account. Please sign in first.']);
            }

            /** @var User $user */
            $user = User::query()->create([
                'name' => $name,
                'email' => $invitation->email,
                'password' => $password,
                'status' => User::STATUS_ACTIVE,
            ]);

            Membership::query()->create([
                'tenant_id' => $invitation->tenant_id,
                'user_id' => $user->getKey(),
                'role' => Membership::ROLE_MANAGER,
                'status' => Membership::STATUS_ACTIVE,
            ]);

            $invitation->update([
                'status' => Invitation::STATUS_ACCEPTED,
                'accepted_at' => now(),
            ]);

            Auth::login($user);
            session(['current_tenant_id' => $invitation->tenant_id]);

            return $user;
        });
    }

    private function assertEmailMatches(Invitation $invitation, User $user): void
    {
        if (! $user->isActive() || strtolower($user->email) !== strtolower($invitation->email)) {
            throw ValidationException::withMessages(['invitation' => 'This invitation does not belong to the current account.']);
        }
    }

    public function assertUsable(Invitation $invitation, string $plainToken): void
    {
        if (! $invitation->isPending() || $invitation->isExpired() || ! $invitation->tenant->isActive() || ! $this->tokens->matches($plainToken, $invitation->token_hash)) {
            throw ValidationException::withMessages(['invitation' => 'This invitation is invalid, expired, or no longer available.']);
        }
    }
}
