<?php

namespace Database\Factories;

use App\Models\MonthlyReport;
use Illuminate\Database\Eloquent\Factories\Factory;

class MonthlyReportFactory extends Factory
{
    protected $model = MonthlyReport::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'month' => now()->format('m'),
            'year' => now()->format('Y'),
            'total_present_days' => 20,
            'total_absent_days' => 2,
            'total_leave_days' => 1,
            'total_work_hours' => 180,
            'missing_hours' => 5,
            'extra_hours' => 10,
            'total_bonus' => 200,
            'total_deductions' => 100,
            'final_salary' => 3500,
        ];
    }
}
