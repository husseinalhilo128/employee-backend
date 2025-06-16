<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // حذف الفروع القديمة قبل إدخال الجديدة
        Branch::truncate();

        // إدخال الفروع الجديدة
        Branch::insert([
            [
                'name' => 'الفرع الرئيسي',
                'latitude' => 32.615430,
                'longitude' => 44.017204,
                'radius' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'فرع مول الحارث',
                'latitude' => 32.614721,
                'longitude' => 44.019330,
                'radius' => 100,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'فرع البناية الجديدة',
                'latitude' => 32.617154,
                'longitude' => 44.015323,
                'radius' => 100,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
