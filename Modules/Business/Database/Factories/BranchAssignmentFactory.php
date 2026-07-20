<?php

namespace Modules\Business\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Business\App\Models\BranchAssignment;

/** @extends Factory<BranchAssignment> */
class BranchAssignmentFactory extends Factory
{
    protected $model = BranchAssignment::class;

    public function definition(): array
    {
        return ['status' => BranchAssignment::STATUS_ACTIVE];
    }
}
