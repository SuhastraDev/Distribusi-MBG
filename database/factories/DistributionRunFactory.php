<?php

namespace Database\Factories;

use App\Models\DistributionRun;
use App\Models\DistributionSchedule;
use App\Models\Officer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DistributionRun>
 */
class DistributionRunFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => 'RUN-'.fake()->unique()->numerify('####'),
            'distribution_schedule_id' => DistributionSchedule::factory(),
            'officer_id' => Officer::factory(),
            'status' => 'ready',
            'started_at' => null,
            'completed_at' => null,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
