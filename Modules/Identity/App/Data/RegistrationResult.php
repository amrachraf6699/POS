<?php

namespace Modules\Identity\App\Data;

use Modules\Identity\App\Models\Membership;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;

final class RegistrationResult
{
    public function __construct(
        public User $user,
        public Tenant $tenant,
        public Membership $membership,
    ) {}
}
