<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * جدولة الأوامر التي تُنفذ تلقائيًا.
     */
    protected function schedule(Schedule $schedule)
    {
        // ✅ انصراف تلقائي يومي الساعة 3 صباحًا
        $schedule->command('attendance:auto-checkout')->dailyAt('03:00');
    }

    /**
     * تعريف الأوامر Artisan الخاصة بالتطبيق.
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
