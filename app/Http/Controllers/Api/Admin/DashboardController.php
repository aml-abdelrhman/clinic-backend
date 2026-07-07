<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Service;
use App\Models\Appointment;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getDashboardStats()
    {
        // 1. تجميع الأرقام من كل الموديلات
        $totalDoctors = Doctor::count();
        $totalServices = Service::count();
        $totalReviews = Review::count();
        
        // إذا كان لديك عمود status في جدول المواعيد يمكنك استخدامه، أو جلب كل المواعيد
        $newAppointments = Appointment::count(); // أو Appointment::where('status', 'pending')->count()

        // 2. بيانات الرسم البياني (عدد المواعيد في آخر 7 أيام)
        $chartData = Appointment::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('count(*) as count')
        )
        ->groupBy('date')
        ->orderBy('date', 'ASC') // الترتيب تصاعدي ليظهر الرسم البياني من الأقدم للأحدث
        ->take(7)
        ->get();

        // 3. إرجاع كل البيانات في API واحد للفرونت إيند
        return response()->json([
            'success' => true,
            'data' => [
                'total_doctors' => $totalDoctors,
                'total_services' => $totalServices,
                'new_orders' => $newAppointments,
                'total_reviews' => $totalReviews,
                'chart_data' => $chartData
            ]
        ]);
    }
}