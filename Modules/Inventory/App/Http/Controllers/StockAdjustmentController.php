<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\Business\App\Domain\Branches\BranchAuthorization;
use Modules\Business\App\Models\Branch;
use Modules\Catalog\App\Models\Product;
use Modules\Identity\App\Domain\Authorization\TenantAuthorization;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Inventory\App\Actions\PostStockAdjustmentAction;
use Modules\Inventory\App\Domain\Data\PostStockAdjustmentData;
use Modules\Inventory\App\Domain\Enums\StockAdjustmentType;
use Modules\Inventory\App\Domain\Exceptions\InventoryMovementException;
use Modules\Inventory\App\Domain\Exceptions\StockAdjustmentException;
use Modules\Inventory\App\Http\Requests\ManageStockAdjustmentRequest;
use Modules\Inventory\App\Http\Requests\PostStockAdjustmentRequest;
use Modules\Inventory\App\Http\Requests\StockAdjustmentIndexRequest;
use Modules\Inventory\App\Models\StockAdjustment;

final class StockAdjustmentController extends Controller
{
    public function __construct(
        private readonly TenantContext $context,
        private readonly TenantAuthorization $authorization,
        private readonly BranchAuthorization $branchAuthorization,
    ) {}

    public function index(StockAdjustmentIndexRequest $request): View
    {
        $query = StockAdjustment::query()->with(['branch', 'actor'])->withCount('items');
        if (! $this->canManageBranches($request->user())) {
            /** @phpstan-ignore-next-line dynamic Eloquent scope */
            $query->whereIn('branch_id', Branch::query()->accessibleTo($request->user())->select('id'));
        }

        return view('inventory::adjustments.index', [
            'adjustments' => $query->latest('posted_at')->get(),
            'canAdjust' => $this->canAdjust($request->user()),
        ]);
    }

    public function createOpening(ManageStockAdjustmentRequest $request): View
    {
        return $this->form(StockAdjustmentType::Opening);
    }

    public function createAdjustment(ManageStockAdjustmentRequest $request): View
    {
        return $this->form(null);
    }

    public function store(PostStockAdjustmentRequest $request, PostStockAdjustmentAction $action): RedirectResponse
    {
        $validated = $request->validated();
        $items = array_map(static fn (array $item): array => [
            'product_id' => (int) $item['product_id'],
            'quantity' => (int) $item['quantity'],
        ], $validated['items']);

        try {
            $result = $action->execute($request->user(), $this->context->tenant(), new PostStockAdjustmentData(
                branchId: (int) $validated['branch_id'],
                type: StockAdjustmentType::fromInput($validated['type']),
                reason: $validated['reason'],
                items: $items,
                idempotencyKey: $validated['idempotency_key'],
            ));
        } catch (StockAdjustmentException|InventoryMovementException $exception) {
            return back()->withInput()->withErrors(['items' => $exception->getMessage()]);
        }

        return redirect()->route('inventory.adjustments.show', $result->adjustment)
            ->with('status', $result->wasIdempotent ? 'تم فتح مستند المخزون المسجل سابقاً.' : 'تم ترحيل مستند المخزون بنجاح.');
    }

    public function show(StockAdjustmentIndexRequest $request, StockAdjustment $stockAdjustment): View
    {
        $this->ensureBranchAccess($request->user(), $stockAdjustment->branch_id);

        return view('inventory::adjustments.show', [
            'adjustment' => $stockAdjustment->load(['branch', 'actor', 'items.product', 'items.movement']),
        ]);
    }

    private function form(?StockAdjustmentType $fixedType): View
    {
        return view('inventory::adjustments.form', [
            'fixedType' => $fixedType,
            'branches' => $this->availableBranches(),
            'products' => Product::query()->where('status', Product::STATUS_ACTIVE)->where('track_inventory', true)->orderBy('name')->get(),
        ]);
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, Branch> */
    private function availableBranches(): \Illuminate\Database\Eloquent\Collection
    {
        $branches = Branch::query()->where('status', Branch::STATUS_ACTIVE);
        if (! $this->canManageBranches(request()->user())) {
            /** @phpstan-ignore-next-line dynamic Eloquent scope */
            $branches->accessibleTo(request()->user());
        }

        return $branches->orderBy('name')->get();
    }

    private function ensureBranchAccess(\Modules\Identity\App\Models\User $user, int $branchId): void
    {
        if ($this->canManageBranches($user)) {
            return;
        }

        /** @phpstan-ignore-next-line dynamic Eloquent scope */
        abort_unless(Branch::query()->accessibleTo($user)->whereKey($branchId)->exists(), 403);
    }

    private function canManageBranches(\Modules\Identity\App\Models\User $user): bool
    {
        return $this->branchAuthorization->canManage($user, $this->context->tenant());
    }

    private function canAdjust(\Modules\Identity\App\Models\User $user): bool
    {
        return $this->authorization->allows($user, $this->context->tenant(), 'inventory.adjust');
    }
}
