<?php

namespace App\Http\Controllers;

use App\Models\Bonus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BonusController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // ✅ إضافة مكافأة من قبل المسؤول فقط
    public function store(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'غير مصرح لك بتنفيذ هذا الإجراء'], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|integer|min:1',
            'reason' => 'nullable|string',
        ]);

        $bonus = Bonus::create([
            'user_id' => $request->user_id,
            'amount' => $request->amount,
            'reason' => $request->reason,
        ]);

        return response()->json([
            'message' => 'تمت إضافة المكافأة بنجاح',
            'data' => $bonus
        ]);
    }

    // ✅ عرض مكافآت موظف معين (متاحة للجميع)
    public function userBonuses($user_id)
    {
        $bonuses = Bonus::where('user_id', $user_id)->latest()->get();

        return response()->json($bonuses);
    }
}
