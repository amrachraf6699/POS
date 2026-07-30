<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Modules\Business\App\Domain\Branches\BranchAuthorization;
use Modules\Business\App\Models\Branch;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Inventory\App\Domain\Services\LowStockReportService;
use Modules\Inventory\App\Http\Requests\LowStockIndexRequest;

final class LowStockController extends Controller
{
    public function __construct(
        private readonly BranchAuthorization $branchAuthorization,
        private readonly LowStockReportService $report,
        private readonly TenantContext $context,
    ) {}

    public function index(LowStockIndexRequest $request): View
    {
        $branches = Branch::query()->where('status', Branch::STATUS_ACTIVE);
        if (! $this->branchAuthorization->canManage($request->user(), $this->context->tenant())) {
            /** @phpstan-ignore-next-line dynamic Eloquent scope */
            $branches->accessibleTo($request->user());
        }
        $availableBranches = $branches->orderBy('name')->get();
        $availableBranchIds = $availableBranches->map(static fn (Branch $branch): int => (int) $branch->getKey())->all();
        $filters = $request->validated();
        $branchId = isset($filters['branch']) ? (int) $filters['branch'] : null;

        abort_if($branchId !== null && ! in_array($branchId, $availableBranchIds, true), 403);

        return view('inventory::low-stock.index', [
            'balances' => $this->report->balances($availableBranchIds, $branchId),
            'branches' => $availableBranches,
            'filters' => $filters,
        ]);
    }
}
