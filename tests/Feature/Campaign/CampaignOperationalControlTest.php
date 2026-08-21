<?php

namespace Tests\Feature\Campaign;

use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\EventLog;
use App\Models\MissionInstance;
use App\Models\QrCode;
use App\Models\RewardDefinition;
use App\Models\User;
use App\Models\Visit;
use App\Services\CampaignBuilderService;
use App\Services\MissionFlowService;
use App\Services\ProductionReadinessService;
use App\Services\RewardGovernanceService;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CampaignOperationalControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PilotLocationSeeder::class);
    }

    public function test_operator_can_pause_only_the_affected_campaign_with_incident_linkage_and_audit(): void
    {
        $operator = User::factory()->create(['role' => UserRole::Operator]);
        $campaign = $this->campaign();
        $qr = $this->qr();

        $this->assertTrue($qr->load(['venue', 'touchpoint', 'campaign'])->isAvailableForLanding());

        $this->actingAs($operator)
            ->postJson(route('admin.campaigns.api.pause', $campaign), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['reason', 'incident_reference']);

        $this->actingAs($operator)
            ->postJson(route('admin.campaigns.api.pause', $campaign), [
                'reason' => 'اختلال کنترل‌شده در مسیر QR ورودی کمپین مشاهده شد.',
                'incident_reference' => 'INC-2026-001',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'inactive')
            ->assertJsonPath('data.operationalControl.state', 'paused')
            ->assertJsonPath('data.operationalControl.scope', 'campaign')
            ->assertJsonPath('data.operationalControl.incident_reference', 'INC-2026-001');

        $campaign->refresh();
        $this->assertSame(RecordStatus::Inactive, $campaign->status);
        $this->assertTrue($campaign->isOperationallyPaused());
        $this->assertSame($operator->id, $campaign->operationalControl()['paused_by_user_id']);
        $this->assertNotEmpty($campaign->operationalControl()['paused_at']);
        $this->assertFalse($qr->refresh()->load(['venue', 'touchpoint', 'campaign'])->isAvailableForLanding());
        $this->get(route('scan.landing', $qr->code))->assertNotFound();

        $audit = EventLog::query()->where('event_type', 'audit.campaign_paused')->sole();
        $this->assertSame($operator->id, $audit->actor_user_id);
        $this->assertSame($campaign->id, $audit->campaign_id);
        $this->assertSame('INC-2026-001', $audit->payload_json['incident_reference']);
        $this->assertSame('blocked_by_campaign_status', $audit->payload_json['qr_coordination']);

        $readinessCheck = collect(app(ProductionReadinessService::class)->report('staging')['checks'])
            ->firstWhere('key', 'operational_pause');
        $this->assertSame('fail', $readinessCheck['status']);
        $this->assertSame([$campaign->code], $readinessCheck['actual']['pausedCampaignCodes']);

        $this->withoutVite();
        $this->actingAs($operator)
            ->get(route('admin.campaigns.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('campaigns.0.operationalControl.state', 'paused')
                ->where('campaigns.0.operationalControl.incident_reference', 'INC-2026-001'));
    }

    public function test_resume_requires_admin_matching_incident_corrective_action_recovery_evidence_and_approval(): void
    {
        $operator = User::factory()->create(['role' => UserRole::Operator]);
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $campaign = $this->campaign();

        $this->actingAs($operator)->postJson(route('admin.campaigns.api.pause', $campaign), [
            'reason' => 'اختلال عملیاتی نیازمند توقف موقت کمپین است.',
            'incident_reference' => 'INC-2026-002',
        ])->assertOk();

        $resumePayload = [
            'incident_reference' => 'INC-2026-002',
            'corrective_action' => 'Binding کمپین و QR بازبینی و تنظیم ناسازگار اصلاح شد.',
            'recovery_evidence' => 'Smoke Test مسیر QR تا صفحه ورود با شناسه EVD-002 موفق بود.',
            'approval_note' => 'دامنه اثر بررسی شد و ازسرگیری همین کمپین تأیید می‌شود.',
            'approval_confirmed' => '1',
        ];

        $this->actingAs($operator)
            ->postJson(route('admin.campaigns.api.resume', $campaign), $resumePayload)
            ->assertForbidden();

        $this->actingAs($admin)
            ->postJson(route('admin.campaigns.api.resume', $campaign), [
                'incident_reference' => 'INC-2026-002',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['corrective_action', 'recovery_evidence', 'approval_note', 'approval_confirmed']);

        $this->actingAs($admin)
            ->postJson(route('admin.campaigns.api.resume', $campaign), [
                ...$resumePayload,
                'incident_reference' => 'INC-WRONG',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('incident_reference');

        $this->actingAs($admin)
            ->postJson(route('admin.campaigns.api.resume', $campaign), $resumePayload)
            ->assertOk()
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.operationalControl.state', 'resumed')
            ->assertJsonPath('data.operationalControl.resume_approved_by_user_id', $admin->id);

        $campaign->refresh();
        $this->assertSame(RecordStatus::Active, $campaign->status);
        $this->assertFalse($campaign->isOperationallyPaused());
        $this->assertSame($admin->id, $campaign->operationalControl()['resume_approved_by_user_id']);
        $this->assertNotEmpty($campaign->operationalControl()['resumed_at']);
        $this->assertTrue($this->qr()->load(['venue', 'touchpoint', 'campaign'])->isAvailableForLanding());

        $audit = EventLog::query()->where('event_type', 'audit.campaign_resumed')->sole();
        $this->assertSame($admin->id, $audit->actor_user_id);
        $this->assertSame('INC-2026-002', $audit->payload_json['incident_reference']);
        $this->assertSame($resumePayload['corrective_action'], $audit->payload_json['corrective_action']);
        $this->assertSame($resumePayload['recovery_evidence'], $audit->payload_json['recovery_evidence']);

        $readinessCheck = collect(app(ProductionReadinessService::class)->report('staging')['checks'])
            ->firstWhere('key', 'operational_pause');
        $this->assertSame('pass', $readinessCheck['status']);
    }

    public function test_operational_pause_cannot_be_bypassed_by_general_edit_or_campaign_builder(): void
    {
        $operator = User::factory()->create(['role' => UserRole::Operator]);
        $campaign = $this->campaign();

        $this->actingAs($operator)->postJson(route('admin.campaigns.api.pause', $campaign), [
            'reason' => 'توقف برای بررسی ریسک عملیاتی مسیر کمپین ثبت می‌شود.',
            'incident_reference' => 'INC-2026-003',
        ])->assertOk();

        $this->actingAs($operator)
            ->postJson(route('admin.campaigns.api.store'), [
                'campaign_id' => $campaign->id,
                'venue_id' => $campaign->venue_id,
                'code' => $campaign->code,
                'name' => $campaign->name,
                'campaign_type' => $campaign->campaign_type,
                'status' => RecordStatus::Active->value,
                'start_at' => $campaign->start_at?->toDateTimeString(),
                'end_at' => $campaign->end_at?->toDateTimeString(),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');

        try {
            app(CampaignBuilderService::class)->activate($operator, $campaign->code);
            $this->fail('Operationally paused campaign was activated through the builder.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('campaign', $exception->errors());
        }

        $this->assertTrue($campaign->refresh()->isOperationallyPaused());
        $this->assertSame(RecordStatus::Inactive, $campaign->status);
    }

    public function test_paused_campaign_blocks_existing_mission_progress_and_new_reward_issuance(): void
    {
        $operator = User::factory()->create(['role' => UserRole::Operator]);
        $visitor = User::factory()->create(['role' => UserRole::Visitor]);
        $campaign = $this->campaign();
        $qr = $this->qr();
        $mission = MissionInstance::query()->where('campaign_id', $campaign->id)->where('status', RecordStatus::Active)->firstOrFail();
        $reward = RewardDefinition::query()->where('campaign_id', $campaign->id)->where('status', RecordStatus::Active)->firstOrFail();
        $visit = Visit::query()->create([
            'user_id' => $visitor->id,
            'qr_code_id' => $qr->id,
            'venue_id' => $qr->venue_id,
            'touchpoint_id' => $qr->touchpoint_id,
            'campaign_id' => $campaign->id,
            'source' => 'operational_control_test',
            'status' => 'confirmed',
            'occurred_at' => now(),
        ]);

        $this->actingAs($operator)->postJson(route('admin.campaigns.api.pause', $campaign), [
            'reason' => 'توقف برای جلوگیری از ادامه تجربه و صدور پاداش جدید.',
            'incident_reference' => 'INC-2026-004',
        ])->assertOk();

        try {
            app(MissionFlowService::class)->start($visitor, $visit, $mission);
            $this->fail('Mission progress was allowed while the campaign was paused.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('mission', $exception->errors());
        }

        try {
            app(RewardGovernanceService::class)->issue($visitor, $reward, ['source' => 'operational_control_test']);
            $this->fail('Reward issuance was allowed while the campaign was paused.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('campaign', $exception->errors());
        }

        $this->assertDatabaseMissing('user_mission_progress', ['user_id' => $visitor->id, 'mission_instance_id' => $mission->id]);
        $this->assertDatabaseMissing('user_rewards', ['user_id' => $visitor->id, 'reward_definition_id' => $reward->id]);
    }

    private function campaign(): Campaign
    {
        return Campaign::query()->where('code', 'ecopark-pilot-1405')->firstOrFail();
    }

    private function qr(): QrCode
    {
        return QrCode::query()->where('campaign_id', $this->campaign()->id)->where('status', RecordStatus::Active)->firstOrFail();
    }
}
