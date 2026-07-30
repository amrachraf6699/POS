<?php

namespace Tests\Feature\Inventory;

use Illuminate\Support\Facades\DB;
use Modules\Business\App\Models\Branch;
use Modules\Catalog\App\Models\Category;
use Modules\Catalog\App\Models\Product;
use Modules\Catalog\App\Models\TaxRate;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Models\Membership;
use Modules\Identity\App\Models\User;
use Modules\Inventory\App\Models\InventoryBalance;
use Tests\Support\Tenancy\TenantIsolationTestCase;

class InventoryBalancePageTest extends TenantIsolationTestCase
{
    public function test_authorized_user_can_filter_tenant_balances_and_see_the_inventory_navigation_link(): void
    {
        /** @var array{0: User, 1: \Modules\Identity\App\Models\Tenant, 2: Membership} $membershipData */
        $membershipData = $this->makeMembership();
        [$owner, $tenant, $membership] = $membershipData;
        app(TenantContext::class)->set($tenant, $membership);
        $branch = $this->createBranch('Cairo', 'CAI');
        $product = $this->createProduct('Coffee');
        $balance = new InventoryBalance;
        $balance->forceFill(['tenant_id' => $tenant->getKey(), 'branch_id' => $branch->getKey(), 'product_id' => $product->getKey(), 'quantity_on_hand' => 12]);
        $balance->save();
        DB::table('tenant_onboardings')->insert([
            'tenant_id' => $tenant->getKey(),
            'first_branch_id' => $branch->getKey(),
            'settings_completed_at' => now(),
            'staff_setup_completed_at' => now(),
            'completed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->get(route('inventory.balances.index', ['branch' => $branch->getKey(), 'product' => $product->getKey()]));

        $response->assertOk()
            ->assertSee('أرصدة المخزون')
            ->assertSee('Cairo')
            ->assertSee('Coffee')
            ->assertSee('12')
            ->assertSee(route('inventory.balances.index'))
            ->assertSee(route('inventory.low-stock.index'))
            ->assertSee('bg-[#eef0ff] text-[#3548c9]', false)
            ->assertSee(route('inventory.adjustments.opening.create'), false)
            ->assertSee(route('inventory.adjustments.index'), false);
    }

    public function test_page_is_forbidden_without_inventory_view_and_never_exposes_another_tenant_balance(): void
    {
        /** @var array{0: User, 1: \Modules\Identity\App\Models\Tenant, 2: Membership} $membershipData */
        $membershipData = $this->makeMembership();
        [$owner, $tenant, $membership] = $membershipData;
        /** @var User $member */
        $member = User::factory()->create();
        Membership::factory()->create(['tenant_id' => $tenant->getKey(), 'user_id' => $member->getKey(), 'role' => 'staff']);

        $this->actingAs($member)->withSession(['current_tenant_id' => $tenant->getKey()])->get('/tenant/dashboard')
            ->assertOk()
            ->assertDontSee(route('inventory.balances.index'));
        $this->actingAs($member)->withSession(['current_tenant_id' => $tenant->getKey()])->get('/tenant/dashboard')
            ->assertDontSee(route('inventory.low-stock.index'));
        $this->actingAs($member)->withSession(['current_tenant_id' => $tenant->getKey()])->get(route('inventory.balances.index'))->assertForbidden();

        app(TenantContext::class)->set($tenant, $membership);
        [$otherOwner, $otherTenant, $otherMembership] = $this->makeMembership();
        app(TenantContext::class)->set($otherTenant, $otherMembership);
        $otherBranch = $this->createBranch('Alexandria', 'ALX');
        $otherProduct = $this->createProduct('Other coffee');
        $otherBalance = new InventoryBalance;
        $otherBalance->forceFill(['tenant_id' => $otherTenant->getKey(), 'branch_id' => $otherBranch->getKey(), 'product_id' => $otherProduct->getKey(), 'quantity_on_hand' => 44]);
        $otherBalance->save();

        $response = $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])->get(route('inventory.balances.index'));
        $response->assertOk()->assertDontSee('Alexandria')->assertDontSee('Other coffee')->assertSee('لا توجد أرصدة مخزون مسجلة بعد.');
    }

    private function createProduct(string $name): Product
    {
        $category = Category::query()->create(['name' => $name.' category']);
        $taxRate = TaxRate::query()->create(['name' => $name.' tax', 'rate_basis_points' => 1400, 'effective_from' => today(), 'status' => TaxRate::STATUS_ACTIVE]);

        /** @var Product $product */
        $product = Product::query()->create(['category_id' => $category->getKey(), 'tax_rate_id' => $taxRate->getKey(), 'name' => $name, 'selling_price_minor' => 100, 'track_inventory' => true, 'status' => Product::STATUS_ACTIVE]);

        return $product;
    }

    private function createBranch(string $name, string $code): Branch
    {
        /** @var Branch $branch */
        $branch = Branch::query()->create(['name' => $name, 'code' => $code, 'status' => Branch::STATUS_ACTIVE]);

        return $branch;
    }
}
