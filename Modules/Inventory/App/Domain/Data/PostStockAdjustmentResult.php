<?php

namespace Modules\Inventory\App\Domain\Data;

use Modules\Inventory\App\Models\StockAdjustment;

final class PostStockAdjustmentResult
{
    public function __construct(
        public readonly StockAdjustment $adjustment,
        public readonly bool $wasIdempotent,
    ) {}
}
