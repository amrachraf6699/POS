<?php

namespace Modules\Inventory\App\Domain\Data;

use Modules\Inventory\App\Domain\Enums\StockAdjustmentType;
use Modules\Inventory\App\Domain\Exceptions\StockAdjustmentException;

final class PostStockAdjustmentData
{
    /** @var array<int, array<string, mixed>> */
    public readonly array $items;

    /** @param array<int, array<string, mixed>> $items */
    public function __construct(
        public readonly int $branchId,
        public readonly StockAdjustmentType $type,
        public readonly string $reason,
        array $items,
        public readonly string $idempotencyKey,
    ) {
        $this->items = $items;
        if ($this->branchId < 1) {
            throw new StockAdjustmentException('An active branch is required for a stock adjustment.');
        }

        if (trim($this->reason) === '' || mb_strlen(trim($this->reason)) > 2_000) {
            throw new StockAdjustmentException('A stock adjustment reason of at most 2,000 characters is required.');
        }

        if ($this->items === []) {
            throw new StockAdjustmentException('A stock adjustment requires at least one item.');
        }

        if (trim($this->idempotencyKey) === '' || mb_strlen($this->idempotencyKey) > 120) {
            throw new StockAdjustmentException('A stock adjustment requires an idempotency key of at most 120 characters.');
        }

        $productIds = [];
        foreach ($this->items as $item) {
            if (! isset($item['product_id'], $item['quantity']) || ! is_int($item['product_id']) || ! is_int($item['quantity'])
                || $item['product_id'] < 1 || $item['quantity'] < 1) {
                throw new StockAdjustmentException('Stock adjustment items require a product and a positive integer quantity.');
            }

            if (in_array($item['product_id'], $productIds, true)) {
                throw new StockAdjustmentException('A product can appear only once in a stock adjustment.');
            }

            $productIds[] = $item['product_id'];
        }
    }
}
