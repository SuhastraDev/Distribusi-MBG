<?php

namespace Database\Factories;

use App\Models\DistributionRun;
use App\Models\RoutePlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoutePlan>
 */
class RoutePlanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => 'RTE-'.fake()->unique()->numerify('####'),
            'distribution_run_id' => DistributionRun::factory(),
            'algorithm' => 'greedy_nearest_neighbor',
            'total_distance_km' => 0,
            'total_estimated_minutes' => 0,
            'status' => 'generated',
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
