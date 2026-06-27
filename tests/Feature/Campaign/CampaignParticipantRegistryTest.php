<?php

namespace Tests\Feature\Campaign;

use App\Enums\UserRole;
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
                ->where('stats.participants', 1)
                ->where('stats.hubs', 1)
                ->has('participants', 1)
                ->where('participants.0.partner.name', 'فروشگاه X')
                ->where('participants.0.hub.code', 'ravaq-commercial-hub'));
    }

    public function test_guest_cannot_read_campaign_participants(): void
    {
        $this->getJson(route('admin.campaign-participants.index'))->assertUnauthorized();
    }
}