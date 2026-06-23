<?php

namespace Database\Seeders;

use App\Models\ConsentVersion;
use Illuminate\Database\Seeder;

class ConsentVersionSeeder extends Seeder
{
    public function run(): void
    {
        ConsentVersion::query()->where('language', 'fa')->update(['is_active' => false]);

        ConsentVersion::query()->updateOrCreate(
            ['version' => 'pilot-fa-0.1', 'language' => 'fa'],
            [
                'title' => 'رضایت‌نامه آزمایشی پایلوت اکسپلوریا',
                'body' => "این متن صرفاً نسخه آزمایشی پایلوت است و متن حقوقی نهایی محسوب نمی‌شود.\n\nبا پذیرش این نسخه، اجازه می‌دهید اطلاعات ضروری بازدید، کد QR، مکان پایلوت و زمان تعامل برای ارزیابی عملکرد پایلوت ثبت شود. شماره موبایل و اطلاعات نشست در گزارش‌های عمومی نمایش داده نمی‌شوند.\n\nنسخه حقوقی نهایی باید پیش از UAT و اجرای عمومی توسط مالک محصول تأیید و جایگزین شود.",
                'is_active' => true,
                'is_demo' => true,
                'published_at' => now(),
            ],
        );
    }
}
