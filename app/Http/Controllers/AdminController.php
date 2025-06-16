<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Bonus;
use App\Models\Deduction;
use App\Models\MonthlyReport;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function cleanupOldData(Request $request)
    {
        $user = auth()->user();

        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'كلمة المرور غير صحيحة'], 401);
        }

        // ✅ حذف جميع البيانات بدون شرط التاريخ
        Attendance::truncate();
        Leave::truncate();
        Bonus::truncate();
        Deduction::truncate();
        MonthlyReport::truncate();

        return response()->json(['message' => 'تم حذف جميع البيانات بنجاح']);
    }
}
