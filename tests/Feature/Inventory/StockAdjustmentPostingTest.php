<?php

namespace Tests\Feature\Inventory;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use LogicException;
use Modules\Business\App\Models\Branch;
use Modules\Business\App\Models\BranchAssignment;
use Modules\Catalog\App\Models\Category;
use Modules\Catalog\App\Models\Product;
use Modules\Catalog\App\Models\TaxRate;
use Modules\Identity\App\Domain\Authorization\TenantRoleService;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Models\Membership;
use Modules\Identity\App\Models\User;
use Modules\Inventory\App\Actions\PostStockAdjustmentAction;
use Modules\Inventory\App\Domain\Data\PostStockAdjustmentData;
use Modules\Inventory\App\Domain\Enums\StockAdjustmentType;
use Modules\Inventory\App\Domain\Exceptions\StockAdjustmentException;
use Modules\Inventory\App\Models\InventoryBalance;
use Modules\Inventory\App\Models\StockAdjustment;
use RuntimeException;
use Tests\Support\Tenancy\TenantIsolationTestCase;

class StockAdjustmentPostingTest extends TenantIsolationTestCase
{
    public function test_opening_stock_posts_an_immutable_document_item_movement_and_balance(): void
    {
        [$owner, $tenant, $branch, $product] = $this->inventoryFixture();

        $result = $this->postAdjustment($owner, $tenant, $branch, StockAdjustmentType::Opening, 'رصيد افتتاحي', [
            ['product_id' => $product->getKey(), 'quantity' => 10],
        ], 'opening-stock');

        $this->assertFalse($result->wasIdempotent);
        $this->assertSame(StockAdjustmentType::Opening, $result->adjustment->type);
        $this->assertDatabaseHas('stock_adjustments', ['tenant_id' => $tenant->getKey(), 'branch_id' => $branch->getKey(), 'reason' => 'رصيد افتتاحي']);
        $this->assertDatabaseHas('stock_adjustment_items', ['stock_adjustment_id' => $result->adjustment->getKey(), 'product_id' => $product->getKey(), 'quantity' => 10]);
        $this->assertDatabaseHas('inventory_movements', ['type' => 'opening', 'source_type' => 'stock_adjustment_item', 'quantity_delta' => 10, 'balance_after' => 10]);
        $this->assertDatabaseHas('inventory_balances', ['branch_id' => $branch->getKey(), 'product_id' => $product->getKey(), 'quantity_on_hand' => 10]);

        $this->expectException(LogicException::class);
        StockAdjustment::query()->findOrFail($result->adjustment->getKey())->forceFill(['reason' => 'changed'])->save();
    }

    public function test_opening_stock_is_allowed_once_per_branch_product_and_idempotency_returns_the_original_document(): void
    {
        [$owner, $tenant, $branch, $product] = $this->inventoryFixture();
        $first = $this->postAdjustment($owner, $tenant, $branch, StockAdjustmentType::Opening, 'opening', [['product_id' => $product->getKey(), 'quantity' => 4]], 'opening-retry');
        $retry = $this->postAdjustment($owner, $tenant, $branch, StockAdjustmentType::AdjustmentIn, 'different retry payload', [['product_id' => $product->getKey(), 'quantity' => 999]], 'opening-retry');

        $this->assertTrue($retry->wasIdempotent);
        $this->assertSame($first->adjustment->getKey(), $retry->adjustment->getKey());
        $this->assertDatabaseCount('stock_adjustments', 1);
        $this->assertDatabaseCount('stock_adjustment_items', 1);
        $this->assertDatabaseCount('inventory_movements', 1);

        $this->expectException(StockAdjustmentException::class);
        try {
            $this->postAdjustment($owner, $tenant, $branch, StockAdjustmentType::Opening, 'second opening', [['product_id' => $product->getKey(), 'quantity' => 1]], 'second-opening');
        } finally {
            $this->assertDatabaseCount('stock_adjustments', 1);
            $this->assertDatabaseCount('inventory_movements', 1);
        }
    }

    public function test_adjustment_in_and_out_write_signed_movements_and_resulting_balances(): void
    {
        [$owner, $tenant, $branch, $product] = $this->inventoryFixture();
        $this->postAdjustment($owner, $tenant, $branch, StockAdjustmentType::Opening, 'opening', [['product_id' => $product->getKey(), 'quantity' => 10]], 'balance-opening');
        $this->postAdjustment($owner, $tenant, $branch, StockAdjustmentType::AdjustmentIn, 'counted extra stock', [['product_id' => $product->getKey(), 'quantity' => 3]], 'balance-in');
        $out = $this->postAdjustment($owner, $tenant, $branch, StockAdjustmentType::AdjustmentOut, 'damaged stock', [['product_id' => $product->getKey(), 'quantity' => 4]], 'balance-out');

        $this->assertSame(9, (int) InventoryBalance::query()->where('branch_id', $branch->getKey())->where('product_id', $product->getKey())->value('quantity_on_hand'));
        $this->assertDatabaseHas('inventory_movements', ['id' => $out->adjustment->items->first()->inventory_movement_id, 'type' => 'adjustment_out', 'quantity_delta' => -4, 'balance_after' => 9]);
    }

    public function test_invalid_lines_and_products_fail_before_any_document_or_inventory_writes(): void
    {
        [$owner, $tenant, $branch, $product] = $this->inventoryFixture();
        $untracked = $this->createProduct('Untracked');
        $untracked->update(['track_inventory' => false]);

        foreach ([
            fn () => $this->postAdjustment($owner, $tenant, $branch, StockAdjustmentType::Opening, 'duplicate', [['product_id' => $product->getKey(), 'quantity' => 1], ['product_id' => $product->getKey(), 'quantity' => 2]], 'duplicate'),
            fn () => $this->postAdjustment($owner, $tenant, $branch, StockAdjustmentType::Opening, 'untracked', [['product_id' => $untracked->getKey(), 'quantity' => 1]], 'untracked'),
        ] as $attempt) {
            try {
                $attempt();
                $this->fail('The invalid stock adjustment was posted.');
            } catch (StockAdjustmentException) {
                $this->assertDatabaseCount('stock_adjustments', 0);
                $this->assertDatabaseCount('stock_adjustment_items', 0);
                $this->assertDatabaseCount('inventory_movements', 0);
                $this->assertDatabaseCount('inventory_balances', 0);
            }
        }
    }

    public function test_inaccessible_branches_cross_tenant_products_and_insufficient_stock_are_rejected(): void
    {
        [$owner, $tenant, $branch, $product] = $this->inventoryFixture();
        [$staff, , $staffMembership] = $this->makeMembership(Membership::ROLE_INVENTORY_STAFF);
        $staffMembership->update(['tenant_id' => $tenant->getKey()]);
        app(TenantRoleService::class)->assign($staff, $tenant, Membership::ROLE_INVENTORY_STAFF);
        app(TenantContext::class)->set($tenant, $this->membershipFor($staff, $tenant));

        try {
            $this->postAdjustment($staff, $tenant, $branch, StockAdjustmentType::Opening, 'no branch assignment', [['product_id' => $product->getKey(), 'quantity' => 1]], 'no-access');
            $this->fail('An inventory staff member posted to an inaccessible branch.');
        } catch (AuthorizationException) {
            $this->assertDatabaseCount('stock_adjustments', 0);
        }

        BranchAssignment::query()->create(['tenant_id' => $tenant->getKey(), 'branch_id' => $branch->getKey(), 'user_id' => $staff->getKey(), 'status' => BranchAssignment::STATUS_ACTIVE]);
        $this->postAdjustment($staff, $tenant, $branch, StockAdjustmentType::Opening, 'assigned branch', [['product_id' => $product->getKey(), 'quantity' => 2]], 'assigned');

        app(TenantContext::class)->set($tenant, $this->membershipFor($owner, $tenant));
        $this->expectException(\Modules\Inventory\App\Domain\Exceptions\InventoryMovementException::class);
        try {
            $this->postAdjustment($owner, $tenant, $branch, StockAdjustmentType::AdjustmentOut, 'too much', [['product_id' => $product->getKey(), 'quantity' => 3]], 'too-much');
        } finally {
            $this->assertDatabaseCount('stock_adjustments', 1);
            $this->assertSame(2, (int) InventoryBalance::query()->value('quantity_on_hand'));
        }
    }

    public function test_a_ledger_failure_rolls_back_the_document_items_and_balance_together(): void
    {
        [$owner, $tenant, $branch, $product] = $this->inventoryFixture();
        DB::listen(function ($query): void {
            if (str_contains(strtolower($query->sql), 'insert into "inventory_movements"')) {
                throw new RuntimeException('Forced inventory movement failure.');
            }
        });

        $this->expectException(RuntimeException::class);
        try {
            $this->postAdjustment($owner, $tenant, $branch, StockAdjustmentType::Opening, 'will roll back', [['product_id' => $product->getKey(), 'quantity' => 5]], 'rollback');
        } finally {
            $this->assertDatabaseCount('stock_adjustments', 0);
            $this->assertDatabaseCount('stock_adjustment_items', 0);
            $this->assertDatabaseCount('inventory_movements', 0);
            $this->assertDatabaseCount('inventory_balances', 0);
        }
    }

    /** @return array{0: User, 1: \Modules\Identity\App\Models\Tenant, 2: Branch, 3: Product} */
    private function inventoryFixture(): array
    {
        [$owner, $tenant, $membership] = $this->makeMembership();
        app(TenantContext::class)->set($tenant, $membership);

        return [$owner, $tenant, $this->createBranch('Main branch', 'MAIN'), $this->createProduct('Tracked product')];
    }

    /** @param array<int, array{product_id: int, quantity: int}> $items */
    private function postAdjustment(User $actor, \Modules\Identity\App\Models\Tenant $tenant, Branch $branch, StockAdjustmentType $type, string $reason, array $items, string $key): \Modules\Inventory\App\Domain\Data\PostStockAdjustmentResult
    {
        return app(PostStockAdjustmentAction::class)->execute($actor, $tenant, new PostStockAdjustmentData($branch->getKey(), $type, $reason, $items, $key));
    }

    private function createProduct(string $name): Product
    {
        $category = Category::query()->create(['name' => $name.' category']);
        $taxRate = TaxRate::query()->create(['name' => $name.' tax', 'rate_basis_points' => 1400, 'effective_from' => today(), 'status' => TaxRate::STATUS_ACTIVE]);

        /** @var Product $product */
        $product = Product::query()->create(['category_id' => $category->getKey(), 'tax_rate_id' => $taxRate->getKey(), 'name' => $name, 'selling_price_minor' => 100, 'track_inventory' => true, 'allow_negative_stock' => false, 'status' => Product::STATUS_ACTIVE]);

        return $product;
    }

    private function createBranch(string $name, string $code): Branch
    {
        /** @var Branch $branch */
        $branch = Branch::query()->create(['name' => $name, 'code' => $code, 'status' => Branch::STATUS_ACTIVE]);

        return $branch;
    }

    private function membershipFor(User $user, \Modules\Identity\App\Models\Tenant $tenant): Membership
    {
        /** @var Membership $membership */
        $membership = Membership::query()->where('user_id', $user->getKey())->where('tenant_id', $tenant->getKey())->firstOrFail();

        return $membership;
    }
}
