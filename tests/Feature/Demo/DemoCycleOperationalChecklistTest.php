<?php

namespace Tests\Feature\Demo;

use App\Enums\UserRole;
use App\Models\OperationalChecklistEntry;
use App\Models\User;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DemoCycleOperationalChecklistTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PilotLocationSeeder::class);
        $this->withoutVite();
    }

    public function test_demo_cycle_page_exposes_read_only_checklist_for_viewers(): void
    {
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);

        $this->actingAs($viewer)
            ->get(route('admin.demo-cycle.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/demo-cycle/index')
                ->where('canManageOperationalChecklist', false)
                ->has('operationalChecklistEntries', 0));
    }

    public function test_operator_can_update_operational_checklist_item(): void
    {
        $operator = User::factory()->create(['role' => UserRole::Operator]);

        $this->actingAs($operator)
            ->post(route('admin.demo-cycle.checklist.update'), [
                'item_key' => 'venue_route',
                'status' => 'blocked',
                'owner_name' => 'مدیر عملیات اکوپارک',
                'note' => 'نیاز به تایید مسیر نصب QR توسط تیم میدان.',
                'due_date' => '2026-07-15',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('operational_checklist_entries', [
            'item_key' => 'venue_route',
            'status' => 'blocked',
            'owner_name' => 'مدیر عملیات اکوپارک',
            'updated_by' => $operator->id,
        ]);

        $entry = OperationalChecklistEntry::query()->where('item_key', 'venue_route')->firstOrFail();

        $this->assertSame('2026-07-15', $entry->due_date?->toDateString());
        $this->assertNull($entry->completed_at);
    }

    public function test_viewer_cannot_update_operational_checklist_item(): void
    {
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);

        $this->actingAs($viewer)
            ->post(route('admin.demo-cycle.checklist.update'), [
                'item_key' => 'venue_route',
                'status' => 'done',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('operational_checklist_entries', [
            'item_key' => 'venue_route',
        ]);
    }
}
