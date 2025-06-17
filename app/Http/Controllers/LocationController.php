<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Attendance;

class LocationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * ✅ تحديث الموقع الحالي للمندوب
     */
    public function updateCurrentLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $user = Auth::user();

        if ($user->role !== 'delivery') {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $user->latitude = $request->latitude;
        $user->longitude = $request->longitude;
        $user->save();

        return response()->json(['message' => 'تم تحديث الموقع بنجاح']);
    }

    /**
     * ✅ جلب مواقع جميع المندوبين (للمسؤول فقط)
     */
    public function getAllDeliveryLocations()
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $deliveryUsers = User::where('role', 'delivery')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get(['id', 'name', 'latitude', 'longitude']);

        return response()->json($deliveryUsers);
    }

    /**
     * ✅ المندوبين الذين حضروا ولم ينصرفوا (للمسؤول فقط)
     */
    public function activeDelegates()
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $today = now()->toDateString();

        $attendances = Attendance::with('user')
            ->whereDate('date', $today)
            ->whereNull('check_out')
            ->whereHas('user', function ($q) {
                $q->where('role', 'delivery')->where('approved', true);
            })
            ->get();

        $delegates = $attendances->map(function ($record) {
            return [
                'id' => $record->user->id,
                'name' => $record->user->name,
                'profile_image_url' => $record->user->profile_image
                    ? asset('storage/' . $record->user->profile_image)
                    : null,
                'attendance_id' => $record->id,
                'check_in' => $record->check_in,
            ];
        });

        return response()->json($delegates->values());
    }

    /**
     * ✅ تتبع مندوب معين (للمسؤول فقط)
     */
    public function track($id)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $user = User::findOrFail($id);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'latitude' => $user->latitude,
            'longitude' => $user->longitude,
        ]);
    }
}
