<?php

namespace Tests\Feature\Game;

use App\Enums\UserRole;
use App\Models\AdRequest;
use App\Models\Campaign;
use App\Models\ConsentVersion;
use App\Models\GameEntryPass;
use App\Models\GameParty;
use App\Models\PartnerAccount;
use App\Models\QrCode;
use App\Models\RewardDefinition;
use App\Models\RewardRedemption;
use App\Models\User;
use App\Models\UserReward;
use App\Models\Visit;
use App\Services\EcoParkOnlineGameService;
use Database\Seeders\ConsentVersionSeeder;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Inertia\Testing\AssertableInertia as Assert;
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

    public function test_group_stays_editable_until_route_and_family_can_reach_eight_members(): void
    {
        $leader = User::factory()->create(['role' => UserRole::Visitor]);
        $visit = $this->visitFor($leader);

        $this->actingAs($leader)->post(route('games.ecopark-treasure.parties.create'), [
            'visit_id' => $visit->id,
            'mode' => 'family',
            'name' => 'خانواده نخست',
            'companion_count' => 2,
        ])->assertRedirect();

        $party = GameParty::query()->firstOrFail();

        $this->actingAs($leader)->patch(
            route('games.ecopark-treasure.parties.update', $party),
            [
                'mode' => 'family',
                'name' => 'خانواده هشت‌نفره',
                'companion_count' => 7,
            ],
        )->assertRedirect();

        $party->refresh();
        $this->assertSame('خانواده هشت‌نفره', $party->name);
        $this->assertSame(
            8,
            $party->members()->where('status', 'active')->count(),
        );
        $serialized = app(EcoParkOnlineGameService::class)
            ->serializeParty($party, $leader);
        $this->assertFalse($serialized['isSetupLocked']);
        $this->assertSame('family', $serialized['recommendedRouteKey']);

        $this->actingAs($leader)->post(
            route('games.ecopark-treasure.parties.route', $party),
            ['route_key' => 'family'],
        )->assertRedirect();

        $this->actingAs($leader)
            ->from(route('games.ecopark-treasure'))
            ->patch(route('games.ecopark-treasure.parties.update', $party), [
                'mode' => 'family',
                'name' => 'تغییر دیرهنگام',
                'companion_count' => 3,
            ])
            ->assertSessionHasErrors('party');

        $this->assertSame('خانواده هشت‌نفره', $party->refresh()->name);
    }

    public function test_team_invitation_reaches_existing_or_new_account_and_membership_locks_after_route(): void
    {
        $leader = User::factory()->create([
            'role' => UserRole::Visitor,
            'mobile_hash' => hash('sha256', '09120000001'),
        ]);
        $existingMember = User::factory()->create([
            'role' => UserRole::Visitor,
            'mobile_hash' => hash('sha256', '09120000002'),
        ]);

        $this->actingAs($leader)->post(route('games.ecopark-treasure.parties.create'), [
            'visit_id' => $this->visitFor($leader)->id,
            'mode' => 'team',
            'name' => 'تیم دعوت هوشمند',
        ])->assertRedirect();
        $party = GameParty::query()->firstOrFail();

        $this->actingAs($leader)->post(
            route('games.ecopark-treasure.parties.invitations.store', $party),
            ['mobile' => '09120000002'],
        )->assertRedirect()->assertSessionHas(
            'success',
            'دعوت به پنل کاربر اکسپلوریا ارسال شد.',
        );

        $this->assertDatabaseHas('game_party_invitations', [
            'game_party_id' => $party->id,
            'invitee_user_id' => $existingMember->id,
            'status' => 'pending',
        ]);

        $this->actingAs($leader)
            ->from(route('games.ecopark-treasure'))
            ->post(route('games.ecopark-treasure.parties.route', $party), [
                'route_key' => 'explorer',
            ])
            ->assertSessionHasErrors('route_key');

        $this->withoutVite();
        $this->actingAs($existingMember)
            ->get(route('participant.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('pendingTeamInvitations', 1)
                ->where(
                    'pendingTeamInvitations.0.inviteCode',
                    $party->invite_code,
                ));

        $this->actingAs($existingMember)->post(
            route('games.ecopark-treasure.parties.join'),
            ['invite_code' => $party->invite_code],
        )->assertRedirect();
        $this->assertDatabaseHas('game_party_invitations', [
            'game_party_id' => $party->id,
            'invitee_user_id' => $existingMember->id,
            'status' => 'accepted',
        ]);

        $newMobile = '09120000003';
        $this->actingAs($leader)->post(
            route('games.ecopark-treasure.parties.invitations.store', $party),
            ['mobile' => $newMobile],
        )->assertRedirect()->assertSessionHas(
            'success',
            'دعوت عضویت آماده شد؛ لینک نمایش‌داده‌شده را برای این فرد بفرستید.',
        );
        $newMember = User::factory()->create([
            'role' => UserRole::Visitor,
            'mobile_hash' => hash('sha256', $newMobile),
        ]);

        $this->actingAs($newMember)
            ->get(route('participant.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('pendingTeamInvitations', 1)
                ->where(
                    'pendingTeamInvitations.0.inviteCode',
                    $party->invite_code,
                ));

        $this->actingAs($leader)->post(
            route('games.ecopark-treasure.parties.route', $party),
            ['route_key' => 'explorer'],
        )->assertRedirect();

        $this->actingAs($newMember)
            ->from(route('games.ecopark-treasure'))
            ->post(route('games.ecopark-treasure.parties.join'), [
                'invite_code' => $party->invite_code,
            ])
            ->assertSessionHasErrors('party');
        $this->assertDatabaseMissing('game_party_members', [
            'game_party_id' => $party->id,
            'user_id' => $newMember->id,
        ]);
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
                ->where('onlineGame.currentStage.index', 2)
                ->where('onlineGame.currentStage.title', 'انتخاب مسیر')
                ->where('onlineGame.currentStage.completedSteps', 1)
                ->has('onlineGame.journeyTimeline', 9)
                ->where('onlineGame.commerce.finalTier', 'base')
                ->where('missionFlow', null)
                ->where('journey.nextAction.label', 'ادامه: انتخاب مسیر')
                ->where('journey.currentOffer', null)
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
            'route_key' => 'quick',
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

        $this->withoutVite();
        $this->actingAs($user)
            ->get(route('scan.landing', ['code' => $onsiteQr->code]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('scan/landing')
                ->where('gamePhysicalScan.role', 'onsite_gate')
                ->where('gamePhysicalScan.isAuthenticated', true)
                ->where(
                    'gamePhysicalScan.confirmUrl',
                    route('games.ecopark-treasure.physical-scans.confirm', ['code' => $onsiteQr->code]),
                ));

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
            'status' => 'onsite_active',
            'score' => 400,
        ]);
        $this->assertDatabaseHas('game_challenge_progress', [
            'game_party_id' => $party->id,
            'step_index' => 6,
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('game_challenge_progress', [
            'game_party_id' => $party->id,
            'step_index' => 7,
            'status' => 'available',
        ]);

        $service = app(EcoParkOnlineGameService::class);
        $wrongVisit = $this->physicalVisit($user, $visit, 'mina');
        $partner = PartnerAccount::query()->where('venue_id', $campaign->venue_id)->firstOrFail();
        $checkpointReward = RewardDefinition::query()->create([
            'campaign_id' => $campaign->id,
            'venue_id' => $campaign->venue_id,
            'partner_account_id' => $partner->id,
            'code' => 'test-fire-water-reward',
            'name' => 'پاداش ایستگاه آب‌وآتش',
            'reward_type' => 'partner_coupon',
            'stock_quantity' => 20,
            'status' => 'active',
            'metadata' => [
                'game_auto_award' => true,
                'game_checkpoint_key' => 'fire-water',
            ],
        ]);
        $baseReward = RewardDefinition::query()->create([
            'campaign_id' => $campaign->id,
            'venue_id' => $campaign->venue_id,
            'partner_account_id' => $partner->id,
            'code' => 'test-final-base-reward',
            'name' => 'پاداش پایه پایان',
            'reward_type' => 'partner_coupon',
            'stock_quantity' => 20,
            'status' => 'active',
            'metadata' => [
                'game_auto_award' => true,
                'game_final_level' => 'base',
            ],
        ]);
        $boostedReward = RewardDefinition::query()->create([
            'campaign_id' => $campaign->id,
            'venue_id' => $campaign->venue_id,
            'partner_account_id' => $partner->id,
            'code' => 'test-final-boosted-reward',
            'name' => 'پاداش تقویت‌شده پایان',
            'reward_type' => 'sponsor_discount',
            'stock_quantity' => 20,
            'status' => 'active',
            'metadata' => [
                'game_auto_award' => true,
                'game_final_level' => 'boosted',
            ],
        ]);

        try {
            $service->redeemOnsiteVisit($user, $wrongVisit->load('qrCode'));
            self::fail('A checkpoint outside the selected route order must not be accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('qr_code', $exception->errors());
        }

        foreach (['fire-water', 'nature', 'ravaq-finish'] as $checkpoint) {
            $physicalVisit = $this->physicalVisit($user, $visit, $checkpoint);
            $service->redeemOnsiteVisit($user, $physicalVisit->load('qrCode'));

            if ($checkpoint === 'fire-water') {
                $issuedCheckpointReward = UserReward::query()
                    ->where('user_id', $user->id)
                    ->where('reward_definition_id', $checkpointReward->id)
                    ->firstOrFail();
                $redemption = RewardRedemption::query()
                    ->where('user_reward_id', $issuedCheckpointReward->id)
                    ->firstOrFail();
                $redemption->update([
                    'status' => 'confirmed',
                    'redeemed_at' => now(),
                ]);
            }
        }

        $this->assertDatabaseHas('game_parties', [
            'id' => $party->id,
            'status' => 'completed',
            'score' => 760,
        ]);
        $this->assertDatabaseHas('game_challenge_progress', [
            'game_party_id' => $party->id,
            'step_index' => 9,
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('user_rewards', [
            'user_id' => $user->id,
            'reward_definition_id' => $baseReward->id,
        ]);
        $this->assertDatabaseHas('user_rewards', [
            'user_id' => $user->id,
            'reward_definition_id' => $boostedReward->id,
        ]);

        $serialized = $service->serializeParty($party->refresh(), $user);
        $this->assertSame('boosted', $serialized['commerce']['finalTier']);
        $this->assertSame(3, $serialized['commerce']['issuedStageRewards']);
    }

    public function test_expired_pass_can_be_renewed_without_duplicate_points(): void
    {
        $this->withoutVite();
        $user = User::factory()->create(['role' => UserRole::Visitor]);
        $visit = $this->visitFor($user);
        $party = $this->readyForVisitParty($user, $visit);
        $pass = GameEntryPass::query()->create([
            'game_party_id' => $party->id,
            'issued_to_user_id' => $user->id,
            'code' => 'ECO-EXPIRED1',
            'token_hash' => hash('sha256', 'expired-token'),
            'status' => 'active',
            'expires_at' => now()->subMinute(),
        ]);

        $serialized = app(EcoParkOnlineGameService::class)
            ->serializeParty($party->refresh(), $user);

        $this->assertSame('expired', $serialized['entryPass']['status']);
        $this->assertTrue($serialized['entryPass']['isRenewable']);
        $this->assertSame('تمدید مجوز حضور', $serialized['currentStage']['title']);

        $scoreBeforeRenewal = $party->score;
        $this->actingAs($user)
            ->post(route('games.ecopark-treasure.parties.pass.renew', $party))
            ->assertRedirect()
            ->assertSessionHas('success');

        $pass->refresh();
        $this->assertSame('active', $pass->status);
        $this->assertNotSame('ECO-EXPIRED1', $pass->code);
        $this->assertTrue($pass->expires_at->isAfter(now()->addDays(6)));
        $this->assertSame(1, $pass->metadata['renewal_count']);
        $this->assertSame($scoreBeforeRenewal, $party->refresh()->score);
        $this->assertDatabaseHas('game_challenge_progress', [
            'game_party_id' => $party->id,
            'step_index' => 5,
            'status' => 'completed',
            'points_awarded' => 120,
        ]);

        $this->actingAs($user)
            ->from(route('games.ecopark-treasure'))
            ->post(route('games.ecopark-treasure.parties.pass.renew', $party))
            ->assertSessionHasErrors('pass');

        $pass->update(['expires_at' => now()->subMinute()]);
        $party->update(['cycle_key' => 'previous-cycle']);

        $this->actingAs($user)
            ->from(route('games.ecopark-treasure'))
            ->post(route('games.ecopark-treasure.parties.pass.renew', $party))
            ->assertSessionHasErrors('pass');
    }

    public function test_scan_landing_preflight_hides_confirmation_for_expired_and_wrong_qr(): void
    {
        $this->withoutVite();
        $user = User::factory()->create(['role' => UserRole::Visitor]);
        $visit = $this->visitFor($user);
        $party = $this->readyForVisitParty($user, $visit);
        $campaign = Campaign::query()->findOrFail($visit->campaign_id);
        $pass = GameEntryPass::query()->create([
            'game_party_id' => $party->id,
            'issued_to_user_id' => $user->id,
            'code' => 'ECO-PREFLIGHT',
            'token_hash' => hash('sha256', 'preflight-token'),
            'status' => 'active',
            'expires_at' => now()->addDay(),
        ]);
        $gate = $this->physicalQr($visit, 'preflight-gate', 'onsite_gate');

        $this->actingAs($user)
            ->get(route('scan.landing', ['code' => $gate->code]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('gamePhysicalScan.scanState.status', 'ready')
                ->where('gamePhysicalScan.scanState.canConfirm', true)
                ->where(
                    'gamePhysicalScan.confirmUrl',
                    route('games.ecopark-treasure.physical-scans.confirm', ['code' => $gate->code]),
                ));

        $pass->update(['expires_at' => now()->subSecond()]);

        $this->actingAs($user)
            ->get(route('scan.landing', ['code' => $gate->code]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('gamePhysicalScan.scanState.status', 'expired')
                ->where('gamePhysicalScan.scanState.canConfirm', false)
                ->where('gamePhysicalScan.confirmUrl', null));

        $this->seed(ConsentVersionSeeder::class);
        $consentVersion = ConsentVersion::query()->where('is_active', true)->firstOrFail();
        $this->actingAs($user)
            ->postJson(route('consents.accept'), [
                'consentVersionId' => $consentVersion->id,
                'source' => 'pwa',
            ])
            ->assertCreated();
        $this->actingAs($user)
            ->from(route('scan.landing', ['code' => $gate->code]))
            ->post(route('games.ecopark-treasure.physical-scans.confirm', ['code' => $gate->code]))
            ->assertRedirect(route('scan.landing', ['code' => $gate->code]))
            ->assertSessionHasErrors('qr_code');
        $this->assertDatabaseMissing('visits', [
            'user_id' => $user->id,
            'qr_code_id' => $gate->id,
        ]);
        $this->assertDatabaseMissing('scan_events', [
            'user_id' => $user->id,
            'qr_code_id' => $gate->id,
        ]);

        $pass->update(['expires_at' => now()->addDay()]);
        $gateVisit = Visit::query()->create([
            'user_id' => $user->id,
            'qr_code_id' => $gate->id,
            'venue_id' => $visit->venue_id,
            'touchpoint_id' => $visit->touchpoint_id,
            'campaign_id' => $campaign->id,
            'source' => 'qr_landing',
            'status' => 'confirmed',
            'occurred_at' => now(),
        ]);
        app(EcoParkOnlineGameService::class)
            ->redeemOnsiteVisit($user, $gateVisit->load('qrCode'));
        $wrongCheckpoint = $this->physicalQr(
            $visit,
            'preflight-wrong-checkpoint',
            'physical_checkpoint',
            'mina',
        );

        $this->actingAs($user)
            ->get(route('scan.landing', ['code' => $wrongCheckpoint->code]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('gamePhysicalScan.scanState.status', 'wrong_checkpoint')
                ->where('gamePhysicalScan.scanState.expectedCheckpointKey', 'fire-water')
                ->where('gamePhysicalScan.scanState.canConfirm', false)
                ->where('gamePhysicalScan.confirmUrl', null));

        $this->actingAs($user)
            ->from(route('scan.landing', ['code' => $wrongCheckpoint->code]))
            ->post(route('games.ecopark-treasure.physical-scans.confirm', ['code' => $wrongCheckpoint->code]))
            ->assertRedirect(route('scan.landing', ['code' => $wrongCheckpoint->code]))
            ->assertSessionHasErrors('qr_code');
        $this->assertDatabaseMissing('visits', [
            'user_id' => $user->id,
            'qr_code_id' => $wrongCheckpoint->id,
        ]);
        $this->assertDatabaseMissing('scan_events', [
            'user_id' => $user->id,
            'qr_code_id' => $wrongCheckpoint->id,
        ]);
    }

    public function test_confirming_a_ready_gate_through_http_redeems_the_pass_and_opens_the_first_checkpoint(): void
    {
        $this->seed(ConsentVersionSeeder::class);
        $user = User::factory()->create(['role' => UserRole::Visitor]);
        $visit = $this->visitFor($user);
        $party = $this->readyForVisitParty($user, $visit);
        GameEntryPass::query()->create([
            'game_party_id' => $party->id,
            'issued_to_user_id' => $user->id,
            'code' => 'ECO-HTTP-GATE',
            'token_hash' => hash('sha256', 'http-gate-token'),
            'status' => 'active',
            'expires_at' => now()->addDay(),
        ]);
        $gate = $this->physicalQr($visit, 'http-onsite-gate', 'onsite_gate');
        $consentVersion = ConsentVersion::query()->where('is_active', true)->firstOrFail();

        $this->actingAs($user)
            ->postJson(route('consents.accept'), [
                'consentVersionId' => $consentVersion->id,
                'source' => 'pwa',
            ])
            ->assertCreated();

        $response = $this->actingAs($user)
            ->post(route('games.ecopark-treasure.physical-scans.confirm', ['code' => $gate->code]));
        $physicalVisit = Visit::query()
            ->where('user_id', $user->id)
            ->where('qr_code_id', $gate->id)
            ->firstOrFail();

        $response
            ->assertRedirect(route('games.ecopark-treasure', ['visit' => $physicalVisit->id]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('game_entry_passes', [
            'game_party_id' => $party->id,
            'status' => 'redeemed',
        ]);
        $this->assertDatabaseHas('game_parties', [
            'id' => $party->id,
            'status' => 'onsite_active',
            'score' => 400,
        ]);
        $this->assertDatabaseHas('game_challenge_progress', [
            'game_party_id' => $party->id,
            'step_index' => 6,
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('game_challenge_progress', [
            'game_party_id' => $party->id,
            'step_index' => 7,
            'status' => 'available',
        ]);
        $this->assertDatabaseHas('scan_events', [
            'qr_code_id' => $gate->id,
            'user_id' => $user->id,
            'result' => 'accepted',
        ]);
    }

    public function test_physical_qr_rejects_previous_cycle_removed_member_and_unknown_checkpoint(): void
    {
        $user = User::factory()->create(['role' => UserRole::Visitor]);
        $visit = $this->visitFor($user);
        $campaign = Campaign::query()->findOrFail($visit->campaign_id);
        $party = $this->readyForVisitParty($user, $visit, 'previous-cycle');
        GameEntryPass::query()->create([
            'game_party_id' => $party->id,
            'issued_to_user_id' => $user->id,
            'code' => 'ECO-OLD-CYCLE',
            'token_hash' => hash('sha256', 'old-cycle-token'),
            'status' => 'active',
            'expires_at' => now()->addDay(),
        ]);
        $gate = $this->physicalQr($visit, 'old-cycle-gate', 'onsite_gate');
        $service = app(EcoParkOnlineGameService::class);

        $this->assertSame('blocked', $service->physicalScanState($user, $gate)['status']);

        $party->update(['cycle_key' => $service->cycleKey($campaign)]);
        $this->assertSame('ready', $service->physicalScanState($user, $gate)['status']);

        $party->members()->where('user_id', $user->id)->update(['status' => 'removed']);
        $this->assertSame('blocked', $service->physicalScanState($user, $gate)['status']);

        $party->members()->where('user_id', $user->id)->update(['status' => 'active']);
        $unknown = $this->physicalQr(
            $visit,
            'unknown-checkpoint',
            'physical_checkpoint',
            'unknown-place',
        );
        $this->assertSame('invalid', $service->physicalScanState($user, $unknown)['status']);
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
            'metadata' => [
                'rewarded_points' => 47,
                'required_seconds' => 12,
                'game_stage_index' => 2,
                'commercial_model' => 'paid_stage_placement',
            ],
        ]);
        $ad->placements()->create([
            'placement_type' => 'post_mission',
            'status' => 'approved',
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDay(),
        ]);

        $wrongStageAd = AdRequest::query()->create([
            'venue_id' => $campaign->venue_id,
            'partner_account_id' => $partner->id,
            'code' => 'wrong-stage-rewarded-test-ad',
            'title' => 'پیشنهاد مرحله دیگر',
            'advertiser_type' => 'sponsor',
            'ad_type' => 'rewarded_content',
            'status' => 'approved',
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDay(),
            'metadata' => ['game_stage_index' => 5],
        ]);
        $wrongStageAd->placements()->create([
            'placement_type' => 'post_mission',
            'status' => 'approved',
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDay(),
        ]);

        $this->actingAs($user)->from(route('games.ecopark-treasure'))
            ->post(route('games.ecopark-treasure.parties.sponsor-bonus.start', $party), [
                'ad_request_id' => $wrongStageAd->id,
            ])
            ->assertSessionHasErrors('ad_request_id');

        $this->actingAs($user)->post(route('games.ecopark-treasure.parties.sponsor-bonus.start', $party), [
            'ad_request_id' => $ad->id,
        ])->assertRedirect();
        $this->assertSame(20, $party->refresh()->score);

        $this->actingAs($user)->from(route('games.ecopark-treasure'))
            ->post(route('games.ecopark-treasure.parties.sponsor-bonus.complete', $party), [
                'ad_request_id' => $ad->id,
            ])
            ->assertSessionHasErrors('ad_request_id');

        $this->travel(13)->seconds();
        $this->actingAs($user)->post(route('games.ecopark-treasure.parties.sponsor-bonus.complete', $party), [
            'ad_request_id' => $ad->id,
        ])->assertRedirect();

        $this->assertSame(67, $party->refresh()->score);
        $this->assertDatabaseHas('game_bonus_claims', [
            'game_party_id' => $party->id,
            'status' => 'completed',
            'points_awarded' => 47,
        ]);
    }

    public function test_map_and_cipher_steps_receive_an_approved_commercial_discovery_clue(): void
    {
        $this->withoutVite();
        $user = User::factory()->create(['role' => UserRole::Visitor]);
        $visit = $this->visitFor($user);

        $this->actingAs($user)->post(route('games.ecopark-treasure.parties.create'), [
            'visit_id' => $visit->id,
            'mode' => 'individual',
        ])->assertRedirect();

        $party = GameParty::query()->firstOrFail();
        $this->actingAs($user)->post(route('games.ecopark-treasure.parties.route', $party), [
            'route_key' => 'quick',
        ])->assertRedirect();

        $campaign = Campaign::query()->findOrFail($visit->campaign_id);
        $partner = PartnerAccount::query()->where('venue_id', $campaign->venue_id)->firstOrFail();
        $ad = AdRequest::query()->create([
            'venue_id' => $campaign->venue_id,
            'partner_account_id' => $partner->id,
            'code' => 'map-commercial-discovery',
            'title' => 'سبد ویژه رواق',
            'body_copy' => 'معرفی فروشگاه و پاداش ویژه مسیر اکوپارک.',
            'advertiser_type' => 'sponsor',
            'ad_type' => 'rewarded_content',
            'status' => 'approved',
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDay(),
            'metadata' => [
                'rewarded_points' => 55,
                'required_seconds' => 10,
                'game_stage_index' => 3,
            ],
        ]);
        $ad->creatives()->create([
            'creative_type' => 'text_card',
            'headline' => 'سبد ویژه رواق',
            'body_copy' => 'معرفی فروشگاه و پاداش ویژه مسیر اکوپارک.',
            'cta_text' => 'مشاهده معرفی',
            'status' => 'approved',
        ]);
        $ad->placements()->create([
            'placement_type' => 'map_route',
            'status' => 'approved',
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDay(),
            'priority' => 1,
        ]);
        RewardDefinition::query()->create([
            'campaign_id' => $campaign->id,
            'venue_id' => $campaign->venue_id,
            'partner_account_id' => $partner->id,
            'code' => 'premium-commercial-discovery',
            'name' => 'جایزه ممتاز رواق',
            'reward_type' => 'sponsor_discount',
            'point_cost' => 900,
            'stock_quantity' => 20,
            'status' => 'active',
            'metadata' => [
                'description' => 'پاداش سطح بالا برای معرفی واحد تجاری عضو.',
                'reward_tier' => 'premium',
            ],
        ]);

        $this->actingAs($user)
            ->get(route('games.ecopark-treasure'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('game.commercialDiscovery.sourceKind', 'rewarded_ad')
                ->where('game.commercialDiscovery.partnerName', $partner->name)
                ->where('game.commercialDiscovery.stageIndex', 3)
                ->where('game.commercialDiscovery.bonusPoints', 55));

        foreach (['mina', 'nature', 'fire-water'] as $hotspot) {
            $this->actingAs($user)->post(route('games.ecopark-treasure.parties.hotspots', $party), [
                'hotspot_key' => $hotspot,
            ])->assertRedirect();
        }

        $this->actingAs($user)
            ->get(route('games.ecopark-treasure'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('game.commercialDiscovery.sourceKind', 'high_value_reward')
                ->where('game.commercialDiscovery.stageIndex', 4));
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

    private function physicalVisit(User $user, Visit $origin, string $checkpoint): Visit
    {
        $qr = QrCode::query()->create([
            'code' => 'physical-'.$checkpoint.'-'.strtolower((string) str()->uuid()),
            'venue_id' => $origin->venue_id,
            'touchpoint_id' => $origin->touchpoint_id,
            'campaign_id' => $origin->campaign_id,
            'destination_url' => url('/scan/physical-'.$checkpoint),
            'status' => 'active',
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDay(),
            'metadata' => [
                'online_game_role' => 'physical_checkpoint',
                'checkpoint_key' => $checkpoint,
            ],
        ]);

        return Visit::query()->create([
            'user_id' => $user->id,
            'qr_code_id' => $qr->id,
            'venue_id' => $origin->venue_id,
            'touchpoint_id' => $origin->touchpoint_id,
            'campaign_id' => $origin->campaign_id,
            'source' => 'qr_landing',
            'status' => 'confirmed',
            'occurred_at' => now(),
        ]);
    }

    private function readyForVisitParty(
        User $user,
        Visit $visit,
        ?string $cycleKey = null,
    ): GameParty {
        $campaign = Campaign::query()->findOrFail($visit->campaign_id);
        $cycleKey ??= app(EcoParkOnlineGameService::class)->cycleKey($campaign);

        $party = GameParty::query()->create([
            'campaign_id' => $visit->campaign_id,
            'visit_id' => $visit->id,
            'owner_user_id' => $user->id,
            'mode' => 'individual',
            'route_key' => 'quick',
            'cycle_key' => $cycleKey,
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

        foreach (range(1, 5) as $step) {
            $party->progress()->create([
                'step_index' => $step,
                'status' => 'completed',
                'points_awarded' => [1 => 20, 2 => 30, 3 => 60, 4 => 80, 5 => 120][$step],
                'completed_at' => now(),
            ]);
        }

        return $party;
    }

    private function physicalQr(
        Visit $origin,
        string $code,
        string $role,
        ?string $checkpoint = null,
    ): QrCode {
        return QrCode::query()->create([
            'code' => $code,
            'venue_id' => $origin->venue_id,
            'touchpoint_id' => $origin->touchpoint_id,
            'campaign_id' => $origin->campaign_id,
            'destination_url' => url('/scan/'.$code),
            'status' => 'active',
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDay(),
            'metadata' => array_filter([
                'online_game_role' => $role,
                'checkpoint_key' => $checkpoint,
            ]),
        ]);
    }
}
