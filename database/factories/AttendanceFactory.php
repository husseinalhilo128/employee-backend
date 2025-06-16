<?php

namespace Database\Factories;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        $date = $this->faker->dateTimeThisMonth();
        $checkIn = Carbon::parse($date)->setTime(8, 30);
        $checkOut = Carbon::parse($date)->setTime(16, 0);
        $workedMinutes = $checkIn->diffInMinutes($checkOut);

        return [
            'user_id' => null, // to be set explicitly in tests
            'date' => $checkIn->toDateString(),
            'check_in' => $checkIn->toTimeString(),
            'check_out' => $checkOut->toTimeString(),
            'worked_hours' => round($workedMinutes / 60, 2),
            'shift_type' => 'صباحي',
            'extra_hours' => 0,
            'missing_hours' => 0,
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'branch_name' => 'فرع تجريبي',
            'note' => null,
        ];
    }
}
