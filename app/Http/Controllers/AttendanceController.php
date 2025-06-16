<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Http\Controllers\ShiftController;

class AttendanceController extends Controller
{
    public function checkIn(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $matchedBranch = Branch::all()->first(function ($branch) use ($request) {
            return $this->calculateDistance(
                $request->latitude,
                $request->longitude,
                $branch->latitude,
                $branch->longitude
            ) <= $branch->radius;
        });

        if (!$matchedBranch) {
            return response()->json(['message' => 'أنت خارج نطاق مواقع الفروع المسموح بها'], 403);
        }

        $today = now()->toDateString();
        $existing = Attendance::where('user_id', $user->id)->where('date', $today)->first();
        if ($existing && $existing->check_in) {
            return response()->json(['message' => 'تم تسجيل الحضور مسبقاً'], 409);
        }

        $attendance = Attendance::updateOrCreate(
            ['user_id' => $user->id, 'date' => $today],
            [
                'check_in' => now()->format('H:i:s'),
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'branch_id' => $matchedBranch->id,
                'branch_name' => $matchedBranch->name,
                'note' => $request->note ?? null,
            ]
        );

        return response()->json(['message' => 'تم تسجيل الحضور بنجاح', 'data' => $attendance]);
    }

    public function checkOut(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $matchedBranch = Branch::all()->first(function ($branch) use ($request) {
            return $this->calculateDistance(
                $request->latitude,
                $request->longitude,
                $branch->latitude,
                $branch->longitude
            ) <= $branch->radius;
        });

        if (!$matchedBranch) {
            return response()->json(['message' => 'أنت خارج نطاق مواقع الفروع المسموح بها لتسجيل الانصراف'], 403);
        }

        $now = Carbon::now();
        $effectiveDate = $now->format('H:i') <= '02:30' ? $now->subDay()->toDateString() : $now->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
                                ->where('date', $effectiveDate)
                                ->first();

        if (!$attendance || !$attendance->check_in) {
            return response()->json(['message' => 'لم يتم تسجيل الحضور لهذا اليوم'], 404);
        }

        if ($attendance->check_out) {
            return response()->json(['message' => 'تم تسجيل الانصراف مسبقاً'], 409);
        }

        $checkInTime = Carbon::parse($attendance->date . ' ' . $attendance->check_in);
        $checkOutTime = now();
        $workedMinutes = $checkInTime->diffInMinutes($checkOutTime);
        $workedHours = $workedMinutes / 60;

        $shiftResult = ShiftController::resolveShiftType($user, $checkInTime, $checkOutTime);

        $attendance->update([
            'check_out' => $checkOutTime->format('H:i:s'),
            'worked_hours' => round($workedHours, 2),
            'shift_type' => $shiftResult['type'],
            'extra_hours' => round($shiftResult['extra_hours'], 2),
            'missing_hours' => round($shiftResult['missing_hours'], 2),
            'note' => $request->note ?? null,
        ]);

        return response()->json(['message' => 'تم تسجيل الانصراف بنجاح', 'data' => $attendance]);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;

        $a = sin($dlat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dlon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function getActiveDeliveries()
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $today = now()->toDateString();

        $activeDeliveries = User::where('type', 'delivery')
            ->where('approved', 1)
            ->whereHas('attendances', function ($query) use ($today) {
                $query->where('date', $today)
                      ->whereNotNull('check_in')
                      ->whereNull('check_out');
            })
            ->with(['attendances' => function ($query) use ($today) {
                $query->where('date', $today);
            }])
            ->get()
            ->map(function ($user) {
                $latestLocation = Location::where('user_id', $user->id)->latest()->first();

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'profile_image_url' => $user->profile_image_url,
                    'latitude' => optional($latestLocation)->latitude,
                    'longitude' => optional($latestLocation)->longitude,
                ];
            });

        return response()->json($activeDeliveries);
    }
}
