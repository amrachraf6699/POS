<?php

namespace Modules\Inventory\App\Domain\Data;

use Modules\Inventory\App\Domain\Enums\InventoryMovementType;
use Modules\Inventory\App\Domain\Exceptions\InventoryMovementException;

final class RecordInventoryMovementData
{
    public function __construct(
        public readonly int $branchId,
        public readonly int $productId,
        public readonly InventoryMovementType $type,
        public readonly int $quantity,
        public readonly string $idempotencyKey,
        public readonly ?string $sourceType = null,
        public readonly ?string $sourceId = null,
    ) {
        if ($this->branchId < 1 || $this->productId < 1) {
            throw new InventoryMovementException('A branch and product are required for an inventory movement.');
        }

        if ($this->quantity < 1) {
            throw new InventoryMovementException('Inventory movement quantities must be positive integers.');
        }

        if (trim($this->idempotencyKey) === '' || mb_strlen($this->idempotencyKey) > 191) {
            throw new InventoryMovementException('An inventory movement requires an idempotency key of at most 191 characters.');
        }

        if (($this->sourceType === null) !== ($this->sourceId === null)) {
            throw new InventoryMovementException('An inventory source type and source ID must be supplied together.');
        }

        if (($this->sourceType !== null && (trim($this->sourceType) === '' || mb_strlen($this->sourceType) > 100))
            || ($this->sourceId !== null && (trim($this->sourceId) === '' || mb_strlen($this->sourceId) > 191))) {
            throw new InventoryMovementException('The inventory source reference is invalid.');
        }
    }
}
