<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\Request;
use App\HandlesImageUpload; // 1. استيراد التريت

class DoctorController extends Controller
{
    use HandlesImageUpload; // 2. تفعيل التريت
    /**
     * عرض قائمة الأطباء (بيانات خفيفة للقائمة)
     */
    public function index(Request $request)
    {
        $query = Doctor::query()->with('specialty');

        if ($request->has('specialty_id')) {
            $query->where('specialty_id', $request->specialty_id);
        }

        $doctors = $query->get()->map(function ($doctor) {
            return $this->formatDoctor($doctor, false); // false يعني لا نجلب الخدمات والمواعيد في القائمة
        });

        return response()->json($doctors);
    }

    /**
     * عرض تفاصيل طبيب واحد (بيانات كاملة)
     */
    public function show($id)
    {
        // استخدام Eager Loading لجلب كل العلاقات في طلب واحد
        $doctor = Doctor::with(['specialty', 'services', 'availabilities'])->findOrFail($id);
        
        return response()->json($this->formatDoctor($doctor, true)); // true تعني جلب كافة التفاصيل
    }


    
    /**
     * دالة مساعدة لتوحيد تنسيق بيانات الطبيب
     * @param Doctor $doctor
     * @param bool $fullDetails جلب التفاصيل الكاملة أم لا
     */
    private function formatDoctor($doctor, $fullDetails = false)
    {
        $data = [
            'id' => $doctor->id,
            'user_id' => $doctor->user_id,
            'specialty_id' => $doctor->specialty_id,
            'name' => is_array($doctor->name) ? $doctor->name : json_decode($doctor->name, true),
            'bio' => is_array($doctor->bio) ? $doctor->bio : json_decode($doctor->bio, true),
            'years_experience' => (int) $doctor->years_experience,
            'rating' => (float) $doctor->rating,
            'image' => $doctor->image,
            // 'image' => $doctor->image ? url('storage/' . $doctor->image) : null,
            'languages' => is_array($doctor->languages) ? $doctor->languages : json_decode($doctor->languages, true),
            'price_from' => (float) $doctor->price_from,
            'specialty' => $doctor->specialty,
        ];

        // في حال طلب التفاصيل الكاملة، نضيف الخدمات والمواعيد
        if ($fullDetails) {
            $data['services'] = $doctor->services;
            $data['availabilities'] = $doctor->availabilities;
        }

        return $data;
    }
    public function updateImage(Request $request, $id)
    {
        $doctor = Doctor::findOrFail($id);
        
        if ($request->hasFile('image')) {
            // حذف القديمة إذا كانت موجودة (اختياري)
            $this->deleteImage($doctor->image);
            
            // رفع الجديدة
            $path = $this->uploadImage($request->file('image'), 'doctors');
            
            $doctor->update(['image' => $path]);
            return response()->json(['message' => 'تم تحديث الصورة', 'image' => $path]);
        }
    }
}