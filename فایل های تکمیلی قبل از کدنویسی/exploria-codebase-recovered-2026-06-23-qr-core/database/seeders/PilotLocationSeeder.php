<?php

namespace Database\Seeders;

use App\Enums\RecordStatus;
use App\Models\Campaign;
use App\Models\Hub;
use App\Models\QrCode;
use App\Models\Touchpoint;
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
