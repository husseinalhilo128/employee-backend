<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * إنشاء حساب الأدمن الرئيسي وتفعيله.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'hussein.alhilo42@gmail.com'],
            [
                'name' => 'حسين الحلو',
                'password' => bcrypt('h8601050'),
                'role' => 'admin',
                'approved' => true,
                'base_salary' => 750000,
                'allowed_absence_days' => 4,
                'delay_allowance_minutes' => 10,
                'morning_start' => '08:30',
                'morning_end' => '16:00',
                'morning_hours' => 6,
                'evening_start' => '16:00',
                'evening_end' => '23:30',
                'evening_hours' => 6,
                'double_shift_hours' => 11,
            ]
        );
    }
}
