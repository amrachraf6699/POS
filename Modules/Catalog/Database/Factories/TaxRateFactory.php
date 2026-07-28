<?php

namespace Modules\Catalog\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Catalog\App\Models\TaxRate;

/** @extends Factory<TaxRate> */
final class TaxRateFactory extends Factory
{
    protected $model = TaxRate::class;

    public function definition(): array
    {
        return [
            'name' => 'ضريبة القيمة المضافة',
            'rate_basis_points' => 1400,
            'effective_from' => now()->startOfDay(),
            'status' => TaxRate::STATUS_ACTIVE,
        ];
    }
}
