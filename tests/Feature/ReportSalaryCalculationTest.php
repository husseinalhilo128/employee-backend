<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Bonus;
use App\Models\Deduction;
use Carbon\Carbon;

class ReportSalaryCalculationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function salary_is_calculated_correctly_with_absences_and_double_shift()
    {
        $user = User::factory()->create([
            'base_salary' => 750000,
            'allowed_absence_days' => 4,
            'morning_start' => '08:30',
            'delay_allowance_minutes' => 10,
        ]);

        $this->actingAs($user);

        $month = 6;
        $year = 2025;

        // حضور أول يوم - شفت عادي
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::create($year, $month, 1)->toDateString(),
            'check_in' => '08:30:00',
            'check_out' => '16:00:00',
            'shift_type' => 'صباحي',
        ]);

        // حضور ثاني يوم - شفتين
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::create($year, $month, 2)->toDateString(),
            'check_in' => '08:30:00',
            'check_out' => '22:00:00',
            'shift_type' => 'شفتين',
        ]);

        $monthDays = Carbon::create($year, $month, 1)->daysInMonth;

        // اليوم الأول = يوم واحد، اليوم الثاني = يومين => المجموع 3 أيام حقيقية
        $realPresentDays = 3;

        $absentDays = $monthDays - 2; // لم يحضر سوى في يومين (تاريخياً)
        $effectiveAbsence = max(0, $absentDays - $user->allowed_absence_days);
        $dayValue = $user->base_salary / $monthDays;

        $expectedSalary = round(($realPresentDays + $user->allowed_absence_days) * $dayValue);

        $response = $this->getJson("/api/reports/{$user->id}?month={$month}&year={$year}");

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'present_days' => $realPresentDays,
            'absent_days' => $absentDays,
            'final_salary' => $expectedSalary,
        ]);
    }
}
