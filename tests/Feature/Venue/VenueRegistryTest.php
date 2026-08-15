<?php

namespace Tests\Feature\Venue;

use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\CampaignParticipant;
use App\Models\CampaignSponsorship;
use App\Models\DisplayDevice;
use App\Models\Hub;
use App\Models\MissionInstance;
use App\Models\PartnerAccount;
use App\Models\QrCode;
use App\Models\RewardDefinition;
use App\Models\RewardInventoryAllocation;
use App\Models\Touchpoint;
use App\Models\Treasure;
use App\Models\User;
use App\Models\UserAccessScope;
use App\Models\Venue;
use App\Models\Zone;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;
use ZipArchive;

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
            ->assertJsonPath('data.0.zonesCount', 3)
            ->assertJsonPath('data.0.hubsCount', 5)
            ->assertJsonPath('data.0.touchpointsCount', 1)
            ->assertJsonPath('data.0.partnerAccountsCount', 5)
            ->assertJsonPath('data.0.locationProfile.readinessScore', 0)
            ->assertJsonPath('data.0.locationProfile.sourceSuggestions.0', 'خانه موسیقی تهران')
            ->assertJsonPath('data.2.locationProfile.sourceSuggestions.0', 'رستوران گردان')
            ->assertJsonPath('data.2.locationProfile.sourceSuggestions.4', 'مرکز همایش‌های بین‌المللی برج میلاد')
            ->assertJsonPath('data.0.demoStressPlan.title', 'دموی فشار از ارزیابی مکان تا اجرا')
            ->assertJsonPath('data.0.demoStressPlan.summary.totalCount', 11)
            ->assertJsonPath('data.0.demoStressPlan.items.0.key', 'venue')
            ->assertJsonPath('data.0.demoStressPlan.items.0.complete', false)
            ->assertJsonPath('data.0.demoStressPlan.nextAction.key', 'venue');
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
        $this->assertDatabaseHas('event_log', [
            'event_type' => 'audit.venue_updated',
            'actor_user_id' => $admin->id,
            'object_type' => 'venue',
            'object_id' => $venue->id,
        ]);

        $this->actingAs($admin)
            ->getJson(route('admin.venues.index'))
            ->assertOk()
            ->assertJsonPath('data.0.locationProfile.venueType', 'ecopark')
            ->assertJsonPath('data.0.locationProfile.readinessScore', 90)
            ->assertJsonPath('data.0.locationProfile.facilities.0.name', 'دریاچه')
            ->assertJsonPath('data.0.locationProfile.facilities.0.campaignUses.1', 'treasure')
            ->assertJsonPath('data.0.locationProfile.facilities.2.name', 'باغ کتاب')
            ->assertJsonPath('data.0.demoStressPlan.items.0.key', 'venue')
            ->assertJsonPath('data.0.demoStressPlan.items.0.complete', true);
    }

    public function test_admin_can_create_new_venue_for_later_evaluation(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->postJson(route('admin.venues.api.store'), [
                'name' => 'باغ ملی گیاه‌شناسی',
                'code' => 'botanical-garden',
                'city' => 'تهران',
                'venue_type' => 'ecopark',
                'primary_audience' => 'خانواده، گردشگر، دانش‌آموز',
                'official_website_url' => 'https://example.com/botanical-garden',
            ])
            ->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.code', 'botanical-garden');

        $venue = Venue::query()->where('code', 'botanical-garden')->firstOrFail();

        $this->assertSame('باغ ملی گیاه‌شناسی', $venue->name);
        $this->assertSame('draft', $venue->status->value);
        $this->assertSame('draft', $venue->profile_status->value);
        $this->assertSame('ecopark', $venue->metadata['location_profile']['venue_type']);
        $this->assertSame([], $venue->metadata['location_profile']['facilities']);
        $this->assertDatabaseHas('event_log', [
            'event_type' => 'audit.venue_created',
            'actor_user_id' => $admin->id,
            'object_type' => 'venue',
            'object_id' => $venue->id,
        ]);

        $this->actingAs($admin)
            ->getJson(route('admin.venues.index'))
            ->assertOk()
            ->assertJsonPath('data.3.code', 'botanical-garden')
            ->assertJsonPath('data.3.locationProfile.venueType', 'ecopark')
            ->assertJsonPath('data.3.locationProfile.facilities', []);

        $this->actingAs($admin)
            ->postJson(route('admin.venues.facility-suggestions', $venue), [
                'source_text' => "باغ ژاپنی\nکافه باغ\nمسیر آموزشی",
                'official_website_url' => 'https://example.com/botanical-garden',
            ])
            ->assertOk()
            ->assertJsonPath('data.summary.count', 3)
            ->assertJsonPath('data.summary.newCount', 3)
            ->assertJsonPath('data.suggestions.1.function', 'retail');
    }

    public function test_admin_can_activate_the_base_operational_cycle_for_any_venue(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $venue = Venue::query()->where('code', 'eram-park')->firstOrFail();

        $this->actingAs($admin)
            ->postJson(route('admin.venues.api.activate', $venue))
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.venueCode', 'eram-park')
            ->assertJsonPath('data.campaignCode', 'eram-park-activation-campaign')
            ->assertJsonPath('data.counts.zones', 1)
            ->assertJsonPath('data.counts.campaigns', 1)
            ->assertJsonPath('data.counts.qrCodes', 1)
            ->assertJsonPath('data.counts.partners', 3)
            ->assertJsonPath('data.counts.missions', 4)
            ->assertJsonPath('data.counts.rewards', 3);

        $venue->refresh();
        $campaign = Campaign::query()->where('venue_id', $venue->id)->where('code', 'eram-park-activation-campaign')->firstOrFail();

        $this->assertSame('active', $venue->status->value);
        $this->assertSame('active', $venue->profile_status->value);
        $this->assertGreaterThanOrEqual(4, count($venue->metadata['location_profile']['facilities']));
        $this->assertSame('venue-activation-pilot', $campaign->metadata['blueprint_code']);
        $this->assertNotNull($campaign->metadata['route_reviewed_at']);
        $this->assertSame(1, Zone::query()->where('venue_id', $venue->id)->where('code', 'activation-core-zone')->count());
        $this->assertSame(3, Hub::query()->whereHas('zone', fn ($query) => $query->where('venue_id', $venue->id))->count());
        $this->assertSame(3, Touchpoint::query()->whereHas('hub.zone', fn ($query) => $query->where('venue_id', $venue->id))->count());
        $this->assertSame(1, QrCode::query()->where('venue_id', $venue->id)->where('campaign_id', $campaign->id)->where('code', 'eram-park-entry-qr')->count());
        $this->assertSame(3, PartnerAccount::query()->where('venue_id', $venue->id)->count());
        $this->assertSame(3, CampaignParticipant::query()->where('campaign_id', $campaign->id)->where('onboarding_status', 'ready')->count());
        $this->assertSame(4, MissionInstance::query()->where('campaign_id', $campaign->id)->where('venue_id', $venue->id)->count());
        $this->assertSame(1, Treasure::query()->where('campaign_id', $campaign->id)->where('venue_id', $venue->id)->count());
        $this->assertSame(3, RewardDefinition::query()->where('campaign_id', $campaign->id)->where('venue_id', $venue->id)->count());
        $this->assertSame(2, RewardInventoryAllocation::query()->where('campaign_id', $campaign->id)->where('status', 'active')->count());
        $this->assertSame(1, DisplayDevice::query()->where('venue_id', $venue->id)->where('code', 'eram-park-activation-display')->count());
        $this->assertSame(1, CampaignSponsorship::query()->where('campaign_id', $campaign->id)->where('status', 'active')->count());
        $this->assertSame(1, UserAccessScope::query()->where('role_key', 'venue_executive')->where('scope_type', 'venue')->where('scope_id', $venue->id)->count());
        $this->assertDatabaseHas('event_log', [
            'event_type' => 'audit.venue_operational_cycle_activated',
            'actor_user_id' => $admin->id,
            'object_type' => 'venue',
            'object_id' => $venue->id,
        ]);

        $this->actingAs($admin)
            ->postJson(route('admin.venues.api.activate', $venue))
            ->assertOk()
            ->assertJsonPath('data.counts.zones', 1)
            ->assertJsonPath('data.counts.campaigns', 1)
            ->assertJsonPath('data.counts.qrCodes', 1)
            ->assertJsonPath('data.counts.partners', 3)
            ->assertJsonPath('data.counts.missions', 4)
            ->assertJsonPath('data.counts.rewards', 3);
    }

    public function test_admin_can_generate_facility_suggestions_from_source_text(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $venue = Venue::query()->where('code', 'ecopark-abbasabad')->firstOrFail();

        $this->actingAs($admin)
            ->postJson(route('admin.venues.facility-suggestions', $venue), [
                'source_text' => "سکوی دید باز\nرستوران گردان\nفودکورت برج میلاد\nمحل پذیرش و بلیت",
                'official_website_url' => 'https://miladtower.tehran.ir/intro',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.summary.count', 4)
            ->assertJsonPath('data.summary.newCount', 4)
            ->assertJsonPath('data.suggestions.0.name', 'سکوی دید باز')
            ->assertJsonPath('data.suggestions.0.alreadyExists', false)
            ->assertJsonPath('data.suggestions.0.function', 'discovery')
            ->assertJsonPath('data.suggestions.0.campaignUses.0', 'qr')
            ->assertJsonPath('data.suggestions.0.priority', 'primary')
            ->assertJsonPath('data.suggestions.0.confidence', 'high')
            ->assertJsonPath('data.suggestions.1.name', 'رستوران گردان')
            ->assertJsonPath('data.suggestions.1.function', 'retail')
            ->assertJsonPath('data.suggestions.1.campaignUses.0', 'reward');
    }

    public function test_facility_suggestions_keep_existing_items_visible_for_review(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $venue = Venue::query()->where('code', 'milad-tower')->firstOrFail();
        $venue->update([
            'metadata' => [
                'location_profile' => [
                    'venue_type' => 'mixed',
                    'official_website_url' => 'https://miladtower.tehran.ir/',
                    'facilities' => [
                        [
                            'name' => 'رستوران گردان',
                            'function' => 'retail',
                            'campaignUses' => ['reward'],
                            'priority' => 'primary',
                        ],
                    ],
                ],
            ],
        ]);

        $this->actingAs($admin)
            ->postJson(route('admin.venues.facility-suggestions', $venue), [
                'source_text' => "رستوران گردان\nسکوی دید باز",
                'official_website_url' => 'https://miladtower.tehran.ir/',
            ])
            ->assertOk()
            ->assertJsonPath('data.summary.count', 2)
            ->assertJsonPath('data.summary.newCount', 1)
            ->assertJsonPath('data.summary.existingCount', 1)
            ->assertJsonPath('data.suggestions.0.name', 'رستوران گردان')
            ->assertJsonPath('data.suggestions.0.alreadyExists', true)
            ->assertJsonPath('data.suggestions.1.name', 'سکوی دید باز')
            ->assertJsonPath('data.suggestions.1.alreadyExists', false);
    }

    public function test_admin_can_apply_generated_facility_suggestions_to_profile(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $venue = Venue::query()->where('code', 'ecopark-abbasabad')->firstOrFail();

        $this->actingAs($admin)
            ->patchJson(route('admin.venues.facility-suggestions.apply', $venue), [
                'source_text' => "گنبد آسمان\nرستوران گردان\nمحل پذیرش و بلیت",
                'official_website_url' => 'https://miladtower.tehran.ir/intro',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $venue->refresh();

        $this->assertSame('گنبد آسمان', $venue->metadata['location_profile']['facilities'][0]['name']);
        $this->assertSame('discovery', $venue->metadata['location_profile']['facilities'][0]['function']);
        $this->assertSame(['qr', 'mission', 'treasure'], $venue->metadata['location_profile']['facilities'][0]['campaignUses']);
        $this->assertSame('high', $venue->metadata['location_profile']['facilities'][0]['confidence']);
        $this->assertFalse($venue->metadata['location_profile']['facilities'][0]['fieldReviewRequired']);
        $this->assertSame('https://miladtower.tehran.ir/intro', $venue->metadata['location_profile']['official_website_url']);
        $this->assertSame(3, $venue->metadata['location_profile']['facility_extraction']['suggested_count']);
        $this->assertDatabaseHas('event_log', [
            'event_type' => 'audit.venue_facilities_suggested',
            'actor_user_id' => $admin->id,
            'object_type' => 'venue',
            'object_id' => $venue->id,
        ]);
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

    public function test_admin_can_download_venue_facilities_template(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->get(route('admin.venues.facilities-template'))
            ->assertOk()
            ->assertDownload('exploria-venue-facilities-template.xlsx');
    }

    public function test_admin_can_import_venue_facilities_from_xlsx_template(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $venue = Venue::query()->where('code', 'ecopark-abbasabad')->firstOrFail();
        $file = $this->makeVenueFacilitiesXlsx([
            ['نام', 'کارکرد', 'کاربرد کمپینی', 'اولویت', 'زیرمجموعه', 'یادداشت'],
            ['کافه کودک رواق', 'retail', 'reward,sponsor', 'primary', 'پروژه رواق', 'پیشنهاد خانواده'],
            ['مسیر کشف رواق', 'discovery', 'qr,mission,treasure', 'secondary', 'پروژه رواق', 'نقطه مأموریت'],
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.venues.profile.update', $venue), [
                'venue_type' => 'ecopark',
                'primary_audience' => 'خانواده',
                'official_website_url' => 'https://example.com/ecopark',
                'manual_research_notes' => 'ورود XLSX واحدهای رواق',
                'facilities_file' => $file,
                'constraints_text' => '',
            ])
            ->assertRedirect();

        $venue->refresh();

        $this->assertSame('کافه کودک رواق', $venue->metadata['location_profile']['facilities'][0]['name']);
        $this->assertSame('retail', $venue->metadata['location_profile']['facilities'][0]['function']);
        $this->assertSame(['reward', 'sponsor'], $venue->metadata['location_profile']['facilities'][0]['campaignUses']);
        $this->assertSame('مسیر کشف رواق', $venue->metadata['location_profile']['facilities'][1]['name']);
        $this->assertSame(['qr', 'mission', 'treasure'], $venue->metadata['location_profile']['facilities'][1]['campaignUses']);
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
                ->where('venues.0.hubsCount', 2)
                ->has('venues.0.zones.0.hubs', 2));
    }

    /** @param array<int, array<int, string>> $rows */
    private function makeVenueFacilitiesXlsx(array $rows): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'venue-facilities-test-');
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="امکانات مکان" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>');
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->xlsxSheet($rows));
        $zip->close();

        return new UploadedFile($path, 'venue-facilities.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    /** @param array<int, array<int, string>> $rows */
    private function xlsxSheet(array $rows): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';

        foreach ($rows as $rowIndex => $row) {
            $excelRow = $rowIndex + 1;
            $xml .= '<row r="'.$excelRow.'">';

            foreach ($row as $columnIndex => $value) {
                $cell = chr(65 + $columnIndex).$excelRow;
                $xml .= '<c r="'.$cell.'" t="inlineStr"><is><t>'.e($value).'</t></is></c>';
            }

            $xml .= '</row>';
        }

        return $xml.'</sheetData></worksheet>';
    }
}
