<?php

namespace Tests\Feature\Offers;

use App\Enums\RecordStatus;
use App\Models\AdRequest;
use App\Models\Campaign;
use App\Models\PartnerAccount;
use App\Models\RewardDefinition;
use App\Services\SmartOffersService;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SmartOffersPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PilotLocationSeeder::class);
    }

    public function test_public_offers_page_lists_active_offers_and_online_ads(): void
    {
        $this->withoutVite();
        $this->createActivePartnerOffer('Smart public cafe offer');
        $this->createApprovedOnlineAd('Smart QR landing ad');

        $this->get(route('offers.page'))
            ->assertOk()
            ->assertSee('Smart public cafe offer')
            ->assertSee('Smart QR landing ad')
            ->assertInertia(fn (Assert $page) => $page
                ->component('offers/index')
                ->where('ads.0.channel', 'legacy_public')
                ->where('governance.pricingPolicy', '۵ انتشار نخست هر فروشگاه در پایلوت رایگان؛ انتشارهای بعدی تعرفه‌دار.')
                ->has('dashboardSummary.items'));
    }

    public function test_public_offers_api_excludes_pending_ads_and_inactive_offers(): void
    {
        $this->createActivePartnerOffer('Visible public offer');
        $this->createActivePartnerOffer('Inactive public offer', RecordStatus::Inactive);
        $this->createApprovedOnlineAd('Visible approved ad');
        $this->createApprovedOnlineAd('Pending hidden ad', 'pending_review');

        $this->getJson(route('offers.index'))
            ->assertOk()
            ->assertJsonFragment(['name' => 'Visible public offer'])
            ->assertJsonFragment(['title' => 'Visible approved ad'])
            ->assertJsonMissing(['name' => 'Inactive public offer'])
            ->assertJsonMissing(['title' => 'Pending hidden ad']);
    }

    public function test_public_feed_is_separate_from_rewarded_game_content_with_legacy_fallback(): void
    {
        $publicAd = $this->createApprovedOnlineAd('Explicit public storefront ad', 'approved', [
            'placement_type' => 'public_feed',
        ]);
        $rewardedAd = $this->createApprovedOnlineAd('Rewarded stage content', 'approved', [
            'ad_type' => 'rewarded_content',
            'placement_type' => 'post_mission',
            'metadata' => [
                'source' => 'smart_offers_test',
                'rewarded_points' => 30,
                'game_stage_index' => 2,
            ],
        ]);
        $legacyAd = $this->createApprovedOnlineAd('Legacy public compatibility ad');

        $this->getJson(route('offers.index'))
            ->assertOk()
            ->assertJsonFragment(['id' => $publicAd->id, 'channel' => 'public_feed'])
            ->assertJsonFragment(['id' => $legacyAd->id, 'channel' => 'legacy_public'])
            ->assertJsonMissing(['id' => $rewardedAd->id, 'title' => 'Rewarded stage content']);

        $campaign = Campaign::query()->where('venue_id', $publicAd->venue_id)->firstOrFail();
        $gameTitles = app(SmartOffersService::class)
            ->gameOffersForCampaign($campaign)
            ->pluck('title');

        $this->assertTrue($gameTitles->contains('Rewarded stage content'));
        $this->assertFalse($gameTitles->contains('Explicit public storefront ad'));
    }

    public function test_public_feed_requires_active_approved_placement_and_creative_and_remaining_caps(): void
    {
        $this->createApprovedOnlineAd('Visible strict public ad', 'approved', [
            'placement_type' => 'public_feed',
        ]);
        $this->createApprovedOnlineAd('Pending placement hidden ad', 'approved', [
            'placement_type' => 'public_feed',
            'placement_status' => 'pending_review',
        ]);
        $this->createApprovedOnlineAd('Expired placement hidden ad', 'approved', [
            'placement_type' => 'public_feed',
            'placement_ends_at' => now()->subMinute(),
        ]);
        $this->createApprovedOnlineAd('Draft creative hidden ad', 'approved', [
            'placement_type' => 'public_feed',
            'creative_status' => 'draft',
        ]);
        $impressionCapped = $this->createApprovedOnlineAd('Impression capped hidden ad', 'approved', [
            'placement_type' => 'public_feed',
            'impression_cap' => 1,
        ]);
        $impressionCapped->events()->create([
            'event_type' => 'impression',
            'occurred_at' => now(),
            'metadata' => ['source' => 'smart_offers_test'],
        ]);
        $clickCapped = $this->createApprovedOnlineAd('Click capped hidden ad', 'approved', [
            'placement_type' => 'public_feed',
            'click_cap' => 1,
        ]);
        $clickCapped->events()->create([
            'event_type' => 'click',
            'occurred_at' => now(),
            'metadata' => ['source' => 'smart_offers_test'],
        ]);

        $this->getJson(route('offers.index'))
            ->assertOk()
            ->assertJsonFragment(['title' => 'Visible strict public ad'])
            ->assertJsonMissing(['title' => 'Pending placement hidden ad'])
            ->assertJsonMissing(['title' => 'Expired placement hidden ad'])
            ->assertJsonMissing(['title' => 'Draft creative hidden ad'])
            ->assertJsonMissing(['title' => 'Impression capped hidden ad'])
            ->assertJsonMissing(['title' => 'Click capped hidden ad']);
    }

    private function createActivePartnerOffer(string $name, RecordStatus $status = RecordStatus::Active): RewardDefinition
    {
        $partner = PartnerAccount::query()->where('status', RecordStatus::Active)->firstOrFail();
        $campaign = Campaign::query()->where('venue_id', $partner->venue_id)->firstOrFail();

        return RewardDefinition::query()->create([
            'campaign_id' => $campaign->id,
            'venue_id' => $partner->venue_id,
            'partner_account_id' => $partner->id,
            'code' => str($name)->slug()->append('-offer')->toString(),
            'name' => $name,
            'reward_type' => 'partner_coupon',
            'point_cost' => 80,
            'stock_quantity' => 15,
            'status' => $status,
            'metadata' => [
                'approval_status' => $status === RecordStatus::Active ? 'approved' : 'rejected',
                'availability_status' => $status === RecordStatus::Active ? 'active' : 'paused',
                'description' => 'پیشنهاد عمومی تاییدشده برای صفحه پیشنهادهای امروز.',
                'terms' => 'اعتبار مصرف پس از تایید فروشگاه ثبت می‌شود.',
            ],
        ]);
    }

    /** @param array<string, mixed> $options */
    private function createApprovedOnlineAd(string $title, string $status = 'approved', array $options = []): AdRequest
    {
        $partner = PartnerAccount::query()->where('status', RecordStatus::Active)->firstOrFail();

        $adRequest = AdRequest::query()->create([
            'venue_id' => $partner->venue_id,
            'partner_account_id' => $partner->id,
            'hub_id' => null,
            'touchpoint_id' => null,
            'submitted_by_user_id' => null,
            'code' => str($title)->slug()->append('-ad')->toString(),
            'title' => $title,
            'body_copy' => 'آگهی عمومی تاییدشده برای جایگاه‌های آنلاین اکسپلوریا.',
            'cta_text' => 'مشاهده',
            'target_url' => 'https://example.com/offers',
            'advertiser_type' => 'member_partner',
            'ad_type' => $options['ad_type'] ?? 'standalone',
            'status' => $status,
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDay(),
            'budget_amount' => null,
            'impression_cap' => $options['impression_cap'] ?? null,
            'click_cap' => $options['click_cap'] ?? null,
            'metadata' => $options['metadata'] ?? ['source' => 'smart_offers_test'],
        ]);

        $adRequest->creatives()->create([
            'creative_type' => 'text_card',
            'headline' => $title,
            'body_copy' => 'کارت تبلیغاتی برای صفحه پیشنهادها.',
            'cta_text' => 'مشاهده',
            'status' => $options['creative_status'] ?? $status,
            'metadata' => ['source' => 'smart_offers_test'],
        ]);

        $adRequest->placements()->create([
            'placement_type' => $options['placement_type'] ?? 'qr_landing',
            'status' => $options['placement_status'] ?? ($status === 'approved' ? 'approved' : 'pending_review'),
            'starts_at' => $options['placement_starts_at'] ?? now()->subHour(),
            'ends_at' => $options['placement_ends_at'] ?? now()->addDay(),
            'priority' => 3,
            'metadata' => ['source' => 'smart_offers_test'],
        ]);

        return $adRequest;
    }
}
