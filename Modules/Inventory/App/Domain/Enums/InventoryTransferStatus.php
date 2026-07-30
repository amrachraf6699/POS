<?php

namespace Modules\Inventory\App\Domain\Enums;

enum InventoryTransferStatus: string
{
    case Pending = 'pending';
    case Posted = 'posted';
    case Cancelled = 'cancelled';
}
