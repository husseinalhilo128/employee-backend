<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'profile_image',
        'base_salary',
        'allowed_absence_days',
        'morning_start',
        'morning_end',
        'morning_hours',
        'evening_start',
        'evening_end',
        'evening_hours',
        'double_shift_hours',
        'delay_allowance_minutes',
        'approved',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'base_salary' => 'float',
        'allowed_absence_days' => 'integer',
        'morning_start' => 'string',
        'morning_end' => 'string',
        'evening_start' => 'string',
        'evening_end' => 'string',
        'morning_hours' => 'float',
        'evening_hours' => 'float',
        'double_shift_hours' => 'float',
        'delay_allowance_minutes' => 'integer',
        'approved' => 'boolean',
        'profile_image' => 'string',
    ];

    // ✅ إظهار URL الصورة في كل استجابة JSON
    protected $appends = ['profile_image_url'];

    public function getProfileImageUrlAttribute()
    {
        return $this->profile_image
            ? asset('storage/' . $this->profile_image)
            : null;
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    public function bonuses()
    {
        return $this->hasMany(Bonus::class);
    }

    public function deductions()
    {
        return $this->hasMany(Deduction::class);
    }

    public function monthlyReports()
    {
        return $this->hasMany(MonthlyReport::class);
    }
}
