<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AppointmentSeeder extends Seeder
{
    public function run()
    {
        // نجلب المرضى، الأطباء، والخدمات
        $patients = User::where('role', 'patient')->get();
        $doctors = Doctor::all();

        if ($patients->isEmpty() || $doctors->isEmpty()) {
            return; // تأكدي من وجود بيانات في الجداول الأساسية أولاً
        }

        foreach ($doctors as $doctor) {
            $services = Service::where('doctor_id', $doctor->id)->get();
            
            // لكل دكتور ننشئ 5 مواعيد تجريبية
            for ($i = 0; $i < 5; $i++) {
                $patient = $patients->random();
                $service = $services->random();
                
                // نختار تاريخاً عشوائياً في الأسبوع القادم
                $date = Carbon::now()->addDays(rand(1, 7));
                
                // نختار ساعة عشوائية بين 09:00 و 15:00 (ليكون هناك متسع لإنهاء الخدمة قبل الساعة 17:00)
                $startTime = Carbon::createFromTime(rand(9, 15), [0, 30][rand(0, 1)]);
                $endTime = $startTime->copy()->addMinutes($service->duration_minutes);

                Appointment::create([
                    'patient_id'       => $patient->id,
                    'doctor_id'        => $doctor->id,
                    'service_id'       => $service->id,
                    'appointment_date' => $date->format('Y-m-d'),
                    'start_time'       => $startTime->format('H:i'),
                    'end_time'         => $endTime->format('H:i'),
                    'status'           => ['confirmed', 'cancelled', 'completed'][rand(0, 2)],
                    'notes'            => [
                        'ar' => 'ملاحظة تجريبية للموعد ' . ($i + 1),
                        'en' => 'Test note for appointment ' . ($i + 1)
                    ],
                ]);
            }
        }
    }
}