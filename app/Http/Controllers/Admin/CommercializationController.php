<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RecordStatus;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignParticipant;
use App\Models\CampaignSponsorship;
use App\Models\DisplayDevice;
use App\Models\PartnerAccount;
use App\Models\RewardDefinition;
use App\Models\RewardInventoryAllocation;
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
    private const STRESS_DEMO_CAMPAIGN_CODE = 'ecopark-online-treasure-map-game-campaign';

    public function page(): Response
    {
        $venue = Venue::query()->where('code', 'ecopark-abbasabad')->first();
        $campaign = $this->commercialCampaign();

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
            'finalDemoReport' => $this->finalDemoReport($campaign),
            'nextActions' => $this->nextActions(),
        ]);
    }

    private function commercialCampaign(): ?Campaign
    {
        return Campaign::query()
            ->where('code', self::STRESS_DEMO_CAMPAIGN_CODE)
            ->first()
            ?? Campaign::query()
                ->where('code', 'ecopark-pilot-1405')
                ->first();
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

    /** @return array<string, mixed> */
    private function finalDemoReport(?Campaign $campaign): array
    {
        if (! $campaign) {
            return [
                'isExecuted' => false,
                'title' => 'گزارش نهایی دمو هنوز ساخته نشده است',
                'campaignName' => null,
                'campaignCode' => null,
                'summary' => [],
                'roi' => $this->emptyRoi(),
                'audiences' => $this->emptyAudienceReports(),
                'recommendation' => 'ابتدا دموی کامل اکوپارک را از صفحه چرخه دمو اجرا کنید.',
                'actionHref' => '/admin/demo-cycle',
            ];
        }

        $campaignId = $campaign->id;
        $rewardIds = RewardDefinition::query()
            ->where('campaign_id', $campaignId)
            ->pluck('id');
        $userRewardIds = UserReward::query()
            ->where('campaign_id', $campaignId)
            ->pluck('id');
        $visits = Visit::query()->where('campaign_id', $campaignId)->count();
        $completedMissions = UserMissionProgress::query()
            ->whereHas('missionInstance', fn ($query) => $query->where('campaign_id', $campaignId))
            ->where('status', 'completed')
            ->count();
        $issuedRewards = $userRewardIds->count();
        $confirmedRedemptions = RewardRedemption::query()
            ->whereIn('user_reward_id', $userRewardIds)
            ->whereIn('status', ['confirmed', 'redeemed'])
            ->count();
        $participants = CampaignParticipant::query()
            ->where('campaign_id', $campaignId)
            ->count();
        $activeInventory = RewardInventoryAllocation::query()
            ->whereIn('reward_definition_id', $rewardIds)
            ->where('status', RecordStatus::Active->value)
            ->count();
        $investment = $this->campaignInvestment($campaign);
        $estimatedValue = $this->campaignEstimatedValue($campaign);
        $roiPercent = $investment > 0 ? (int) round((($estimatedValue - $investment) / $investment) * 100) : 0;
        $redemptionRate = $visits > 0 ? (int) round(($confirmedRedemptions / $visits) * 100) : 0;
        $isExecuted = $visits > 0 && $completedMissions > 0 && $confirmedRedemptions > 0;

        return [
            'isExecuted' => $isExecuted,
            'title' => $isExecuted ? 'گزارش نهایی قابل ارائه دمو' : 'گزارش نهایی دمو نیازمند اجراست',
            'campaignName' => $campaign->name,
            'campaignCode' => $campaign->code,
            'summary' => [
                ['label' => 'بازدید ثبت‌شده', 'value' => $visits],
                ['label' => 'ماموریت تکمیل‌شده', 'value' => $completedMissions],
                ['label' => 'پاداش صادرشده', 'value' => $issuedRewards],
                ['label' => 'مصرف تاییدشده', 'value' => $confirmedRedemptions],
                ['label' => 'واحد/اسپانسر عضو', 'value' => $participants],
                ['label' => 'سهم موجودی فعال', 'value' => $activeInventory],
            ],
            'roi' => [
                'investment' => $investment,
                'estimatedValue' => $estimatedValue,
                'roiPercent' => $roiPercent,
                'redemptionRate' => $redemptionRate,
            ],
            'audiences' => $this->audienceReports($campaign, [
                'visits' => $visits,
                'completedMissions' => $completedMissions,
                'issuedRewards' => $issuedRewards,
                'confirmedRedemptions' => $confirmedRedemptions,
                'participants' => $participants,
                'roiPercent' => $roiPercent,
                'redemptionRate' => $redemptionRate,
            ]),
            'recommendation' => $isExecuted
                ? 'پیشنهاد بعدی: نسخه استاندارد ۳۰ روزه برای همین مکان با یک اسپانسر و ۵ تا ۱۰ واحد عضو ارائه شود.'
                : 'برای فعال شدن متن فروش نهایی، دموی کامل را اجرا و مصرف پاداش را ثبت کنید.',
            'actionHref' => $isExecuted ? '/admin/demo-cycle' : '/admin/demo-cycle',
        ];
    }

    private function campaignInvestment(Campaign $campaign): int
    {
        return (int) CampaignSponsorship::query()
            ->where('campaign_id', $campaign->id)
            ->sum('budget_amount')
            + (int) SponsorProposal::query()
                ->where('campaign_id', $campaign->id)
                ->where('status', 'approved')
                ->sum('proposed_budget_amount');
    }

    private function campaignEstimatedValue(Campaign $campaign): int
    {
        return (int) CampaignSponsorship::query()
            ->where('campaign_id', $campaign->id)
            ->sum('contract_value')
            + (int) SponsorProposal::query()
                ->where('campaign_id', $campaign->id)
                ->where('status', 'approved')
                ->sum('estimated_value_amount');
    }

    /** @param array<string, int> $stats */
    private function audienceReports(Campaign $campaign, array $stats): array
    {
        $proposal = SponsorProposal::query()
            ->where('campaign_id', $campaign->id)
            ->where('status', 'approved')
            ->latest('created_at')
            ->first();
        $partnerReward = RewardDefinition::query()
            ->with('partnerAccount:id,name')
            ->where('campaign_id', $campaign->id)
            ->whereNotNull('partner_account_id')
            ->whereHas('partnerAccount', fn ($query) => $query->where('partner_type', '!=', 'sponsor'))
            ->first();

        return [
            [
                'title' => 'خلاصه مدیر مکان',
                'audience' => 'مدیر اجرایی مکان',
                'headline' => 'دمو نشان می‌دهد اکسپلوریا می‌تواند بازدید را به مشارکت، مسیر، پاداش و گزارش مدیریتی تبدیل کند.',
                'proofPoints' => [
                    $stats['visits'].' ورود/بازدید ثبت‌شده',
                    $stats['completedMissions'].' ماموریت تکمیل‌شده در مسیر',
                    $stats['participants'].' واحد یا اسپانسر عضو کمپین',
                ],
                'offer' => 'پکیج استاندارد ۳۰ روزه مکان',
                'nextStep' => 'جلسه تصمیم برای محدوده اجرا، تعداد واحدها و تاریخ شروع پایلوت.',
            ],
            [
                'title' => 'خلاصه اسپانسر',
                'audience' => 'اسپانسر کمپین',
                'headline' => 'حضور برند به ماموریت، گنج، جایزه و گزارش ROI وصل شده است.',
                'proofPoints' => [
                    'پیشنهاد اسپانسر: '.($proposal?->title ?? 'آماده تعریف'),
                    'ROI تخمینی '.$stats['roiPercent'].'٪',
                    'نرخ مصرف پاداش '.$stats['redemptionRate'].'٪',
                ],
                'offer' => 'بسته اسپانسر کمپین با جایزه/گنج و گزارش تعامل',
                'nextStep' => 'انتخاب سطح جایزه، پیام برند و شاخص‌های گزارش تمدید.',
            ],
            [
                'title' => 'خلاصه فروشگاه',
                'audience' => 'واحد عضو یا فروشگاه',
                'headline' => 'فروشگاه می‌تواند پیشنهاد بدهد، کد مصرف کند و اثر مراجعه را در پنل ببیند.',
                'proofPoints' => [
                    $stats['issuedRewards'].' پاداش صادرشده',
                    $stats['confirmedRedemptions'].' مصرف تاییدشده',
                    'نمونه واحد: '.($partnerReward?->partnerAccount?->name ?? 'واحد عضو دمو'),
                ],
                'offer' => 'عضویت واحد با پاداش قابل مصرف و گزارش مراجعه',
                'nextStep' => 'ثبت پیشنهاد واقعی واحد و تعریف سهم موجودی قابل مصرف.',
            ],
        ];
    }

    private function emptyRoi(): array
    {
        return [
            'investment' => 0,
            'estimatedValue' => 0,
            'roiPercent' => 0,
            'redemptionRate' => 0,
        ];
    }

    private function emptyAudienceReports(): array
    {
        return [
            [
                'title' => 'خلاصه مدیر مکان',
                'audience' => 'مدیر اجرایی مکان',
                'headline' => 'پس از اجرای دمو، خروجی مدیریتی مکان اینجا آماده می‌شود.',
                'proofPoints' => [],
                'offer' => 'پکیج پایلوت مکان',
                'nextStep' => 'اجرای دموی کامل اکوپارک.',
            ],
            [
                'title' => 'خلاصه اسپانسر',
                'audience' => 'اسپانسر کمپین',
                'headline' => 'پس از اجرای دمو، ROI اسپانسر و مصرف پاداش اینجا نمایش داده می‌شود.',
                'proofPoints' => [],
                'offer' => 'پکیج اسپانسر کمپین',
                'nextStep' => 'فعال‌سازی پیشنهاد اسپانسر و پاداش.',
            ],
            [
                'title' => 'خلاصه فروشگاه',
                'audience' => 'واحد عضو یا فروشگاه',
                'headline' => 'پس از اجرای دمو، اثر پاداش و مصرف کد فروشگاه اینجا نمایش داده می‌شود.',
                'proofPoints' => [],
                'offer' => 'پکیج واحد عضو',
                'nextStep' => 'ثبت مصرف پاداش توسط فروشگاه.',
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
