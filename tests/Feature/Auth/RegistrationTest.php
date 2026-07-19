<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\EventLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessFortifyHas(Features::registration());
    }

    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->get(route('register'));

        $response->assertOk();
    }

    public function test_new_users_can_register()
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('participant.dashboard', absolute: false));

        $user = User::query()->where('email', 'test@example.com')->firstOrFail();

        $this->assertSame(UserRole::Visitor, $user->role);
        $event = EventLog::query()->where('event_type', 'user_registered')->firstOrFail();
        $this->assertSame($user->id, $event->actor_user_id);
        $this->assertSame((string) $user->id, $event->object_id);
        $this->assertArrayNotHasKey('email', $event->payload_json ?? []);
    }

    public function test_registered_public_users_are_sent_to_participant_dashboard(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Public Visitor',
            'email' => 'visitor@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('participant.dashboard', absolute: false));

        $this->get(route('dashboard'))
            ->assertRedirect(route('participant.dashboard'));
    }
}
