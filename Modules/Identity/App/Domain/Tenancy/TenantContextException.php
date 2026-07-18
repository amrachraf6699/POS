<?php

namespace Modules\Identity\App\Domain\Tenancy;

use RuntimeException;

class TenantContextException extends RuntimeException
{
    // Context failures are intentionally distinct from generic runtime failures.
}
