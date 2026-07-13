<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * جلب قائمة بكل المستخدمين
     */
    public function index()
    {
        try {
            $users = User::all();
            return response()->json($users, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'حدث خطأ أثناء جلب المستخدمين'], 500);
        }
    }

    /**
     * تحديث دور المستخدم (Role)
     */
    public function updateRole(Request $request, $id)
    {
        try {
            $request->validate([
                'role' => 'required|in:patient,doctor,admin',
            ]);

            $user = User::findOrFail($id);
            $user->update([
                'role' => $request->role
            ]);

            return response()->json([
                'status' => true,
                'message' => 'تم تحديث دور المستخدم بنجاح',
                'user' => $user
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'تعذر تحديث الدور: ' . $e->getMessage()], 500);
        }
    }

    /**
     * حذف مستخدم (اختياري)
     */
  public function destroy($id)
{
    try {
        $user = User::findOrFail($id);

        $user->appointments()->delete(); 
        $user->favorites()->delete();
       $user->delete();
        
        return response()->json(['status' => true, 'message' => 'تم الحذف بنجاح'], 200);
    } catch (\Exception $e) {
          \Log::error("Error deleting user: " . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
}