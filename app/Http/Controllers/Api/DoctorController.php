<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\Request;
use App\HandlesImageUpload;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class DoctorController extends Controller
{
    use HandlesImageUpload;

    /**
     * عرض قائمة الأطباء
     */
    public function index(Request $request)
    {
        $query = Doctor::query()->with('specialty');

        if ($request->has('specialty_id')) {
            $query->where('specialty_id', $request->specialty_id);
        }

        $doctors = $query->get()->map(function ($doctor) {
            return $this->formatDoctor($doctor, false);
        });

        return response()->json($doctors);
    }

    /**
     * عرض تفاصيل طبيب واحد
     */
    public function show($id)
    {
        $doctor = Doctor::with(['specialty', 'services', 'availabilities'])->findOrFail($id);
        return response()->json($this->formatDoctor($doctor, true));
    }

    /**
     * بروفايل الطبيب الحالي (عن طريق التوكن)
     */
    public function myProfile(Request $request)
    {
        $user = auth()->user();

        if (!$user || $user->role !== 'doctor') {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $doctor = Doctor::with(['specialty', 'services', 'availabilities'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        return response()->json([
            'status' => true,
            'data'   => $this->formatDoctor($doctor, true)
        ]);
    }

    /**
     * تحديث بيانات الطبيب
     */
    public function update(Request $request, $id)
    {
        $doctor = Doctor::findOrFail($id);
        $user   = auth()->user();

        if ($user->role === 'doctor' && $doctor->user_id !== $user->id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name_ar'          => 'sometimes|string|max:255',
            'name_en'          => 'sometimes|string|max:255',
            'specialty_id'     => 'sometimes|exists:specialties,id',
            'bio_ar'           => 'nullable|string',
            'bio_en'           => 'nullable|string',
            'years_experience' => 'nullable|integer|min:0',
            'price_from'       => 'sometimes|numeric|min:0',
            'languages'        => 'nullable',
            'rating'           => 'nullable|numeric|min:0|max:5',
            'email'            => 'sometimes|email|unique:users,email,' . $doctor->user_id,
            'password'         => 'sometimes|string|min:6',
            'image'            => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        // 🔍 تشخيص مؤقت - احذفيه بعد ما تتأكدي إن كل حاجة شغالة
        Log::info('=== DOCTOR UPDATE: RAW REQUEST ===', $request->except(['image', 'password']));
        Log::info('=== DOCTOR UPDATE: HAS FILE? ===', ['hasFile' => $request->hasFile('image')]);

        DB::beginTransaction();
        try {
            $doctorData = [];

            // الاسم - بنبعته كـ array عادي، مفيش json_encode يدوي
            // عشان مانعملش double-encoding مع الـ cast بتاع الموديل
            if (isset($validated['name_ar']) || isset($validated['name_en'])) {
                $currentName = is_array($doctor->name)
                    ? $doctor->name
                    : (json_decode($doctor->name, true) ?? []);

                $doctorData['name'] = [
                    'ar' => $validated['name_ar'] ?? $currentName['ar'] ?? '',
                    'en' => $validated['name_en'] ?? $currentName['en'] ?? '',
                ];
            }

            // البايو - بقى بيقرا bio_ar/bio_en زي ما الفرونت فعلياً بيبعت
            // مع الحفاظ على القيمة القديمة للغة الغير معدلة
            if ($request->has('bio_ar') || $request->has('bio_en')) {
                $currentBio = is_array($doctor->bio)
                    ? $doctor->bio
                    : (json_decode($doctor->bio, true) ?? []);

                $doctorData['bio'] = [
                    'ar' => $request->input('bio_ar', $currentBio['ar'] ?? ''),
                    'en' => $request->input('bio_en', $currentBio['en'] ?? ''),
                ];
            }

            foreach (['specialty_id', 'years_experience', 'price_from', 'rating'] as $field) {
                if (isset($validated[$field])) {
                    $doctorData[$field] = $validated[$field];
                }
            }

            if ($request->filled('languages')) {
                $doctorData['languages'] = is_string($request->input('languages'))
                    ? json_decode($request->input('languages'), true)
                    : $request->input('languages');
            }

            // ✅ التعامل مع الصورة لو اترفعت مع باقي بيانات الفورم
            if ($request->hasFile('image')) {
                $imageData = $this->uploadImage($request->file('image'), 'doctors');
                $doctorData['image'] = $imageData['url'];
                Log::info('=== DOCTOR UPDATE: NEW IMAGE URL ===', ['url' => $imageData['url']]);
            }

            // 🔍 تشخيص مؤقت
            Log::info('=== DOCTOR UPDATE: DATA TO SAVE ===', $doctorData);

            if (!empty($doctorData)) {
                $doctor->update($doctorData);
            }

            if (isset($validated['email']) || isset($validated['password'])) {
                $userUpdateData = [];
                if (isset($validated['email'])) $userUpdateData['email'] = $validated['email'];
                if (isset($validated['password'])) $userUpdateData['password'] = Hash::make($validated['password']);
                User::where('id', $doctor->user_id)->update($userUpdateData);
            }

            DB::commit();

            $fresh = $doctor->fresh();

            // 🔍 تشخيص مؤقت - شوفي القيمة الخام بعد الحفظ
            Log::info('=== DOCTOR UPDATE: AFTER SAVE ===', [
                'raw_attributes' => $fresh->getAttributes(),
                'casted_name'     => $fresh->name,
                'casted_bio'      => $fresh->bio,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Doctor updated successfully',
                'data'    => $this->formatDoctor($fresh, true),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('=== DOCTOR UPDATE: EXCEPTION ===', ['message' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * تحديث صورة الطبيب فقط (endpoint منفصل)
     */
    public function updateImage(Request $request, $id)
    {
        $doctor = Doctor::findOrFail($id);
        $user   = auth()->user();

        if ($user->role === 'doctor' && $doctor->user_id !== $user->id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate(['image' => 'required|image|max:5120']);

        try {
            $imageData = $this->uploadImage($request->file('image'), 'doctors');
            $doctor->update(['image' => $imageData['url']]);
            return response()->json(['status' => true, 'data' => $this->formatDoctor($doctor->fresh(), true)]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * تنسيق بيانات الطبيب قبل الإرجاع للفرونت
     */
    private function formatDoctor($doctor, $fullDetails = false)
    {
        $data = [
            'id'               => $doctor->id,
            'user_id'          => $doctor->user_id,
            'specialty_id'     => $doctor->specialty_id,
            'name'             => is_array($doctor->name) ? $doctor->name : json_decode($doctor->name, true),
            'bio'              => is_array($doctor->bio) ? $doctor->bio : json_decode($doctor->bio, true),
            'years_experience' => (int) $doctor->years_experience,
            'rating'           => (float) $doctor->rating,
            'image'            => $doctor->image,
            'languages'        => is_array($doctor->languages) ? $doctor->languages : json_decode($doctor->languages, true),
            'price_from'       => (float) $doctor->price_from,
            'specialty'        => $doctor->specialty,
        ];

        if ($fullDetails) {
            $data['services']       = $doctor->services;
            $data['availabilities'] = $doctor->availabilities;
        }

        return $data;
    }
}