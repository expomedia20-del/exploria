<?php

namespace Tests\Feature\Campaign;

use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\CampaignParticipant;
use App\Models\PartnerAccount;
use App\Models\User;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CampaignParticipantRegistryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PilotLocationSeeder::class);
    }

    public function test_pilot_seed_registers_campaign_participants(): void
    {
        $this->assertDatabaseCount('campaign_participants', 3);
        $this->assertDatabaseHas('campaign_participants', [
            'participation_role' => 'commercial_activation',
            'onboarding_status' => 'ready',
        ]);
    }

    public function test_admin_can_read_campaign_participant_registry_api(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->getJson(route('admin.campaign-participants.index'))
            ->assertOk()
            ->assertJsonPath('data.stats.participants', 3)
            ->assertJsonPath('data.stats.hubs', 3)
            ->assertJsonFragment(['name' => 'فروشگاه X']);
    }

    public function test_hub_manager_only_sees_scoped_campaign_participants(): void
    {
        $this->withoutVite();
        $manager = User::query()->where('email', 'ravaq.manager@example.test')->firstOrFail();

        $this->actingAs($manager)
            ->get(route('admin.campaign-participants.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/campaign-participants/index')
                ->where('stats.participants', 2)
                ->where('stats.hubs', 2)
                ->has('participants', 2)
                ->where('participants.0.partner.name', 'کافه اکو')
                ->where('participants.0.hub.code', 'foodcourt-family-hub')
                ->where('participants.1.partner.name', 'فروشگاه X')
                ->where('participants.1.hub.code', 'ravaq-commercial-hub'));
    }

    public function test_registering_same_partner_for_campaign_updates_existing_participant(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $campaign = Campaign::query()->where('code', 'ecopark-pilot-1405')->firstOrFail();
        $partner = PartnerAccount::query()->where('code', 'cafe-eco')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.campaign-participants.store'), [
                'campaign_id' => $campaign->id,
                'partner_account_id' => $partner->id,
                'participant_type' => 'member_shop',
                'participation_role' => 'reward_redemption',
                'status' => 'draft',
                'onboarding_status' => 'invited',
                'connections_rewards' => 1,
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('admin.campaign-participants.store'), [
                'campaign_id' => $campaign->id,
                'partner_account_id' => $partner->id,
                'participant_type' => 'member_shop',
                'participation_role' => 'commercial_activation',
                'status' => 'active',
                'onboarding_status' => 'ready',
                'connections_rewards' => 2,
                'connections_ads' => 1,
            ])
            ->assertRedirect();

        $this->assertSame(1, CampaignParticipant::query()
            ->where('campaign_id', $campaign->id)
            ->where('partner_account_id', $partner->id)
            ->count());

        $participant = CampaignParticipant::query()
            ->where('campaign_id', $campaign->id)
            ->where('partner_account_id', $partner->id)
            ->firstOrFail();

        $this->assertSame('commercial_activation', $participant->participation_role);
        $this->assertSame('ready', $participant->onboarding_status);
        $this->assertSame(2, $participant->metadata['connections']['rewards']);
        $this->assertSame(1, $participant->metadata['connections']['ads']);
    }

    public function test_guest_cannot_read_campaign_participants(): void
    {
        $this->getJson(route('admin.campaign-participants.index'))->assertUnauthorized();
    }
}
