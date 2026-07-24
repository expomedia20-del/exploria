<?php

namespace Tests\Feature\Game;

use App\Enums\UserRole;
use App\Models\AdRequest;
use App\Models\Campaign;
use App\Models\GameEntryPass;
use App\Models\GameParty;
use App\Models\PartnerAccount;
use App\Models\QrCode;
use App\Models\User;
use App\Models\Visit;
use App\Services\EcoParkOnlineGameService;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EcoParkCollaborativeGameTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PilotLocationSeeder::class);
    }

    public function test_family_completes_verified_five_step_journey_with_collaboration_bonus(): void
    {
        $leader = User::factory()->create(['role' => UserRole::Visitor]);
        $visit = $this->visitFor($leader);

        $this->actingAs($leader)->post(route('games.ecopark-treasure.parties.create'), [
            'visit_id' => $visit->id,
            'mode' => 'family',
            'name' => 'خانواده کاوشگر',
            'companion_count' => 2,
        ])->assertRedirect();

        $party = GameParty::query()->with(['members', 'progress'])->firstOrFail();
        $this->assertSame('family', $party->mode);
        $this->assertCount(3, $party->members);
        $this->assertSame('completed', $party->progress->firstWhere('step_index', 1)->status);
        $this->assertSame('available', $party->progress->firstWhere('step_index', 2)->status);

        $this->actingAs($leader)->post(route('games.ecopark-treasure.parties.route', $party), [
            'route_key' => 'family',
        ])->assertRedirect();

        $leaderMember = $party->members->firstWhere('user_id', $leader->id);
        $companion = $party->members->firstWhere('member_type', 'companion');

        $this->actingAs($leader)
            ->from(route('games.ecopark-treasure'))
            ->post(route('games.ecopark-treasure.parties.hotspots', $party), [
                'hotspot_key' => 'book-garden',
                'member_id' => $leaderMember->id,
            ])
            ->assertSessionHasErrors('hotspot_key');

        $this->assertSame(
            0,
            count($party->progress()->where('step_index', 3)->firstOrFail()->metadata['found'] ?? []),
        );

        foreach ([
            ['nature', $leaderMember->id],
            ['fire-water', $companion->id],
            ['mina', $companion->id],
        ] as [$hotspot, $memberId]) {
            $this->actingAs($leader)->post(route('games.ecopark-treasure.parties.hotspots', $party), [
                'hotspot_key' => $hotspot,
                'member_id' => $memberId,
            ])->assertRedirect();
        }

        $party->refresh();
        $this->assertTrue($party->collaboration_bonus_awarded);
        $this->assertSame(140, $party->score);

        $this->actingAs($leader)->from(route('games.ecopark-treasure'))
            ->post(route('games.ecopark-treasure.parties.clue', $party), ['answer_key' => '999'])
            ->assertSessionHasErrors('answer_key');

        $this->actingAs($leader)->post(route('games.ecopark-treasure.parties.clue', $party), [
            'answer_key' => '۲۴۵',
        ])->assertRedirect();

        $this->actingAs($leader)->post(route('games.ecopark-treasure.parties.pass', $party))
            ->assertRedirect();

        $party->refresh();
        $this->assertSame('ready_for_visit', $party->status);
        $this->assertSame(340, $party->score);
        $this->assertDatabaseHas('game_entry_passes', [
            'game_party_id' => $party->id,
            'status' => 'active',
        ]);

        $this->actingAs($leader)->from(route('games.ecopark-treasure'))
            ->post(route('games.ecopark-treasure.parties.create'), [
                'visit_id' => $visit->id,
                'mode' => 'individual',
            ])
            ->assertSessionHasErrors('mode');
    }

    public function test_team_members_join_with_code_and_share_real_progress(): void
    {
        $leader = User::factory()->create(['role' => UserRole::Visitor]);
        $member = User::factory()->create(['role' => UserRole::Visitor]);

        $this->actingAs($leader)->post(route('games.ecopark-treasure.parties.create'), [
            'visit_id' => $this->visitFor($leader)->id,
            'mode' => 'team',
            'name' => 'تیم ستاره شمال',
        ])->assertRedirect();

        $party = GameParty::query()->firstOrFail();
        $this->assertNotNull($party->invite_code);

        $this->actingAs($member)->post(route('games.ecopark-treasure.parties.join'), [
            'invite_code' => strtolower($party->invite_code),
        ])->assertRedirect();

        $this->actingAs($leader)->post(route('games.ecopark-treasure.parties.route', $party), [
            'route_key' => 'quick',
        ])->assertRedirect();

        $this->actingAs($member)->post(route('games.ecopark-treasure.parties.hotspots', $party), [
            'hotspot_key' => 'mina',
        ])->assertRedirect();
        $this->actingAs($leader)->post(route('games.ecopark-treasure.parties.hotspots', $party), [
            'hotspot_key' => 'nature',
        ])->assertRedirect();
        $this->actingAs($member)->post(route('games.ecopark-treasure.parties.hotspots', $party), [
            'hotspot_key' => 'fire-water',
        ])->assertRedirect();

        $party->refresh();
        $this->assertTrue($party->collaboration_bonus_awarded);
        $this->assertSame(2, $party->members()->where('member_type', 'registered')->count());
        $this->assertSame('available', $party->progress()->where('step_index', 4)->value('status'));

        $this->actingAs($member)->get(route('games.ecopark-treasure'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('game.party.id', $party->id)
                ->where('game.party.foundHotspots', ['mina', 'nature', 'fire-water'])
                ->where('game.party.foundFragments', ['۳', '۱', '۷'])
                ->where('game.party.nextHotspotHint', null)
                ->where('game.party.collaborationBonusAwarded', true));
    }

    public function test_participant_dashboard_uses_single_online_game_flow_instead_of_legacy_missions(): void
    {
        $this->withoutVite();

        $user = User::factory()->create(['role' => UserRole::Visitor]);
        $visit = $this->visitFor($user);

        $this->actingAs($user)->post(route('games.ecopark-treasure.parties.create'), [
            'visit_id' => $visit->id,
            'mode' => 'individual',
        ])->assertRedirect();

        $party = GameParty::query()->firstOrFail();

        $this->actingAs($user)
            ->get(route('participant.dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('onlineGame.id', $party->id)
                ->where('onlineGame.score', 20)
                ->where('missionFlow', null)
                ->where(
                    'journey.nextAction.href',
                    route('games.ecopark-treasure', ['visit' => $visit->id]),
                )
                ->where(
                    'journey.activeCampaigns.0.experienceUrl',
                    route('games.ecopark-treasure', ['visit' => $visit->id]),
                ));
    }

    public function test_onsite_gate_redeems_active_pass_once(): void
    {
        $user = User::factory()->create(['role' => UserRole::Visitor]);
        $visit = $this->visitFor($user);
        $campaign = Campaign::query()->findOrFail($visit->campaign_id);

        $party = GameParty::query()->create([
            'campaign_id' => $campaign->id,
            'visit_id' => $visit->id,
            'owner_user_id' => $user->id,
            'mode' => 'individual',
            'cycle_key' => 'launch-1405',
            'status' => 'ready_for_visit',
            'score' => 250,
        ]);
        $party->members()->create([
            'user_id' => $user->id,
            'display_name' => $user->name,
            'member_type' => 'registered',
            'role' => 'leader',
            'status' => 'active',
            'joined_at' => now(),
        ]);
        GameEntryPass::query()->create([
            'game_party_id' => $party->id,
            'issued_to_user_id' => $user->id,
            'code' => 'ECO-TEST1405',
            'token_hash' => hash('sha256', 'test-token'),
            'status' => 'active',
            'expires_at' => now()->addDay(),
        ]);

        $onsiteQr = QrCode::query()->create([
            'code' => 'onsite-gate-test',
            'venue_id' => $visit->venue_id,
            'touchpoint_id' => $visit->touchpoint_id,
            'campaign_id' => $visit->campaign_id,
            'destination_url' => url('/scan/onsite-gate-test'),
            'status' => 'active',
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDay(),
            'metadata' => ['online_game_role' => 'onsite_gate'],
        ]);
        $onsiteVisit = Visit::query()->create([
            'user_id' => $user->id,
            'qr_code_id' => $onsiteQr->id,
            'venue_id' => $visit->venue_id,
            'touchpoint_id' => $visit->touchpoint_id,
            'campaign_id' => $visit->campaign_id,
            'source' => 'qr_landing',
            'status' => 'confirmed',
            'occurred_at' => now(),
        ]);

        app(EcoParkOnlineGameService::class)
            ->redeemOnsiteVisit($user, $onsiteVisit->load('qrCode'));

        $this->assertDatabaseHas('game_entry_passes', [
            'game_party_id' => $party->id,
            'status' => 'redeemed',
        ]);
        $this->assertDatabaseHas('game_parties', [
            'id' => $party->id,
            'status' => 'completed',
            'score' => 400,
        ]);
    }

    public function test_rewarded_sponsor_content_is_optional_delayed_and_once_per_party(): void
    {
        $user = User::factory()->create(['role' => UserRole::Visitor]);
        $visit = $this->visitFor($user);

        $this->actingAs($user)->post(route('games.ecopark-treasure.parties.create'), [
            'visit_id' => $visit->id,
            'mode' => 'individual',
        ])->assertRedirect();

        $party = GameParty::query()->firstOrFail();
        $campaign = Campaign::query()->findOrFail($visit->campaign_id);
        $partner = PartnerAccount::query()->where('venue_id', $campaign->venue_id)->firstOrFail();
        $ad = AdRequest::query()->create([
            'venue_id' => $campaign->venue_id,
            'partner_account_id' => $partner->id,
            'code' => 'rewarded-test-ad',
            'title' => 'نکته همکاری',
            'body_copy' => 'محتوای کوتاه اختیاری',
            'advertiser_type' => 'sponsor',
            'ad_type' => 'rewarded_content',
            'status' => 'approved',
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDay(),
        ]);
        $ad->placements()->create([
            'placement_type' => 'post_mission',
            'status' => 'approved',
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDay(),
        ]);

        $this->actingAs($user)->post(route('games.ecopark-treasure.parties.sponsor-bonus.start', $party), [
            'ad_request_id' => $ad->id,
        ])->assertRedirect();
        $this->assertSame(20, $party->refresh()->score);

        $this->actingAs($user)->from(route('games.ecopark-treasure'))
            ->post(route('games.ecopark-treasure.parties.sponsor-bonus.complete', $party), [
                'ad_request_id' => $ad->id,
            ])
            ->assertSessionHasErrors('ad_request_id');

        $this->travel(11)->seconds();
        $this->actingAs($user)->post(route('games.ecopark-treasure.parties.sponsor-bonus.complete', $party), [
            'ad_request_id' => $ad->id,
        ])->assertRedirect();

        $this->assertSame(50, $party->refresh()->score);
        $this->assertDatabaseHas('game_bonus_claims', [
            'game_party_id' => $party->id,
            'status' => 'completed',
            'points_awarded' => 30,
        ]);
    }

    private function visitFor(User $user): Visit
    {
        $qr = QrCode::query()->where('code', PilotLocationSeeder::DEMO_QR_CODE)->firstOrFail();

        return Visit::query()->create([
            'user_id' => $user->id,
            'qr_code_id' => $qr->id,
            'venue_id' => $qr->venue_id,
            'touchpoint_id' => $qr->touchpoint_id,
            'campaign_id' => $qr->campaign_id,
            'source' => 'qr_landing',
            'status' => 'confirmed',
            'occurred_at' => now(),
            'metadata' => ['is_demo' => true],
        ]);
    }
}
