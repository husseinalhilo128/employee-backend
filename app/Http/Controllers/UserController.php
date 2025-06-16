<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function statistics($id)
    {
        $authUser = Auth::user();

        if (!$authUser || $authUser->role !== 'admin') {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $user = User::findOrFail($id);

        $profile = new ProfileController();
        return $profile->buildProfileStatistics($user);
    }

    public function index()
    {
        $authUser = auth()->user();

        if (!$authUser || $authUser->role !== 'admin') {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $employees = User::where('approved', true)->get();

        return response()->json($employees);
    }

    public function show($id)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $user = User::findOrFail($id);
        return response()->json($user);
    }

    /**
     * ✅ حذف موظف (فقط من قبل المسؤول)
     */
    public function destroy($id)
    {
        $authUser = auth()->user();

        if (!$authUser || $authUser->role !== 'admin') {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $user = User::findOrFail($id);

        if ($user->role === 'admin') {
            return response()->json(['message' => 'لا يمكن حذف مستخدم بصلاحية مسؤول'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'تم حذف الموظف بنجاح']);
    }
}
