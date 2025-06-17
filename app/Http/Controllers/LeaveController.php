<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // ✅ تقديم طلب إجازة (من قبل الموظف)
    public function store(Request $request)
    {
        // التطبيع قبل التحقق
        if ($request->type === 'day') {
            $request->merge(['type' => 'daily']);
        }

        $request->validate([
            'type' => 'required|in:daily,time',
            'date' => 'required|date',
            'end_date' => 'nullable|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'reason' => 'nullable|string',
        ]);

        $user = Auth::user();

        $leave = Leave::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'date' => $request->date,
            'end_date' => $request->end_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        // ✅ إشعار عند تقديم الطلب
        Notification::create([
            'user_id' => $user->id,
            'title' => 'تم تقديم طلب إجازة',
            'body' => 'تم إرسال طلب إجازة بتاريخ ' . $leave->date,
        ]);

        return response()->json(['message' => 'تم إرسال طلب الإجازة', 'data' => $leave]);
    }

    // ✅ عرض الإجازات الخاصة بالمستخدم الحالي
    public function myLeaves()
    {
        $user = Auth::user();
        $leaves = Leave::where('user_id', $user->id)->latest()->get();

        return response()->json($leaves);
    }

    // ✅ عرض جميع الإجازات (للمسؤول فقط)
    public function index()
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'غير مصرح لك بتنفيذ هذا الإجراء'], 403);
        }

        $leaves = Leave::with('user')->latest()->get();
        return response()->json($leaves);
    }

    // ✅ تحديث حالة الإجازة (موافقة/رفض)
    public function updateStatus(Request $request, $id)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'غير مصرح لك بتنفيذ هذا الإجراء'], 403);
        }

        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $leave = Leave::findOrFail($id);
        $leave->status = $request->status;
        $leave->save();

        // ✅ إشعار عند الموافقة أو الرفض
        Notification::create([
            'user_id' => $leave->user_id,
            'title' => 'تحديث على طلب الإجازة',
            'body' => $leave->status === 'approved'
                ? 'تمت الموافقة على طلب الإجازة الخاص بك'
                : 'تم رفض طلب الإجازة الخاص بك',
        ]);

        return response()->json(['message' => 'تم تحديث حالة الإجازة', 'data' => $leave]);
    }

    // ✅ موافقة على طلب الإجازة
    public function approve($id)
    {
        return $this->updateStatus(new Request(['status' => 'approved']), $id);
    }

    // ✅ رفض طلب الإجازة
    public function reject($id)
    {
        return $this->updateStatus(new Request(['status' => 'rejected']), $id);
    }
}
