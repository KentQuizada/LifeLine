<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    public const FIRST_NAMES = [
        'Juan', 'Maria', 'Jose', 'Ana', 'Mark', 'Grace', 'John Paul', 'Kristine',
        'Carlo', 'Angelica', 'Miguel', 'Joy', 'Rafael', 'Jasmine', 'Paolo', 'Liza',
    ];

    public const LAST_NAMES = [
        'dela Cruz', 'Santos', 'Reyes', 'Garcia', 'Mendoza', 'Bautista', 'Villanueva',
        'Ramos', 'Castillo', 'Torres', 'Flores', 'Aquino', 'Navarro', 'Gonzales',
    ];

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'role' => 'requester',
            'first_name' => fake()->randomElement(self::FIRST_NAMES),
            'last_name' => fake()->randomElement(self::LAST_NAMES),
            'email' => fake()->unique()->safeEmail(),
            'mobile_number' => fake()->unique()->numerify('09#########'),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'notifications_enabled' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }
}
