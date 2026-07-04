<?php

namespace Tests\Feature\Admin;

use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Models\Hub;
use App\Models\User;
use App\Models\UserAccessScope;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class UserAccessScopePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PilotLocationSeeder::class);
    }

    public function test_admin_can_open_access_scope_page(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->get(route('admin.access-scopes.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/access-scopes/index')
                ->where('stats.total', 5)
                ->where('stats.active', 5)
                ->has('accessScopes', 5)
                ->has('userOptions')
                ->has('roleOptions')
                ->has('scopeOptions.hub'));
    }

    public function test_admin_can_create_hub_access_scope(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $operator = User::factory()->create(['role' => UserRole::Operator]);
        $hub = Hub::query()->where('code', 'ravaq-commercial-hub')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.access-scopes.store'), [
                'user_id' => $operator->id,
                'role_key' => 'project_admin',
                'scope_type' => 'hub',
                'scope_id' => $hub->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('user_access_scopes', [
            'user_id' => $operator->id,
            'role_key' => 'project_admin',
            'scope_type' => 'hub',
            'scope_id' => $hub->id,
            'status' => 'active',
        ]);
    }

    public function test_admin_can_deactivate_access_scope(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $scope = UserAccessScope::query()->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.access-scopes.deactivate', $scope))
            ->assertRedirect();

        $this->assertSame(RecordStatus::Inactive, $scope->fresh()->status);
    }

    public function test_viewer_cannot_mutate_access_scopes(): void
    {
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);
        $hub = Hub::query()->where('code', 'ravaq-commercial-hub')->firstOrFail();

        $this->actingAs($viewer)
            ->post(route('admin.access-scopes.store'), [
                'user_id' => $viewer->id,
                'role_key' => 'hub_manager',
                'scope_type' => 'hub',
                'scope_id' => $hub->id,
            ])
            ->assertForbidden();
    }
}
