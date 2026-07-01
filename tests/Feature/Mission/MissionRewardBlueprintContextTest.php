<?php

namespace Tests\Feature\Mission;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Venue;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MissionRewardBlueprintContextTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PilotLocationSeeder::class);
    }

    public function test_admin_can_read_venue_design_context_from_blueprints_api(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $venue = Venue::query()->where('code', 'ecopark-abbasabad')->firstOrFail();

        $venue->update([
            'metadata' => [
                'location_profile' => [
                    'venue_type' => 'ecopark',
                    'primary_audience' => 'family',
                    'official_website_url' => 'https://example.com',
                    'manual_research_notes' => 'campaign design context',
                    'facilities' => [
                        [
                            'name' => 'Ravaq food court',
                            'function' => 'retail',
                            'campaignUses' => ['reward', 'sponsor'],
                            'priority' => 'primary',
                            'notes' => 'partner reward point',
                        ],
                        [
                            'name' => 'Discovery route',
                            'function' => 'discovery',
                            'campaignUses' => ['qr', 'mission', 'treasure'],
                            'priority' => 'secondary',
                            'notes' => 'mission route',
                        ],
                    ],
                    'constraints' => ['weekend crowd'],
                    'updated_at' => now()->toIso8601String(),
                ],
            ],
        ]);

        $this->actingAs($admin)
            ->getJson(route('admin.mission-blueprints.index'))
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.venueDesignContext.totals.facilities', 2)
            ->assertJsonPath('data.venueDesignContext.totals.campaignUsableFacilities', 2)
            ->assertJsonPath('data.venueDesignContext.venues.0.code', 'ecopark-abbasabad')
            ->assertJsonPath('data.venueDesignContext.venues.0.designAssets.mission.0.name', 'Discovery route')
            ->assertJsonPath('data.venueDesignContext.venues.0.designAssets.reward.0.name', 'Ravaq food court');
    }
}
