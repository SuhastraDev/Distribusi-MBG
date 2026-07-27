<?php

namespace Database\Factories;

use App\Models\DistributionRun;
use App\Models\Officer;
use App\Models\OfficerPosition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OfficerPosition>
 */
class OfficerPositionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'distribution_run_id' => DistributionRun::factory(),
            'officer_id' => Officer::factory(),
            'latitude' => fake()->latitude(-3.1, -2.8),
            'longitude' => fake()->longitude(104.6, 104.9),
            'accuracy_meters' => fake()->randomFloat(2, 5, 50),
            'recorded_at' => now(),
        ];
    }
}
