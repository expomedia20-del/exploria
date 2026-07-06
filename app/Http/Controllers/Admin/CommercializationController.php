<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RecordStatus;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignParticipant;
use App\Models\DisplayDevice;
use App\Models\PartnerAccount;
use App\Models\RewardRedemption;
use App\Models\SponsorProposal;
use App\Models\UserMissionProgress;
use App\Models\UserReward;
use App\Models\Visit;
use App\Models\Venue;
use Inertia\Inertia;
use Inertia\Response;

class CommercializationController extends Controller
{
    public function page(): Response
    {
        $venue = Venue::query()->where('code', 'ecopark-abbasabad')->first();
        $campaign = Campaign::query()
            ->where('code', 'ecopark-pilot-1405')
            ->first();

        return Inertia::render('admin/commercialization/index', [
            'summary' => [
                'title' => 'تجاری‌سازی اکسپلوریا',
                'positioning' => 'اکسپلوریا بازدیدکننده مکان را با ماموریت، QR، پاداش و گزارش عددی به فروشگاه و اسپانسر وصل می‌کند.',
                'venue' => $venue?->name ?? 'اکوپارک عباس‌آباد',
                'campaign' => $campaign?->name ?? 'پایلوت بازدید اکوپارک ۱۴۰۵',
                'status' => 'آماده تبدیل دمو به بسته فروش',
            ],
            'salesMetrics' => $this->salesMetrics($venue, $campaign),
            'packages' => $this->packages(),
            'roiCards' => $this->roiCards(),
            'salesPipeline' => $this->salesPipeline(),
            'documents' => $this->documents(),
            'pricingTiers' => $this->pricingTiers(),
            'salesAssets' => $this->salesAssets(),
            'leadTargets' => $this->leadTargets(),
            'nextActions' => $this->nextActions(),
        ]);
    }

    /**
     * @return array<int, array<string, string|int>>
     */
    private function salesMetrics(?Venue $venue, ?Campaign $campaign): array
    {
        $venueId = $venue?->id;
        $campaignId = $campaign?->id;

        return [
            ['label' => 'کمپین قابل فروش', 'value' => Campaign::query()->where('status', RecordStatus::Active)->count()],
            ['label' => 'شریک/واحد آماده', 'value' => $venueId ? PartnerAccount::query()->where('venue_id', $venueId)->where('status', RecordStatus::Active)->count() : 0],
            ['label' => 'عضویت در کمپین', 'value' => $campaignId ? CampaignParticipant::query()->where('campaign_id', $campaignId)->count() : 0],
            ['label' => 'بازدید ثبت‌شده', 'value' => $campaignId ? Visit::query()->where('campaign_id', $campaignId)->count() : Visit::query()->count()],
            ['label' => 'ماموریت تکمیل‌شده', 'value' => UserMissionProgress::query()->where('status', 'completed')->count()],
            ['label' => 'پاداش صادرشده', 'value' => UserReward::query()->count()],
            ['label' => 'مصرف/تایید پاداش', 'value' => RewardRedemption::query()->whereIn('status', ['confirmed', 'redeemed'])->count()],
            ['label' => 'نمایشگر فعال', 'value' => DisplayDevice::query()->where('status', RecordStatus::Active)->count()],
            ['label' => 'پیشنهاد اسپانسر', 'value' => SponsorProposal::query()->count()],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function packages(): array
    {
        return [
            [
                'title' => 'پکیج پایلوت مکان',
                'buyer' => 'مدیر اجرایی مکان',
                'priceRange' => 'قیمت پیشنهادی: ۱۲۰ تا ۲۵۰ میلیون تومان برای پایلوت ۳۰ روزه',
                'deliverables' => [
                    'راه‌اندازی یک کمپین کامل با QR، ماموریت، پاداش و گزارش پایان اجرا',
                    'آموزش مدیر مکان، مدیر هاب/رواق و واحدهای منتخب',
                    'گزارش روز اجرا، ریسک‌ها، مشارکت کاربران و مصرف پاداش',
                ],
                'successMetric' => 'تعداد ورود، شروع مشارکت، تکمیل ماموریت و مصرف پاداش',
            ],
            [
                'title' => 'پکیج اسپانسر کمپین',
                'buyer' => 'اسپانسر داخلی یا خارجی',
                'priceRange' => 'قیمت پیشنهادی: ۸۰ تا ۳۰۰ میلیون تومان بر اساس جایزه، نمایشگر و سطح گزارش',
                'deliverables' => [
                    'اتصال برند به ماموریت، گنج، جایزه، تبلیغ یا مسیر خانوادگی',
                    'نمایش جایزه/پیام اسپانسر در مسیر کاربر و خروجی گزارش',
                    'گزارش تعامل، مصرف پاداش، بازدید و اثر کمپین',
                ],
                'successMetric' => 'تعامل برند، تعداد claim، مصرف جایزه و نرخ ادامه مشارکت',
            ],
            [
                'title' => 'پکیج واحد عضو',
                'buyer' => 'فروشگاه، فودکورت، رستوران یا واحد فرهنگی',
                'priceRange' => 'قیمت پیشنهادی: اشتراک ماهانه ۱۰ تا ۳۰ میلیون تومان + کارمزد مصرف پاداش',
                'deliverables' => [
                    'پنل واحد، پیشنهاد/پاداش، مصرف کد و گزارش عملکرد',
                    'قرار گرفتن در مسیر ماموریت یا مشوق خرید بعدی',
                    'گزارش مراجعه، پاداش مصرف‌شده و فرصت فروش مجدد',
                ],
                'successMetric' => 'مراجعه به واحد، مصرف کد، خرید ویژه و بازگشت کاربر',
            ],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function roiCards(): array
    {
        return [
            [
                'title' => 'ROI مکان',
                'formula' => 'ارزش اجرای پایلوت = تعداد بازدید فعال × نرخ مشارکت × ارزش هر تعامل + درآمد واحدها و اسپانسرها',
                'evidence' => 'گزارش ورود، ماموریت، پاداش، مصرف کد و وضعیت واحدهای عضو',
            ],
            [
                'title' => 'ROI اسپانسر',
                'formula' => 'ارزش اسپانسر = نمایش/تعامل برند + claim جایزه + مصرف پاداش + lead قابل پیگیری',
                'evidence' => 'گزارش تعامل کمپین، گنج/جایزه، مصرف پاداش و مسیر مشارکت',
            ],
            [
                'title' => 'ROI واحد عضو',
                'formula' => 'ارزش واحد = مراجعه ایجادشده + مصرف کوپن + خرید ویژه + احتمال مراجعه بعدی',
                'evidence' => 'گزارش کد مصرف‌شده، پیشنهاد فعال، مراجعه و مشوق خرید بعدی',
            ],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function salesPipeline(): array
    {
        return [
            ['step' => '۱', 'title' => 'دموی عدددار', 'output' => 'اجرای چرخه اکوپارک با گزارش 17 pass / 0 warning / 0 fail'],
            ['step' => '۲', 'title' => 'جلسه با مکان', 'output' => 'ارائه پکیج پایلوت مکان، محدوده اجرا، زمان و خروجی گزارش'],
            ['step' => '۳', 'title' => 'جذب واحدهای عضو', 'output' => 'انتخاب ۳ تا ۱۰ واحد اولیه برای پاداش، کد مصرف و پیشنهاد خرید'],
            ['step' => '۴', 'title' => 'جذب اسپانسر', 'output' => 'پیشنهاد جایزه/گنج/تبلیغ با گزارش ROI و امکان تمدید'],
            ['step' => '۵', 'title' => 'قرارداد و اجرا', 'output' => 'قرارداد کوتاه، جدول مسئولیت، برنامه روز اجرا و گزارش پایان کمپین'],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function documents(): array
    {
        return [
            ['title' => 'فایل معرفی ۵ تا ۷ صفحه‌ای', 'status' => 'باید از همین صفحه و چرخه دمو استخراج شود'],
            ['title' => 'پیشنهاد قیمت پایلوت مکان', 'status' => 'آماده مذاکره اولیه؛ قبل از قرارداد با عدد واقعی مکان تنظیم شود'],
            ['title' => 'قرارداد ساده پایلوت', 'status' => 'نیازمند نسخه حقوقی کوتاه با محدوده، مدت، تحویل و مسئولیت‌ها'],
            ['title' => 'قرارداد اسپانسر کمپین', 'status' => 'نیازمند بندهای جایزه، نمایش، گزارش و مالکیت محتوا'],
            ['title' => 'شرایط واحد عضو', 'status' => 'نیازمند فرم عضویت، پاداش، کد مصرف و تسویه/کارمزد'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function pricingTiers(): array
    {
        return [
            [
                'title' => 'نسخه پایه',
                'price' => '۹۰ تا ۱۲۰ میلیون تومان',
                'bestFor' => 'یک دمو کوتاه برای اثبات ارزش در یک مکان',
                'items' => [
                    'یک کمپین فعال با QR، ماموریت و گزارش پایان اجرا',
                    'تا ۳ واحد عضو یا شریک پاداش',
                    'یک گزارش ساده ROI برای مدیر مکان',
                ],
            ],
            [
                'title' => 'نسخه استاندارد',
                'price' => '۱۲۰ تا ۲۵۰ میلیون تومان',
                'bestFor' => 'پایلوت ۳۰ روزه قابل ارائه به مدیر مکان و اسپانسر',
                'items' => [
                    'یک چرخه کامل با پنل مکان، رواق/هاب، واحد و کاربر',
                    'تا ۱۰ واحد عضو و یک اسپانسر کمپین',
                    'گزارش روز اجرا، گزارش پاداش و گزارش فروش پیشنهادی',
                ],
            ],
            [
                'title' => 'نسخه ویژه',
                'price' => '۲۵۰ تا ۵۵۰ میلیون تومان',
                'bestFor' => 'اجرای برنددار با اسپانسر، نمایشگر و خروجی مدیریتی کامل',
                'items' => [
                    'چند مسیر/هاب، چند گروه کاربری و چند نوع پاداش',
                    'اتصال نمایشگر، تبلیغ مستقل و بسته اسپانسر',
                    'گزارش کامل ROI و پیشنهاد تمدید یا توسعه کمپین',
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function salesAssets(): array
    {
        return [
            [
                'title' => 'فایل معرفی ۷ صفحه‌ای',
                'owner' => 'تیم فروش اکسپلوریا',
                'status' => 'باید از صفحه چرخه دمو و همین صفحه تجاری‌سازی استخراج شود.',
            ],
            [
                'title' => 'پیشنهاد پایلوت مکان',
                'owner' => 'مدیر پروژه مکانی اکسپلوریا',
                'status' => 'شامل محدوده، مدت، مسئولیت‌ها، خروجی‌ها و قیمت پیشنهادی باشد.',
            ],
            [
                'title' => 'قرارداد کوتاه پایلوت',
                'owner' => 'ادمین مرکزی و مشاور حقوقی',
                'status' => 'نسخه ساده برای شروع سریع مذاکره و اجرای محدود آماده شود.',
            ],
            [
                'title' => 'فرم عضویت واحد تجاری/غذایی',
                'owner' => 'تیم جذب واحدها',
                'status' => 'تعهد پاداش، نحوه مصرف کد، کارمزد و گزارش عملکرد را روشن کند.',
            ],
            [
                'title' => 'پیشنهاد اسپانسر کمپین',
                'owner' => 'تیم فروش اسپانسر',
                'status' => 'سطح حضور برند، جایزه، نمایش، گزارش و امکان تمدید را مشخص کند.',
            ],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function leadTargets(): array
    {
        return [
            [
                'segment' => 'مکان پایلوت',
                'target' => 'اکوپارک، شهربازی، برج، باغ‌موزه یا مجموعه گردشگری پرتردد',
                'firstOffer' => 'نسخه استاندارد ۳۰ روزه با گزارش پایان اجرا',
            ],
            [
                'segment' => 'واحد عضو',
                'target' => 'فودکورت، رستوران، کافه، فروشگاه هدیه و واحد فرهنگی',
                'firstOffer' => 'عضویت پایه با یک پاداش قابل مصرف و گزارش مراجعه',
            ],
            [
                'segment' => 'اسپانسر داخلی',
                'target' => 'برند یا فروشگاه داخل همان مکان/هاب',
                'firstOffer' => 'جایزه یا گنج اسپانسری متصل به مسیر بازدید',
            ],
            [
                'segment' => 'اسپانسر بیرونی',
                'target' => 'برند خانوادگی، غذا، نوشیدنی، بانک، بیمه یا اپلیکیشن شهری',
                'firstOffer' => 'حضور برنددار در کمپین با گزارش تعامل و claim',
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function nextActions(): array
    {
        return [
            'ساخت یک صفحه معرفی کوتاه برای فروش که فقط سه بسته و خروجی عددی را توضیح دهد.',
            'تبدیل پکیج‌ها به جدول قیمت قابل مذاکره با نسخه پایه، استاندارد و ویژه.',
            'ساخت گزارش ROI قابل چاپ برای مکان، اسپانسر و واحد عضو.',
            'آماده‌سازی قراردادهای ساده و فرم عضویت واحدها.',
            'انتخاب یک مکان یا مجموعه واقعی برای جلسه پایلوت و ارائه دمو.',
        ];
    }
}
