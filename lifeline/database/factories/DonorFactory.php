<?php

namespace Database\Factories;

use App\Models\Donor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Sample data only — these values are not eligibility rules.
 *
 * @extends Factory<Donor>
 */
class DonorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => 'donor']),
            'blood_type' => fake()->randomElement(Donor::BLOOD_TYPES),
            'birthdate' => fake()->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
            'weight_kg' => fake()->randomFloat(2, 50, 90),
            'city' => 'Butuan City',
            // Random points within about 4 km of Butuan City centre, stored at ~1 km precision.
            'approx_latitude' => round(8.95 + fake()->randomFloat(4, -0.04, 0.04), 2),
            'approx_longitude' => round(125.54 + fake()->randomFloat(4, -0.04, 0.04), 2),
            'location_sharing' => true,
            'is_available' => fake()->boolean(70),
            'verification_status' => fake()->randomElement(['verified', 'verified', 'verified', 'pending']),
        ];
    }
}
