<?php

namespace Modules\Identity\App\Data;

final class RegisterOwnerData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public string $tenantName,
        public ?string $tenantSlug = null,
    ) {}
}
