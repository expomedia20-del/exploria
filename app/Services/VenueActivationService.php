<?php

namespace App\Services;

use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\CampaignParticipant;
use App\Models\CampaignSponsorship;
use App\Models\DisplayDevice;
use App\Models\FinancialAccount;
use App\Models\Hub;
use App\Models\HubManagementAssignment;
use App\Models\MissionInstance;
use App\Models\MissionTemplate;
use App\Models\PartnerAccount;
use App\Models\PartnerLocation;
use App\Models\QrCode;
use App\Models\RewardDefinition;
use App\Models\RewardInventoryAllocation;
use App\Models\SponsorAccount;
use App\Models\Touchpoint;
use App\Models\Treasure;
use App\Models\User;
use App\Models\UserAccessScope;
use App\Models\Venue;
use App\Models\Zone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VenueActivationService
{
    public function __construct(private readonly FinancialLedgerService $financialLedger) {}

    /** @return array<string, mixed> */
    public function activate(Venue $venue): array
    {
        return DB::transaction(function () use ($venue): array {
            $prefix = $this->prefix($venue);
            $this->activateProfile($venue);

            $coreZone = $this->zone($venue, 'activation-core-zone', 'محدوده عملیاتی '.$venue->name);
            $entryHub = $this->hub($coreZone, 'visitor-welcome-hub', 'هاب خوش‌آمدگویی '.$venue->name, 'experience');
            $commercialHub = $this->hub($coreZone, 'ravaq-commercial-hub', 'هاب تجاری '.$venue->name, 'commercial_cluster');
            $rewardHub = $this->hub($coreZone, 'reward-service-hub', 'هاب پاداش و خدمات '.$venue->name, 'reward_service');

            $entryTouchpoint = $this->touchpoint($entryHub, 'activation-entry-qr', 'نقطه ورود QR '.$venue->name, 'qr_stand');
            $missionTouchpoint = $this->touchpoint($entryHub, 'activation-mission-checkpoint', 'نقطه مأموریت '.$venue->name, 'mission_checkpoint');
            $rewardTouchpoint = $this->touchpoint($rewardHub, 'activation-reward-counter', 'نقطه تحویل پاداش '.$venue->name, 'reward_counter');

            $campaign = $this->campaign($venue, $prefix);
            $this->qrCode($venue, $campaign, $entryTouchpoint, $prefix);
            $this->displayDevice($venue, $entryHub, $entryTouchpoint, $prefix);

            $partners = [
                $this->partner($venue, $commercialHub, 'activation-cafe', 'کافه منتخب '.$venue->name, 'food_reward_point', 'reward_redemption'),
                $this->partner($venue, $commercialHub, 'activation-shop', 'واحد تجاری منتخب '.$venue->name, 'member_shop', 'commercial_activation'),
                $this->partner($venue, $rewardHub, 'activation-sponsor-partner', 'حامی داخلی '.$venue->name, 'sponsor', 'route_sponsor'),
            ];

            foreach ($partners as $partner) {
                $this->participant($campaign, $partner);
            }

            $missions = $this->missions($venue, $campaign, $entryHub, $missionTouchpoint, $rewardHub, $rewardTouchpoint);
            $treasure = $this->treasure($venue, $campaign, $missions[3]);
            $sponsorship = $this->sponsorship($venue, $campaign, $prefix);
            $rewards = $this->rewards($venue, $campaign, $partners, $missions[2], $treasure, $sponsorship);
            $this->inventory($campaign, $rewards[1], $partners[0], $missions[2], $treasure, 100);
            $this->inventory($campaign, $rewards[2], $partners[2], $missions[3], $treasure, 50);
            $this->accessScopes($venue, $commercialHub, $partners);

            return [
                'venue' => $venue->refresh(),
                'campaign' => $campaign->refresh(),
                'counts' => [
                    'zones' => Zone::query()->where('venue_id', $venue->id)->count(),
                    'hubs' => Hub::query()->whereHas('zone', fn ($query) => $query->where('venue_id', $venue->id))->count(),
                    'touchpoints' => Touchpoint::query()->whereHas('hub.zone', fn ($query) => $query->where('venue_id', $venue->id))->count(),
                    'campaigns' => Campaign::query()->where('venue_id', $venue->id)->count(),
                    'qrCodes' => QrCode::query()->where('venue_id', $venue->id)->count(),
                    'partners' => PartnerAccount::query()->where('venue_id', $venue->id)->count(),
                    'missions' => MissionInstance::query()->where('venue_id', $venue->id)->count(),
                    'rewards' => RewardDefinition::query()->where('venue_id', $venue->id)->count(),
                ],
            ];
        });
    }

    private function activateProfile(Venue $venue): void
    {
        $metadata = is_array($venue->metadata) ? $venue->metadata : [];
        $profile = is_array($metadata['location_profile'] ?? null) ? $metadata['location_profile'] : [];
        $facilities = $this->facilities($profile);
        $constraints = $profile['constraints'] ?? [];

        if (! is_array($constraints) || count($constraints) === 0) {
            $constraints = ['بازبینی میدانی محل نصب QR', 'تأیید ظرفیت واحدهای تجاری و پاداش پیش از اجرای عمومی'];
        }

        $metadata['activation'] = [
            'status' => 'activated',
            'source' => 'admin_venue_activation',
            'activated_at' => now()->toIso8601String(),
        ];

        $metadata['location_profile'] = [
            ...$profile,
            'venue_type' => $profile['venue_type'] ?? 'mixed',
            'primary_audience' => $profile['primary_audience'] ?? 'خانواده، گردشگر، بازدیدکننده عمومی',
            'official_website_url' => $profile['official_website_url'] ?? null,
            'manual_research_notes' => $profile['manual_research_notes'] ?? 'اسکلت عملیاتی مکان برای اتصال زون، هاب، نقطه تماس، QR، کمپین، مأموریت، پاداش، شریک و نمایشگر فعال شد.',
            'facilities' => $facilities,
            'constraints' => $constraints,
            'updated_at' => now()->toIso8601String(),
        ];

        $venue->update([
            'status' => RecordStatus::Active,
            'profile_status' => RecordStatus::Active,
            'metadata' => $metadata,
        ]);
    }

    /**
     * @param  array<string, mixed>  $profile
     * @return array<int, array{name: string, function: string, campaignUses: array<int, string>, priority: string, notes: string|null, confidence: string, fieldReviewRequired: bool, source: string}>
     */
    private function facilities(array $profile): array
    {
        $facilities = collect(is_array($profile['facilities'] ?? null) ? $profile['facilities'] : [])
            ->filter(fn (mixed $item): bool => is_array($item) && filled($item['name'] ?? null))
            ->values();

        $defaults = collect([
            ['name' => 'دروازه ورود', 'function' => 'access', 'campaignUses' => ['qr', 'mission'], 'priority' => 'primary', 'notes' => 'شروع مسیر بازدید و اسکن QR', 'confidence' => 'medium', 'fieldReviewRequired' => true, 'source' => 'activation'],
            ['name' => 'مسیر کشف مکان', 'function' => 'discovery', 'campaignUses' => ['mission', 'treasure'], 'priority' => 'primary', 'notes' => 'مسیر پایه مأموریت و گنج', 'confidence' => 'medium', 'fieldReviewRequired' => true, 'source' => 'activation'],
            ['name' => 'واحد پاداش', 'function' => 'retail', 'campaignUses' => ['reward', 'sponsor'], 'priority' => 'primary', 'notes' => 'تحویل پاداش و پیشنهاد واحد تجاری', 'confidence' => 'medium', 'fieldReviewRequired' => true, 'source' => 'activation'],
            ['name' => 'نقطه نمایش و اطلاع‌رسانی', 'function' => 'media', 'campaignUses' => ['ad', 'display'], 'priority' => 'secondary', 'notes' => 'نمایش پیام کمپین و اسپانسر', 'confidence' => 'medium', 'fieldReviewRequired' => true, 'source' => 'activation'],
        ]);

        return $facilities
            ->merge($defaults)
            ->unique(fn (array $item): string => Str::lower(trim((string) $item['name'])))
            ->take(12)
            ->map(function (array $item): array {
                $campaignUses = is_array($item['campaignUses'] ?? null)
                    ? $item['campaignUses']
                    : ['mission'];
                $notes = $item['notes'] ?? null;

                return [
                    'name' => (string) $item['name'],
                    'function' => (string) ($item['function'] ?? 'discovery'),
                    'campaignUses' => array_values(array_unique(array_map(
                        static fn (mixed $use): string => (string) $use,
                        $campaignUses,
                    ))),
                    'priority' => (string) ($item['priority'] ?? 'secondary'),
                    'notes' => filled($notes) ? (string) $notes : null,
                    'confidence' => (string) ($item['confidence'] ?? 'medium'),
                    'fieldReviewRequired' => (bool) ($item['fieldReviewRequired'] ?? true),
                    'source' => (string) ($item['source'] ?? 'venue_profile'),
                ];
            })
            ->values()
            ->all();
    }

    private function zone(Venue $venue, string $code, string $name): Zone
    {
        return Zone::query()->updateOrCreate(
            ['venue_id' => $venue->id, 'code' => $code],
            ['name' => $name, 'status' => RecordStatus::Active, 'metadata' => ['source' => 'admin_venue_activation']],
        );
    }

    private function hub(Zone $zone, string $code, string $name, string $type): Hub
    {
        return Hub::query()->updateOrCreate(
            ['zone_id' => $zone->id, 'code' => $code],
            ['name' => $name, 'hub_type' => $type, 'status' => RecordStatus::Active, 'metadata' => ['source' => 'admin_venue_activation']],
        );
    }

    private function touchpoint(Hub $hub, string $code, string $label, string $type): Touchpoint
    {
        return Touchpoint::query()->updateOrCreate(
            ['hub_id' => $hub->id, 'code' => $code],
            [
                'label' => $label,
                'type' => $type,
                'owner_type' => 'venue',
                'status' => RecordStatus::Active,
                'install_notes' => 'محل دقیق نصب در بازدید میدانی تأیید شود.',
                'metadata' => ['source' => 'admin_venue_activation'],
            ],
        );
    }

    private function campaign(Venue $venue, string $prefix): Campaign
    {
        return Campaign::query()->updateOrCreate(
            ['venue_id' => $venue->id, 'code' => $prefix.'-activation-campaign'],
            [
                'name' => 'کمپین پایه '.$venue->name,
                'campaign_type' => 'venue_activation',
                'status' => RecordStatus::Active,
                'start_at' => now()->startOfDay(),
                'end_at' => now()->addMonths(3)->endOfDay(),
                'metadata' => [
                    'source' => 'admin_venue_activation',
                    'blueprint_code' => 'venue-activation-pilot',
                    'design_source' => 'venue_activation_wizard',
                    'design_venue_id' => $venue->id,
                    'design_venue_code' => $venue->code,
                    'route_reviewed_at' => now()->toIso8601String(),
                    'route_review_notes' => 'اسکلت اولیه مسیر مکان برای QR، مأموریت، پاداش، شریک و نمایشگر آماده شد.',
                ],
            ],
        );
    }

    private function qrCode(Venue $venue, Campaign $campaign, Touchpoint $touchpoint, string $prefix): QrCode
    {
        $code = $prefix.'-entry-qr';

        return QrCode::query()->updateOrCreate(
            ['code' => $code],
            [
                'venue_id' => $venue->id,
                'touchpoint_id' => $touchpoint->id,
                'campaign_id' => $campaign->id,
                'destination_url' => url('/scan/'.$code),
                'label' => 'QR ورود '.$venue->name,
                'status' => RecordStatus::Active,
                'valid_from' => now()->subMinute(),
                'valid_until' => now()->addMonths(3),
                'max_scans_per_user_per_window' => 1,
                'duplicate_window_seconds' => 300,
                'metadata' => ['source' => 'admin_venue_activation', 'role' => 'entry'],
            ],
        );
    }

    private function displayDevice(Venue $venue, Hub $hub, Touchpoint $touchpoint, string $prefix): DisplayDevice
    {
        return DisplayDevice::query()->updateOrCreate(
            ['code' => $prefix.'-activation-display'],
            [
                'venue_id' => $venue->id,
                'hub_id' => $hub->id,
                'touchpoint_id' => $touchpoint->id,
                'name' => 'نمایشگر پایه '.$venue->name,
                'device_type' => 'fixed_display',
                'status' => RecordStatus::Active,
                'supported_media_formats' => ['image', 'video', 'display_banner'],
                'metadata' => ['source' => 'admin_venue_activation'],
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
                'metadata' => ['source' => 'admin_venue_activation'],
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
                'metadata' => ['source' => 'admin_venue_activation'],
            ],
        );

        return $partner;
    }

    private function participant(Campaign $campaign, PartnerAccount $partner): CampaignParticipant
    {
        return CampaignParticipant::query()->updateOrCreate(
            ['campaign_id' => $campaign->id, 'partner_account_id' => $partner->id],
            [
                'venue_id' => $campaign->venue_id,
                'hub_id' => $partner->locations()->value('hub_id'),
                'participant_type' => $partner->partner_type === 'sponsor' ? 'sponsor' : 'partner',
                'participation_role' => $partner->partner_type === 'sponsor' ? 'route_sponsor' : 'reward_redemption',
                'status' => RecordStatus::Active,
                'onboarding_status' => 'ready',
                'joined_at' => now(),
                'metadata' => ['source' => 'admin_venue_activation'],
            ],
        );
    }

    /** @return array<int, MissionInstance> */
    private function missions(Venue $venue, Campaign $campaign, Hub $entryHub, Touchpoint $missionTouchpoint, Hub $rewardHub, Touchpoint $rewardTouchpoint): array
    {
        $templates = [
            $this->missionTemplate('venue-activation-checkin', 'شروع بازدید', 'qr_check_in', 'qr_scan', 10),
            $this->missionTemplate('venue-activation-discovery', 'کشف مسیر مکان', 'discovery', 'manual', 20),
            $this->missionTemplate('venue-activation-reward', 'مراجعه به واحد پاداش', 'partner_visit', 'manual', 20),
            $this->missionTemplate('venue-activation-treasure', 'گنج پایانی مکان', 'treasure', 'manual', 30),
        ];

        return [
            $this->mission($venue, $campaign, $templates[0], $entryHub, $missionTouchpoint, 'welcome', 'شروع مسیر '.$venue->name),
            $this->mission($venue, $campaign, $templates[1], $entryHub, $missionTouchpoint, 'discover', 'کشف مسیر '.$venue->name),
            $this->mission($venue, $campaign, $templates[2], $rewardHub, $rewardTouchpoint, 'reward-visit', 'مراجعه به واحد پاداش '.$venue->name),
            $this->mission($venue, $campaign, $templates[3], $rewardHub, $rewardTouchpoint, 'treasure-finale', 'گنج پایانی '.$venue->name),
        ];
    }

    private function missionTemplate(string $code, string $title, string $type, string $trigger, int $points): MissionTemplate
    {
        return MissionTemplate::query()->updateOrCreate(
            ['code' => $code],
            [
                'title' => $title,
                'description' => 'قالب پایه برای فعال‌سازی مکان‌های اکسپلوریا.',
                'mission_type' => $type,
                'trigger_type' => $trigger,
                'point_value' => $points,
                'status' => RecordStatus::Active,
                'metadata' => ['source' => 'admin_venue_activation'],
            ],
        );
    }

    private function mission(Venue $venue, Campaign $campaign, MissionTemplate $template, Hub $hub, Touchpoint $touchpoint, string $code, string $title): MissionInstance
    {
        return MissionInstance::query()->updateOrCreate(
            ['campaign_id' => $campaign->id, 'code' => $code],
            [
                'mission_template_id' => $template->id,
                'venue_id' => $venue->id,
                'hub_id' => $hub->id,
                'touchpoint_id' => $touchpoint->id,
                'title_override' => $title,
                'status' => RecordStatus::Active,
                'starts_at' => now()->startOfDay(),
                'ends_at' => now()->addMonths(3)->endOfDay(),
                'unlock_rule' => ['type' => 'sequential'],
                'metadata' => ['source' => 'admin_venue_activation'],
            ],
        );
    }

    private function treasure(Venue $venue, Campaign $campaign, MissionInstance $mission): Treasure
    {
        return Treasure::query()->updateOrCreate(
            ['campaign_id' => $campaign->id, 'code' => 'activation-final-treasure'],
            [
                'venue_id' => $venue->id,
                'mission_instance_id' => $mission->id,
                'name' => 'گنج پایانی '.$venue->name,
                'treasure_type' => 'final_reward',
                'status' => RecordStatus::Active,
                'reveal_rule' => ['type' => 'mission_completion', 'mission_code' => $mission->code],
                'metadata' => ['source' => 'admin_venue_activation'],
            ],
        );
    }

    /**
     * @param  array<int, PartnerAccount>  $partners
     * @return array{RewardDefinition, RewardDefinition, RewardDefinition}
     */
    private function rewards(Venue $venue, Campaign $campaign, array $partners, MissionInstance $mission, Treasure $treasure, CampaignSponsorship $sponsorship): array
    {
        $this->financialLedger->ensureDefaultSetup();
        $platformCostOwner = FinancialAccount::query()->where('account_key', 'exploria-platform-main')->firstOrFail();
        $partnerCostOwner = $this->financialLedger->ensurePartnerAccount($partners[0]);
        $sponsorCostOwner = $this->financialLedger->ensureSponsorAccount($sponsorship->sponsorAccount);

        return [
            RewardDefinition::query()->updateOrCreate(
                ['campaign_id' => $campaign->id, 'code' => 'activation-points-reward'],
                [
                    'venue_id' => $venue->id,
                    'partner_account_id' => null,
                    'name' => 'امتیاز پایه '.$venue->name,
                    'reward_type' => 'points',
                    'inventory_mode' => 'non_inventory',
                    'point_cost' => null,
                    'stock_quantity' => null,
                    'cost_owner_financial_account_id' => $platformCostOwner->id,
                    'available_from' => $campaign->start_at,
                    'available_until' => $campaign->end_at,
                    'expires_after_minutes' => null,
                    'per_user_award_limit' => 1,
                    'status' => RecordStatus::Active,
                    'metadata' => ['source' => 'admin_venue_activation', 'approval_status' => 'approved'],
                ],
            ),
            RewardDefinition::query()->updateOrCreate(
                ['campaign_id' => $campaign->id, 'code' => 'activation-partner-reward'],
                [
                    'venue_id' => $venue->id,
                    'partner_account_id' => $partners[0]->id,
                    'name' => 'پاداش واحد تجاری '.$venue->name,
                    'reward_type' => 'partner_coupon',
                    'inventory_mode' => 'finite',
                    'point_cost' => 40,
                    'stock_quantity' => 100,
                    'cost_owner_financial_account_id' => $partnerCostOwner->id,
                    'available_from' => $campaign->start_at,
                    'available_until' => $campaign->end_at,
                    'expires_after_minutes' => 10080,
                    'per_user_award_limit' => 1,
                    'status' => RecordStatus::Active,
                    'metadata' => ['source' => 'partner_offer_submission', 'approval_status' => 'approved'],
                ],
            ),
            RewardDefinition::query()->updateOrCreate(
                ['campaign_id' => $campaign->id, 'code' => 'activation-sponsor-reward'],
                [
                    'venue_id' => $venue->id,
                    'partner_account_id' => $partners[2]->id,
                    'name' => 'مشوق اسپانسری '.$venue->name,
                    'reward_type' => 'sponsor_bonus',
                    'inventory_mode' => 'finite',
                    'point_cost' => 80,
                    'stock_quantity' => 50,
                    'cost_owner_financial_account_id' => $sponsorCostOwner->id,
                    'available_from' => $campaign->start_at,
                    'available_until' => $campaign->end_at,
                    'expires_after_minutes' => 10080,
                    'per_user_award_limit' => 1,
                    'status' => RecordStatus::Active,
                    'metadata' => [
                        'source' => 'admin_sponsor_activation',
                        'approval_status' => 'approved',
                        'assignment_status' => 'assigned_to_mission',
                        'mission_instance_id' => $mission->id,
                        'treasure_id' => $treasure->id,
                    ],
                ],
            ),
        ];
    }

    private function inventory(Campaign $campaign, RewardDefinition $reward, PartnerAccount $partner, MissionInstance $mission, Treasure $treasure, int $quantity): RewardInventoryAllocation
    {
        return RewardInventoryAllocation::query()->updateOrCreate(
            ['reward_definition_id' => $reward->id, 'partner_account_id' => $partner->id],
            [
                'treasure_id' => $treasure->id,
                'campaign_id' => $campaign->id,
                'sponsor_proposal_activation_id' => null,
                'mission_instance_id' => $mission->id,
                'allocated_quantity' => $quantity,
                'reserved_quantity' => 0,
                'redeemed_quantity' => 0,
                'status' => 'active',
                'metadata' => ['source' => 'admin_venue_activation'],
            ],
        );
    }

    private function sponsorship(Venue $venue, Campaign $campaign, string $prefix): CampaignSponsorship
    {
        $sponsor = SponsorAccount::query()->updateOrCreate(
            ['code' => $prefix.'-activation-sponsor'],
            [
                'venue_id' => $venue->id,
                'name' => 'اسپانسر پایه '.$venue->name,
                'sponsor_type' => 'internal',
                'status' => RecordStatus::Active,
                'contact_name' => 'مسئول اسپانسر '.$venue->name,
                'contact_mobile' => '09120000000',
                'website_url' => null,
                'metadata' => ['source' => 'admin_venue_activation'],
            ],
        );

        $sponsorship = CampaignSponsorship::query()->updateOrCreate(
            ['campaign_id' => $campaign->id, 'sponsor_account_id' => $sponsor->id],
            [
                'sponsorship_goal' => 'route_activation',
                'package_type' => 'pilot_activation',
                'status' => RecordStatus::Active,
                'budget_amount' => 0,
                'contract_value' => 0,
                'starts_at' => now()->startOfDay(),
                'ends_at' => now()->addMonths(3)->endOfDay(),
                'notes' => 'اسپانسر پایه برای فعال‌سازی چرخه مکان.',
                'metadata' => ['source' => 'admin_venue_activation'],
            ],
        );

        $sponsorship->setRelation('sponsorAccount', $sponsor);

        return $sponsorship;
    }

    /** @param array<int, PartnerAccount> $partners */
    private function accessScopes(Venue $venue, Hub $hub, array $partners): void
    {
        $venueManager = $this->user('venue.manager.'.$this->prefix($venue).'@example.test', 'مدیر مکان '.$venue->name, UserRole::Viewer);
        $hubManager = $this->user('hub.manager.'.$this->prefix($venue).'@example.test', 'مدیر هاب '.$venue->name, UserRole::HubManager);
        $shopManager = $this->user('shop.manager.'.$this->prefix($venue).'@example.test', 'مدیر واحد '.$venue->name, UserRole::ShopPartner);
        $sponsorManager = $this->user('sponsor.manager.'.$this->prefix($venue).'@example.test', 'مدیر اسپانسر '.$venue->name, UserRole::Sponsor);

        $this->scope($venueManager, 'venue_executive', 'venue', $venue->id);
        $this->scope($hubManager, 'hub_manager', 'hub', $hub->id);
        $this->scope($shopManager, 'shop_manager', 'partner', $partners[0]->id);
        $this->scope($sponsorManager, 'internal_sponsor', 'partner', $partners[2]->id);

        HubManagementAssignment::query()->updateOrCreate(
            ['hub_id' => $hub->id, 'user_id' => $hubManager->id, 'assignment_role' => 'hub_manager'],
            ['status' => RecordStatus::Active, 'metadata' => ['source' => 'admin_venue_activation']],
        );
    }

    private function user(string $email, string $name, UserRole $role): User
    {
        return User::query()->updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => Str::random(32), 'role' => $role],
        );
    }

    private function scope(User $user, string $roleKey, string $scopeType, string $scopeId): UserAccessScope
    {
        return UserAccessScope::query()->updateOrCreate(
            ['user_id' => $user->id, 'role_key' => $roleKey, 'scope_type' => $scopeType, 'scope_id' => $scopeId],
            ['status' => RecordStatus::Active, 'metadata' => ['source' => 'admin_venue_activation']],
        );
    }

    private function prefix(Venue $venue): string
    {
        return Str::slug($venue->code) ?: 'venue-'.$venue->id;
    }
}
