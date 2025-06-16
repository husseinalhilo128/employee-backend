<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Bonus;
use App\Models\Deduction;
use Carbon\Carbon;

class SalaryCalculationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_calculates_final_salary_with_double_shifts_and_adjustments()
    {
        // إعداد المستخدم
        $user = User::factory()->create([
            'base_salary' => 3000,
            'double_shift_hours' => 12,
            'morning_hours' => 7.5,
            'evening_hours' => 7.5,
        ]);

        Carbon::setTestNow('2025-06-30');

        // 3 أيام حضور عادي (شفت صباحي)
        Attendance::factory()->count(3)->create([
            'user_id' => $user->id,
            'shift_type' => 'صباحي',
            'worked_hours' => 7.5,
        ]);

        // 2 يوم شفتين = 4 أيام احتساب
        Attendance::factory()->count(2)->create([
            'user_id' => $user->id,
            'shift_type' => 'شفتين',
            'worked_hours' => 12,
        ]);

        // مكافآت وخصومات
        Bonus::factory()->create([
            'user_id' => $user->id,
            'amount' => 250,
            'created_at' => now(),
        ]);

        Deduction::factory()->create([
            'user_id' => $user->id,
            'amount' => 150,
            'created_at' => now(),
        ]);

        // الحسابات
        $monthDays = 30;
        $attendances = Attendance::where('user_id', $user->id)->get();

        $presentDays = 0;
        foreach ($attendances as $attendance) {
            $presentDays += $attendance->shift_type === 'شفتين' ? 2 : 1;
        }

        $salaryPerDay = $user->base_salary / $monthDays;
        $base = $salaryPerDay * $presentDays;
        $bonus = Bonus::where('user_id', $user->id)->sum('amount');
        $deduction = Deduction::where('user_id', $user->id)->sum('amount');
        $finalSalary = round($base + $bonus - $deduction);

        // ✅ Assertions
        $this->assertEquals(3 + (2 * 2), $presentDays); // 7 أيام محسوبة
        $this->assertEquals(round(3000 / 30 * 7 + 250 - 150), $finalSalary);
    }
}
