<?php

namespace Tests\Feature\Inventory;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Business\App\Models\Branch;
use Modules\Catalog\App\Models\Category;
use Modules\Catalog\App\Models\Product;
use Modules\Catalog\App\Models\TaxRate;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;
use Modules\Inventory\App\Actions\CreateInventoryTransferAction;
use Modules\Inventory\App\Actions\PostStockAdjustmentAction;
use Modules\Inventory\App\Actions\RecordInventoryMovementAction;
use Modules\Inventory\App\Domain\Data\CreateInventoryTransferData;
use Modules\Inventory\App\Domain\Data\PostStockAdjustmentData;
use Modules\Inventory\App\Domain\Data\RecordInventoryMovementData;
use Modules\Inventory\App\Domain\Enums\InventoryMovementType;
use Modules\Inventory\App\Domain\Enums\StockAdjustmentType;
use Modules\Inventory\App\Domain\Events\LowStockDetected;
use Modules\Inventory\App\Models\InventoryBalance;
use RuntimeException;
use Tests\Support\Tenancy\TenantIsolationTestCase;

class LowStockDetectionTest extends TenantIsolationTestCase
{
    public function test_detects_only_a_crossing_into_an_enabled_low_stock_threshold_and_snapshots_the_result(): void
    {
        [$owner, $tenant, $branch, $product] = $this->fixture(5);
        Event::fake([LowStockDetected::class]);

        $this->record($owner, $tenant, $branch, $product, InventoryMovementType::Opening, 6, 'open');
        $this->record($owner, $tenant, $branch, $product, InventoryMovementType::AdjustmentOut, 1, 'at-threshold');
        $this->record($owner, $tenant, $branch, $product, InventoryMovementType::AdjustmentOut, 1, 'at-threshold');
        $this->record($owner, $tenant, $branch, $product, InventoryMovementType::AdjustmentOut, 1, 'below-threshold');

        Event::assertDispatchedTimes(LowStockDetected::class, 1);
        Event::assertDispatched(LowStockDetected::class, function (LowStockDetected $event) use ($tenant, $branch, $product): bool {
            return $event->tenantId === (int) $tenant->getKey()
                && $event->branchId === (int) $branch->getKey()
                && $event->productId === (int) $product->getKey()
                && $event->resultingBalance === 5
                && $event->threshold === 5;
        });
    }

    public function test_disabled_threshold_and_balances_already_at_or_below_the_threshold_stay_silent_until_recovery(): void
    {
        [$owner, $tenant, $branch, $product] = $this->fixture(0);
        Event::fake([LowStockDetected::class]);

        $this->record($owner, $tenant, $branch, $product, InventoryMovementType::Opening, 2, 'disabled-open');
        $this->record($owner, $tenant, $branch, $product, InventoryMovementType::AdjustmentOut, 1, 'disabled-out');
        Event::assertNotDispatched(LowStockDetected::class);

        $product->update(['low_stock_threshold' => 5]);
        $this->record($owner, $tenant, $branch, $product, InventoryMovementType::AdjustmentIn, 5, 'recover-above-threshold');
        $this->record($owner, $tenant, $branch, $product, InventoryMovementType::AdjustmentOut, 1, 'new-crossing');

        Event::assertDispatchedTimes(LowStockDetected::class, 1);
    }

    public function test_low_stock_event_is_not_dispatched_when_the_outer_transaction_rolls_back(): void
    {
        [$owner, $tenant, $branch, $product] = $this->fixture(5);
        Event::fake([LowStockDetected::class]);

        try {
            DB::transaction(function () use ($owner, $tenant, $branch, $product): void {
                $this->record($owner, $tenant, $branch, $product, InventoryMovementType::Opening, 6, 'rollback-open');
                $this->record($owner, $tenant, $branch, $product, InventoryMovementType::AdjustmentOut, 1, 'rollback-crossing');

                throw new RuntimeException('Force the enclosing transaction to roll back.');
            });
            $this->fail('The forced rollback did not occur.');
        } catch (RuntimeException) {
            Event::assertNotDispatched(LowStockDetected::class);
            $this->assertDatabaseCount('inventory_movements', 0);
        }
    }

    public function test_low_stock_event_waits_for_the_outer_transaction_to_commit(): void
    {
        [$owner, $tenant, $branch, $product] = $this->fixture(5);
        Event::fake([LowStockDetected::class]);

        DB::transaction(function () use ($owner, $tenant, $branch, $product): void {
            $this->record($owner, $tenant, $branch, $product, InventoryMovementType::Opening, 6, 'commit-open');
            $this->record($owner, $tenant, $branch, $product, InventoryMovementType::AdjustmentOut, 1, 'commit-crossing');

            Event::assertNotDispatched(LowStockDetected::class);
        });

        Event::assertDispatched(LowStockDetected::class);
    }

    public function test_stock_adjustment_posting_uses_the_same_low_stock_crossing_rule(): void
    {
        [$owner, $tenant, $branch, $product] = $this->fixture(5);
        $this->record($owner, $tenant, $branch, $product, InventoryMovementType::Opening, 6, 'adjustment-open');
        Event::fake([LowStockDetected::class]);

        app(PostStockAdjustmentAction::class)->execute($owner, $tenant, new PostStockAdjustmentData(
            branchId: (int) $branch->getKey(),
            type: StockAdjustmentType::AdjustmentOut,
            reason: 'Damaged item',
            items: [['product_id' => (int) $product->getKey(), 'quantity' => 1]],
            idempotencyKey: 'adjustment-crossing',
        ));

        Event::assertDispatched(LowStockDetected::class, fn (LowStockDetected $event): bool => $event->branchId === (int) $branch->getKey() && $event->resultingBalance === 5);
    }

    public function test_transfer_posting_evaluates_source_and_destination_balances_without_alerting_the_destination_that_was_already_low(): void
    {
        [$owner, $tenant, $source, $product] = $this->fixture(5);
        $destination = Branch::query()->create(['name' => 'Giza', 'code' => 'GIZ', 'status' => Branch::STATUS_ACTIVE]);
        $sourceBalance = new InventoryBalance;
        $sourceBalance->forceFill(['branch_id' => $source->getKey(), 'product_id' => $product->getKey(), 'quantity_on_hand' => 6]);
        $sourceBalance->save();
        Event::fake([LowStockDetected::class]);

        app(CreateInventoryTransferAction::class)->execute($owner, $tenant, new CreateInventoryTransferData(
            sourceBranchId: (int) $source->getKey(),
            destinationBranchId: (int) $destination->getKey(),
            reason: 'Rebalance stock',
            items: [['product_id' => (int) $product->getKey(), 'quantity' => 1]],
            idempotencyKey: 'transfer-crossing',
        ));

        Event::assertDispatchedTimes(LowStockDetected::class, 1);
        Event::assertDispatched(LowStockDetected::class, fn (LowStockDetected $event): bool => $event->branchId === (int) $source->getKey() && $event->resultingBalance === 5);
        $this->assertDatabaseHas('inventory_balances', ['branch_id' => $destination->getKey(), 'product_id' => $product->getKey(), 'quantity_on_hand' => 1]);
    }

    /** @return array{0: User, 1: Tenant, 2: Branch, 3: Product} */
    private function fixture(int $threshold): array
    {
        [$owner, $tenant, $membership] = $this->makeMembership();
        app(TenantContext::class)->set($tenant, $membership);
        $branch = new Branch;
        $branch->forceFill(['name' => 'Cairo', 'code' => 'CAI', 'status' => Branch::STATUS_ACTIVE]);
        $branch->save();
        $category = Category::query()->create(['name' => 'Drinks']);
        $taxRate = TaxRate::query()->create(['name' => 'VAT', 'rate_basis_points' => 1400, 'effective_from' => today(), 'status' => TaxRate::STATUS_ACTIVE]);
        /** @var Product $product */
        $product = Product::query()->create(['category_id' => $category->getKey(), 'tax_rate_id' => $taxRate->getKey(), 'name' => 'Coffee', 'selling_price_minor' => 100, 'track_inventory' => true, 'low_stock_threshold' => $threshold, 'status' => Product::STATUS_ACTIVE]);

        return [$owner, $tenant, $branch, $product];
    }

    private function record(User $actor, Tenant $tenant, Branch $branch, Product $product, InventoryMovementType $type, int $quantity, string $key): void
    {
        app(RecordInventoryMovementAction::class)->execute($actor, $tenant, new RecordInventoryMovementData(
            branchId: (int) $branch->getKey(),
            productId: (int) $product->getKey(),
            type: $type,
            quantity: $quantity,
            idempotencyKey: $key,
        ));
    }
}
