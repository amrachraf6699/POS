<?php

namespace Modules\Identity\App\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Identity\App\Data\InvitationCreationResult;
use Modules\Identity\App\Domain\Invitations\InvitationAuthorization;
use Modules\Identity\App\Domain\Invitations\InvitationTokenService;
use Modules\Identity\App\Models\Invitation;
use Modules\Identity\App\Models\Membership;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;

final class CreateInvitationAction
{
    public function __construct(
        private readonly InvitationAuthorization $authorization,
        private readonly InvitationTokenService $tokens,
    ) {}

    public function execute(User $inviter, Tenant $tenant, string $email): InvitationCreationResult
    {
        $email = Str::lower(trim($email));

        if (! $this->authorization->canManage($inviter, $tenant)) {
            throw ValidationException::withMessages(['email' => 'You are not authorized to invite users to this tenant.']);
        }

        $existingUser = User::query()->where('email', $email)->first();

        if ($existingUser !== null && Membership::query()
            ->where('tenant_id', $tenant->getKey())
            ->where('user_id', $existingUser->getKey())
            ->where('status', Membership::STATUS_ACTIVE)
            ->exists()) {
            throw ValidationException::withMessages(['email' => 'This user is already a member of the tenant.']);
        }

        return DB::transaction(function () use ($inviter, $tenant, $email): InvitationCreationResult {
            Invitation::query()
                ->where('tenant_id', $tenant->getKey())
                ->where('email', $email)
                ->where('status', Invitation::STATUS_PENDING)
                ->update([
                    'status' => Invitation::STATUS_REVOKED,
                    'revoked_at' => now(),
                ]);

            $token = $this->tokens->issue();

            /** @var Invitation $invitation */
            $invitation = Invitation::query()->create([
                'tenant_id' => $tenant->getKey(),
                'invited_by' => $inviter->getKey(),
                'email' => $email,
                'role' => Invitation::ROLE_MANAGER,
                'token_hash' => $token['hash'],
                'status' => Invitation::STATUS_PENDING,
                'expires_at' => now()->addHours((int) config('identity.invitation_lifetime_hours', 72)),
            ]);

            return new InvitationCreationResult($invitation, $token['plain']);
        });
    }
}
