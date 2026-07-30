<?php

namespace Modules\Inventory\App\Domain\Data;

use Modules\Inventory\App\Models\InventoryTransfer;

final class InventoryTransferResult
{
    public function __construct(public readonly InventoryTransfer $transfer, public readonly bool $wasIdempotent) {}
}
