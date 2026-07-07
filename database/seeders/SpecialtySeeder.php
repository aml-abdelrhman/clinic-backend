<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Specialty;

class SpecialtySeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\Specialty::truncate();
        $specialties = [
            [
                'name' => ['ar' => 'تجميل', 'en' => 'Aesthetics'],
                'description' => ['ar' => 'نقدم حلولاً متطورة للعناية بالبشرة، تشمل علاجات التجديد، شد الوجه غير الجراحي، وبرامج العناية بالجمال باستخدام أحدث التقنيات.', 'en' => 'Offering advanced skin care solutions, including rejuvenation treatments, non-surgical facelifts, and beauty care programs using state-of-the-art technology.'],
            ],
            [
                'name' => ['ar' => 'أسنان', 'en' => 'Dentistry'],
                'description' => ['ar' => 'رعاية شاملة لصحة الفم والأسنان، بدءاً من الفحوصات الدورية والحشوات، وصولاً إلى تجميل الأسنان وتقويمها بأعلى معايير الجودة.', 'en' => 'Comprehensive dental care, ranging from routine check-ups and fillings to advanced cosmetic dentistry and orthodontics with the highest quality standards.'],
            ],
            [
                'name' => ['ar' => 'تغذية', 'en' => 'Nutrition'],
                'description' => ['ar' => 'تصميم خطط غذائية مخصصة لإنقاص الوزن أو اكتسابه، وعلاجات التغذية السريرية للمساعدة في إدارة الأمراض المزمنة بأسلوب صحي.', 'en' => 'Designing customized diet plans for weight management and clinical nutrition therapies to help manage chronic diseases in a healthy way.'],
            ],
            [
                'name' => ['ar' => 'نساء وتوليد', 'en' => 'OB/GYN'],
                'description' => ['ar' => 'رعاية طبية متكاملة لصحة المرأة، تشمل متابعة الحمل، الولادة، صحة الجهاز التناسلي، وتقديم الاستشارات النسائية التخصصية.', 'en' => 'Integrated medical care for women, including pregnancy monitoring, childbirth, reproductive health, and specialized gynecological consultations.'],
            ],
            [
                'name' => ['ar' => 'علاج طبيعي', 'en' => 'Physical Therapy'],
                'description' => ['ar' => 'برامج تأهيل حركي متقدمة لعلاج إصابات الملاعب، آلام الظهر والمفاصل، وما بعد الجراحات لاستعادة القوة والوظائف الحركية للجسم.', 'en' => 'Advanced rehabilitation programs to treat sports injuries, back and joint pain, and post-surgical recovery to restore body strength and mobility.'],
            ],
            [
                'name' => ['ar' => 'نفسية', 'en' => 'Psychiatry'],
                'description' => ['ar' => 'توفير بيئة آمنة وداعمة للصحة النفسية، تشمل التشخيص والعلاج النفسي لمختلف الاضطرابات لتعزيز الاستقرار الذهني والعاطفي.', 'en' => 'Providing a safe and supportive environment for mental health, including diagnosis and psychological therapy for various disorders to promote mental and emotional stability.'],
            ],
        ];

        foreach ($specialties as $item) {
            Specialty::create([
                'name' => $item['name'],
                'description' => $item['description'],
                'image' => null, 
            ]);
        }
    }
}