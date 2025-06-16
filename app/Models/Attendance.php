<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'check_in',
        'check_out',
        'worked_hours',
        'latitude',
        'longitude',
        'branch_name',
        'note',
        'shift_type',
        'extra_hours',
        'missing_hours',
    ];

    protected $casts = [
        'worked_hours' => 'float',
        'extra_hours' => 'float',
        'missing_hours' => 'float',
    ];

    public function getIsAutoCheckoutAttribute()
    {
        return $this->note === 'تسجيل انصراف تلقائي';
    }

    // ✅ العلاقة مع المستخدم
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
