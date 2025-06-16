<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'date',
        'end_date',
        'start_time',
        'end_time',
        'reason',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
