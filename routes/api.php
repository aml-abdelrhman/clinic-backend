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
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\AdminSpecialtyController;

// 1. المسارات العامة
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/specialties', [SpecialtyController::class, 'index']);
Route::get('/specialties/{slug}', [SpecialtyController::class, 'show']);
Route::get('/doctors', [DoctorController::class, 'index']);
Route::get('/doctors/{id}', [DoctorController::class, 'show']);
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services-with-specialties', [ServiceController::class, 'getServicesWithSpecialties']);
Route::get('/availabilities', [DoctorAvailabilityController::class, 'index']);

// 2. المسارات المحمية للمستخدمين
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::get('/doctor/profile', [DoctorController::class, 'myProfile']);
    Route::get('/my-appointments', [AppointmentController::class, 'myAppointments']);
    Route::post('/appointments', [AppointmentController::class, 'store']);
    Route::put('/appointments/{id}/cancel', [AppointmentController::class, 'cancel']);
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites/{doctor_id}', [FavoriteController::class, 'toggle']);
});

// 3. مسارات الأدمن (المحمية)
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    // التخصصات (نستخدم AdminSpecialtyController فقط)
    Route::get('/specialties', [AdminSpecialtyController::class, 'index']);
    Route::get('/specialties/{id}', [AdminSpecialtyController::class, 'show']);
    Route::post('/specialties', [AdminSpecialtyController::class, 'store']);
    Route::post('/specialties/{id}', [AdminSpecialtyController::class, 'update']);
    Route::delete('/specialties/{id}', [AdminSpecialtyController::class, 'destroy']);

    // المستخدمون
    Route::get('/users', [UserController::class, 'index']);
    Route::put('/users/{id}/role', [UserController::class, 'updateRole']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    // الأطباء
    Route::get('/doctors', [AdminDoctorController::class, 'index']);
    Route::post('/doctors', [AdminDoctorController::class, 'store']);
    Route::get('/doctors/{id}', [AdminDoctorController::class, 'show']);
    Route::match(['post', 'put'], '/doctors/{id}', [AdminDoctorController::class, 'update']);
    Route::delete('/doctors/{id}', [AdminDoctorController::class, 'destroy']);

    // الخدمات
    Route::apiResource('services', AdminServiceController::class);
    Route::get('/doctor-services', [AdminServiceController::class, 'index']);

    // المواعيد والمراجعات
    Route::get('/availability', [DoctorAvailabilityController::class, 'index']);
    Route::delete('/availability/{id}', [DoctorAvailabilityController::class, 'destroy']);
    Route::get('/appointments', [AdminAppointmentController::class, 'index']);
    Route::delete('/appointments/{id}', [AdminAppointmentController::class, 'destroy']);
    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
    Route::get('/dashboard-stats', [DashboardController::class, 'getDashboardStats']);
});

// 4. مسارات الطبيب (المحمية)
Route::middleware(['auth:sanctum', 'is_doctor'])->prefix('doctor')->group(function () {
    Route::post('/profile/{id}', [DoctorController::class, 'update']);
    Route::post('/profile/{id}/image', [DoctorController::class, 'updateImage']);
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
    Route::get('/reviews', [ReviewController::class, 'doctorReviews']);
});