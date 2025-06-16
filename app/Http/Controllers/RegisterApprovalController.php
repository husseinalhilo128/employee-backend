<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class RegisterApprovalController extends Controller
{
    /**
     * عرض المستخدمين بانتظار الموافقة (approved = false)
     */
    public function pending()
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'غير مصرح');
        }

        $users = User::where('approved', false)->get();
        return response()->json($users);
    }

    /**
     * الموافقة على مستخدم جديد وتحديث بياناته
     */
    public function approve(Request $request, $id)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'غير مصرح');
        }

        $request->validate([
            'shift_start_morning' => 'required|date_format:H:i',
            'shift_end_morning' => 'required|date_format:H:i',
            'shift_start_evening' => 'required|date_format:H:i',
            'shift_end_evening' => 'required|date_format:H:i',
            'hours_per_morning_shift' => 'required|numeric|min:0',
            'hours_per_evening_shift' => 'required|numeric|min:0',
            'hours_for_both_shifts' => 'required|numeric|min:0',
            'allowed_delay_minutes' => 'required|numeric|min:0',
            'allowed_absence_days' => 'required|numeric|min:0',
            'auto_checkout_enabled' => 'required|boolean',
            'salary' => 'required|numeric|min:0',
            'role' => 'required|in:employee,delivery,admin',
        ]);

        $user = User::where('approved', false)->findOrFail($id);

        $user->update([
            'approved' => true,
            'role' => $request->role,
            'base_salary' => $request->salary,
            'delay_allowance_minutes' => $request->allowed_delay_minutes,
            'allowed_absence_days' => $request->allowed_absence_days,
            'morning_start' => $request->shift_start_morning,
            'morning_end' => $request->shift_end_morning,
            'morning_hours' => $request->hours_per_morning_shift,
            'evening_start' => $request->shift_start_evening,
            'evening_end' => $request->shift_end_evening,
            'evening_hours' => $request->hours_per_evening_shift,
            'double_shift_hours' => $request->hours_for_both_shifts,
            'auto_checkout_enabled' => $request->auto_checkout_enabled,
        ]);

        return response()->json(['message' => 'تمت الموافقة على المستخدم وتحديث بياناته']);
    }

    /**
     * رفض طلب التسجيل
     */
    public function reject($id)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'غير مصرح');
        }

        $user = User::where('approved', false)->findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'تم رفض المستخدم وحذفه']);
    }
}
