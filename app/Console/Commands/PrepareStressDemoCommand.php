<?php

namespace App\Console\Commands;

use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Models\AdRequest;
use App\Models\Campaign;
use App\Models\CampaignParticipant;
use App\Models\DisplayDevice;
use App\Models\GameParty;
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
use App\Models\UserAccessScope;
use App\Models\UserMissionProgress;
use App\Models\UserReward;
use App\Models\Venue;
use App\Models\Visit;
use App\Models\Zone;
use App\Services\EcoParkOnlineGameService;
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
        EcoParkOnlineGameService $onlineGame,
    ): int {
        $campaignCode = Str::lower((string) $this->option('campaign'));
        $venueCode = Str::lower((string) $this->option('venue'));

        $result = DB::transaction(function () use ($campaignCode, $onlineGame, $sponsors, $venueCode): array {
            $actor = $this->adminUser();
            $venue = $this->venue($venueCode);
            $zone = $this->zone($venue);
            $entryHub = $this->hub($zone, 'visitor-welcome-hub', 'هاب خوش‌آمدگویی بازدیدکنندگان', 'experience');
            $ravaqHub = $this->hub($zone, 'ravaq-commercial-hub', 'رواق تجاری اکوپارک', 'commercial_ravaq');
            $foodHub = $this->hub($zone, 'foodcourt-family-hub', 'هاب فودکورت و خانواده', 'food_family');
            $scienceHub = $this->hub($zone, 'gonbad-mina-science-hub', 'هاب گنبد مینا و روایت علمی', 'science_story');
            $touchpoint = $this->touchpoint($entryHub);
            $onsiteTouchpoint = $this->onsiteTouchpoint($entryHub);
            $physicalTouchpoints = collect([
                $this->physicalCheckpointTouchpoint(
                    $entryHub,
                    'fire-water',
                    'ایستگاه میدان آب‌وآتش',
                    'میدان آب‌وآتش، کنار مسیر اصلی پیاده‌روی؛ استند نارنجی اکسپلوریا.',
                ),
                $this->physicalCheckpointTouchpoint(
                    $entryHub,
                    'nature',
                    'ایستگاه پل طبیعت',
                    'ورودی پل طبیعت از سمت بوستان آب‌وآتش؛ استند سبز اکسپلوریا.',
                ),
                $this->physicalCheckpointTouchpoint(
                    $scienceHub,
                    'book-garden',
                    'ایستگاه باغ کتاب',
                    'ورودی اصلی باغ کتاب؛ کنار نقشه راهنمای مجموعه.',
                ),
                $this->physicalCheckpointTouchpoint(
                    $scienceHub,
                    'mina',
                    'ایستگاه گنبد مینا',
                    'ورودی گنبد مینا؛ کنار تابلوی اطلاعات بازدید.',
                ),
                $this->physicalCheckpointTouchpoint(
                    $ravaqHub,
                    'ravaq-finish',
                    'گنج پایانی رواق',
                    'رواق تجاری اکوپارک؛ کنار نشان طلایی پایان مسیر اکسپلوریا.',
                ),
            ]);

            $this->completeVenueProfile($venue);
            $this->venueExecutiveScope($venue);

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

            $this->qrCodes($venue, $campaign, $touchpoint, $onsiteTouchpoint, $physicalTouchpoints);
            $this->partnerReward($campaign, $partners[0]);
            $this->rewardedGameAds($campaign, $partners);
            $proposal = $this->sponsorProposal($sponsors, $actor, $campaign, $venue, $partners->take(2)->values());
            $this->manualSponsorTrack($sponsors, $campaign, $venue, $partners[1]);

            Artisan::call('exploria:prepare-demo-cycle', [
                'campaign' => $campaign->code,
                '--reward-step' => 4,
                '--claim-condition' => 'mission_completion',
            ]);

            $campaign->refresh();
            $this->finalTreasure($campaign);
            $this->gameCommerceRewards($campaign, $partners);
            GameParty::query()
                ->where('campaign_id', $campaign->id)
                ->with(['members', 'progress', 'bonusClaims'])
                ->each(fn (GameParty $party) => $onlineGame->syncCommercialRewards($party));

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

    private function venueExecutiveScope(Venue $venue): void
    {
        $manager = User::query()->updateOrCreate(
            ['email' => 'venue.manager.ecopark@example.test'],
            ['name' => 'مدیر اجرایی مکان اکوپارک', 'password' => 'password', 'role' => UserRole::Viewer],
        );

        UserAccessScope::query()->updateOrCreate(
            [
                'user_id' => $manager->id,
                'role_key' => 'venue_executive',
                'scope_type' => 'venue',
                'scope_id' => $venue->id,
            ],
            [
                'status' => RecordStatus::Active,
                'metadata' => [
                    'source' => 'stress_demo_cycle',
                    'purpose' => 'venue_manager_readiness',
                ],
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

    private function onsiteTouchpoint(Hub $hub): Touchpoint
    {
        return Touchpoint::query()->updateOrCreate(
            ['hub_id' => $hub->id, 'code' => 'online-game-onsite-gate'],
            [
                'label' => 'دروازه حضور بازی اکسپلوریا',
                'type' => 'qr_stand',
                'owner_type' => 'venue',
                'status' => RecordStatus::Active,
                'install_notes' => 'ورودی اصلی اکوپارک عباس‌آباد، کنار میز راهنمای بازدیدکنندگان؛ استند سبز اکسپلوریا.',
                'metadata' => [
                    'is_demo' => true,
                    'stress_demo' => true,
                    'online_game_role' => 'onsite_gate',
                    'public_location' => 'ورودی اصلی اکوپارک عباس‌آباد، کنار میز راهنمای بازدیدکنندگان',
                    'finding_instruction' => 'استند سبز اکسپلوریا با عنوان «دروازه حضور بازی» را پیدا و QR روی همان استند را اسکن کنید.',
                ],
            ],
        );
    }

    private function physicalCheckpointTouchpoint(
        Hub $hub,
        string $key,
        string $label,
        string $location,
    ): Touchpoint {
        return Touchpoint::query()->updateOrCreate(
            ['hub_id' => $hub->id, 'code' => 'online-game-checkpoint-'.$key],
            [
                'label' => $label,
                'type' => 'qr_stand',
                'owner_type' => 'venue',
                'status' => RecordStatus::Active,
                'install_notes' => $location,
                'metadata' => [
                    'is_demo' => true,
                    'stress_demo' => true,
                    'online_game_role' => 'physical_checkpoint',
                    'checkpoint_key' => $key,
                    'public_location' => $location,
                    'finding_instruction' => 'استند اکسپلوریا با عنوان «'.$label.'» را پیدا و QR روی همان استند را اسکن کنید.',
                ],
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
                    'online_game_cycle_key' => 'launch-1405',
                    'online_game_cycle_label' => 'دوره آغاز ۱۴۰۵',
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

    /** @param Collection<int, Touchpoint> $physicalTouchpoints */
    private function qrCodes(
        Venue $venue,
        Campaign $campaign,
        Touchpoint $touchpoint,
        Touchpoint $onsiteTouchpoint,
        Collection $physicalTouchpoints,
    ): void {
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
                'metadata' => ['is_demo' => true, 'stress_demo' => true, 'online_game_role' => 'start'],
            ],
        );

        QrCode::query()->updateOrCreate(
            ['code' => 'stress-demo-onsite-gate-1405'],
            [
                'venue_id' => $venue->id,
                'touchpoint_id' => $onsiteTouchpoint->id,
                'campaign_id' => $campaign->id,
                'destination_url' => url('/scan/stress-demo-onsite-gate-1405'),
                'label' => 'QR دروازه حضور بازی اکسپلوریا',
                'status' => RecordStatus::Active,
                'valid_from' => now()->subDay(),
                'valid_until' => now()->addMonths(6),
                'max_scans_per_user_per_window' => 1,
                'duplicate_window_seconds' => 300,
                'metadata' => [
                    'is_demo' => true,
                    'stress_demo' => true,
                    'online_game_role' => 'onsite_gate',
                    'public_location' => 'ورودی اصلی اکوپارک عباس‌آباد، کنار میز راهنمای بازدیدکنندگان',
                    'finding_instruction' => 'استند سبز اکسپلوریا با عنوان «دروازه حضور بازی» را پیدا و QR روی همان استند را اسکن کنید.',
                ],
            ],
        );

        $physicalTouchpoints->each(function (Touchpoint $checkpoint) use ($campaign, $venue): void {
            $key = (string) data_get($checkpoint->metadata, 'checkpoint_key');
            $code = 'stress-demo-physical-'.$key.'-1405';

            QrCode::query()->updateOrCreate(
                ['code' => $code],
                [
                    'venue_id' => $venue->id,
                    'touchpoint_id' => $checkpoint->id,
                    'campaign_id' => $campaign->id,
                    'destination_url' => url('/scan/'.$code),
                    'label' => 'QR '.$checkpoint->label,
                    'status' => RecordStatus::Active,
                    'valid_from' => now()->subDay(),
                    'valid_until' => now()->addMonths(6),
                    'max_scans_per_user_per_window' => 1,
                    'duplicate_window_seconds' => 300,
                    'metadata' => [
                        'is_demo' => true,
                        'stress_demo' => true,
                        'online_game_role' => 'physical_checkpoint',
                        'checkpoint_key' => $key,
                        'public_location' => data_get($checkpoint->metadata, 'public_location'),
                        'finding_instruction' => data_get($checkpoint->metadata, 'finding_instruction'),
                    ],
                ],
            );
        });
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
    private function rewardedGameAds(Campaign $campaign, Collection $partners): void
    {
        $definitions = [
            ['code' => 'ecopark-rewarded-family-tip-1405', 'partner' => 2, 'title' => 'تقسیم نقش برای یک کاوش بهتر', 'body' => 'نقشه‌خوان، نشانه‌یاب و ثبت‌کننده انتخاب کنید تا هر عضو در کشف مسیر سهم واقعی داشته باشد.', 'stage' => 2, 'checkpoint' => null, 'points' => 15],
            ['code' => 'ecopark-rewarded-map-tip-1405', 'partner' => 1, 'title' => 'پیشنهاد ویژه مسیر نقشه', 'body' => 'فروشگاه X برای کاوشگرانی که نقشه را دقیق دنبال می‌کنند یک مشوق خرید مرحله‌ای آماده کرده است.', 'stage' => 3, 'checkpoint' => null, 'points' => 20],
            ['code' => 'ecopark-rewarded-clue-tip-1405', 'partner' => 2, 'title' => 'سرنخ کوتاه برند حامی', 'body' => 'روایت کوتاه اسپانسر درباره تبدیل تکه‌های سرنخ به رمز نهایی را ببینید.', 'stage' => 4, 'checkpoint' => null, 'points' => 25],
            ['code' => 'ecopark-rewarded-pass-tip-1405', 'partner' => 0, 'title' => 'آمادگی برای حضور در اکوپارک', 'body' => 'کافه اکو نکات کوتاه مسیر حضوری و زمان مناسب توقف در واحدهای عضو را معرفی می‌کند.', 'stage' => 5, 'checkpoint' => null, 'points' => 20],
            ['code' => 'ecopark-rewarded-gate-welcome-1405', 'partner' => 2, 'title' => 'خوش‌آمدگویی حامی مرحله حضوری', 'body' => 'پیام کوتاه حامی کمپین و راهنمای شروع ایمن مسیر حضوری را مشاهده کنید.', 'stage' => 6, 'checkpoint' => 'onsite-gate', 'points' => 20],
            ['code' => 'ecopark-rewarded-fire-water-1405', 'partner' => 0, 'title' => 'توقف خوش‌طعم آب‌وآتش', 'body' => 'پیشنهاد امروز کافه اکو برای کاوشگران میدان آب‌وآتش را ببینید؛ خرید کاملاً اختیاری است.', 'stage' => null, 'checkpoint' => 'fire-water', 'points' => 25],
            ['code' => 'ecopark-rewarded-nature-1405', 'partner' => 1, 'title' => 'انتخاب سبز فروشگاه X', 'body' => 'محصول منتخب و کم‌حجم مسیر پل طبیعت با تخفیف ویژه اعضای اکسپلوریا معرفی می‌شود.', 'stage' => null, 'checkpoint' => 'nature', 'points' => 30],
            ['code' => 'ecopark-rewarded-book-garden-1405', 'partner' => 2, 'title' => 'پیشنهاد فرهنگی باغ کتاب', 'body' => 'حامی خانواده یک انتخاب فرهنگی کوتاه برای توقف خانوادگی این ایستگاه معرفی می‌کند.', 'stage' => null, 'checkpoint' => 'book-garden', 'points' => 25],
            ['code' => 'ecopark-rewarded-mina-1405', 'partner' => 1, 'title' => 'یادگاری علمی گنبد مینا', 'body' => 'پیشنهاد فروشگاه عضو برای یک یادگاری علمی و تخفیف مرحله‌ای را مشاهده کنید.', 'stage' => null, 'checkpoint' => 'mina', 'points' => 30],
            ['code' => 'ecopark-rewarded-ravaq-finish-1405', 'partner' => 1, 'title' => 'پیشنهاد پایانی رواق', 'body' => 'سبد نهایی فروشگاه‌های عضو و شرایط تقویت تخفیف پایانی را پیش از کشف گنج ببینید.', 'stage' => 9, 'checkpoint' => 'ravaq-finish', 'points' => 40],
        ];

        foreach ($definitions as $index => $definition) {
            $partner = $partners->get($definition['partner']);

            if (! $partner instanceof PartnerAccount) {
                continue;
            }

            $ad = AdRequest::query()->updateOrCreate(
                ['code' => $definition['code']],
                [
                    'venue_id' => $campaign->venue_id,
                    'partner_account_id' => $partner->id,
                    'title' => $definition['title'],
                    'body_copy' => $definition['body'],
                    'cta_text' => 'مشاهده اختیاری',
                    'target_url' => null,
                    'advertiser_type' => $partner->partner_type === 'sponsor' ? 'sponsor' : 'member_partner',
                    'ad_type' => 'rewarded_content',
                    'status' => 'approved',
                    'starts_at' => now()->subDay(),
                    'ends_at' => now()->addMonths(6),
                    'budget_amount' => 3000000 + ($index * 500000),
                    'impression_cap' => 10000,
                    'click_cap' => 5000,
                    'metadata' => [
                        'is_demo' => true,
                        'stress_demo' => true,
                        'rewarded_points' => $definition['points'],
                        'required_seconds' => 10,
                        'game_stage_index' => $definition['stage'],
                        'checkpoint_key' => $definition['checkpoint'],
                        'commercial_model' => 'paid_stage_placement',
                    ],
                ],
            );

            $ad->creatives()->updateOrCreate(
                ['creative_type' => 'text_card'],
                [
                    'headline' => $definition['title'],
                    'body_copy' => $definition['body'],
                    'cta_text' => 'مشاهده اختیاری',
                    'status' => 'approved',
                    'metadata' => ['is_demo' => true, 'rewarded' => true],
                ],
            );

            $ad->placements()->updateOrCreate(
                ['placement_type' => 'post_mission'],
                [
                    'status' => 'approved',
                    'starts_at' => now()->subDay(),
                    'ends_at' => now()->addMonths(6),
                    'priority' => $index + 1,
                    'metadata' => [
                        'is_demo' => true,
                        'rewarded' => true,
                        'game_stage_index' => $definition['stage'],
                        'checkpoint_key' => $definition['checkpoint'],
                    ],
                ],
            );
        }
    }

    /** @param Collection<int, PartnerAccount> $partners */
    private function gameCommerceRewards(Campaign $campaign, Collection $partners): void
    {
        $definitions = [
            ['code' => 'ecopark-fire-water-cafe-15', 'partner' => 0, 'name' => 'تخفیف ۱۵٪ کافه اکو', 'type' => 'partner_coupon', 'checkpoint' => 'fire-water'],
            ['code' => 'ecopark-nature-store-20', 'partner' => 1, 'name' => 'تخفیف ۲۰٪ انتخاب سبز فروشگاه X', 'type' => 'partner_coupon', 'checkpoint' => 'nature'],
            ['code' => 'ecopark-book-garden-family-gift', 'partner' => 2, 'name' => 'هدیه فرهنگی خانواده', 'type' => 'sponsor_product', 'checkpoint' => 'book-garden'],
            ['code' => 'ecopark-mina-science-20', 'partner' => 1, 'name' => 'تخفیف ۲۰٪ یادگاری علمی', 'type' => 'partner_coupon', 'checkpoint' => 'mina'],
        ];

        foreach ($definitions as $definition) {
            $partner = $partners->get($definition['partner']);

            if (! $partner instanceof PartnerAccount) {
                continue;
            }

            RewardDefinition::query()->updateOrCreate(
                ['campaign_id' => $campaign->id, 'code' => $definition['code']],
                [
                    'venue_id' => $campaign->venue_id,
                    'partner_account_id' => $partner->id,
                    'name' => $definition['name'],
                    'reward_type' => $definition['type'],
                    'point_cost' => 0,
                    'stock_quantity' => 200,
                    'status' => RecordStatus::Active,
                    'metadata' => [
                        'is_demo' => true,
                        'stress_demo' => true,
                        'source' => 'game_commercial_checkpoint',
                        'approval_status' => 'approved',
                        'availability_status' => 'active',
                        'game_auto_award' => true,
                        'game_checkpoint_key' => $definition['checkpoint'],
                        'description' => 'پس از کشف ایستگاه صادر می‌شود و مصرف آن در واحد عضو، مراجعه تجاری را ثبت می‌کند.',
                        'terms' => 'تا هفت روز پس از صدور؛ خرید اختیاری و مصرف کد توسط واحد عضو تأیید می‌شود.',
                    ],
                ],
            );
        }

        $finalPartner = $partners->get(1);

        RewardDefinition::query()->updateOrCreate(
            ['campaign_id' => $campaign->id, 'code' => 'ecopark-final-explorer-base'],
            [
                'venue_id' => $campaign->venue_id,
                'partner_account_id' => $finalPartner instanceof PartnerAccount ? $finalPartner->id : null,
                'name' => 'مشوق پایه پایان مسیر',
                'reward_type' => 'partner_coupon',
                'point_cost' => 0,
                'stock_quantity' => 300,
                'status' => RecordStatus::Active,
                'metadata' => [
                    'is_demo' => true,
                    'stress_demo' => true,
                    'source' => 'game_final_reward',
                    'approval_status' => 'approved',
                    'availability_status' => 'active',
                    'game_auto_award' => true,
                    'game_final_level' => 'base',
                    'description' => 'پاداش پایه برای همه کسانی که گنج پایانی را کشف می‌کنند.',
                    'terms' => 'مصرف در واحد عضو تا هفت روز پس از پایان مسیر.',
                ],
            ],
        );

        $boostedReward = RewardDefinition::query()
            ->where('campaign_id', $campaign->id)
            ->where('reward_type', 'sponsor_discount')
            ->whereIn('metadata->source', ['sponsor_proposal_activation', 'admin_sponsor_activation'])
            ->orderByDesc('stock_quantity')
            ->first();
        $premiumReward = RewardDefinition::query()
            ->where('campaign_id', $campaign->id)
            ->where('reward_type', 'sponsor_product')
            ->whereIn('metadata->source', ['sponsor_proposal_activation', 'admin_sponsor_activation'])
            ->orderByDesc('stock_quantity')
            ->first();

        if ($boostedReward) {
            $boostedReward->update([
                'status' => RecordStatus::Active,
                'metadata' => array_merge($boostedReward->metadata ?? [], [
                    'availability_status' => 'active',
                    'game_auto_award' => true,
                    'game_final_level' => 'boosted',
                    'description' => 'تخفیف ۷۰٪ با ترکیب پیشرفت ماجراجویی و تعامل اختیاری یا مصرف واقعی در واحد عضو.',
                    'terms' => 'پس از تکمیل مسیر و تحقق شرط تقویت، کد مصرف هفت‌روزه صادر می‌شود.',
                ]),
            ]);
        }

        if ($premiumReward) {
            $premiumReward->update([
                'status' => RecordStatus::Active,
                'metadata' => array_merge($premiumReward->metadata ?? [], [
                    'availability_status' => 'active',
                    'game_auto_award' => true,
                    'game_final_level' => 'premium',
                    'description' => 'سبد ممتاز برای کاربری که هم تعامل اختیاری و هم تبدیل واقعی فروشگاهی داشته است.',
                    'terms' => 'پس از پایان مسیر؛ موجودی محدود و مصرف در واحدهای عضو تعیین‌شده.',
                ]),
            ]);
        }
    }

    /**
     * @param  Collection<int, PartnerAccount>  $partners
     */
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

    /**
     * @param  Collection<int, PartnerAccount>  $partners
     * @param  list<int>  $shares
     */
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
                    'points_awarded' => (int) ($mission->missionTemplate->point_value ?? 0),
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
