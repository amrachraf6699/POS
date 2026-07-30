<?php

declare(strict_types=1);

namespace Modules\Catalog\App\Domain\Pricing\Services;

use Illuminate\Database\Eloquent\SoftDeletingScope;
use Modules\Catalog\App\Domain\Pricing\Data\PricedProductLineSnapshot;
use Modules\Catalog\App\Domain\Pricing\Data\PricingCalculationInput;
use Modules\Catalog\App\Domain\Pricing\Exceptions\PricingCalculationException;
use Modules\Catalog\App\Models\Product;
use Modules\Catalog\App\Models\TaxRate;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Domain\Tenancy\TenantContextException;

final class VatInclusivePricingService
{
    private const BASIS_POINTS_PER_WHOLE = 10000;

    private const MAX_RATE_BASIS_POINTS = 10000;

    public function __construct(private readonly TenantContext $tenantContext) {}

    public function calculate(PricingCalculationInput $input): PricedProductLineSnapshot
    {
        if (! $this->tenantContext->hasTenant()) {
            throw new TenantContextException('A tenant context must be established before calculating a product price.');
        }

        $tenantId = $this->tenantContext->id();
        $this->assertPositiveInteger($input->quantity, 'The quantity must be a positive integer.');
        $this->assertNonNegativeInteger($input->expectedUnitGrossMinor, 'The expected unit gross price must be a non-negative integer.');

        $suppliedProductId = $input->product->getKey();
        if (! is_numeric($suppliedProductId) || (int) $suppliedProductId <= 0) {
            throw new PricingCalculationException('The product must be persisted before its price can be calculated.');
        }
        if ((int) $input->product->getAttribute('tenant_id') !== $tenantId) {
            throw new PricingCalculationException('The product does not belong to the current tenant.');
        }

        /** @var Product|null $product */
        $product = Product::query()->withoutGlobalScope(SoftDeletingScope::class)->whereKey($suppliedProductId)->first();
        if (! $product instanceof Product || ! $product->isSaleAvailable()) {
            throw new PricingCalculationException('The product is not available for sale.');
        }

        $unitGrossMinor = $product->getAttribute('selling_price_minor');
        $this->assertNonNegativeInteger($unitGrossMinor, 'The product unit gross price must be a non-negative integer.');
        if ($unitGrossMinor !== $input->expectedUnitGrossMinor) {
            throw new PricingCalculationException('The displayed unit gross price is stale.');
        }

        $taxRateId = $product->getAttribute('tax_rate_id');
        if (! is_numeric($taxRateId) || (int) $taxRateId <= 0) {
            throw new PricingCalculationException('The product does not have a valid tax rate.');
        }
        /** @var TaxRate|null $taxRate */
        $taxRate = TaxRate::query()->whereKey($taxRateId)->first();
        if (! $taxRate instanceof TaxRate || ! $taxRate->isEffectiveOn($input->calculationDate)) {
            throw new PricingCalculationException('The product tax rate is not active and effective on the calculation date.');
        }

        $rateBasisPoints = $taxRate->getAttribute('rate_basis_points');
        $this->assertNonNegativeInteger($rateBasisPoints, 'The tax rate must be a non-negative integer.');
        if ($rateBasisPoints > self::MAX_RATE_BASIS_POINTS) {
            throw new PricingCalculationException('The tax rate exceeds the supported VAT range.');
        }

        $grossMinor = $this->multiplySafely($unitGrossMinor, $input->quantity, 'The gross line total exceeds the supported integer range.');
        $vatMinor = $this->calculateVatMinor($grossMinor, $rateBasisPoints);
        $netMinor = $grossMinor - $vatMinor;

        return new PricedProductLineSnapshot(
            productId: (int) $product->getKey(),
            productName: (string) $product->getAttribute('name'),
            productSku: $this->nullableString($product->getAttribute('sku')),
            productBarcode: $this->nullableString($product->getAttribute('barcode')),
            quantity: $input->quantity,
            unitGrossMinor: $unitGrossMinor,
            grossMinor: $grossMinor,
            netMinor: $netMinor,
            vatMinor: $vatMinor,
            taxRateId: (int) $taxRate->getKey(),
            taxRateName: (string) $taxRate->getAttribute('name'),
            taxRateBasisPoints: $rateBasisPoints,
        );
    }

    private function calculateVatMinor(int $grossMinor, int $rateBasisPoints): int
    {
        if ($grossMinor === 0 || $rateBasisPoints === 0) {
            return 0;
        }

        $denominator = self::BASIS_POINTS_PER_WHOLE + $rateBasisPoints;
        $wholeUnits = intdiv($grossMinor, $denominator);
        $remainder = $grossMinor % $denominator;
        $wholeVat = $this->multiplySafely($wholeUnits, $rateBasisPoints, 'The VAT total exceeds the supported integer range.');
        $fractionNumerator = $this->multiplySafely($remainder, $rateBasisPoints, 'The VAT total exceeds the supported integer range.');
        $fractionVat = intdiv($fractionNumerator, $denominator);

        if (($fractionNumerator % $denominator) * 2 >= $denominator) {
            $fractionVat++;
        }
        if ($wholeVat > PHP_INT_MAX - $fractionVat) {
            throw new PricingCalculationException('The VAT total exceeds the supported integer range.');
        }

        return $wholeVat + $fractionVat;
    }

    private function multiplySafely(int $left, int $right, string $message): int
    {
        if ($left !== 0 && $right > intdiv(PHP_INT_MAX, $left)) {
            throw new PricingCalculationException($message);
        }

        return $left * $right;
    }

    private function assertPositiveInteger(int $value, string $message): void
    {
        if ($value <= 0) {
            throw new PricingCalculationException($message);
        }
    }

    private function assertNonNegativeInteger(mixed $value, string $message): void
    {
        if (! is_int($value) || $value < 0) {
            throw new PricingCalculationException($message);
        }
    }

    private function nullableString(mixed $value): ?string
    {
        return $value === null ? null : (string) $value;
    }
}
