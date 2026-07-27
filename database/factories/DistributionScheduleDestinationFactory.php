<?php

namespace Database\Factories;

use App\Models\DistributionSchedule;
use App\Models\DistributionScheduleDestination;
use App\Models\Location;
use App\Models\Recipient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DistributionScheduleDestination>
 */
class DistributionScheduleDestinationFactory extends Factory
{
    public function definition(): array
    {
        $location = Location::factory()->create(['type' => 'school']);
        $recipient = Recipient::factory()->create(['location_id' => $location->id]);

        return [
            'distribution_schedule_id' => DistributionSchedule::factory(),
            'location_id' => $location->id,
            'recipient_id' => $recipient->id,
            'portion_count' => $recipient->portion_count,
            'sequence_order' => 1,
        ];
    }
}
