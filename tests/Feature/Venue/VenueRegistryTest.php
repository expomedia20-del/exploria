<?php

namespace Tests\Feature\Venue;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Venue;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class VenueRegistryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PilotLocationSeeder::class);
    }

    public function test_venue_registry_requires_authentication(): void
    {
        $this->getJson('/api/v1/admin/venues')->assertUnauthorized();
    }

    public function test_admin_can_read_full_venue_registry(): void
    {
        $viewer = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($viewer)
            ->getJson('/api/v1/admin/venues')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.code', 'ecopark-abbasabad')
            ->assertJsonPath('data.0.zonesCount', 1)
            ->assertJsonPath('data.0.hubsCount', 4)
            ->assertJsonPath('data.0.touchpointsCount', 1)
            ->assertJsonPath('data.0.partnerAccountsCount', 3)
            ->assertJsonPath('data.0.locationProfile.readinessScore', 0)
            ->assertJsonPath('data.0.locationProfile.sourceSuggestions.0', 'خانه موسیقی تهران');
    }

    public function test_admin_can_update_venue_location_profile(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $venue = Venue::query()->where('code', 'ecopark-abbasabad')->firstOrFail();

        $this->actingAs($admin)
            ->patchJson(route('admin.venues.profile.api.update', $venue), [
                'venue_type' => 'ecopark',
                'primary_audience' => 'خانواده، کودک، گردشگر',
                'official_website_url' => 'https://example.com/ecopark',
                'manual_research_notes' => 'فضای مناسب برای مسیر آموزشی، مأموریت محیطی و پاداش فروشگاهی.',
                'facilities' => [
                    [
                        'name' => 'دریاچه',
                        'function' => 'entertainment',
                        'campaign_uses' => ['mission', 'treasure'],
                        'priority' => 'primary',
                        'notes' => 'نقطه جذاب برای کشف مسیر.',
                    ],
                    [
                        'name' => 'مسیر پیاده‌روی',
                        'function' => 'route',
                        'campaign_uses' => ['qr', 'mission'],
                        'priority' => 'secondary',
                        'notes' => null,
                    ],
                ],
                'facilities_text' => "دریاچه\nباغ کتاب",
                'constraints_text' => "ازدحام آخر هفته\nنیاز به جانمایی امن QR",
            ])
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $venue->refresh();

        $this->assertSame('ecopark', $venue->metadata['location_profile']['venue_type']);
        $this->assertSame('دریاچه', $venue->metadata['location_profile']['facilities'][0]['name']);
        $this->assertSame(['mission', 'treasure'], $venue->metadata['location_profile']['facilities'][0]['campaignUses']);
        $this->assertSame('باغ کتاب', $venue->metadata['location_profile']['facilities'][2]['name']);
        $this->assertCount(3, $venue->metadata['location_profile']['facilities']);

        $this->actingAs($admin)
            ->getJson(route('admin.venues.index'))
            ->assertOk()
            ->assertJsonPath('data.0.locationProfile.venueType', 'ecopark')
            ->assertJsonPath('data.0.locationProfile.readinessScore', 90)
            ->assertJsonPath('data.0.locationProfile.facilities.0.name', 'دریاچه')
            ->assertJsonPath('data.0.locationProfile.facilities.0.campaignUses.1', 'treasure')
            ->assertJsonPath('data.0.locationProfile.facilities.2.name', 'باغ کتاب');
    }

    public function test_admin_can_import_venue_facilities_from_spreadsheet_csv(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $venue = Venue::query()->where('code', 'ecopark-abbasabad')->firstOrFail();
        $file = UploadedFile::fake()->createWithContent(
            'ravaq-units.csv',
            "name,function,campaign_uses,priority,parent,notes\nکافه رواق,retail,\"reward,sponsor\",primary,پروژه رواق,پیشنهاد نوشیدنی\nفست فود رواق,فروشگاهی,\"پاداش، تبلیغ\",secondary,پروژه رواق,غذا و تخفیف\n",
        );

        $this->actingAs($admin)
            ->patch(route('admin.venues.profile.update', $venue), [
                'venue_type' => 'ecopark',
                'primary_audience' => 'خانواده',
                'official_website_url' => 'https://example.com/ecopark',
                'manual_research_notes' => 'ورود گروهی واحدهای رواق',
                'facilities_file' => $file,
                'constraints_text' => '',
            ])
            ->assertRedirect();

        $venue->refresh();

        $this->assertSame('کافه رواق', $venue->metadata['location_profile']['facilities'][0]['name']);
        $this->assertSame('retail', $venue->metadata['location_profile']['facilities'][0]['function']);
        $this->assertSame(['reward', 'sponsor'], $venue->metadata['location_profile']['facilities'][0]['campaignUses']);
        $this->assertSame('فست فود رواق', $venue->metadata['location_profile']['facilities'][1]['name']);
        $this->assertSame(['reward', 'ad'], $venue->metadata['location_profile']['facilities'][1]['campaignUses']);
        $this->assertStringContainsString('زیرمجموعه: پروژه رواق', $venue->metadata['location_profile']['facilities'][1]['notes']);
    }

    public function test_hub_manager_can_open_venue_registry_page(): void
    {
        $this->withoutVite();

        $manager = User::query()->where('role', UserRole::HubManager)->firstOrFail();

        $this->actingAs($manager)
            ->get(route('admin.venues.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/venues/index')
                ->has('venues', 1)
                ->where('venues.0.code', 'ecopark-abbasabad')
                ->where('venues.0.hubsCount', 1)
                ->has('venues.0.zones.0.hubs', 1));
    }
}
