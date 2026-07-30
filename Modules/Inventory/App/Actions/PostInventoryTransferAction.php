<?php

namespace Modules\Inventory\App\Actions;

use Modules\Business\App\Models\Branch;
use Modules\Catalog\App\Models\Product;
use Modules\Identity\App\Models\User;
use Modules\Inventory\App\Domain\Enums\InventoryMovementType;
use Modules\Inventory\App\Domain\Enums\InventoryTransferStatus;
use Modules\Inventory\App\Domain\Exceptions\InventoryTransferException;
use Modules\Inventory\App\Models\InventoryBalance;
use Modules\Inventory\App\Models\InventoryMovement;
use Modules\Inventory\App\Models\InventoryTransfer;
use Modules\Inventory\App\Models\InventoryTransferItem;

final class PostInventoryTransferAction
{
    public function post(InventoryTransfer $transfer, User $actor): InventoryTransfer
    {
        if ($transfer->status === InventoryTransferStatus::Posted) {
            return $transfer;
        }
        if ($transfer->status !== InventoryTransferStatus::Pending) {
            throw new InventoryTransferException('Only pending transfers can be posted.');
        }

        $branchIds = [(int) $transfer->source_branch_id, (int) $transfer->destination_branch_id];
        /** @var \Illuminate\Database\Eloquent\Collection<int, Branch> $branches */
        $branches = Branch::query()->whereIn('id', $branchIds)->orderBy('id')->lockForUpdate()->get();
        if ($branches->count() !== 2 || $branches->contains(fn (Branch $branch): bool => ! $branch->isActive())) {
            throw new InventoryTransferException('Transfers require two active branches in the current tenant.');
        }

        /** @var \Illuminate\Database\Eloquent\Collection<int, InventoryTransferItem> $items */
        $items = $transfer->items()->getQuery()->orderBy('product_id')->lockForUpdate()->get();
        if ($items->isEmpty()) {
            throw new InventoryTransferException('A transfer requires at least one item.');
        }
        $productIds = $items->pluck('product_id')->map(static fn ($id): int => (int) $id)->all();
        /** @var \Illuminate\Database\Eloquent\Collection<int, Product> $products */
        $products = Product::withTrashed()->whereIn('id', $productIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
        if ($products->count() !== count($productIds)) {
            throw new InventoryTransferException('Transfer products must belong to the current tenant.');
        }

        foreach ($items as $item) {
            $product = $products->get($item->product_id);
            if (! $product instanceof Product || ! $product->isSaleAvailable() || ! $product->track_inventory) {
                throw new InventoryTransferException('Transfer items require active tracked products.');
            }
        }

        foreach ($productIds as $productId) {
            foreach ($branchIds as $branchId) {
                $balance = InventoryBalance::query()->where('branch_id', $branchId)->where('product_id', $productId)->first();
                if (! $balance instanceof InventoryBalance) {
                    $balance = new InventoryBalance;
                    $balance->forceFill(['branch_id' => $branchId, 'product_id' => $productId, 'quantity_on_hand' => 0]);
                    $balance->save();
                }
            }
        }
        /** @var \Illuminate\Database\Eloquent\Collection<int, InventoryBalance> $balances */
        $balances = InventoryBalance::query()->whereIn('branch_id', $branchIds)->whereIn('product_id', $productIds)
            ->orderBy('branch_id')->orderBy('product_id')->lockForUpdate()->get();
        /** @var array<string, InventoryBalance> $balancesByKey */
        $balancesByKey = [];
        foreach ($balances as $balance) {
            $balancesByKey[$balance->branch_id.':'.$balance->product_id] = $balance;
        }

        foreach ($items as $item) {
            $quantity = (int) $item->quantity;
            /** @var Product $product */
            $product = $products->get($item->product_id);
            $source = $balancesByKey[$transfer->source_branch_id.':'.$item->product_id] ?? null;
            $destination = $balancesByKey[$transfer->destination_branch_id.':'.$item->product_id] ?? null;
            if (! $source instanceof InventoryBalance || ! $destination instanceof InventoryBalance) {
                throw new InventoryTransferException('Could not lock transfer inventory balances.');
            }
            $sourceAfter = (int) $source->quantity_on_hand - $quantity;
            if (! $product->allow_negative_stock && $sourceAfter < 0) {
                throw new InventoryTransferException('The source branch has insufficient stock for this transfer.');
            }
            $destinationAfter = (int) $destination->quantity_on_hand + $quantity;
            $out = InventoryMovement::record(['branch_id' => $source->branch_id, 'product_id' => $item->product_id, 'type' => InventoryMovementType::TransferOut, 'quantity' => $quantity, 'quantity_delta' => -$quantity, 'balance_after' => $sourceAfter, 'idempotency_key' => 'transfer:'.$transfer->getKey().':out:'.$item->getKey(), 'source_type' => 'inventory_transfer_item', 'source_id' => (string) $item->getKey(), 'actor_user_id' => $actor->getKey()]);
            $in = InventoryMovement::record(['branch_id' => $destination->branch_id, 'product_id' => $item->product_id, 'type' => InventoryMovementType::TransferIn, 'quantity' => $quantity, 'quantity_delta' => $quantity, 'balance_after' => $destinationAfter, 'idempotency_key' => 'transfer:'.$transfer->getKey().':in:'.$item->getKey(), 'source_type' => 'inventory_transfer_item', 'source_id' => (string) $item->getKey(), 'actor_user_id' => $actor->getKey()]);
            $source->forceFill(['quantity_on_hand' => $sourceAfter])->save();
            $destination->forceFill(['quantity_on_hand' => $destinationAfter])->save();
            $item->forceFill(['transfer_out_movement_id' => $out->getKey(), 'transfer_in_movement_id' => $in->getKey()])->save();
        }
        $transfer->forceFill(['status' => InventoryTransferStatus::Posted, 'posted_by_user_id' => $actor->getKey(), 'posted_at' => now()])->save();

        return $transfer->refresh();
    }
}
