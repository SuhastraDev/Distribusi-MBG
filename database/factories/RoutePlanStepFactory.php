<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\RoutePlan;
use App\Models\RoutePlanStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoutePlanStep>
 */
class RoutePlanStepFactory extends Factory
{
    public function definition(): array
    {
        return [
            'route_plan_id' => RoutePlan::factory(),
            'distribution_run_destination_id' => null,
            'location_id' => Location::factory(),
            'step_order' => 1,
            'step_type' => 'start',
            'distance_from_previous_km' => 0,
            'cumulative_distance_km' => 0,
        ];
    }
}
