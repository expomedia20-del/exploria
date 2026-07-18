<?php

namespace App\Services;

use App\Models\AdRequest;
use App\Models\Campaign;
use App\Models\CampaignParticipant;
use App\Models\CampaignSponsorship;
use App\Models\DisplayDevice;
use App\Models\Hub;
use App\Models\HubManagementAssignment;
use App\Models\MissionInstance;
use App\Models\QrCode;
use App\Models\RewardDefinition;
use App\Models\RewardInventoryAllocation;
use App\Models\RewardRedemption;
use App\Models\Treasure;
use App\Models\User;
use App\Models\UserMissionProgress;
use App\Models\UserReward;
use App\Models\Venue;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use ZipArchive;

class VenueRegistryService
{
    private const ABBASABAD_OFFICIAL_SOURCE_SUGGESTIONS = [
        'خانه موسیقی تهران',
        'خانه خلاق هزاره',
        'مرکز نوآوری هزاره',
        'پروژه رواق',
        'یادمان عروج',
        'دریاچه نوروز',
        'بوستان بهشت مادران',
        'میدان مشاهیر',
        'بوستان اکو',
        'آسمان نمای گنبد مینا',
        'پل ابریشم 2',
        'اسکیت پارک',
        'پل طبیعت',
        'پل ابریشم یک',
        'بوستان بنادر',
        'هتل دیدار',
        'فانوس دریایی',
        'اقیانوس پارک',
        'کبوتر خانه',
        'گذر گردشگری آب و آتش',
        'تندیس سیاوش',
        'سجاده نماز',
        'بوستان آب و آتش',
        'برج پرچم',
        'بوستان طالقانی',
        'موزه ملی انقلاب اسلامی و دفاع مقدس',
        'باغ هنر',
        'گذر فرهنگ',
        'دریاچه هنر',
        'کوشک باغ هنر',
        'خانه شعر و ادبیات',
        'باغ غذا',
        'باغ کتاب',
        'موزه نادر ابراهیمی',
    ];

    public function __construct(private readonly UserAccessScopeService $accessScopes) {}

    /** @return Collection<int, covariant array<string, mixed>> */
    public function list(?User $user = null): Collection
    {
        $hubIds = $user ? $this->accessScopes->hubIds($user) : collect();
        $assignedVenueIds = $user ? $this->accessScopes->assignedVenueIds($user) : collect();
        $venueIds = $user ? $this->accessScopes->venueIds($user) : collect();
        $isGlobal = $user === null || $this->accessScopes->hasGlobalAccess($user);

        return Venue::query()
            ->when(! $isGlobal, fn (Builder $query) => $query->whereIn('id', $venueIds))
            ->with([
                'zones.hubs' => fn ($query) => $query->when(! $isGlobal, fn ($query) => $query->where(function ($query) use ($hubIds, $assignedVenueIds): void {
                    $query->whereIn('id', $hubIds)
                        ->orWhereHas('zone', fn ($query) => $query->whereIn('venue_id', $assignedVenueIds));
                })),
                'zones.hubs.touchpoints:id,hub_id,code,label,type,status',
                'zones.hubs.partnerLocations.partnerAccount:id,code,name,partner_type,status',
                'zones.hubs.managementAssignments.user:id,name,email,role',
            ])
            ->withCount(['zones', 'campaigns', 'qrCodes', 'partnerAccounts'])
            ->orderBy('created_at')
            ->get()
            ->toBase()
            ->map(fn (Venue $venue): array => $this->serializeVenue($venue, $isGlobal));
    }

    /** @param array<string, mixed> $data */
    public function updateProfile(Venue $venue, array $data): Venue
    {
        $metadata = is_array($venue->metadata) ? $venue->metadata : [];
        $profile = [
            'venue_type' => $data['venue_type'],
            'primary_audience' => $data['primary_audience'] ?? null,
            'official_website_url' => $data['official_website_url'] ?? null,
            'manual_research_notes' => $data['manual_research_notes'] ?? null,
            'facilities' => $this->facilityItems($data),
            'constraints' => $this->linesToItems($data['constraints_text'] ?? ''),
            'updated_at' => now()->toIso8601String(),
        ];

        $venue->update([
            'metadata' => [
                ...$metadata,
                'location_profile' => $profile,
            ],
        ]);

        return $venue->refresh();
    }

    /** @return array<string, mixed> */
    private function serializeVenue(Venue $venue, bool $isGlobal = true): array
    {
        $zones = $venue->zones->map(fn (Zone $zone): array => [
            'id' => $zone->id,
            'code' => $zone->code,
            'name' => $zone->name,
            'status' => $zone->status->value,
            'hubs' => $zone->hubs->map(fn (Hub $hub): array => [
                'id' => $hub->id,
                'code' => $hub->code,
                'name' => $hub->name,
                'hubType' => $hub->hub_type,
                'status' => $hub->status->value,
                'touchpointsCount' => $hub->touchpoints->count(),
                'partnersCount' => $hub->partnerLocations->count(),
                'managerNames' => $hub->managementAssignments
                    ->map(fn (HubManagementAssignment $assignment): ?string => $assignment->user?->name)
                    ->filter()
                    ->values(),
            ])->values(),
        ])
            ->when(! $isGlobal, fn (Collection $zones): Collection => $zones->filter(fn (array $zone): bool => count($zone['hubs']) > 0))
            ->values();

        $hubsCount = $zones->sum(fn (array $zone): int => count($zone['hubs']));
        $touchpointsCount = $zones->sum(
            fn (array $zone): int => collect($zone['hubs'])->sum('touchpointsCount'),
        );

        $locationProfile = $this->locationProfile($venue);

        return [
            'id' => $venue->id,
            'code' => $venue->code,
            'name' => $venue->name,
            'city' => $venue->city,
            'status' => $venue->status->value,
            'profileStatus' => $venue->profile_status->value,
            'zonesCount' => (int) $venue->getAttribute('zones_count'),
            'hubsCount' => $hubsCount,
            'touchpointsCount' => $touchpointsCount,
            'campaignsCount' => (int) $venue->getAttribute('campaigns_count'),
            'qrCodesCount' => (int) $venue->getAttribute('qr_codes_count'),
            'partnerAccountsCount' => (int) $venue->getAttribute('partner_accounts_count'),
            'locationProfile' => $locationProfile,
            'demoStressPlan' => $this->demoStressPlan($venue, $locationProfile),
            'zones' => $zones,
        ];
    }

    /**
     * @param  array<string, mixed>  $locationProfile
     * @return array<string, mixed>
     */
    private function demoStressPlan(Venue $venue, array $locationProfile): array
    {
        $campaigns = Campaign::query()
            ->where('venue_id', $venue->id)
            ->orderByDesc('created_at')
            ->get(['id', 'code', 'name', 'status', 'metadata']);
        $campaignIds = $campaigns->pluck('id');
        $selectedCampaign = $campaigns->first(fn (Campaign $campaign): bool => ($campaign->metadata['blueprint_code'] ?? null) === 'ecopark-online-treasure-map-game')
            ?? $campaigns->first();
        $campaignCode = $selectedCampaign?->code;
        $blueprintCode = $selectedCampaign?->metadata['blueprint_code'] ?? null;
        $facilities = collect($this->arrayList($locationProfile['facilities'] ?? null));
        $hasUse = fn (string $use): bool => $facilities->contains(fn (array $facility): bool => in_array($use, $facility['campaignUses'] ?? [], true));

        $readyParticipants = CampaignParticipant::query()
            ->whereIn('campaign_id', $campaignIds)
            ->where('onboarding_status', 'ready')
            ->count();
        $sponsorships = CampaignSponsorship::query()->whereIn('campaign_id', $campaignIds)->count();
        $partnerOffers = RewardDefinition::query()
            ->whereIn('campaign_id', $campaignIds)
            ->where('metadata->source', 'partner_offer_submission')
            ->count();
        $sponsorRewards = RewardDefinition::query()
            ->whereIn('campaign_id', $campaignIds)
            ->whereIn('metadata->source', ['sponsor_proposal_activation', 'admin_sponsor_activation'])
            ->count();
        $assignedSponsorRewards = RewardDefinition::query()
            ->whereIn('campaign_id', $campaignIds)
            ->whereIn('metadata->source', ['sponsor_proposal_activation', 'admin_sponsor_activation'])
            ->where('metadata->assignment_status', 'assigned_to_mission')
            ->count();
        $approvedRewards = RewardDefinition::query()
            ->whereIn('campaign_id', $campaignIds)
            ->where('status', 'active')
            ->where(function (Builder $query): void {
                $query->where('metadata->approval_status', 'approved')
                    ->orWhereNull('metadata->approval_status');
            })
            ->count();
        $missions = MissionInstance::query()->whereIn('campaign_id', $campaignIds)->count();
        $treasures = Treasure::query()->whereIn('campaign_id', $campaignIds)->count();
        $qrCodes = QrCode::query()->whereIn('campaign_id', $campaignIds)->count();
        $entryQrCode = QrCode::query()
            ->whereIn('campaign_id', $campaignIds)
            ->orderBy('created_at')
            ->value('code');
        $routeReviewed = $campaigns->contains(fn (Campaign $campaign): bool => filled($campaign->metadata['route_reviewed_at'] ?? null));
        $allocations = RewardInventoryAllocation::query()->whereIn('campaign_id', $campaignIds);
        $activeAllocations = (clone $allocations)->where('status', 'active')->count();
        $allocatedQuantity = (int) (clone $allocations)->sum('allocated_quantity');
        $completedMissions = UserMissionProgress::query()
            ->whereHas('missionInstance', fn (Builder $query) => $query->whereIn('campaign_id', $campaignIds))
            ->where('status', 'completed')
            ->count();
        $issuedRewards = UserReward::query()->whereIn('campaign_id', $campaignIds)->count();
        $redemptions = RewardRedemption::query()
            ->whereHas('userReward', fn (Builder $query) => $query->whereIn('campaign_id', $campaignIds))
            ->count();
        $confirmedRedemptions = RewardRedemption::query()
            ->whereHas('userReward', fn (Builder $query) => $query->whereIn('campaign_id', $campaignIds))
            ->where('status', 'confirmed')
            ->count();
        $mediaAssets = AdRequest::query()->where('venue_id', $venue->id)->count()
            + DisplayDevice::query()->where('venue_id', $venue->id)->count();

        $items = [
            $this->demoStressItem('venue', 'ارزیابی مکان', 'ادمین / مدیر مکان', $locationProfile['readinessScore'] >= 70 && $facilities->count() >= 3, 'شناخت مکان، مخاطب، امکانات، محدودیت‌ها و قابلیت‌های QR/ماموریت/گنج/پاداش باید ثبت شود.', '/admin/venues', $locationProfile['readinessScore'].'٪ آمادگی'),
            $this->demoStressItem('blueprint', 'انتخاب الگو و ساخت کمپین', 'ادمین / اپراتور', $selectedCampaign !== null && filled($blueprintCode), 'کمپین باید با الگوی مشخص ساخته و در ادامه صفحات حفظ شود.', $campaignCode ? '/admin/campaign-builder?campaign='.$campaignCode : '/admin/campaigns', $campaigns->count().' کمپین'),
            $this->demoStressItem('partner_mix', 'مشارکت فروشگاه‌ها و شرکا', 'ادمین / فروشگاه', $readyParticipants > 0 && $venue->getAttribute('partner_accounts_count') >= 2, 'حداقل چند واحد فروشگاهی/غذایی با نقش اجرایی و وضعیت آماده لازم است.', $this->contextUrl('/admin/campaign-participants', $campaignCode, $blueprintCode, 'participants'), $readyParticipants.' آماده'),
            $this->demoStressItem('sponsor_mix', 'مشارکت اسپانسرها', 'ادمین / اسپانسر', $sponsorships > 0 && $sponsorRewards > 0, 'دمو باید هم مسیر دستی اسپانسر و هم پیشنهاد اسپانسر قابل تبدیل به بسته اجرایی را پوشش دهد.', $this->contextUrl('/admin/sponsors', $campaignCode, $blueprintCode, 'sponsors'), $sponsorRewards.' مشوق اسپانسری'),
            $this->demoStressItem('layered_incentives', 'امتیاز، پاداش و گنج چندلایه', 'ادمین / فروشگاه / اسپانسر', $missions >= 3 && $approvedRewards >= 2 && $treasures > 0 && $assignedSponsorRewards > 0, 'باید امتیاز پایه، پاداش فروشگاهی، پاداش اسپانسری و گنج پنهان در گام‌های مختلف وجود داشته باشد.', $this->contextUrl('/admin/missions', $campaignCode, $blueprintCode, 'components'), $approvedRewards.' پاداش، '.$treasures.' گنج'),
            $this->demoStressItem('qr_entry', 'QR و ورود کاربر', 'ادمین / مدیر هاب', $qrCodes > 0 && $hasUse('qr'), 'کاربر باید از QR درست وارد کمپین درست شود و اولین ماموریت را ببیند.', $this->contextUrl('/admin/qr-codes', $campaignCode, $blueprintCode, 'qr'), $qrCodes.' QR'),
            $this->demoStressItem('route_operations', 'نقشه عملیات و مسیر اجرایی', 'ادمین / مدیر هاب', $routeReviewed && $hasUse('mission'), 'اتصال QR، هاب، مسیر، ماموریت، فروشگاه، اسپانسر، نمایشگر و نقطه تحویل باید تایید شود.', $this->contextUrl('/admin/campaign-operations', $campaignCode, $blueprintCode, 'route'), $routeReviewed ? 'تایید شده' : 'در انتظار'),
            $this->demoStressItem('inventory', 'سهم واحدها و موجودی پاداش', 'ادمین / فروشگاه', $activeAllocations > 0 && $allocatedQuantity > 0, 'پاداش‌های اسپانسری باید سهم واحد، موجودی قابل ارائه، رزرو و مصرف قابل ردیابی داشته باشند.', $this->contextUrl('/admin/missions', $campaignCode, $blueprintCode, 'inventory'), $allocatedQuantity.' آیتم'),
            $this->demoStressItem('visitor_execution', 'اجرای واقعی کاربر', 'کاربر مصرف‌کننده', $completedMissions > 0 && $issuedRewards > 0, 'حداقل یک کاربر باید ماموریت متصل را کامل کند و پاداش یا گنج دریافت کند.', $entryQrCode ? '/scan/'.$entryQrCode : '/admin/qr-codes', $completedMissions.' تکمیل'),
            $this->demoStressItem('redemption', 'تایید مصرف توسط فروشگاه', 'فروشگاه / شریک', $redemptions > 0 && $confirmedRedemptions > 0, 'کد پاداش باید در پنل فروشگاه تایید و موجودی از رزرو به مصرف‌شده منتقل شود.', $campaignCode ? '/partner/dashboard?campaign='.$campaignCode : '/partner/dashboard', $confirmedRedemptions.' تایید'),
            $this->demoStressItem('reporting', 'گزارش نهایی قابل فروش', 'ادمین / اسپانسر', $confirmedRedemptions > 0 && $mediaAssets > 0, 'خروجی نهایی باید اثر کمپین، مصرف پاداش، وضعیت اسپانسر و آمادگی رسانه‌ای را نشان دهد.', $this->contextUrl('/admin/campaign-builder', $campaignCode, $blueprintCode, 'review'), $mediaAssets.' رسانه/تبلیغ'),
        ];
        $completeCount = collect($items)->where('complete', true)->count();
        $nextAction = collect($items)->firstWhere('complete', false);

        return [
            'title' => 'دموی فشار از ارزیابی مکان تا اجرا',
            'selectedCampaign' => $selectedCampaign ? [
                'code' => $selectedCampaign->code,
                'name' => $selectedCampaign->name,
                'blueprintCode' => $blueprintCode,
            ] : null,
            'summary' => [
                'completeCount' => $completeCount,
                'totalCount' => count($items),
                'progress' => (int) round(($completeCount / count($items)) * 100),
                'riskLevel' => $completeCount < 4 ? 'high' : ($completeCount < 8 ? 'medium' : 'low'),
            ],
            'nextAction' => $nextAction,
            'items' => $items,
        ];
    }

    /** @return array<string, mixed> */
    private function demoStressItem(string $key, string $title, string $owner, bool $complete, string $detail, string $actionHref, string $metric): array
    {
        return [
            'key' => $key,
            'title' => $title,
            'owner' => $owner,
            'complete' => $complete,
            'status' => $complete ? 'complete' : 'needs_action',
            'detail' => $detail,
            'actionHref' => $actionHref,
            'metric' => $metric,
        ];
    }

    private function contextUrl(string $path, ?string $campaignCode, mixed $blueprintCode, string $action): string
    {
        $params = array_filter([
            'campaign' => $campaignCode,
            'blueprint' => is_string($blueprintCode) ? $blueprintCode : null,
            'blueprint_action' => $action,
        ]);

        return $path.($params === [] ? '' : '?'.http_build_query($params));
    }

    /** @return array<string, mixed> */
    private function locationProfile(Venue $venue): array
    {
        $profile = Arr::get(is_array($venue->metadata) ? $venue->metadata : [], 'location_profile', []);
        $facilities = $this->normalizeFacilities(Arr::get($profile, 'facilities', []));
        $constraints = collect($this->valueList(Arr::get($profile, 'constraints', [])))->filter()->values();

        return [
            'venueType' => Arr::get($profile, 'venue_type'),
            'primaryAudience' => Arr::get($profile, 'primary_audience'),
            'officialWebsiteUrl' => Arr::get($profile, 'official_website_url'),
            'manualResearchNotes' => Arr::get($profile, 'manual_research_notes'),
            'facilities' => $facilities,
            'constraints' => $constraints,
            'sourceSuggestions' => $this->sourceSuggestions($venue),
            'updatedAt' => Arr::get($profile, 'updated_at'),
            'readinessScore' => $this->profileReadinessScore($profile, $facilities->count()),
        ];
    }

    /** @param array<string, mixed> $profile */
    private function profileReadinessScore(array $profile, int $facilitiesCount): int
    {
        $score = 0;
        $score += filled($profile['venue_type'] ?? null) ? 25 : 0;
        $score += filled($profile['primary_audience'] ?? null) ? 20 : 0;
        $score += filled($profile['official_website_url'] ?? null) ? 15 : 0;
        $score += filled($profile['manual_research_notes'] ?? null) ? 15 : 0;
        $score += min(25, $facilitiesCount * 5);

        return min(100, $score);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, array<string, mixed>>
     */
    private function facilityItems(array $data): array
    {
        $structured = $this->normalizeFacilities($data['facilities'] ?? []);
        $textItems = collect($this->linesToItems($data['facilities_text'] ?? ''))
            ->map(fn (string $name): array => [
                'name' => $name,
                'function' => null,
                'campaignUses' => [],
                'priority' => 'secondary',
                'notes' => null,
            ]);

        return $structured
            ->merge($textItems)
            ->merge($this->fileItems($data['facilities_file'] ?? null))
            ->unique(fn (array $item): string => mb_strtolower($item['name']))
            ->values()
            ->all();
    }

    /** @return Collection<int, covariant array<string, mixed>> */
    private function normalizeFacilities(mixed $items): Collection
    {
        return collect(is_array($items) ? $items : [])
            ->map(function (mixed $item): array {
                if (is_string($item)) {
                    return [
                        'name' => trim($item),
                        'function' => null,
                        'campaignUses' => [],
                        'priority' => 'secondary',
                        'notes' => null,
                    ];
                }

                $item = is_array($item) ? $item : [];

                return [
                    'name' => trim((string) ($item['name'] ?? '')),
                    'function' => blank($item['function'] ?? null) ? null : trim((string) $item['function']),
                    'campaignUses' => collect($this->stringList($item['campaignUses'] ?? $item['campaign_uses'] ?? null))
                        ->unique()
                        ->values()
                        ->all(),
                    'priority' => in_array($item['priority'] ?? null, ['primary', 'secondary', 'low'], true) ? $item['priority'] : 'secondary',
                    'notes' => blank($item['notes'] ?? null) ? null : trim((string) $item['notes']),
                ];
            })
            ->filter(fn (array $item): bool => filled($item['name']))
            ->values();
    }

    /** @return array<int, string> */
    private function linesToItems(?string $value): array
    {
        return collect(preg_split('/\R/u', (string) $value) ?: [])
            ->map(fn (string $line): string => trim($line, " \t\n\r\0\x0B-•*"))
            ->filter()
            ->values()
            ->all();
    }

    /** @return Collection<int, covariant array<string, mixed>> */
    private function fileItems(mixed $file): Collection
    {
        if (! $file instanceof UploadedFile || ! $file->isValid()) {
            return collect();
        }

        if (mb_strtolower($file->getClientOriginalExtension()) === 'xlsx') {
            return $this->xlsxItems($file);
        }

        $content = file_get_contents($file->getRealPath());

        if ($content === false || trim($content) === '') {
            return collect();
        }

        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content) ?? $content;
        $rows = collect(preg_split('/\R/u', $content) ?: [])
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->map(fn (string $line): array => str_getcsv($line, $this->csvDelimiter($line)))
            ->filter(fn (array $row): bool => collect($row)->filter(fn (mixed $cell): bool => filled(trim((string) $cell)))->isNotEmpty())
            ->values();

        if ($rows->isEmpty()) {
            return collect();
        }

        $headerMap = $this->facilityImportHeaderMap($rows->first());

        if ($headerMap !== []) {
            $rows = $rows->skip(1)->values();
        }

        return $rows
            ->map(fn (array $row): array => $this->facilityImportRow($row, $headerMap))
            ->filter(fn (array $item): bool => filled($item['name']))
            ->values();
    }

    /** @return Collection<int, covariant array<string, mixed>> */
    private function xlsxItems(UploadedFile $file): Collection
    {
        if (! class_exists(ZipArchive::class)) {
            return collect();
        }

        $zip = new ZipArchive;

        if ($zip->open($file->getRealPath()) !== true) {
            return collect();
        }

        $sharedStrings = $this->xlsxSharedStrings($zip);
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            return collect();
        }

        $sheet = simplexml_load_string($sheetXml);

        if ($sheet === false) {
            return collect();
        }

        $sheet->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $rows = collect($sheet->xpath('//main:sheetData/main:row') ?: [])
            ->map(function (\SimpleXMLElement $row) use ($sharedStrings): array {
                $cells = [];

                foreach ($row->children('http://schemas.openxmlformats.org/spreadsheetml/2006/main')->c as $cell) {
                    $attributes = $cell->attributes();
                    $reference = (string) ($attributes['r'] ?? '');
                    $column = $this->xlsxColumnIndex($reference);
                    $cells[$column] = $this->xlsxCellValue($cell, $sharedStrings);
                }

                if ($cells === []) {
                    return [];
                }

                ksort($cells);

                return collect(range(0, max(array_keys($cells))))
                    ->map(fn (int $index): string => $cells[$index] ?? '')
                    ->all();
            })
            ->filter(fn (array $row): bool => collect($row)->filter(fn (mixed $cell): bool => filled(trim((string) $cell)))->isNotEmpty())
            ->values();

        if ($rows->isEmpty()) {
            return collect();
        }

        $headerMap = $this->facilityImportHeaderMap($rows->first());

        if ($headerMap !== []) {
            $rows = $rows->skip(1)->values();
        }

        return $rows
            ->map(fn (array $row): array => $this->facilityImportRow($row, $headerMap))
            ->filter(fn (array $item): bool => filled($item['name']))
            ->values();
    }

    /** @return array<int, string> */
    private function xlsxSharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');

        if ($xml === false) {
            return [];
        }

        $shared = simplexml_load_string($xml);

        if ($shared === false) {
            return [];
        }

        $shared->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        return collect($shared->xpath('//main:si') ?: [])
            ->map(function (\SimpleXMLElement $item): string {
                $item->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

                return collect($item->xpath('.//main:t') ?: [])
                    ->map(fn (\SimpleXMLElement $text): string => (string) $text)
                    ->implode('');
            })
            ->all();
    }

    /** @param array<int, string> $sharedStrings */
    private function xlsxCellValue(\SimpleXMLElement $cell, array $sharedStrings): string
    {
        $attributes = $cell->attributes();
        $type = (string) ($attributes['t'] ?? '');
        $children = $cell->children('http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        if ($type === 's') {
            $index = (int) ($children->v ?? -1);

            return $sharedStrings[$index] ?? '';
        }

        if ($type === 'inlineStr') {
            return (string) ($children->is->t ?? '');
        }

        return trim((string) ($children->v ?? ''));
    }

    private function xlsxColumnIndex(string $reference): int
    {
        $letters = preg_replace('/[^A-Z]/', '', strtoupper($reference)) ?: 'A';
        $index = 0;

        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return max(0, $index - 1);
    }

    private function csvDelimiter(string $line): string
    {
        $delimiters = [',', "\t", ';'];

        return collect($delimiters)
            ->mapWithKeys(fn (string $delimiter): array => [$delimiter => substr_count($line, $delimiter)])
            ->sortDesc()
            ->keys()
            ->first() ?? ',';
    }

    /**
     * @param  array<int, mixed>  $row
     * @return array<string, int>
     */
    private function facilityImportHeaderMap(array $row): array
    {
        $aliases = [
            'name' => ['name', 'title', 'facility', 'attraction', 'نام', 'عنوان', 'نام امکان', 'نام جاذبه', 'امکان', 'جاذبه'],
            'function' => ['function', 'type', 'category', 'کارکرد', 'نوع', 'دسته', 'دسته بندی'],
            'campaign_uses' => ['campaign_uses', 'campaign uses', 'uses', 'use', 'کاربرد', 'کارکرد کمپینی', 'کاربرد کمپینی'],
            'priority' => ['priority', 'importance', 'اولویت', 'اهمیت'],
            'parent' => ['parent', 'area', 'zone', 'hub', 'بخش', 'زیرمجموعه', 'والد', 'هاب', 'زون'],
            'notes' => ['notes', 'note', 'description', 'توضیح', 'توضیحات', 'یادداشت'],
        ];

        return collect($row)
            ->map(fn (mixed $cell): string => mb_strtolower(trim((string) $cell)))
            ->flatMap(function (string $cell, int $index) use ($aliases): array {
                foreach ($aliases as $field => $labels) {
                    if (in_array($cell, $labels, true)) {
                        return [$field => $index];
                    }
                }

                return [];
            })
            ->all();
    }

    /**
     * @param  array<int, mixed>  $row
     * @param  array<string, int>  $headerMap
     * @return array<string, mixed>
     */
    private function facilityImportRow(array $row, array $headerMap): array
    {
        $value = fn (string $field, int $fallbackIndex): string => trim((string) ($row[$headerMap[$field] ?? $fallbackIndex] ?? ''));
        $parent = $value('parent', 5);
        $notes = $value('notes', 4);

        if (filled($parent)) {
            $notes = filled($notes) ? "{$notes} | زیرمجموعه: {$parent}" : "زیرمجموعه: {$parent}";
        }

        return [
            'name' => $value('name', 0),
            'function' => $this->normalizeFacilityFunction($value('function', 1)),
            'campaignUses' => $this->normalizeCampaignUses($value('campaign_uses', 2)),
            'priority' => $this->normalizeFacilityPriority($value('priority', 3)),
            'notes' => blank($notes) ? null : $notes,
        ];
    }

    private function normalizeFacilityFunction(string $value): ?string
    {
        $normalized = mb_strtolower(trim($value));

        return match ($normalized) {
            'education', 'آموزشی' => 'education',
            'entertainment', 'تفریحی', 'سرگرمی' => 'entertainment',
            'retail', 'shop', 'store', 'فروشگاهی', 'تجاری', 'خرید' => 'retail',
            'rest', 'استراحت', 'رفاهی' => 'rest',
            'route', 'مسیر', 'جهت یابی', 'جهت‌یابی' => 'route',
            'media', 'تبلیغاتی', 'رسانه' => 'media',
            'reward', 'پاداش', 'تحویل پاداش' => 'reward',
            'discovery', 'کشف', 'گنج' => 'discovery',
            default => blank($normalized) ? null : $value,
        };
    }

    /** @return array<int, string> */
    private function normalizeCampaignUses(string $value): array
    {
        $aliases = [
            'qr' => ['qr', 'کیوآر', 'کیو آر'],
            'mission' => ['mission', 'ماموریت', 'مأموریت'],
            'treasure' => ['treasure', 'گنج'],
            'reward' => ['reward', 'پاداش'],
            'sponsor' => ['sponsor', 'اسپانسر', 'حامی'],
            'ad' => ['ad', 'advertising', 'تبلیغ', 'تبلیغات'],
            'display' => ['display', 'نمایشگر'],
        ];

        return collect(preg_split('/[،,;|]+/u', $value) ?: [])
            ->map(fn (string $item): string => mb_strtolower(trim($item)))
            ->filter()
            ->map(function (string $item) use ($aliases): string {
                foreach ($aliases as $key => $values) {
                    if (in_array($item, $values, true)) {
                        return $key;
                    }
                }

                return $item;
            })
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeFacilityPriority(string $value): string
    {
        return match (mb_strtolower(trim($value))) {
            'primary', 'اصلی', 'زیاد', 'بالا' => 'primary',
            'low', 'کم', 'کم اهمیت', 'کم‌اهمیت' => 'low',
            default => 'secondary',
        };
    }

    /** @return array<int, string> */
    private function sourceSuggestions(Venue $venue): array
    {
        if ($venue->code !== 'ecopark-abbasabad') {
            return [];
        }

        return self::ABBASABAD_OFFICIAL_SOURCE_SUGGESTIONS;
    }

    /** @return list<array<string, mixed>> */
    private function arrayList(mixed $value): array
    {
        $items = $value instanceof Collection ? $value->all() : $value;

        return is_array($items) ? array_values(array_filter($items, is_array(...))) : [];
    }

    /** @return list<mixed> */
    private function valueList(mixed $value): array
    {
        if ($value instanceof Collection) {
            return array_values($value->all());
        }

        return is_array($value) ? array_values($value) : [];
    }

    /** @return list<string> */
    private function stringList(mixed $value): array
    {
        return is_array($value) ? array_values(array_filter($value, is_string(...))) : [];
    }
}
