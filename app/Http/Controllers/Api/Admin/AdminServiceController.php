<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use App\HandlesImageUpload;

class AdminServiceController extends Controller
{
    use HandlesImageUpload;

    public function store(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'name'      => 'required',
            'price'     => 'required|numeric',
            'duration_minutes' => 'required|integer',
            'image'     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // استدعاء التريت والحصول على المصفوفة
            $imageData = $this->uploadImage($request->file('image'), 'services');
            // استخراج الرابط فقط ليتم تخزينه في قاعدة البيانات
            $validated['image'] = $imageData['url'];
        }

        try {
            $service = Service::create($validated);
            return response()->json(['success' => true, 'message' => 'تمت الإضافة بنجاح', 'data' => $service], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'خطأ في قاعدة البيانات'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);
        $data = $request->except(['image', '_method']);

        if ($request->hasFile('image')) {
            // حذف الصورة القديمة (إذا كان المخزن هو URL، فلا بأس، سيحاول التريت التعامل معه)
            $this->deleteImage($service->image);
            
            // رفع الصورة الجديدة والحصول على المصفوفة
            $imageData = $this->uploadImage($request->file('image'), 'services');
            // تخزين الرابط فقط في قاعدة البيانات
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
        $service = Service::findOrFail($id);
        
        // حذف الصورة من السحابة
        $this->deleteImage($service->image);
        
        $service->delete();
        return response()->json(['success' => true, 'message' => 'تم الحذف بنجاح']);
    }
}