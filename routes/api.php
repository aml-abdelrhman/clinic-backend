<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SpecialtyController;
use App\Http\Controllers\Api\Admin\DoctorController as AdminDoctorController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\DoctorServiceController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\Admin\AdminServiceController;
use App\Http\Controllers\DoctorAvailabilityController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\Admin\AdminAppointmentController;
use App\Http\Controllers\Api\Doctor\DoctorAppointmentController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\FavoriteController;

use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Admin\AdminApi;

// 1. المسارات العامة (متاحة للجميع)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/specialties', [SpecialtyController::class, 'index']);
Route::get('/specialties/{slug}', [SpecialtyController::class, 'show']);
Route::get('/doctors', [DoctorController::class, 'index']);
Route::get('/doctors/{id}', [DoctorController::class, 'show']);
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services-with-specialties', [ServiceController::class, 'getServicesWithSpecialties']);
Route::get('/availabilities', [DoctorAvailabilityController::class, 'index']);

// 2. المسارات المحمية للمستخدمين العاديين
Route::middleware('auth:sanctum')->group(function () {
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/profile', [AuthController::class, 'profile']);
Route::get('/my-appointments', [AppointmentController::class, 'myAppointments']);
Route::post('/appointments', [AppointmentController::class, 'store']);
Route::put('/appointments/{id}/cancel', [AppointmentController::class, 'cancel']);
  
 Route::post('/reviews', [ReviewController::class, 'store']);

    // مساراتك الحالية (خارج الـ middleware المحمي):
Route::get('/favorites', [FavoriteController::class, 'index']);
Route::post('/favorites/{doctor_id}', [FavoriteController::class, 'toggle']);
});

// 3. مسارات الأدمن (تتطلب صلاحية admin)
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
// Route::apiResource('specialties', SpecialtyController::class);


Route::get('/specialties', [SpecialtyController::class, 'index']);
Route::post('/specialties', [SpecialtyController::class, 'store']);
Route::post('/specialties/{specialty}', [SpecialtyController::class, 'update']); // نستخدم POST للتحديث لتجاوز مشاكل الـ FormData
Route::delete('/specialties/{specialty}', [SpecialtyController::class, 'destroy']);

Route::get('/doctor-services', [App\Http\Controllers\Api\Admin\AdminServiceController::class, 'index']);
Route::post('/doctor-services', [DoctorServiceController::class, 'store']);
Route::put('/doctor-services/{id}', [DoctorServiceController::class, 'update']);
Route::delete('/doctor-services/{id}', [DoctorServiceController::class, 'destroy']);
    // Route::apiResource('doctors', DoctorController::class);
Route::get('/doctors', [AdminDoctorController::class, 'index']);
Route::post('/doctors', [AdminDoctorController::class, 'store']);
Route::get('/doctors/{id}', [AdminDoctorController::class, 'show']);
    
    // هذا السطر هو الأهم، سيقبل الطلب سواء كان POST أو PUT
Route::match(['post', 'put'], '/doctors/{id}', [AdminDoctorController::class, 'update']);
    
Route::delete('/doctors/{id}', [AdminDoctorController::class, 'destroy']);    Route::apiResource('services', AdminServiceController::class);
Route::get('/availability', [DoctorAvailabilityController::class, 'index']);
Route::delete('/availability/{id}', [DoctorAvailabilityController::class, 'destroy']);

Route::get('/appointments', [AdminAppointmentController::class, 'index']);
Route::delete('/appointments/{id}', [AdminAppointmentController::class, 'destroy']);
    
Route::get('/reviews', [ReviewController::class, 'index']);
Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
   
Route::get('/dashboard-stats', [DashboardController::class, 'getDashboardStats']);
    });

// 4. مسارات الطبيب (تتطلب صلاحية is_doctor)
Route::middleware(['auth:sanctum', 'is_doctor'])->prefix('doctor')->group(function () {
Route::get('/services', [DoctorServiceController::class, 'index']);
Route::post('/services', [DoctorServiceController::class, 'store']);
Route::put('/services/{id}', [DoctorServiceController::class, 'update']);
Route::delete('/services/{id}', [DoctorServiceController::class, 'destroy']);

Route::get('/my-availability', [DoctorAvailabilityController::class, 'index']);
Route::post('/update-schedule', [DoctorAvailabilityController::class, 'updateSchedule']);
Route::delete('/availability/{id}', [DoctorAvailabilityController::class, 'destroy']);
Route::post('/availability', [DoctorAvailabilityController::class, 'store']);
Route::put('/availability/{id}', [DoctorAvailabilityController::class, 'update']);

Route::get('/appointments', [DoctorAppointmentController::class, 'index']);
Route::patch('/appointments/{id}/cancel', [DoctorAppointmentController::class, 'cancel']);
Route::post('/appointments/{id}/complete', [DoctorAppointmentController::class, 'complete']);

Route::get('/reviews', [ReviewController::class, 'doctorReviews']);    });
Route::get('/test-cloudinary', function () {
    try {
        // 1. الإعداد المباشر
        Configuration::instance([
            'cloud' => [
                'cloud_name' => 'dfgdtlfhg',
                'api_key'    => '572968122319822',
                'api_secret' => 'zdkcDD05lfv_3dTwL4KPK29zz50',
            ],
        ]);

        // 2. استخدام AdminApi وهو الجزء الذي يحتوي على دوال حقيقية
        $adminApi = new AdminApi();
        $response = $adminApi->ping();

        return response()->json([
            'status' => 'success',
            'message' => 'تم الاتصال بنجاح!',
            'data' => $response
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'خطأ: ' . $e->getMessage()
        ]);
    }
});