<?php

namespace Modules\Identity\App\Domain\Invitations;

final class InvitationTokenService
{
    /** @return array{plain: string, hash: string} */
    public function issue(): array
    {
        $plain = bin2hex(random_bytes(32));

        return [
            'plain' => $plain,
            'hash' => $this->hash($plain),
        ];
    }

    public function hash(string $plain): string
    {
        return hash('sha256', $plain);
    }

    public function matches(string $plain, string $hash): bool
    {
        return hash_equals($hash, $this->hash($plain));
    }
}
