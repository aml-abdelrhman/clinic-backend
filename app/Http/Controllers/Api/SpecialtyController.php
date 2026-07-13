<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Specialty;
use Illuminate\Http\Request;
use App\HandlesImageUpload; // استيراد التريت

class SpecialtyController extends Controller
{
    use HandlesImageUpload; // تفعيل التريت داخل الكنترولر

    // عرض جميع التخصصات
    public function index()
    {
        return Specialty::all();
    }

    // // إضافة تخصص جديد
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required',
    //         'description' => 'required',
    //         'slug' => 'required|unique:specialties,slug',
    //         'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
    //     ]);

    //     $specialty = new Specialty();
    //     $specialty->name = $request->input('name');
    //     $specialty->description = $request->input('description');
    //     $specialty->slug = $request->input('slug');

    //     // استخدام دالة الرفع من التريت
    //     if ($request->hasFile('image')) {
    //         $imageData = $this->uploadImage($request->file('image'), 'specialties');
    //         $specialty->image = $imageData['url']; // استخراج الرابط فقط من المصفوفة
    //     }

    //     $specialty->save();

    //     return response()->json(['message' => 'تمت إضافة التخصص بنجاح', 'data' => $specialty], 201);
    // }

//     // تحديث تخصص موجود
//     public function update(Request $request, $id)
//     {
//         $specialty = Specialty::findOrFail($id);

//         $request->validate([
//             'name' => 'sometimes|array',
//             'description' => 'sometimes|array',
//             'slug' => 'sometimes|unique:specialties,slug,' . $id,
//             'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
//         ]);

//         if ($request->has('name')) $specialty->name = $request->input('name');
//         if ($request->has('description')) $specialty->description = $request->input('description');
//         if ($request->has('slug')) $specialty->slug = $request->input('slug');

//         // استخدام دالة الرفع من التريت
//         if ($request->hasFile('image')) {
//             $imageData = $this->uploadImage($request->file('image'), 'specialties');
//             $specialty->image = $imageData['url']; // استخراج الرابط فقط من المصفوفة
//         }

//         $specialty->save();

//         return response()->json(['message' => 'تم تحديث التخصص بنجاح', 'data' => $specialty]);
//     }

//     // حذف تخصص
// public function destroy(Request $request, $id)
// {
//     $specialty = Specialty::findOrFail($id);

//     // التحقق من وجود أطباء
//     if ($specialty->doctors()->count() > 0 && !$request->has('force')) {
//         return response()->json(['message' => 'هذا التخصص مرتبط بأطباء!'], 409);
//     }

//     // إذا تم إرسال طلب الحذف الإجباري، احذفي الأطباء أولاً
//     if ($request->has('force')) {
//         $specialty->doctors()->delete(); 
//     }

//     $specialty->delete();
//     return response()->json(['message' => 'تم الحذف بنجاح']);
// }

public function show($slug)
{
    // نبحث عن التخصص بالـ slug
    $specialty = Specialty::where('slug', $slug)->first();

    if (!$specialty) {
        return response()->json(['message' => 'Specialty not found'], 404);
    }

    return response()->json($specialty);
}
}