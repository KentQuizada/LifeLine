<?php

namespace Database\Factories;

use App\Models\BloodRequest;
use App\Models\Donor;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BloodRequest>
 */
class BloodRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reference_number' => BloodRequest::generateReferenceNumber(),
            'requester_id' => User::factory()->state(['role' => 'requester']),
            'hospital_id' => Hospital::factory(),
            'patient_name' => fake()->randomElement(UserFactory::FIRST_NAMES).' '.fake()->randomElement(UserFactory::LAST_NAMES),
            'blood_type' => fake()->randomElement(Donor::BLOOD_TYPES),
            'units' => fake()->numberBetween(1, 4),
            'urgency' => fake()->randomElement(['critical', 'urgent']),
            'status' => 'open',
            'notes' => null,
        ];
    }
}
