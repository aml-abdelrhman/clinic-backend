<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    // دالة مساعدة موحدة لبناء رابط الصورة الصحيح
    // - لو الرابط Cloudinary كامل (بيبدأ بـ http) بترجعه زي ما هو
    // - لو مسار محلي قديم بتبني رابط باستخدام url() (للتوافق مع اللوكال هوست)
    // - لو مفيش صورة أصلاً بترجع الصورة الافتراضية
    private function buildImageUrl($imagePath)
    {
        if (!$imagePath) {
            return asset('images/default.png');
        }

        if (str_starts_with($imagePath, 'http')) {
            return $imagePath;
        }

        return url($imagePath);
    }

    public function index(Request $request)
    {
        $query = Service::query();

        if ($request->has('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        $services = $query->get()->map(function ($service) {
            return [
                'id' => $service->id,
                'doctor_id' => $service->doctor_id,
                'name' => $service->name,
                'price' => (float) $service->price,
                'duration_minutes' => (int) $service->duration_minutes,
                'image_url' => $this->buildImageUrl($service->image),
                'is_active' => (bool) $service->is_active
            ];
        });

        return response()->json(['success' => true, 'data' => $services]);
    }

    public function show($id)
    {
        $service = Service::findOrFail($id);
        return response()->json(['success' => true, 'data' => $service]);
    }

    // دالة جديدة لصفحة الخدمات فقط
    public function getServicesWithSpecialties()
    {
        // نجلب الخدمات مع بيانات الطبيب وتخصصه
        // whereHas('doctor') بتستبعد أي خدمة يتيمة (doctor محذوف) عشان منوصلش لخطأ null->id
        $services = Service::with('doctor.specialty')
            ->whereHas('doctor')
            ->get()
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'price' => (float) $service->price,
                    'duration_minutes' => (int) $service->duration_minutes,
                    'image_url' => $this->buildImageUrl($service->image),
                    'doctor' => [
                        'id' => $service->doctor->id,
                        'name' => $service->doctor->name,
                        'specialty_id' => $service->doctor->specialty_id, // هنا رقم التخصص
                    ],
                    // إذا كنتِ تحتاجين اسم التخصص نفسه في الفرونت إند
                    'specialty_name' => $service->doctor->specialty?->name ?? null
                ];
            });

        return response()->json(['success' => true, 'data' => $services]);
    }
}