<?php

namespace App\Services;

use App\Enums\RecordStatus;
use App\Models\AdRequest;
use App\Models\Campaign;
use App\Models\CampaignParticipant;
use App\Models\DisplayDevice;
use App\Models\MissionInstance;
use App\Models\QrCode;
use App\Models\RewardDefinition;
use App\Models\Treasure;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CampaignBuilderService
{
    public function __construct(private readonly UserAccessScopeService $accessScopes) {}

    /** @return array<string, mixed> */
    public function overview(?User $user = null, ?string $campaignCode = null): array
    {
        $campaigns = $this->campaigns($user);
        $selectedCampaign = $this->selectedCampaign($campaigns, $campaignCode);
        $counts = $selectedCampaign ? $this->counts($selectedCampaign) : $this->emptyCounts();

        return [
            'campaigns' => $campaigns->map(fn (Campaign $campaign): array => $this->serializeCampaign($campaign))->values(),
            'selectedCampaign' => $selectedCampaign ? $this->serializeCampaign($selectedCampaign) : null,
            'counts' => $counts,
            'readiness' => $this->launchReadiness($selectedCampaign, $counts),
            'steps' => $this->steps($selectedCampaign, $counts),
            'roleTracks' => $this->roleTracks($selectedCampaign, $counts),
        ];
    }

    public function activate(?User $user, string $campaignCode): Campaign
    {
        $campaign = $this->selectedCampaign($this->campaigns($user), $campaignCode);

        if (! $campaign) {
            throw ValidationException::withMessages(['campaign' => 'کمپین انتخاب‌شده در دسترس نیست.']);
        }

        $readiness = $this->launchReadiness($campaign, $this->counts($campaign));

        if (! $readiness['canActivate']) {
            throw ValidationException::withMessages(['campaign' => 'کمپین هنوز برای اجرا کامل نیست. موارد باقی‌مانده را در چک نهایی ببینید.']);
        }

        $metadata = is_array($campaign->metadata) ? $campaign->metadata : [];
        $campaign->update([
            'status' => 'active',
            'metadata' => [
                ...$metadata,
                'activated_from_builder_at' => now()->toIso8601String(),
                'activated_by_user_id' => $user?->id,
            ],
        ]);

        return $campaign;
    }

    /** @return Collection<int, Campaign> */
    private function campaigns(?User $user): Collection
    {
        $venueIds = $user ? $this->accessScopes->venueIds($user) : collect();
        $isGlobal = $user === null || $this->accessScopes->hasGlobalAccess($user);

        return Campaign::query()
            ->when(! $isGlobal, fn (Builder $query) => $query->whereIn('venue_id', $venueIds))
            ->with('venue:id,code,name')
            ->orderByDesc('created_at')
            ->get();
    }

    /** @param Collection<int, Campaign> $campaigns */
    private function selectedCampaign(Collection $campaigns, ?string $campaignCode): ?Campaign
    {
        if ($campaignCode) {
            return $campaigns->first(fn (Campaign $campaign): bool => $campaign->code === Str::lower($campaignCode));
        }

        return $campaigns->first();
    }

    /** @return array<string, int> */
    private function counts(Campaign $campaign): array
    {
        return [
            'qrCodes' => QrCode::query()->where('campaign_id', $campaign->id)->count(),
            'missions' => MissionInstance::query()->where('campaign_id', $campaign->id)->count(),
            'rewards' => RewardDefinition::query()->where('campaign_id', $campaign->id)->count(),
            'approvedRewards' => RewardDefinition::query()
                ->where('campaign_id', $campaign->id)
                ->where('status', RecordStatus::Active)
                ->where(function (Builder $query): void {
                    $query->where('metadata->approval_status', 'approved')
                        ->orWhereNull('metadata->approval_status');
                })
                ->count(),
            'pendingRewards' => RewardDefinition::query()
                ->where('campaign_id', $campaign->id)
                ->where('metadata->approval_status', 'pending_review')
                ->count(),
            'partnerRewardOffers' => RewardDefinition::query()
                ->where('campaign_id', $campaign->id)
                ->where('metadata->source', 'partner_offer_submission')
                ->count(),
            'treasures' => Treasure::query()->where('campaign_id', $campaign->id)->count(),
            'participants' => CampaignParticipant::query()->where('campaign_id', $campaign->id)->count(),
            'readyParticipants' => CampaignParticipant::query()->where('campaign_id', $campaign->id)->where('onboarding_status', 'ready')->count(),
            'ads' => AdRequest::query()->where('venue_id', $campaign->venue_id)->count(),
            'displayDevices' => DisplayDevice::query()->where('venue_id', $campaign->venue_id)->count(),
        ];
    }

    /** @return array<string, int> */
    private function emptyCounts(): array
    {
        return [
            'qrCodes' => 0,
            'missions' => 0,
            'rewards' => 0,
            'approvedRewards' => 0,
            'pendingRewards' => 0,
            'partnerRewardOffers' => 0,
            'treasures' => 0,
            'participants' => 0,
            'readyParticipants' => 0,
            'ads' => 0,
            'displayDevices' => 0,
        ];
    }

    /** @return array<string, mixed> */
    private function serializeCampaign(Campaign $campaign): array
    {
        return [
            'id' => $campaign->id,
            'code' => $campaign->code,
            'name' => $campaign->name,
            'campaignType' => $campaign->campaign_type,
            'blueprintCode' => $campaign->metadata['blueprint_code'] ?? null,
            'routeReviewedAt' => $campaign->metadata['route_reviewed_at'] ?? null,
            'status' => $campaign->status->value,
            'startAt' => $campaign->start_at?->toIso8601String(),
            'endAt' => $campaign->end_at?->toIso8601String(),
            'venue' => $campaign->venue ? [
                'id' => $campaign->venue->id,
                'code' => $campaign->venue->code,
                'name' => $campaign->venue->name,
            ] : null,
        ];
    }

    /**
     * @param  array<string, int>  $counts
     * @return array<int, array<string, mixed>>
     */
    private function steps(?Campaign $campaign, array $counts): array
    {
        $campaignCode = $campaign?->code;
        $blueprintCode = $campaign?->metadata['blueprint_code'] ?? null;

        return [
            $this->step('setup', 'اطلاعات پایه کمپین', 'ادمین / اپراتور', $campaign !== null, 'نام، مکان، بازه زمانی، وضعیت و الگوی مرجع کمپین را کنترل کنید.', '/admin/campaigns'.($campaignCode ? '?campaign='.$campaignCode : '')),
            $this->step('qr', 'نقاط ورود و QR', 'ادمین / اپراتور / مدیر مکان', $counts['qrCodes'] > 0, 'حداقل یک QR معتبر برای شروع مسیر کاربر تعریف شود.', '/admin/qr-codes'.($campaignCode ? '?campaign='.$campaignCode : '')),
            $this->step('components', 'مأموریت، امتیاز، پاداش و گنج', 'ادمین / اپراتور / فروشگاه', $counts['missions'] > 0 && ($counts['approvedRewards'] > 0 || $counts['treasures'] > 0), 'مأموریت‌ها، مشوق‌ها، هزینه امتیازی و شرایط تحویل تکمیل شوند و پاداش‌های پیشنهادی بازبینی شوند.', $this->contextUrl('/admin/missions', $campaignCode, $blueprintCode, 'components')),
            $this->step('partners', 'اعضا، فروشگاه‌ها و اسپانسرها', 'فروشگاه/واحد تجاری / اسپانسر / ادمین', $counts['readyParticipants'] > 0 && $counts['partnerRewardOffers'] > 0, 'مالک پاداش، نقش فروشگاه‌ها، اسپانسرها، وضعیت آماده‌سازی و پیشنهاد پاداش مشخص شود.', $this->contextUrl('/admin/campaign-participants', $campaignCode, $blueprintCode, 'participants')),
            $this->step('route', 'مسیر عملیاتی کمپین', 'ادمین / مدیر مکان / مدیر هاب', $counts['qrCodes'] > 0 && $counts['missions'] > 0 && $counts['readyParticipants'] > 0 && (bool) ($campaign?->metadata['route_reviewed_at'] ?? false), 'ارتباط QR، مأموریت، مکان، فروشگاه، نمایشگر و تبلیغات در یک مسیر قابل اجرا بررسی و تایید شود.', $this->contextUrl('/admin/campaign-operations', $campaignCode, $blueprintCode, 'route')),
            $this->step('review', 'بررسی نهایی و آماده اجرا', 'ادمین', $campaign?->status->value === 'active' && $this->launchReadiness($campaign, $counts)['canActivate'], 'قبل از فعال‌سازی عمومی، نقص‌ها و مسئولیت‌های باقی‌مانده را مرور کنید و کمپین را فعال کنید.', $this->contextUrl('/admin/campaign-builder', $campaignCode, $blueprintCode, 'review')),
        ];
    }

    /** @return array<string, mixed> */
    private function step(string $key, string $title, string $owner, bool $complete, string $description, string $href): array
    {
        return [
            'key' => $key,
            'title' => $title,
            'owner' => $owner,
            'complete' => $complete,
            'status' => $complete ? 'complete' : 'needs_action',
            'description' => $description,
            'href' => $href,
        ];
    }

    /**
     * @param  array<string, int>  $counts
     * @return array<int, array<string, mixed>>
     */
    private function roleTracks(?Campaign $campaign, array $counts): array
    {
        $campaignCode = $campaign?->code;

        return [
            [
                'role' => 'ادمین و اپراتور',
                'responsibility' => 'ساخت کمپین، کنترل نهایی، اتصال QR، تعریف مأموریت و فعال‌سازی',
                'status' => $campaign ? 'در جریان' : 'نیازمند ساخت کمپین',
                'href' => '/admin/campaigns'.($campaignCode ? '?campaign='.$campaignCode : ''),
            ],
            [
                'role' => 'فروشگاه/واحد تجاری و حامی پاداش',
                'responsibility' => 'تکمیل پیشنهاد، موجودی، شرایط تحویل و آمادگی پذیرش کاربر',
                'status' => $counts['readyParticipants'] > 0 ? 'دارای عضو آماده' : 'نیازمند دعوت/آماده‌سازی',
                'href' => $campaignCode ? '/partner/dashboard?campaign='.$campaignCode : '/partner/dashboard',
            ],
            [
                'role' => 'مدیر مکان و هاب',
                'responsibility' => 'کنترل نقطه نصب QR، مسیر محیطی، نمایشگرها و اجرای میدانی',
                'status' => $counts['qrCodes'] > 0 ? 'دارای نقطه ورود' : 'نیازمند نقطه ورود',
                'href' => $campaignCode ? '/hub/dashboard?campaign='.$campaignCode : '/hub/dashboard',
            ],
            [
                'role' => 'بازبین کمپین',
                'responsibility' => 'بررسی اینکه هیچ مرحله‌ای بدون مالک، پاداش یا مسیر اجرایی نمانده باشد',
                'status' => $campaign?->status->value === 'active' ? 'آماده اجرا' : 'پیش از فعال‌سازی',
                'href' => $campaignCode ? '/admin/campaign-builder?campaign='.$campaignCode : '/admin/campaign-builder',
            ],
        ];
    }

    private function contextUrl(string $path, ?string $campaignCode, ?string $blueprintCode, string $action): string
    {
        $params = array_filter([
            'campaign' => $campaignCode,
            'blueprint' => $blueprintCode,
            'blueprint_action' => $action,
        ]);

        return $path.($params === [] ? '' : '?'.http_build_query($params));
    }

    /**
     * @param  array<string, int>  $counts
     * @return array<string, mixed>
     */
    private function launchReadiness(?Campaign $campaign, array $counts): array
    {
        $campaignCode = $campaign?->code;
        $blueprintCode = $campaign?->metadata['blueprint_code'] ?? null;
        $checks = [
            $this->launchReadinessCheck(
                'campaign',
                'اطلاعات پایه کمپین ثبت شده باشد.',
                'نام، مکان، بازه زمانی و الگوی مرجع باید مشخص باشد تا بقیه گام‌ها به کمپین درست وصل شوند.',
                $campaign !== null,
                'blocker',
                '/admin/campaigns'.($campaignCode ? '?campaign='.$campaignCode : ''),
                'تکمیل اطلاعات پایه',
            ),
            $this->launchReadinessCheck(
                'qr',
                'حداقل یک QR ورودی به کمپین وصل باشد.',
                'بدون QR معتبر، کاربر نقطه شروع قابل ردیابی برای ورود به تجربه کمپین ندارد.',
                $counts['qrCodes'] > 0,
                'blocker',
                $this->contextUrl('/admin/qr-codes', $campaignCode, $blueprintCode, 'qr'),
                'ثبت یا اتصال QR',
            ),
            $this->launchReadinessCheck(
                'missions',
                'حداقل یک ماموریت برای کمپین ثبت شده باشد.',
                'ماموریت‌ها موتور حرکت کاربر هستند و باید با چرخه کاربر همین کمپین همخوان باشند.',
                $counts['missions'] > 0,
                'blocker',
                $this->contextUrl('/admin/missions', $campaignCode, $blueprintCode, 'components'),
                'تعریف ماموریت',
            ),
            $this->launchReadinessCheck(
                'incentives',
                'حداقل یک پاداش تاییدشده یا گنج برای کمپین ثبت شده باشد.',
                'پاداش خروجی قطعی است و گنج کشف/انگیزه مسیر را تقویت می‌کند؛ یکی از این دو برای اجرا لازم است.',
                $counts['approvedRewards'] > 0 || $counts['treasures'] > 0,
                'blocker',
                $this->contextUrl('/admin/missions', $campaignCode, $blueprintCode, 'components'),
                'تکمیل پاداش یا گنج',
            ),
            $this->launchReadinessCheck(
                'reward_review',
                'پیشنهاد پاداش در انتظار بررسی باقی نمانده باشد.',
                'پیشنهادهای فروشگاه یا اسپانسر باید پیش از اجرا تایید، رد یا برای اصلاح برگشت داده شوند.',
                $counts['pendingRewards'] === 0,
                'blocker',
                $this->contextUrl('/admin/missions', $campaignCode, $blueprintCode, 'reward_review'),
                'بررسی پیشنهادها',
            ),
            $this->launchReadinessCheck(
                'participants',
                'حداقل یک عضو، فروشگاه یا واحد تجاری آماده ثبت شده باشد.',
                'برای تحویل پاداش، اجرای میدانی یا پشتیبانی مسیر باید حداقل یک مشارکت‌کننده آماده باشد.',
                $counts['readyParticipants'] > 0,
                'blocker',
                $this->contextUrl('/admin/campaign-participants', $campaignCode, $blueprintCode, 'participants'),
                'آماده‌سازی مشارکت‌کننده',
            ),
            $this->launchReadinessCheck(
                'route',
                'مسیر عملیاتی کمپین در نقشه عملیات تایید شده باشد.',
                'اتصال QR، ماموریت، مکان، فروشگاه و تحویل پاداش باید در نقشه عملیات بازبینی شود.',
                (bool) ($campaign?->metadata['route_reviewed_at'] ?? false),
                'blocker',
                $this->contextUrl('/admin/campaign-operations', $campaignCode, $blueprintCode, 'route'),
                'بازبینی نقشه عملیات',
            ),
            $this->launchReadinessCheck(
                'media_display',
                'تبلیغات یا نمایشگرهای محیطی برای کمپین آماده باشد.',
                'این مورد مانع فعال‌سازی نیست، اما برای اجرای قوی‌تر بهتر است قبل از شروع تکمیل شود.',
                $counts['ads'] > 0 || $counts['displayDevices'] > 0,
                'warning',
                $this->contextUrl('/admin/campaign-operations', $campaignCode, $blueprintCode, 'route'),
                'بررسی تبلیغات و نمایشگر',
            ),
        ];
        $blockersCount = collect($checks)
            ->filter(fn (array $check): bool => ! $check['complete'] && $check['severity'] === 'blocker')
            ->count();
        $warningsCount = collect($checks)
            ->filter(fn (array $check): bool => ! $check['complete'] && $check['severity'] === 'warning')
            ->count();
        $status = $blockersCount > 0 ? 'needs_action' : ($warningsCount > 0 ? 'ready_with_warnings' : 'ready');

        return [
            'checks' => $checks,
            'canActivate' => $blockersCount === 0,
            'routeReviewedAt' => $campaign?->metadata['route_reviewed_at'] ?? null,
            'status' => $status,
            'blockersCount' => $blockersCount,
            'warningsCount' => $warningsCount,
            'summary' => match ($status) {
                'ready' => 'همه موارد ضروری و تکمیلی آماده است.',
                'ready_with_warnings' => 'موارد ضروری کامل است؛ فقط چند هشدار تکمیلی باقی مانده.',
                default => 'برای فعال‌سازی، ابتدا موارد ضروری باقی‌مانده را تکمیل کنید.',
            },
        ];
    }

    /** @return array<string, mixed> */
    private function launchReadinessCheck(
        string $key,
        string $label,
        string $detail,
        bool $complete,
        string $severity,
        string $actionHref,
        string $actionLabel,
    ): array {
        return [
            'key' => $key,
            'label' => $label,
            'detail' => $detail,
            'complete' => $complete,
            'severity' => $severity,
            'actionHref' => $actionHref,
            'actionLabel' => $actionLabel,
        ];
    }
}
