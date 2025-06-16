<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Carbon\Carbon;

class ShiftController extends Controller
{
    public function determineShiftType(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $request->validate([
            'check_in' => 'required|date_format:Y-m-d H:i:s',
            'check_out' => 'required|date_format:Y-m-d H:i:s',
        ]);

        $user = Auth::user();
        return response()->json(self::resolveShiftType($user, $request->check_in, $request->check_out));
    }

    public static function resolveShiftType($user, $checkIn, $checkOut)
    {
        $checkIn = Carbon::parse($checkIn);
        $checkOut = Carbon::parse($checkOut);

        if ($checkIn->gt($checkOut)) {
            [$checkIn, $checkOut] = [$checkOut, $checkIn];
        }

        $workMinutes = $checkIn->diffInMinutes($checkOut);
        $workHours = $workMinutes / 60;

        // إعداد أوقات الشفتات
        $morningStart     = Carbon::parse($checkIn->toDateString() . ' 08:00');
        $morningEnd       = Carbon::parse($checkIn->toDateString() . ' 12:30');
        $doubleShiftEnd   = Carbon::parse($checkIn->toDateString() . ' 14:00');

        // ساعات العمل المطلوبة لكل شفت
        $doubleShiftHours = $user->double_shift_hours ?? 12;
        $morningHours     = $user->morning_hours ?? 7.5;
        $eveningHours     = $user->evening_hours ?? 7.5;

        $type = null;

        // ✅ شفتين: دخول بين 08:00 و 14:00، وساعات ≥ 10
        if (
            $checkIn->between($morningStart, $doubleShiftEnd) &&
            $workHours >= 10
        ) {
            $type = 'شفتين';
        }

        // ✅ صباحي: دخول بين 08:00 و 12:30، وساعات < 10
        elseif (
            $checkIn->between($morningStart, $morningEnd) &&
            $workHours < 10
        ) {
            $type = 'صباحي';
        }

        // ✅ مسائي: دخول بعد 12:30، وساعات < 10
        elseif (
            $checkIn->greaterThan($morningEnd) &&
            $workHours < 10
        ) {
            $type = 'مسائي';
        }

        // تحديد الساعات المتوقعة لهذا النوع
        $expectedHours = match ($type) {
            'شفتين' => $doubleShiftHours,
            'صباحي' => $morningHours,
            'مسائي' => $eveningHours,
            default => 0,
        };

        $difference = $workHours - $expectedHours;

        return [
            'type' => $type,
            'extra_hours' => $difference > 0 ? round($difference, 2) : 0,
            'missing_hours' => $difference < 0 ? round(abs($difference), 2) : 0,
        ];
    }
}
