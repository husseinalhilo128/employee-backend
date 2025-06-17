<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    // ✅ تسجيل الدخول
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'بيانات الدخول غير صحيحة'], 401);
        }

        $user = Auth::user();

        // ✅ التحقق من الموافقة
        if (!$user->approved) {
            Auth::logout();
            return response()->json(['message' => 'لم تتم الموافقة على حسابك بعد'], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'تم تسجيل الدخول بنجاح',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'profile_image_url' => $user->profile_image_url,
            ],
            'token' => $token,
        ]);
    }

    // ✅ تسجيل الخروج
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'تم تسجيل الخروج بنجاح']);
    }

    // ✅ تسجيل حساب جديد
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);

        // الحساب الرئيسي الوحيد الذي يتم تفعيله مباشرة
        $isMainAdmin = $request->email === 'hussein.alhilo42@gmail.com';
        $role = $isMainAdmin ? 'admin' : 'employee';
        $approved = $isMainAdmin ? true : false;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $role,
            'approved' => $approved,
            'base_salary' => 0,
            'allowed_absence_days' => 0,
            'delay_allowance_minutes' => 0,
        ]);

        return response()->json([
            'message' => 'تم إنشاء الحساب بنجاح. بانتظار موافقة الإدارة.',
        ], 201);
    }
}
