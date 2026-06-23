<?php

namespace Tests\Feature\Venue;

use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Models\QrCode;
use App\Models\User;
use App\Models\Venue;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PilotLocationQrTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PilotLocationSeeder::class);
    }

    public function test_seed_contains_only_the_three_prioritized_pilot_venues(): void
    {
        $this->assertDatabaseCount('venues', 3);
        $this->assertDatabaseHas('venues', ['code' => 'ecopark-abbasabad', 'status' => 'active']);
        $this->assertDatabaseHas('venues', ['code' => 'eram-park', 'status' => 'draft']);
        $this->assertDatabaseHas('venues', ['code' => 'milad-tower', 'status' => 'placeholder']);
    }

    public function test_ecopark_demo_qr_has_the_required_active_bindings(): void
    {
        $qr = QrCode::query()->with(['venue', 'touchpoint.hub.zone', 'campaign'])->firstOrFail();

        $this->assertSame('ecopark-abbasabad', $qr->venue->code);
        $this->assertSame($qr->venue->id, $qr->touchpoint->hub->zone->venue_id);
        $this->assertSame($qr->venue->id, $qr->campaign->venue_id);
        $this->assertTrue($qr->isAvailableForLanding());
    }

    public function test_pilot_seeder_is_idempotent(): void
    {
        $this->seed(PilotLocationSeeder::class);

        $this->assertDatabaseCount('venues', 3);
        $this->assertDatabaseCount('qr_codes', 1);
    }

    public function test_active_demo_qr_opens_the_persian_landing_page(): void
    {
        $this->withoutVite();

        $this->get('/scan/'.PilotLocationSeeder::DEMO_QR_CODE)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('scan/landing')
                ->where('qr.venueName', 'اکوپارک عباس‌آباد')
                ->where('qr.isDemo', true));
    }

    public function test_inactive_qr_does_not_open_an_accepted_landing(): void
    {
        QrCode::query()->firstOrFail()->update(['status' => RecordStatus::Inactive]);

        $this->getJson('/scan/'.PilotLocationSeeder::DEMO_QR_CODE)->assertNotFound();
    }

    public function test_qr_registry_requires_authentication(): void
    {
        $this->getJson('/api/v1/admin/qr-codes')->assertUnauthorized();
    }

    public function test_viewer_can_read_the_minimal_qr_registry(): void
    {
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);

        $this->actingAs($viewer)
            ->getJson('/api/v1/admin/qr-codes')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.venue.code', 'ecopark-abbasabad')
            ->assertJsonPath('data.0.touchpoint.code', 'main-gate-qr-stand');
    }

    public function test_milad_remains_a_controlled_placeholder(): void
    {
        $milad = Venue::query()->where('code', 'milad-tower')->firstOrFail();

        $this->assertSame(RecordStatus::Placeholder, $milad->status);
        $this->assertSame('controlled_placeholder', $milad->metadata['pilot_role']);
        $this->assertCount(0, $milad->zones);
    }
}
