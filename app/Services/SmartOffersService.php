<?php

namespace App\Services;

use App\Enums\RecordStatus;
use App\Models\AdEvent;
use App\Models\AdPlacement;
use App\Models\AdRequest;
use App\Models\Campaign;
use App\Models\GameChallengeProgress;
use App\Models\GameParty;
use App\Models\RewardDefinition;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * @phpstan-type OnlineAd array{
 *     id: string,
 *     code: string,
 *     title: string,
 *     bodyCopy: string|null,
 *     ctaText: string,
 *     targetUrl: string|null,
 *     adType: string,
 *     creativeType: string|null,
 *     assetUrl: string|null,
 *     placementType: string|null,
 *     placementTypes: list<string>,
 *     venueName: string|null,
 *     partnerName: string|null,
 *     partnerType: string|null,
 *     startsAt: string|null,
 *     endsAt: string|null,
 *     rewardedPoints: int|null,
 *     requiredSeconds: int|null,
 *     gameStageIndex: int|null,
 *     checkpointKey: string|null,
 *     commercialModel: string|null
 * }
 * @phpstan-type PartnerOffer array{
 *     id: string,
 *     code: string,
 *     name: string,
 *     rewardType: string,
 *     pointCost: int|null,
 *     stockQuantity: int|null,
 *     userRewardsCount: int,
 *     description: mixed,
 *     terms: mixed,
 *     rewardTier: mixed,
 *     rewardOption: mixed,
 *     availableFrom: mixed,
 *     availableUntil: mixed,
 *     venueName: string|null,
 *     campaignName: string|null,
 *     partnerName: string|null,
 *     partnerType: string|null
 * }
 * @phpstan-type GameOffer array{
 *     id: string,
 *     kind: string,
 *     adRequestId: string|null,
 *     title: string,
 *     partnerName: string|null,
 *     bodyCopy: mixed,
 *     ctaText: string,
 *     targetUrl: string|null,
 *     assetUrl: string|null,
 *     placementType: string|null,
 *     points: int|null,
 *     terms: mixed,
 *     bonusPoints: int|null,
 *     requiredSeconds: int|null,
 *     stageIndex: int|null,
 *     checkpointKey: string|null,
 *     commercialModel: string|null
 * }
 */
class SmartOffersService
{
    private const ONLINE_AD_PLACEMENTS = [
        'qr_landing',
        'reward_page',
        'map_route',
        'post_mission',
    ];

    private const GAME_EVENT_TYPES = [
        'game_offer_view',
        'game_offer_click',
        'game_clue_complete',
    ];

    /** @return array<string, mixed> */
    public function publicOverview(): array
    {
        $ads = $this->approvedOnlineAds();
        $offers = $this->activePartnerOffers();

        return [
            'governance' => [
                'title' => 'پیشنهادهای امروز اکسپلوریا',
                'policy' => 'این صفحه فقط پیشنهادها و آگهی‌های تاییدشده تیم اکسپلوریا را نمایش می‌دهد. مدیران مکان و هاب می‌توانند ظرفیت اجرا و هماهنگی محلی را پیشنهاد کنند، اما حق حذف یا رد نهایی تبلیغات را ندارند.',
            ],
            'stats' => [
                'ads' => $ads->count(),
                'offers' => $offers->count(),
                'total' => $ads->count() + $offers->count(),
            ],
            'ads' => $ads,
            'offers' => $offers,
        ];
    }

    /** @return Collection<int, covariant GameOffer> */
    public function gameOffersForCampaign(?Campaign $campaign): Collection
    {
        $gamePlacements = ['map_route', 'post_mission', 'reward_page'];

        $ads = $this->approvedOnlineAds($campaign?->venue_id)
            ->filter(fn (array $ad): bool => collect($ad['placementTypes'])->intersect($gamePlacements)->isNotEmpty())
            ->map(fn (array $ad): array => $this->gameAdOffer($ad, $gamePlacements));

        $rewards = $this->activePartnerOffers($campaign)
            ->map(fn (array $reward): array => $this->gameRewardOffer($reward));

        return $ads
            ->merge($rewards)
            ->filter(fn (array $offer): bool => filled($offer['title']))
            ->values()
            ->take(24);
    }

    /** @return Collection<int, covariant GameOffer> */
    public function gameOffersForParty(?Campaign $campaign, ?GameParty $party): Collection
    {
        $offers = $this->gameOffersForCampaign($campaign);

        if (! $party) {
            return $offers;
        }

        $party->loadMissing(['progress', 'bonusClaims']);
        $current = $party->progress
            ->where('status', 'available')
            ->sortBy('step_index')
            ->first();
        $startedAdIds = $party->bonusClaims
            ->where('status', 'started')
            ->pluck('ad_request_id')
            ->all();

        if (! $current instanceof GameChallengeProgress) {
            return $offers
                ->filter(fn (array $offer): bool => $offer['kind'] === 'reward'
                    || in_array($offer['adRequestId'], $startedAdIds, true))
                ->values();
        }

        $checkpointKey = data_get($current->metadata, 'checkpoint_key');

        return $offers
            ->filter(function (array $offer) use ($checkpointKey, $current, $startedAdIds): bool {
                if ($offer['kind'] === 'reward') {
                    return true;
                }

                if (in_array($offer['adRequestId'], $startedAdIds, true)) {
                    return true;
                }

                $matchesStage = $offer['stageIndex'] === null
                    || $offer['stageIndex'] === $current->step_index;
                $matchesCheckpoint = $offer['checkpointKey'] === null
                    || $offer['checkpointKey'] === $checkpointKey;

                return $matchesStage && $matchesCheckpoint;
            })
            ->values();
    }

    /** @param array<string, mixed> $data */
    public function recordGameOfferEvent(array $data): AdEvent
    {
        if (! in_array($data['event_type'], self::GAME_EVENT_TYPES, true)) {
            throw ValidationException::withMessages([
                'event_type' => 'نوع رخداد بازی معتبر نیست.',
            ]);
        }

        $adRequest = AdRequest::query()
            ->whereKey($data['ad_request_id'])
            ->where('status', 'approved')
            ->whereHas('placements', fn ($query) => $query
                ->whereIn('placement_type', ['map_route', 'post_mission', 'reward_page'])
                ->where('status', 'approved'))
            ->first();

        if (! $adRequest instanceof AdRequest) {
            throw ValidationException::withMessages([
                'ad_request_id' => 'این پیشنهاد برای بازی آنلاین فعال یا تاییدشده نیست.',
            ]);
        }

        return AdEvent::query()->create([
            'ad_request_id' => $adRequest->id,
            'display_device_id' => null,
            'event_type' => $data['event_type'],
            'occurred_at' => now(),
            'metadata' => [
                'source' => 'online_game',
                'mission_code' => $data['mission_code'] ?? null,
                'choice' => $data['choice'] ?? null,
                'context' => $data['metadata'] ?? [],
            ],
        ]);
    }

    /** @return Collection<int, covariant OnlineAd> */
    private function approvedOnlineAds(?string $venueId = null): Collection
    {
        $now = now();

        return AdRequest::query()
            ->when($venueId, fn ($query) => $query->where('venue_id', $venueId))
            ->where('status', 'approved')
            ->where(function ($query) use ($now): void {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($query) use ($now): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->whereHas('placements', fn ($query) => $query->whereIn('placement_type', self::ONLINE_AD_PLACEMENTS))
            ->with([
                'venue:id,code,name',
                'partnerAccount:id,code,name,partner_type',
                'placements' => fn ($query) => $query->whereIn('placement_type', self::ONLINE_AD_PLACEMENTS)->orderBy('priority'),
                'creatives:id,ad_request_id,creative_type,asset_url,headline,body_copy,cta_text,status',
            ])
            ->latest('created_at')
            ->limit(12)
            ->get()
            ->toBase()
            ->map(fn (AdRequest $adRequest): array => $this->onlineAd($adRequest))
            ->values();
    }

    /** @return Collection<int, covariant PartnerOffer> */
    private function activePartnerOffers(?Campaign $campaign = null): Collection
    {
        $now = now();

        return RewardDefinition::query()
            ->when($campaign, fn ($query) => $query->where(function ($query) use ($campaign): void {
                $query
                    ->where('campaign_id', $campaign->id)
                    ->orWhere('venue_id', $campaign->venue_id);
            }))
            ->where('status', RecordStatus::Active)
            ->where(function ($query) use ($now): void {
                $query->whereNull('metadata->available_from')
                    ->orWhere('metadata->available_from', '<=', $now->toIso8601String());
            })
            ->where(function ($query) use ($now): void {
                $query->whereNull('metadata->available_until')
                    ->orWhere('metadata->available_until', '>=', $now->toIso8601String());
            })
            ->with(['venue:id,code,name', 'campaign:id,code,name', 'partnerAccount:id,code,name,partner_type'])
            ->withCount(['userRewards'])
            ->latest('created_at')
            ->limit(12)
            ->get()
            ->toBase()
            ->filter(fn (RewardDefinition $reward): bool => ($reward->metadata['availability_status'] ?? 'active') !== 'paused')
            ->map(fn (RewardDefinition $reward): array => $this->partnerOffer($reward))
            ->values();
    }

    /**
     * @param  OnlineAd  $ad
     * @param  list<string>  $gamePlacements
     * @return GameOffer
     */
    private function gameAdOffer(array $ad, array $gamePlacements): array
    {
        $gamePlacement = collect($ad['placementTypes'])
            ->first(fn (string $placement): bool => in_array($placement, $gamePlacements, true));

        return [
            'id' => 'ad-'.$ad['id'],
            'kind' => 'ad',
            'adRequestId' => $ad['id'],
            'title' => $ad['title'],
            'partnerName' => $ad['partnerName'],
            'bodyCopy' => $ad['bodyCopy'],
            'ctaText' => $ad['ctaText'],
            'targetUrl' => $ad['targetUrl'],
            'assetUrl' => $ad['assetUrl'],
            'placementType' => $gamePlacement,
            'points' => null,
            'terms' => null,
            'bonusPoints' => $ad['rewardedPoints'],
            'requiredSeconds' => $ad['requiredSeconds'],
            'stageIndex' => $ad['gameStageIndex'],
            'checkpointKey' => $ad['checkpointKey'],
            'commercialModel' => $ad['commercialModel'],
        ];
    }

    /**
     * @param  PartnerOffer  $reward
     * @return GameOffer
     */
    private function gameRewardOffer(array $reward): array
    {
        return [
            'id' => 'reward-'.$reward['id'],
            'kind' => 'reward',
            'adRequestId' => null,
            'title' => $reward['name'],
            'partnerName' => $reward['partnerName'],
            'bodyCopy' => $reward['description'],
            'ctaText' => 'دیدن پیشنهاد',
            'targetUrl' => route('offers.page'),
            'assetUrl' => null,
            'placementType' => 'game_reward',
            'points' => $reward['pointCost'],
            'terms' => $reward['terms'],
            'bonusPoints' => null,
            'requiredSeconds' => null,
            'stageIndex' => null,
            'checkpointKey' => null,
            'commercialModel' => null,
        ];
    }

    /** @return OnlineAd */
    private function onlineAd(AdRequest $adRequest): array
    {
        $placement = $adRequest->placements->first();
        $creative = $adRequest->creatives->first();
        $placementTypes = $adRequest->placements
            ->map(fn (AdPlacement $adPlacement): string => $adPlacement->placement_type)
            ->values()
            ->all();

        return [
            'id' => $adRequest->id,
            'code' => $adRequest->code,
            'title' => $adRequest->title,
            'bodyCopy' => $adRequest->body_copy,
            'ctaText' => $adRequest->cta_text ?? $creative->cta_text ?? 'مشاهده پیشنهاد',
            'targetUrl' => $adRequest->target_url,
            'adType' => $adRequest->ad_type,
            'creativeType' => $creative?->creative_type,
            'assetUrl' => $creative?->asset_url,
            'placementType' => $placement?->placement_type,
            'placementTypes' => array_values($placementTypes),
            'venueName' => $adRequest->venue?->name,
            'partnerName' => $adRequest->partnerAccount?->name,
            'partnerType' => $adRequest->partnerAccount?->partner_type,
            'startsAt' => $adRequest->starts_at?->toIso8601String(),
            'endsAt' => $adRequest->ends_at?->toIso8601String(),
            'rewardedPoints' => is_numeric(data_get($adRequest->metadata, 'rewarded_points'))
                ? (int) data_get($adRequest->metadata, 'rewarded_points')
                : null,
            'requiredSeconds' => is_numeric(data_get($adRequest->metadata, 'required_seconds'))
                ? (int) data_get($adRequest->metadata, 'required_seconds')
                : null,
            'gameStageIndex' => is_numeric(data_get($adRequest->metadata, 'game_stage_index'))
                ? (int) data_get($adRequest->metadata, 'game_stage_index')
                : null,
            'checkpointKey' => is_string(data_get($adRequest->metadata, 'checkpoint_key'))
                ? data_get($adRequest->metadata, 'checkpoint_key')
                : null,
            'commercialModel' => is_string(data_get($adRequest->metadata, 'commercial_model'))
                ? data_get($adRequest->metadata, 'commercial_model')
                : null,
        ];
    }

    /** @return PartnerOffer */
    private function partnerOffer(RewardDefinition $reward): array
    {
        return [
            'id' => $reward->id,
            'code' => $reward->code,
            'name' => $reward->name,
            'rewardType' => $reward->reward_type,
            'pointCost' => $reward->point_cost,
            'stockQuantity' => $reward->stock_quantity,
            'userRewardsCount' => (int) $reward->getAttribute('user_rewards_count'),
            'description' => $reward->metadata['description'] ?? null,
            'terms' => $reward->metadata['terms'] ?? null,
            'rewardTier' => $reward->metadata['reward_tier'] ?? null,
            'rewardOption' => $reward->metadata['reward_option'] ?? null,
            'availableFrom' => $reward->metadata['available_from'] ?? null,
            'availableUntil' => $reward->metadata['available_until'] ?? null,
            'venueName' => $reward->venue?->name,
            'campaignName' => $reward->campaign?->name,
            'partnerName' => $reward->partnerAccount?->name,
            'partnerType' => $reward->partnerAccount?->partner_type,
        ];
    }
}
