<?php

namespace Modules\Inventory\App\Actions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Modules\Business\App\Domain\Branches\BranchAuthorization;
use Modules\Business\App\Models\Branch;
use Modules\Catalog\App\Models\Product;
use Modules\Identity\App\Domain\Authorization\TenantAuthorization;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Domain\Tenancy\TenantContextException;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;
use Modules\Inventory\App\Domain\Data\PostStockAdjustmentData;
use Modules\Inventory\App\Domain\Data\PostStockAdjustmentResult;
use Modules\Inventory\App\Domain\Data\RecordInventoryMovementData;
use Modules\Inventory\App\Domain\Enums\StockAdjustmentType;
use Modules\Inventory\App\Domain\Exceptions\StockAdjustmentException;
use Modules\Inventory\App\Models\InventoryMovement;
use Modules\Inventory\App\Models\StockAdjustment;
use Modules\Inventory\App\Models\StockAdjustmentItem;

final class PostStockAdjustmentAction
{
    public function __construct(
        private readonly TenantContext $context,
        private readonly TenantAuthorization $authorization,
        private readonly BranchAuthorization $branchAuthorization,
        private readonly RecordInventoryMovementAction $recordMovement,
    ) {}

    public function execute(User $actor, Tenant $tenant, PostStockAdjustmentData $data): PostStockAdjustmentResult
    {
        $this->ensureAuthorizedActor($actor, $tenant);

        return DB::transaction(function () use ($actor, $data): PostStockAdjustmentResult {
            /** @var StockAdjustment|null $existing */
            $existing = StockAdjustment::query()->where('idempotency_key', $data->idempotencyKey)->lockForUpdate()->first();
            if ($existing instanceof StockAdjustment) {
                return new PostStockAdjustmentResult($existing->load(['items.product', 'items.movement']), true);
            }

            /** @var Branch|null $branch */
            $branch = Branch::query()->whereKey($data->branchId)->lockForUpdate()->first();
            if (! $branch instanceof Branch || ! $branch->isActive()) {
                throw new StockAdjustmentException('Stock adjustments require an active branch in the current tenant.');
            }

            if (! $this->branchAuthorization->canManage($actor, $this->context->tenant())
                /** @phpstan-ignore-next-line dynamic Eloquent scope */
                && ! Branch::query()->accessibleTo($actor)->whereKey($branch->getKey())->lockForUpdate()->exists()) {
                throw new AuthorizationException('The current actor cannot adjust inventory at this branch.');
            }

            $adjustment = new StockAdjustment;
            $adjustment->forceFill([
                'tenant_id' => $this->context->id(),
                'branch_id' => $branch->getKey(),
                'type' => $data->type,
                'reason' => trim($data->reason),
                'actor_user_id' => $actor->getKey(),
                'posted_at' => now(),
                'idempotency_key' => $data->idempotencyKey,
            ]);
            $adjustment->save();

            foreach ($data->items as $line) {
                /** @var Product|null $product */
                $product = Product::withTrashed()->whereKey($line['product_id'])->lockForUpdate()->first();
                if (! $product instanceof Product || ! $product->isSaleAvailable() || ! $product->track_inventory) {
                    throw new StockAdjustmentException('Stock adjustment items require active tracked products in the current tenant.');
                }

                if ($data->type === StockAdjustmentType::Opening
                    && InventoryMovement::query()->where('branch_id', $branch->getKey())->where('product_id', $product->getKey())->lockForUpdate()->exists()) {
                    throw new StockAdjustmentException('Opening stock can be posted only before any movement exists for a branch and product.');
                }

                $item = new StockAdjustmentItem;
                $item->forceFill([
                    'tenant_id' => $this->context->id(),
                    'stock_adjustment_id' => $adjustment->getKey(),
                    'product_id' => $product->getKey(),
                    'quantity' => $line['quantity'],
                ]);
                $item->save();

                $movement = $this->recordMovement->execute($actor, $this->context->tenant(), new RecordInventoryMovementData(
                    branchId: (int) $branch->getKey(),
                    productId: (int) $product->getKey(),
                    type: $data->type->movementType(),
                    quantity: $line['quantity'],
                    idempotencyKey: 'stock-adjustment:'.$data->idempotencyKey.':'.$product->getKey(),
                    sourceType: 'stock_adjustment_item',
                    sourceId: (string) $item->getKey(),
                ));
                /** @var InventoryMovement $recordedMovement */
                $recordedMovement = InventoryMovement::query()->findOrFail($movement->movementId);
                $item->attachMovement($recordedMovement);
            }

            return new PostStockAdjustmentResult($adjustment->load(['items.product', 'items.movement']), false);
        }, 3);
    }

    private function ensureAuthorizedActor(User $actor, Tenant $tenant): void
    {
        if (! $this->context->hasTenant() || ! $this->context->tenant()->is($tenant) || $this->context->userId() !== (int) $actor->getKey()) {
            throw new TenantContextException('Stock adjustments require the current tenant context and actor.');
        }

        if (! $this->authorization->allows($actor, $tenant, 'inventory.adjust')) {
            throw new AuthorizationException('The current actor cannot post stock adjustments.');
        }
    }
}
