<?php

namespace App\Services;

use Illuminate\Support\Collection;

class MissionRewardBlueprintService
{
    public function __construct(private readonly VenueDesignContextService $venueDesignContext) {}

    /** @return array<string, mixed>|null */
    public function handoff(?string $code): ?array
    {
        if (blank($code)) {
            return null;
        }

        return collect($this->templates())
            ->map(fn (array $template) => $this->enrichTemplate($template))
            ->firstWhere('code', $code);
    }

    /** @return array<string, mixed> */
    public function overview(): array
    {
        $templates = collect($this->templates())->map(fn (array $template) => $this->enrichTemplate($template));
        $venueDesignContext = $this->withTemplateRecommendations($this->venueDesignContext->overview(), $templates);

        return [
            'stats' => [
                'templates' => $templates->count(),
                'missionFamilies' => $templates->pluck('family')->unique()->count(),
                'rewardModels' => $templates->pluck('rewardModel')->unique()->count(),
                'evidenceTypes' => $templates->pluck('evidenceType')->unique()->count(),
                'mvpFocus' => $templates->where('mvpPriority', '<', 99)->count(),
            ],
            'principles' => $this->principles(),
            'designFlow' => $this->designFlow(),
            'templates' => $templates->values(),
            'venueDesignContext' => $venueDesignContext,
            'scoringMatrix' => $this->scoringMatrix(),
            'rewardVault' => $this->rewardVault(),
            'globalPatterns' => $this->globalPatterns(),
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  Collection<int, array<string, mixed>>  $templates
     * @return array<string, mixed>
     */
    private function withTemplateRecommendations(array $context, Collection $templates): array
    {
        $context['venues'] = collect($this->arrayList($context['venues'] ?? null))
            ->map(function (array $venue) use ($templates): array {
                $venue['templateRecommendations'] = $this->templateRecommendationsForVenue($venue, $templates);

                return $venue;
            })
            ->all();

        return $context;
    }

    /**
     * @param  array<string, mixed>  $venue
     * @param  Collection<int, array<string, mixed>>  $templates
     * @return array<int, array<string, mixed>>
     */
    private function templateRecommendationsForVenue(array $venue, Collection $templates): array
    {
        $assets = collect($this->associativeArray($venue['designAssets'] ?? null));
        $useCounts = $assets
            ->map(fn (mixed $items): int => is_array($items) ? count($items) : 0)
            ->filter(fn (int $count): bool => $count > 0);

        if ($useCounts->isEmpty()) {
            return [];
        }

        $topUses = array_values($useCounts->sortDesc()->keys()->filter(fn (mixed $use): bool => is_string($use))->take(5)->all());

        return $templates
            ->map(function (array $template) use ($topUses, $venue): array {
                $score = $this->templateVenueScore($template, $topUses, $venue);

                return [
                    'code' => $template['code'],
                    'title' => $template['title'],
                    'family' => $template['family'],
                    'launchPhase' => $template['launchPhase'],
                    'matchScore' => $score,
                    'matchedUses' => $topUses,
                    'reason' => $this->recommendationReason($topUses),
                    'buildUrl' => '/admin/campaigns?blueprint='.$template['code'].'&venue='.$venue['id'].'&blueprint_action=build',
                ];
            })
            ->filter(fn (array $recommendation): bool => $recommendation['matchScore'] > 0)
            ->sortByDesc('matchScore')
            ->take(3)
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $template
     * @param  list<string>  $topUses
     * @param  array<string, mixed>  $venue
     */
    private function templateVenueScore(array $template, array $topUses, array $venue): int
    {
        $code = strtolower((string) $template['code']);
        $score = max(0, 120 - ((int) ($template['mvpPriority'] ?? 99) * 4));
        $facilities = collect($this->arrayList($venue['topFacilities'] ?? null))
            ->map(fn (array $facility): string => strtolower((string) ($facility['function'] ?? '').' '.implode(' ', $this->stringList($facility['campaignUses'] ?? null))))
            ->implode(' ');

        foreach ($topUses as $use) {
            $score += match ($use) {
                'qr' => str_contains($code, 'qr') || str_contains($code, 'route') || str_contains($code, 'treasure') ? 24 : 8,
                'mission' => str_contains($code, 'quest') || str_contains($code, 'route') || str_contains($code, 'challenge') ? 28 : 10,
                'treasure' => str_contains($code, 'treasure') || str_contains($code, 'clue') ? 34 : 8,
                'reward' => str_contains($code, 'foodcourt') || str_contains($code, 'taste') || str_contains($code, 'boost') ? 34 : 10,
                'sponsor' => str_contains($code, 'sponsored') || str_contains($code, 'brand') || str_contains($code, 'foodcourt') ? 30 : 8,
                'ad', 'display' => str_contains($code, 'hologram') || str_contains($code, 'online') || str_contains($code, 'brand') ? 24 : 6,
                default => 4,
            };
        }

        if (str_contains($facilities, 'retail') && (str_contains($code, 'foodcourt') || str_contains($code, 'boost'))) {
            $score += 22;
        }

        if (str_contains($facilities, 'discovery') && (str_contains($code, 'treasure') || str_contains($code, 'route') || str_contains($code, 'clue'))) {
            $score += 22;
        }

        return $score;
    }

    /** @param array<int, string> $uses */
    private function recommendationReason(array $uses): string
    {
        $labels = [
            'qr' => 'QR',
            'mission' => 'مأموریت',
            'treasure' => 'گنج',
            'reward' => 'پاداش',
            'sponsor' => 'اسپانسر',
            'ad' => 'تبلیغ',
            'display' => 'نمایشگر',
        ];

        $text = collect($uses)
            ->map(fn (string $use): string => $labels[$use] ?? $use)
            ->take(3)
            ->implode('، ');

        return 'بر اساس کارکردهای ثبت‌شده در ارزیابی مکان: '.$text;
    }

    /** @return array<int, array<string, mixed>> */
    private function principles(): array
    {
        return [
            [
                'title' => 'هدف رفتاری قبل از امتیاز',
                'body' => 'اول مشخص کنید کاربر باید چه رفتاری انجام دهد؛ بازدید، یادگیری، خرید، اشتراک گذاری، کشف یا تعامل با شریک. امتیاز فقط ابزار هدایت همان رفتار است.',
            ],
            [
                'title' => 'مدرک انجام مأموریت',
                'body' => 'هر مأموریت باید مدرک داشته باشد: اسکن QR، حضور مکانی، عکس، پاسخ، خرید، تأیید مجری یا ثبت خاطره. بدون مدرک، پاداش قابل دفاع نیست.',
            ],
            [
                'title' => 'پاداش نزدیک به تجربه',
                'body' => 'پاداش باید به همان مسیر بازدید ربط داشته باشد؛ نوشیدنی، کوپن فروشگاه، نشان دیجیتال، گنج خانوادگی، قرعه کشی اسپانسر یا دسترسی ویژه.',
            ],
            [
                'title' => 'پیشرفت مرحله ای',
                'body' => 'کاربر باید بداند کجای مسیر است، مرحله بعد چیست و چرا ادامه دادن ارزش دارد. مأموریت های کوچک، زنجیره های قابل فهم و بازخورد فوری مهم هستند.',
            ],
        ];
    }

    /** @return array<int, array<string, string>> */
    private function designFlow(): array
    {
        return [
            ['step' => '۱', 'title' => 'انتخاب الگو', 'body' => 'از گنجینه، الگوی مناسب با هاب، سناریو و مخاطب را انتخاب کنید.'],
            ['step' => '۲', 'title' => 'تعریف در مأموریت‌ها و پاداش‌ها', 'body' => 'در صفحه مأموریت‌ها و پاداش‌ها، عنوان، نوع مأموریت، امتیاز، مدرک انجام، گنج و پاداش را ثبت کنید.'],
            ['step' => '۳', 'title' => 'اتصال به کمپین', 'body' => 'مأموریت را به کمپین، هاب، شریک، QR، نمایشگر یا نقطه مسیر وصل کنید.'],
            ['step' => '۴', 'title' => 'تأیید اجرایی', 'body' => 'مجری یا مدیر هاب مسیر، متن راهنما، موجودی پاداش و امکان انجام در محل را کنترل کند.'],
            ['step' => '۵', 'title' => 'انتشار و پایش', 'body' => 'پس از انتشار، نرخ شروع، تکمیل، مصرف پاداش و خطاهای مسیر را پایش کنید.'],
        ];
    }

    /**
     * @param  array<string, mixed>  $template
     * @return array<string, mixed>
     */
    private function enrichTemplate(array $template): array
    {
        $plan = $this->executionPlans()[$template['code']] ?? $this->defaultExecutionPlan();
        $merged = array_merge($template, $plan);

        return array_merge($merged, [
            'missionPlan' => $this->missionPlan($merged),
            'rewardDesign' => $this->rewardDesign($merged),
        ]);
    }

    /**
     * @param  array<string, mixed>  $template
     * @return array<int, array<string, mixed>>
     */
    private function missionPlan(array $template): array
    {
        $steps = collect($this->stringList($template['userSteps'] ?? null))->values();
        $stepCount = max($steps->count(), 1);
        $basePoints = max((int) data_get($template, 'points.base', 120), 60);
        $tiers = ['bronze', 'silver', 'gold', 'diamond'];

        return $steps
            ->values()
            ->map(function (string $step, int $index) use ($template, $stepCount, $basePoints, $tiers): array {
                $templateCode = $this->missionTemplateCodeForStep($step, $index);
                $tierIndex = min((int) floor(($index / max($stepCount - 1, 1)) * 3), 3);
                $unlockMinPoints = $index === 0 ? 0 : (int) round(($basePoints / $stepCount) * $index);

                return [
                    'index' => $index + 1,
                    'userStep' => $step,
                    'recommendedTemplateCode' => $templateCode,
                    'title' => $this->missionTitleForStep($step),
                    'suggestedCodeSuffix' => 'step-'.($index + 1).'-'.$templateCode,
                    'suggestedUnlockMinPoints' => $unlockMinPoints,
                    'rewardTier' => $tiers[$tierIndex],
                    'routeIntent' => $this->routeIntentForStep($step, $template['navigationHint'] ?? ''),
                    'operationLink' => $index === 0 ? 'QR/نقطه شروع' : 'هاب، نقطه تماس یا مقصد بعدی',
                ];
            })
            ->all();
    }

    /**
     * @param  array<string, mixed>  $template
     * @return array<string, mixed>
     */
    private function rewardDesign(array $template): array
    {
        $tierKeys = ['bronze', 'silver', 'gold', 'diamond'];
        $optionCounts = ['bronze' => 5, 'silver' => 4, 'gold' => 3, 'diamond' => 2];
        $basket = collect($this->arrayList($template['rewardBasket'] ?? null))->values();

        $tiers = $basket->map(function (array $tier, int $index) use ($tierKeys, $optionCounts): array {
            $tierKey = $tierKeys[$index] ?? 'custom';
            $items = $this->stringList($tier['items'] ?? null);

            return [
                'tierKey' => $tierKey,
                'level' => $tier['level'] ?? $tierKey,
                'items' => $items,
                'suggestedOptionCount' => $optionCounts[$tierKey] ?? 1,
                'options' => $this->rewardOptionsForTier($tierKey, $items),
            ];
        })->all();

        return [
            'tiers' => $tiers,
            'hiddenTreasures' => [
                [
                    'code' => 'hidden-treasure-1',
                    'title' => 'گنج پنهان ۱',
                    'rule' => 'برای کاربرانی که مسیر را با بازگشت، مشارکت گروهی یا ارجاع به شریک تکمیل می کنند.',
                ],
                [
                    'code' => 'hidden-treasure-2',
                    'title' => 'گنج پنهان ۲',
                    'rule' => 'برای ادامه در کمپین بعدی و کسب امتیاز ویژه.',
                ],
            ],
        ];
    }

    /**
     * @param  list<string>  $items
     * @return list<string>
     */
    private function rewardOptionsForTier(string $tierKey, array $items): array
    {
        $primary = $items[0] ?? 'پاداش اصلی';
        $secondary = $items[1] ?? 'امتیاز تکمیل';
        $third = $items[2] ?? 'نشان مسیر';

        return match ($tierKey) {
            'bronze' => [
                $primary,
                $secondary,
                $third,
                $primary.' + امتیاز بازگشت',
                $secondary.' + ارجاع به یک همراه',
            ],
            'silver' => [
                $primary.' + '.$secondary,
                $primary.' + مشوق فروشگاه/واحد تجاری',
                $secondary.' + کوپن جایزه دار',
                $third.' + امتیاز گروهی',
            ],
            'gold' => [
                $primary.' + '.$secondary.' + امتیاز ویژه',
                $primary.' + پکیج خانوادگی',
                $third.' + ارجاع به هاب/شریک بعدی',
            ],
            'diamond' => [
                $primary.' + '.$secondary.' + '.$third,
                $primary.' + گنج پنهان ۱',
            ],
            default => $items,
        };
    }

    private function missionTemplateCodeForStep(string $step, int $index): string
    {
        $text = mb_strtolower($step);

        if ($index === 0 || str_contains($text, 'qr') || str_contains($text, 'اسکن') || str_contains($text, 'ورود')) {
            return 'scan-entry-qr';
        }

        if (str_contains($text, 'رأی') || str_contains($text, 'رای') || str_contains($text, 'نظر') || str_contains($text, 'پیشنهاد') || str_contains($text, 'تجربه') || str_contains($text, 'پرسش') || str_contains($text, 'پاسخ') || str_contains($text, 'محتوا') || str_contains($text, 'مشاهده') || str_contains($text, 'نمایشگر')) {
            return 'watch-place-story';
        }

        if (str_contains($text, 'گنج') || str_contains($text, 'جایزه') || str_contains($text, 'خاطره') || str_contains($text, 'عکس') || str_contains($text, 'چالش') || str_contains($text, 'تایید') || str_contains($text, 'تأیید') || str_contains($text, 'ثبت')) {
            return 'photo-memory-challenge';
        }

        if (str_contains($text, 'غرفه') || str_contains($text, 'کافه') || str_contains($text, 'فروشگاه') || str_contains($text, 'مقصد') || str_contains($text, 'حرکت') || str_contains($text, 'رفتن') || str_contains($text, 'نقطه')) {
            return 'discover-route-guide';
        }

        return 'discover-route-guide';
    }

    private function missionTitleForStep(string $step): string
    {
        return 'مأموریت: '.$step;
    }

    private function routeIntentForStep(string $step, string $navigationHint): string
    {
        $text = mb_strtolower($step.' '.$navigationHint);

        if (str_contains($text, 'فروشگاه') || str_contains($text, 'کوپن') || str_contains($text, 'غرفه')) {
            return 'اتصال به هاب/فروشگاه و نقطه تحویل پاداش';
        }

        if (str_contains($text, 'نقشه') || str_contains($text, 'مسیر') || str_contains($text, 'نقطه')) {
            return 'اتصال به نقطه مسیر و مقصد بعدی در نقشه عملیات';
        }

        return 'اتصال به چرخه کاربر و ثبت در مسیر عملیاتی کمپین';
    }

    /** @return array<string, array<string, mixed>> */
    private function executionPlans(): array
    {
        $plans = [
            'ecopark-online-treasure-map-game' => [
                'launchPhase' => 'MVP اولویت ۱',
                'mvpPriority' => 1,
                'priorityReason' => 'شروع تجربه از خانه، جذب اولیه، وایرال شدن و تبدیل کاربر آنلاین به بازدید حضوری.',
                'connectedSurfaces' => ['بازی آنلاین', 'صفحه کاربر', 'نقشه عملیات', 'QR شروع حضوری', 'داشبورد کمپین'],
                'rewardBasket' => [
                    ['level' => 'برنزی', 'items' => ['نشان کاشف آنلاین', 'کد شروع سریع در محل', 'امتیاز پیش ورود']],
                    ['level' => 'نقره‌ای', 'items' => ['کوپن شروع حضوری', 'سرنخ اختصاصی مسیر', 'ورود به قرعه کشی کوچک']],
                    ['level' => 'طلایی', 'items' => ['سبد ترکیبی رواق و غذا', 'دعوت خانوادگی', 'امتیاز دوبرابر برای اولین QR حضوری']],
                    ['level' => 'الماسی', 'items' => ['جایزه اسپانسر', 'بسته VIP بازدید', 'نمایش نام برنده در داشبورد/نمایشگر']],
                ],
                'nextBuildAction' => 'ساخت نسخه ساده بازی آنلاین نقشه گنج با چند نقطه قابل کلیک و کد شروع حضوری.',
            ],
            'hologram-backpack-campaign-starter' => [
                'launchPhase' => 'MVP اولویت ۱',
                'mvpPriority' => 2,
                'priorityReason' => 'پل اصلی بین بازی آنلاین و تجربه واقعی در محل؛ بهترین ابزار شروع مسیر در ورودی و نقاط پرتردد.',
                'connectedSurfaces' => ['کوله هولوگرامی', 'نمایشگر سیار', 'QR ورود', 'مجری میدانی', 'نقشه عملیات'],
                'rewardBasket' => [
                    ['level' => 'برنزی', 'items' => ['نشان ورود', 'امتیاز شروع', 'سرنخ اول']],
                    ['level' => 'نقره‌ای', 'items' => ['کد خوشامد فروشگاهی', 'اولویت دریافت مسیر پیشنهادی', 'شانس قرعه کشی همان روز']],
                    ['level' => 'طلایی', 'items' => ['کوپن ترکیبی رواق و طعم گردی', 'امتیاز تکمیل سریع مسیر', 'دعوت همراه']],
                    ['level' => 'الماسی', 'items' => ['جایزه ویژه شروع کمپین', 'ثبت تصویر یادگاری با نمایشگر', 'بسته اسپانسر']],
                ],
                'nextBuildAction' => 'اتصال QR شروع کوله به مسیر کاربر و ثبت اولین وضعیت حضور در کمپین.',
            ],
            'foodcourt-taste-tour-quest' => [
                'launchPhase' => 'MVP اولویت ۱',
                'mvpPriority' => 3,
                'priorityReason' => 'سریع ترین مسیر تبدیل تعامل به خرید، تجربه واقعی، رأی دادن و بازگشت کاربر به فودکورت.',
                'connectedSurfaces' => ['پنل هاب غذا', 'پنل فروشگاه', 'کوپن', 'QR غرفه', 'گزارش مصرف پاداش'],
                'rewardBasket' => [
                    ['level' => 'برنزی', 'items' => ['امتیاز طعم گردی', 'تخفیف کوچک', 'رأی ثبت شده']],
                    ['level' => 'نقره‌ای', 'items' => ['نوشیدنی یا آیتم کوچک', 'کوپن غرفه دوم', 'نشان خوش سلیقه']],
                    ['level' => 'طلایی', 'items' => ['سبد خوراک خانوادگی', 'دعوت به مسیر ویژه غذا', 'امتیاز چند غرفه']],
                    ['level' => 'الماسی', 'items' => ['جایزه جشنواره غذا', 'میزان تخفیف ویژه اسپانسر', 'قرعه کشی بزرگ فودکورت']],
                ],
                'nextBuildAction' => 'تعریف مصرف کوپن و تایید فروشگاه برای مسیر طعم گردی.',
            ],
            'ravaq-rewarded-shopping-treasure' => [
                'launchPhase' => 'MVP اولویت ۱',
                'mvpPriority' => 4,
                'priorityReason' => 'تبدیل بازدید به مراجعه فروشگاهی، خرید، کوپن و گزارش قابل فهم برای مدیر رواق و واحدهای تجاری.',
                'connectedSurfaces' => ['پنل مدیر رواق', 'پنل فروشگاه', 'مدیریت شرکا', 'کوپن فروشگاه', 'داشبورد کمپین'],
                'rewardBasket' => [
                    ['level' => 'برنزی', 'items' => ['کوپن کوچک فروشگاه', 'امتیاز مراجعه', 'نشان بازدید رواق']],
                    ['level' => 'نقره‌ای', 'items' => ['تخفیف خرید', 'هدیه کوچک', 'ورود به قرعه کشی رواق']],
                    ['level' => 'طلایی', 'items' => ['سبد هدیه چند فروشگاه', 'دعوت خانوادگی', 'کد خرید ویژه']],
                    ['level' => 'الماسی', 'items' => ['جایزه ویژه رواق', 'بسته VIP خرید', 'جایزه مشترک اسپانسر و فروشگاه']],
                ],
                'nextBuildAction' => 'اتصال الگو به فهرست فروشگاه ها و وضعیت موجودی/مصرف کوپن.',
            ],
            'brand-legendary-sponsored-treasure' => [
                'launchPhase' => 'MVP اولویت ۲',
                'mvpPriority' => 5,
                'priorityReason' => 'برای بالاترین سطح برنده ها و اسپانسرهای بزرگ؛ بهتر است بعد از پایدار شدن مسیر اصلی فعال شود.',
                'connectedSurfaces' => ['ادمین مرکزی', 'اسپانسر خارجی', 'قرعه کشی', 'گزارش حقوقی', 'داشبورد KPI'],
                'rewardBasket' => [
                    ['level' => 'برنزی', 'items' => ['امتیاز ورود به مسیر اسپانسر', 'نشان اسپانسر']],
                    ['level' => 'نقره‌ای', 'items' => ['کد برند', 'شانس قرعه کشی', 'محتوای اختصاصی']],
                    ['level' => 'طلایی', 'items' => ['بسته ترکیبی برند و اکوپارک', 'دعوت ویژه', 'امتیاز ویژه خانواده']],
                    ['level' => 'الماسی', 'items' => ['جایزه بزرگ اسپانسر', 'بسته VIP', 'اعلام رسمی برنده']],
                ],
                'nextBuildAction' => 'تعریف قوانین قرعه کشی، ظرفیت جایزه و سطح دسترسی ادمین مرکزی.',
            ],
        ];

        return array_replace($plans, $this->campaignPriorityPlans());
    }

    /** @return array<string, array<string, mixed>> */
    private function campaignPriorityPlans(): array
    {
        return [
            'ecopark-pilot-treasure-route' => $this->campaignPlan('MVP مرحله ۱: شروع مشترک و مسیر مادر', 1, 'مسیر مادر کمپین است: شروع مشترک، انتخاب شاخه، QRهای مرحله‌ای و رسیدن به گنج نهایی. همه مسیرهای دیگر می‌توانند زیرمجموعه این نقشه شوند.', ['نقشه گنج', 'QR مرحله‌ای', 'بازی آنلاین', 'نمایشگر سیار', 'پاداش ترکیبی'], ['نشان شروع مسیر', '۳۰ امتیاز ورود', 'سرنخ اول'], ['کوپن کوچک مسیر', 'باز شدن شاخه انتخابی', 'شانس قرعه‌کشی روزانه'], ['سبد ترکیبی رواق + غذا + تجربه', 'دعوت همراه', 'امتیاز تکمیل مسیر'], ['جایزه اسپانسر', 'تجربه VIP اکوپارک', 'نشان کاشف ارشد'], 'نقشه مادر را به سه شاخه کوتاه خانواده، هیجان و خرید/طعم وصل کنید تا مسیر کاربر طولانی و خسته‌کننده نشود.'),
            'ecopark-online-treasure-map-game' => $this->campaignPlan('MVP مرحله ۱: شروع از خانه', 2, 'ورود اولیه را قبل از حضور فیزیکی می‌سازد؛ کاربر در خانه مسیر را انتخاب می‌کند و با کد ادامه وارد اکوپارک می‌شود.', ['بازی آنلاین', 'شروع از خانه', 'کد ادامه حضوری', 'QR ورودی', 'دعوت خانوادگی'], ['نشان کاشف آنلاین', 'کد شروع سریع', 'امتیاز پیش‌ورود'], ['سرنخ اختصاصی مسیر', 'کوپن ورود حضوری', 'شانس قرعه‌کشی کوچک'], ['دوبرابر شدن امتیاز اولین QR حضوری', 'دعوت خانوادگی', 'سبد شروع طلایی'], ['بسته VIP بازدید', 'جایزه اسپانسر', 'نمایش نشان برنده'], 'صفحه بازی آنلاین را به انتخاب مسیرهای کوتاه و کد ادامه حضوری وصل کنید.'),
            'hologram-backpack-campaign-starter' => $this->campaignPlan('MVP مرحله ۱: شروع میدانی', 3, 'پل بین فضای واقعی و بازی است؛ نمایشگر سیار یا کوله هولوگرامی می‌تواند شروع مسیر را در نقاط پرتردد فعال کند.', ['کوله هولوگرامی', 'نمایشگر سیار', 'مجری میدانی', 'QR شروع', 'نقطه تجمع'], ['نشان ورود', 'سرنخ اول', 'امتیاز شروع'], ['کد خوشامد فروشگاهی', 'مسیر پیشنهادی', 'شانس جایزه همان روز'], ['کوپن ترکیبی رواق و طعم‌گردی', 'امتیاز تکمیل سریع', 'دعوت همراه'], ['عکس یادگاری با نمایشگر', 'بسته اسپانسر', 'جایزه شروع کمپین'], 'QR شروع کوله را به مسیر انتخابی کاربر و اولین وضعیت حضور در کمپین وصل کنید.'),
            'hidden-qr-treasure' => $this->campaignPlan('MVP مرحله ۲: کشف و حرکت', 4, 'برای جذاب کردن حرکت بین نقاط است؛ کاربر QRهای مخفی یا نیمه‌مخفی را پیدا می‌کند و با هر کشف یک پاداش کوچک می‌گیرد.', ['QR مخفی', 'نقشه عملیات', 'نقاط کم‌تردد', 'گنج مرحله‌ای', 'مجری میدانی'], ['نشان کشف اول', 'امتیاز سرنخ', 'پیام تبریک فوری'], ['کوپن کوچک', 'باز شدن سرنخ بعدی', 'شانس گنج نقره‌ای'], ['گنج طلایی مسیر', 'دعوت همراه', 'پاداش تکمیل چند QR'], ['جایزه نهایی مسیر', 'بسته اسپانسر داخلی', 'نشان کاشف ویژه'], 'QRهای مخفی را به نقاط خلوت‌تر وصل کنید تا هدایت جمعیت هم در طراحی مسیر لحاظ شود.'),
            'ravaq-rewarded-shopping-treasure' => $this->campaignPlan('MVP مرحله ۲: خرید و تبدیل', 5, 'مأموریت بازدید را به مراجعه فروشگاهی، کوپن، خرید و گزارش قابل فهم برای مدیر رواق و واحدهای تجاری تبدیل می‌کند.', ['پنل مدیر رواق', 'پنل فروشگاه', 'کوپن فروشگاه', 'مدیریت شرکا', 'QR واحد تجاری'], ['کوپن کوچک فروشگاه', 'امتیاز مراجعه', 'نشان بازدید رواق'], ['تخفیف خرید', 'هدیه کوچک', 'ورود به قرعه‌کشی رواق'], ['سبد هدیه چند فروشگاه', 'کد خرید ویژه', 'دعوت خانوادگی'], ['جایزه ویژه رواق', 'بسته VIP خرید', 'جایزه مشترک اسپانسر و فروشگاه'], 'این الگو را به فهرست فروشگاه‌ها، موجودی/ظرفیت کوپن و تأیید مصرف پاداش وصل کنید.'),
            'foodcourt-taste-tour-quest' => $this->campaignPlan('MVP مرحله ۲: طعم‌گردی', 6, 'سریع‌ترین مسیر تبدیل تعامل به خرید و تجربه واقعی است؛ هر توقف غذایی می‌تواند پاداش فوری کوچک و رأی کاربر داشته باشد.', ['باغ غذا', 'کافه', 'کوپن خوراکی', 'رأی‌گیری', 'QR غرفه'], ['امتیاز طعم‌گردی', 'رأی ثبت‌شده', 'تخفیف کوچک'], ['نوشیدنی یا آیتم کوچک', 'کوپن غرفه دوم', 'نشان خوش‌سلیقه'], ['سبد خوراک خانوادگی', 'امتیاز چند غرفه', 'دعوت به مسیر ویژه غذا'], ['جایزه جشنواره غذا', 'قرعه‌کشی بزرگ فودکورت', 'تخفیف ویژه اسپانسر'], 'برای هر غرفه ظرفیت و ساعت شلوغی تعریف کنید تا پاداش‌ها باعث ازدحام ناخواسته نشوند.'),
            'family-team-route' => $this->campaignPlan('MVP مرحله ۲: مسیر خانواده', 7, 'برای مشارکت گروهی و خانوادگی است؛ امتیاز تیمی باعث می‌شود والدین، کودک و همراهان همزمان درگیر شوند.', ['پاسپورت خانواده', 'دعوت همراه', 'چالش عکس', 'اقیانوس پارک', 'مسیر کودک'], ['نشان خانواده کاشف', 'امتیاز تیمی', 'سرنخ کودک'], ['کوپن خانوادگی کوچک', 'چالش عکس', 'شانس قرعه‌کشی خانواده'], ['پاس خانوادگی', 'سبد خوراک و خرید', 'دعوت به مسیر ویژه کودک'], ['جایزه خانواده برتر', 'تجربه VIP خانوادگی', 'نشان عمومی تیم برنده'], 'مسیر خانواده را کوتاه و چندانتخابی نگه دارید تا برای کودک و والدین قابل انجام باشد.'),
            'ocean-park-family-passport' => $this->campaignPlan('MVP مرحله ۲: شاخه اقیانوس پارک', 8, 'شاخه کودک و خانواده را با کشف موجودات دریایی، عکس، سؤال کوتاه و پاسپورت خانوادگی فعال می‌کند.', ['اقیانوس پارک', 'کودک و خانواده', 'پاسپورت خانوادگی', 'عکس یادگاری', 'یادگیری کوتاه'], ['نشان موجود دریایی', 'امتیاز کودک', 'سرنخ خانوادگی'], ['کارت امتیاز خانواده', 'هدیه کوچک کودک', 'شانس مسیر خانوادگی'], ['پاس خانوادگی', 'سبد خانواده', 'دعوت بازگشت'], ['جایزه ویژه خانواده', 'تجربه اختصاصی کودک', 'نشان کاشف اقیانوس'], 'این شاخه را به مسیر خانواده وصل کنید و مدرک انجام را QR + عکس یا پاسخ کوتاه قرار دهید.'),
            'skate-park-skill-leaderboard' => $this->campaignPlan('MVP مرحله ۲: شاخه هیجان', 9, 'برای نوجوانان و جوانان مناسب است؛ چالش مهارت، امتیاز سریع و جدول رتبه‌بندی انرژی کمپین را بالا می‌برد.', ['اسکیت پارک', 'نوجوانان', 'لیدربورد', 'چالش مهارت', 'نشان هیجان'], ['امتیاز مهارت', 'نشان شروع هیجان', 'ثبت تلاش'], ['ارتقای رتبه', 'کوپن کوچک', 'شانس چالش روزانه'], ['نشان طلایی مهارت', 'دعوت به رقابت ویژه', 'پاداش ورزشی'], ['جایزه نفرات برتر', 'نمایش رتبه روی نمایشگر', 'اسپانسر ورزشی'], 'قوانین ایمنی، سن و تأیید مجری باید قبل از فعال شدن چالش مشخص باشد.'),
            'gonbad-mina-star-treasure' => $this->campaignPlan('MVP مرحله ۲: شاخه علمی', 10, 'شاخه علمی و آموزشی را با سؤال کوتاه، کشف ستاره‌ای و نشان یادگیری به تجربه بازی وصل می‌کند.', ['گنبد مینا', 'پرسش علمی', 'نشان آموزشی', 'مدرسه و خانواده', 'QR آموزشی'], ['نشان ستاره', 'امتیاز پاسخ', 'سرنخ علمی'], ['تخفیف کتاب یا آیتم آموزشی', 'امتیاز تیمی', 'شانس مسیر علمی'], ['دعوت به رویداد علمی', 'سبد آموزشی خانواده', 'نشان طلایی یادگیری'], ['جایزه علمی اسپانسر', 'تجربه اختصاصی آموزشی', 'نشان کاشف ستاره'], 'سؤال‌ها باید کوتاه، قابل فهم و بدون توقف طولانی در مسیر باشند.'),
            'tabiat-bridge-urban-photo-clue' => $this->campaignPlan('MVP مرحله ۳: مسیر شهری و روایت', 11, 'پل طبیعت و پل‌های ابریشم را به روایت شهری، عکس و کشف مسیر تبدیل می‌کند و مسیرهای بیرونی اکوپارک را فعال نگه می‌دارد.', ['پل طبیعت', 'پل ابریشم ۱ و ۲', 'عکس شهری', 'روایت مسیر', 'گردشگری'], ['نشان روایت شهری', 'امتیاز عکس', 'سرنخ پل'], ['قاب عکس دیجیتال', 'کوپن بازگشت', 'شانس گنج شهری'], ['مسیر طلایی شهری', 'دعوت همراه', 'پاداش بازدید چند نقطه'], ['جایزه گردشگری', 'نمایش عکس منتخب', 'بسته اسپانسر شهری'], 'این مسیر برای روزهای خلوت یا هدایت بازدیدکننده به نقاط باز و پیاده‌روی بسیار مناسب است.'),
            'quiet-hour-demand-boost' => $this->campaignPlan('MVP مرحله ۳: توزیع هوشمند جمعیت', 12, 'مأموریت‌ها را برای روزها یا ساعت‌های خلوت جذاب‌تر می‌کند؛ این مورد طراحی کمپین است نه داشبورد مدیریتی، اما داده آن بعدا در داشبورد تحلیل می‌آید.', ['ساعت خلوت', 'روز خلوت', 'هدایت جمعیت', 'پاداش زمان‌دار', 'ظرفیت هاب'], ['امتیاز حضور در زمان خلوت', 'نشان آرامش', 'سرنخ سریع'], ['کوپن زمان‌دار', 'پاداش بدون صف', 'شانس جایزه خلوت'], ['امتیاز دوبرابر', 'سبد زمان خلوت', 'دعوت بازگشت'], ['جایزه ویژه روز خلوت', 'تجربه VIP کم‌ازدحام', 'پاداش اسپانسر'], 'در تعریف مأموریت، بازه زمانی و ظرفیت هر هاب را مشخص کنید تا مسیر به نقاط خلوت هدایت شود.'),
            'photo-memory-challenge' => $this->campaignPlan('مخزن کمپین‌های تکمیلی: محتوا و اشتراک‌گذاری', 13, 'برای تولید محتوای کاربر و خاطره‌سازی است و می‌تواند به همه شاخه‌ها اضافه شود.', ['چالش عکس', 'ثبت خاطره', 'اشتراک‌گذاری', 'خانواده', 'فضای فرهنگی'], ['نشان خاطره', 'امتیاز عکس', 'قاب دیجیتال'], ['شانس انتخاب عکس', 'کوپن کوچک', 'امتیاز اشتراک‌گذاری'], ['نمایش عکس منتخب', 'دعوت خانوادگی', 'سبد خاطره'], ['جایزه محتوای برتر', 'نمایش روی نمایشگر', 'بسته اسپانسر'], 'این الگو را به عنوان افزونه قابل ترکیب روی همه مسیرهای اصلی نگه دارید.'),
            'checkin-streak' => $this->campaignPlan('مخزن کمپین‌های تکمیلی: بازگشت', 14, 'برای چندروز کردن کمپین و بازگشت کاربر مناسب است؛ زنجیره حضور می‌تواند سطح پاداش را بالا ببرد.', ['بازگشت کاربر', 'چند روزه', 'check-in', 'وفاداری', 'سطح پاداش'], ['امتیاز حضور', 'نشان روز اول', 'سرنخ بازگشت'], ['کوپن بازگشت', 'ارتقای سطح نقره‌ای', 'شانس روز دوم'], ['پاداش طلایی زنجیره', 'دعوت همراه', 'امتیاز چندروزه'], ['جایزه وفاداری', 'سطح الماسی', 'دعوت ویژه'], 'برای جلوگیری از تقلب، فاصله زمانی و مکانی بین check-inها را کنترل کنید.'),
            'brand-legendary-sponsored-treasure' => $this->campaignPlan('مخزن کمپین‌های تکمیلی: اسپانسر خارجی', 15, 'برای بالاترین سطح برنده‌ها و اسپانسرهای بزرگ است؛ بهتر است پس از پایدار شدن مسیرهای اصلی فعال شود.', ['ادمین مرکزی', 'اسپانسر خارجی', 'قرعه‌کشی', 'جایزه بزرگ', 'قوانین حقوقی'], ['نشان اسپانسر', 'امتیاز ورود', 'محتوای اختصاصی'], ['کد برند', 'شانس قرعه‌کشی', 'پاداش کوچک اسپانسر'], ['بسته ترکیبی برند و اکوپارک', 'دعوت ویژه', 'امتیاز خانواده'], ['جایزه بزرگ اسپانسر', 'بسته VIP', 'اعلام رسمی برنده'], 'قوانین قرعه‌کشی، ظرفیت جایزه و سطح دسترسی ادمین مرکزی باید مشخص باشد.'),
        ];
    }

    /** @return array<string, mixed> */
    /**
     * @param  list<string>  $connectedSurfaces
     * @param  list<string>  $bronze
     * @param  list<string>  $silver
     * @param  list<string>  $gold
     * @param  list<string>  $diamond
     * @return array<string, mixed>
     */
    private function campaignPlan(string $launchPhase, int $mvpPriority, string $priorityReason, array $connectedSurfaces, array $bronze, array $silver, array $gold, array $diamond, string $nextBuildAction): array
    {
        return [
            'launchPhase' => $launchPhase,
            'mvpPriority' => $mvpPriority,
            'priorityReason' => $priorityReason,
            'connectedSurfaces' => $connectedSurfaces,
            'rewardBasket' => [
                ['level' => 'برنزی', 'items' => $bronze],
                ['level' => 'نقره‌ای', 'items' => $silver],
                ['level' => 'طلایی', 'items' => $gold],
                ['level' => 'الماسی', 'items' => $diamond],
            ],
            'nextBuildAction' => $nextBuildAction,
        ];
    }

    /** @return array<string, mixed> */
    private function defaultExecutionPlan(): array
    {
        return [
            'launchPhase' => 'مخزن ایده',
            'mvpPriority' => 99,
            'priorityReason' => 'فعلاً به عنوان الگوی پشتیبان نگهداری شود و بعد از مسیر اصلی به اجرا برسد.',
            'connectedSurfaces' => ['گنجینه الگوها', 'تعریف مأموریت', 'نقشه عملیات کمپین'],
            'rewardBasket' => [
                ['level' => 'برنزی', 'items' => ['امتیاز پایه', 'نشان دیجیتال']],
                ['level' => 'نقره‌ای', 'items' => ['کوپن کوچک', 'ورود به قرعه کشی']],
                ['level' => 'طلایی', 'items' => ['سبد ترکیبی کوچک', 'دعوت همراه']],
                ['level' => 'الماسی', 'items' => ['جایزه ویژه در صورت اتصال اسپانسر']],
            ],
            'nextBuildAction' => 'در زمان طراحی کمپین، با یکی از مسیرهای MVP ترکیب شود.',
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function templates(): array
    {
        return [
            [
                'code' => 'photo-memory-challenge',
                'title' => 'چالش عکس و ثبت خاطره',
                'family' => 'تجربه و محتوا',
                'bestFor' => 'هاب فرهنگی، آکواریوم، فضای تفریحی، رویداد خانوادگی',
                'missionGoal' => 'کاربر یک عکس یا خاطره کوتاه از نقطه مشخص ثبت می کند.',
                'evidenceType' => 'عکس + متن کوتاه + موقعیت یا QR',
                'userSteps' => ['اسکن QR نقطه', 'خواندن چالش', 'ثبت عکس یا خاطره', 'ارسال برای تأیید یا انتشار کنترل شده'],
                'navigationHint' => 'روی نقشه، نقطه عکاسی با نشانه محیطی و مسیر کوتاه از ورودی نمایش داده شود.',
                'points' => ['base' => 120, 'bonus' => '۴۰ امتیاز برای کیفیت محتوا یا اشتراک خانوادگی'],
                'rewardModel' => 'نشان دیجیتال + قرعه کشی',
                'rewardIdeas' => ['نشان خاطره ساز اکوپارک', 'ورود به قرعه کشی جایزه اسپانسر', 'چاپ عکس منتخب در نمایشگر'],
                'stakeholders' => ['مجری میدانی', 'مدیر هاب', 'تیم محتوا'],
                'riskControl' => 'بازبینی محتوا قبل از نمایش عمومی و جلوگیری از ثبت تصویر نامناسب.',
            ],
            [
                'code' => 'hidden-qr-treasure',
                'title' => 'کشف QR پنهان و گنج کوچک',
                'family' => 'کشف گنج',
                'bestFor' => 'مسیرهای پیاده، فروشگاه ها، هاب علمی، غرفه های اسپانسر',
                'missionGoal' => 'کاربر با راهنمایی مرحله ای یک QR نیمه پنهان را پیدا می کند.',
                'evidenceType' => 'اسکن QR پنهان',
                'userSteps' => ['دریافت سرنخ اول', 'حرکت به نقطه بعدی', 'کشف QR', 'باز شدن گنج یا کوپن'],
                'navigationHint' => 'نقشه فقط محدوده تقریبی را نشان دهد تا حس کشف از بین نرود.',
                'points' => ['base' => 180, 'bonus' => '۶۰ امتیاز برای کشف در زمان کوتاه'],
                'rewardModel' => 'گنج فوری',
                'rewardIdeas' => ['کوپن نوشیدنی', 'تخفیف خرید اکسسوری', 'نشان کاشف مسیر'],
                'stakeholders' => ['مجری میدانی', 'فروشگاه عضو', 'مدیر هاب'],
                'riskControl' => 'QR باید قابل دسترس، امن و بدون ایجاد ازدحام نصب شود.',
            ],
            [
                'code' => 'learning-mini-quest',
                'title' => 'مأموریت آموزشی سه سوالی',
                'family' => 'یادگیری و آگاهی',
                'bestFor' => 'هاب علمی، آکواریوم، موزه، رویداد کودک و خانواده',
                'missionGoal' => 'کاربر بعد از مشاهده یک محتوا یا نمایشگر به چند سوال کوتاه پاسخ می دهد.',
                'evidenceType' => 'پاسخ صحیح + زمان تکمیل',
                'userSteps' => ['مشاهده محتوای آموزشی', 'پاسخ به سوال ها', 'دریافت امتیاز و باز شدن مرحله بعد'],
                'navigationHint' => 'در نقشه، مسیر از نمایشگر آموزشی تا نقطه سوال مشخص باشد.',
                'points' => ['base' => 150, 'bonus' => '۵۰ امتیاز برای پاسخ کامل در تلاش اول'],
                'rewardModel' => 'امتیاز + سطح',
                'rewardIdeas' => ['نشان دانای اکوپارک', 'باز شدن مأموریت پیشرفته', 'امتیاز تیم خانوادگی'],
                'stakeholders' => ['مدیر هاب علمی', 'تیم محتوا', 'مجری میدانی'],
                'riskControl' => 'سوال ها کوتاه، غیرمبهم و متناسب با سن مخاطب باشند.',
            ],
            [
                'code' => 'partner-coupon-loop',
                'title' => 'مسیر کوپن شریک تجاری',
                'family' => 'تجاری و فروش',
                'bestFor' => 'کافه، فروشگاه، رواق تجاری، اسپانسر داخلی',
                'missionGoal' => 'کاربر با انجام یک عمل ساده، کوپن قابل مصرف نزد شریک دریافت می کند.',
                'evidenceType' => 'QR مصرف کوپن یا تأیید فروشگاه',
                'userSteps' => ['انجام مأموریت مقدماتی', 'دریافت کوپن', 'مراجعه به فروشگاه', 'اسکن یا تأیید مصرف'],
                'navigationHint' => 'مسیر از نقطه مأموریت تا فروشگاه روی نقشه مشخص شود.',
                'points' => ['base' => 100, 'bonus' => 'امتیاز اضافه پس از مصرف کوپن'],
                'rewardModel' => 'کوپن قابل مصرف',
                'rewardIdeas' => ['۱۰٪ تخفیف', 'هدیه نوشیدنی کوچک', 'اکسسوری با قیمت ویژه'],
                'stakeholders' => ['مدیر فروشگاه', 'مدیر هاب', 'ادمین کمپین'],
                'riskControl' => 'موجودی، زمان اعتبار و سقف مصرف کوپن باید روشن باشد.',
            ],
            [
                'code' => 'family-team-route',
                'title' => 'مسیر تیمی خانواده',
                'family' => 'تیمی و خانوادگی',
                'bestFor' => 'پروژه های بزرگ، شهربازی، برج، اکوپارک',
                'missionGoal' => 'چند عضو خانواده هرکدام بخشی از مسیر را انجام می دهند و امتیاز مشترک می گیرند.',
                'evidenceType' => 'چند QR + تکمیل گروهی',
                'userSteps' => ['ساخت تیم', 'تقسیم مأموریت ها', 'تکمیل نقاط مسیر', 'باز شدن گنج خانوادگی'],
                'navigationHint' => 'نقشه باید مسیر کوتاه، متوسط و کامل را برای خانواده ها تفکیک کند.',
                'points' => ['base' => 300, 'bonus' => '۱۰۰ امتیاز برای تکمیل بدون خروج از مسیر'],
                'rewardModel' => 'گنج خانوادگی',
                'rewardIdeas' => ['عکس یادگاری', 'کوپن خانوادگی', 'نشان خانواده کاشف'],
                'stakeholders' => ['مجری میدانی', 'مدیر پروژه', 'اسپانسر مسیر'],
                'riskControl' => 'مسیر برای کودک و سالمند ایمن و قابل انجام باشد.',
            ],
            [
                'code' => 'sponsor-prize-draw',
                'title' => 'قرعه کشی اسپانسر پس از تکمیل مسیر',
                'family' => 'اسپانسری',
                'bestFor' => 'اسپانسر خارجی، برند بزرگ، کمپین فصلی',
                'missionGoal' => 'کاربر با تکمیل چند مرحله وارد قرعه کشی اسپانسر می شود.',
                'evidenceType' => 'تکمیل زنجیره مأموریت + رضایت نامه',
                'userSteps' => ['تکمیل مأموریت های لازم', 'پذیرش شرایط', 'ثبت ورود به قرعه کشی', 'نمایش کد پیگیری'],
                'navigationHint' => 'مکان فیزیکی الزامی نیست؛ نقطه تماس تبلیغاتی و مرحله ورود باید در نقشه عملیات روشن باشد.',
                'points' => ['base' => 220, 'bonus' => 'شانس اضافه برای تکمیل مسیر کامل'],
                'rewardModel' => 'قرعه کشی',
                'rewardIdeas' => ['جایزه اسپانسر', 'کد تخفیف برند', 'دعوت به رویداد ویژه'],
                'stakeholders' => ['ادمین مرکزی', 'ادمین منطقه ای', 'اسپانسر خارجی'],
                'riskControl' => 'شرایط قرعه کشی، حریم خصوصی و نحوه اعلام برنده باید شفاف باشد.',
            ],
            [
                'code' => 'display-sync-mission',
                'title' => 'مأموریت همزمان با نمایشگر',
                'family' => 'نمایشگر و تبلیغات',
                'bestFor' => 'نمایشگر ثابت، نمایشگر سیار، کمپین تبلیغاتی محیطی',
                'missionGoal' => 'کاربر پس از دیدن محتوای نمایشگر، کد یا QR مرتبط را وارد می کند.',
                'evidenceType' => 'کد نمایشگر + زمان مشاهده',
                'userSteps' => ['مشاهده پیام نمایشگر', 'اسکن QR یا ورود کد', 'پاسخ یا انتخاب', 'دریافت امتیاز'],
                'navigationHint' => 'نمایشگر باید روی نقشه عملیات به محدوده پخش و مأموریت بعدی وصل باشد.',
                'points' => ['base' => 90, 'bonus' => 'امتیاز اضافه برای واکنش سریع'],
                'rewardModel' => 'امتیاز فوری',
                'rewardIdeas' => ['امتیاز پخش زنده', 'باز شدن محتوای مخفی', 'کوپن اسپانسر'],
                'stakeholders' => ['مدیر نمایشگر', 'تیم تبلیغات', 'مجری کمپین'],
                'riskControl' => 'زمان بندی نمایش و مأموریت باید همگام باشد تا کاربر سردرگم نشود.',
            ],
            [
                'code' => 'checkin-streak',
                'title' => 'زنجیره حضور و بازدید',
                'family' => 'وفاداری و بازگشت',
                'bestFor' => 'پروژه های چندروزه، رویدادهای فصلی، مراکز تجاری',
                'missionGoal' => 'کاربر در چند روز یا چند نقطه check-in می کند و سطح می گیرد.',
                'evidenceType' => 'حضور مکانی یا QR متوالی',
                'userSteps' => ['ثبت حضور اول', 'باز شدن سطح بعد', 'تکمیل زنجیره', 'دریافت پاداش سطحی'],
                'navigationHint' => 'مسیر بازگشت یا نقاط روز بعد باید در نقشه کاربر روشن باشد.',
                'points' => ['base' => 80, 'bonus' => 'پاداش زنجیره برای چند check-in پشت سر هم'],
                'rewardModel' => 'سطح و نشان',
                'rewardIdeas' => ['نشان بازدیدکننده وفادار', 'سطح نقره ای/طلایی', 'کوپن بازگشت'],
                'stakeholders' => ['ادمین کمپین', 'مدیر مکان', 'فروشگاه ها'],
                'riskControl' => 'تقلب مکانی و اسکن تکراری باید محدود شود.',
            ],

            [
                'code' => 'ecopark-pilot-treasure-route',
                'title' => 'مسیر کشف گنج اکوپارک',
                'family' => 'اکوپارک عباس آباد',
                'bestFor' => 'کمپین اصلی اکوپارک با حضور چند هاب مانند رواق، گنبد مینا، اقیانوس پارک، فودکورت و مسیرهای پیاده روی',
                'missionGoal' => 'بازدیدکننده با اسکن QR در چند نقطه، سرنخ ها را جمع می کند و در پایان یک گنج یا پاداش می گیرد.',
                'evidenceType' => 'اسکن QRهای مرحله ای + تکمیل مسیر + ثبت گنج نهایی',
                'userSteps' => ['شروع از پیام نمایشگر یا کوله هولوگرامی', 'اسکن QR ورود', 'انتخاب مسیر پیشنهادی بر اساس علاقه، سن یا زمان آزاد', 'رفتن به نقطه های مأموریت', 'جمع کردن سرنخ ها و رسیدن به جایزه نهایی'],
                'navigationHint' => 'نقشه باید مسیر پیشنهادی را با شماره مرحله ها نشان دهد و کاربر بداند بعد از هر نقطه باید به کدام مقصد برود.',
                'points' => ['base' => 260, 'bonus' => 'گنج طلایی برای تکمیل همه مرحله ها و ثبت بازگشت'],
                'rewardModel' => 'گنج مرحله ای + امتیاز KPI',
                'rewardIdeas' => ['کوپن خرید اکوپارک', 'دعوت خانوادگی', 'تخفیف در فروشگاه X', 'نشان مسیر طلایی'],
                'stakeholders' => ['ادمین کمپین', 'مدیر پروژه', 'مدیر هاب', 'فروشگاه ها', 'اسپانسر داخلی'],
                'riskControl' => 'برای هر مرحله زمان و فاصله منطقی تعریف شود تا QRها پشت سر هم و بدون حضور واقعی ثبت نشوند.',
            ],
            [
                'code' => 'hologram-backpack-campaign-starter',
                'title' => 'شروع مأموریت با کوله هولوگرامی',
                'family' => 'اکوپارک عباس آباد',
                'bestFor' => 'مجری میدانی، نمایشگر سیار، نقطه های پرتردد و شروع مسیر بازدیدکننده',
                'missionGoal' => 'کاربر پیام کوتاه روی نمایشگر را می بیند، QR را اسکن می کند و اولین مرحله فعال می شود.',
                'evidenceType' => 'اسکن QR نمایشگر سیار + زمان و نقطه شروع',
                'userSteps' => ['دیدن پیام کمپین', 'اسکن QR روی نمایشگر یا کارت همراه', 'انتخاب مسیر مناسب', 'دریافت اولین سرنخ'],
                'navigationHint' => 'نمایشگر سیار باید نزدیک ورودی، رواق یا نقطه تجمع باشد و پس از اسکن، کاربر را به اولین مقصد هدایت کند.',
                'points' => ['base' => 70, 'bonus' => 'اگر کاربر همان روز یک مأموریت دیگر هم انجام دهد'],
                'rewardModel' => 'امتیاز شروع + نشان ورود',
                'rewardIdeas' => ['نشان کاشف تازه', 'کد خوشامد', 'شانس قرعه کشی', 'پیشنهاد ویژه همان روز'],
                'stakeholders' => ['مجری میدانی', 'تیم نمایشگر', 'ادمین کمپین'],
                'riskControl' => 'ظرفیت جمعیت و ازدحام کنار نمایشگر باید کنترل شود.',
            ],
            [
                'code' => 'ravaq-rewarded-shopping-treasure',
                'title' => 'گنج خرید در رواق تجاری',
                'family' => 'اکوپارک عباس آباد',
                'bestFor' => 'مدیر هاب تجاری، فروشگاه ها، کافه ها و واحدهای فرهنگی رواق',
                'missionGoal' => 'کاربر با ورود به هاب، دیدن یک پیشنهاد، تعامل کوتاه و اسکن QR فروشگاه، پاداش تجاری دریافت می کند.',
                'evidenceType' => 'QR فروشگاه منتخب + تایید تعامل توسط واحد تجاری',
                'userSteps' => ['ورود به مسیر رواق', 'دیدن پیشنهاد هاب', 'اسکن QR فروشگاه', 'انجام تعامل کوچک', 'ثبت پاداش در کیف امتیاز'],
                'navigationHint' => 'در نقشه هاب، مسیر کوتاه بین ورودی رواق و فروشگاه هدف مشخص شود.',
                'points' => ['base' => 140, 'bonus' => 'اگر کاربر خرید یا بازدید دوم انجام دهد'],
                'rewardModel' => 'کوپن داخلی + امتیاز وفاداری',
                'rewardIdeas' => ['تخفیف خرید', 'هدیه کوچک', 'کد نوشیدنی', 'قرعه کشی رواق'],
                'stakeholders' => ['مدیر رواق', 'مدیر فروشگاه', 'ادمین کمپین', 'بازدیدکننده'],
                'riskControl' => 'هر کوپن باید ظرفیت، تاریخ مصرف و سقف استفاده داشته باشد.',
            ],
            [
                'code' => 'foodcourt-taste-tour-quest',
                'title' => 'مأموریت طعم گردی باغ غذا',
                'family' => 'اکوپارک عباس آباد',
                'bestFor' => 'باغ غذا، فودکورت، رستوران ها، کافه ها و جشنواره های خوراک',
                'missionGoal' => 'کاربر از چند غرفه یا کافه سرنخ می گیرد، رأی می دهد و در پایان شانس جایزه غذایی می گیرد.',
                'evidenceType' => 'اسکن QR غرفه + رأی یا ثبت تجربه کوتاه',
                'userSteps' => ['انتخاب مسیر طعم گردی', 'رفتن به غرفه اول', 'دریافت پیشنهاد ویژه', 'رأی دادن به یک آیتم', 'ثبت گنج غذایی در پایان مسیر'],
                'navigationHint' => 'نقشه باید چیدمان غرفه ها را ساده نشان دهد و مسیر پیشنهادی را کوتاه نگه دارد.',
                'points' => ['base' => 130, 'bonus' => 'اگر کاربر چند غرفه را پشت سر هم کامل کند'],
                'rewardModel' => 'پاداش خوشمزه',
                'rewardIdeas' => ['نوشیدنی رایگان', 'تخفیف غذا', 'ارتقای سفارش', 'قرعه کشی خانوادگی'],
                'stakeholders' => ['فودکورت', 'کافه', 'مدیر هاب غذا', 'ادمین کمپین'],
                'riskControl' => 'ظرفیت پذیرش و ساعات شلوغی هر واحد در تنظیم مأموریت لحاظ شود.',
            ],
            [
                'code' => 'gonbad-mina-star-treasure',
                'title' => 'گنج ستاره ای گنبد مینا',
                'family' => 'اکوپارک عباس آباد',
                'bestFor' => 'هاب علمی آموزشی، خانواده ها، نوجوانان و بازدیدهای مدرسه ای',
                'missionGoal' => 'کاربر یک پرسش ساده علمی را پاسخ می دهد و با اسکن QR آموزشی، نشان علمی دریافت می کند.',
                'evidenceType' => 'پاسخ کوتاه + QR نقطه آموزشی',
                'userSteps' => ['رفتن به محدوده گنبد مینا', 'دریافت پرسش علمی', 'ثبت پاسخ کوتاه', 'دریافت نشان ستاره ای یا سرنخ گنج بعدی'],
                'navigationHint' => 'مسیر باید از ورودی هاب به نقطه امن آموزشی هدایت شود و ازدحام در جلوی ورودی کنترل گردد.',
                'points' => ['base' => 170, 'bonus' => 'اگر کاربر همراه کودک یا خانواده باشد'],
                'rewardModel' => 'نشان علمی + گنج آموزشی',
                'rewardIdeas' => ['نشان کاشف ستاره', 'دعوت به رویداد علمی', 'تخفیف کتاب کودک', 'امتیاز تیمی'],
                'stakeholders' => ['مدیر گنبد مینا', 'تیم علمی', 'مدیر پروژه', 'خانواده ها'],
                'riskControl' => 'سؤال ها باید کوتاه، قابل فهم و بدون نیاز به توقف طولانی باشند.',
            ],
            [
                'code' => 'ocean-park-family-passport',
                'title' => 'پاسپورت خانوادگی اقیانوس پارک',
                'family' => 'اکوپارک عباس آباد',
                'bestFor' => 'خانواده ها، کودکان، مسیرهای آموزشی، عکس یادگاری و تجربه گروهی',
                'missionGoal' => 'خانواده در چند ایستگاه مهر یا امتیاز می گیرد و در پایان پاسپورت خانوادگی تکمیل می شود.',
                'evidenceType' => 'اسکن QR هر ایستگاه + ثبت عضو یا عکس گروهی',
                'userSteps' => ['فعال سازی پاسپورت خانوادگی', 'ثبت تعداد اعضا', 'رفتن به چند نقطه', 'ثبت عکس یا پاسخ کودک', 'دریافت جایزه خانوادگی'],
                'navigationHint' => 'مسیر باید کوتاه، امن و مناسب کودک باشد و زمان تقریبی هر نقطه مشخص شود.',
                'points' => ['base' => 240, 'bonus' => 'برای تکمیل مسیر خانوادگی با همه اعضا'],
                'rewardModel' => 'گنج خانوادگی + نشان گروهی',
                'rewardIdeas' => ['عکس یادگاری دیجیتال', 'بلیت تخفیفی', 'هدیه کودک', 'دعوت خانوادگی بعدی'],
                'stakeholders' => ['مدیر اقیانوس پارک', 'خانواده ها', 'تیم اجرایی', 'اسپانسر کودک'],
                'riskControl' => 'حریم تصویر کودکان و رضایت والدین باید رعایت شود.',
            ],
            [
                'code' => 'skate-park-skill-leaderboard',
                'title' => 'چالش مهارت و لیدربورد اسکیت پارک',
                'family' => 'اکوپارک عباس آباد',
                'bestFor' => 'نوجوانان، جوانان، رقابت دوستانه، ورزش شهری و اسپانسر سبک زندگی',
                'missionGoal' => 'کاربر یک چالش مهارتی ساده را انجام می دهد، نتیجه ثبت می شود و در جدول امتیاز روزانه قرار می گیرد.',
                'evidenceType' => 'ثبت زمان یا امتیاز + تایید مجری + QR محل',
                'userSteps' => ['اسکن QR محل', 'انتخاب سطح چالش', 'انجام فعالیت کوتاه و امن', 'ثبت نتیجه', 'دیدن جایگاه در جدول روز'],
                'navigationHint' => 'مسیر باید کاربر را به نقطه امن چالش ببرد و قوانین ایمنی را قبل از شروع نشان دهد.',
                'points' => ['base' => 200, 'bonus' => 'برای رکورد برتر روز یا تیم برتر'],
                'rewardModel' => 'لیدربورد + نشان مهارت',
                'rewardIdeas' => ['نشان مهارت روز', 'امتیاز رتبه برتر', 'جایزه اسپانسر ورزشی', 'دعوت به چالش بعدی'],
                'stakeholders' => ['مدیر اسکیت پارک', 'مجری میدان', 'تیم ایمنی', 'ادمین کمپین'],
                'riskControl' => 'فعالیت ها باید کم خطر، قابل نظارت و مناسب سطح کاربران باشند.',
            ],
            [
                'code' => 'tabiat-bridge-urban-photo-clue',
                'title' => 'سرنخ عکس در پل طبیعت',
                'family' => 'اکوپارک عباس آباد',
                'bestFor' => 'کمپین تصویری، شبکه اجتماعی، مسیرهای پیاده روی و نقاط دیدنی',
                'missionGoal' => 'کاربر از یک زاویه مشخص عکس می گیرد، سرنخ را پیدا می کند و QR مقصد بعدی را اسکن می کند.',
                'evidenceType' => 'ثبت تصویر + QR مقصد بعدی',
                'userSteps' => ['دریافت قاب عکس', 'رفتن به نقطه دید', 'ثبت عکس یا اسکن QR', 'دریافت سرنخ بعدی در مسیر'],
                'navigationHint' => 'مسیر باید کاربر را به نقطه امن عکس برداری ببرد و بعد مقصد بعدی روی نقشه باز شود.',
                'points' => ['base' => 160, 'bonus' => 'برای اشتراک گذاری یا تکمیل مسیر تصویری'],
                'rewardModel' => 'گنج عکس + امتیاز مسیر شهری',
                'rewardIdeas' => ['نشان عکس اکوپارک', 'چاپ عکس منتخب', 'هدیه کوچک', 'امتیاز مسیر طبیعت'],
                'stakeholders' => ['مدیر پروژه', 'تیم رسانه', 'مدیر مسیر', 'بازدیدکننده'],
                'riskControl' => 'نباید کاربر را به نقطه شلوغ یا خطرناک هدایت کند.',
            ],
            [
                'code' => 'quiet-hour-demand-boost',
                'title' => 'تقویت مراجعه در ساعت خلوت',
                'family' => 'اکوپارک عباس آباد',
                'bestFor' => 'فروشگاه ها، رستوران ها، کافه ها و هاب هایی که در بعضی ساعت ها خلوت هستند',
                'missionGoal' => 'در زمان های کم تردد، مأموریت کوتاه و پاداش سریع تعریف شود تا مراجعه به آن نقطه بیشتر شود.',
                'evidenceType' => 'زمان حضور + QR فروشگاه یا نقطه + تایید پاداش',
                'userSteps' => ['دریافت پیشنهاد ساعت خلوت', 'رفتن به نقطه هدف', 'اسکن QR در بازه زمانی', 'دریافت پاداش سریع'],
                'navigationHint' => 'نقشه باید مسیر کوتاه ترین راه به نقطه هدف و زمان باقی مانده را نشان دهد.',
                'points' => ['base' => 110, 'bonus' => 'برای مراجعه در ساعت دقیق پیشنهادی'],
                'rewardModel' => 'پاداش لحظه ای',
                'rewardIdeas' => ['تخفیف سریع', 'ارتقای سفارش', 'هدیه کوچک', 'شانس قرعه کشی'],
                'stakeholders' => ['مدیر فروشگاه', 'فودکورت', 'ادمین کمپین', 'مدیر هاب'],
                'riskControl' => 'ظرفیت پذیرش و تعداد پاداش های روزانه محدود شود.',
            ],
            [
                'code' => 'brand-legendary-sponsored-treasure',
                'title' => 'گنج افسانه ای اسپانسر',
                'family' => 'اکوپارک عباس آباد',
                'bestFor' => 'اسپانسر خارجی، برندهای بزرگ، ادمین مرکزی و کمپین های شاخص',
                'missionGoal' => 'یک جایزه ویژه از طرف اسپانسر به مسیر گنج اضافه می شود و فقط با تکمیل چند مأموریت فعال می گردد.',
                'evidenceType' => 'تکمیل چند مأموریت + کد اسپانسر + تایید نهایی',
                'userSteps' => ['انتخاب مسیر اسپانسر', 'تکمیل مأموریت های لازم', 'باز شدن گنج افسانه ای', 'ثبت کد اسپانسر یا شماره تماس', 'اعلام وضعیت دریافت جایزه'],
                'navigationHint' => 'گنج اسپانسر باید نقطه مقصد مشخص داشته باشد یا از طریق صفحه جایزه و ادمین قابل مدیریت باشد.',
                'points' => ['base' => 420, 'bonus' => 'برای تکمیل مسیر بدون توقف زیاد'],
                'rewardModel' => 'اسپانسری + قرعه کشی ویژه',
                'rewardIdeas' => ['جایزه برند اسپانسر', 'کد خرید ویژه', 'دعوت به رویداد برند', 'بسته VIP بازدید'],
                'stakeholders' => ['ادمین مرکزی', 'اسپانسر خارجی', 'مدیر پروژه', 'تیم حقوقی'],
                'riskControl' => 'قوانین اسپانسر، حریم داده و ظرفیت جایزه باید شفاف باشد.',
            ],
            [
                'code' => 'ecopark-online-treasure-map-game',
                'title' => 'بازی آنلاین نقشه گنج اکوپارک',
                'family' => 'اکوپارک عباس آباد',
                'bestFor' => 'پیش جذب مخاطب قبل از حضور، ادامه تجربه بعد از بازدید و معرفی مسیرها در شبکه های اجتماعی',
                'missionGoal' => 'کاربر روی نقشه آنلاین اکوپارک چند نقطه را باز می کند، سرنخ می گیرد و برای حضور واقعی انگیزه پیدا می کند.',
                'evidenceType' => 'پیشرفت مرحله آنلاین + انتخاب مسیر + کد ورود حضوری',
                'userSteps' => ['ورود به بازی آنلاین', 'انتخاب مسیر اکوپارک', 'باز کردن چند نقطه روی نقشه', 'حل سرنخ کوتاه', 'دریافت کد شروع حضوری'],
                'navigationHint' => 'نقشه آنلاین باید شبیه نسخه ساده مسیر واقعی باشد تا کاربر بعداً همان نقاط را در محیط پیدا کند.',
                'points' => ['base' => 180, 'bonus' => 'امتیاز اضافه برای حضور واقعی و اسکن QR پس از بازی'],
                'rewardModel' => 'پیش امتیاز + اتصال به بازدید واقعی',
                'rewardIdeas' => ['کد شروع سریع در محل', 'نشان کاشف آنلاین', 'هدیه شروع حضوری', 'گنج اول برای مراجعه حضوری'],
                'stakeholders' => ['ادمین کمپین', 'کاربر نهایی', 'تیم رسانه', 'اسپانسر', 'مجری میدان'],
                'riskControl' => 'بازی آنلاین نباید جای تجربه حضوری را بگیرد؛ باید کاربر را به حضور واقعی و ادامه مسیر هدایت کند.',
            ],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function scoringMatrix(): array
    {
        return [
            ['level' => 'ساده', 'range' => '۵۰ تا ۱۲۰', 'rule' => 'اسکن، مشاهده، پاسخ کوتاه یا ثبت حضور ساده.'],
            ['level' => 'متوسط', 'range' => '۱۲۰ تا ۲۲۰', 'rule' => 'عکس، پاسخ چندمرحله ای، مراجعه به یک نقطه یا تعامل با شریک.'],
            ['level' => 'چالشی', 'range' => '۲۲۰ تا ۴۰۰', 'rule' => 'مسیر چند نقطه ای، تیم خانوادگی، گنج پنهان یا تکمیل زنجیره.'],
            ['level' => 'ویژه', 'range' => '۴۰۰ به بالا', 'rule' => 'رویداد محدود، اسپانسر بزرگ، قرعه کشی مهم یا مأموریت ترکیبی.'],
        ];
    }

    /** @return array<int, array<string, string>> */
    private function rewardVault(): array
    {
        return [
            ['type' => 'امتیاز', 'use' => 'برای پیشرفت مرحله ای، سطح بندی و رتبه بندی نرم.'],
            ['type' => 'نشان', 'use' => 'برای هویت، خاطره و افتخار بدون هزینه مستقیم.'],
            ['type' => 'کوپن', 'use' => 'برای هدایت کاربر به فروشگاه یا واحد تجاری داخلی.'],
            ['type' => 'گنج', 'use' => 'برای لحظه کشف، هیجان مسیر و روایت کمپین.'],
            ['type' => 'قرعه کشی', 'use' => 'برای اسپانسرهای بزرگ و پاداش های محدود.'],
            ['type' => 'دسترسی ویژه', 'use' => 'برای تجربه VIP، مسیر کوتاه تر یا محتوای مخفی.'],
        ];
    }

    /** @return array<int, array<string, string>> */
    private function globalPatterns(): array
    {
        return [
            ['name' => 'Local Guides', 'pattern' => 'امتیاز، سطح و نشان برای مشارکت مداوم کاربر در مکان ها.'],
            ['name' => 'Foursquare / Swarm', 'pattern' => 'check-in، نشان و رقابت سبک برای حضور مکانی.'],
            ['name' => 'Geocaching', 'pattern' => 'کشف گنج، سرنخ، ثبت پیدا شدن و روایت مسیر.'],
            ['name' => 'Pokémon GO style routes', 'pattern' => 'مسیر، نقطه مکانی، مأموریت مرحله ای و پاداش فوری.'],
            ['name' => 'UX gamification guidance', 'pattern' => 'پاداش باید به رفتار واقعی وصل باشد و جای تجربه اصلی را نگیرد.'],
        ];
    }

    /** @return list<array<string, mixed>> */
    private function arrayList(mixed $value): array
    {
        return is_array($value) ? array_values(array_filter($value, is_array(...))) : [];
    }

    /** @return array<array-key, mixed> */
    private function associativeArray(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    /** @return list<string> */
    private function stringList(mixed $value): array
    {
        return is_array($value) ? array_values(array_filter($value, is_string(...))) : [];
    }
}
