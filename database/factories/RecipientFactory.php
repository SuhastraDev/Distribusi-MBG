<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\Recipient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Recipient>
 */
class RecipientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'location_id' => Location::factory(),
            'code' => 'RCV-'.fake()->unique()->numerify('####'),
            'name' => 'Penerima '.fake()->company(),
            'portion_count' => fake()->numberBetween(50, 250),
            'notes' => fake()->optional()->sentence(),
            'status' => 'active',
        ];
    }
}
