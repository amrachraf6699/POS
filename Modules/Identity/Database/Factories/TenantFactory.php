<?php

namespace Modules\Identity\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Identity\App\Models\Tenant;

/** @extends Factory<Tenant> */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'status' => Tenant::STATUS_ACTIVE,
        ];
    }

    public function suspended(): static
    {
        return $this->state(fn (): array => ['status' => Tenant::STATUS_SUSPENDED]);
    }
}
