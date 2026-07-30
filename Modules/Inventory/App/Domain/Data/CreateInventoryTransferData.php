<?php

namespace Modules\Inventory\App\Domain\Data;

use Modules\Inventory\App\Domain\Exceptions\InventoryTransferException;

final class CreateInventoryTransferData
{
    /** @var array<int, array{product_id: int, quantity: int}> */
    public readonly array $items;

    /** @param array<int, array{product_id: int, quantity: int}> $items */
    public function __construct(
        public readonly int $sourceBranchId,
        public readonly int $destinationBranchId,
        public readonly string $reason,
        array $items,
        public readonly string $idempotencyKey,
    ) {
        $this->items = $items;
        if ($this->sourceBranchId < 1 || $this->destinationBranchId < 1 || $this->sourceBranchId === $this->destinationBranchId) {
            throw new InventoryTransferException('A transfer requires two distinct active branches.');
        }
        if (trim($this->reason) === '' || mb_strlen(trim($this->reason)) > 2000 || $items === [] || trim($this->idempotencyKey) === '' || mb_strlen($this->idempotencyKey) > 120) {
            throw new InventoryTransferException('A transfer requires a reason, at least one item, and a valid idempotency key.');
        }
        $productIds = [];
        foreach ($items as $item) {
            if ($item['product_id'] < 1 || $item['quantity'] < 1 || in_array($item['product_id'], $productIds, true)) {
                throw new InventoryTransferException('Transfer items require unique products and positive integer quantities.');
            }
            $productIds[] = $item['product_id'];
        }
    }
}
