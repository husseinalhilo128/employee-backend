<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AutoCheckoutService;

class AutoCheckoutEmployees extends Command
{
    protected $signature = 'attendance:auto-checkout';

    protected $description = 'تسجيل انصراف تلقائي للموظفين الذين لم يسجلوا انصراف حتى الساعة 3 صباحاً';

    public function handle()
    {
        // استدعاء الخدمة التي تحتوي على منطق الانصراف التلقائي
        app(AutoCheckoutService::class)->run();

        // إظهار رسالة نجاح في سطر الأوامر فقط
        if ($this->output !== null) {
            $this->info('✅ تم تنفيذ الانصراف التلقائي بنجاح.');
        }
    }
}
