<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Service;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    // عرض مواعيد المريض الشخصية
    public function myAppointments()
    {
        return Appointment::with(['doctor', 'service'])
            ->where('patient_id', auth()->id())
            ->latest()
            ->get();
    }

    // إنشاء حجز جديد
    public function store(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'service_id' => 'required|exists:services,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
        ]);

        $service = Service::findOrFail($request->service_id);
        $startTime = Carbon::createFromFormat('H:i', $request->start_time);
        $endTime = $startTime->copy()->addMinutes($service->duration_minutes);

        // منع التضارب: هل هناك حجز في نفس الفترة؟
        $exists = Appointment::where('doctor_id', $request->doctor_id)
            ->where('appointment_date', $request->appointment_date)
            ->where('status', '!=', 'cancelled')
            ->where(function($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime->format('H:i'), $endTime->format('H:i')])
                      ->orWhereBetween('end_time', [$startTime->format('H:i'), $endTime->format('H:i')]);
            })->exists();

        if ($exists) {
            return response()->json(['message' => 'هذا الموعد محجوز مسبقاً'], 422);
        }

        return Appointment::create([
            'patient_id' => auth()->id(),
            'doctor_id' => $request->doctor_id,
            'service_id' => $request->service_id,
            'appointment_date' => $request->appointment_date,
            'start_time' => $startTime->format('H:i'),
            'end_time' => $endTime->format('H:i'),
            'status' => 'confirmed'
        ]);
    }

    // إلغاء الحجز
    public function cancel($id)
    {
        $appointment = Appointment::findOrFail($id);
        if (auth()->id() !== $appointment->patient_id && auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'غير مصرح'], 403);
        }
        $appointment->update(['status' => 'cancelled']);
        return response()->json(['message' => 'تم إلغاء الموعد']);
    }
}