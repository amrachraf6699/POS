<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Modules\Business\App\Models\Branch;
use Modules\Catalog\App\Models\Product;
use Modules\Inventory\App\Http\Requests\BalanceIndexRequest;
use Modules\Inventory\App\Models\InventoryBalance;

final class InventoryBalanceController extends Controller
{
    public function index(BalanceIndexRequest $request): View
    {
        $filters = $request->validated();
        $balances = InventoryBalance::query()
            ->with(['branch', 'product'])
            ->whereHas('branch', fn ($query) => $query->where('status', Branch::STATUS_ACTIVE))
            ->whereHas('product', fn ($query) => $query->where('track_inventory', true)->where('status', Product::STATUS_ACTIVE))
            ->when(isset($filters['branch']), fn ($query) => $query->where('branch_id', $filters['branch']))
            ->when(isset($filters['product']), fn ($query) => $query->where('product_id', $filters['product']))
            ->join('branches', 'inventory_balances.branch_id', '=', 'branches.id')
            ->join('products', 'inventory_balances.product_id', '=', 'products.id')
            ->select('inventory_balances.*')
            ->orderBy('branches.name')
            ->orderBy('products.name')
            ->get();

        return view('inventory::balances.index', [
            'balances' => $balances,
            'branches' => Branch::query()->where('status', Branch::STATUS_ACTIVE)->orderBy('name')->get(),
            'products' => Product::query()->where('track_inventory', true)->where('status', Product::STATUS_ACTIVE)->orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }
}
