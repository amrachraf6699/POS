<?php

namespace Tests\Feature\Catalog;

use App\Http\Middleware\VerifyCsrfToken;
use Modules\Catalog\App\Models\Category;
use Modules\Catalog\App\Models\Product;
use Modules\Catalog\App\Models\TaxRate;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Tests\Support\Tenancy\TenantIsolationTestCase;

class ProductManagementTest extends TenantIsolationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    public function test_owner_can_create_deactivate_and_soft_delete_a_product(): void
    {
        [$owner, $tenant, $membership] = $this->makeMembership();
        app(TenantContext::class)->set($tenant, $membership);
        /** @var Category $category */
        $category = Category::query()->create(['name' => 'مشروبات']);
        /** @var TaxRate $taxRate */
        $taxRate = TaxRate::query()->create(['name' => 'ضريبة', 'rate_basis_points' => 1400, 'effective_from' => today(), 'status' => TaxRate::STATUS_ACTIVE]);
        $session = ['current_tenant_id' => $tenant->getKey()];
        $payload = $this->payload($category, $taxRate);

        $this->actingAs($owner)->withSession($session)->post('/tenant/catalog/products', $payload)->assertRedirect();
        $this->assertDatabaseHas('products', ['tenant_id' => $tenant->getKey(), 'sku' => 'COF-1', 'selling_price_minor' => 250]);
        /** @var Product $product */
        $product = Product::query()->firstOrFail();
        $this->assertTrue($product->isSaleAvailable());

        $this->actingAs($owner)->withSession($session)->post('/tenant/catalog/products/'.$product->getKey().'/deactivate')->assertRedirect();
        /** @var Product $inactive */
        $inactive = $product->fresh();
        $this->assertFalse($inactive->isSaleAvailable());
        $this->actingAs($owner)->withSession($session)->delete('/tenant/catalog/products/'.$product->getKey())->assertRedirect();
        $this->assertSoftDeleted('products', ['id' => $product->getKey()]);
    }

    public function test_product_identifiers_and_source_records_are_tenant_scoped(): void
    {
        [$owner, $tenant, $membership] = $this->makeMembership();
        app(TenantContext::class)->set($tenant, $membership);
        /** @var Category $category */
        $category = Category::query()->create(['name' => 'أغذية']);
        /** @var TaxRate $taxRate */
        $taxRate = TaxRate::query()->create(['name' => 'ضريبة', 'rate_basis_points' => 1400, 'effective_from' => today(), 'status' => TaxRate::STATUS_ACTIVE]);
        $session = ['current_tenant_id' => $tenant->getKey()];
        $this->actingAs($owner)->withSession($session)->post('/tenant/catalog/products', $this->payload($category, $taxRate))->assertRedirect();
        $this->actingAs($owner)->withSession($session)->post('/tenant/catalog/products', $this->payload($category, $taxRate))->assertSessionHasErrors(['sku', 'barcode']);

        [$otherOwner, $otherTenant, $otherMembership] = $this->makeMembership();
        app(TenantContext::class)->set($otherTenant, $otherMembership);
        /** @var Category $otherCategory */
        $otherCategory = Category::query()->create(['name' => 'أخرى']);
        /** @var TaxRate $otherTax */
        $otherTax = TaxRate::query()->create(['name' => 'ضريبة', 'rate_basis_points' => 1400, 'effective_from' => today(), 'status' => TaxRate::STATUS_ACTIVE]);
        $this->actingAs($otherOwner)->withSession(['current_tenant_id' => $otherTenant->getKey()])->post('/tenant/catalog/products', $this->payload($otherCategory, $otherTax))->assertRedirect();
        $this->assertSame(2, Product::query()->withoutGlobalScopes()->count());
    }

    private function payload(Category $category, TaxRate $taxRate): array
    {
        return ['name' => 'قهوة', 'category_id' => $category->getKey(), 'tax_rate_id' => $taxRate->getKey(), 'sku' => 'COF-1', 'barcode' => '123', 'cost_price_minor' => 100, 'selling_price_minor' => 250, 'track_inventory' => '1', 'low_stock_threshold' => 3, 'allow_negative_stock' => '0'];
    }
}
