<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\Business\App\Models\Branch;
use Modules\Business\App\Models\BranchAssignment;
use Modules\Catalog\App\Models\Product;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Inventory\App\Actions\ApproveInventoryTransferAction;
use Modules\Inventory\App\Actions\CancelInventoryTransferAction;
use Modules\Inventory\App\Actions\CreateInventoryTransferAction;
use Modules\Inventory\App\Domain\Data\CreateInventoryTransferData;
use Modules\Inventory\App\Domain\Enums\InventoryTransferStatus;
use Modules\Inventory\App\Domain\Exceptions\InventoryTransferException;
use Modules\Inventory\App\Http\Requests\CancelInventoryTransferRequest;
use Modules\Inventory\App\Http\Requests\ManageInventoryTransferRequest;
use Modules\Inventory\App\Http\Requests\StoreInventoryTransferRequest;
use Modules\Inventory\App\Http\Requests\ViewInventoryTransferRequest;
use Modules\Inventory\App\Models\InventoryTransfer;

final class InventoryTransferController extends Controller
{
    public function __construct(private readonly TenantContext $context) {}

    public function index(ViewInventoryTransferRequest $request): View
    {
        $query = InventoryTransfer::query()->with(['sourceBranch', 'destinationBranch', 'creator'])->withCount('items')->latest();
        if (! $this->canManageBranches()) {
            $branchIds = BranchAssignment::query()->where('user_id', $request->user()->getKey())->where('status', BranchAssignment::STATUS_ACTIVE)->pluck('branch_id');
            $query->whereIn('source_branch_id', $branchIds)->whereIn('destination_branch_id', $branchIds);
        }

        return view('inventory::transfers.index', ['transfers' => $query->get(), 'canTransfer' => true]);
    }

    public function create(ManageInventoryTransferRequest $request): View
    {
        return view('inventory::transfers.form', ['branches' => Branch::query()->where('status', Branch::STATUS_ACTIVE)->orderBy('name')->get(), 'products' => Product::query()->where('status', Product::STATUS_ACTIVE)->where('track_inventory', true)->orderBy('name')->get()]);
    }

    public function store(StoreInventoryTransferRequest $request, CreateInventoryTransferAction $action): RedirectResponse
    {
        try {
            $data = $request->validated();
            $result = $action->execute($request->user(), $this->context->tenant(), new CreateInventoryTransferData((int) $data['source_branch_id'], (int) $data['destination_branch_id'], $data['reason'], array_map(static fn (array $item): array => ['product_id' => (int) $item['product_id'], 'quantity' => (int) $item['quantity']], $data['items']), $data['idempotency_key']));
        } catch (InventoryTransferException $exception) {
            return back()->withInput()->withErrors(['items' => $exception->getMessage()]);
        }

        return redirect()->route('inventory.transfers.show', $result->transfer)->with('status', $result->wasIdempotent ? 'تم فتح التحويل المسجل سابقاً.' : 'تم تسجيل التحويل بنجاح.');
    }

    public function show(ViewInventoryTransferRequest $request, InventoryTransfer $inventoryTransfer): View
    {
        if (! $this->canManageBranches()) {
            $assigned = BranchAssignment::query()->where('user_id', $request->user()->getKey())->where('status', BranchAssignment::STATUS_ACTIVE)->whereIn('branch_id', [$inventoryTransfer->source_branch_id, $inventoryTransfer->destination_branch_id])->count();
            abort_unless($assigned === 2, 403);
        }

        return view('inventory::transfers.show', ['transfer' => $inventoryTransfer->load(['sourceBranch', 'destinationBranch', 'creator', 'poster', 'canceller', 'items.product', 'items.transferOutMovement', 'items.transferInMovement']), 'canApprove' => $inventoryTransfer->status === InventoryTransferStatus::Pending && $inventoryTransfer->requires_manager_approval && ($this->context->membership()->isOwner() || $this->context->membership()->isManager()), 'canCancel' => $inventoryTransfer->status === InventoryTransferStatus::Pending && ((int) $inventoryTransfer->created_by_user_id === (int) $request->user()->getKey() || $this->context->membership()->isOwner() || $this->context->membership()->isManager())]);
    }

    public function approve(ManageInventoryTransferRequest $request, InventoryTransfer $inventoryTransfer, ApproveInventoryTransferAction $action): RedirectResponse
    {
        try {
            $action->execute($request->user(), $this->context->tenant(), $inventoryTransfer);
        } catch (InventoryTransferException $exception) {
            return back()->withErrors(['transfer' => $exception->getMessage()]);
        }

        return redirect()->route('inventory.transfers.show', $inventoryTransfer)->with('status', 'تم اعتماد وترحيل التحويل.');
    }

    public function cancel(CancelInventoryTransferRequest $request, InventoryTransfer $inventoryTransfer, CancelInventoryTransferAction $action): RedirectResponse
    {
        try {
            $action->execute($request->user(), $this->context->tenant(), $inventoryTransfer, $request->validated('reason'));
        } catch (InventoryTransferException $exception) {
            return back()->withErrors(['transfer' => $exception->getMessage()]);
        }

        return redirect()->route('inventory.transfers.show', $inventoryTransfer)->with('status', 'تم إلغاء التحويل دون تسجيل حركة مخزون.');
    }

    private function canManageBranches(): bool
    {
        return $this->context->membership()->isOwner() || $this->context->membership()->isManager();
    }
}
