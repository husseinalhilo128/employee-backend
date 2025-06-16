<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Services\AutoCheckoutService;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;

class AutoCheckoutTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function assigns_morning_if_attendance_between_8_and_14_and_hours_less_than_10()
    {
        Carbon::setTestNow('2025-06-10 03:01:00');

        $user = User::factory()->create([
            'morning_start' => '08:30',
            'morning_end' => '16:00',
            'double_shift_hours' => 12,
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-06-09',
            'check_in' => '08:30:00',
        ]);

        (new AutoCheckoutService())->run();

        $attendance = Attendance::first();

        $this->assertEquals('16:00:00', $attendance->check_out);
        $this->assertEquals('صباحي', $attendance->shift_type);
        $this->assertGreaterThanOrEqual(0, $attendance->extra_hours);
    }

    #[Test]
    public function assigns_morning_if_hours_less_than_10()
    {
        Carbon::setTestNow('2025-06-10 03:01:00');

        $user = User::factory()->create([
            'morning_start' => '08:30',
            'morning_end' => '16:00',
            'morning_hours' => 7.5,
            'double_shift_hours' => 12,
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-06-09',
            'check_in' => '10:30:00',
        ]);

        (new AutoCheckoutService())->run();

        $attendance = Attendance::first();

        $this->assertEquals('16:00:00', $attendance->check_out);
        $this->assertEquals('صباحي', $attendance->shift_type);
    }

    #[Test]
    public function assigns_evening_if_checkin_after_grace_period_morning()
    {
        Carbon::setTestNow('2025-06-10 03:01:00');

        $user = User::factory()->create([
            'morning_start' => '08:30',
            'morning_end' => '16:00',
            'evening_start' => '16:00',
            'evening_end' => '23:30',
            'evening_hours' => 7.5,
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-06-09',
            'check_in' => '15:30:00',
        ]);

        (new AutoCheckoutService())->run();

        $attendance = Attendance::first();

        $this->assertEquals('23:30:00', $attendance->check_out);
        $this->assertEquals('مسائي', $attendance->shift_type);
    }
}
