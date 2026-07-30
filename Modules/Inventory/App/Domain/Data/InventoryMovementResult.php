<?php

namespace Modules\Inventory\App\Domain\Data;

use Modules\Inventory\App\Models\InventoryMovement;

final class InventoryMovementResult
{
    public function __construct(
        public readonly int $movementId,
        public readonly int $branchId,
        public readonly int $productId,
        public readonly int $quantityDelta,
        public readonly int $balanceAfter,
        public readonly bool $wasIdempotent,
    ) {}

    public static function fromMovement(InventoryMovement $movement, bool $wasIdempotent): self
    {
        return new self(
            (int) $movement->getKey(),
            (int) $movement->branch_id,
            (int) $movement->product_id,
            (int) $movement->quantity_delta,
            (int) $movement->balance_after,
            $wasIdempotent,
        );
    }
}
