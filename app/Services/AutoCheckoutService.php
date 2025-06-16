<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\User;
use App\Http\Controllers\ShiftController;
use Carbon\Carbon;

class AutoCheckoutService
{
    public function run(): void
    {
        $now = Carbon::now();
        echo "🕒 Carbon::now(): {$now}\n";

        $date = $now->copy()->subDay()->toDateString();
        echo "📅 التاريخ المستهدف للحضور: {$date}\n";

        $attendances = Attendance::where('date', $date)
            ->whereNull('check_out')
            ->whereNotNull('check_in')
            ->get();

        echo "📋 عدد السجلات المطابقة: " . $attendances->count() . "\n";

        foreach ($attendances as $attendance) {
            $user = User::find($attendance->user_id);
            if (!$user) continue;

            $checkInTime = Carbon::parse("{$attendance->date} {$attendance->check_in}");

            $morningEnd  = $user->morning_end ?? '16:00';
            $eveningEnd  = $user->evening_end ?? '23:30';
            $cutoffTime  = Carbon::parse("{$attendance->date} 12:00");

            $checkoutTime = null;
            $note = null;

            // 🔍 منطق الانصراف التلقائي بناءً على وقت الدخول
            if ($checkInTime->lte($cutoffTime)) {
                // صباحي
                $checkoutTime = Carbon::parse("{$attendance->date} {$morningEnd}");
                $note = 'تسجيل انصراف تلقائي - صباحي';
                echo "✅ صباحي: انصراف تلقائي عند {$checkoutTime->format('H:i:s')}\n";
            } else {
                // مسائي
                $checkoutTime = Carbon::parse($attendance->date)
                    ->addDay()
                    ->setTimeFromTimeString($eveningEnd);
                $note = 'تسجيل انصراف تلقائي - مسائي';
                echo "✅ مسائي: انصراف تلقائي عند {$checkoutTime->format('H:i:s')}\n";
            }

            $workedMinutes = $checkInTime->diffInMinutes($checkoutTime);
            $workedHours   = $workedMinutes / 60;

            $shiftResult = ShiftController::resolveShiftType($user, $checkInTime, $checkoutTime);

            echo "📌 Check-in: {$checkInTime}, Check-out: {$checkoutTime}\n";
            echo "⏱️ Worked Hours: {$workedHours}\n";
            echo "📤 Final Shift Type: {$shiftResult['type']}\n";

            $attendance->update([
                'check_out'     => $checkoutTime->format('H:i:s'),
                'worked_hours'  => round($workedHours, 2),
                'shift_type'    => $shiftResult['type'],
                'extra_hours'   => $shiftResult['extra_hours'],
                'missing_hours' => $shiftResult['missing_hours'],
                'note'          => $note,
            ]);

            echo "✅ saved shift_type: {$shiftResult['type']}\n";
        }
    }
}
