<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RolePanelJourneyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_demo_accounts_land_on_their_role_panel_after_login(): void
    {
        $journeys = [
            'admin@example.test' => route('dashboard', absolute: false),
            'regional@example.test' => route('dashboard', absolute: false),
            'viewer@example.test' => route('dashboard', absolute: false),
            'demo@example.test' => route('participant.dashboard', absolute: false),
            'ravaq.manager@example.test' => route('ravaq.dashboard', absolute: false),
            'venue.manager.ecopark@example.test' => route('venue.dashboard', absolute: false),
            'cafe.eco@example.test' => route('partner.dashboard', absolute: false),
            'ravaq.store@example.test' => route('partner.dashboard', absolute: false),
            'family.sponsor@example.test' => route('sponsor.dashboard', absolute: false),
        ];

        foreach ($journeys as $email => $expectedPath) {
            $response = $this->post(route('login.store'), [
                'email' => $email,
                'password' => 'password',
            ]);

            $this->assertAuthenticated();
            $response->assertSessionHasNoErrors();
            $response->assertRedirect($expectedPath);

            $this->app['auth']->guard()->logout();
            $this->flushSession();
        }
    }

    public function test_role_panels_render_for_seeded_demo_accounts(): void
    {
        $this->withoutVite();

        $journeys = [
            'admin@example.test' => [route('dashboard', absolute: false), 'dashboard'],
            'regional@example.test' => [route('dashboard', absolute: false), 'dashboard'],
            'viewer@example.test' => [route('dashboard', absolute: false), 'dashboard'],
            'demo@example.test' => [route('participant.dashboard', absolute: false), 'participant/dashboard'],
            'ravaq.manager@example.test' => [route('ravaq.dashboard', absolute: false), 'hub/dashboard'],
            'venue.manager.ecopark@example.test' => [route('venue.dashboard', absolute: false), 'venue/dashboard'],
            'cafe.eco@example.test' => [route('partner.dashboard', absolute: false), 'partner/dashboard'],
            'family.sponsor@example.test' => [route('sponsor.dashboard', absolute: false), 'sponsor/dashboard'],
        ];

        foreach ($journeys as $email => [$path, $component]) {
            $user = User::query()->where('email', $email)->firstOrFail();

            $this->actingAs($user)
                ->get($path)
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page->component($component));

            $this->app['auth']->guard()->logout();
        }
    }

    public function test_external_and_read_only_accounts_cannot_open_central_admin_mutations(): void
    {
        $shopPartner = User::query()->where('email', 'cafe.eco@example.test')->firstOrFail();
        $visitor = User::query()->where('email', 'demo@example.test')->firstOrFail();
        $viewer = User::query()->where('email', 'viewer@example.test')->firstOrFail();

        $this->actingAs($shopPartner)
            ->get(route('admin.access-scopes.page'))
            ->assertForbidden();

        $this->actingAs($visitor)
            ->get(route('admin.support.page'))
            ->assertForbidden();

        $this->actingAs($viewer)
            ->get(route('admin.display-operations.page'))
            ->assertForbidden();
    }

    public function test_new_visitors_still_land_on_participant_dashboard_without_access_scopes(): void
    {
        $visitor = User::factory()->create(['role' => UserRole::Visitor]);

        $response = $this->post(route('login.store'), [
            'email' => $visitor->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($visitor);
        $response->assertRedirect(route('participant.dashboard', absolute: false));
    }
}
