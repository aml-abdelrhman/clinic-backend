<?php



namespace App\Http\Controllers\Api\Admin;



use App\Http\Controllers\Controller;

use App\Models\Doctor;

use App\Models\User;

use Illuminate\Http\Request;

use App\HandlesImageUpload;

use Illuminate\Support\Facades\DB; 

use Illuminate\Support\Facades\Hash;



class DoctorController extends Controller

{

    use HandlesImageUpload;


    /**
     * عرض كل الأطباء (للصفحة العامة / الأدمن)
     */
    public function index(Request $request)
    {
        $query = Doctor::with(['specialty']);

        if ($request->filled('specialty_id')) {
            $query->where('specialty_id', $request->input('specialty_id'));
        }

        $doctors = $query->get();

        return response()->json($doctors);
    }

    /**
     * عرض تفاصيل طبيب معين (لصفحة عامة: مريض بيحجز، أو أدمن بيراجع)
     * تعتمد على doctors.id المبعوت في الـ URL - دي مش لداشبورد الطبيب نفسه
     */
    public function show($id)
    {
        $doctor = Doctor::with(['specialty', 'services', 'availabilities'])->findOrFail($id);

        return response()->json($this->formatDoctor($doctor, true));
    }

    /**
     * عرض بروفايل الطبيب نفسه في داشبورده
     * تعتمد على auth()->id() فقط - مفيش أي id مبعوت من الفرونت
     */
    public function myProfile(Request $request)
    {
        $user = auth()->user();

        if ($user->role !== 'doctor') {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized - not a doctor',
            ], 403);
        }

        $doctor = Doctor::with(['specialty', 'services', 'availabilities'])
            ->where('user_id', $user->id)
            ->first();

        if (!$doctor) {
            return response()->json([
                'status'  => false,
                'message' => 'Doctor profile not found',
            ], 404);
        }

        return response()->json($this->formatDoctor($doctor, true));
    }

    /**
     * إضافة طبيب جديد (من لوحة تحكم الأدمن)
     * بينشئ User أولاً، ثم Doctor مربوط بـ user_id
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_ar'          => 'required|string|max:255',
            'name_en'          => 'required|string|max:255',
            'email'            => 'required|email|unique:users,email',
            'password'         => 'required|string|min:6',
            'specialty_id'     => 'required|exists:specialties,id',
            'bio'              => 'nullable|string',
            'years_experience' => 'nullable|integer|min:0',
            'price_from'       => 'required|numeric|min:0',
            'languages'        => 'nullable|string',
            'rating'           => 'nullable|numeric|min:0|max:5',
            'image'            => 'nullable|image|max:5120',
        ]);

        DB::beginTransaction();

        try {
            // الخطوة أ + ب: إنشاء المستخدم أولاً في جدول Users
            $user = User::create([
                'name'     => $validated['name_en'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role'     => 'doctor',
            ]);

            // تجهيز بيانات الطبيب
            $doctorData = [
                'user_id'          => $user->id, // الخطوة ج: الربط بالـ user_id
                'name'             => json_encode([
                    'ar' => $validated['name_ar'],
                    'en' => $validated['name_en'],
                ]),
                'specialty_id'     => $validated['specialty_id'],
                'bio'              => $request->filled('bio') ? $request->input('bio') : null,
                'years_experience' => $validated['years_experience'] ?? 0,
                'price_from'       => $validated['price_from'],
                'languages'        => $request->filled('languages')
                    ? json_decode($request->input('languages'), true)
                    : [],
                'rating'           => $validated['rating'] ?? 5,
            ];

            if ($request->hasFile('image')) {
                $imageData = $this->uploadImage($request->file('image'), 'doctors');
                $doctorData['image'] = $imageData['url'];
            }

            $doctor = Doctor::create($doctorData);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Doctor created successfully',
                'data'    => $doctor->load('user', 'specialty'),
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * تحديث بيانات طبيب
     * لو الطالب طبيب: مسموحله يعدل بروفايله هو بس (محمي بـ user_id)
     * لو أدمن: مسموحله يعدل أي طبيب، وممكن كمان يغير email/password
     */
   public function update(Request $request, $id)
    {
        $doctor = Doctor::findOrFail($id);
        $user   = auth()->user();

        // حماية: طبيب مش مسموحله يعدل بروفايل طبيب تاني
        if ($user->role === 'doctor' && $doctor->user_id !== $user->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized - this is not your profile',
            ], 403);
        }

        $validated = $request->validate([
            'name_ar'          => 'sometimes|string|max:255',
            'name_en'          => 'sometimes|string|max:255',
            'specialty_id'     => 'sometimes|exists:specialties,id',
            'bio'              => 'nullable',
            'years_experience' => 'nullable|integer|min:0',
            'price_from'       => 'sometimes|numeric|min:0',
            'languages'        => 'nullable',
            'rating'           => 'nullable|numeric|min:0|max:5',
            'email'            => 'sometimes|email|unique:users,email,' . $doctor->user_id,
            'password'         => 'sometimes|string|min:6',
            'image'            => 'nullable|image|max:5120', // أضفنا هذا الحقل
        ]);

        DB::beginTransaction();

        try {
            $doctorData = [];

            // 1. تحديث الاسم (إذا تم إرساله)
            if (isset($validated['name_ar']) || isset($validated['name_en'])) {
                $currentName = is_array($doctor->name) ? $doctor->name : (json_decode($doctor->name, true) ?? []);
                $doctorData['name'] = [
                    'ar' => $validated['name_ar'] ?? $currentName['ar'] ?? '',
                    'en' => $validated['name_en'] ?? $currentName['en'] ?? '',
                ];
            }

            // 2. تحديث الـ Bio (إذا تم إرساله)
            if ($request->has('bio_ar') || $request->has('bio_en')) {
                $doctorData['bio'] = [
                    'ar' => $request->input('bio_ar'),
                    'en' => $request->input('bio_en')
                ];
            } elseif ($request->has('bio')) {
                $doctorData['bio'] = $request->input('bio');
            }

            // 3. تحديث البيانات الرقمية
            foreach (['specialty_id', 'years_experience', 'price_from', 'rating'] as $field) {
                if (isset($validated[$field])) {
                    $doctorData[$field] = $validated[$field];
                }
            }

            // 4. تحديث اللغات
            if ($request->filled('languages')) {
                $doctorData['languages'] = is_string($request->input('languages'))
                    ? json_decode($request->input('languages'), true)
                    : $request->input('languages');
            }

            // 5. معالجة الصورة (الجزء الناقص)
            if ($request->hasFile('image')) {
                $imageData = $this->uploadImage($request->file('image'), 'doctors');
                $doctorData['image'] = $imageData['url'];
            }

            // تنفيذ التحديث للجدول الرئيسي
            if (!empty($doctorData)) {
                $doctor->update($doctorData);
            }

            // 6. تحديث جدول المستخدمين (الإيميل/الباسورد)
            if (isset($validated['email']) || isset($validated['password'])) {
                $userUpdateData = [];
                if (isset($validated['email'])) {
                    $userUpdateData['email'] = $validated['email'];
                }
                if (isset($validated['password'])) {
                    $userUpdateData['password'] = Hash::make($validated['password']);
                }
                User::where('id', $doctor->user_id)->update($userUpdateData);
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Doctor updated successfully',
                'data'    => $doctor->fresh()->load('user', 'specialty'),
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    /**
     * تحديث صورة الطبيب فقط
     */
    public function updateImage(Request $request, $id)
    {
        $doctor = Doctor::findOrFail($id);
        $user   = auth()->user();

        if ($user->role === 'doctor' && $doctor->user_id !== $user->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized - this is not your profile',
            ], 403);
        }

        $request->validate([
            'image' => 'required|image|max:5120',
        ]);

        try {
            $imageData = $this->uploadImage($request->file('image'), 'doctors');

            $doctor->update(['image' => $imageData['url']]);

            return response()->json([
                'status'  => true,
                'message' => 'Image updated successfully',
                'data'    => $doctor->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * حذف طبيب (أدمن فقط - يفضل تفعيل middleware role:admin على الـ route ده)
     * بيحذف صف الطبيب + المستخدم المرتبط بيه
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $doctor = Doctor::findOrFail($id);
            $userId = $doctor->user_id;

            $doctor->delete();

            if ($userId) {
                User::where('id', $userId)->delete();
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Doctor deleted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * تنسيق بيانات الطبيب قبل الإرجاع للفرونت
     */
    private function formatDoctor($doctor, $full = false)
    {
        $data = [
            'id'               => $doctor->id,
            'user_id'          => $doctor->user_id,
            'name'             => $doctor->name,
            'image'            => $doctor->image,
            'specialty_id'     => $doctor->specialty_id,
            'specialty'        => $doctor->specialty,
            'years_experience' => $doctor->years_experience,
            'price_from'       => $doctor->price_from,
            'rating'           => $doctor->rating,
        ];

        if ($full) {
            $data['bio']            = $doctor->bio;
            $data['languages']      = $doctor->languages;
            $data['services']       = $doctor->services ?? [];
            $data['availabilities'] = $doctor->availabilities ?? [];
        }

        return $data;
    }
}