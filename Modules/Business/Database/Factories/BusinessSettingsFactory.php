<?php

namespace Modules\Business\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Business\App\Models\BusinessSettings;
use Modules\Identity\App\Models\Tenant;

/** @extends Factory<BusinessSettings> */
class BusinessSettingsFactory extends Factory
{
    protected $model = BusinessSettings::class;

    public function definition(): array
    {
        return BusinessSettings::defaults(fake()->company());
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(['tenant_id' => $tenant->getKey(), 'display_name' => $tenant->getAttribute('name')]);
    }
}
