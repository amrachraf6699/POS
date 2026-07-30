<?php

namespace Modules\Inventory\App\Domain\Services;

use Modules\Catalog\App\Models\Product;
use Modules\Inventory\App\Domain\Events\LowStockDetected;

final class LowStockDetectionService
{
    public function detectCrossing(int $tenantId, int $branchId, Product $product, int $previousBalance, int $resultingBalance): void
    {
        $threshold = (int) $product->low_stock_threshold;

        if ($threshold <= 0 || $previousBalance <= $threshold || $resultingBalance > $threshold) {
            return;
        }

        event(new LowStockDetected(
            tenantId: $tenantId,
            branchId: $branchId,
            productId: (int) $product->getKey(),
            resultingBalance: $resultingBalance,
            threshold: $threshold,
        ));
    }
}
