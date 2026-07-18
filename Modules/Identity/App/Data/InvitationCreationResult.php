<?php

namespace Modules\Identity\App\Data;

use Modules\Identity\App\Models\Invitation;

final class InvitationCreationResult
{
    public function __construct(
        public Invitation $invitation,
        public string $plainToken,
    ) {}
}
