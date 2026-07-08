<?php

namespace Tests\Feature\Mission;

use App\Enums\UserRole;
use App\Models\MissionInstance;
use App\Models\RewardDefinition;
use App\Models\User;
use App\Models\UserMissionProgress;
use App\Models\UserReward;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MissionRewardFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PilotLocationSeeder::class);
    }

    public function test_pilot_seed_creates_mission_reward_foundation(): void
    {
        $this->assertDatabaseCount('mission_templates', 4);
        $this->assertDatabaseCount('mission_instances', 4);
        $this->assertDatabaseCount('treasures', 1);
        $this->assertDatabaseCount('reward_definitions', 4);

        $this->assertDatabaseHas('mission_templates', [
            'code' => 'scan-entry-qr',
            'point_value' => 120,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('treasures', [
            'code' => 'eco-family-route-treasure',
            'treasure_type' => 'family_team',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('reward_definitions', [
            'code' => 'small-drink-coupon',
            'reward_type' => 'partner_coupon',
            'status' => 'active',
        ]);
    }

    public function test_pilot_mission_seed_is_idempotent(): void
    {
        $this->seed(PilotLocationSeeder::class);

        $this->assertDatabaseCount('mission_templates', 4);
        $this->assertDatabaseCount('mission_instances', 4);
        $this->assertDatabaseCount('treasures', 1);
        $this->assertDatabaseCount('reward_definitions', 4);
    }

    public function test_mission_reward_relationships_are_available(): void
    {
        $mission = MissionInstance::query()
            ->where('code', 'photo-memory-challenge')
            ->with(['missionTemplate', 'campaign', 'venue', 'hub', 'treasure'])
            ->firstOrFail();
        $reward = RewardDefinition::query()
            ->where('code', 'small-drink-coupon')
            ->with(['campaign', 'venue', 'partnerAccount'])
            ->firstOrFail();

        $this->assertSame('چالش عکس و ثبت خاطره', $mission->missionTemplate->title);
        $this->assertSame('ecopark-pilot-1405', $mission->campaign->code);
        $this->assertSame('ecopark-abbasabad', $mission->venue->code);
        $this->assertSame('eco-family-route-treasure', $mission->treasure->code);
        $this->assertSame('cafe-eco', $reward->partnerAccount->code);
    }

    public function test_user_progress_and_reward_wallet_records_can_be_created(): void
    {
        $user = User::factory()->create(['role' => UserRole::Visitor]);
        $mission = MissionInstance::query()->where('code', 'scan-entry-qr')->with('missionTemplate')->firstOrFail();
        $rewardDefinition = RewardDefinition::query()->where('code', 'pilot-entry-badge')->firstOrFail();

        UserMissionProgress::query()->create([
            'user_id' => $user->id,
            'mission_instance_id' => $mission->id,
            'status' => 'completed',
            'started_at' => now()->subMinute(),
            'completed_at' => now(),
            'points_awarded' => $mission->missionTemplate->point_value,
            'metadata' => ['source' => 'feature_test'],
        ]);

        UserReward::query()->create([
            'user_id' => $user->id,
            'reward_definition_id' => $rewardDefinition->id,
            'campaign_id' => $rewardDefinition->campaign_id,
            'status' => 'awarded',
            'awarded_at' => now(),
            'metadata' => ['source' => 'mission_completed'],
        ]);

        $this->assertSame(1, $user->missionProgress()->count());
        $this->assertSame(1, $user->rewards()->count());
    }

    public function test_admin_can_read_mission_reward_registry_api(): void
    {
        $viewer = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($viewer)
            ->getJson('/api/v1/admin/missions')
            ->assertOk()
            ->assertJsonPath('data.stats.missions', 4)
            ->assertJsonPath('data.stats.rewards', 4)
            ->assertJsonPath('data.stats.treasures', 1)
            ->assertJsonPath('data.missions.0.code', 'scan-entry-qr');
    }

    public function test_hub_manager_can_open_mission_reward_registry_page(): void
    {
        $this->withoutVite();
        $manager = User::query()->where('role', UserRole::HubManager)->firstOrFail();

        $this->actingAs($manager)
            ->get(route('admin.missions.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/missions/index')
                ->where('stats.missions', 1)
                ->has('missions', 1)
                ->has('rewards', 1)
                ->has('treasures', 0));
    }
}
