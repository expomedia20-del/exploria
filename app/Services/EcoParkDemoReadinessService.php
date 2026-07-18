<?php

namespace App\Services;

use App\Enums\RecordStatus;
use App\Models\Campaign;
use App\Models\CampaignParticipant;
use App\Models\DisplayDevice;
use App\Models\Hub;
use App\Models\MissionInstance;
use App\Models\PartnerAccount;
use App\Models\PartnerLocation;
use App\Models\QrCode;
use App\Models\RewardDefinition;
use App\Models\RewardInventoryAllocation;
use App\Models\Treasure;
use App\Models\UserAccessScope;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

/**
 * @phpstan-type ReadinessCheck array{key: string, label: string, status: string, count: int, message: string, nextAction: string|null, minimum?: int}
 * @phpstan-type ReadinessReport array{
 *     summary: array{venueCode: string, campaigns: list<array{code: string, name: string}>, ready: bool, passCount: int, warningCount: int, failCount: int},
 *     checks: list<ReadinessCheck>,
 *     nextActions: list<string>
 * }
 */
class EcoParkDemoReadinessService
{
    /** @return ReadinessReport */
    public function report(string $venueCode = 'ecopark-abbasabad'): array
    {
        $venue = Venue::query()->where('code', $venueCode)->first();
        $venueId = $venue?->id;

        $campaigns = $venueId
            ? Campaign::query()
                ->where('venue_id', $venueId)
                ->where('status', RecordStatus::Active)
                ->get(['id', 'code', 'name'])
            : collect();

        $campaignIds = $campaigns->pluck('id');
        $partnerIds = $venueId
            ? PartnerAccount::query()->where('venue_id', $venueId)->pluck('id')
            : collect();

        $checks = collect([
            $this->check(
                'venue_active',
                'مکان پایلوت فعال',
                $venue !== null && $venue->status === RecordStatus::Active && $venue->profile_status === RecordStatus::Active,
                $venue ? 1 : 0,
                'اکوپارک عباس‌آباد برای شروع دمو آماده است.',
                'ابتدا مکان اکوپارک را در ارزیابی مکان فعال و پروفایل آن را تکمیل کنید.',
            ),
            $this->minimumCountCheck(
                'active_campaign',
                'کمپین فعال برای مکان',
                $campaigns->count(),
                1,
                'حداقل یک کمپین فعال برای دمو وجود دارد.',
                'یک کمپین فعال برای اکوپارک ثبت یا کمپین موجود را فعال کنید.',
            ),
            $this->minimumCountCheck(
                'active_qr',
                'QR فعال متصل به کمپین',
                $this->countForVenueCampaigns(QrCode::query(), $venueId, $campaignIds),
                1,
                'QR ورودی یا عملیاتی برای دمو آماده است.',
                'برای کمپین فعال، حداقل یک QR فعال بسازید.',
            ),
            $this->minimumCountCheck(
                'mission_chain',
                'زنجیره ماموریت‌ها',
                $this->countForVenueCampaigns(MissionInstance::query(), $venueId, $campaignIds),
                4,
                'زنجیره ماموریت چندمرحله‌ای برای دمو موجود است.',
                'برای کمپین فعال حداقل چهار ماموریت عملیاتی ثبت کنید.',
            ),
            $this->minimumCountCheck(
                'treasure_connected',
                'گنج متصل به مسیر',
                $this->countForVenueCampaigns(Treasure::query(), $venueId, $campaignIds),
                1,
                'حداقل یک گنج به کمپین/ماموریت وصل است.',
                'یک گنج برای مرحله پایانی یا خانوادگی مسیر تعریف کنید.',
            ),
            $this->minimumCountCheck(
                'partner_accounts',
                'واحدها و شرکای فعال',
                $venueId ? PartnerAccount::query()->where('venue_id', $venueId)->where('status', RecordStatus::Active)->count() : 0,
                3,
                'واحدهای تجاری و اسپانسر پایه برای دمو وجود دارند.',
                'حداقل سه حساب شریک/فروشگاه/اسپانسر فعال ثبت کنید.',
            ),
            $this->minimumCountCheck(
                'partner_locations',
                'استقرار واحدها در هاب‌ها',
                $venueId ? PartnerLocation::query()->where('venue_id', $venueId)->where('status', RecordStatus::Active)->count() : 0,
                3,
                'واحدها به هاب‌های عملیاتی متصل شده‌اند.',
                'هر واحد منتخب را به هاب یا محدوده اجرایی مربوط وصل کنید.',
            ),
            $this->minimumCountCheck(
                'campaign_participants',
                'عضویت واحدها در کمپین',
                $this->countForVenueCampaigns(CampaignParticipant::query(), $venueId, $campaignIds),
                3,
                'واحدهای کلیدی عضو کمپین هستند.',
                'واحدهای فعال را به کمپین هدف متصل کنید.',
            ),
            $this->minimumCountCheck(
                'reward_layers',
                'پاداش‌های چندلایه',
                $this->countForVenueCampaigns(RewardDefinition::query(), $venueId, $campaignIds),
                3,
                'پاداش‌های پایه، شریک و اسپانسر در دمو وجود دارند.',
                'برای ماموریت‌ها پاداش داخلی، شریک و اسپانسری تعریف کنید.',
            ),
            $this->minimumCountCheck(
                'partner_rewards',
                'پاداش متصل به واحد تجاری',
                $this->rewardCountForPartners($venueId, $campaignIds, $partnerIds, false),
                1,
                'حداقل یک مشوق فروشگاه/شریک در کمپین آماده است.',
                'یک پاداش قابل دریافت از طرف واحد تجاری تعریف کنید.',
            ),
            $this->minimumCountCheck(
                'sponsor_rewards',
                'مشوق اسپانسری',
                $this->rewardCountForPartners($venueId, $campaignIds, $partnerIds, true),
                1,
                'حداقل یک مشوق اسپانسری در مسیر دمو وجود دارد.',
                'یک پیشنهاد یا حمایت اسپانسر را به پاداش/گنج کمپین وصل کنید.',
            ),
            $this->minimumCountCheck(
                'inventory_allocations',
                'ردیابی سهم و موجودی',
                $campaignIds->isEmpty() ? 0 : RewardInventoryAllocation::query()
                    ->whereIn('reward_definition_id', RewardDefinition::query()->whereIn('campaign_id', $campaignIds)->select('id'))
                    ->where('status', RecordStatus::Active)
                    ->count(),
                1,
                'حداقل یک سهم واحد/موجودی برای دمو قابل ردیابی است.',
                'برای مشوق‌های قابل دریافت، سهم هر واحد و موجودی را ثبت کنید.',
                warningOnly: true,
            ),
            $this->minimumCountCheck(
                'display_operations',
                'نمایشگرهای عملیاتی',
                $venueId ? DisplayDevice::query()->where('venue_id', $venueId)->where('status', RecordStatus::Active)->count() : 0,
                1,
                'حداقل یک نمایشگر برای لایه رسانه و تبلیغات آماده است.',
                'نمایشگر ورودی یا تبلیغاتی فعال را برای مکان ثبت کنید.',
            ),
            $this->minimumCountCheck(
                'ravaq_hub',
                'هاب/رواق تجاری',
                $venueId ? Hub::query()
                    ->where('code', 'ravaq-commercial-hub')
                    ->where('status', RecordStatus::Active)
                    ->whereHas('zone', fn ($query) => $query->where('venue_id', $venueId))
                    ->count() : 0,
                1,
                'رواق تجاری اکوپارک برای مدیریت واحدها آماده است.',
                'هاب رواق/زون تجاری مکان را فعال کنید.',
            ),
            $this->minimumCountCheck(
                'role_scopes',
                'نقش‌ها و دسترسی‌های اجرایی',
                UserAccessScope::query()
                    ->where('status', RecordStatus::Active)
                    ->whereIn('role_key', ['hub_manager', 'shop_manager', 'internal_sponsor'])
                    ->count(),
                3,
                'دسترسی مدیر رواق، فروشگاه و اسپانسر برای دمو وجود دارد.',
                'برای مدیر رواق، فروشگاه و اسپانسر دسترسی فعال تعریف کنید.',
            ),
            $this->minimumCountCheck(
                'venue_manager_scope',
                'دسترسی مدیر مکان',
                $venueId ? UserAccessScope::query()
                    ->where('status', RecordStatus::Active)
                    ->where('role_key', 'venue_executive')
                    ->where('scope_type', 'venue')
                    ->where('scope_id', $venueId)
                    ->count() : 0,
                1,
                'مدیر اجرایی مکان به پنل مکان دسترسی دارد.',
                'برای مدیر اجرایی اکوپارک یک دسترسی venue_executive ثبت کنید.',
                warningOnly: true,
            ),
            $this->routeCheck(),
        ]);

        $summary = [
            'venueCode' => $venueCode,
            'campaigns' => array_values($campaigns->map(fn (Campaign $campaign): array => [
                'code' => $campaign->code,
                'name' => $campaign->name,
            ])->all()),
            'ready' => $checks->where('status', 'fail')->isEmpty(),
            'passCount' => $checks->where('status', 'pass')->count(),
            'warningCount' => $checks->where('status', 'warning')->count(),
            'failCount' => $checks->where('status', 'fail')->count(),
        ];

        return [
            'summary' => $summary,
            'checks' => array_values($checks->all()),
            'nextActions' => array_values($checks
                ->whereIn('status', ['warning', 'fail'])
                ->pluck('nextAction')
                ->filter(fn (mixed $action): bool => is_string($action))
                ->all()),
        ];
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @param  Collection<int, string>  $campaignIds
     */
    private function countForVenueCampaigns($query, ?string $venueId, Collection $campaignIds): int
    {
        if (! $venueId || $campaignIds->isEmpty()) {
            return 0;
        }

        return (int) $query
            ->where('venue_id', $venueId)
            ->whereIn('campaign_id', $campaignIds)
            ->where('status', RecordStatus::Active)
            ->count();
    }

    /**
     * @param  Collection<int, string>  $campaignIds
     * @param  Collection<int, string>  $partnerIds
     */
    private function rewardCountForPartners(?string $venueId, Collection $campaignIds, Collection $partnerIds, bool $sponsorOnly): int
    {
        if (! $venueId || $campaignIds->isEmpty() || $partnerIds->isEmpty()) {
            return 0;
        }

        $query = RewardDefinition::query()
            ->where('venue_id', $venueId)
            ->whereIn('campaign_id', $campaignIds)
            ->where('status', RecordStatus::Active);

        if ($sponsorOnly) {
            $sponsorIds = PartnerAccount::query()
                ->where('venue_id', $venueId)
                ->where('partner_type', 'sponsor')
                ->pluck('id');

            return (int) (clone $query)
                ->where(fn ($rewardQuery) => $rewardQuery
                    ->whereIn('partner_account_id', $sponsorIds)
                    ->orWhere('reward_type', 'like', '%sponsor%')
                    ->orWhere('metadata->source', 'sponsor_proposal_activation')
                    ->orWhere('metadata->source', 'admin_sponsor_activation'))
                ->count();
        }

        return (int) (clone $query)
            ->whereIn('partner_account_id', $partnerIds)
            ->whereHas('partnerAccount', fn ($partnerQuery) => $partnerQuery->where('partner_type', '!=', 'sponsor'))
            ->count();
    }

    /** @return ReadinessCheck */
    private function routeCheck(): array
    {
        $missingRoutes = collect(['venue.dashboard', 'ravaq.dashboard', 'hub.dashboard', 'sponsor.dashboard', 'dashboard'])
            ->reject(fn (string $route): bool => Route::has($route))
            ->values();

        return $this->check(
            'panel_routes',
            'مسیر پنل‌های مدیریتی',
            $missingRoutes->isEmpty(),
            5 - $missingRoutes->count(),
            'مسیر پنل‌های مکان، رواق، هاب، اسپانسر و داشبورد اصلی ثبت شده‌اند.',
            'Routeهای ناقص را برای پنل‌های مدیریتی تکمیل کنید: '.$missingRoutes->implode('، '),
        );
    }

    /** @return ReadinessCheck */
    private function minimumCountCheck(
        string $key,
        string $label,
        int $count,
        int $minimum,
        string $passMessage,
        string $nextAction,
        bool $warningOnly = false,
    ): array {
        return $this->check(
            $key,
            $label,
            $count >= $minimum,
            $count,
            $passMessage,
            $nextAction,
            $warningOnly,
        ) + ['minimum' => $minimum];
    }

    /** @return ReadinessCheck */
    private function check(
        string $key,
        string $label,
        bool $passes,
        int $count,
        string $passMessage,
        string $nextAction,
        bool $warningOnly = false,
    ): array {
        return [
            'key' => $key,
            'label' => $label,
            'status' => $passes ? 'pass' : ($warningOnly ? 'warning' : 'fail'),
            'count' => $count,
            'message' => $passes ? $passMessage : $nextAction,
            'nextAction' => $passes ? null : $nextAction,
        ];
    }
}
