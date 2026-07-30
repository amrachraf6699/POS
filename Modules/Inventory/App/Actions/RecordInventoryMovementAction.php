<?php

namespace Modules\Inventory\App\Actions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Modules\Business\App\Models\Branch;
use Modules\Catalog\App\Models\Product;
use Modules\Identity\App\Domain\Authorization\TenantAuthorization;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Domain\Tenancy\TenantContextException;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;
use Modules\Inventory\App\Domain\Data\InventoryMovementResult;
use Modules\Inventory\App\Domain\Data\RecordInventoryMovementData;
use Modules\Inventory\App\Domain\Exceptions\InventoryMovementException;
use Modules\Inventory\App\Domain\Services\LowStockDetectionService;
use Modules\Inventory\App\Models\InventoryBalance;
use Modules\Inventory\App\Models\InventoryMovement;

final class RecordInventoryMovementAction
{
    public function __construct(
        private readonly TenantContext $context,
        private readonly TenantAuthorization $authorization,
        private readonly LowStockDetectionService $lowStock,
    ) {}

    public function execute(User $actor, Tenant $tenant, RecordInventoryMovementData $data): InventoryMovementResult
    {
        $this->ensureAuthorizedActor($actor, $tenant);

        return DB::transaction(function () use ($actor, $data): InventoryMovementResult {
            /** @var InventoryMovement|null $existing */
            $existing = InventoryMovement::query()
                ->where('idempotency_key', $data->idempotencyKey)
                ->lockForUpdate()
                ->first();

            if ($existing instanceof InventoryMovement) {
                return InventoryMovementResult::fromMovement($existing, true);
            }

            /** @var Branch|null $branch */
            $branch = Branch::query()->whereKey($data->branchId)->lockForUpdate()->first();
            if (! $branch instanceof Branch || ! $branch->isActive()) {
                throw new InventoryMovementException('Inventory movements require an active branch in the current tenant.');
            }

            /** @var Product|null $product */
            $product = Product::withTrashed()->whereKey($data->productId)->lockForUpdate()->first();
            if (! $product instanceof Product || ! $product->isSaleAvailable()) {
                throw new InventoryMovementException('Inventory movements require an active product in the current tenant.');
            }

            if (! $product->track_inventory) {
                throw new InventoryMovementException('Inventory movements are not allowed for products that do not track inventory.');
            }

            if ((int) $branch->tenant_id !== $this->context->id() || (int) $product->tenant_id !== $this->context->id()) {
                throw new TenantContextException('The inventory branch and product must belong to the current tenant.');
            }

            /** @var InventoryBalance|null $balance */
            $balance = InventoryBalance::query()
                ->where('branch_id', $branch->getKey())
                ->where('product_id', $product->getKey())
                ->lockForUpdate()
                ->first();

            if (! $balance instanceof InventoryBalance) {
                $balance = new InventoryBalance;
                $balance->forceFill([
                    'tenant_id' => $this->context->id(),
                    'branch_id' => $branch->getKey(),
                    'product_id' => $product->getKey(),
                    'quantity_on_hand' => 0,
                ]);
                $balance->save();
            }

            $quantityDelta = $data->type->signedQuantityDelta($data->quantity);
            $previousBalance = (int) $balance->quantity_on_hand;
            $resultingBalance = $previousBalance + $quantityDelta;
            if (! is_int($resultingBalance)) {
                throw new InventoryMovementException('The resulting inventory balance is outside the supported range.');
            }

            if (! $product->allow_negative_stock && $resultingBalance < 0) {
                throw new InventoryMovementException('This product does not allow negative stock.');
            }

            $movement = InventoryMovement::record([
                'tenant_id' => $this->context->id(),
                'branch_id' => $branch->getKey(),
                'product_id' => $product->getKey(),
                'type' => $data->type,
                'quantity' => $data->quantity,
                'quantity_delta' => $quantityDelta,
                'balance_after' => $resultingBalance,
                'idempotency_key' => $data->idempotencyKey,
                'source_type' => $data->sourceType,
                'source_id' => $data->sourceId,
                'actor_user_id' => $actor->getKey(),
            ]);

            $balance->forceFill(['quantity_on_hand' => $resultingBalance]);
            $balance->save();
            $this->lowStock->detectCrossing($this->context->id(), (int) $branch->getKey(), $product, $previousBalance, $resultingBalance);

            return InventoryMovementResult::fromMovement($movement, false);
        }, 3);
    }

    private function ensureAuthorizedActor(User $actor, Tenant $tenant): void
    {
        if (! $this->context->hasTenant() || ! $this->context->tenant()->is($tenant) || $this->context->userId() !== (int) $actor->getKey()) {
            throw new TenantContextException('Inventory movements require the current tenant context and actor.');
        }

        if (! $this->authorization->allows($actor, $tenant, 'inventory.adjust')) {
            throw new AuthorizationException('The current actor cannot record inventory movements.');
        }
    }
}
