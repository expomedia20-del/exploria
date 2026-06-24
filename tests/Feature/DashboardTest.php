<?php

namespace Tests\Feature;

use App\Models\ConsentVersion;
use App\Models\User;
use Database\Seeders\ConsentVersionSeeder;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_dashboard_shows_pilot_operational_stats(): void
    {
        $this->withoutVite();
        $this->seed([ConsentVersionSeeder::class, PilotLocationSeeder::class]);

        $user = User::factory()->create();
        $version = ConsentVersion::query()->where('is_active', true)->firstOrFail();

        $this->actingAs($user)->postJson('/api/v1/consents/accept', [
            'consentVersionId' => $version->id,
            'source' => 'qr_landing',
            'sourceQrCode' => PilotLocationSeeder::DEMO_QR_CODE,
        ])->assertCreated();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboard')
                ->where('stats.venues', 3)
                ->where('stats.activeQrCodes', 1)
                ->where('stats.consents', 1)
                ->where('stats.visits', 1)
                ->has('latestVisits', 1));
    }
}
