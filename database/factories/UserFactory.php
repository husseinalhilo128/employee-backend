<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),

            // معلومات تتعلق بالحضور والرواتب
            'role' => 'employee',
            'approved' => true,
            'base_salary' => 1000,
            'allowed_absence_days' => 2,
            'morning_start' => '08:30',
            'morning_end' => '16:00',
            'morning_hours' => 7.5,
            'evening_start' => '16:00',
            'evening_end' => '23:30',
            'evening_hours' => 7.5,
            'double_shift_hours' => 12,
            'delay_allowance_minutes' => 15,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
