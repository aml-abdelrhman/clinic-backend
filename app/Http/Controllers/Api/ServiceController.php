<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
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
            // التعديل هنا: استخدام url() مباشرة بدل asset('storage/')
            'image_url' => $service->image ? url($service->image) : asset('images/default.png'),
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
    $services = Service::with('doctor.specialty')->get()->map(function ($service) {
        return [
            'id' => $service->id,
            'name' => $service->name,
            'price' => (float) $service->price,
            'duration_minutes' => (int) $service->duration_minutes,
            'image_url' => $service->image ? url($service->image) : asset('images/default.png'),
            'doctor' => [
                'id' => $service->doctor->id,
                'name' => $service->doctor->name,
                'specialty_id' => $service->doctor->specialty_id, // هنا رقم التخصص
            ],
            // إذا كنتِ تحتاجين اسم التخصص نفسه في الفرونت إند
            'specialty_name' => $service->doctor->specialty ? $service->doctor->specialty->name : null
        ];
    });

    return response()->json(['success' => true, 'data' => $services]);
}

}
