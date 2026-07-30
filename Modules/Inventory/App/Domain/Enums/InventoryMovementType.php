<?php

namespace Modules\Inventory\App\Domain\Enums;

enum InventoryMovementType: string
{
    case Opening = 'opening';
    case AdjustmentIn = 'adjustment_in';
    case AdjustmentOut = 'adjustment_out';
    case SaleOut = 'sale_out';
    case ReturnIn = 'return_in';
    case TransferIn = 'transfer_in';
    case TransferOut = 'transfer_out';

    public function direction(): int
    {
        return match ($this) {
            self::Opening, self::AdjustmentIn, self::ReturnIn, self::TransferIn => 1,
            self::AdjustmentOut, self::SaleOut, self::TransferOut => -1,
        };
    }

    public function signedQuantityDelta(int $quantity): int
    {
        return $this->direction() * $quantity;
    }
}
