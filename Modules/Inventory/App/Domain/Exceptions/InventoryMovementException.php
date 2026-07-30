<?php

namespace Modules\Inventory\App\Domain\Exceptions;

use DomainException;

final class InventoryMovementException extends DomainException
{
    // Domain boundary failures are intentionally exposed as one stable exception type.
}
