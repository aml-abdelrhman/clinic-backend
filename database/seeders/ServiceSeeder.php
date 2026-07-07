<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run()
    {
        // 1. حذف البيانات القديمة لضمان توزيع جديد ونظيف
        Service::truncate();

        // 2. قائمة الخدمات الموسعة
        $servicesCatalog = [
            'plastic' => [
                ['name' => ['ar' => 'حقن فيلر للوجه', 'en' => 'Facial Filler Injection'], 'price' => 2500, 'duration' => 45, 'image' => 'services/filler.jpg'],
                ['name' => ['ar' => 'حقن بوتكس', 'en' => 'Botox Injection'], 'price' => 1800, 'duration' => 30, 'image' => 'services/botox.jpg'],
                ['name' => ['ar' => 'جلسة ليزر تفتيح', 'en' => 'Laser Skin Whitening'], 'price' => 900, 'duration' => 40, 'image' => 'services/laser.jpg'],
                ['name' => ['ar' => 'نحت الجسم بالليزر', 'en' => 'Laser Body Contouring'], 'price' => 4500, 'duration' => 90, 'image' => 'services/body-contour.jpg'],
                ['name' => ['ar' => 'شد الوجه بالخيوط', 'en' => 'Thread Face Lift'], 'price' => 5000, 'duration' => 60, 'image' => 'services/thread-lift.jpg'],
                ['name' => ['ar' => 'تقشير كيميائي للبشرة', 'en' => 'Chemical Peeling'], 'price' => 1200, 'duration' => 45, 'image' => 'services/peeling.jpg'],
            ],
            'nutrition' => [
                ['name' => ['ar' => 'خطة غذائية لمرضى السكري', 'en' => 'Diabetic Diet Plan'], 'price' => 600, 'duration' => 60, 'image' => 'services/diabetes-diet.jpg'],
                ['name' => ['ar' => 'برنامج إنقاص وزن مكثف', 'en' => 'Intensive Weight Loss'], 'price' => 450, 'duration' => 45, 'image' => 'services/weight-loss.jpg'],
                ['name' => ['ar' => 'تحليل مكونات الجسم (InBody)', 'en' => 'InBody Analysis'], 'price' => 200, 'duration' => 20, 'image' => 'services/inbody.jpg'],
                ['name' => ['ar' => 'استشارة تغذية رياضيين', 'en' => 'Sports Nutrition Plan'], 'price' => 550, 'duration' => 50, 'image' => 'services/sports-nutri.jpg'],
                ['name' => ['ar' => 'علاج النحافة المفرطة', 'en' => 'Underweight Treatment'], 'price' => 400, 'duration' => 45, 'image' => 'services/underweight.jpg'],
                ['name' => ['ar' => 'نظام غذائي للحوامل', 'en' => 'Pregnancy Diet Plan'], 'price' => 500, 'duration' => 50, 'image' => 'services/pregnancy-diet.jpg'],
            ],
            'dental' => [
                ['name' => ['ar' => 'تنظيف جير وتلميع', 'en' => 'Scaling and Polishing'], 'price' => 700, 'duration' => 45, 'image' => 'services/scaling.jpg'],
                ['name' => ['ar' => 'حشو عصب بالليزر', 'en' => 'Laser Root Canal'], 'price' => 1800, 'duration' => 90, 'image' => 'services/root-canal.jpg'],
                ['name' => ['ar' => 'حشو تجميلي', 'en' => 'Cosmetic Filling'], 'price' => 800, 'duration' => 45, 'image' => 'services/filling.jpg'],
                ['name' => ['ar' => 'تبييض أسنان احترافي', 'en' => 'Professional Teeth Whitening'], 'price' => 2200, 'duration' => 60, 'image' => 'services/whitening.jpg'],
                ['name' => ['ar' => 'تركيبات زيركون', 'en' => 'Zirconia Crowns'], 'price' => 3500, 'duration' => 120, 'image' => 'services/zirconia.jpg'],
                ['name' => ['ar' => 'تقويم أسنان شفاف', 'en' => 'Clear Aligners'], 'price' => 12000, 'duration' => 60, 'image' => 'services/aligners.jpg'],
            ],
            'obgyn' => [
                ['name' => ['ar' => 'متابعة حمل دورية', 'en' => 'Regular Pregnancy Checkup'], 'price' => 500, 'duration' => 30, 'image' => 'services/pregnancy.jpg'],
                ['name' => ['ar' => 'سونار رباعي الأبعاد', 'en' => '4D Ultrasound'], 'price' => 900, 'duration' => 45, 'image' => 'services/ultrasound.jpg'],
                ['name' => ['ar' => 'علاج تكيس المبايض', 'en' => 'PCOS Treatment'], 'price' => 600, 'duration' => 45, 'image' => 'services/pcos.jpg'],
                ['name' => ['ar' => 'فحص سرطان عنق الرحم', 'en' => 'Cervical Cancer Screening'], 'price' => 1100, 'duration' => 40, 'image' => 'services/cervical-exam.jpg'],
                ['name' => ['ar' => 'علاج تأخر الإنجاب', 'en' => 'Infertility Treatment'], 'price' => 850, 'duration' => 50, 'image' => 'services/infertility.jpg'],
            ],
            'physio' => [
                ['name' => ['ar' => 'جلسة علاج طبيعي مكثفة', 'en' => 'Intensive Physio Session'], 'price' => 450, 'duration' => 60, 'image' => 'services/physio.jpg'],
                ['name' => ['ar' => 'تأهيل إصابات ملاعب', 'en' => 'Sports Injury Rehab'], 'price' => 600, 'duration' => 75, 'image' => 'services/sports-rehab.jpg'],
                ['name' => ['ar' => 'علاج الفقرات بالليزر', 'en' => 'Laser Spine Therapy'], 'price' => 700, 'duration' => 60, 'image' => 'services/spine-laser.jpg'],
                ['name' => ['ar' => 'تأهيل ما بعد العمليات', 'en' => 'Post-Surgical Rehab'], 'price' => 650, 'duration' => 60, 'image' => 'services/post-surgery.jpg'],
                ['name' => ['ar' => 'حجامة طبية متطورة', 'en' => 'Advanced Cupping'], 'price' => 400, 'duration' => 45, 'image' => 'services/cupping.jpg'],
            ],
            'psychiatry' => [
                ['name' => ['ar' => 'جلسة إرشاد أسري', 'en' => 'Family Counseling'], 'price' => 700, 'duration' => 60, 'image' => 'services/family.jpg'],
                ['name' => ['ar' => 'علاج معرفي سلوكي', 'en' => 'Cognitive Behavioral Therapy'], 'price' => 800, 'duration' => 50, 'image' => 'services/cbt.jpg'],
                ['name' => ['ar' => 'جلسة دعم نفسي للفرد', 'en' => 'Individual Therapy'], 'price' => 600, 'duration' => 50, 'image' => 'services/individual-therapy.jpg'],
                ['name' => ['ar' => 'علاج اضطرابات النوم', 'en' => 'Sleep Disorder Therapy'], 'price' => 750, 'duration' => 50, 'image' => 'services/sleep-disorder.jpg'],
                ['name' => ['ar' => 'تنمية مهارات الأطفال', 'en' => 'Child Development Session'], 'price' => 700, 'duration' => 50, 'image' => 'services/child-dev.jpg'],
            ]
        ];

       $doctors = Doctor::all();
        foreach ($doctors as $doctor) {
            // نستخدم معرف التخصص (specialty_id) لضمان الدقة
            $category = $this->getCategoryBySpecialtyId($doctor->specialty_id);
            
            if (isset($servicesCatalog[$category])) {
                foreach ($servicesCatalog[$category] as $service) {
                    Service::create([
                        'doctor_id'        => $doctor->id,
                        'name'             => $service['name'],
                        'price'            => $service['price'],
                        'duration_minutes' => $service['duration'],
                        'image'            => $service['image'],
                        'is_active'        => true 
                    ]);
                }
            }
        }
    }

private function getCategoryBySpecialtyId($id) {
        // قم بضبط الأرقام هنا لتطابق الـ IDs الموجودة في جدول الـ specialties لديك
        $map = [
            1 => 'plastic',
            2 => 'dental',
            3 => 'nutrition',
            4 => 'obgyn',
            5 => 'physio',
            6 => 'psychiatry'
        ];
        return $map[$id] ?? 'psychiatry';
    }
}