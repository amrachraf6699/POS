<?php

namespace Modules\Catalog\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Catalog\App\Models\Product;

/** @extends Factory<Product> */
final class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return ['name' => fake()->word(), 'sku' => fake()->unique()->bothify('SKU-###'), 'cost_price_minor' => 0, 'selling_price_minor' => 10000, 'track_inventory' => true, 'low_stock_threshold' => 0, 'allow_negative_stock' => false, 'status' => Product::STATUS_ACTIVE];
    }
}
