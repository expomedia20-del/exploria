<?php

namespace Tests\Feature\Admin;

use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Models\User;
use App\Models\UserAccessScope;
use App\Models\Venue;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InternalOperationsPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PilotLocationSeeder::class);
    }

    public function test_internal_operations_panel_lists_team_accounts_entry_pages_and_supervision(): void
    {
        $this->withoutVite();

        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $projectAdmin = User::factory()->create([
            'name' => 'Exploria Project Lead',
            'email' => 'project.lead@example.test',
            'role' => UserRole::Operator,
        ]);
        $fieldOperator = User::factory()->create([
            'name' => 'Exploria Field Operator',
            'email' => 'field.operator@example.test',
            'role' => UserRole::Operator,
        ]);
        $venue = Venue::query()->where('code', 'ecopark-abbasabad')->firstOrFail();

        UserAccessScope::query()->create([
            'user_id' => $projectAdmin->id,
            'role_key' => 'project_admin',
            'scope_type' => 'venue',
            'scope_id' => $venue->id,
            'status' => RecordStatus::Active,
        ]);

        UserAccessScope::query()->create([
            'user_id' => $fieldOperator->id,
            'role_key' => 'field_operator',
            'scope_type' => 'campaign',
            'scope_id' => 'ecopark-pilot-1405',
            'status' => RecordStatus::Active,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.internal-operations.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/internal-operations/index')
                ->where('stats.internalUsers', 2)
                ->where('stats.activeAssignments', 2)
                ->has('teamMembers', 2)
                ->where('teamMembers.0.user.email', 'project.lead@example.test')
                ->where('teamMembers.0.entryHref', '/admin/internal-operations')
                ->where('teamMembers.0.subordinateCount', 2)
                ->where('teamMembers.1.user.email', 'field.operator@example.test')
                ->where('teamMembers.1.entryHref', '/admin/campaign-operations')
                ->where('teamMembers.1.reportsToKey', 'project_admin')
                ->has('supervisionLines'));
    }

    public function test_role_operations_panel_exposes_account_role_and_entry_panel_map(): void
    {
        $this->withoutVite();

        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->get(route('admin.role-operations.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/role-operations/index')
                ->where('roles.0.key', 'super_admin')
                ->where('roles.0.accountRole', 'admin')
                ->where('roles.0.entryHref', '/dashboard')
                ->where('roles.1.key', 'regional_admin')
                ->where('roles.1.accountRole', 'regional_admin')
                ->where('roles.1.entryHref', '/dashboard')
                ->where('roles.6.key', 'venue_executive')
                ->where('roles.6.accountRole', 'viewer')
                ->where('roles.6.entryHref', '/venue/dashboard')
                ->where('roles.9.key', 'shop_manager')
                ->where('roles.9.accountRole', 'shop_partner')
                ->where('roles.9.entryHref', '/partner/dashboard')
                ->where('roles.12.key', 'participant')
                ->where('roles.12.entryHref', '/participant/dashboard'));
    }
}
