<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Http\Controllers\ShiftController;
use PHPUnit\Framework\Attributes\Test;

class ShiftTypeLogicTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(): User
    {
        return User::factory()->create([
            'morning_start' => '08:30',
            'morning_end' => '16:00',
            'evening_start' => '16:00',
            'evening_end' => '23:30',
            'morning_hours' => 7.5,
            'evening_hours' => 7.5,
            'double_shift_hours' => 12,
        ]);
    }

    #[Test]
    public function shift_case_checkin_1100_checkout_2115()
    {
        $user = $this->makeUser();
        $result = ShiftController::resolveShiftType($user, '2025-06-09 11:00:00', '2025-06-09 21:15:00');

        dump($result);
        $this->assertEquals('شفتين', $result['type']);
        $this->assertEqualsWithDelta(0.0, $result['extra_hours'], 0.01);
        $this->assertEqualsWithDelta(1.75, $result['missing_hours'], 0.01);
    }

    #[Test]
    public function shift_case_checkin_0831_checkout_2200()
    {
        $user = $this->makeUser();
        $result = ShiftController::resolveShiftType($user, '2025-06-09 08:31:00', '2025-06-09 22:00:00');

        dump($result);
        $this->assertEquals('شفتين', $result['type']);
        $this->assertEqualsWithDelta(1.48, $result['extra_hours'], 0.01);
        $this->assertEqualsWithDelta(0.0, $result['missing_hours'], 0.01);
    }

    #[Test]
    public function shift_case_checkin_1500_checkout_2330()
    {
        $user = $this->makeUser();
        $result = ShiftController::resolveShiftType($user, '2025-06-09 15:00:00', '2025-06-09 23:30:00');

        dump($result);
        $this->assertEquals('مسائي', $result['type']);
        $this->assertEqualsWithDelta(1.0, $result['extra_hours'], 0.01);
        $this->assertEqualsWithDelta(0.0, $result['missing_hours'], 0.01);
    }

    #[Test]
    public function shift_case_checkin_1701_checkout_1913()
    {
        $user = $this->makeUser();
        $result = ShiftController::resolveShiftType($user, '2025-06-09 17:01:00', '2025-06-09 19:13:00');

        dump($result);
        $this->assertEquals('مسائي', $result['type']);
        $this->assertEqualsWithDelta(0.0, $result['extra_hours'], 0.01);
        $this->assertEqualsWithDelta(5.3, $result['missing_hours'], 0.01);
    }
}
