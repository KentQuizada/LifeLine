<?php

namespace Database\Factories;

use App\Models\Hospital;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Hospital>
 */
class HospitalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Sample Hospital '.fake()->unique()->numberBetween(1, 9999),
            'type' => 'hospital',
            'address' => 'Sample address (replace with real data)',
            'city' => 'Butuan City',
            'latitude' => 8.947500,
            'longitude' => 125.540600,
            'contact_number' => null,
        ];
    }
}
