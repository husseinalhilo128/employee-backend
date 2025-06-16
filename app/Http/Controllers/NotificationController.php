<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * إرسال إشعار لمستخدم معين (للمسؤول فقط)
     */
    public function send(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string',
            'body' => 'required|string',
        ]);

        $notification = Notification::create([
            'user_id' => $request->user_id,
            'title' => $request->title,
            'body' => $request->body,
        ]);

        // ملاحظة: يمكن هنا إرسال إشعار فعلي عبر FCM أو غيره إذا كان مفعلاً

        return response()->json(['message' => 'تم إرسال الإشعار', 'notification' => $notification], 201);
    }

    /**
     * عرض كل الإشعارات للمستخدم الحالي
     */
    public function myNotifications()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return response()->json($notifications);
    }
}
