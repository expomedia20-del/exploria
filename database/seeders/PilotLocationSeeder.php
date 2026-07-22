<?php

namespace Database\Seeders;

use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\CampaignParticipant;
use App\Models\DisplayDevice;
use App\Models\Hub;
use App\Models\HubManagementAssignment;
use App\Models\MissionInstance;
use App\Models\MissionTemplate;
use App\Models\PartnerAccount;
use App\Models\PartnerLocation;
use App\Models\PartnerUser;
use App\Models\QrCode;
use App\Models\RewardDefinition;
use App\Models\RewardInventoryAllocation;
use App\Models\Touchpoint;
use App\Models\Treasure;
use App\Models\User;
use App\Models\UserAccessScope;
use App\Models\Venue;
use App\Models\Zone;
use Illuminate\Database\Seeder;

class PilotLocationSeeder extends Seeder
{
    public const DEMO_QR_CODE = 'ep1405-a7f3k9m2q8x4';

    public function run(): void
    {
        $ecoPark = Venue::query()->updateOrCreate(
            ['code' => 'ecopark-abbasabad'],
            [
                'name' => 'اکوپارک عباس‌آباد',
                'city' => 'تهران',
                'status' => RecordStatus::Active,
                'profile_status' => RecordStatus::Active,
                'metadata' => ['is_demo' => true, 'rollout_order' => 1, 'pilot_role' => 'primary'],
            ],
        );

        Venue::query()->updateOrCreate(
            ['code' => 'eram-park'],
            [
                'name' => 'پارک و شهربازی ارم',
                'city' => 'تهران',
                'status' => RecordStatus::Draft,
                'profile_status' => RecordStatus::Draft,
                'metadata' => ['is_demo' => true, 'rollout_order' => 2, 'pilot_role' => 'secondary'],
            ],
        );

        Venue::query()->updateOrCreate(
            ['code' => 'milad-tower'],
            [
                'name' => 'برج میلاد',
                'city' => 'تهران',
                'status' => RecordStatus::Placeholder,
                'profile_status' => RecordStatus::Placeholder,
                'metadata' => ['is_demo' => true, 'rollout_order' => 3, 'pilot_role' => 'controlled_placeholder'],
            ],
        );

        $zone = Zone::query()->updateOrCreate(
            ['venue_id' => $ecoPark->id, 'code' => 'main-entry-zone'],
            ['name' => 'محدوده ورودی اصلی', 'status' => RecordStatus::Active, 'metadata' => ['is_demo' => true]],
        );

        $ravaqZone = Zone::query()->updateOrCreate(
            ['venue_id' => $ecoPark->id, 'code' => 'ravaq-commercial-zone'],
            [
                'name' => 'محدوده رواق تجاری',
                'status' => RecordStatus::Active,
                'metadata' => [
                    'is_demo' => true,
                    'operational_scope' => 'ravaq_commercial_units',
                    'scope_note' => 'Ravaq managers see only the commercial ravaq, food court, restaurant, and shop units inside this zone.',
                ],
            ],
        );

        $hub = Hub::query()->updateOrCreate(
            ['zone_id' => $zone->id, 'code' => 'visitor-welcome-hub'],
            [
                'name' => 'هاب خوش‌آمدگویی بازدیدکنندگان',
                'hub_type' => 'experience',
                'status' => RecordStatus::Active,
                'metadata' => ['is_demo' => true],
            ],
        );

        $touchpoint = Touchpoint::query()->updateOrCreate(
            ['hub_id' => $hub->id, 'code' => 'main-gate-qr-stand'],
            [
                'label' => 'استند QR ورودی اصلی',
                'type' => 'qr_stand',
                'owner_type' => 'venue',
                'status' => RecordStatus::Active,
                'install_notes' => 'داده آزمایشی؛ محل دقیق نصب باید پیش از عملیات میدانی تأیید شود.',
                'metadata' => ['is_demo' => true],
            ],
        );

        $ravaqHub = Hub::query()->updateOrCreate(
            ['zone_id' => $ravaqZone->id, 'code' => 'ravaq-commercial-hub'],
            [
                'name' => 'رواق تجاری اکوپارک',
                'hub_type' => 'commercial_ravaq',
                'status' => RecordStatus::Active,
                'metadata' => [
                    'is_demo' => true,
                    'commercial_role' => 'merchant_cluster',
                    'manager_scope' => 'ravaq_manager',
                    'scope_note' => 'Commercial units, shops, and ravaq retailers only.',
                ],
            ],
        );

        $foodHub = Hub::query()->updateOrCreate(
            ['zone_id' => $ravaqZone->id, 'code' => 'foodcourt-family-hub'],
            [
                'name' => 'هاب فودکورت و خانواده',
                'hub_type' => 'food_family',
                'status' => RecordStatus::Active,
                'metadata' => [
                    'is_demo' => true,
                    'commercial_role' => 'reward_redemption',
                    'manager_scope' => 'ravaq_manager',
                    'scope_note' => 'Food court, restaurant, ice cream, and food-service units inside the ravaq commercial zone.',
                ],
            ],
        );

        $scienceHub = Hub::query()->updateOrCreate(
            ['zone_id' => $zone->id, 'code' => 'gonbad-mina-science-hub'],
            [
                'name' => 'هاب گنبد مینا و روایت علمی',
                'hub_type' => 'science_story',
                'status' => RecordStatus::Active,
                'metadata' => ['is_demo' => true, 'commercial_role' => 'sponsor_storytelling'],
            ],
        );

        $ravaqManager = User::query()->updateOrCreate(
            ['email' => 'ravaq.manager@example.test'],
            ['name' => 'مدیر رواق اکوپارک', 'password' => 'password', 'role' => UserRole::HubManager],
        );

        HubManagementAssignment::query()->updateOrCreate(
            ['hub_id' => $ravaqHub->id, 'user_id' => $ravaqManager->id, 'assignment_role' => 'ravaq_manager'],
            ['status' => RecordStatus::Active, 'metadata' => ['is_demo' => true]],
        );
        HubManagementAssignment::query()->updateOrCreate(
            ['hub_id' => $foodHub->id, 'user_id' => $ravaqManager->id, 'assignment_role' => 'ravaq_manager'],
            ['status' => RecordStatus::Active, 'metadata' => ['is_demo' => true, 'scope_reason' => 'foodcourt_inside_ravaq_zone']],
        );
        UserAccessScope::query()->updateOrCreate(
            ['user_id' => $ravaqManager->id, 'role_key' => 'ravaq_manager', 'scope_type' => 'hub', 'scope_id' => $ravaqHub->id],
            ['status' => RecordStatus::Active, 'metadata' => ['source' => 'pilot_seed', 'legacy_table' => 'hub_management_assignments']],
        );
        UserAccessScope::query()->updateOrCreate(
            ['user_id' => $ravaqManager->id, 'role_key' => 'ravaq_manager', 'scope_type' => 'hub', 'scope_id' => $foodHub->id],
            ['status' => RecordStatus::Active, 'metadata' => ['source' => 'pilot_seed', 'scope_reason' => 'foodcourt_inside_ravaq_zone']],
        );

        $venueManager = User::query()->updateOrCreate(
            ['email' => 'venue.manager.ecopark@example.test'],
            ['name' => 'مدیر اجرایی مکان اکوپارک', 'password' => 'password', 'role' => UserRole::Viewer],
        );

        UserAccessScope::query()->updateOrCreate(
            ['user_id' => $venueManager->id, 'role_key' => 'venue_executive', 'scope_type' => 'venue', 'scope_id' => $ecoPark->id],
            ['status' => RecordStatus::Active, 'metadata' => ['source' => 'pilot_seed', 'purpose' => 'venue_manager_readiness']],
        );

        $projectManager = User::query()->updateOrCreate(
            ['email' => 'project.manager.ecopark@example.test'],
            ['name' => 'Ù…Ø¯ÛŒØ± Ù¾Ø±ÙˆÚ˜Ù‡ Ù…Ú©Ø§Ù†ÛŒ Ø§Ú©ÙˆÙ¾Ø§Ø±Ú©', 'password' => 'password', 'role' => UserRole::Operator],
        );

        UserAccessScope::query()->updateOrCreate(
            ['user_id' => $projectManager->id, 'role_key' => 'project_admin', 'scope_type' => 'venue', 'scope_id' => $ecoPark->id],
            ['status' => RecordStatus::Active, 'metadata' => ['source' => 'pilot_seed', 'purpose' => 'project_manager_readiness']],
        );

        $partners = [
            [
                'code' => 'cafe-eco',
                'name' => 'کافه اکو',
                'partner_type' => 'member_shop',
                'contact_name' => 'مسئول کافه اکو',
                'contact_mobile' => '09120000001',
                'hub' => $foodHub,
                'user_email' => 'cafe.eco@example.test',
                'user_name' => 'مدیر کافه اکو',
                'user_role' => UserRole::ShopPartner,
                'location_role' => 'reward_redemption',
            ],
            [
                'code' => 'ravaq-store',
                'name' => 'فروشگاه X',
                'partner_type' => 'member_shop',
                'contact_name' => 'مسئول فروشگاه X',
                'contact_mobile' => '09120000002',
                'hub' => $ravaqHub,
                'user_email' => 'ravaq.store@example.test',
                'user_name' => 'مدیر فروشگاه X',
                'user_role' => UserRole::ShopPartner,
                'location_role' => 'commercial_partner',
            ],
            [
                'code' => 'family-route-sponsor',
                'name' => 'اسپانسر مسیر خانوادگی',
                'partner_type' => 'sponsor',
                'contact_name' => 'مسئول اسپانسر مسیر خانوادگی',
                'contact_mobile' => '09120000003',
                'hub' => $scienceHub,
                'user_email' => 'family.sponsor@example.test',
                'user_name' => 'نماینده اسپانسر خانوادگی',
                'user_role' => UserRole::Sponsor,
                'location_role' => 'sponsored_story',
            ],
        ];

        foreach ($partners as $partnerData) {
            /** @var Hub $partnerHub */
            $partnerHub = $partnerData['hub'];

            $partner = PartnerAccount::query()->updateOrCreate(
                ['venue_id' => $ecoPark->id, 'code' => $partnerData['code']],
                [
                    'name' => $partnerData['name'],
                    'partner_type' => $partnerData['partner_type'],
                    'status' => RecordStatus::Active,
                    'contact_name' => $partnerData['contact_name'],
                    'contact_mobile' => $partnerData['contact_mobile'],
                    'metadata' => ['is_demo' => true],
                ],
            );

            PartnerLocation::query()->updateOrCreate(
                ['partner_account_id' => $partner->id, 'hub_id' => $partnerHub->id],
                [
                    'venue_id' => $ecoPark->id,
                    'zone_id' => $partnerHub->zone_id,
                    'touchpoint_id' => null,
                    'location_role' => $partnerData['location_role'],
                    'status' => RecordStatus::Active,
                    'metadata' => ['is_demo' => true],
                ],
            );

            $partnerUser = User::query()->updateOrCreate(
                ['email' => $partnerData['user_email']],
                ['name' => $partnerData['user_name'], 'password' => 'password', 'role' => $partnerData['user_role']],
            );

            PartnerUser::query()->updateOrCreate(
                ['partner_account_id' => $partner->id, 'user_id' => $partnerUser->id],
                ['role' => 'manager', 'status' => RecordStatus::Active, 'metadata' => ['is_demo' => true]],
            );
            UserAccessScope::query()->updateOrCreate(
                [
                    'user_id' => $partnerUser->id,
                    'role_key' => $partnerData['user_role'] === UserRole::Sponsor ? 'internal_sponsor' : 'shop_manager',
                    'scope_type' => 'partner',
                    'scope_id' => $partner->id,
                ],
                ['status' => RecordStatus::Active, 'metadata' => ['source' => 'pilot_seed', 'legacy_table' => 'partner_users']],
            );
        }

        User::query()
            ->whereIn('email', [
                'ravaq.manager.ops@example.test',
                'ravaq.zone.manager@example.test',
                'cafe.eco.morning@example.test',
                'cafe.eco.evening@example.test',
                'ravaq.store.backup@example.test',
                'family.sponsor.backup@example.test',
            ])
            ->whereDoesntHave('accessScopes')
            ->delete();

        $campaign = Campaign::query()->updateOrCreate(
            ['venue_id' => $ecoPark->id, 'code' => 'ecopark-pilot-1405'],
            [
                'name' => 'پایلوت بازدید اکوپارک ۱۴۰۵',
                'campaign_type' => 'pilot_visit',
                'status' => RecordStatus::Active,
                'start_at' => '2026-06-20 00:00:00',
                'end_at' => '2027-03-20 23:59:59',
                'metadata' => ['is_demo' => true],
            ],
        );

        $campaignParticipants = [
            'cafe-eco' => ['participation_role' => 'reward_redemption', 'onboarding_status' => 'ready', 'connections' => ['rewards' => 1, 'ads' => 0, 'qr_codes' => 0, 'missions' => 1]],
            'ravaq-store' => ['participation_role' => 'commercial_activation', 'onboarding_status' => 'ready', 'connections' => ['rewards' => 0, 'ads' => 1, 'qr_codes' => 0, 'missions' => 1]],
            'family-route-sponsor' => ['participation_role' => 'route_sponsor', 'onboarding_status' => 'invited', 'connections' => ['rewards' => 1, 'ads' => 1, 'qr_codes' => 0, 'missions' => 1]],
        ];

        foreach ($campaignParticipants as $partnerCode => $participantData) {
            $participantPartner = PartnerAccount::query()->where('code', $partnerCode)->first();
            $participantLocation = $participantPartner?->locations()->where('status', RecordStatus::Active)->first();

            if (! $participantPartner) {
                continue;
            }

            CampaignParticipant::query()->updateOrCreate(
                ['campaign_id' => $campaign->id, 'partner_account_id' => $participantPartner->id],
                [
                    'venue_id' => $ecoPark->id,
                    'hub_id' => $participantLocation?->hub_id,
                    'participant_type' => $participantPartner->partner_type,
                    'participation_role' => $participantData['participation_role'],
                    'status' => RecordStatus::Active,
                    'onboarding_status' => $participantData['onboarding_status'],
                    'joined_at' => $participantData['onboarding_status'] === 'ready' ? now() : null,
                    'metadata' => ['is_demo' => true, 'campaign_participant_seed' => true, 'connections' => $participantData['connections']],
                ],
            );
        }
        QrCode::query()->updateOrCreate(
            ['code' => self::DEMO_QR_CODE],
            [
                'venue_id' => $ecoPark->id,
                'touchpoint_id' => $touchpoint->id,
                'campaign_id' => $campaign->id,
                'destination_url' => url('/scan/'.self::DEMO_QR_CODE),
                'label' => 'QR دمو ورودی اکوپارک',
                'status' => RecordStatus::Active,
                'valid_from' => '2026-06-20 00:00:00',
                'valid_until' => '2027-03-20 23:59:59',
                'metadata' => ['is_demo' => true],
            ],
        );

        DisplayDevice::query()->updateOrCreate(
            ['code' => 'ecopark-entry-fixed-display'],
            [
                'venue_id' => $ecoPark->id,
                'hub_id' => $hub->id,
                'touchpoint_id' => $touchpoint->id,
                'name' => 'نمایشگر ثابت ورودی اکوپارک',
                'device_type' => 'fixed_display',
                'status' => RecordStatus::Active,
                'supported_media_formats' => ['image', 'video', 'display_banner'],
                'metadata' => ['is_demo' => true, 'inventory_role' => 'entry_awareness'],
            ],
        );

        DisplayDevice::query()->updateOrCreate(
            ['code' => 'ecopark-mobile-promo-display'],
            [
                'venue_id' => $ecoPark->id,
                'hub_id' => $ravaqHub->id,
                'touchpoint_id' => null,
                'name' => 'نمایشگر سیار تبلیغات محیطی',
                'device_type' => 'mobile_display',
                'status' => RecordStatus::Active,
                'supported_media_formats' => ['image', 'video'],
                'metadata' => ['is_demo' => true, 'inventory_role' => 'mobile_campaign'],
            ],
        );

        $missionTemplates = [
            [
                'code' => 'scan-entry-qr',
                'title' => 'ورود از QR در دروازه اصلی',
                'description' => 'بازدیدکننده از QR ورودی اصلی وارد مسیر پایلوت می‌شود.',
                'mission_type' => 'qr_check_in',
                'trigger_type' => 'qr_scan',
                'point_value' => 120,
                'hub' => $hub,
                'touchpoint' => $touchpoint,
                'reward' => 'نشان ورود پایلوت',
                'reward_code' => 'pilot-entry-badge',
            ],
            [
                'code' => 'discover-route-guide',
                'title' => 'کشف نقطه راهنمای مسیر',
                'description' => 'بازدیدکننده اولین راهنمای مسیر را در محدوده ورودی پیدا می‌کند.',
                'mission_type' => 'location_discovery',
                'trigger_type' => 'manual_check',
                'point_value' => 180,
                'hub' => $hub,
                'touchpoint' => $touchpoint,
                'reward' => 'کوپن نوشیدنی کوچک',
                'reward_code' => 'small-drink-coupon',
            ],
            [
                'code' => 'watch-place-story',
                'title' => 'مشاهده روایت مکان',
                'description' => 'بازدیدکننده روایت یا محتوای معرفی هاب را مشاهده می‌کند.',
                'mission_type' => 'content_view',
                'trigger_type' => 'content_complete',
                'point_value' => 220,
                'hub' => $scienceHub,
                'touchpoint' => null,
                'reward' => 'باز شدن ماموریت ویژه',
                'reward_code' => 'special-mission-unlock',
            ],
            [
                'code' => 'photo-memory-challenge',
                'title' => 'چالش عکس و ثبت خاطره',
                'description' => 'ماموریت لایه عمیق‌تر برای مشارکت کاربر و تولید خاطره کمپین.',
                'mission_type' => 'participation_challenge',
                'trigger_type' => 'admin_approval',
                'point_value' => 260,
                'hub' => $ravaqHub,
                'touchpoint' => null,
                'reward' => 'قرعه‌کشی جایزه پایلوت',
                'reward_code' => 'pilot-prize-draw',
                'unlock_rule' => ['min_points' => 520],
            ],
        ];

        $missionInstances = [];

        foreach ($missionTemplates as $missionData) {
            /** @var Hub $missionHub */
            $missionHub = $missionData['hub'];
            /** @var Touchpoint|null $missionTouchpoint */
            $missionTouchpoint = $missionData['touchpoint'];

            $template = MissionTemplate::query()->updateOrCreate(
                ['code' => $missionData['code']],
                [
                    'title' => $missionData['title'],
                    'description' => $missionData['description'],
                    'mission_type' => $missionData['mission_type'],
                    'trigger_type' => $missionData['trigger_type'],
                    'point_value' => $missionData['point_value'],
                    'status' => RecordStatus::Active,
                    'metadata' => ['is_demo' => true, 'demo_reward' => $missionData['reward']],
                ],
            );

            $missionInstances[$missionData['code']] = MissionInstance::query()->updateOrCreate(
                ['campaign_id' => $campaign->id, 'code' => $missionData['code']],
                [
                    'mission_template_id' => $template->id,
                    'venue_id' => $ecoPark->id,
                    'hub_id' => $missionHub->id,
                    'touchpoint_id' => $missionTouchpoint?->id,
                    'title_override' => null,
                    'status' => RecordStatus::Active,
                    'starts_at' => '2026-06-20 00:00:00',
                    'ends_at' => '2027-03-20 23:59:59',
                    'unlock_rule' => $missionData['unlock_rule'] ?? null,
                    'metadata' => [
                        'is_demo' => true,
                        'layer_reward' => $missionData['reward'],
                        'reward_code' => $missionData['reward_code'],
                        'visitor_instruction' => $missionData['description'],
                        'completion_evidence' => match ($missionData['trigger_type']) {
                            'qr_scan' => "\u{0627}\u{0633}\u{06A9}\u{0646} QR \u{0647}\u{0645}\u{06CC}\u{0646} \u{0646}\u{0642}\u{0637}\u{0647}",
                            'manual_check' => "\u{062F}\u{0646}\u{0628}\u{0627}\u{0644} \u{06A9}\u{0631}\u{062F}\u{0646} \u{0631}\u{0627}\u{0647}\u{0646}\u{0645}\u{0627}\u{06CC} \u{0645}\u{0633}\u{06CC}\u{0631} \u{062F}\u{0631} \u{0645}\u{062D}\u{0644}",
                            'content_complete' => "\u{0645}\u{0634}\u{0627}\u{0647}\u{062F}\u{0647} \u{0645}\u{062D}\u{062A}\u{0648}\u{0627} \u{0648} \u{062A}\u{0627}\u{06CC}\u{06CC}\u{062F} \u{0627}\u{062F}\u{0627}\u{0645}\u{0647} \u{0645}\u{0633}\u{06CC}\u{0631}",
                            'admin_approval' => "\u{062A}\u{0627}\u{06CC}\u{06CC}\u{062F} \u{0645}\u{062C}\u{0631}\u{06CC} \u{06CC}\u{0627} \u{0627}\u{062F}\u{0645}\u{06CC}\u{0646} \u{06A9}\u{0645}\u{067E}\u{06CC}\u{0646}",
                        },
                        'success_message' => "\u{0645}\u{0623}\u{0645}\u{0648}\u{0631}\u{06CC}\u{062A} \u{06A9}\u{0627}\u{0645}\u{0644} \u{0634}\u{062F} \u{0648} \u{0645}\u{0631}\u{062D}\u{0644}\u{0647} \u{0628}\u{0639}\u{062F}\u{06CC} \u{0645}\u{0633}\u{06CC}\u{0631} \u{0628}\u{0631}\u{0627}\u{06CC} \u{0634}\u{0645}\u{0627} \u{0628}\u{0627}\u{0632} \u{0645}\u{06CC}\u{200C}\u{0634}\u{0648}\u{062F}.",
                    ],
                ],
            );
        }

        $treasure = Treasure::query()->updateOrCreate(
            ['campaign_id' => $campaign->id, 'code' => 'eco-family-route-treasure'],
            [
                'venue_id' => $ecoPark->id,
                'mission_instance_id' => $missionInstances['photo-memory-challenge']->id,
                'name' => 'گنج مسیر خانوادگی اکوپارک',
                'treasure_type' => 'family_team',
                'status' => RecordStatus::Active,
                'reveal_rule' => ['required_completed_missions' => 3, 'team_or_family_bonus' => true],
                'metadata' => ['is_demo' => true, 'demo_layer' => 'team_family_incentive'],
            ],
        );

        $cafePartner = PartnerAccount::query()->where('code', 'cafe-eco')->first();
        $sponsorPartner = PartnerAccount::query()->where('code', 'family-route-sponsor')->first();

        $rewardDefinitions = [
            [
                'code' => 'pilot-entry-badge',
                'name' => 'نشان ورود پایلوت',
                'reward_type' => 'badge',
                'partner' => null,
                'point_cost' => null,
                'stock_quantity' => null,
            ],
            [
                'code' => 'small-drink-coupon',
                'name' => 'کوپن نوشیدنی کوچک',
                'reward_type' => 'partner_coupon',
                'partner' => $cafePartner,
                'point_cost' => 180,
                'stock_quantity' => 500,
            ],
            [
                'code' => 'special-mission-unlock',
                'name' => 'باز شدن ماموریت ویژه',
                'reward_type' => 'mission_unlock',
                'partner' => null,
                'point_cost' => null,
                'stock_quantity' => null,
            ],
            [
                'code' => 'pilot-prize-draw',
                'name' => 'قرعه‌کشی جایزه پایلوت',
                'reward_type' => 'sponsor_prize_draw',
                'partner' => $sponsorPartner,
                'point_cost' => 520,
                'stock_quantity' => 100,
            ],
        ];

        foreach ($rewardDefinitions as $rewardData) {
            /** @var PartnerAccount|null $rewardPartner */
            $rewardPartner = $rewardData['partner'];

            $reward = RewardDefinition::query()->updateOrCreate(
                ['campaign_id' => $campaign->id, 'code' => $rewardData['code']],
                [
                    'venue_id' => $ecoPark->id,
                    'partner_account_id' => $rewardPartner?->id,
                    'name' => $rewardData['name'],
                    'reward_type' => $rewardData['reward_type'],
                    'point_cost' => $rewardData['point_cost'],
                    'stock_quantity' => $rewardData['stock_quantity'],
                    'status' => RecordStatus::Active,
                    'metadata' => ['is_demo' => true],
                ],
            );

            if ($rewardPartner && $reward->stock_quantity) {
                RewardInventoryAllocation::query()->updateOrCreate(
                    [
                        'reward_definition_id' => $reward->id,
                        'partner_account_id' => $rewardPartner->id,
                    ],
                    [
                        'treasure_id' => $reward->code === 'pilot-prize-draw' ? $treasure->id : null,
                        'campaign_id' => $campaign->id,
                        'mission_instance_id' => null,
                        'allocated_quantity' => $reward->stock_quantity,
                        'reserved_quantity' => 0,
                        'redeemed_quantity' => 0,
                        'status' => RecordStatus::Active,
                        'metadata' => ['source' => 'pilot_seed', 'is_demo' => true],
                    ],
                );
            }
        }
    }
}
