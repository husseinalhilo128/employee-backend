<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * مكان التحويل بعد تسجيل الدخول (إن وجد).
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * تسجيل خدمات الراوت.
     */
    public function boot(): void
    {
        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api') // ✅ مهم لتفعيل مسارات api.php بشكل صحيح
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
