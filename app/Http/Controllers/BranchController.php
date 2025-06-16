<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;

class BranchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * ✅ عرض جميع الفروع (للمسؤول فقط)
     */
    public function index()
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'غير مصرح لك بتنفيذ هذا الإجراء'], 403);
        }

        $branches = Branch::all();
        return response()->json($branches);
    }

    /**
     * ✅ إضافة فرع جديد (للمسؤول فقط)
     */
    public function store(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'غير مصرح لك بتنفيذ هذا الإجراء'], 403);
        }

        $request->validate([
            'name' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'required|numeric',
        ]);

        $branch = Branch::create($request->only(['name', 'latitude', 'longitude', 'radius']));

        return response()->json(['message' => 'تم إنشاء الفرع بنجاح', 'branch' => $branch], 201);
    }

    /**
     * ✅ تحديث بيانات فرع (للمسؤول فقط)
     */
    public function update(Request $request, $id)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'غير مصرح لك بتنفيذ هذا الإجراء'], 403);
        }

        $branch = Branch::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string',
            'latitude' => 'sometimes|numeric',
            'longitude' => 'sometimes|numeric',
            'radius' => 'sometimes|numeric',
        ]);

        $branch->update($request->only(['name', 'latitude', 'longitude', 'radius']));

        return response()->json(['message' => 'تم تحديث الفرع بنجاح', 'branch' => $branch]);
    }

    /**
     * ✅ حذف فرع (للمسؤول فقط)
     */
    public function destroy($id)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'غير مصرح لك بتنفيذ هذا الإجراء'], 403);
        }

        $branch = Branch::findOrFail($id);
        $branch->delete();

        return response()->json(['message' => 'تم حذف الفرع بنجاح']);
    }
}
