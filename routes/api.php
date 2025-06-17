<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\BonusController;
use App\Http\Controllers\DeductionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\RegisterApprovalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DisciplineController;
use App\Http\Controllers\AdminController;

// ✅ مسارات لا تحتاج توكن
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ✅ مسارات مشتركة بعد التحقق بالتوكن
Route::middleware('auth:sanctum')->group(function () {

    // الملف الشخصي
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile/image', [ProfileController::class, 'updateImage']);
    Route::get('/profile/statistics', [ProfileController::class, 'statistics']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // ✅ عرض إحصائيات أي موظف
    Route::get('/employees/{id}/statistics', [ProfileController::class, 'statisticsForUser']);

    // الحضور والانصراف
    Route::post('/attendance/checkin', [AttendanceController::class, 'checkIn']);
    Route::post('/attendance/checkout', [AttendanceController::class, 'checkOut']);
    Route::get('/attendance', [AttendanceController::class, 'index']);

    // الإجازات
    Route::post('/leaves', [LeaveController::class, 'store']);
    Route::get('/leaves', [LeaveController::class, 'index']);
    Route::get('/leaves/my', [LeaveController::class, 'myLeaves']);
    Route::post('/leaves/{id}/approve', [LeaveController::class, 'approve']);
    Route::post('/leaves/{id}/reject', [LeaveController::class, 'reject']);

    // المكافآت والخصومات
    Route::post('/bonus', [BonusController::class, 'store']);
    Route::post('/deduction', [DeductionController::class, 'store']);
    Route::get('/bonus/{user_id}', [BonusController::class, 'userBonuses']);
    Route::get('/deduction/{user_id}', [DeductionController::class, 'userDeductions']);

    // الموظفين
    Route::get('/employees', [UserController::class, 'index']);
    Route::get('/employees/{id}', [UserController::class, 'show']);
    Route::put('/employees/{id}', [UserController::class, 'update']);
    Route::delete('/employees/{id}', [UserController::class, 'destroy']);

    // ✅ التقارير
    Route::get('/reports/{id}', [ReportController::class, 'getLiveReport']);
    Route::get('/reports/{id}/details', [ReportController::class, 'dailyDetails'])->name('monthly.report.details');
    Route::post('/reports/generate', [ReportController::class, 'generateMonthlyReports']);
    Route::get('/reports/{id}/live', [ReportController::class, 'getLiveReport']);
    Route::get('/daily-report', [ReportController::class, 'dailyAttendance']);

    // ✅ حذف بيانات قديمة
    Route::post('/admin/cleanup', [AdminController::class, 'cleanupOldData']);

    // الإشعارات
    Route::post('/notifications/send', [NotificationController::class, 'send']);
    Route::get('/notifications', [NotificationController::class, 'myNotifications']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead']);

    // الفروع
    Route::get('/branches', [BranchController::class, 'index']);
    Route::post('/branches', [BranchController::class, 'store']);
    Route::put('/branches/{id}', [BranchController::class, 'update']);
    Route::delete('/branches/{id}', [BranchController::class, 'destroy']);

    // الموافقة على التسجيل
    Route::get('/registration-requests', [RegisterApprovalController::class, 'pending']);
    Route::post('/registration-requests/{id}/approve', [RegisterApprovalController::class, 'approve']);
    Route::post('/registration-requests/{id}/reject', [RegisterApprovalController::class, 'reject']);

    // الانضباط
    Route::get('/discipline', [DisciplineController::class, 'index']);
    Route::get('/discipline/{id}', [DisciplineController::class, 'show']);
    Route::get('/discipline-report', [DisciplineController::class, 'all']);

    // المواقع
    Route::get('/locations/active-delegates', [LocationController::class, 'activeDelegates']);
    Route::get('/locations/{id}/track', [LocationController::class, 'track']);
});

// ✅ مسار مؤقت لعرض سجل الأخطاء
Route::get('/debug-log', function () {
    $path = storage_path('logs/laravel.log');
    if (File::exists($path)) {
        return response(File::get($path), 200)
            ->header('Content-Type', 'text/plain');
    }
    return response('Log file not found.', 404);
});
