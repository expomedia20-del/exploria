<?php

namespace Tests\Feature\Game;

use App\Enums\UserRole;
use App\Models\AdEvent;
use App\Models\AdRequest;
use App\Models\Campaign;
use App\Models\MissionInstance;
use App\Models\PartnerAccount;
use App\Models\QrCode;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EcoParkTreasureGameTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PilotLocationSeeder::class);
    }

    public function test_guest_can_open_online_game_with_real_campaign_and_qr_context(): void
    {
        $this->withoutVite();

        $this->get(route('games.ecopark-treasure'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('games/ecopark-treasure')
                ->where('game.campaign.code', 'ecopark-pilot-1405')
                ->where('game.entryQr.code', PilotLocationSeeder::DEMO_QR_CODE)
                ->where('game.visitorState.isAuthenticated', false)
                ->where('game.visitorState.hasLinkedVisit', false)
                ->has('game.missionNodes', 4)
                ->has('game.gameOffers')
                ->where('game.missionFlow', null));
    }

    public function test_online_game_lists_only_approved_game_offers(): void
    {
        $this->withoutVite();

        $this->createGameAd('Approved game clue', 'approved', 'map_route');
        $this->createGameAd('Pending hidden clue', 'pending_review', 'map_route');
        $this->createGameAd('Display only hidden clue', 'approved', 'screen_loop');

        $this->get(route('games.ecopark-treasure'))
            ->assertOk()
            ->assertSee('Approved game clue')
            ->assertDontSee('Pending hidden clue')
            ->assertDontSee('Display only hidden clue')
            ->assertInertia(fn (Assert $page) => $page
                ->component('games/ecopark-treasure')
                ->has('game.gameOffers'));
    }

    public function test_game_offer_event_is_recorded_for_approved_game_ad(): void
    {
        $adRequest = $this->createGameAd('Trackable game clue', 'approved', 'post_mission');

        $this->postJson(route('offers.game-events.store'), [
            'ad_request_id' => $adRequest->id,
            'event_type' => 'game_offer_click',
            'mission_code' => 'scan-entry-qr',
            'choice' => 'quick-route',
            'metadata' => ['surface' => 'ecopark-treasure'],
        ])
            ->assertCreated()
            ->assertJsonPath('data.eventType', 'game_offer_click');

        $this->assertDatabaseHas('ad_events', [
            'ad_request_id' => $adRequest->id,
            'event_type' => 'game_offer_click',
        ]);

        $event = AdEvent::query()->where('ad_request_id', $adRequest->id)->firstOrFail();

        $this->assertSame('online_game', $event->metadata['source']);
        $this->assertSame('scan-entry-qr', $event->metadata['mission_code']);
    }

    public function test_game_offer_event_rejects_non_game_or_pending_ads(): void
    {
        $pending = $this->createGameAd('Pending event clue', 'pending_review', 'map_route');
        $displayOnly = $this->createGameAd('Display event clue', 'approved', 'screen_loop');

        $this->postJson(route('offers.game-events.store'), [
            'ad_request_id' => $pending->id,
            'event_type' => 'game_offer_click',
        ])->assertUnprocessable();

        $this->postJson(route('offers.game-events.store'), [
            'ad_request_id' => $displayOnly->id,
            'event_type' => 'game_offer_click',
        ])->assertUnprocessable();
    }

    public function test_authenticated_visitor_sees_saved_mission_progress_in_online_game(): void
    {
        $this->withoutVite();

        $visitor = User::factory()->create(['role' => UserRole::Visitor]);
        $qr = QrCode::query()->where('code', PilotLocationSeeder::DEMO_QR_CODE)->firstOrFail();
        $visit = Visit::query()->create([
            'user_id' => $visitor->id,
            'qr_code_id' => $qr->id,
            'venue_id' => $qr->venue_id,
            'touchpoint_id' => $qr->touchpoint_id,
            'campaign_id' => $qr->campaign_id,
            'source' => 'qr_landing',
            'status' => 'confirmed',
            'occurred_at' => now(),
            'metadata' => ['is_demo' => true],
        ]);
        $mission = MissionInstance::query()->where('code', 'scan-entry-qr')->firstOrFail();

        $this->actingAs($visitor)
            ->post(route('visits.missions.complete', [$visit, $mission]))
            ->assertRedirect();

        $this->actingAs($visitor)
            ->get(route('games.ecopark-treasure'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('games/ecopark-treasure')
                ->where('game.latestVisit.id', $visit->id)
                ->where('game.visitorState.isAuthenticated', true)
                ->where('game.visitorState.hasLinkedVisit', true)
                ->where('game.missionFlow.stats.totalPoints', 120)
                ->where('game.missionFlow.stats.completedMissions', 1)
                ->where('game.missionFlow.missions.0.code', 'scan-entry-qr')
                ->where('game.missionFlow.missions.0.status', 'completed'));
    }

    public function test_authenticated_visitor_can_open_online_game_for_a_specific_visit(): void
    {
        $this->withoutVite();

        $visitor = User::factory()->create(['role' => UserRole::Visitor]);
        $qr = QrCode::query()->where('code', PilotLocationSeeder::DEMO_QR_CODE)->firstOrFail();
        $visit = Visit::query()->create([
            'user_id' => $visitor->id,
            'qr_code_id' => $qr->id,
            'venue_id' => $qr->venue_id,
            'touchpoint_id' => $qr->touchpoint_id,
            'campaign_id' => $qr->campaign_id,
            'source' => 'qr_landing',
            'status' => 'confirmed',
            'occurred_at' => now()->subDay(),
            'metadata' => ['is_demo' => true],
        ]);

        $this->actingAs($visitor)
            ->get(route('games.ecopark-treasure', ['visit' => $visit->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('games/ecopark-treasure')
                ->where('game.latestVisit.id', $visit->id)
                ->where('game.latestVisit.showUrl', route('visits.show', ['visit' => $visit->id])));
    }

    private function createGameAd(string $title, string $status, string $placementType): AdRequest
    {
        $campaign = Campaign::query()->where('code', 'ecopark-pilot-1405')->firstOrFail();
        $partner = PartnerAccount::query()->where('venue_id', $campaign->venue_id)->firstOrFail();

        $adRequest = AdRequest::query()->create([
            'venue_id' => $campaign->venue_id,
            'partner_account_id' => $partner->id,
            'hub_id' => null,
            'touchpoint_id' => null,
            'submitted_by_user_id' => null,
            'code' => str($title)->slug()->append('-ad')->toString(),
            'title' => $title,
            'body_copy' => 'پیشنهاد تاییدشده برای نمایش در مسیر بازی آنلاین.',
            'cta_text' => 'دیدن سرنخ',
            'target_url' => 'https://example.com/game-offer',
            'advertiser_type' => 'member_partner',
            'ad_type' => 'standalone',
            'status' => $status,
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDay(),
            'budget_amount' => null,
            'impression_cap' => null,
            'click_cap' => null,
            'metadata' => ['source' => 'game_offer_test'],
        ]);

        $adRequest->creatives()->create([
            'creative_type' => 'text_card',
            'headline' => $title,
            'body_copy' => 'کارت سرنخ اسپانسری برای بازی.',
            'cta_text' => 'دیدن سرنخ',
            'status' => $status === 'approved' ? 'approved' : 'draft',
            'metadata' => ['source' => 'game_offer_test'],
        ]);

        $adRequest->placements()->create([
            'placement_type' => $placementType,
            'status' => $status === 'approved' ? 'approved' : 'pending_review',
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDay(),
            'priority' => 2,
            'metadata' => ['source' => 'game_offer_test'],
        ]);

        return $adRequest;
    }
}
