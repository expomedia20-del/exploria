<?php

namespace Tests\Feature\Admin;

use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Models\EventLog;
use App\Models\User;
use App\Models\UserAccessScope;
use App\Models\Venue;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class UserManagementPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PilotLocationSeeder::class);
    }

    public function test_admin_can_open_user_management_page(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->get(route('admin.users.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/users/index')
                ->has('users')
                ->has('stats.total')
                ->has('stats.publicRegistered')
                ->has('stats.publicParticipants')
                ->has('stats.activeScopedUsers')
                ->has('roleOptions')
                ->has('filters')
            );
    }

    public function test_user_management_page_separates_registered_and_participant_visitors(): void
    {
        $this->withoutVite();

        $admin = User::factory()->create(['role' => UserRole::Admin]);
        User::factory()->create([
            'role' => UserRole::Visitor,
            'public_participation_status' => 'registered',
        ]);
        User::factory()->create([
            'role' => UserRole::Visitor,
            'public_participation_status' => 'participant',
            'public_participation_mode' => 'family',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/users/index')
                ->where('stats.publicRegistered', fn (int $count): bool => $count >= 1)
                ->where('stats.publicParticipants', fn (int $count): bool => $count >= 1)
                ->has('users.0.publicStatus')
                ->has('users.0.publicStatusLabel'));
    }

    public function test_only_central_admin_receives_participant_mobile_in_user_management(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);
        $participant = User::factory()->create([
            'role' => UserRole::Visitor,
            'mobile' => '09121234567',
            'mobile_hash' => hash('sha256', '09121234567'),
            'public_participation_status' => 'participant',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('users', fn (Collection $users): bool => $users->contains(
                    fn (array $user): bool => $user['id'] === $participant->id
                        && ($user['mobile'] ?? null) === '09121234567',
                )));

        $this->actingAs($viewer)
            ->get(route('admin.users.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('users', fn (Collection $users): bool => $users->every(
                    fn (array $user): bool => ! array_key_exists('mobile', $user),
                )));
    }

    public function test_admin_can_open_user_management_guide_page(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->get(route('admin.users.guide'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/users/guide'));
    }

    public function test_admin_can_change_base_user_role(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $user = User::factory()->create(['role' => UserRole::Viewer]);

        $this->actingAs($admin)
            ->patch(route('admin.users.role', $user), [
                'role' => UserRole::Operator->value,
            ])
            ->assertRedirect();

        $this->assertSame(UserRole::Operator, $user->fresh()->role);
        $this->assertDatabaseHas('event_log', [
            'event_type' => 'audit.user_role_updated',
            'actor_user_id' => $admin->id,
            'object_type' => 'user',
            'object_id' => (string) $user->id,
        ]);
    }

    public function test_admin_can_deactivate_all_active_user_access_scopes(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $operator = User::factory()->create(['role' => UserRole::Operator]);
        $venue = Venue::query()->where('code', 'ecopark-abbasabad')->firstOrFail();

        UserAccessScope::query()->create([
            'user_id' => $operator->id,
            'role_key' => 'project_admin',
            'scope_type' => 'venue',
            'scope_id' => $venue->id,
            'status' => RecordStatus::Active,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.users.deactivate-access', $operator))
            ->assertRedirect();

        $this->assertDatabaseHas('user_access_scopes', [
            'user_id' => $operator->id,
            'role_key' => 'project_admin',
            'status' => RecordStatus::Inactive->value,
        ]);
        $this->assertDatabaseHas('event_log', [
            'event_type' => 'audit.user_access_deactivated',
            'actor_user_id' => $admin->id,
            'object_id' => (string) $operator->id,
        ]);
    }

    public function test_admin_cannot_delete_user_with_operational_history(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $operator = User::factory()->create(['role' => UserRole::Operator]);
        $venue = Venue::query()->where('code', 'ecopark-abbasabad')->firstOrFail();

        UserAccessScope::query()->create([
            'user_id' => $operator->id,
            'role_key' => 'project_admin',
            'scope_type' => 'venue',
            'scope_id' => $venue->id,
            'status' => RecordStatus::Inactive,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $operator))
            ->assertSessionHasErrors('delete');

        $this->assertDatabaseHas('users', ['id' => $operator->id]);
        $this->assertDatabaseMissing('event_log', [
            'event_type' => 'audit.user_deleted',
            'object_id' => (string) $operator->id,
        ]);
    }

    public function test_admin_can_delete_user_without_history_and_audit_contains_no_pii(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $user = User::factory()->create(['role' => UserRole::Viewer]);
        $email = $user->email;

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $user))
            ->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $event = EventLog::query()->where('event_type', 'audit.user_deleted')->firstOrFail();
        $this->assertSame((string) $user->id, $event->object_id);
        $this->assertArrayNotHasKey('email', $event->payload_json ?? []);
        $this->assertStringNotContainsString((string) $email, json_encode($event->payload_json, JSON_THROW_ON_ERROR));

        $this->actingAs($admin)
            ->getJson(route('admin.events.scan-log.index', ['event_type' => 'audit.user_deleted']))
            ->assertOk()
            ->assertJsonPath('data.items.0.objectType', 'user')
            ->assertJsonPath('data.items.0.objectId', (string) $user->id)
            ->assertJsonMissingPath('data.items.0.email');
    }

    public function test_viewer_cannot_mutate_users(): void
    {
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);
        $user = User::factory()->create(['role' => UserRole::Visitor]);

        $this->actingAs($viewer)
            ->patch(route('admin.users.role', $user), [
                'role' => UserRole::Operator->value,
            ])
            ->assertForbidden();
    }
}
