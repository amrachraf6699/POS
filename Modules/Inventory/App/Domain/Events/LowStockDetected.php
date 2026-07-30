<?php

namespace Modules\Inventory\App\Domain\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

final class LowStockDetected implements ShouldDispatchAfterCommit
{
    public function __construct(
        public readonly int $tenantId,
        public readonly int $branchId,
        public readonly int $productId,
        public readonly int $resultingBalance,
        public readonly int $threshold,
    ) {}
}
