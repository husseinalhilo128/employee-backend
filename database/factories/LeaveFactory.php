<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Leave;

class LeaveFactory extends Factory
{
    protected $model = Leave::class;

    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'type' => 'daily',
            'date' => $this->faker->date(),
            'end_date' => null,
            'start_time' => null,
            'end_time' => null,
            'reason' => $this->faker->sentence(),
            'status' => 'pending',
        ];
    }
}
