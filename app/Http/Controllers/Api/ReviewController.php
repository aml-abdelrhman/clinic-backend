<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Doctor;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    // 1. المريض: إضافة تقييم
    public function store(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_id' => 'required|unique:reviews,appointment_id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|array', // يتوقع {"ar": "...", "en": "..."}
        ]);

        $review = Review::create([
            'patient_id' => auth()->id(),
            ...$validated
        ]);

        // تحديث المتوسط تلقائياً
        $doctor = Doctor::find($request->doctor_id);
        $doctor->update(['rating' => Review::where('doctor_id', $request->doctor_id)->avg('rating')]);

        return response()->json($review, 201);
    }

    // 2. الطبيب: عرض تقييماته فقط
public function doctorReviews()
{
    // بدلاً من الاعتماد على العلاقة $user->doctor، سنبحث مباشرة في جدول الأطباء
    // عن الطبيب الذي يطابق الـ user_id للمستخدم المسجل حالياً
    $doctor = \App\Models\Doctor::where('user_id', auth()->id())->first();

    if (!$doctor) {
        return response()->json([
            'message' => 'Doctor profile not found',
            'debug_user_id' => auth()->id() // لنعرف أي ID يحاول الدخول
        ], 404);
    }

    $reviews = Review::where('doctor_id', $doctor->id)
                     ->with('patient:id,name')
                     ->latest()
                     ->get();

    return response()->json($reviews);
}
    // 3. الأدمن: عرض كل التقييمات
    public function index()
    {
        return Review::with(['doctor:id,name', 'patient:id,name'])->latest()->get();
    }

    // 4. الأدمن: مسح تقييم غير لائق
    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        $doctor_id = $review->doctor_id;
        $review->delete();

        // إعادة حساب التقييم بعد الحذف
        $doctor = Doctor::find($doctor_id);
        $doctor->update(['rating' => Review::where('doctor_id', $doctor_id)->avg('rating') ?? 0]);

        return response()->json(['message' => 'تم حذف التقييم بنجاح']);
    }
}