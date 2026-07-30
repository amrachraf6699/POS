<?php

declare(strict_types=1);

namespace Modules\Catalog\App\Domain\Pricing\Data;

final class PricedProductLineSnapshot
{
    public readonly bool $taxIncluded;

    public function __construct(
        public readonly int $productId,
        public readonly string $productName,
        public readonly ?string $productSku,
        public readonly ?string $productBarcode,
        public readonly int $quantity,
        public readonly int $unitGrossMinor,
        public readonly int $grossMinor,
        public readonly int $netMinor,
        public readonly int $vatMinor,
        public readonly int $taxRateId,
        public readonly string $taxRateName,
        public readonly int $taxRateBasisPoints,
    ) {
        $this->taxIncluded = true;
    }
}
