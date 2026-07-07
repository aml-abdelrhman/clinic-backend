<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed',
            'phone' => 'nullable|string'
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => Hash::make($fields['password']),
            'phone' => $fields['phone'],
            'role' => 'patient' // الافتراضي مريض
        ]);

        $token = $user->createToken('myapptoken')->plainTextToken;

        return response(['user' => $user, 'token' => $token], 201);
    }

    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $fields['email'])->first();

        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response(['message' => 'بيانات الدخول غير صحيحة'], 401);
        }

        $token = $user->createToken('myapptoken')->plainTextToken;

        return response(['user' => $user, 'token' => $token], 200);
    }

  public function profile(Request $request)
{
    return response()->json([
        'user' => $request->user()
    ], 200);
}

// دالة تسجيل الخروج
public function logout(Request $request)
{
    // حذف الـ Token اللي المستخدم بيستخدمه حالياً
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'message' => 'تم تسجيل الخروج بنجاح'
    ], 200);
}
}