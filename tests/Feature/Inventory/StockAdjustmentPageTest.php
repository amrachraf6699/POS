<?php

namespace Tests\Feature\Inventory;

use Illuminate\Support\Str;
use Modules\Business\App\Models\Branch;
use Modules\Catalog\App\Models\Category;
use Modules\Catalog\App\Models\Product;
use Modules\Catalog\App\Models\TaxRate;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Models\Membership;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;
use Modules\Inventory\App\Models\StockAdjustment;
use Tests\Support\Tenancy\TenantIsolationTestCase;

class StockAdjustmentPageTest extends TenantIsolationTestCase
{
    public function test_authorized_user_can_navigate_from_balances_to_arabic_stock_adjustment_pages(): void
    {
        [$owner, $tenant, $branch, $product] = $this->fixture();

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->get(route('inventory.balances.index'))
            ->assertOk()
            ->assertSee(route('inventory.adjustments.opening.create'), false)
            ->assertSee(route('inventory.adjustments.index'), false);

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->get(route('inventory.adjustments.opening.create'))
            ->assertOk()
            ->assertSee('إدخال الرصيد الافتتاحي')
            ->assertSee('name="type" value="opening"', false)
            ->assertSee($branch->name)
            ->assertSee($product->name);

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->get(route('inventory.adjustments.create'))
            ->assertOk()
            ->assertSee('تسوية المخزون')
            ->assertSee('زيادة المخزون')
            ->assertSee('نقص المخزون');
    }

    public function test_posted_document_has_required_reason_and_an_immutable_detail_page(): void
    {
        [$owner, $tenant, $branch, $product] = $this->fixture();
        $session = ['current_tenant_id' => $tenant->getKey()];

        $this->actingAs($owner)->withSession($session)->post(route('inventory.adjustments.opening.store'), [
            'branch_id' => $branch->getKey(), 'type' => 'opening', 'reason' => '', 'items' => [['product_id' => $product->getKey(), 'quantity' => 3]], 'idempotency_key' => (string) Str::uuid(),
        ])->assertSessionHasErrors('reason');

        $response = $this->actingAs($owner)->withSession($session)->post(route('inventory.adjustments.opening.store'), [
            'branch_id' => $branch->getKey(), 'type' => 'opening', 'reason' => 'بضاعة بداية التشغيل', 'items' => [['product_id' => $product->getKey(), 'quantity' => 3]], 'idempotency_key' => (string) Str::uuid(),
        ]);
        /** @var StockAdjustment $adjustment */
        $adjustment = StockAdjustment::query()->firstOrFail();
        $response->assertRedirect(route('inventory.adjustments.show', $adjustment));

        $this->actingAs($owner)->withSession($session)->get(route('inventory.adjustments.show', $adjustment))
            ->assertOk()->assertSee('تفاصيل المستند')->assertSee('بضاعة بداية التشغيل')->assertSee($product->name)
            ->assertDontSee('/edit', false)->assertDontSee('method="DELETE"', false);
    }

    public function test_users_without_inventory_permissions_are_denied_direct_adjustment_routes(): void
    {
        [$owner, $tenant, $branch, $product] = $this->fixture();
        /** @var User $cashier */
        $cashier = User::factory()->create();
        Membership::factory()->create(['tenant_id' => $tenant->getKey(), 'user_id' => $cashier->getKey(), 'role' => Membership::ROLE_CASHIER]);
        $session = ['current_tenant_id' => $tenant->getKey()];

        $this->actingAs($cashier)->withSession($session)->get(route('inventory.adjustments.opening.create'))->assertForbidden();
        $this->actingAs($cashier)->withSession($session)->post(route('inventory.adjustments.store'), [
            'branch_id' => $branch->getKey(), 'type' => 'adjustment_in', 'reason' => 'not allowed', 'items' => [['product_id' => $product->getKey(), 'quantity' => 1]], 'idempotency_key' => (string) Str::uuid(),
        ])->assertForbidden();
    }

    public function test_document_route_binding_never_exposes_another_tenant_document(): void
    {
        [$owner, $tenant] = $this->fixture();
        [$otherOwner, $otherTenant, $otherBranch, $otherProduct] = $this->fixture();
        app(TenantContext::class)->set($otherTenant, Membership::query()->where('tenant_id', $otherTenant->getKey())->where('user_id', $otherOwner->getKey())->firstOrFail());
        $otherAdjustment = new StockAdjustment;
        $otherAdjustment->forceFill(['tenant_id' => $otherTenant->getKey(), 'branch_id' => $otherBranch->getKey(), 'type' => 'opening', 'reason' => 'other tenant', 'actor_user_id' => $otherOwner->getKey(), 'posted_at' => now(), 'idempotency_key' => 'other-'.Str::uuid()]);
        $otherAdjustment->save();

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->get(route('inventory.adjustments.show', $otherAdjustment))->assertNotFound();
    }

    /** @return array{0: User, 1: Tenant, 2: Branch, 3: Product} */
    private function fixture(): array
    {
        [$owner, $tenant, $membership] = $this->makeMembership();
        app(TenantContext::class)->set($tenant, $membership);
        /** @var Branch $branch */
        $branch = Branch::query()->create(['name' => 'Cairo', 'code' => 'CAI', 'status' => Branch::STATUS_ACTIVE]);
        $category = Category::query()->create(['name' => 'Coffee category']);
        $taxRate = TaxRate::query()->create(['name' => 'Coffee tax', 'rate_basis_points' => 1400, 'effective_from' => today(), 'status' => TaxRate::STATUS_ACTIVE]);
        /** @var Product $product */
        $product = Product::query()->create(['category_id' => $category->getKey(), 'tax_rate_id' => $taxRate->getKey(), 'name' => 'Coffee', 'selling_price_minor' => 100, 'track_inventory' => true, 'status' => Product::STATUS_ACTIVE]);

        return [$owner, $tenant, $branch, $product];
    }
}
