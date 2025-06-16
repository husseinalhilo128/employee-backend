<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    // تحديد الحقول القابلة للتعبئة
    protected $fillable = [
        'user_id',
        'title',
        'body',
    ];

    // علاقة الإشعار بالمستخدم
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
