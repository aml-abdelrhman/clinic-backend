<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use App\HandlesImageUpload;

class DoctorServiceController extends Controller
{
    use HandlesImageUpload;

    public function index()
    {
        $user = auth()->user();
        $doctor = \App\Models\Doctor::where('user_id', $user->id)->first();
        
        $services = \App\Models\Service::where('doctor_id', $doctor->id)->get()->map(function($service) {
            return [
                'id' => $service->id,
                'doctor_id' => $service->doctor_id,
                'name' => $service->name,
                'price' => $service->price,
                'duration_minutes' => $service->duration_minutes,
                'is_active' => $service->is_active,
                'image_url' => $service->image // هنا الرابط مخزن كنص
            ];
        });
        return response()->json($services);
    }

    public function store(Request $request)
    {
        $data = $request->except(['image', '_method']);
        $doctor = \App\Models\Doctor::where('user_id', auth()->id())->first();
        $data['doctor_id'] = $doctor?->id;

        if ($request->hasFile('image')) {
            // التعديل هنا: نستقبل المصفوفة ثم نأخذ الرابط فقط
            $imageData = $this->uploadImage($request->file('image'), 'services');
            $data['image'] = $imageData['url']; 
        }

        $service = Service::create($data);
        return response()->json(['success' => true, 'data' => $service], 201);
    }

    public function update(Request $request, $id)
    {
        $service = Service::where('id', $id)->firstOrFail();
        $data = $request->except(['image', '_method']);

        if ($request->hasFile('image')) {
            // ملاحظة: إذا كانت deleteImage تتوقع public_id وليس URL، 
            // فهذا قد يسبب خطأ في الحذف لاحقاً.
            $this->deleteImage($service->image);
            
            // التعديل هنا: نستقبل المصفوفة ثم نأخذ الرابط فقط
            $imageData = $this->uploadImage($request->file('image'), 'services');
            $data['image'] = $imageData['url'];
        }

        $service->update($data);
        return response()->json([
            'success' => true,
            'message' => 'تم التحديث بنجاح',
            'data' => $service
        ]);
    }

    public function destroy($id)
    {
        $service = Service::where('id', $id)->where('doctor_id', auth()->id())->firstOrFail();
        
        // حذف الصورة
        $this->deleteImage($service->image);
        
        $service->delete();
        return response()->json(['success' => true, 'message' => 'Deleted successfully']);
    }
}