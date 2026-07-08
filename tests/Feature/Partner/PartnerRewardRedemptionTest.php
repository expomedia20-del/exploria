<?php

namespace Tests\Feature\Partner;

use App\Enums\UserRole;
use App\Models\MissionInstance;
use App\Models\MissionTemplate;
use App\Models\PartnerAccount;
use App\Models\QrCode;
use App\Models\RewardDefinition;
use App\Models\RewardInventoryAllocation;
use App\Models\RewardRedemption;
use App\Models\User;
use App\Models\Visit;
use App\Services\MissionRewardBlueprintService;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PartnerRewardRedemptionTest extends TestCase
{
    use RefreshDatabase;

    private User $visitor;

    private Visit $visit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PilotLocationSeeder::class);

        $this->visitor = User::factory()->create(['role' => UserRole::Visitor]);
        $qr = QrCode::query()->firstOrFail();
        $this->visit = Visit::query()->create([
            'user_id' => $this->visitor->id,
            'qr_code_id' => $qr->id,
            'venue_id' => $qr->venue_id,
            'touchpoint_id' => $qr->touchpoint_id,
            'campaign_id' => $qr->campaign_id,
            'source' => 'qr_landing',
            'status' => 'confirmed',
            'occurred_at' => now(),
        ]);
    }

    public function test_partner_can_open_dashboard_for_own_rewards(): void
    {
        $this->withoutVite();
        $partnerUser = User::query()->where('email', 'cafe.eco@example.test')->firstOrFail();

        $this->actingAs($partnerUser)
            ->get(route('partner.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('partner/dashboard')
                ->where('partner.code', 'cafe-eco')
                ->where('stats.rewardDefinitions', 1)
                ->where('proposalContext.campaign.code', 'ecopark-pilot-1405')
                ->has('proposalContext.rewardTiers')
                ->has('rewardDefinitions', 1));
    }

    public function test_admin_can_open_partner_dashboard_for_support(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->get(route('partner.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('partner/dashboard')
                ->where('partner.code', 'cafe-eco')
                ->has('rewardDefinitions'));

        $this->actingAs($admin)
            ->getJson(route('partner.dashboard.index'))
            ->assertOk()
            ->assertJsonPath('data.partner.code', 'cafe-eco');
    }


    public function test_partner_reward_completion_creates_pending_redemption(): void
    {
        $this->completeMission('scan-entry-qr');
        $this->completeMission('discover-route-guide');

        $redemption = RewardRedemption::query()
            ->with(['partnerAccount', 'userReward.rewardDefinition'])
            ->firstOrFail();

        $this->assertSame('cafe-eco', $redemption->partnerAccount->code);
        $this->assertSame('small-drink-coupon', $redemption->userReward->rewardDefinition->code);
        $this->assertSame('pending', $redemption->status);
    }

    public function test_partner_can_confirm_own_redemption_code(): void
    {
        $this->completeMission('scan-entry-qr');
        $this->completeMission('discover-route-guide');

        $partnerUser = User::query()->where('email', 'cafe.eco@example.test')->firstOrFail();
        $redemption = RewardRedemption::query()->firstOrFail();

        $this->actingAs($partnerUser)
            ->postJson(route('partner.redemptions.api.confirm'), [
                'redemption_code' => strtolower($redemption->redemption_code),
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'confirmed');

        $redemption->refresh();

        $this->assertSame('confirmed', $redemption->status);
        $this->assertSame('redeemed', $redemption->userReward->status);
    }

    public function test_sponsor_inventory_is_reserved_and_redeemed_through_partner_code(): void
    {
        $campaign = $this->visit->campaign;
        $partner = PartnerAccount::query()->where('code', 'cafe-eco')->firstOrFail();
        $reward = RewardDefinition::query()->create([
            'campaign_id' => $campaign->id,
            'venue_id' => $campaign->venue_id,
            'code' => 'sponsor-inventory-redemption',
            'name' => 'Sponsor Inventory Redemption',
            'reward_type' => 'sponsor_reward',
            'point_cost' => 0,
            'stock_quantity' => 3,
            'status' => 'active',
            'metadata' => [
                'source' => 'admin_sponsor_activation',
                'approval_status' => 'approved',
                'availability_status' => 'active',
                'target_partner_account_ids' => [$partner->id],
                'partner_allocations' => [
                    ['partner_account_id' => $partner->id, 'quantity' => 3],
                ],
            ],
        ]);
        $allocation = RewardInventoryAllocation::query()->create([
            'reward_definition_id' => $reward->id,
            'campaign_id' => $campaign->id,
            'partner_account_id' => $partner->id,
            'allocated_quantity' => 3,
            'reserved_quantity' => 0,
            'redeemed_quantity' => 0,
            'status' => 'active',
            'metadata' => ['source' => 'test'],
        ]);
        $template = MissionTemplate::query()->create([
            'code' => 'sponsor-inventory-redemption-template',
            'title' => 'Sponsor Inventory Redemption Template',
            'description' => 'Issues a sponsor redemption code.',
            'mission_type' => 'challenge',
            'trigger_type' => 'manual',
            'point_value' => 20,
            'status' => 'active',
        ]);
        MissionInstance::query()->create([
            'mission_template_id' => $template->id,
            'campaign_id' => $campaign->id,
            'venue_id' => $campaign->venue_id,
            'code' => 'sponsor-inventory-redemption-mission',
            'title_override' => 'Sponsor inventory redemption mission',
            'status' => 'active',
            'metadata' => [
                'source' => 'test',
                'reward_code' => $reward->code,
            ],
        ]);

        $this->completeMission('sponsor-inventory-redemption-mission');

        $redemption = RewardRedemption::query()
            ->with(['partnerAccount', 'userReward.rewardDefinition'])
            ->whereHas('userReward', fn ($query) => $query->where('reward_definition_id', $reward->id))
            ->firstOrFail();

        $this->assertSame('pending', $redemption->status);
        $this->assertSame('cafe-eco', $redemption->partnerAccount->code);
        $this->assertSame($allocation->id, $redemption->metadata['reward_inventory_allocation_id']);
        $this->assertSame(1, $allocation->fresh()->reserved_quantity);
        $this->assertSame(0, $allocation->fresh()->redeemed_quantity);

        $partnerUser = User::query()->where('email', 'cafe.eco@example.test')->firstOrFail();
        $this->actingAs($partnerUser)
            ->postJson(route('partner.redemptions.api.confirm'), [
                'redemption_code' => $redemption->redemption_code,
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'confirmed');

        $allocation->refresh();
        $this->assertSame(0, $allocation->reserved_quantity);
        $this->assertSame(1, $allocation->redeemed_quantity);
        $this->assertSame('redeemed', $redemption->fresh()->userReward->status);
    }

    public function test_other_partner_cannot_confirm_foreign_redemption_code(): void
    {
        $this->completeMission('scan-entry-qr');
        $this->completeMission('discover-route-guide');

        $otherPartnerUser = User::query()->where('email', 'ravaq.store@example.test')->firstOrFail();
        $redemption = RewardRedemption::query()->firstOrFail();

        $this->actingAs($otherPartnerUser)
            ->postJson(route('partner.redemptions.api.confirm'), [
                'redemption_code' => $redemption->redemption_code,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('redemption_code');

        $this->assertSame('pending', $redemption->fresh()->status);
    }

    public function test_partner_dashboard_api_reports_pending_and_confirmed_redemptions(): void
    {
        $this->completeMission('scan-entry-qr');
        $this->completeMission('discover-route-guide');

        $partnerUser = User::query()->where('email', 'cafe.eco@example.test')->firstOrFail();
        $redemption = RewardRedemption::query()->firstOrFail();

        $this->actingAs($partnerUser)
            ->getJson(route('partner.dashboard.index'))
            ->assertOk()
            ->assertJsonPath('data.stats.pendingRedemptions', 1)
            ->assertJsonPath('data.redemptions.0.redemptionCode', $redemption->redemption_code);

        $this->actingAs($partnerUser)
            ->postJson(route('partner.redemptions.api.confirm'), [
                'redemption_code' => $redemption->redemption_code,
            ])
            ->assertOk();

        $this->actingAs($partnerUser)
            ->getJson(route('partner.dashboard.index'))
            ->assertOk()
            ->assertJsonPath('data.stats.pendingRedemptions', 0)
            ->assertJsonPath('data.stats.confirmedRedemptions', 1);
    }

    public function test_partner_dashboard_reports_own_ad_requests(): void
    {
        $partnerUser = User::query()->where('email', 'cafe.eco@example.test')->firstOrFail();

        $this->actingAs($partnerUser)
            ->postJson(route('partner.ads.api.store'), [
                'title' => 'Cafe dashboard ad status',
                'body_copy' => 'Partner dashboard ad summary test.',
                'ad_type' => 'standalone',
                'creative_type' => 'image',
                'placement_type' => 'fixed_display',
            ])
            ->assertCreated();

        $this->actingAs($partnerUser)
            ->getJson(route('partner.dashboard.index'))
            ->assertOk()
            ->assertJsonPath('data.stats.adRequests', 1)
            ->assertJsonPath('data.stats.pendingAds', 1)
            ->assertJsonPath('data.adRequests.0.title', 'Cafe dashboard ad status')
            ->assertJsonPath('data.adRequests.0.placementType', 'fixed_display')
            ->assertJsonPath('data.adRequests.0.placementStatus', 'pending_review');
    }

    public function test_partner_can_submit_offer_for_admin_review(): void
    {
        $partnerUser = User::query()->where('email', 'cafe.eco@example.test')->firstOrFail();
        $campaign = QrCode::query()->firstOrFail()->campaign;
        $rewardStep = $this->rewardStepPayload('silver');

        $this->actingAs($partnerUser)
            ->postJson(route('partner.offers.api.store'), [
                'campaign_id' => $campaign?->id,
                ...$rewardStep,
                'name' => 'Ã˜ÂªÃ˜Â®Ã™ÂÃ›Å’Ã™Â Ã™â€ Ã™Ë†Ã˜Â´Ã›Å’Ã˜Â¯Ã™â€ Ã›Å’ Ã˜Â®Ã˜Â§Ã™â€ Ã™Ë†Ã˜Â§Ã˜Â¯ÃšÂ¯Ã›Å’',
                'reward_type' => 'discount',
                'point_cost' => 250,
                'stock_quantity' => 30,
                'description' => 'Ã˜Â¨Ã˜Â±Ã˜Â§Ã›Å’ Ã˜Â®Ã˜Â§Ã™â€ Ã™Ë†Ã˜Â§Ã˜Â¯Ã™â€¡Ã¢â‚¬Å’Ã™â€¡Ã˜Â§Ã›Å’Ã›Å’ ÃšÂ©Ã™â€¡ Ã™â€¦Ã˜Â³Ã›Å’Ã˜Â± Ã˜Â§ÃšÂ©Ã™Ë†Ã™Â¾Ã˜Â§Ã˜Â±ÃšÂ© Ã˜Â±Ã˜Â§ ÃšÂ©Ã˜Â§Ã™â€¦Ã™â€ž Ã™â€¦Ã›Å’Ã¢â‚¬Å’ÃšÂ©Ã™â€ Ã™â€ Ã˜Â¯.',
                'terms' => 'Ã™â€¡Ã˜Â± ÃšÂ©Ã˜Â§Ã˜Â±Ã˜Â¨Ã˜Â± Ã™ÂÃ™â€šÃ˜Â· Ã›Å’ÃšÂ© Ã˜Â¨Ã˜Â§Ã˜Â±.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'draft');

        $offer = RewardDefinition::query()
            ->where('name', 'Ã˜ÂªÃ˜Â®Ã™ÂÃ›Å’Ã™Â Ã™â€ Ã™Ë†Ã˜Â´Ã›Å’Ã˜Â¯Ã™â€ Ã›Å’ Ã˜Â®Ã˜Â§Ã™â€ Ã™Ë†Ã˜Â§Ã˜Â¯ÃšÂ¯Ã›Å’')
            ->with('partnerAccount')
            ->firstOrFail();

        $this->assertSame('cafe-eco', $offer->partnerAccount->code);
        $this->assertSame('draft', $offer->status->value);
        $this->assertSame($campaign?->id, $offer->campaign_id);
        $this->assertSame('pending_review', $offer->metadata['approval_status']);
        $this->assertSame('partner_offer_submission', $offer->metadata['source']);
        $this->assertSame($rewardStep['cycle_step_index'], $offer->metadata['cycle_step_index']);
        $this->assertSame($rewardStep['reward_tier'], $offer->metadata['reward_tier']);
        $this->assertSame($rewardStep['reward_option'], $offer->metadata['reward_option']);
    }

    public function test_partner_dashboard_uses_campaign_query_for_draft_reward_proposals(): void
    {
        $partnerUser = User::query()->where('email', 'cafe.eco@example.test')->firstOrFail();
        $seedCampaign = QrCode::query()->firstOrFail()->campaign;
        $draftCampaign = $seedCampaign?->replicate(['id', 'created_at', 'updated_at']);

        $this->assertNotNull($draftCampaign);

        $draftCampaign->forceFill([
            'code' => 'draft-partner-proposal',
            'name' => 'Draft partner proposal',
            'status' => \App\Enums\RecordStatus::Draft,
        ])->save();

        $this->actingAs($partnerUser)
            ->get(route('partner.dashboard', ['campaign' => 'draft-partner-proposal']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('proposalContext.campaign.code', 'draft-partner-proposal')
                ->where('proposalContext.campaign.status', 'draft'));

        $this->actingAs($partnerUser)
            ->postJson(route('partner.offers.api.store'), [
                'campaign_id' => $draftCampaign->id,
                ...$this->rewardStepPayload('gold'),
                'name' => 'Draft campaign partner offer',
                'reward_type' => 'discount',
                'point_cost' => 300,
                'stock_quantity' => 15,
            ])
            ->assertCreated();

        $this->assertDatabaseHas('reward_definitions', [
            'campaign_id' => $draftCampaign->id,
            'name' => 'Draft campaign partner offer',
        ]);
    }

    public function test_partner_can_update_own_store_profile(): void
    {
        $partnerUser = User::query()->where('email', 'cafe.eco@example.test')->firstOrFail();

        $this->actingAs($partnerUser)
            ->patchJson(route('partner.profile.api.update'), [
                'name' => 'Cafe Eco Updated',
                'contact_name' => 'Cafe Operations Lead',
                'contact_mobile' => '09123334444',
                'category' => 'beverage',
                'operating_notes' => 'Open during the family route pilot hours.',
                'display_visibility' => true,
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Cafe Eco Updated')
            ->assertJsonPath('data.category', 'beverage')
            ->assertJsonPath('data.displayVisibility', true);

        $this->actingAs($partnerUser)
            ->getJson(route('partner.dashboard.index'))
            ->assertOk()
            ->assertJsonPath('data.partner.name', 'Cafe Eco Updated')
            ->assertJsonPath('data.partner.contactMobile', '09123334444')
            ->assertJsonPath('data.partner.operatingNotes', 'Open during the family route pilot hours.');
    }

    public function test_partner_can_update_own_offer_inventory_and_pause_status(): void
    {
        $offer = $this->submitPartnerOffer();
        $partnerUser = User::query()->where('email', 'cafe.eco@example.test')->firstOrFail();
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->postJson(route('admin.rewards.api.approve', $offer))
            ->assertOk();

        $this->actingAs($partnerUser)
            ->patchJson(route('partner.offers.api.update', $offer), [
                'stock_quantity' => 25,
                'point_cost' => 140,
                'availability_status' => 'paused',
                'available_from' => now()->addDay()->toIso8601String(),
                'available_until' => now()->addDays(10)->toIso8601String(),
                'description' => 'Updated inventory for pilot demand.',
                'terms' => 'Valid once for each visitor.',
            ])
            ->assertOk()
            ->assertJsonPath('data.stockQuantity', 25)
            ->assertJsonPath('data.pointCost', 140)
            ->assertJsonPath('data.status', 'inactive')
            ->assertJsonPath('data.availabilityStatus', 'paused')
            ->assertJsonPath('data.terms', 'Valid once for each visitor.');

        $offer->refresh();

        $this->assertSame('inactive', $offer->status->value);
        $this->assertSame(25, $offer->stock_quantity);
        $this->assertSame('paused', $offer->metadata['availability_status']);
    }

    public function test_partner_cannot_update_foreign_offer_inventory(): void
    {
        $offer = $this->submitPartnerOffer('ravaq.store@example.test', 'Foreign ravaq offer');
        $partnerUser = User::query()->where('email', 'cafe.eco@example.test')->firstOrFail();

        $this->actingAs($partnerUser)
            ->patchJson(route('partner.offers.api.update', $offer), [
                'stock_quantity' => 99,
                'point_cost' => 90,
                'availability_status' => 'active',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('reward');

        $this->assertSame(10, $offer->fresh()->stock_quantity);
    }

    public function test_admin_can_approve_partner_offer(): void
    {
        $offer = $this->submitPartnerOffer();
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->postJson(route('admin.rewards.api.approve', $offer))
            ->assertOk()
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.approvalStatus', 'approved');

        $offer->refresh();

        $this->assertSame('active', $offer->status->value);
        $this->assertSame('approved', $offer->metadata['approval_status']);
        $this->assertSame($admin->id, $offer->metadata['approved_by_user_id']);
    }

    public function test_hub_manager_can_reject_partner_offer_and_viewer_cannot_approve_it(): void
    {
        $offer = $this->submitPartnerOffer('ravaq.store@example.test', 'Ravaq scoped partner offer');
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);

        $this->actingAs($viewer)
            ->postJson(route('admin.rewards.api.approve', $offer))
            ->assertForbidden();

        $manager = User::query()->where('email', 'ravaq.manager@example.test')->firstOrFail();

        $this->actingAs($manager)
            ->postJson(route('admin.rewards.api.reject', $offer), [
                'notes' => 'Offer needs clearer redemption terms.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'inactive')
            ->assertJsonPath('data.approvalStatus', 'rejected')
            ->assertJsonPath('data.reviewNotes', 'Offer needs clearer redemption terms.');

        $offer->refresh();

        $this->assertSame('inactive', $offer->status->value);
        $this->assertSame('rejected', $offer->metadata['approval_status']);
        $this->assertSame('Offer needs clearer redemption terms.', $offer->metadata['review_notes']);
    }

    public function test_admin_can_request_partner_offer_revision(): void
    {
        $offer = $this->submitPartnerOffer();
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->postJson(route('admin.rewards.api.revision', $offer), [
                'notes' => 'Please clarify which item is offered for this campaign step.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.approvalStatus', 'revision_requested')
            ->assertJsonPath('data.reviewNotes', 'Please clarify which item is offered for this campaign step.');

        $offer->refresh();

        $this->assertSame('draft', $offer->status->value);
        $this->assertSame('revision_requested', $offer->metadata['approval_status']);
        $this->assertSame($admin->id, $offer->metadata['revision_requested_by_user_id']);
    }

    public function test_hub_manager_cannot_review_offer_outside_managed_hub(): void
    {
        $offer = $this->submitPartnerOffer('family.sponsor@example.test', 'Science hub sponsor offer');
        $manager = User::query()->where('email', 'ravaq.manager@example.test')->firstOrFail();

        $this->actingAs($manager)
            ->postJson(route('admin.rewards.api.approve', $offer))
            ->assertForbidden();

        $this->assertSame('draft', $offer->fresh()->status->value);
    }

    public function test_admin_mission_registry_surfaces_pending_partner_offer_review_details(): void
    {
        $offer = $this->submitPartnerOffer();
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)
            ->getJson(route('admin.missions.index'))
            ->assertOk()
            ->assertJsonPath('data.stats.pendingRewards', 1);

        $reward = collect($response->json('data.rewards'))
            ->firstWhere('id', $offer->id);

        $this->assertNotNull($reward);
        $this->assertSame('pending_review', $reward['approvalStatus']);
        $this->assertSame('active', $reward['availabilityStatus']);
        $this->assertSame('partner_offer_submission', $reward['source']);
        $this->assertSame('bronze', $reward['rewardTier']);
        $this->assertSame($offer->metadata['reward_option'], $reward['rewardOption']);
        $this->assertSame('cafe-eco', $reward['partner']['code']);
        $this->assertArrayHasKey('submittedAt', $reward);
    }


    private function completeMission(string $code): void
    {
        $mission = MissionInstance::query()->where('code', $code)->firstOrFail();

        $this->actingAs($this->visitor)
            ->post(route('visits.missions.complete', [$this->visit, $mission]))
            ->assertRedirect();
    }

    private function submitPartnerOffer(string $email = 'cafe.eco@example.test', string $name = 'Ã™Â¾Ã›Å’Ã˜Â´Ã™â€ Ã™â€¡Ã˜Â§Ã˜Â¯ Ã˜ÂªÃ˜Â³Ã˜ÂªÃ›Å’ Ã™ÂÃ˜Â±Ã™Ë†Ã˜Â´ÃšÂ¯Ã˜Â§Ã™â€¡'): RewardDefinition
    {
        $partnerUser = User::query()->where('email', $email)->firstOrFail();

        $this->actingAs($partnerUser)
            ->postJson(route('partner.offers.api.store'), [
                ...$this->rewardStepPayload('bronze'),
                'name' => $name,
                'reward_type' => 'partner_coupon',
                'point_cost' => 120,
                'stock_quantity' => 10,
            ])
            ->assertCreated();

        return RewardDefinition::query()->where('name', $name)->firstOrFail();
    }

    /** @return array{cycle_step_index: int, cycle_step_label: string, reward_tier: string, reward_option: string|null} */
    private function rewardStepPayload(string $tierKey): array
    {
        $campaign = QrCode::query()->firstOrFail()->campaign;
        $blueprintCode = $campaign?->metadata['blueprint_code'] ?? null;
        if (! is_string($blueprintCode) && $campaign?->campaign_type === 'pilot_visit') {
            $blueprintCode = 'ecopark-pilot-treasure-route';
        }
        $blueprint = app(MissionRewardBlueprintService::class)->handoff(is_string($blueprintCode) ? $blueprintCode : null);
        $step = collect($blueprint['missionPlan'] ?? [])->firstWhere('rewardTier', $tierKey)
            ?? collect($blueprint['missionPlan'] ?? [])->first();
        $tier = collect($blueprint['rewardDesign']['tiers'] ?? [])->firstWhere('tierKey', $step['rewardTier'] ?? $tierKey);

        return [
            'cycle_step_index' => (int) ($step['index'] ?? 1),
            'cycle_step_label' => (string) ($step['userStep'] ?? 'campaign step'),
            'reward_tier' => (string) ($step['rewardTier'] ?? $tierKey),
            'reward_option' => $tier['options'][0] ?? null,
        ];
    }
}
