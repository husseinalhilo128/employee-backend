<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'month',
        'year',
        'total_present_days',
        'total_absent_days',
        'total_leave_days',
        'total_work_hours',
        'missing_hours',
        'extra_hours',
        'total_bonus',
        'total_deductions',
        'final_salary',
    ];

    protected $casts = [
        'total_present_days' => 'integer',
        'total_absent_days' => 'integer',
        'total_leave_days' => 'integer',
        'total_work_hours' => 'float',
        'missing_hours' => 'float',
        'extra_hours' => 'float',
        'total_bonus' => 'float',
        'total_deductions' => 'float',
        'final_salary' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
