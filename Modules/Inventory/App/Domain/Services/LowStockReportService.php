<?php

namespace Modules\Inventory\App\Domain\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Catalog\App\Models\Product;
use Modules\Inventory\App\Models\InventoryBalance;

final class LowStockReportService
{
    /** @param array<int, int> $availableBranchIds
     * @return Collection<int, InventoryBalance>
     */
    public function balances(array $availableBranchIds, ?int $branchId = null): Collection
    {
        if ($availableBranchIds === []) {
            return new Collection;
        }

        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = InventoryBalance::query()
            ->with(['branch', 'product'])
            ->whereIn('inventory_balances.branch_id', $availableBranchIds);

        if ($branchId !== null) {
            $query->where('inventory_balances.branch_id', $branchId);
        }

        return $query
            ->whereHas('branch', fn ($query) => $query->where('status', 'active'))
            ->whereHas('product', fn ($query) => $query
                ->where('track_inventory', true)
                ->where('status', Product::STATUS_ACTIVE)
                ->where('low_stock_threshold', '>', 0))
            ->join('branches', 'inventory_balances.branch_id', '=', 'branches.id')
            ->join('products', 'inventory_balances.product_id', '=', 'products.id')
            ->whereColumn('inventory_balances.quantity_on_hand', '<=', 'products.low_stock_threshold')
            ->select('inventory_balances.*')
            ->orderBy('branches.name')
            ->orderBy('products.name')
            ->get();
    }
}
