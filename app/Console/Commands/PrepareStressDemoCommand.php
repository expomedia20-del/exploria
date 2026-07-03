<?php

namespace App\Console\Commands;

use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\CampaignParticipant;
use App\Models\DisplayDevice;
use App\Models\Hub;
use App\Models\PartnerAccount;
use App\Models\PartnerLocation;
use App\Models\QrCode;
use App\Models\RewardDefinition;
use App\Models\RewardInventoryAllocation;
use App\Models\RewardRedemption;
use App\Models\SponsorAccount;
use App\Models\SponsorProposal;
use App\Models\SponsorProposalActivation;
use App\Models\SponsorProposalItem;
use App\Models\SponsorProposalPartnerAccount;
use App\Models\Touchpoint;
use App\Models\Treasure;
use App\Models\User;
use App\Models\UserMissionProgress;
use App\Models\UserReward;
use App\Models\Venue;
use App\Models\Visit;
use App\Models\Zone;
use App\Services\MissionRewardRegistryService;
use App\Services\SponsorActivationService;
use App\Services\VenueRegistryService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PrepareStressDemoCommand extends Command
{
    protected $signature = 'exploria:prepare-stress-demo
        {--campaign=ecopark-online-treasure-map-game-campaign : Campaign code for the stress demo}
        {--venue=ecopark-abbasabad : Venue code}
        {--execute-visitor : Also create a completed visitor journey, issued reward, and confirmed redemption}';

    protected $description = 'Prepare the full Exploria stress demo from venue evaluation to reward redemption.';

    public function handle(
        SponsorActivationService $sponsors,
        MissionRewardRegistryService $registry,
        VenueRegistryService $venues,
    ): int {
        $campaignCode = Str::lower((string) $this->option('campaign'));
        $venueCode = Str::lower((string) $this->option('venue'));

        $result = DB::transaction(function () use ($campaignCode, $sponsors, $venueCode): array {
            $actor = $this->adminUser();
            $venue = $this->venue($venueCode);
            $zone = $this->zone($venue);
            $entryHub = $this->hub($zone, 'visitor-welcome-hub', 'هاب خوش‌آمدگویی بازدیدکنندگان', 'experience');
            $ravaqHub = $this->hub($zone, 'ravaq-commercial-hub', 'رواق تجاری اکوپارک', 'commercial_ravaq');
            $foodHub = $this->hub($zone, 'foodcourt-family-hub', 'هاب فودکورت و خانواده', 'food_family');
            $scienceHub = $this->hub($zone, 'gonbad-mina-science-hub', 'هاب گنبد مینا و روایت علمی', 'science_story');
            $touchpoint = $this->touchpoint($entryHub);

            $this->completeVenueProfile($venue);

            $campaign = $this->campaign($venue, $campaignCode);
            $this->displayDevice($venue, $entryHub, $touchpoint);

            $partners = collect([
                $this->partner($venue, $foodHub, 'cafe-eco', 'کافه اکو', 'food_reward_point', 'reward_redemption'),
                $this->partner($venue, $ravaqHub, 'ravaq-store', 'فروشگاه X', 'member_shop', 'commercial_activation'),
                $this->partner($venue, $scienceHub, 'family-route-sponsor', 'اسپانسر مسیر خانوادگی', 'sponsor', 'route_sponsor'),
            ]);

            foreach ($partners as $partner) {
                $this->participant($campaign, $partner);
            }

            $this->qrCode($venue, $campaign, $touchpoint);
            $this->partnerReward($campaign, $partners[0]);
            $proposal = $this->sponsorProposal($sponsors, $actor, $campaign, $venue, $partners->take(2)->values());
            $this->manualSponsorTrack($sponsors, $campaign, $venue, $partners[1]);

            Artisan::call('exploria:prepare-demo-cycle', [
                'campaign' => $campaign->code,
                '--reward-step' => 4,
                '--claim-condition' => 'mission_completion',
            ]);

            $campaign->refresh();
            $this->finalTreasure($campaign);

            if ($this->option('execute-visitor')) {
                $this->executeVisitorJourney($campaign, $partners[0]);
            }

            return [
                'venue' => $venue->refresh(),
                'campaign' => $campaign->refresh(),
                'proposal' => $proposal->refresh(),
            ];
        });

        $plan = $venues->list()->firstWhere('code', $result['venue']->code)['demoStressPlan'] ?? null;

        $this->info('Stress demo prepared.');
        $this->line('Venue: '.$result['venue']->name.' / '.$result['venue']->code);
        $this->line('Campaign: '.$result['campaign']->name.' / '.$result['campaign']->code);
        $this->line('Sponsor proposal: '.$result['proposal']->title.' / '.$result['proposal']->code);

        if (is_array($plan)) {
            $this->line('Checklist progress: '.$plan['summary']['progress'].'%');
            $this->line('Next action: '.($plan['nextAction']['title'] ?? 'همه مراحل کامل است'));
        }

        return self::SUCCESS;
    }

    private function adminUser(): User
    {
        return User::query()->updateOrCreate(
            ['email' => 'admin.stress-demo@example.test'],
            ['name' => 'ادمین دموی فشار', 'password' => 'password', 'role' => UserRole::Admin],
        );
    }

    private function venue(string $code): Venue
    {
        return Venue::query()->updateOrCreate(
            ['code' => $code],
            [
                'name' => 'اکوپارک عباس‌آباد',
                'city' => 'تهران',
                'status' => RecordStatus::Active,
                'profile_status' => RecordStatus::Active,
                'metadata' => ['is_demo' => true, 'stress_demo' => true],
            ],
        );
    }

    private function zone(Venue $venue): Zone
    {
        return Zone::query()->updateOrCreate(
            ['venue_id' => $venue->id, 'code' => 'main-entry-zone'],
            ['name' => 'محدوده ورودی اصلی', 'status' => RecordStatus::Active, 'metadata' => ['is_demo' => true, 'stress_demo' => true]],
        );
    }

    private function hub(Zone $zone, string $code, string $name, string $type): Hub
    {
        return Hub::query()->updateOrCreate(
            ['zone_id' => $zone->id, 'code' => $code],
            ['name' => $name, 'hub_type' => $type, 'status' => RecordStatus::Active, 'metadata' => ['is_demo' => true, 'stress_demo' => true]],
        );
    }

    private function touchpoint(Hub $hub): Touchpoint
    {
        return Touchpoint::query()->updateOrCreate(
            ['hub_id' => $hub->id, 'code' => 'stress-demo-entry-qr'],
            [
                'label' => 'استند QR دموی فشار',
                'type' => 'qr_stand',
                'owner_type' => 'venue',
                'status' => RecordStatus::Active,
                'install_notes' => 'نقطه شروع دموی کامل از ارزیابی مکان تا مصرف پاداش.',
                'metadata' => ['is_demo' => true, 'stress_demo' => true],
            ],
        );
    }

    private function completeVenueProfile(Venue $venue): void
    {
        $metadata = $venue->metadata ?? [];
        $metadata['location_profile'] = [
            'venue_type' => 'ecopark',
            'primary_audience' => 'خانواده‌ها، نوجوانان، گردشگران شهری و گروه‌های علمی/فرهنگی',
            'official_website_url' => 'https://example.com/ecopark-abbasabad-demo',
            'manual_research_notes' => 'پروفایل دموی فشار: مسیر QR، گنج پنهان، پاداش فروشگاهی، پاداش اسپانسری و نمایشگر محیطی باید همزمان تست شوند.',
            'facilities' => [
                ['name' => 'ورودی اصلی', 'function' => 'شروع مسیر و اسکن QR', 'campaignUses' => ['qr', 'mission'], 'priority' => 'primary', 'notes' => 'نقطه ورود کاربر'],
                ['name' => 'رواق تجاری', 'function' => 'تعامل با فروشگاه‌ها', 'campaignUses' => ['mission', 'reward'], 'priority' => 'primary', 'notes' => 'فروشگاه X'],
                ['name' => 'فودکورت خانواده', 'function' => 'تحویل جایزه و مصرف کد', 'campaignUses' => ['reward', 'redemption'], 'priority' => 'primary', 'notes' => 'کافه اکو'],
                ['name' => 'گنبد مینا', 'function' => 'روایت علمی و فرهنگی', 'campaignUses' => ['mission', 'treasure'], 'priority' => 'secondary', 'notes' => 'گنج پنهان'],
                ['name' => 'مسیر پیاده‌روی خانوادگی', 'function' => 'مشارکت گروهی', 'campaignUses' => ['mission', 'treasure', 'reward'], 'priority' => 'secondary', 'notes' => 'چالش گروهی خانواده'],
            ],
            'constraints' => ['کنترل ازدحام در ورودی', 'تایید ظرفیت هر واحد پیش از اجرای عمومی'],
            'updated_at' => now()->toIso8601String(),
        ];

        $venue->update(['metadata' => $metadata]);
    }

    private function campaign(Venue $venue, string $code): Campaign
    {
        return Campaign::query()->updateOrCreate(
            ['venue_id' => $venue->id, 'code' => $code],
            [
                'name' => 'کاشفان گنج پنهان',
                'campaign_type' => 'treasure_route',
                'status' => RecordStatus::Active,
                'start_at' => now()->startOfDay(),
                'end_at' => now()->addMonths(6)->endOfDay(),
                'metadata' => [
                    'is_demo' => true,
                    'stress_demo' => true,
                    'blueprint_code' => 'ecopark-online-treasure-map-game',
                    'design_source' => 'venue_blueprint_recommendation',
                    'design_venue_id' => $venue->id,
                    'design_venue_code' => $venue->code,
                    'route_reviewed_at' => now()->toIso8601String(),
                    'route_review_notes' => 'مسیر دمو از QR ورودی تا رواق، فودکورت و گنج پنهان بازبینی شد.',
                ],
            ],
        );
    }

    private function displayDevice(Venue $venue, Hub $hub, Touchpoint $touchpoint): void
    {
        DisplayDevice::query()->updateOrCreate(
            ['code' => 'stress-demo-entry-display'],
            [
                'venue_id' => $venue->id,
                'hub_id' => $hub->id,
                'touchpoint_id' => $touchpoint->id,
                'name' => 'نمایشگر دموی ورودی',
                'device_type' => 'fixed_display',
                'status' => RecordStatus::Active,
                'supported_media_formats' => ['image', 'video', 'display_banner'],
                'metadata' => ['is_demo' => true, 'stress_demo' => true],
            ],
        );
    }

    private function partner(Venue $venue, Hub $hub, string $code, string $name, string $type, string $role): PartnerAccount
    {
        $partner = PartnerAccount::query()->updateOrCreate(
            ['venue_id' => $venue->id, 'code' => $code],
            [
                'name' => $name,
                'partner_type' => $type,
                'status' => RecordStatus::Active,
                'contact_name' => 'مسئول '.$name,
                'contact_mobile' => '09120000000',
                'metadata' => ['is_demo' => true, 'stress_demo' => true],
            ],
        );

        PartnerLocation::query()->updateOrCreate(
            ['partner_account_id' => $partner->id, 'hub_id' => $hub->id],
            [
                'venue_id' => $venue->id,
                'zone_id' => $hub->zone_id,
                'touchpoint_id' => null,
                'location_role' => $role,
                'status' => RecordStatus::Active,
                'metadata' => ['is_demo' => true, 'stress_demo' => true],
            ],
        );

        return $partner;
    }

    private function participant(Campaign $campaign, PartnerAccount $partner): void
    {
        $location = $partner->locations()->where('status', RecordStatus::Active)->first();

        CampaignParticipant::query()->updateOrCreate(
            ['campaign_id' => $campaign->id, 'partner_account_id' => $partner->id],
            [
                'venue_id' => $campaign->venue_id,
                'hub_id' => $location?->hub_id,
                'participant_type' => $partner->partner_type,
                'participation_role' => $partner->partner_type === 'sponsor' ? 'route_sponsor' : 'reward_redemption',
                'status' => RecordStatus::Active,
                'onboarding_status' => 'ready',
                'joined_at' => now(),
                'metadata' => ['is_demo' => true, 'stress_demo' => true],
            ],
        );
    }

    private function qrCode(Venue $venue, Campaign $campaign, Touchpoint $touchpoint): void
    {
        QrCode::query()->updateOrCreate(
            ['code' => 'stress-demo-entry-qr-1405'],
            [
                'venue_id' => $venue->id,
                'touchpoint_id' => $touchpoint->id,
                'campaign_id' => $campaign->id,
                'destination_url' => url('/scan/stress-demo-entry-qr-1405'),
                'label' => 'QR شروع دموی فشار',
                'status' => RecordStatus::Active,
                'valid_from' => now()->subDay(),
                'valid_until' => now()->addMonths(6),
                'max_scans_per_user_per_window' => 3,
                'duplicate_window_seconds' => 120,
                'metadata' => ['is_demo' => true, 'stress_demo' => true],
            ],
        );
    }

    private function partnerReward(Campaign $campaign, PartnerAccount $partner): void
    {
        RewardDefinition::query()->updateOrCreate(
            ['campaign_id' => $campaign->id, 'code' => 'stress-demo-small-drink-coupon'],
            [
                'venue_id' => $campaign->venue_id,
                'partner_account_id' => $partner->id,
                'name' => 'نوشیدنی کوچک کافه اکو',
                'reward_type' => 'partner_coupon',
                'point_cost' => 180,
                'stock_quantity' => 300,
                'status' => RecordStatus::Active,
                'metadata' => [
                    'is_demo' => true,
                    'stress_demo' => true,
                    'source' => 'partner_offer_submission',
                    'approval_status' => 'approved',
                    'availability_status' => 'active',
                    'claim_condition' => 'mission_completion',
                ],
            ],
        );
    }

    /** @param Collection<int, PartnerAccount> $partners */
    private function sponsorProposal(
        SponsorActivationService $sponsors,
        User $actor,
        Campaign $campaign,
        Venue $venue,
        $partners,
    ): SponsorProposal {
        $sponsor = SponsorAccount::query()->updateOrCreate(
            ['code' => 'stress-family-brand-0001'],
            [
                'venue_id' => $venue->id,
                'name' => 'برند حامی خانواده',
                'sponsor_type' => 'brand',
                'status' => RecordStatus::Active,
                'contact_name' => 'نماینده برند حامی خانواده',
                'contact_mobile' => '09120000010',
                'website_url' => 'https://example.com/family-brand',
                'metadata' => ['is_demo' => true, 'stress_demo' => true],
            ],
        );

        $proposal = SponsorProposal::query()->updateOrCreate(
            ['code' => 'stress-family-brand-proposal-0001'],
            [
                'sponsor_account_id' => $sponsor->id,
                'campaign_id' => $campaign->id,
                'preferred_partner_account_id' => $partners->first()?->id,
                'title' => 'بسته اسپانسری خانواده و گنج پنهان',
                'proposal_type' => 'reward_offer',
                'objective' => 'engagement',
                'status' => 'approved',
                'proposed_budget_amount' => 20000000,
                'estimated_value_amount' => 35000000,
                'reward_offer' => 'بسته ویژه خانوادگی و تخفیف ۷۰٪',
                'discount_offer' => 'تخفیف ۷۰٪ برای مرحله چهارم',
                'target_audience' => 'خانواده‌ها و گروه‌های دوستانه',
                'notes' => 'پیشنهاد تاییدشده برای دموی فشار مسیر کامل.',
                'metadata' => ['is_demo' => true, 'stress_demo' => true],
            ],
        );

        foreach ($partners as $index => $partner) {
            SponsorProposalPartnerAccount::query()->updateOrCreate(
                ['sponsor_proposal_id' => $proposal->id, 'partner_account_id' => $partner->id],
                ['sort_order' => $index, 'metadata' => ['stress_demo' => true]],
            );
        }

        $this->proposalItem($proposal, 'product', 'بسته ویژه خانوادگی', 100, $partners, [40, 60]);
        $this->proposalItem($proposal, 'discount', 'تخفیف 70 %', 100, $partners, [40, 60]);

        $proposal->loadMissing(['items', 'partnerAccounts.partnerAccount']);

        if (! SponsorProposalActivation::query()->where('sponsor_proposal_id', $proposal->id)->exists()) {
            $sponsors->activateProposal($proposal, ['activation_notes' => 'آماده برای دموی فشار از ارزیابی مکان تا مصرف پاداش.'], $actor);
        }

        return $proposal->refresh();
    }

    /** @param Collection<int, PartnerAccount> $partners */
    private function proposalItem(SponsorProposal $proposal, string $type, string $title, int $quantity, $partners, array $shares): SponsorProposalItem
    {
        return SponsorProposalItem::query()->updateOrCreate(
            ['sponsor_proposal_id' => $proposal->id, 'item_type' => $type, 'title' => $title],
            [
                'quantity' => $quantity,
                'estimated_unit_value_amount' => $type === 'discount' ? 100000 : 250000,
                'target_partner_account_ids' => $partners->pluck('id')->all(),
                'partner_allocations' => $partners
                    ->values()
                    ->map(fn (PartnerAccount $partner, int $index): array => [
                        'partner_account_id' => $partner->id,
                        'quantity' => $shares[$index] ?? 0,
                    ])
                    ->filter(fn (array $allocation): bool => $allocation['quantity'] > 0)
                    ->values()
                    ->all(),
                'description' => $type === 'discount'
                    ? 'تخفیف اسپانسری که در گام چهارم و بعد از حل سرنخ آزاد می‌شود.'
                    : 'بسته محصولی که می‌تواند به گنج پنهان یا سطح پاداش بالاتر وصل شود.',
                'metadata' => ['is_demo' => true, 'stress_demo' => true],
            ],
        );
    }

    private function manualSponsorTrack(SponsorActivationService $sponsors, Campaign $campaign, Venue $venue, PartnerAccount $partner): void
    {
        $existingSponsor = SponsorAccount::query()->where('code', 'stress-manual-culture-sponsor')->first();
        $sponsor = $sponsors->storeSponsor([
            'sponsor_id' => $existingSponsor?->id,
            'venue_id' => $venue->id,
            'code' => 'stress-manual-culture-sponsor',
            'name' => 'حامی فرهنگی مسیر',
            'sponsor_type' => 'cultural',
            'status' => RecordStatus::Active->value,
            'contact_name' => 'نماینده حامی فرهنگی',
            'contact_mobile' => '09120000011',
            'website_url' => 'https://example.com/culture-sponsor',
            'notes' => 'اسپانسر دستی برای مقایسه با مسیر پیشنهاد اسپانسر.',
        ]);

        $sponsors->storeSponsorship([
            'campaign_id' => $campaign->id,
            'sponsor_account_id' => $sponsor->id,
            'sponsorship_goal' => 'awareness',
            'package_type' => 'display_media',
            'status' => RecordStatus::Active->value,
            'budget_amount' => 8000000,
            'contract_value' => 12000000,
            'notes' => 'حمایت رسانه‌ای دستی برای نمایشگر و روایت مسیر.',
        ]);

        $sponsors->storePartnerAssignment([
            'sponsor_account_id' => $sponsor->id,
            'partner_account_id' => $partner->id,
            'campaign_id' => $campaign->id,
            'activation_role' => 'content_delivery',
            'status' => RecordStatus::Active->value,
            'notes' => 'اتصال دستی اسپانسر به واحد اجرایی دمو.',
        ]);
    }

    private function finalTreasure(Campaign $campaign): void
    {
        $mission = $campaign->missionInstances()
            ->where('metadata->cycle_step_index', 5)
            ->first();

        Treasure::query()->updateOrCreate(
            ['campaign_id' => $campaign->id, 'code' => 'stress-demo-final-hidden-treasure'],
            [
                'venue_id' => $campaign->venue_id,
                'mission_instance_id' => $mission?->id,
                'name' => 'گنج پنهان مرحله پایانی',
                'treasure_type' => 'hidden_treasure',
                'status' => RecordStatus::Active,
                'reveal_rule' => [
                    'mode' => 'after_late_mission',
                    'required_completed_missions' => 4,
                    'family_or_group_bonus' => true,
                ],
                'metadata' => [
                    'is_demo' => true,
                    'stress_demo' => true,
                    'cycle_step_index' => 5,
                    'cycle_step_label' => 'دریافت کد شروع حضوری',
                    'treasure_tier' => 'gold',
                    'discovery_hint' => 'گنج پنهان در پایان مسیر و پس از چند گام آزاد می‌شود، نه در ورود اولیه.',
                ],
            ],
        );
    }

    private function executeVisitorJourney(Campaign $campaign, PartnerAccount $partner): void
    {
        $visitor = User::query()->updateOrCreate(
            ['email' => 'visitor.stress-demo@example.test'],
            ['name' => 'کاربر دموی فشار', 'password' => 'password', 'role' => UserRole::Visitor],
        );

        $qr = QrCode::query()->where('campaign_id', $campaign->id)->orderBy('created_at')->firstOrFail();
        $visit = Visit::query()->updateOrCreate(
            ['user_id' => $visitor->id, 'qr_code_id' => $qr->id, 'session_hash' => 'stress-demo-session'],
            [
                'venue_id' => $campaign->venue_id,
                'touchpoint_id' => $qr->touchpoint_id,
                'campaign_id' => $campaign->id,
                'source' => 'qr',
                'status' => 'completed',
                'occurred_at' => now(),
                'metadata' => ['is_demo' => true, 'stress_demo' => true],
            ],
        );

        $missions = $campaign->missionInstances()->with('missionTemplate')->orderBy('created_at')->get();
        foreach ($missions as $mission) {
            UserMissionProgress::query()->updateOrCreate(
                ['user_id' => $visitor->id, 'mission_instance_id' => $mission->id],
                [
                    'visit_id' => $visit->id,
                    'status' => 'completed',
                    'started_at' => now()->subMinutes(30),
                    'completed_at' => now(),
                    'points_awarded' => (int) ($mission->missionTemplate?->point_value ?? 0),
                    'metadata' => ['is_demo' => true, 'stress_demo' => true],
                ],
            );
        }

        $reward = RewardDefinition::query()
            ->where('campaign_id', $campaign->id)
            ->where('metadata->assignment_status', 'assigned_to_mission')
            ->whereIn('metadata->source', ['sponsor_proposal_activation', 'admin_sponsor_activation'])
            ->orderBy('created_at')
            ->firstOrFail();

        $userReward = UserReward::query()->updateOrCreate(
            [
                'user_id' => $visitor->id,
                'reward_definition_id' => $reward->id,
                'campaign_id' => $campaign->id,
            ],
            [
                'status' => 'awarded',
                'awarded_at' => now(),
                'expires_at' => now()->addDays(7),
                'metadata' => [
                    'is_demo' => true,
                    'stress_demo' => true,
                    'source' => 'stress_demo_visitor_execution',
                    'reward_code' => $reward->code,
                ],
            ],
        );

        RewardRedemption::query()->updateOrCreate(
            ['redemption_code' => 'STRESS-DEMO-REDEEM-001'],
            [
                'user_reward_id' => $userReward->id,
                'user_id' => $visitor->id,
                'partner_account_id' => $partner->id,
                'status' => 'confirmed',
                'redeemed_at' => now(),
                'metadata' => ['is_demo' => true, 'stress_demo' => true, 'confirmed_by' => 'stress_demo_command'],
            ],
        );

        $allocation = RewardInventoryAllocation::query()
            ->where('reward_definition_id', $reward->id)
            ->where('partner_account_id', $partner->id)
            ->first();

        if ($allocation) {
            $allocation->update([
                'reserved_quantity' => max(0, $allocation->reserved_quantity),
                'redeemed_quantity' => max(1, $allocation->redeemed_quantity),
                'status' => 'active',
            ]);
        }
    }
}
