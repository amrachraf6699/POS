<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use Carbon\CarbonImmutable;
use Modules\Catalog\App\Domain\Pricing\Data\PricingCalculationInput;
use Modules\Catalog\App\Domain\Pricing\Exceptions\PricingCalculationException;
use Modules\Catalog\App\Domain\Pricing\Services\VatInclusivePricingService;
use Modules\Catalog\App\Models\Category;
use Modules\Catalog\App\Models\Product;
use Modules\Catalog\App\Models\TaxRate;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Domain\Tenancy\TenantContextException;
use Modules\Identity\App\Models\Membership;
use Modules\Identity\App\Models\Tenant;
use Tests\Support\Tenancy\TenantIsolationTestCase;

class VatInclusivePricingServiceTest extends TenantIsolationTestCase
{
    private const CALCULATION_DATE = '2026-07-30';

    public function test_calculates_zero_hundred_and_fourteen_percent_vat_inclusive_lines(): void
    {
        [, $tenant, $membership] = $this->makeMembership();
        $this->setTenantContext($tenant, $membership);

        [$zeroVatProduct] = $this->createProduct(0, 125);
        [$hundredVatProduct] = $this->createProduct(10000, 100);
        [$fourteenVatProduct] = $this->createProduct(1400, 11400);

        $zeroVat = $this->calculate($zeroVatProduct, 2, 125);
        $hundredVat = $this->calculate($hundredVatProduct, 1, 100);
        $fourteenVat = $this->calculate($fourteenVatProduct, 1, 11400);

        $this->assertSame([250, 250, 0], [$zeroVat->grossMinor, $zeroVat->netMinor, $zeroVat->vatMinor]);
        $this->assertSame([100, 50, 50], [$hundredVat->grossMinor, $hundredVat->netMinor, $hundredVat->vatMinor]);
        $this->assertSame([11400, 10000, 1400], [$fourteenVat->grossMinor, $fourteenVat->netMinor, $fourteenVat->vatMinor]);
        $this->assertReconciles($zeroVat->grossMinor, $zeroVat->netMinor, $zeroVat->vatMinor);
        $this->assertReconciles($hundredVat->grossMinor, $hundredVat->netMinor, $hundredVat->vatMinor);
        $this->assertReconciles($fourteenVat->grossMinor, $fourteenVat->netMinor, $fourteenVat->vatMinor);
    }

    public function test_uses_half_up_rounding_once_per_line_for_non_even_amounts_and_multiple_quantities(): void
    {
        [, $tenant, $membership] = $this->makeMembership();
        $this->setTenantContext($tenant, $membership);

        [$halfUpProduct] = $this->createProduct(1200, 14);
        [$perLineProduct] = $this->createProduct(1400, 5);

        $halfUp = $this->calculate($halfUpProduct, 1, 14);
        $perLine = $this->calculate($perLineProduct, 3, 5);

        $this->assertSame([14, 12, 2], [$halfUp->grossMinor, $halfUp->netMinor, $halfUp->vatMinor]);
        $this->assertSame([15, 13, 2], [$perLine->grossMinor, $perLine->netMinor, $perLine->vatMinor]);
        $this->assertReconciles($halfUp->grossMinor, $halfUp->netMinor, $halfUp->vatMinor);
        $this->assertReconciles($perLine->grossMinor, $perLine->netMinor, $perLine->vatMinor);
    }

    public function test_rejects_invalid_quantities_and_expected_unit_prices(): void
    {
        [, $tenant, $membership] = $this->makeMembership();
        $this->setTenantContext($tenant, $membership);
        [$product] = $this->createProduct(1400, 100);

        $this->expectException(PricingCalculationException::class);
        $this->pricing()->calculate($this->input($product, 0, 100));
    }

    public function test_rejects_negative_quantities_and_expected_unit_prices(): void
    {
        [, $tenant, $membership] = $this->makeMembership();
        $this->setTenantContext($tenant, $membership);
        [$product] = $this->createProduct(1400, 100);

        try {
            $this->pricing()->calculate($this->input($product, -1, 100));
            $this->fail('A negative quantity must be rejected.');
        } catch (PricingCalculationException) {
        }

        $this->expectException(PricingCalculationException::class);
        $this->pricing()->calculate($this->input($product, 1, -1));
    }

    public function test_rejects_stale_prices_and_products_that_are_unavailable_or_deleted(): void
    {
        [, $tenant, $membership] = $this->makeMembership();
        $this->setTenantContext($tenant, $membership);
        [$staleProduct] = $this->createProduct(1400, 100);
        [$inactiveProduct] = $this->createProduct(1400, 100);
        [$deletedProduct] = $this->createProduct(1400, 100);

        $this->expectPricingFailure($this->input($staleProduct, 1, 99));
        $inactiveProduct->update(['status' => Product::STATUS_INACTIVE]);
        $this->expectPricingFailure($this->input($inactiveProduct, 1, 100));
        $deletedProduct->delete();
        $this->expectPricingFailure($this->input($deletedProduct, 1, 100));
    }

    public function test_rejects_inactive_and_expired_tax_rate_versions(): void
    {
        [, $tenant, $membership] = $this->makeMembership();
        $this->setTenantContext($tenant, $membership);
        [$inactiveProduct, $inactiveTaxRate] = $this->createProduct(1400, 100);
        [$expiredProduct] = $this->createProduct(1400, 100, '2026-01-01', '2026-07-30');

        $inactiveTaxRate->update(['status' => TaxRate::STATUS_INACTIVE]);

        $this->expectPricingFailure($this->input($inactiveProduct, 1, 100));
        $this->expectPricingFailure($this->input($expiredProduct, 1, 100));
    }

    public function test_rejects_cross_tenant_product_and_tax_rate_access(): void
    {
        [, $tenant, $membership] = $this->makeMembership();
        $this->setTenantContext($tenant, $membership);
        [$currentProduct] = $this->createProduct(1400, 100);

        [, $otherTenant, $otherMembership] = $this->makeMembership();
        $this->setTenantContext($otherTenant, $otherMembership);
        [$otherProduct, $otherTaxRate] = $this->createProduct(1400, 100);

        $this->setTenantContext($tenant, $membership);
        $this->expectPricingFailure($this->input($otherProduct, 1, 100));
        Product::query()->whereKey($currentProduct->getKey())->update(['tax_rate_id' => $otherTaxRate->getKey()]);
        /** @var Product $productWithCrossTenantTax */
        $productWithCrossTenantTax = Product::query()->findOrFail($currentProduct->getKey());
        $this->expectPricingFailure($this->input($productWithCrossTenantTax, 1, 100));
    }

    public function test_requires_a_tenant_context_and_refuses_integer_overflow(): void
    {
        [, $tenant, $membership] = $this->makeMembership();
        $this->setTenantContext($tenant, $membership);
        [$product] = $this->createProduct(0, PHP_INT_MAX);
        app(TenantContext::class)->clear();

        $this->expectException(TenantContextException::class);
        $this->pricing()->calculate($this->input($product, 1, PHP_INT_MAX));
    }

    public function test_refuses_integer_overflow_before_multiplication(): void
    {
        [, $tenant, $membership] = $this->makeMembership();
        $this->setTenantContext($tenant, $membership);
        [$product] = $this->createProduct(0, PHP_INT_MAX);

        $this->expectException(PricingCalculationException::class);
        $this->pricing()->calculate($this->input($product, 2, PHP_INT_MAX));
    }

    public function test_snapshot_remains_immutable_after_its_source_records_change(): void
    {
        [, $tenant, $membership] = $this->makeMembership();
        $this->setTenantContext($tenant, $membership);
        [$product, $taxRate] = $this->createProduct(1400, 11400, '2026-01-01', null, 'Coffee', 'COF-1', '123');

        $snapshot = $this->calculate($product, 2, 11400);
        $originalTaxRateName = $taxRate->getAttribute('name');
        $product->update(['name' => 'Changed coffee', 'selling_price_minor' => 22800, 'sku' => 'COF-2', 'barcode' => '456']);
        $taxRate->update(['name' => 'Changed VAT', 'rate_basis_points' => 0]);

        $this->assertSame($product->getKey(), $snapshot->productId);
        $this->assertSame('Coffee', $snapshot->productName);
        $this->assertSame('COF-1', $snapshot->productSku);
        $this->assertSame('123', $snapshot->productBarcode);
        $this->assertSame(11400, $snapshot->unitGrossMinor);
        $this->assertSame(22800, $snapshot->grossMinor);
        $this->assertSame(20000, $snapshot->netMinor);
        $this->assertSame(2800, $snapshot->vatMinor);
        $this->assertSame($taxRate->getKey(), $snapshot->taxRateId);
        $this->assertSame($originalTaxRateName, $snapshot->taxRateName);
        $this->assertSame(1400, $snapshot->taxRateBasisPoints);
        $this->assertTrue($snapshot->taxIncluded);
    }

    /** @return array{0: Product, 1: TaxRate} */
    private function createProduct(
        int $rateBasisPoints,
        int $sellingPriceMinor,
        string $effectiveFrom = '2026-01-01',
        ?string $effectiveTo = null,
        string $name = 'Product',
        ?string $sku = null,
        ?string $barcode = null,
    ): array {
        /** @var Category $category */
        $category = Category::query()->create(['name' => 'Category '.uniqid()]);
        /** @var TaxRate $taxRate */
        $taxRate = TaxRate::query()->create([
            'name' => 'VAT '.uniqid(),
            'rate_basis_points' => $rateBasisPoints,
            'effective_from' => $effectiveFrom,
            'effective_to' => $effectiveTo,
            'status' => TaxRate::STATUS_ACTIVE,
        ]);
        /** @var Product $product */
        $product = Product::query()->create([
            'category_id' => $category->getKey(),
            'tax_rate_id' => $taxRate->getKey(),
            'name' => $name,
            'sku' => $sku,
            'barcode' => $barcode,
            'cost_price_minor' => 0,
            'selling_price_minor' => $sellingPriceMinor,
            'track_inventory' => true,
            'low_stock_threshold' => 0,
            'allow_negative_stock' => false,
            'status' => Product::STATUS_ACTIVE,
        ]);

        return [$product, $taxRate];
    }

    private function calculate(Product $product, int $quantity, int $expectedUnitGrossMinor): \Modules\Catalog\App\Domain\Pricing\Data\PricedProductLineSnapshot
    {
        return $this->pricing()->calculate($this->input($product, $quantity, $expectedUnitGrossMinor));
    }

    private function input(Product $product, int $quantity, int $expectedUnitGrossMinor): PricingCalculationInput
    {
        return new PricingCalculationInput($product, $quantity, $expectedUnitGrossMinor, CarbonImmutable::parse(self::CALCULATION_DATE));
    }

    private function pricing(): VatInclusivePricingService
    {
        return app(VatInclusivePricingService::class);
    }

    private function setTenantContext(Tenant $tenant, Membership $membership): void
    {
        app(TenantContext::class)->set($tenant, $membership);
    }

    private function expectPricingFailure(PricingCalculationInput $input): void
    {
        try {
            $this->pricing()->calculate($input);
            $this->fail('The pricing calculation should have been rejected.');
        } catch (PricingCalculationException) {
            $this->addToAssertionCount(1);
        }
    }

    private function assertReconciles(int $grossMinor, int $netMinor, int $vatMinor): void
    {
        $this->assertSame($grossMinor, $netMinor + $vatMinor);
    }
}
