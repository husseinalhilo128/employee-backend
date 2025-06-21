<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ✅ استدعاء Seeders بالترتيب المناسب
        $this->call([
            BranchSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
