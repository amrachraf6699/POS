<?php

namespace Tests\Feature\Inventory;

use Modules\Business\App\Models\Branch;
use Modules\Business\App\Models\BranchAssignment;
use Modules\Catalog\App\Models\Category;
use Modules\Catalog\App\Models\Product;
use Modules\Catalog\App\Models\TaxRate;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Models\Membership;
use Modules\Identity\App\Models\User;
use Modules\Inventory\App\Models\InventoryBalance;
use Tests\Support\Tenancy\TenantIsolationTestCase;

class LowStockPageTest extends TenantIsolationTestCase
{
    public function test_authorized_owner_sees_only_persisted_low_stock_balances_and_can_filter_by_branch(): void
    {
        [$owner, $tenant, $membership] = $this->makeMembership();
        app(TenantContext::class)->set($tenant, $membership);
        $cairo = $this->branch('Cairo', 'CAI');
        $giza = $this->branch('Giza', 'GIZ');
        $low = $this->product('Coffee', 3);
        $above = $this->product('Tea', 3);
        $withoutBalance = $this->product('No balance', 3);
        $this->balance($cairo, $low, 3);
        $this->balance($giza, $above, 4);

        $response = $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->get(route('inventory.low-stock.index', ['branch' => $cairo->getKey()]));

        $response->assertOk()
            ->assertSee('الأصناف منخفضة المخزون')
            ->assertSee('Cairo')
            ->assertSee('Coffee')
            ->assertDontSee('Tea')
            ->assertDontSee('No balance')
            ->assertSee(route('inventory.balances.index'), false);

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->get(route('inventory.low-stock.index', ['branch' => 999999]))
            ->assertRedirect()
            ->assertSessionHasErrors('branch');
    }

    public function test_inventory_staff_can_only_view_low_stock_for_assigned_branches_and_unassigned_filters_are_forbidden(): void
    {
        [$owner, $tenant, $membership] = $this->makeMembership();
        app(TenantContext::class)->set($tenant, $membership);
        $assignedBranch = $this->branch('Cairo', 'CAI');
        $unassignedBranch = $this->branch('Giza', 'GIZ');
        $assignedProduct = $this->product('Assigned coffee', 5);
        $unassignedProduct = $this->product('Unassigned coffee', 5);
        $this->balance($assignedBranch, $assignedProduct, 5);
        $this->balance($unassignedBranch, $unassignedProduct, 5);
        /** @var User $staff */
        $staff = User::factory()->create();
        Membership::factory()->create(['tenant_id' => $tenant->getKey(), 'user_id' => $staff->getKey(), 'role' => Membership::ROLE_INVENTORY_STAFF]);
        BranchAssignment::query()->create(['branch_id' => $assignedBranch->getKey(), 'user_id' => $staff->getKey(), 'status' => BranchAssignment::STATUS_ACTIVE]);

        $this->actingAs($staff)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->get(route('inventory.low-stock.index'))
            ->assertOk()
            ->assertSee('Assigned coffee')
            ->assertDontSee('Unassigned coffee');
        $this->actingAs($staff)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->get(route('inventory.low-stock.index', ['branch' => $unassignedBranch->getKey()]))
            ->assertForbidden();
    }

    public function test_low_stock_page_is_permission_gated_and_does_not_expose_another_tenant(): void
    {
        [$owner, $tenant, $membership] = $this->makeMembership();
        /** @var User $staff */
        $staff = User::factory()->create();
        Membership::factory()->create(['tenant_id' => $tenant->getKey(), 'user_id' => $staff->getKey(), 'role' => Membership::ROLE_CASHIER]);
        $this->actingAs($staff)->withSession(['current_tenant_id' => $tenant->getKey()])->get(route('inventory.low-stock.index'))->assertForbidden();

        app(TenantContext::class)->set($tenant, $membership);
        [$otherOwner, $otherTenant, $otherMembership] = $this->makeMembership();
        app(TenantContext::class)->set($otherTenant, $otherMembership);
        $otherBranch = $this->branch('Alexandria', 'ALX');
        $otherProduct = $this->product('Other tenant coffee', 5);
        $this->balance($otherBranch, $otherProduct, 5);

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->get(route('inventory.low-stock.index'))
            ->assertOk()
            ->assertDontSee('Other tenant coffee')
            ->assertSee('لا توجد أصناف منخفضة المخزون حالياً.');
    }

    private function branch(string $name, string $code): Branch
    {
        /** @var Branch $branch */
        $branch = Branch::query()->create(['name' => $name, 'code' => $code, 'status' => Branch::STATUS_ACTIVE]);

        return $branch;
    }

    private function product(string $name, int $threshold): Product
    {
        $category = Category::query()->create(['name' => $name.' category']);
        $taxRate = TaxRate::query()->create(['name' => $name.' tax', 'rate_basis_points' => 1400, 'effective_from' => today(), 'status' => TaxRate::STATUS_ACTIVE]);

        /** @var Product $product */
        $product = Product::query()->create(['category_id' => $category->getKey(), 'tax_rate_id' => $taxRate->getKey(), 'name' => $name, 'selling_price_minor' => 100, 'track_inventory' => true, 'low_stock_threshold' => $threshold, 'status' => Product::STATUS_ACTIVE]);

        return $product;
    }

    private function balance(Branch $branch, Product $product, int $quantity): void
    {
        $balance = new InventoryBalance;
        $balance->forceFill(['branch_id' => $branch->getKey(), 'product_id' => $product->getKey(), 'quantity_on_hand' => $quantity]);
        $balance->save();
    }
}
