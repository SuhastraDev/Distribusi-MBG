<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => 'LOC-'.fake()->unique()->numerify('####'),
            'name' => fake()->company(),
            'type' => 'school',
            'address' => fake()->address(),
            'latitude' => fake()->latitude(-3.1, -2.8),
            'longitude' => fake()->longitude(104.6, 104.9),
            'status' => 'active',
        ];
    }
}
