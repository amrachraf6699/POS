<?php

namespace Tests\Feature\Inventory;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use LogicException;
use Modules\Business\App\Models\Branch;
use Modules\Catalog\App\Models\Category;
use Modules\Catalog\App\Models\Product;
use Modules\Catalog\App\Models\TaxRate;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Models\Membership;
use Modules\Inventory\App\Actions\RecordInventoryMovementAction;
use Modules\Inventory\App\Domain\Data\RecordInventoryMovementData;
use Modules\Inventory\App\Domain\Enums\InventoryMovementType;
use Modules\Inventory\App\Domain\Exceptions\InventoryMovementException;
use Modules\Inventory\App\Models\InventoryMovement;
use RuntimeException;
use Tests\Support\Tenancy\TenantIsolationTestCase;

class InventoryLedgerAndBalancesTest extends TenantIsolationTestCase
{
    public function test_inbound_and_outbound_movements_update_the_immutable_ledger_and_balance(): void
    {
        [$owner, $tenant, $branch, $product] = $this->inventoryFixture();

        $opening = $this->record($owner, $tenant, $branch, $product, InventoryMovementType::Opening, 10, 'opening-1');
        $adjustment = $this->record($owner, $tenant, $branch, $product, InventoryMovementType::AdjustmentIn, 3, 'adjustment-1');
        $sale = $this->record($owner, $tenant, $branch, $product, InventoryMovementType::SaleOut, 4, 'sale-1');

        $this->assertSame(10, $opening->balanceAfter);
        $this->assertSame(13, $adjustment->balanceAfter);
        $this->assertSame(-4, $sale->quantityDelta);
        $this->assertSame(9, $sale->balanceAfter);
        $this->assertDatabaseHas('inventory_balances', ['tenant_id' => $tenant->getKey(), 'branch_id' => $branch->getKey(), 'product_id' => $product->getKey(), 'quantity_on_hand' => 9]);
        $this->assertDatabaseCount('inventory_movements', 3);
        $this->assertDatabaseHas('inventory_movements', ['id' => $sale->movementId, 'type' => InventoryMovementType::SaleOut->value, 'quantity' => 4, 'quantity_delta' => -4, 'balance_after' => 9]);

        $this->expectException(LogicException::class);
        InventoryMovement::query()->findOrFail($sale->movementId)->forceFill(['balance_after' => 99])->save();
    }

    public function test_invalid_movements_fail_without_writing_a_balance_or_ledger_row(): void
    {
        [$owner, $tenant, $branch, $product] = $this->inventoryFixture();

        $this->expectException(InventoryMovementException::class);
        try {
            $this->record($owner, $tenant, $branch, $product, InventoryMovementType::Opening, 0, 'invalid-quantity');
        } finally {
            $this->assertDatabaseCount('inventory_balances', 0);
            $this->assertDatabaseCount('inventory_movements', 0);
        }
    }

    public function test_inactive_or_deleted_sources_untracked_products_cross_tenant_pairs_and_insufficient_stock_fail_safely(): void
    {
        [$owner, $tenant, $branch, $product] = $this->inventoryFixture();
        $branch->update(['status' => Branch::STATUS_INACTIVE]);

        foreach ([
            fn () => $this->record($owner, $tenant, $branch, $product, InventoryMovementType::Opening, 1, 'inactive-branch'),
            function () use ($owner, $tenant, $product): void {
                $activeBranch = $this->createBranch('Active branch', 'ACTIVE');
                $product->update(['track_inventory' => false]);
                $this->record($owner, $tenant, $activeBranch, $product, InventoryMovementType::Opening, 1, 'untracked-product');
            },
        ] as $attempt) {
            try {
                $attempt();
                $this->fail('The invalid inventory movement was recorded.');
            } catch (InventoryMovementException) {
                // Expected: the mutation boundary rejects invalid stock records.
            }
        }

        $product->update(['track_inventory' => true]);
        $activeBranch = $this->createBranch('Second branch', 'SECOND');
        $product->update(['status' => Product::STATUS_INACTIVE]);
        try {
            $this->record($owner, $tenant, $activeBranch, $product, InventoryMovementType::Opening, 1, 'inactive-product');
            $this->fail('The inactive product was accepted.');
        } catch (InventoryMovementException) {
            $this->assertDatabaseCount('inventory_movements', 0);
        }
        $product->update(['status' => Product::STATUS_ACTIVE]);
        $deletedProduct = $this->createProduct('Deleted product');
        $deletedProduct->delete();
        try {
            $this->record($owner, $tenant, $activeBranch, $deletedProduct, InventoryMovementType::Opening, 1, 'deleted-product');
            $this->fail('The deleted product was accepted.');
        } catch (InventoryMovementException) {
            $this->assertDatabaseCount('inventory_movements', 0);
        }
        $this->record($owner, $tenant, $activeBranch, $product, InventoryMovementType::Opening, 2, 'stock-in');

        try {
            $this->record($owner, $tenant, $activeBranch, $product, InventoryMovementType::AdjustmentOut, 3, 'insufficient-stock');
            $this->fail('The negative stock movement was recorded.');
        } catch (InventoryMovementException) {
            $this->assertDatabaseCount('inventory_movements', 1);
        }

        [$otherOwner, $otherTenant, $otherMembership] = $this->makeMembership();
        app(TenantContext::class)->set($otherTenant, $otherMembership);
        $otherProduct = $this->createProduct('Other product');
        app(TenantContext::class)->set($tenant, $this->membershipFor($owner, $tenant));

        try {
            $this->record($owner, $tenant, $activeBranch, $otherProduct, InventoryMovementType::Opening, 1, 'cross-tenant-product');
            $this->fail('The cross-tenant product was accepted.');
        } catch (InventoryMovementException) {
            $this->assertDatabaseCount('inventory_movements', 1);
            $this->assertDatabaseCount('inventory_balances', 1);
        }
    }

    public function test_mutation_authorization_boundary_rejects_users_without_inventory_adjust_permission(): void
    {
        [$owner, $tenant, $branch, $product] = $this->inventoryFixture();
        [$member, , $memberMembership] = $this->makeMembership(Membership::ROLE_CASHIER);
        $memberMembership->update(['tenant_id' => $tenant->getKey()]);
        app(TenantContext::class)->set($tenant, $memberMembership);

        $this->expectException(AuthorizationException::class);
        try {
            $this->record($member, $tenant, $branch, $product, InventoryMovementType::Opening, 1, 'unauthorized');
        } finally {
            app(TenantContext::class)->set($tenant, $this->membershipFor($owner, $tenant));
            $this->assertDatabaseCount('inventory_movements', 0);
        }
    }

    public function test_retry_creates_only_one_movement_and_returns_the_original_result(): void
    {
        [$owner, $tenant, $branch, $product] = $this->inventoryFixture();

        $first = $this->record($owner, $tenant, $branch, $product, InventoryMovementType::Opening, 7, 'retry-key');
        $retry = $this->record($owner, $tenant, $branch, $product, InventoryMovementType::AdjustmentIn, 99, 'retry-key');

        $this->assertFalse($first->wasIdempotent);
        $this->assertTrue($retry->wasIdempotent);
        $this->assertSame($first->movementId, $retry->movementId);
        $this->assertSame(7, $retry->balanceAfter);
        $this->assertDatabaseCount('inventory_movements', 1);
        $this->assertDatabaseHas('inventory_balances', ['branch_id' => $branch->getKey(), 'product_id' => $product->getKey(), 'quantity_on_hand' => 7]);
    }

    public function test_failed_ledger_insert_rolls_back_the_new_balance_row(): void
    {
        [$owner, $tenant, $branch, $product] = $this->inventoryFixture();
        DB::listen(function ($query): void {
            if (str_contains(strtolower($query->sql), 'insert into "inventory_movements"')) {
                throw new RuntimeException('Forced inventory movement failure.');
            }
        });

        $this->expectException(RuntimeException::class);
        try {
            $this->record($owner, $tenant, $branch, $product, InventoryMovementType::Opening, 5, 'rollback-key');
        } finally {
            $this->assertDatabaseCount('inventory_movements', 0);
            $this->assertDatabaseCount('inventory_balances', 0);
        }
    }

    /** @return array{0: \Modules\Identity\App\Models\User, 1: \Modules\Identity\App\Models\Tenant, 2: Branch, 3: Product} */
    private function inventoryFixture(): array
    {
        [$owner, $tenant, $membership] = $this->makeMembership();
        app(TenantContext::class)->set($tenant, $membership);
        $branch = $this->createBranch('Main branch', 'MAIN');

        return [$owner, $tenant, $branch, $this->createProduct('Tracked product')];
    }

    private function createProduct(string $name): Product
    {
        $category = Category::query()->create(['name' => $name.' category']);
        $taxRate = TaxRate::query()->create(['name' => $name.' tax', 'rate_basis_points' => 1400, 'effective_from' => today(), 'status' => TaxRate::STATUS_ACTIVE]);

        /** @var Product $product */
        $product = Product::query()->create([
            'category_id' => $category->getKey(),
            'tax_rate_id' => $taxRate->getKey(),
            'name' => $name,
            'selling_price_minor' => 100,
            'track_inventory' => true,
            'allow_negative_stock' => false,
            'status' => Product::STATUS_ACTIVE,
        ]);

        return $product;
    }

    private function createBranch(string $name, string $code): Branch
    {
        /** @var Branch $branch */
        $branch = Branch::query()->create(['name' => $name, 'code' => $code, 'status' => Branch::STATUS_ACTIVE]);

        return $branch;
    }

    private function record(\Modules\Identity\App\Models\User $actor, \Modules\Identity\App\Models\Tenant $tenant, Branch $branch, Product $product, InventoryMovementType $type, int $quantity, string $key): \Modules\Inventory\App\Domain\Data\InventoryMovementResult
    {
        return app(RecordInventoryMovementAction::class)->execute($actor, $tenant, new RecordInventoryMovementData($branch->getKey(), $product->getKey(), $type, $quantity, $key));
    }

    private function membershipFor($user, $tenant): Membership
    {
        /** @var Membership $membership */
        $membership = Membership::query()->where('user_id', $user->getKey())->where('tenant_id', $tenant->getKey())->firstOrFail();

        return $membership;
    }
}
