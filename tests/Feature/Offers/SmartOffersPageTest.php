<?php

namespace Tests\Feature\Offers;

use App\Enums\RecordStatus;
use App\Models\AdRequest;
use App\Models\Campaign;
use App\Models\PartnerAccount;
use App\Models\RewardDefinition;
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
                ->component('offers/index'));
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

    private function createApprovedOnlineAd(string $title, string $status = 'approved'): AdRequest
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
            'ad_type' => 'standalone',
            'status' => $status,
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDay(),
            'budget_amount' => null,
            'impression_cap' => null,
            'click_cap' => null,
            'metadata' => ['source' => 'smart_offers_test'],
        ]);

        $adRequest->creatives()->create([
            'creative_type' => 'text_card',
            'headline' => $title,
            'body_copy' => 'کارت تبلیغاتی برای صفحه پیشنهادها.',
            'cta_text' => 'مشاهده',
            'status' => $status,
            'metadata' => ['source' => 'smart_offers_test'],
        ]);

        $adRequest->placements()->create([
            'placement_type' => 'qr_landing',
            'status' => $status === 'approved' ? 'approved' : 'pending_review',
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDay(),
            'priority' => 3,
            'metadata' => ['source' => 'smart_offers_test'],
        ]);

        return $adRequest;
    }
}
