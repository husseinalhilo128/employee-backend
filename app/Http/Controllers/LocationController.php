<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

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

        $delegates = User::where('role', 'delivery')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereHas('attendances', function ($query) {
                $query->whereDate('date', now())
                      ->whereNull('check_out');
            })
            ->get(['id', 'name', 'latitude', 'longitude', 'profile_image']);

        $delegates = $delegates->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'profile_image_url' => $user->profile_image
                    ? asset('storage/' . $user->profile_image)
                    : null,
                'latitude' => $user->latitude,
                'longitude' => $user->longitude,
            ];
        });

        return response()->json($delegates);
    }
}
