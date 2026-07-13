<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Specialty;
use Illuminate\Http\Request;
use App\HandlesImageUpload;
use Illuminate\Support\Facades\Log;

class AdminSpecialtyController extends Controller
{
    use HandlesImageUpload;

    // عرض جميع التخصصات (للأدمن)
    public function index()
    {
        return response()->json(['success' => true, 'data' => Specialty::all()]);
    }

    // عرض تخصص واحد بالـ id (مش slug، عشان الأدمن بيتعامل بالـ id)
    public function show($id)
    {
        $specialty = Specialty::findOrFail($id);
        return response()->json(['success' => true, 'data' => $specialty]);
    }

    // إضافة تخصص جديد
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'slug' => 'required|unique:specialties,slug',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $specialty = new Specialty();
        $specialty->name = $request->input('name');
        $specialty->description = $request->input('description');
        $specialty->slug = $request->input('slug');

        if ($request->hasFile('image')) {
            $imageData = $this->uploadImage($request->file('image'), 'specialties');
            $specialty->image = $imageData['url'];
            Log::info('=== ADMIN SPECIALTY STORE: NEW IMAGE ===', ['url' => $imageData['url']]);
        }

        $specialty->save();

        return response()->json(['message' => 'تمت إضافة التخصص بنجاح', 'data' => $specialty], 201);
    }

    // تحديث تخصص موجود
    public function update(Request $request, $id)
    {
        $specialty = Specialty::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|array',
            'description' => 'sometimes|array',
            'slug' => 'sometimes|unique:specialties,slug,' . $id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // 🔍 تشخيص مؤقت - احذفيه بعد التأكد إن كل حاجة شغالة
        Log::info('=== ADMIN SPECIALTY UPDATE: HAS FILE? ===', [
            'hasFile' => $request->hasFile('image'),
            'all_keys' => array_keys($request->all()),
        ]);

        if ($request->has('name')) $specialty->name = $request->input('name');
        if ($request->has('description')) $specialty->description = $request->input('description');
        if ($request->has('slug')) $specialty->slug = $request->input('slug');

        if ($request->hasFile('image')) {
            $imageData = $this->uploadImage($request->file('image'), 'specialties');
            $specialty->image = $imageData['url'];
            Log::info('=== ADMIN SPECIALTY UPDATE: NEW IMAGE ===', ['url' => $imageData['url']]);
        }

        $specialty->save();

        Log::info('=== ADMIN SPECIALTY UPDATE: AFTER SAVE ===', [
            'raw_image' => $specialty->getAttributes()['image'] ?? null,
        ]);

        return response()->json(['message' => 'تم تحديث التخصص بنجاح', 'data' => $specialty]);
    }

    // حذف تخصص
    public function destroy(Request $request, $id)
    {
        $specialty = Specialty::findOrFail($id);

        if ($specialty->doctors()->count() > 0 && !$request->has('force')) {
            return response()->json(['message' => 'هذا التخصص مرتبط بأطباء!'], 409);
        }

        if ($request->has('force')) {
            $specialty->doctors()->delete();
        }

        $specialty->delete();
        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}