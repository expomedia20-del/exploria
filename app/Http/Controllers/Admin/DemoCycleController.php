<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\DisplayDevice;
use App\Models\RewardRedemption;
use App\Models\User;
use App\Models\UserMissionProgress;
use App\Models\UserReward;
use App\Models\Visit;
use App\Services\EcoParkDemoReadinessService;
use App\Services\VenueRegistryService;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class DemoCycleController extends Controller
{
    public function page(EcoParkDemoReadinessService $readiness, VenueRegistryService $venues): Response
    {
        $readinessReport = $readiness->report();
        $demoStressPlan = $venues->list()
            ->firstWhere('code', 'ecopark-abbasabad')['demoStressPlan']
            ?? null;

        return Inertia::render('admin/demo-cycle/index', [
            'summary' => [
                'title' => 'چرخه کامل دمو اکوپارک',
                'campaign' => 'پایلوت بازدید اکوپارک ۱۴۰۵',
                'venue' => 'اکوپارک عباس آباد',
                'status' => $readinessReport['summary']['ready'] ? 'آماده اجرای مرحله ای' : 'نیازمند رفع نقص',
                'stagesCount' => 5,
            ],
            'stages' => $this->stages(),
            'stageHealth' => $this->stageHealth($readinessReport),
            'demoStressPlan' => $demoStressPlan,
            'commercialPackages' => $this->commercialPackages(),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function stages(): array
    {
        return [
            [
                'title' => 'سناریو و معیار قبولی',
                'goal' => 'یک روایت روشن برای دمو، مسیر بازدید، نقش ها و خروجی قابل قبول داشته باشیم.',
                'owner' => 'مدیر پروژه مکانی اکسپلوریا',
                'status' => 'شروع',
                'links' => [
                    ['label' => 'ارزیابی مکان', 'href' => '/admin/venues'],
                    ['label' => 'ساختار نقش ها', 'href' => '/admin/role-operations'],
                ],
                'checks' => [
                    'مکان، زون، هاب، فروشگاه و اسپانسر جدا تعریف شده باشند.',
                    'مدیر مکان فقط نمای مدیریتی کل ببیند و وارد جزئیات فروشگاه نشود.',
                    'مدیر رواق فقط محدوده رواق و هماهنگی واحدهای همان مجموعه را ببیند.',
                ],
            ],
            [
                'title' => 'آمادگی داده و دسترسی ها',
                'goal' => 'کمپین، QR، ماموریت، پاداش، گنج، شرکا و دسترسی ها برای اجرای واقعی آماده باشند.',
                'owner' => 'ادمین مرکزی و مدیر پروژه',
                'status' => 'ضروری',
                'links' => [
                    ['label' => 'ثبت کمپین', 'href' => '/admin/campaigns'],
                    ['label' => 'تخصیص دسترسی', 'href' => '/admin/access-scopes'],
                    ['label' => 'مدیریت شرکا', 'href' => '/admin/partners'],
                ],
                'checks' => [
                    'کمپین فعال و دارای QR معتبر باشد.',
                    'حداقل یک فروشگاه، یک پاداش و یک اسپانسر قابل نمایش وجود داشته باشد.',
                    'اکانت های مدیریتی دستی و کاربران عمومی خودکار ساخته شوند.',
                ],
            ],
            [
                'title' => 'اجرای مسیر کاربر',
                'goal' => 'کاربر از ورود با موبایل تا انتخاب کمپین، شروع مشارکت، انجام ماموریت و مشاهده کیف پاداش جلو برود.',
                'owner' => 'مجری میدانی و یاریگر کاربران',
                'status' => 'دموپذیر',
                'links' => [
                    ['label' => 'پنل مشارکت کننده', 'href' => '/participant/dashboard'],
                    ['label' => 'مدیریت QR', 'href' => '/admin/qr-codes'],
                    ['label' => 'ماموریت و پاداش', 'href' => '/admin/missions'],
                ],
                'checks' => [
                    'کاربر عادی بدون تایید ادمین بتواند مشارکت را شروع کند.',
                    'مسیر فردی، خانوادگی و تیمی در پنل کاربر قابل فهم باشد.',
                    'سوابق مراجعه، امتیاز، پاداش و ادامه مسیر کاربر دیده شود.',
                ],
            ],
            [
                'title' => 'مصرف پاداش، تبلیغ و نمایشگر',
                'goal' => 'پاداش صادرشده در فروشگاه/واحد قابل مصرف باشد و تبلیغ یا نمایشگر مرتبط با کمپین کنترل شود.',
                'owner' => 'مدیر تبلیغات، مدیر فروشگاه و مدیر رواق',
                'status' => 'نیازمند کنترل',
                'links' => [
                    ['label' => 'پنل فروشگاه', 'href' => '/partner/dashboard'],
                    ['label' => 'تبلیغات مستقل', 'href' => '/admin/ads'],
                    ['label' => 'عملیات نمایشگرها', 'href' => '/admin/display-operations'],
                ],
                'checks' => [
                    'فروشگاه فقط عملکرد، پیشنهاد، کد مصرف و گزارش خودش را ببیند.',
                    'مدیر رواق هماهنگی و آمادگی واحدها را ببیند، نه دخل و خرج هر واحد.',
                    'تبلیغ تاییدشده روی نمایشگر زمان بندی یا وضعیت روشن داشته باشد.',
                ],
            ],
            [
                'title' => 'گزارش نهایی و بسته فروش',
                'goal' => 'خروجی دمو به زبان مدیر مکان، اسپانسر و فروشگاه قابل ارائه و قیمت گذاری باشد.',
                'owner' => 'ادمین مرکزی و تیم فروش اکسپلوریا',
                'status' => 'مرحله بعد',
                'links' => [
                    ['label' => 'داشبورد ادمین', 'href' => '/dashboard'],
                    ['label' => 'پشتیبانی و چت بات', 'href' => '/admin/support'],
                ],
                'checks' => [
                    'تعداد ورود، ماموریت، پاداش، مصرف پاداش و تعامل اسپانسر گزارش شود.',
                    'سه بسته فروش پایلوت مکان، اسپانسر کمپین و واحد عضو آماده باشد.',
                    'خروجی دمو برای مذاکره تجاری کوتاه، عدددار و قابل اعتماد باشد.',
                ],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $readinessReport
     * @return array<int, array<string, mixed>>
     */
    private function stageHealth(array $readinessReport): array
    {
        $checks = collect($readinessReport['checks'] ?? [])->keyBy('key');

        return [
            [
                'stage' => 2,
                'title' => 'آمادگی داده و دسترسی ها',
                'status' => $this->statusFromChecks($checks, [
                    'active_campaign',
                    'active_qr',
                    'mission_chain',
                    'treasure_connected',
                    'partner_accounts',
                    'partner_locations',
                    'campaign_participants',
                    'reward_layers',
                    'role_scopes',
                    'ravaq_hub',
                ]),
                'metrics' => $this->metricsFromChecks($checks, [
                    'active_campaign' => 'کمپین فعال',
                    'active_qr' => 'QR فعال',
                    'mission_chain' => 'ماموریت',
                    'partner_accounts' => 'شریک/واحد',
                    'reward_layers' => 'پاداش',
                    'role_scopes' => 'دسترسی اجرایی',
                ]),
                'nextActions' => $this->nextActionsFromChecks($checks, [
                    'active_campaign',
                    'active_qr',
                    'mission_chain',
                    'treasure_connected',
                    'partner_accounts',
                    'partner_locations',
                    'campaign_participants',
                    'reward_layers',
                    'role_scopes',
                    'ravaq_hub',
                ]),
                'links' => [
                    ['label' => 'آمادگی دمو', 'href' => '/admin/demo-cycle'],
                    ['label' => 'تخصیص دسترسی', 'href' => '/admin/access-scopes'],
                ],
            ],
            [
                'stage' => 3,
                'title' => 'اجرای مسیر کاربر',
                'status' => $this->statusFromBooleans([
                    Visit::query()->exists(),
                    UserMissionProgress::query()->where('status', 'completed')->exists(),
                    UserReward::query()->exists(),
                ]),
                'metrics' => [
                    $this->metric('بازدید ثبت شده', Visit::query()->count()),
                    $this->metric('ماموریت تکمیل شده', UserMissionProgress::query()->where('status', 'completed')->count()),
                    $this->metric('پاداش صادر شده', UserReward::query()->count()),
                    $this->metric('مشارکت کننده', $this->participantCount()),
                ],
                'nextActions' => $this->nextActionsFromBooleans([
                    [Visit::query()->exists(), 'یک مسیر بازدید واقعی یا دموی فشار را از QR شروع کنید.'],
                    [UserMissionProgress::query()->where('status', 'completed')->exists(), 'حداقل یک ماموریت را در مسیر کاربر تکمیل کنید.'],
                    [UserReward::query()->exists(), 'یک پاداش قابل نمایش در کیف پاداش کاربر صادر کنید.'],
                ]),
                'links' => [
                    ['label' => 'پنل مشارکت کننده', 'href' => '/participant/dashboard'],
                    ['label' => 'صفحه QR', 'href' => '/admin/qr-codes'],
                ],
            ],
            [
                'stage' => 4,
                'title' => 'مصرف پاداش، تبلیغ و نمایشگر',
                'status' => $this->statusFromChecks($checks, [
                    'partner_rewards',
                    'sponsor_rewards',
                    'inventory_allocations',
                    'display_operations',
                ]),
                'metrics' => [
                    $this->metric('پاداش منتظر مصرف', RewardRedemption::query()->where('status', 'pending')->count()),
                    $this->metric('پاداش مصرف شده', RewardRedemption::query()->whereIn('status', ['confirmed', 'redeemed'])->count()),
                    $this->metric('نمایشگر فعال', DisplayDevice::query()->where('status', 'active')->count()),
                ],
                'nextActions' => $this->nextActionsFromChecks($checks, [
                    'partner_rewards',
                    'sponsor_rewards',
                    'inventory_allocations',
                    'display_operations',
                ]),
                'links' => [
                    ['label' => 'پنل فروشگاه', 'href' => '/partner/dashboard'],
                    ['label' => 'تبلیغات', 'href' => '/admin/ads'],
                    ['label' => 'نمایشگرها', 'href' => '/admin/display-operations'],
                ],
            ],
            [
                'stage' => 5,
                'title' => 'گزارش نهایی و بسته فروش',
                'status' => $this->statusFromBooleans([
                    Campaign::query()->where('status', 'active')->exists(),
                    Visit::query()->exists(),
                    UserReward::query()->exists(),
                    RewardRedemption::query()->exists(),
                ]),
                'metrics' => [
                    $this->metric('کمپین فعال', Campaign::query()->where('status', 'active')->count()),
                    $this->metric('بازدید', Visit::query()->count()),
                    $this->metric('پاداش صادر شده', UserReward::query()->count()),
                    $this->metric('مصرف/تایید پاداش', RewardRedemption::query()->whereIn('status', ['confirmed', 'redeemed'])->count()),
                ],
                'nextActions' => $this->nextActionsFromBooleans([
                    [Visit::query()->exists(), 'برای گزارش فروش، حداقل یک بازدید واقعی یا دموی تکمیل شده لازم است.'],
                    [RewardRedemption::query()->whereIn('status', ['confirmed', 'redeemed'])->exists(), 'برای گزارش ROI، حداقل یک مصرف پاداش توسط فروشگاه ثبت کنید.'],
                    [DisplayDevice::query()->where('status', 'active')->exists(), 'وضعیت رسانه و نمایشگر را در خروجی تجاری روشن کنید.'],
                ]),
                'links' => [
                    ['label' => 'داشبورد ادمین', 'href' => '/dashboard'],
                    ['label' => 'پشتیبانی', 'href' => '/admin/support'],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function commercialPackages(): array
    {
        return [
            [
                'title' => 'پکیج پایلوت مکان',
                'buyer' => 'مدیر اجرایی مکان',
                'deliverable' => 'راه اندازی یک کمپین کامل، QR، ماموریت، پاداش، گزارش روز اجرا و گزارش نهایی',
            ],
            [
                'title' => 'پکیج اسپانسر کمپین',
                'buyer' => 'اسپانسر داخلی یا خارجی',
                'deliverable' => 'اتصال برند به ماموریت، گنج، جایزه، تبلیغ و گزارش تعامل',
            ],
            [
                'title' => 'پکیج واحد عضو',
                'buyer' => 'فروشگاه، فودکورت، رستوران یا واحد فرهنگی',
                'deliverable' => 'پنل واحد، پیشنهاد/پاداش، مصرف کد، گزارش مراجعه و مشوق خرید بعدی',
            ],
        ];
    }

    /** @param Collection<string, array<string, mixed>> $checks */
    private function statusFromChecks(Collection $checks, array $keys): string
    {
        $selected = $checks->only($keys);

        if ($selected->where('status', 'fail')->isNotEmpty()) {
            return 'needs_work';
        }

        if ($selected->where('status', 'warning')->isNotEmpty()) {
            return 'warning';
        }

        return 'ready';
    }

    private function statusFromBooleans(array $conditions): string
    {
        $passed = collect($conditions)->filter()->count();

        if ($passed === count($conditions)) {
            return 'ready';
        }

        return $passed > 0 ? 'warning' : 'needs_work';
    }

    /** @param Collection<string, array<string, mixed>> $checks */
    private function metricsFromChecks(Collection $checks, array $labels): array
    {
        return collect($labels)
            ->map(fn (string $label, string $key): array => $this->metric($label, (int) ($checks[$key]['count'] ?? 0)))
            ->values()
            ->all();
    }

    private function metric(string $label, int $value): array
    {
        return ['label' => $label, 'value' => $value];
    }

    /** @param Collection<string, array<string, mixed>> $checks */
    private function nextActionsFromChecks(Collection $checks, array $keys): array
    {
        return $checks
            ->only($keys)
            ->filter(fn (array $check): bool => ($check['status'] ?? 'pass') !== 'pass')
            ->pluck('nextAction')
            ->filter()
            ->values()
            ->all();
    }

    private function nextActionsFromBooleans(array $items): array
    {
        return collect($items)
            ->filter(fn (array $item): bool => ! $item[0])
            ->pluck(1)
            ->values()
            ->all();
    }

    private function participantCount(): int
    {
        return User::query()
            ->where('role', UserRole::Visitor)
            ->where(fn ($query) => $query
                ->where('public_participation_status', 'participant')
                ->orWhereHas('visits'))
            ->count();
    }
}
