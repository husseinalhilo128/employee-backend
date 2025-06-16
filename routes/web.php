<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
});

// ✅ مجموعة مسارات إدارية مستقبلية (تحقق داخلي من admin داخل الـ Controllers)
Route::middleware(['auth'])->group(function () {
    // مثال: Route::get('/dashboard', [DashboardController::class, 'index']);
});
