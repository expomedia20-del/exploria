<?php

namespace Tests\Feature\Campaign;

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CampaignOperationsBlueprintTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PilotLocationSeeder::class);
    }

    public function test_admin_can_read_campaign_operations_blueprint_api(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->getJson(route('admin.campaign-operations.index'))
            ->assertOk()
            ->assertJsonPath('data.stats.campaigns', 1)
            ->assertJsonPath('data.stats.participants', 3)
            ->assertJsonPath('data.stats.internalSponsors', 1)
            ->assertJsonPath('data.stats.externalSponsors', 0)
            ->assertJsonPath('data.stats.entryPoints', 1)
            ->assertJsonPath('data.campaigns.0.stats.missions', 4)
            ->assertJsonPath('data.campaigns.0.stats.readyParticipants', 2)
            ->assertJsonPath('data.campaigns.0.operationalReview.status', 'needs_attention')
            ->assertJsonPath('data.campaigns.0.operationalReview.checks.0.key', 'qr');
    }

    public function test_hub_manager_reads_only_scoped_campaign_operations(): void
    {
        $this->withoutVite();
        $manager = User::query()->where('email', 'ravaq.manager@example.test')->firstOrFail();

        $this->actingAs($manager)
            ->get(route('admin.campaign-operations.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/campaign-operations/index')
                ->where('stats.campaigns', 1)
                ->where('stats.participants', 1)
                ->where('stats.internalSponsors', 0)
                ->where('campaigns.0.participantsByHub.0.hub.code', 'ravaq-commercial-hub')
                ->where('campaigns.0.journey.commercial.items.0.partner.name', 'فروشگاه X'));
    }

    public function test_guest_cannot_read_campaign_operations(): void
    {
        $this->getJson(route('admin.campaign-operations.index'))->assertUnauthorized();
    }

    public function test_admin_cannot_confirm_operational_route_while_checks_need_attention(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $campaignId = \App\Models\Campaign::query()
            ->where('code', 'ecopark-pilot-1405')
            ->valueOrFail('id');

        $this->actingAs($admin)
            ->post(route('admin.campaign-operations.review'), [
                'campaign_id' => $campaignId,
                'route_notes' => 'route checked',
            ])
            ->assertSessionHasErrors('campaign_id');

        $this->assertDatabaseHas('campaigns', [
            'id' => $campaignId,
        ]);

        $this->assertNull(\App\Models\Campaign::query()->findOrFail($campaignId)->metadata['route_reviewed_at'] ?? null);
    }
}
