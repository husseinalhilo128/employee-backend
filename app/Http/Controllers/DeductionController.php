<?php

namespace App\Http\Controllers;

use App\Models\Deduction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeductionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // ✅ إضافة خصم من قبل المسؤول فقط
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

        $deduction = Deduction::create([
            'user_id' => $request->user_id,
            'amount' => $request->amount,
            'reason' => $request->reason,
        ]);

        return response()->json([
            'message' => 'تمت إضافة الخصم بنجاح',
            'data' => $deduction
        ]);
    }

    // ✅ عرض خصومات موظف معين (متاح للجميع)
    public function userDeductions($user_id)
    {
        $deductions = Deduction::where('user_id', $user_id)->latest()->get();

        return response()->json($deductions);
    }
}
