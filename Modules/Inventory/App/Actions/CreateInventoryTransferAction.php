<?php

namespace Modules\Inventory\App\Actions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Modules\Business\App\Domain\Settings\BusinessSettingsService;
use Modules\Business\App\Models\Branch;
use Modules\Business\App\Models\BranchAssignment;
use Modules\Catalog\App\Models\Product;
use Modules\Identity\App\Domain\Authorization\TenantAuthorization;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Domain\Tenancy\TenantContextException;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;
use Modules\Inventory\App\Domain\Data\CreateInventoryTransferData;
use Modules\Inventory\App\Domain\Data\InventoryTransferResult;
use Modules\Inventory\App\Domain\Enums\InventoryTransferStatus;
use Modules\Inventory\App\Domain\Exceptions\InventoryTransferException;
use Modules\Inventory\App\Models\InventoryTransfer;
use Modules\Inventory\App\Models\InventoryTransferItem;

final class CreateInventoryTransferAction
{
    public function __construct(private readonly TenantContext $context, private readonly TenantAuthorization $authorization, private readonly BusinessSettingsService $settings, private readonly PostInventoryTransferAction $post) {}

    public function execute(User $actor, Tenant $tenant, CreateInventoryTransferData $data): InventoryTransferResult
    {
        $this->ensureAuthorizedActor($actor, $tenant);

        return DB::transaction(function () use ($actor, $data): InventoryTransferResult {
            $existing = InventoryTransfer::query()->where('idempotency_key', $data->idempotencyKey)->lockForUpdate()->first();
            if ($existing instanceof InventoryTransfer) {
                return new InventoryTransferResult($existing->load(['items.product']), true);
            }
            $branches = Branch::query()->whereIn('id', [$data->sourceBranchId, $data->destinationBranchId])->orderBy('id')->lockForUpdate()->get();
            if ($branches->count() !== 2 || $branches->contains(fn (Branch $branch): bool => ! $branch->isActive())) {
                throw new InventoryTransferException('A transfer requires two active branches in the current tenant.');
            }
            $membership = $this->context->membership();
            if (! $membership->isOwner() && ! $membership->isManager()) {
                $assigned = BranchAssignment::query()->where('user_id', $actor->getKey())->where('status', BranchAssignment::STATUS_ACTIVE)->whereIn('branch_id', [$data->sourceBranchId, $data->destinationBranchId])->lockForUpdate()->count();
                if ($assigned !== 2) {
                    throw new AuthorizationException('Inventory staff must be assigned to both transfer branches.');
                }
            }
            $productIds = array_column($data->items, 'product_id');
            $products = Product::withTrashed()->whereIn('id', $productIds)->orderBy('id')->lockForUpdate()->get();
            if ($products->count() !== count($productIds) || $products->contains(fn (Product $product): bool => ! $product->isSaleAvailable() || ! $product->track_inventory)) {
                throw new InventoryTransferException('Transfer items require active tracked products in the current tenant.');
            }
            $requiresApproval = $this->settings->settingsForCurrentTenant()->transfer_requires_manager_approval;
            $transfer = new InventoryTransfer;
            $transfer->forceFill(['source_branch_id' => $data->sourceBranchId, 'destination_branch_id' => $data->destinationBranchId, 'status' => InventoryTransferStatus::Pending, 'requires_manager_approval' => $requiresApproval, 'reason' => trim($data->reason), 'created_by_user_id' => $actor->getKey(), 'idempotency_key' => $data->idempotencyKey]);
            $transfer->save();
            foreach ($data->items as $line) {
                $item = new InventoryTransferItem;
                $item->forceFill(['inventory_transfer_id' => $transfer->getKey(), 'product_id' => $line['product_id'], 'quantity' => $line['quantity']]);
                $item->save();
            }
            if (! $requiresApproval) {
                $transfer = $this->post->post($transfer, $actor);
            }

            return new InventoryTransferResult($transfer->load(['items.product', 'sourceBranch', 'destinationBranch']), false);
        }, 3);
    }

    private function ensureAuthorizedActor(User $actor, Tenant $tenant): void
    {
        if (! $this->context->hasTenant() || ! $this->context->tenant()->is($tenant) || $this->context->userId() !== (int) $actor->getKey()) {
            throw new TenantContextException('Transfers require the current tenant context and actor.');
        }
        if (! $this->authorization->allows($actor, $tenant, 'inventory.transfer')) {
            throw new AuthorizationException('The current actor cannot create inventory transfers.');
        }
    }
}
