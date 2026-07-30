<?php

namespace Modules\Inventory\App\Domain\Enums;

use Modules\Inventory\App\Domain\Exceptions\StockAdjustmentException;

enum StockAdjustmentType: string
{
    case Opening = 'opening';
    case AdjustmentIn = 'adjustment_in';
    case AdjustmentOut = 'adjustment_out';

    public function movementType(): InventoryMovementType
    {
        return match ($this) {
            self::Opening => InventoryMovementType::Opening,
            self::AdjustmentIn => InventoryMovementType::AdjustmentIn,
            self::AdjustmentOut => InventoryMovementType::AdjustmentOut,
        };
    }

    public static function fromInput(string $type): self
    {
        try {
            return self::from($type);
        } catch (\ValueError) {
            throw new StockAdjustmentException('The stock adjustment type is invalid.');
        }
    }
}
