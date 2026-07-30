<?php

declare(strict_types=1);

namespace Modules\Catalog\App\Domain\Pricing\Data;

use Carbon\CarbonImmutable;
use Modules\Catalog\App\Models\Product;

final class PricingCalculationInput
{
    public function __construct(
        public readonly Product $product,
        public readonly int $quantity,
        public readonly int $expectedUnitGrossMinor,
        public readonly CarbonImmutable $calculationDate,
    ) {}
}
