<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. إضافة المستخدم التجريبي
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // 2. استدعاء ملفات الـ Seeders الأخرى بالترتيب الصحيح
        $this->call([
            DoctorSeeder::class,        // أولاً: إنشاء الأطباء
            ServiceSeeder::class,       // ثانياً: إنشاء الخدمات لكل طبيب
            AvailabilitySeeder::class,  // ثالثاً: إنشاء المواعيد لكل طبيب
      AppointmentSeeder::class,
            ]);
    }
}