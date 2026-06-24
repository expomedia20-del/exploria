<?php

namespace Database\Seeders;

use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\Hub;
use App\Models\HubManagementAssignment;
use App\Models\PartnerAccount;
use App\Models\PartnerLocation;
use App\Models\PartnerUser;
use App\Models\QrCode;
use App\Models\Touchpoint;
use App\Models\User;
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
            ['zone_id' => $zone->id, 'code' => 'ravaq-commercial-hub'],
            [
                'name' => 'رواق تجاری اکوپارک',
                'hub_type' => 'commercial_ravaq',
                'status' => RecordStatus::Active,
                'metadata' => ['is_demo' => true, 'commercial_role' => 'merchant_cluster'],
            ],
        );

        $foodHub = Hub::query()->updateOrCreate(
            ['zone_id' => $zone->id, 'code' => 'foodcourt-family-hub'],
            [
                'name' => 'هاب فودکورت و خانواده',
                'hub_type' => 'food_family',
                'status' => RecordStatus::Active,
                'metadata' => ['is_demo' => true, 'commercial_role' => 'reward_redemption'],
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
                'name' => 'فروشگاه رواق',
                'partner_type' => 'member_shop',
                'contact_name' => 'مسئول فروشگاه رواق',
                'contact_mobile' => '09120000002',
                'hub' => $ravaqHub,
                'user_email' => 'ravaq.store@example.test',
                'user_name' => 'مدیر فروشگاه رواق',
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
                    'zone_id' => $zone->id,
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
        }

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
    }
}
