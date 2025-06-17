<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Bonus;
use App\Models\Deduction;
use App\Models\MonthlyReport;
use App\Models\Branch;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function getLiveReport($id)
    {
        $authUser = Auth::user();
        if (!$authUser || ($authUser->role !== 'admin' && $authUser->id != $id)) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $month = request('month', now()->format('m'));
        $year = request('year', now()->format('Y'));

        $user = User::findOrFail($id);

        $attendances = Attendance::where('user_id', $user->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date')
            ->get();

        $attendanceGrouped = $attendances->groupBy('date');
        $days = $attendanceGrouped->map(function ($records, $date) {
            $first = $records->first();
            $last = $records->last();

            $branchName = $first->branch_name ?? null;
            if (!$branchName && $first->branch_id) {
                $branch = Branch::find($first->branch_id);
                $branchName = $branch?->name ?? 'غير معروف';
            }

            return [
                'date' => $date,
                'weekday' => Carbon::parse($date)->translatedFormat('l'),
                'check_in' => $first->check_in,
                'check_out' => $last->check_out,
                'branch_name' => $branchName ?? 'غير معروف',
                'shift_type' => $first->shift_type ?? '-',
                'notes' => $last->auto_checkout ? 'تم تسجيل انصراف تلقائي' : null,
            ];
        })->values();

        $monthDays = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $rangeDates = collect();
        for ($i = 1; $i <= $monthDays; $i++) {
            $date = Carbon::createFromDate($year, $month, $i)->toDateString();
            $rangeDates->push($date);
        }

        $presentDays = $attendanceGrouped->reduce(function ($carry, $records) {
            $shiftType = $records->first()->shift_type;
            return $carry + ($shiftType === 'شفتين' ? 2 : 1);
        }, 0);

        $leaveDays = Leave::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('type', 'day')
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->count();

        $leaveHours = Leave::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('type', 'time')
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->sum('duration_in_hours');

        $attendedDates = $attendanceGrouped->keys()->toArray();
        $leaveDates = Leave::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->pluck('date')
            ->toArray();

        $absentDays = 0;
        foreach ($rangeDates as $date) {
            if (!in_array($date, $attendedDates) && !in_array($date, $leaveDates)) {
                $absentDays++;
            }
        }

        $lateDays = $attendances->filter(function ($att) use ($user) {
            $allowed = Carbon::createFromFormat('H:i', $user->morning_start)
                ->addMinutes($user->delay_allowance_minutes);
            $checkIn = Carbon::createFromFormat('H:i:s', $att->check_in);
            return $checkIn->greaterThan($allowed);
        })->count();

        $extraHours = round($attendances->sum('extra_hours'), 2);
        $missingHours = round($attendances->sum('missing_hours'), 2);

        $bonuses = Bonus::where('user_id', $user->id)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->sum('amount');

        $deductions = Deduction::where('user_id', $user->id)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->sum('amount');

        $dayValue = $user->base_salary / $monthDays;
        $paidDays = $presentDays + $user->allowed_absence_days;
        $finalSalary = round(($paidDays * $dayValue) + $bonuses - $deductions);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'profile_image_url' => $user->profile_image_url,
            ],
            'days' => $days,
            'summary' => [
                'present_days' => $presentDays,
                'absent_days' => $absentDays,
                'leave_days' => $leaveDays,
                'leave_hours' => round($leaveHours, 1),
                'late_days' => $lateDays,
                'extra_hours' => $extraHours,
                'missing_hours' => $missingHours,
                'total_bonus' => $bonuses,
                'total_deduction' => $deductions,
                'final_salary' => $finalSalary,
            ],
        ]);
    }

    public function generateMonthlyReports()
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'غير مصرح');
        }

        $today = Carbon::today();
        $month = $today->subMonth()->format('m');
        $year = $today->format('Y');

        $users = User::where('approved', 1)->get();

        foreach ($users as $user) {
            $exists = MonthlyReport::where('user_id', $user->id)
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            if (!$exists) {
                // يمكن تنفيذ إنشاء التقرير الشهري هنا لاحقًا إذا لزم الأمر
            }
        }

        return response()->json(['message' => 'تم توليد التقارير الشهرية بنجاح']);
    }

    public function show(Request $request, $userId)
    {
        $month = $request->query('month', now()->format('m'));
        $year = $request->query('year', now()->format('Y'));

        $user = User::findOrFail($userId);
        $report = MonthlyReport::where('user_id', $userId)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'profile_image_url' => $user->profile_image_url,
            ],
            'report' => $report,
        ]);
    }

    public function dailyDetails($id)
    {
        $month = request('month');
        $year = request('year');

        $user = User::findOrFail($id);

        $attendances = Attendance::where('user_id', $user->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get();

        return response()->json([
            'days' => $attendances,
        ]);
    }

    public function getDisciplineReport(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'غير مصرح');
        }

        $month = $request->query('month', now()->format('m'));
        $year = $request->query('year', now()->format('Y'));

        $users = User::where('approved', 1)
            ->orderByDesc('discipline_score')
            ->get(['id', 'name', 'profile_image_url', 'discipline_score']);

        return response()->json([
            'month' => $month,
            'year' => $year,
            'employees' => $users,
        ]);
    }

    public function dailyAttendance()
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $today = now()->toDateString();

        $attendances = Attendance::with('user')
            ->where('date', $today)
            ->get();

        $report = $attendances->map(function ($record) {
            return [
                'id' => $record->user->id,
                'name' => $record->user->name,
                'profile_image_url' => $record->user->profile_image_url,
                'role' => $record->user->role,
                'check_in' => $record->check_in,
                'check_out' => $record->check_out,
                'branch_name' => $record->branch_name,
            ];
        });

        return response()->json($report);
    }
}
