<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Modules\Business\App\Domain\Branches\BranchAuthorization;
use Modules\Business\App\Models\Branch;
use Modules\Catalog\App\Models\Product;
use Modules\Identity\App\Domain\Authorization\TenantAuthorization;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Inventory\App\Http\Requests\BalanceIndexRequest;
use Modules\Inventory\App\Models\InventoryBalance;

final class InventoryBalanceController extends Controller
{
    public function __construct(
        private readonly TenantContext $context,
        private readonly TenantAuthorization $authorization,
        private readonly BranchAuthorization $branchAuthorization,
    ) {}

    public function index(BalanceIndexRequest $request): View
    {
        $filters = $request->validated();
        $canManageBranches = $this->branchAuthorization->canManage($request->user(), $this->context->tenant());
        $branches = Branch::query()->where('status', Branch::STATUS_ACTIVE);
        if (! $canManageBranches) {
            /** @phpstan-ignore-next-line dynamic Eloquent scope */
            $branches->accessibleTo($request->user());
        }
        $availableBranches = $branches->orderBy('name')->get();
        $availableBranchIds = $availableBranches->map(static fn (Branch $branch): int => (int) $branch->getKey())->all();
        $balances = InventoryBalance::query()
            ->with(['branch', 'product'])
            ->whereHas('branch', fn ($query) => $query->where('status', Branch::STATUS_ACTIVE))
            ->whereHas('product', fn ($query) => $query->where('track_inventory', true)->where('status', Product::STATUS_ACTIVE))
            ->when(! $canManageBranches, fn ($query) => $query->whereIn('branch_id', $availableBranchIds))
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
            'branches' => $availableBranches,
            'products' => Product::query()->where('track_inventory', true)->where('status', Product::STATUS_ACTIVE)->orderBy('name')->get(),
            'filters' => $filters,
            'canAdjust' => $this->authorization->allows($request->user(), $this->context->tenant(), 'inventory.adjust'),
        ]);
    }
}
