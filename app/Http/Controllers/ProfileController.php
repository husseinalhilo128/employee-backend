<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Bonus;
use App\Models\Deduction;
use Carbon\Carbon;
use App\Http\Controllers\DisciplineController;

class ProfileController extends Controller
{
    public function statistics()
    {
        $user = Auth::user();
        return $this->buildProfileStatistics($user);
    }

    public function statisticsForUser($id)
    {
        $authUser = auth()->user();
        if (!$authUser || $authUser->role !== 'admin') {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $user = \App\Models\User::findOrFail($id);
        return $this->buildProfileStatistics($user);
    }

    public function updateImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = Auth::user();
        $imagePath = $request->file('image')->store('profile_images', 'public');
        $user->profile_image = $imagePath;
        $user->save();

        return response()->json([
            'message' => 'تم تحديث صورة البروفايل',
            'image_url' => asset('storage/' . $imagePath),
        ]);
    }

    public function buildProfileStatistics($user)
    {
        $today = Carbon::now();
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();
        $monthDays = $today->daysInMonth;

        $presentDays = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->selectRaw("SUM(CASE WHEN shift_type = 'double' THEN 2 ELSE 1 END) as counted_days")
            ->value('counted_days') ?? 0;

        $attendedDates = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->pluck('date')->toArray();

        $leaveDates = Leave::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->pluck('date')->toArray();

        $absentDays = 0;
        for ($day = 1; $day <= $monthDays; $day++) {
            $date = Carbon::create($today->year, $today->month, $day)->toDateString();
            if (!in_array($date, $attendedDates) && !in_array($date, $leaveDates) && Carbon::parse($date)->lte($today)) {
                $absentDays++;
            }
        }

        // ✅ حساب وقت التأخير المسموح به باستخدام Carbon
        $allowedTime = Carbon::createFromFormat('H:i', $user->morning_start ?? '00:00')
            ->addMinutes((int)($user->delay_allowance_minutes ?? 0))
            ->format('H:i:s');

        $lateDays = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->whereTime('check_in', '>', $allowedTime)
            ->count();

        $hourLeave = Leave::where('user_id', $user->id)
            ->where('type', 'time')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('duration_in_hours');

        $dayLeave = Leave::where('user_id', $user->id)
            ->where('type', 'day')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('duration_in_days');

        $totalBonus = Bonus::where('user_id', $user->id)
            ->whereMonth('created_at', $today->month)
            ->sum('amount');

        $totalDeduction = Deduction::where('user_id', $user->id)
            ->whereMonth('created_at', $today->month)
            ->sum('amount');

        $extraHours = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('extra_hours');

        $missingHours = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('missing_hours');

        $disciplineScore = (new DisciplineController())->calculateScore($user->id);

        $finalSalary = round(($presentDays / $monthDays) * $user->base_salary + $totalBonus - $totalDeduction);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'approved' => $user->approved,
            'profile_image' => $user->profile_image,
            'profile_image_url' => $user->profile_image ? asset('storage/' . $user->profile_image) : null,
            'base_salary' => $user->base_salary,
            'allowed_absence_days' => $user->allowed_absence_days,
            'morning_start' => $user->morning_start,
            'evening_start' => $user->evening_start,
            'delay_allowance_minutes' => $user->delay_allowance_minutes,
            'created_at' => $user->created_at->format('Y-m-d'),

            'present_days' => (int)$presentDays,
            'absent_days' => $absentDays,
            'late_days' => $lateDays,
            'hour_leave' => round($hourLeave, 1),
            'day_leave' => (int)$dayLeave,
            'total_bonus' => (float)$totalBonus,
            'total_deduction' => (float)$totalDeduction,
            'extra_hours' => round($extraHours, 2),
            'missing_hours' => round($missingHours, 2),
            'discipline_score' => $disciplineScore,
            'final_salary' => $finalSalary,
        ]);
    }
}
