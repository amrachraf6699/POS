<?php

namespace Modules\Business\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Business\App\Models\Branch;

/** @extends Factory<Branch> */
class BranchFactory extends Factory
{
    protected $model = Branch::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'code' => strtoupper(fake()->unique()->bothify('BR-###')),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'address_line_1' => fake()->streetAddress(),
            'city' => 'Cairo',
            'country_code' => 'EG',
            'timezone' => 'Africa/Cairo',
            'status' => Branch::STATUS_ACTIVE,
        ];
    }
}
