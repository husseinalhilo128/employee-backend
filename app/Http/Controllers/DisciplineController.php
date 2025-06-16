<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DisciplineController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * تقييم الانضباط للمستخدم الحالي
     */
    public function index()
    {
        $user = Auth::user();
        return $this->calculateDiscipline($user);
    }

    /**
     * تقييم الانضباط لمستخدم محدد (للمسؤول فقط)
     */
    public function show($id)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $user = User::findOrFail($id);
        return $this->calculateDiscipline($user);
    }

    /**
     * تقرير شامل لجميع الموظفين مع درجة الانضباط (للمسؤول فقط)
     */
    public function all()
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $users = User::where('approved', 1)->get();
        $month = now()->format('m');
        $year = now()->format('Y');

        $report = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'profile_image_url' => $user->profile_image_url,
                'discipline_score' => $this->calculateScore($user->id),
            ];
        });

        return response()->json([
            'month' => $month,
            'year' => $year,
            'employees' => $report,
        ]);
    }

    /**
     * دالة الحساب الرئيسية لإرجاع تفاصيل التقييم
     */
    private function calculateDiscipline(User $user)
    {
        $today = now();
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();
        $monthDays = $endOfMonth->day;

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get();

        $leaveDates = Leave::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->pluck('date')
            ->toArray();

        $attendedDates = $attendances->pluck('date')->toArray();

        $absentDays = 0;
        for ($i = 0; $i < $monthDays; $i++) {
            $date = $startOfMonth->copy()->addDays($i)->toDateString();
            if (!in_array($date, $attendedDates) && !in_array($date, $leaveDates) && Carbon::parse($date)->lte($today)) {
                $absentDays++;
            }
        }

        $delays = 0;
        foreach ($attendances as $record) {
            if (!$record->check_in || !$record->shift_type) {
                continue;
            }

            $checkIn = Carbon::parse($record->check_in);
            $expectedStart = match ($record->shift_type) {
                'صباحي', 'شفتين' => Carbon::parse($user->morning_start),
                'مسائي' => Carbon::parse($user->evening_start),
                default => null,
            };

            if ($expectedStart) {
                $delayMinutes = $checkIn->diffInMinutes($expectedStart, false);
                if ($delayMinutes > $user->delay_allowance_minutes) {
                    $lateBy = $delayMinutes - $user->delay_allowance_minutes;
                    $delays += match (true) {
                        $lateBy <= 10 => 1,
                        $lateBy <= 30 => 2,
                        default => 3,
                    };
                }
            }
        }

        $disciplineScore = 100 - ($absentDays * 10) - $delays;
        $disciplineScore = max(0, $disciplineScore);

        return response()->json([
            'user_id' => $user->id,
            'name' => $user->name,
            'present_days' => count($attendedDates),
            'absent_days' => $absentDays,
            'total_delays' => $delays,
            'discipline_score' => $disciplineScore,
        ]);
    }

    /**
     * دالة مساعدة: تُستخدم لحساب قيمة التقييم فقط لأي مستخدم
     */
    public function calculateScore($userId)
    {
        $user = User::findOrFail($userId);

        $today = now();
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();
        $monthDays = $endOfMonth->day;

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get();

        $leaveDates = Leave::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->pluck('date')
            ->toArray();

        $attendedDates = $attendances->pluck('date')->toArray();

        $absentDays = 0;
        for ($i = 0; $i < $monthDays; $i++) {
            $date = $startOfMonth->copy()->addDays($i)->toDateString();
            if (!in_array($date, $attendedDates) && !in_array($date, $leaveDates) && Carbon::parse($date)->lte($today)) {
                $absentDays++;
            }
        }

        $delays = 0;
        foreach ($attendances as $record) {
            if (!$record->check_in || !$record->shift_type) {
                continue;
            }

            $checkIn = Carbon::parse($record->check_in);
            $expectedStart = match ($record->shift_type) {
                'صباحي', 'شفتين' => Carbon::parse($user->morning_start),
                'مسائي' => Carbon::parse($user->evening_start),
                default => null,
            };

            if ($expectedStart) {
                $delayMinutes = $checkIn->diffInMinutes($expectedStart, false);
                if ($delayMinutes > $user->delay_allowance_minutes) {
                    $lateBy = $delayMinutes - $user->delay_allowance_minutes;
                    $delays += match (true) {
                        $lateBy <= 10 => 1,
                        $lateBy <= 30 => 2,
                        default => 3,
                    };
                }
            }
        }

        $disciplineScore = 100 - ($absentDays * 10) - $delays;
        return max(0, $disciplineScore);
    }
}
