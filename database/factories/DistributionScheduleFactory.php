<?php

namespace Database\Factories;

use App\Models\DistributionSchedule;
use App\Models\Location;
use App\Models\Officer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DistributionSchedule>
 */
class DistributionScheduleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => 'SCHD-'.fake()->unique()->numerify('####'),
            'scheduled_date' => now()->toDateString(),
            'officer_id' => Officer::factory(),
            'depot_location_id' => Location::factory(['type' => 'depot']),
            'total_portions' => 0,
            'status' => 'draft',
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
