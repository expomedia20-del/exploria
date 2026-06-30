<?php

namespace Tests\Feature\Campaign;

use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\CampaignParticipant;
use App\Models\MissionInstance;
use App\Models\MissionTemplate;
use App\Models\QrCode;
use App\Models\RewardDefinition;
use App\Models\Touchpoint;
use App\Models\Treasure;
use App\Models\User;
use App\Models\Venue;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CampaignQrCoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PilotLocationSeeder::class);
    }

    public function test_viewer_can_open_campaign_registry_page(): void
    {
        $this->withoutVite();
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);

        $this->actingAs($viewer)
            ->get(route('admin.campaigns.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/campaigns/index')
                ->has('campaigns', 1)
                ->has('venueOptions', 3)
                ->where('campaigns.0.code', 'ecopark-pilot-1405'));
    }

    public function test_operator_can_create_campaign(): void
    {
        $operator = User::factory()->create(['role' => UserRole::Operator]);
        $venue = Venue::query()->where('code', 'ecopark-abbasabad')->firstOrFail();

        $this->actingAs($operator)
            ->post(route('admin.campaigns.store'), [
                'venue_id' => $venue->id,
                'code' => 'family-route-1405',
                'name' => 'مسیر خانوادگی ۱۴۰۵',
                'campaign_type' => 'family_route',
                'blueprint_code' => 'family-route',
                'status' => RecordStatus::Draft->value,
                'start_at' => '2026-07-01 09:00:00',
                'end_at' => '2026-08-01 22:00:00',
            ])
            ->assertRedirect(route('admin.campaign-builder.page', [
                'campaign' => 'family-route-1405',
                'blueprint' => 'family-route',
                'blueprint_action' => 'build',
            ]));

        $this->assertDatabaseHas('campaigns', [
            'venue_id' => $venue->id,
            'code' => 'family-route-1405',
            'campaign_type' => 'family_route',
            'status' => 'draft',
        ]);
    }

    public function test_operator_can_update_and_delete_empty_campaign(): void
    {
        $operator = User::factory()->create(['role' => UserRole::Operator]);
        $venue = Venue::query()->where('code', 'ecopark-abbasabad')->firstOrFail();
        $campaign = Campaign::query()->create([
            'venue_id' => $venue->id,
            'code' => 'editable-empty-campaign',
            'name' => 'Editable empty campaign',
            'campaign_type' => 'pilot_visit',
            'status' => RecordStatus::Draft,
        ]);

        $this->actingAs($operator)
            ->from(route('admin.campaigns.page'))
            ->post(route('admin.campaigns.store'), [
                'campaign_id' => $campaign->id,
                'venue_id' => $venue->id,
                'code' => 'editable-empty-campaign',
                'name' => 'Updated empty campaign',
                'campaign_type' => 'pilot_visit',
                'status' => RecordStatus::Inactive->value,
            ])
            ->assertRedirect(route('admin.campaigns.page'));

        $this->assertDatabaseHas('campaigns', [
            'id' => $campaign->id,
            'name' => 'Updated empty campaign',
            'status' => 'inactive',
        ]);

        $this->actingAs($operator)
            ->from(route('admin.campaigns.page'))
            ->delete(route('admin.campaigns.destroy', ['campaign' => $campaign->id]))
            ->assertRedirect(route('admin.campaigns.page'));

        $this->assertDatabaseMissing('campaigns', ['id' => $campaign->id]);
    }

    public function test_viewer_can_open_campaign_builder_page(): void
    {
        $this->withoutVite();
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);

        $this->actingAs($viewer)
            ->get(route('admin.campaign-builder.page', ['campaign' => 'ecopark-pilot-1405']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/campaign-builder/index')
                ->where('selectedCampaign.code', 'ecopark-pilot-1405')
                ->has('steps', 6)
                ->has('roleTracks', 4));
    }

    public function test_viewer_cannot_create_campaign_qr_or_components(): void
    {
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);

        $this->actingAs($viewer)
            ->post(route('admin.campaigns.store'), [])
            ->assertForbidden();

        $this->actingAs($viewer)
            ->post(route('admin.qr-codes.store'), [])
            ->assertForbidden();

        $this->actingAs($viewer)
            ->post(route('admin.missions.store'), [])
            ->assertForbidden();

        $this->actingAs($viewer)
            ->post(route('admin.rewards.store'), [])
            ->assertForbidden();

        $this->actingAs($viewer)
            ->post(route('admin.treasures.store'), [])
            ->assertForbidden();

        $this->actingAs($viewer)
            ->post(route('admin.campaign-participants.store'), [])
            ->assertForbidden();

        $this->actingAs($viewer)
            ->post(route('admin.campaign-operations.review'), [])
            ->assertForbidden();

        $this->actingAs($viewer)
            ->post(route('admin.campaign-builder.activate', ['campaign' => 'ecopark-pilot-1405']))
            ->assertForbidden();
    }

    public function test_operator_can_create_campaign_components(): void
    {
        $operator = User::factory()->create(['role' => UserRole::Operator]);
        $campaign = Campaign::query()->where('code', 'ecopark-pilot-1405')->firstOrFail();
        $template = MissionTemplate::query()->where('status', RecordStatus::Active)->firstOrFail();

        $this->actingAs($operator)
            ->get(route('admin.missions.page', ['campaign' => $campaign->code]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('selectedBlueprint.missionPlan')
                ->has('selectedBlueprint.rewardDesign.tiers')
                ->where('formOptions.missionTemplates.0.recommended', true)
                ->has('formOptions.missionTemplates.0.recommendationReason'));

        $this->actingAs($operator)
            ->from(route('admin.missions.page', ['campaign' => $campaign->code]))
            ->post(route('admin.missions.store'), [
                'campaign_id' => $campaign->id,
                'mission_template_id' => $template->id,
                'code' => 'builder-first-mission',
                'cycle_step_index' => 1,
                'cycle_step_label' => 'builder cycle step',
                'title_override' => 'ماموریت تست کارگاه',
                'status' => RecordStatus::Draft->value,
                'unlock_min_points' => 100,
            ])
            ->assertRedirect(route('admin.missions.page', ['campaign' => $campaign->code]));

        $mission = MissionInstance::query()
            ->where('campaign_id', $campaign->id)
            ->where('code', 'builder-first-mission')
            ->firstOrFail();

        $this->assertSame(1, $mission->metadata['cycle_step_index']);
        $this->assertSame('builder cycle step', $mission->metadata['cycle_step_label']);

        $this->actingAs($operator)
            ->from(route('admin.missions.page', ['campaign' => $campaign->code]))
            ->post(route('admin.missions.store'), [
                'campaign_id' => $campaign->id,
                'mission_template_id' => $template->id,
                'code' => 'builder-first-mission',
                'cycle_step_index' => 1,
                'cycle_step_label' => 'builder cycle step',
                'title_override' => 'builder mission replacement',
                'status' => RecordStatus::Draft->value,
                'unlock_min_points' => 120,
            ])
            ->assertRedirect(route('admin.missions.page', ['campaign' => $campaign->code]));

        $mission = MissionInstance::query()
            ->where('campaign_id', $campaign->id)
            ->where('code', 'builder-first-mission')
            ->firstOrFail();

        $this->assertSame('builder mission replacement', $mission->title_override);
        $this->assertSame(1, MissionInstance::query()->where('campaign_id', $campaign->id)->where('metadata->cycle_step_index', 1)->count());

        $this->actingAs($operator)
            ->from(route('admin.missions.page', ['campaign' => $campaign->code]))
            ->post(route('admin.rewards.store'), [
                'campaign_id' => $campaign->id,
                'code' => 'builder-test-reward',
                'name' => 'پاداش تست کارگاه',
                'reward_type' => 'badge',
                'reward_tier' => 'bronze',
                'reward_option' => 'نشان شروع مسیر',
                'cycle_step_index' => 1,
                'cycle_step_label' => 'builder cycle step',
                'point_cost' => 100,
                'stock_quantity' => 50,
                'status' => RecordStatus::Draft->value,
                'available_from' => '2026-07-01 09:00:00',
                'available_until' => '2026-07-10 22:00:00',
                'fulfillment_window' => 'within 48 hours',
                'description' => 'پاداش ساخته شده در مرحله سه',
            ])
            ->assertRedirect(route('admin.missions.page', ['campaign' => $campaign->code]));

        $this->actingAs($operator)
            ->from(route('admin.missions.page', ['campaign' => $campaign->code]))
            ->post(route('admin.rewards.store'), [
                'campaign_id' => $campaign->id,
                'code' => 'builder-test-reward',
                'name' => 'builder reward replacement',
                'reward_type' => 'badge',
                'reward_tier' => 'bronze',
                'reward_option' => 'نشان شروع مسیر',
                'cycle_step_index' => 1,
                'cycle_step_label' => 'builder cycle step',
                'point_cost' => 120,
                'stock_quantity' => 40,
                'status' => RecordStatus::Draft->value,
                'available_from' => '2026-07-02 09:00:00',
                'available_until' => '2026-07-11 22:00:00',
                'fulfillment_window' => 'same day',
                'description' => 'replacement reward for stage three',
            ])
            ->assertRedirect(route('admin.missions.page', ['campaign' => $campaign->code]));

        $this->actingAs($operator)
            ->from(route('admin.missions.page', ['campaign' => $campaign->code]))
            ->post(route('admin.treasures.store'), [
                'campaign_id' => $campaign->id,
                'code' => 'builder-test-treasure',
                'name' => 'گنج تست کارگاه',
                'treasure_type' => 'final_treasure',
                'treasure_tier' => 'bronze',
                'cycle_step_index' => 1,
                'cycle_step_label' => 'builder cycle step',
                'reveal_mode' => 'after_step_completion',
                'reveal_description' => 'builder treasure reveal',
                'discovery_hint' => 'follow the first mission',
                'status' => RecordStatus::Draft->value,
                'required_completed_missions' => 1,
                'required_min_points' => 120,
            ])
            ->assertRedirect(route('admin.missions.page', ['campaign' => $campaign->code]));

        $this->assertDatabaseHas('mission_instances', [
            'campaign_id' => $campaign->id,
            'code' => 'builder-first-mission',
            'title_override' => 'builder mission replacement',
        ]);

        $this->assertDatabaseHas('reward_definitions', [
            'campaign_id' => $campaign->id,
            'code' => 'builder-test-reward',
            'name' => 'builder reward replacement',
        ]);

        $this->assertDatabaseHas('treasures', [
            'campaign_id' => $campaign->id,
            'mission_instance_id' => $mission->id,
            'code' => 'builder-test-treasure',
        ]);

        $reward = RewardDefinition::query()->where('code', 'builder-test-reward')->firstOrFail();

        $this->assertSame('bronze', $reward->metadata['reward_tier']);
        $this->assertSame('نشان شروع مسیر', $reward->metadata['reward_option']);
        $this->assertSame(1, $reward->metadata['cycle_step_index']);
        $this->assertSame('2026-07-02 09:00:00', $reward->metadata['available_from']);
        $this->assertSame('same day', $reward->metadata['fulfillment_window']);
        $this->assertSame(1, RewardDefinition::query()->where('code', 'builder-test-reward')->count());
        $this->assertSame(1, RewardDefinition::query()->where('campaign_id', $campaign->id)->where('metadata->cycle_step_index', 1)->count());
        $this->assertSame(1, Treasure::query()->where('code', 'builder-test-treasure')->count());

        $treasure = Treasure::query()->where('code', 'builder-test-treasure')->firstOrFail();
        $this->assertSame('bronze', $treasure->metadata['treasure_tier']);
        $this->assertSame(1, $treasure->metadata['cycle_step_index']);
        $this->assertSame('after_step_completion', $treasure->reveal_rule['reveal_mode']);
    }

    public function test_blueprint_campaign_rejects_mismatched_stage_three_components(): void
    {
        $operator = User::factory()->create(['role' => UserRole::Operator]);
        $campaign = Campaign::query()->where('code', 'ecopark-pilot-1405')->firstOrFail();
        $wrongTemplate = MissionTemplate::query()->where('code', 'discover-route-guide')->firstOrFail();
        $rightTemplate = MissionTemplate::query()->where('code', 'scan-entry-qr')->firstOrFail();

        $this->actingAs($operator)
            ->from(route('admin.missions.page', ['campaign' => $campaign->code]))
            ->post(route('admin.missions.store'), [
                'campaign_id' => $campaign->id,
                'mission_template_id' => $wrongTemplate->id,
                'code' => 'bad-template-step-one',
                'cycle_step_index' => 1,
                'cycle_step_label' => 'گام اول',
                'status' => RecordStatus::Draft->value,
            ])
            ->assertSessionHasErrors('mission_template_id');

        $this->actingAs($operator)
            ->post(route('admin.missions.store'), [
                'campaign_id' => $campaign->id,
                'mission_template_id' => $rightTemplate->id,
                'code' => 'valid-template-step-one',
                'cycle_step_index' => 1,
                'cycle_step_label' => 'گام اول',
                'status' => RecordStatus::Draft->value,
            ])
            ->assertRedirect();

        $this->actingAs($operator)
            ->from(route('admin.missions.page', ['campaign' => $campaign->code]))
            ->post(route('admin.rewards.store'), [
                'campaign_id' => $campaign->id,
                'code' => 'bad-tier-step-one',
                'name' => 'پاداش ناسازگار',
                'reward_type' => 'badge',
                'reward_tier' => 'gold',
                'reward_option' => 'سبد ترکیبی رواق + غذا + تجربه',
                'cycle_step_index' => 1,
                'cycle_step_label' => 'گام اول',
                'status' => RecordStatus::Draft->value,
            ])
            ->assertSessionHasErrors('reward_tier');

        $this->assertDatabaseMissing('mission_instances', ['code' => 'bad-template-step-one']);
        $this->assertDatabaseMissing('reward_definitions', ['code' => 'bad-tier-step-one']);
    }

    public function test_operator_can_delete_draft_campaign_components(): void
    {
        $operator = User::factory()->create(['role' => UserRole::Operator]);
        $campaign = Campaign::query()->where('code', 'ecopark-pilot-1405')->firstOrFail();
        $template = MissionTemplate::query()->where('status', RecordStatus::Active)->firstOrFail();

        $this->actingAs($operator)
            ->post(route('admin.missions.store'), [
                'campaign_id' => $campaign->id,
                'mission_template_id' => $template->id,
                'code' => 'delete-test-mission',
                'cycle_step_index' => 2,
                'cycle_step_label' => 'delete cycle step',
                'title_override' => 'delete test mission',
                'status' => RecordStatus::Draft->value,
            ])
            ->assertRedirect();

        $mission = MissionInstance::query()->where('code', 'delete-test-mission')->firstOrFail();

        $this->actingAs($operator)
            ->post(route('admin.rewards.store'), [
                'campaign_id' => $campaign->id,
                'code' => 'delete-test-reward',
                'name' => 'delete test reward',
                'reward_type' => 'badge',
                'reward_tier' => 'bronze',
                'cycle_step_index' => 2,
                'cycle_step_label' => 'delete cycle step',
                'status' => RecordStatus::Draft->value,
            ])
            ->assertRedirect();

        $reward = RewardDefinition::query()->where('code', 'delete-test-reward')->firstOrFail();

        $this->actingAs($operator)
            ->delete(route('admin.rewards.destroy', ['reward' => $reward->id]))
            ->assertRedirect();

        $this->actingAs($operator)
            ->delete(route('admin.missions.destroy', ['mission' => $mission->id]))
            ->assertRedirect();

        $this->assertDatabaseMissing('reward_definitions', ['id' => $reward->id]);
        $this->assertDatabaseMissing('mission_instances', ['id' => $mission->id]);
    }

    public function test_operator_can_complete_campaign_participants_route_and_launch_review(): void
    {
        $operator = User::factory()->create(['role' => UserRole::Operator]);
        $campaign = Campaign::query()->where('code', 'ecopark-pilot-1405')->firstOrFail();
        $campaign->update(['status' => RecordStatus::Draft, 'metadata' => ['blueprint_code' => 'family-route']]);

        $this->actingAs($operator)
            ->from(route('admin.campaign-participants.page', ['campaign' => $campaign->code]))
            ->post(route('admin.campaign-participants.store'), [
                'campaign_id' => $campaign->id,
                'participant_type' => 'member_shop',
                'participation_role' => 'reward_redemption',
                'status' => RecordStatus::Active->value,
                'onboarding_status' => 'ready',
                'connections_rewards' => 1,
                'connections_missions' => 1,
                'connections_qr_codes' => 1,
            ])
            ->assertRedirect(route('admin.campaign-participants.page', ['campaign' => $campaign->code]));

        $this->assertDatabaseHas('campaign_participants', [
            'campaign_id' => $campaign->id,
            'participant_type' => 'member_shop',
            'participation_role' => 'reward_redemption',
            'onboarding_status' => 'ready',
        ]);

        $this->actingAs($operator)
            ->from(route('admin.campaign-operations.page', ['campaign' => $campaign->code]))
            ->post(route('admin.campaign-operations.review'), [
                'campaign_id' => $campaign->id,
                'route_notes' => 'مسیر تست کارگاه بررسی شد.',
            ])
            ->assertRedirect(route('admin.campaign-operations.page', ['campaign' => $campaign->code]));

        $campaign->refresh();
        $this->assertNotEmpty($campaign->metadata['route_reviewed_at'] ?? null);

        $this->actingAs($operator)
            ->from(route('admin.campaign-operations.page', ['campaign' => $campaign->code]))
            ->delete(route('admin.campaign-operations.review.destroy'), [
                'campaign_id' => $campaign->id,
            ])
            ->assertRedirect(route('admin.campaign-operations.page', ['campaign' => $campaign->code]));

        $campaign->refresh();
        $this->assertEmpty($campaign->metadata['route_reviewed_at'] ?? null);

        $this->actingAs($operator)
            ->from(route('admin.campaign-operations.page', ['campaign' => $campaign->code]))
            ->post(route('admin.campaign-operations.review'), [
                'campaign_id' => $campaign->id,
                'route_notes' => 'route reviewed again',
            ])
            ->assertRedirect(route('admin.campaign-operations.page', ['campaign' => $campaign->code]));

        $this->actingAs($operator)
            ->from(route('admin.campaign-builder.page', ['campaign' => $campaign->code]))
            ->post(route('admin.campaign-builder.activate', ['campaign' => $campaign->code]))
            ->assertRedirect(route('admin.campaign-builder.page', ['campaign' => $campaign->code]));

        $campaign->refresh();
        $this->assertSame(RecordStatus::Active, $campaign->status);
        $this->assertNotEmpty($campaign->metadata['activated_from_builder_at'] ?? null);
        $this->assertGreaterThanOrEqual(1, CampaignParticipant::query()->where('campaign_id', $campaign->id)->count());
    }

    public function test_operator_can_update_and_delete_campaign_participant(): void
    {
        $operator = User::factory()->create(['role' => UserRole::Operator]);
        $campaign = Campaign::query()->where('code', 'ecopark-pilot-1405')->firstOrFail();

        $this->actingAs($operator)
            ->post(route('admin.campaign-participants.store'), [
                'campaign_id' => $campaign->id,
                'participant_type' => 'member_shop',
                'participation_role' => 'reward_redemption',
                'status' => RecordStatus::Draft->value,
                'onboarding_status' => 'invited',
                'connections_rewards' => 1,
            ])
            ->assertRedirect();

        $participant = CampaignParticipant::query()
            ->where('campaign_id', $campaign->id)
            ->where('participation_role', 'reward_redemption')
            ->firstOrFail();

        $this->actingAs($operator)
            ->from(route('admin.campaign-participants.page', ['campaign' => $campaign->code]))
            ->post(route('admin.campaign-participants.store'), [
                'participant_id' => $participant->id,
                'campaign_id' => $campaign->id,
                'participant_type' => 'sponsor',
                'participation_role' => 'route_sponsor',
                'status' => RecordStatus::Active->value,
                'onboarding_status' => 'ready',
                'connections_rewards' => 2,
                'connections_ads' => 1,
            ])
            ->assertRedirect(route('admin.campaign-participants.page', ['campaign' => $campaign->code]));

        $participant->refresh();
        $this->assertSame('sponsor', $participant->participant_type);
        $this->assertSame('route_sponsor', $participant->participation_role);
        $this->assertSame(2, $participant->metadata['connections']['rewards']);

        $this->actingAs($operator)
            ->from(route('admin.campaign-participants.page', ['campaign' => $campaign->code]))
            ->delete(route('admin.campaign-participants.destroy', ['participant' => $participant->id]))
            ->assertRedirect(route('admin.campaign-participants.page', ['campaign' => $campaign->code]));

        $this->assertDatabaseMissing('campaign_participants', ['id' => $participant->id]);
    }

    public function test_operator_can_create_qr_for_matching_venue_campaign_and_touchpoint(): void
    {
        $operator = User::factory()->create(['role' => UserRole::Operator]);
        $venue = Venue::query()->where('code', 'ecopark-abbasabad')->firstOrFail();
        $campaign = Campaign::query()->where('code', 'ecopark-pilot-1405')->firstOrFail();
        $touchpoint = Touchpoint::query()->where('code', 'main-gate-qr-stand')->firstOrFail();

        $this->actingAs($operator)
            ->post(route('admin.qr-codes.store'), [
                'venue_id' => $venue->id,
                'campaign_id' => $campaign->id,
                'touchpoint_id' => $touchpoint->id,
                'code' => 'ep1405-main-gate-extra',
                'label' => 'QR تست عملیاتی ورودی',
                'status' => RecordStatus::Active->value,
                'valid_from' => '2026-07-01 09:00:00',
                'valid_until' => '2026-08-01 22:00:00',
                'max_scans_per_user_per_window' => 2,
                'duplicate_window_seconds' => 600,
            ])
            ->assertRedirect();

        $qr = QrCode::query()->where('code', 'ep1405-main-gate-extra')->firstOrFail();

        $this->assertSame($venue->id, $qr->venue_id);
        $this->assertSame($campaign->id, $qr->campaign_id);
        $this->assertSame($touchpoint->id, $qr->touchpoint_id);
        $this->assertSame(url('/scan/ep1405-main-gate-extra'), $qr->destination_url);
        $this->assertSame(2, $qr->max_scans_per_user_per_window);
        $this->assertSame(600, $qr->duplicate_window_seconds);
    }

    public function test_operator_can_update_and_delete_unused_qr(): void
    {
        $operator = User::factory()->create(['role' => UserRole::Operator]);
        $venue = Venue::query()->where('code', 'ecopark-abbasabad')->firstOrFail();
        $campaign = Campaign::query()->where('code', 'ecopark-pilot-1405')->firstOrFail();
        $touchpoint = Touchpoint::query()->where('code', 'main-gate-qr-stand')->firstOrFail();

        $this->actingAs($operator)
            ->post(route('admin.qr-codes.store'), [
                'venue_id' => $venue->id,
                'campaign_id' => $campaign->id,
                'touchpoint_id' => $touchpoint->id,
                'code' => 'editable-unused-qr',
                'label' => 'Editable QR',
                'status' => RecordStatus::Draft->value,
                'max_scans_per_user_per_window' => 1,
                'duplicate_window_seconds' => 300,
            ])
            ->assertRedirect();

        $qr = QrCode::query()->where('code', 'editable-unused-qr')->firstOrFail();

        $this->actingAs($operator)
            ->from(route('admin.qr-codes.page'))
            ->post(route('admin.qr-codes.store'), [
                'qr_code_id' => $qr->id,
                'venue_id' => $venue->id,
                'campaign_id' => $campaign->id,
                'touchpoint_id' => $touchpoint->id,
                'code' => 'editable-unused-qr',
                'label' => 'Updated QR',
                'status' => RecordStatus::Inactive->value,
                'max_scans_per_user_per_window' => 2,
                'duplicate_window_seconds' => 600,
            ])
            ->assertRedirect(route('admin.qr-codes.page'));

        $this->assertDatabaseHas('qr_codes', [
            'id' => $qr->id,
            'label' => 'Updated QR',
            'status' => 'inactive',
            'duplicate_window_seconds' => 600,
        ]);

        $this->actingAs($operator)
            ->from(route('admin.qr-codes.page'))
            ->delete(route('admin.qr-codes.destroy', ['qrCode' => $qr->id]))
            ->assertRedirect(route('admin.qr-codes.page'));

        $this->assertDatabaseMissing('qr_codes', ['id' => $qr->id]);
    }

    public function test_qr_creation_rejects_cross_venue_campaign(): void
    {
        $operator = User::factory()->create(['role' => UserRole::Operator]);
        $ecoPark = Venue::query()->where('code', 'ecopark-abbasabad')->firstOrFail();
        $eram = Venue::query()->where('code', 'eram-park')->firstOrFail();
        $touchpoint = Touchpoint::query()->where('code', 'main-gate-qr-stand')->firstOrFail();
        $eramCampaign = Campaign::query()->create([
            'venue_id' => $eram->id,
            'code' => 'eram-campaign',
            'name' => 'کمپین ارم',
            'campaign_type' => 'pilot_visit',
            'status' => RecordStatus::Draft,
        ]);

        $this->actingAs($operator)
            ->from(route('admin.qr-codes.page'))
            ->post(route('admin.qr-codes.store'), [
                'venue_id' => $ecoPark->id,
                'campaign_id' => $eramCampaign->id,
                'touchpoint_id' => $touchpoint->id,
                'code' => 'bad-cross-venue-campaign',
                'status' => RecordStatus::Draft->value,
                'max_scans_per_user_per_window' => 1,
                'duplicate_window_seconds' => 300,
            ])
            ->assertRedirect(route('admin.qr-codes.page'))
            ->assertSessionHasErrors('campaign_id');

        $this->assertDatabaseMissing('qr_codes', ['code' => 'bad-cross-venue-campaign']);
    }

    public function test_qr_registry_page_includes_form_options(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->get(route('admin.qr-codes.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/qr-codes/index')
                ->has('qrCodes', 1)
                ->has('formOptions.venues', 3)
                ->has('formOptions.campaigns', 1)
                ->has('formOptions.touchpoints', 1));
    }
}
