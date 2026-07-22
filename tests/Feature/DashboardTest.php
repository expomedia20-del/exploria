<?php

namespace Tests\Feature;

use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\ConsentVersion;
use App\Models\MissionInstance;
use App\Models\RewardRedemption;
use App\Models\User;
use App\Models\UserAccessScope;
use App\Models\UserReward;
use App\Models\Venue;
use App\Models\Visit;
use App\Services\SupportKnowledgeBaseService;
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

    public function test_internal_users_can_visit_the_dashboard()
    {
        $user = User::factory()->create(['role' => UserRole::Admin]);
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_internal_users_can_open_support_center(): void
    {
        $this->withoutVite();

        $user = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($user)
            ->get(route('admin.support.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/support/index'));
    }

    public function test_support_center_uses_role_specific_knowledge_base(): void
    {
        $this->withoutVite();

        $roles = [
            [UserRole::Admin, 'admin'],
            [UserRole::RegionalAdmin, 'regional_admin'],
            [UserRole::Operator, 'operator'],
            [UserRole::Viewer, 'viewer'],
            [UserRole::ShopPartner, 'shop_partner'],
            [UserRole::Sponsor, 'sponsor'],
            [UserRole::HubManager, 'hub_manager'],
            [UserRole::Visitor, 'visitor'],
        ];

        foreach ($roles as [$role, $expectedKey]) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)
                ->get(route('admin.support.page'))
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page
                    ->component('admin/support/index')
                    ->where('support.roleContext.key', $expectedKey)
                    ->has('support.promptGroups.0.prompts', 4)
                    ->has('support.promptGroups.1.prompts', 4)
                    ->has('support.promptGroups.2.prompts', 4)
                    ->has('support.supportPriorities', 4)
                    ->has('support.quickActions', 4)
                    ->has('support.checklist', 6)
                    ->has('support.handoffNotes', 2));
        }
    }

    public function test_support_knowledge_base_covers_every_role_without_duplicate_questions(): void
    {
        $knowledgeBase = app(SupportKnowledgeBaseService::class);

        foreach (UserRole::cases() as $role) {
            $user = User::factory()->make(['role' => $role]);
            $support = $knowledgeBase->forUser($user);
            $questions = collect($support['promptGroups'])
                ->flatMap(fn (array $group) => collect($group['prompts'])->pluck('question'));

            $this->assertNotEmpty($support['roleContext']['key'], $role->value);
            $this->assertGreaterThanOrEqual(3, count($support['promptGroups']), $role->value);
            $this->assertGreaterThanOrEqual(12, $questions->count(), $role->value);
            $this->assertSame($questions->count(), $questions->unique()->count(), $role->value);
            $this->assertGreaterThanOrEqual(4, count($support['supportPriorities']), $role->value);
            $this->assertGreaterThanOrEqual(4, count($support['quickActions']), $role->value);
            $this->assertGreaterThanOrEqual(6, count($support['checklist']), $role->value);
            $this->assertGreaterThanOrEqual(2, count($support['handoffNotes']), $role->value);
        }
    }

    public function test_support_operational_sections_are_role_specific(): void
    {
        $knowledgeBase = app(SupportKnowledgeBaseService::class);
        $signatures = [];

        foreach (UserRole::cases() as $role) {
            $user = User::factory()->make(['role' => $role]);
            $support = $knowledgeBase->forUser($user);

            $signatures[$role->value] = [
                'priorities' => implode('|', $support['supportPriorities']),
                'actions' => collect($support['quickActions'])->pluck('title')->implode('|'),
                'checklist' => implode('|', $support['checklist']),
                'handoff' => implode('|', $support['handoffNotes']),
            ];
        }

        foreach (['priorities', 'actions', 'checklist', 'handoff'] as $section) {
            $sectionSignatures = collect($signatures)->pluck($section);

            $this->assertSame(
                $sectionSignatures->count(),
                $sectionSignatures->unique()->count(),
                $section,
            );
        }
    }

    public function test_sponsor_support_links_stay_on_sponsor_accessible_surfaces(): void
    {
        $knowledgeBase = app(SupportKnowledgeBaseService::class);
        $support = $knowledgeBase->forUser(User::factory()->make(['role' => UserRole::Sponsor]));
        $allowedPrefixes = ['/sponsor/dashboard', '/admin/support', '/games/ecopark-treasure'];

        $links = collect($support['promptGroups'])
            ->flatMap(fn (array $group) => collect($group['prompts'])->pluck('routeHref'))
            ->merge(collect($support['quickActions'])->pluck('href'))
            ->filter()
            ->values();

        $this->assertNotEmpty($links);

        foreach ($links as $link) {
            $this->assertTrue(
                collect($allowedPrefixes)->contains(fn (string $prefix) => str_starts_with((string) $link, $prefix)),
                (string) $link,
            );
        }

        $this->assertFalse($links->contains('/partner/dashboard'));
        $this->assertFalse($links->contains('/partner/ads'));
    }

    public function test_central_and_regional_admins_have_expanded_support_coverage(): void
    {
        $knowledgeBase = app(SupportKnowledgeBaseService::class);

        foreach ([UserRole::Admin, UserRole::RegionalAdmin] as $role) {
            $user = User::factory()->make(['role' => $role]);
            $support = $knowledgeBase->forUser($user);
            $questions = collect($support['promptGroups'])
                ->flatMap(fn (array $group) => collect($group['prompts'])->pluck('question'));

            $this->assertGreaterThanOrEqual(6, count($support['promptGroups']), $role->value);
            $this->assertGreaterThanOrEqual(24, $questions->count(), $role->value);
            $this->assertTrue($questions->contains(fn (string $question) => str_contains($question, 'منطقه') || str_contains($question, 'مرکزی')), $role->value);
        }
    }

    public function test_regional_admin_can_observe_without_central_or_operator_mutations(): void
    {
        $this->withoutVite();

        $user = User::factory()->create(['role' => UserRole::RegionalAdmin]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('admin.access-scopes.page'))
            ->assertOk();

        $this->actingAs($user)
            ->post(route('admin.access-scopes.store'), [])
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.demo-cycle.run-stress-demo'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin.mission-blueprints.page'))
            ->assertForbidden();
    }

    public function test_regional_admin_dashboard_is_limited_to_assigned_region(): void
    {
        $this->withoutVite();
        $this->seed(PilotLocationSeeder::class);

        $outOfRegionVenue = Venue::query()->create([
            'code' => 'isfahan-demo-venue',
            'name' => 'مکان نمونه اصفهان',
            'city' => 'اصفهان',
            'status' => RecordStatus::Active,
            'profile_status' => RecordStatus::Active,
            'metadata' => ['is_test' => true],
        ]);
        Campaign::query()->create([
            'venue_id' => $outOfRegionVenue->id,
            'code' => 'isfahan-out-of-scope-campaign',
            'name' => 'کمپین خارج از محدوده تهران',
            'campaign_type' => 'pilot_visit',
            'status' => RecordStatus::Active,
            'start_at' => '2026-06-20 00:00:00',
            'end_at' => '2027-03-20 23:59:59',
            'metadata' => ['is_test' => true],
        ]);

        $user = User::factory()->create(['role' => UserRole::RegionalAdmin]);
        UserAccessScope::query()->create([
            'user_id' => $user->id,
            'role_key' => 'regional_admin',
            'scope_type' => 'region',
            'scope_id' => 'تهران',
            'status' => RecordStatus::Active,
            'metadata' => ['source' => 'test'],
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboard')
                ->where('scopeSummary.isGlobal', false)
                ->where('scopeSummary.regions.0', 'تهران')
                ->where('stats.venues', 1)
                ->where('stats.activeCampaigns', 1)
                ->where('stats.activeQrCodes', 1)
                ->where('stats.activeMissions', 4)
                ->has('campaignPerformance', 1)
                ->where('campaignPerformance.0.code', 'ecopark-pilot-1405'));
    }

    public function test_internal_users_can_open_demo_cycle_page(): void
    {
        $this->withoutVite();
        $this->seed(PilotLocationSeeder::class);

        $user = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($user)
            ->get(route('admin.demo-cycle.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/demo-cycle/index')
                ->where('summary.stagesCount', 5)
                ->has('stages', 5)
                ->has('stageHealth', 4)
                ->where('demoStressPlan.title', 'دموی فشار از ارزیابی مکان تا اجرا')
                ->where('demoStressPlan.summary.totalCount', 11)
                ->where('demoStressPlan.items.0.key', 'venue')
                ->where('demoStressPlan.items.1.key', 'blueprint')
                ->where('demoStressPlan.items.9.key', 'redemption')
                ->where('demoStressPlan.items.9.actionHref', '/partner/dashboard?campaign=ecopark-pilot-1405')
                ->where('executionReport.isExecuted', false)
                ->where('executionReport.action.href', '/admin/demo-cycle/run-stress-demo')
                ->has('executionReport.timeline', 9)
                ->has('commercialPackages', 3));
    }

    public function test_admin_can_run_full_demo_cycle_from_demo_page(): void
    {
        $this->withoutVite();
        $this->seed(PilotLocationSeeder::class);

        $user = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($user)
            ->post(route('admin.demo-cycle.run-stress-demo'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(1, UserReward::query()
            ->whereHas('campaign', fn ($query) => $query->where('code', 'ecopark-online-treasure-map-game-campaign'))
            ->count());
        $this->assertSame(1, RewardRedemption::query()->where('status', 'confirmed')->count());

        $this->actingAs($user)
            ->get(route('admin.demo-cycle.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/demo-cycle/index')
                ->where('executionReport.isExecuted', true)
                ->where('executionReport.campaign.code', 'ecopark-online-treasure-map-game-campaign')
                ->where('executionReport.roi.roiPercent', 71)
                ->where('executionReport.roi.redemptionRate', 100)
                ->where('executionReport.metrics.5.label', 'مصرف تاییدشده')
                ->where('executionReport.metrics.5.value', 1)
                ->where('executionReport.latestRedemption.status', 'confirmed')
                ->has('executionReport.timeline', 9)
                ->where('executionReport.timeline.8.key', 'roi')
                ->where('executionReport.timeline.8.status', 'complete'));
    }

    public function test_internal_users_can_open_commercialization_page(): void
    {
        $this->withoutVite();

        $user = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($user)
            ->get(route('admin.commercialization.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/commercialization/index')
                ->where('summary.status', 'آماده تبدیل دمو به بسته فروش')
                ->has('salesMetrics', 9)
                ->has('packages', 3)
                ->has('roiCards', 3)
                ->has('salesPipeline', 5)
                ->has('documents', 5)
                ->has('pricingTiers', 3)
                ->has('salesAssets', 5)
                ->has('leadTargets', 4)
                ->where('finalDemoReport.isExecuted', false)
                ->has('finalDemoReport.audiences', 3)
                ->has('nextActions', 5));
    }

    public function test_commercialization_page_surfaces_final_demo_report_after_full_demo_run(): void
    {
        $this->withoutVite();
        $this->seed(PilotLocationSeeder::class);

        $user = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($user)
            ->post(route('admin.demo-cycle.run-stress-demo'))
            ->assertRedirect();

        $this->actingAs($user)
            ->get(route('admin.commercialization.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/commercialization/index')
                ->where('finalDemoReport.isExecuted', true)
                ->where('finalDemoReport.campaignCode', 'ecopark-online-treasure-map-game-campaign')
                ->where('finalDemoReport.summary.0.label', 'بازدید ثبت‌شده')
                ->where('finalDemoReport.summary.0.value', 1)
                ->where('finalDemoReport.summary.3.label', 'مصرف تاییدشده')
                ->where('finalDemoReport.summary.3.value', 1)
                ->where('finalDemoReport.roi.roiPercent', 71)
                ->where('finalDemoReport.roi.redemptionRate', 100)
                ->has('finalDemoReport.audiences', 3)
                ->where('finalDemoReport.audiences.0.title', 'خلاصه مدیر مکان')
                ->where('finalDemoReport.audiences.1.title', 'خلاصه اسپانسر')
                ->where('finalDemoReport.audiences.2.title', 'خلاصه فروشگاه'));
    }

    public function test_visitor_is_redirected_to_participant_dashboard(): void
    {
        $visitor = User::factory()->create(['role' => UserRole::Visitor]);

        $this->actingAs($visitor)
            ->get(route('dashboard'))
            ->assertRedirect(route('participant.dashboard'));
    }

    public function test_default_users_do_not_get_internal_dashboard_access(): void
    {
        $user = User::factory()->create();

        $this->assertSame(UserRole::Visitor, $user->role);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('participant.dashboard'));

        $this->actingAs($user)
            ->get(route('admin.demo-cycle.page'))
            ->assertForbidden();
    }

    public function test_dashboard_shows_pilot_operational_stats(): void
    {
        $this->withoutVite();
        $this->seed([ConsentVersionSeeder::class, PilotLocationSeeder::class]);

        $user = User::factory()->create(['role' => UserRole::Admin]);
        $version = ConsentVersion::query()->where('is_active', true)->firstOrFail();

        $this->actingAs($user)->postJson('/api/v1/consents/accept', [
            'consentVersionId' => $version->id,
            'source' => 'qr_landing',
            'sourceQrCode' => PilotLocationSeeder::DEMO_QR_CODE,
        ])->assertCreated();
        $visit = Visit::query()->where('user_id', $user->id)->firstOrFail();
        $entryMission = MissionInstance::query()->where('code', 'scan-entry-qr')->firstOrFail();
        $partnerRewardMission = MissionInstance::query()->where('code', 'discover-route-guide')->firstOrFail();

        $this->actingAs($user)
            ->post(route('visits.missions.complete', [$visit, $entryMission]))
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('visits.missions.complete', [$visit, $partnerRewardMission]))
            ->assertRedirect();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboard')
                ->where('stats.venues', 3)
                ->where('stats.activeQrCodes', 1)
                ->where('stats.consents', 1)
                ->where('stats.scans', 1)
                ->where('stats.acceptedScans', 1)
                ->where('stats.visits', 1)
                ->where('stats.activeCampaigns', 1)
                ->where('stats.activeMissions', 4)
                ->where('stats.missionCompletions', 2)
                ->where('stats.issuedRewards', 2)
                ->where('stats.pendingRedemptions', 1)
                ->where('stats.confirmedRedemptions', 0)
                ->has('latestVisits', 1)
                ->has('latestRedemptions', 1)
                ->where('latestRedemptions.0.status', 'pending')
                ->where('latestRedemptions.0.campaignCode', 'ecopark-pilot-1405')
                ->has('latestRedemptions.0.redemptionCode')
                ->has('latestRedemptions.0.partnerName')
                ->has('operationalAlerts', 1)
                ->where('operationalAlerts.0.severity', 'attention')
                ->where('operationalAlerts.0.actionHref', '/partner/dashboard?campaign=ecopark-pilot-1405')
                ->has('campaignPerformance', 1)
                ->where('campaignPerformance.0.visits', 1)
                ->where('campaignPerformance.0.completedMissions', 2)
                ->where('campaignPerformance.0.rewards', 2)
                ->where('campaignPerformance.0.pendingRedemptions', 1)
                ->where('campaignPerformance.0.confirmedRedemptions', 0)
                ->where('campaignPerformance.0.progressPercent', 50));
    }
}
