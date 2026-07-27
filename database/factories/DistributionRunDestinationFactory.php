<?php

namespace Database\Factories;

use App\Models\DistributionRun;
use App\Models\DistributionRunDestination;
use App\Models\DistributionScheduleDestination;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DistributionRunDestination>
 */
class DistributionRunDestinationFactory extends Factory
{
    public function definition(): array
    {
        $scheduleDestination = DistributionScheduleDestination::factory()->create();

        return [
            'distribution_run_id' => DistributionRun::factory(),
            'distribution_schedule_destination_id' => $scheduleDestination->id,
            'location_id' => $scheduleDestination->location_id,
            'recipient_id' => $scheduleDestination->recipient_id,
            'planned_portion_count' => $scheduleDestination->portion_count,
            'delivered_portion_count' => null,
            'sequence_order' => $scheduleDestination->sequence_order,
            'status' => 'pending',
            'arrived_at' => null,
            'delivered_at' => null,
            'proof_notes' => null,
        ];
    }
}
