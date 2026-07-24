<?php

namespace App\Services;

use App\Models\AdEvent;
use App\Models\AdRequest;
use App\Models\Campaign;
use App\Models\GameBonusClaim;
use App\Models\GameChallengeProgress;
use App\Models\GameEntryPass;
use App\Models\GameParty;
use App\Models\GamePartyMember;
use App\Models\QrCode;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EcoParkOnlineGameService
{
    public const CAMPAIGN_CODE = 'ecopark-online-treasure-map-game-campaign';

    public const BLUEPRINT_CODE = 'ecopark-online-treasure-map-game';

    private const STEP_POINTS = [1 => 20, 2 => 30, 3 => 60, 4 => 80, 5 => 120, 6 => 150, 7 => 80, 8 => 80, 9 => 200];

    private const COLLABORATION_BONUS = 30;

    private const SPONSOR_BONUS = 30;

    private const ROUTE_KEYS = ['quick', 'family', 'explorer'];

    private const HOTSPOT_KEYS = ['mina', 'nature', 'fire-water', 'book-garden', 'art-lake', 'taleghani'];

    private const ROUTE_PUZZLES = [
        'quick' => [
            ['key' => 'mina', 'fragment' => '۳', 'hint' => 'نقطه‌ای با سقف نیم‌کره‌ای و روایت آسمان را روی نقشه پیدا کنید.'],
            ['key' => 'nature', 'fragment' => '۱', 'hint' => 'سازه‌ای سه‌طبقه که دو بوستان را به هم پیوند می‌دهد، کدام است؟'],
            ['key' => 'fire-water', 'fragment' => '۷', 'hint' => 'میدانی را پیدا کنید که دو عنصر متضاد در نامش کنار هم آمده‌اند.'],
        ],
        'family' => [
            ['key' => 'nature', 'fragment' => '۲', 'hint' => 'نقطه‌ای را انتخاب کنید که خانواده می‌تواند از روی آن میان دو بوستان حرکت کند.'],
            ['key' => 'fire-water', 'fragment' => '۴', 'hint' => 'کدام میدان، آب و شعله را در یک نام مشترک کنار هم می‌آورد؟'],
            ['key' => 'mina', 'fragment' => '۵', 'hint' => 'حالا سراغ گنبدی بروید که داستان آسمان و ستاره‌ها را روایت می‌کند.'],
        ],
        'explorer' => [
            ['key' => 'fire-water', 'fragment' => '۶', 'hint' => 'از میان نشانه‌ها، محل هم‌نشینی دو عنصر مخالف را پیدا کنید.'],
            ['key' => 'mina', 'fragment' => '۸', 'hint' => 'نقطه‌ای علمی با گنبد آبی و نگاه رو به آسمان را انتخاب کنید.'],
            ['key' => 'nature', 'fragment' => '۴', 'hint' => 'آخرین نشانه، پلی شهری است که دو سوی سبز مسیر را به هم متصل می‌کند.'],
        ],
    ];

    private const PHYSICAL_ROUTE_CHECKPOINTS = [
        'quick' => [
            ['step' => 7, 'key' => 'fire-water', 'title' => 'ایستگاه میدان آب‌وآتش', 'instruction' => 'به میدان آب‌وآتش بروید و QR اکسپلوریا روی استند مرحله را اسکن کنید.'],
            ['step' => 8, 'key' => 'nature', 'title' => 'ایستگاه پل طبیعت', 'instruction' => 'استند اکسپلوریا در ورودی پل طبیعت را پیدا و QR مرحله را اسکن کنید.'],
            ['step' => 9, 'key' => 'ravaq-finish', 'title' => 'گنج پایانی رواق', 'instruction' => 'برای پایان مسیر به رواق تجاری بروید و QR گنج پایانی را اسکن کنید.'],
        ],
        'family' => [
            ['step' => 7, 'key' => 'book-garden', 'title' => 'ایستگاه باغ کتاب', 'instruction' => 'همراه خانواده به استند اکسپلوریا در ورودی باغ کتاب بروید و QR را اسکن کنید.'],
            ['step' => 8, 'key' => 'mina', 'title' => 'ایستگاه گنبد مینا', 'instruction' => 'استند مرحله خانوادگی کنار ورودی گنبد مینا را پیدا و QR را اسکن کنید.'],
            ['step' => 9, 'key' => 'ravaq-finish', 'title' => 'گنج خانوادگی رواق', 'instruction' => 'با اعضای خانواده به رواق تجاری بروید و QR گنج پایانی را اسکن کنید.'],
        ],
        'explorer' => [
            ['step' => 7, 'key' => 'mina', 'title' => 'ایستگاه گنبد مینا', 'instruction' => 'به ورودی گنبد مینا بروید و QR استند اکسپلوریا را اسکن کنید.'],
            ['step' => 8, 'key' => 'nature', 'title' => 'ایستگاه پل طبیعت', 'instruction' => 'نشانه بعدی در ورودی پل طبیعت است؛ QR استند مرحله را اسکن کنید.'],
            ['step' => 9, 'key' => 'ravaq-finish', 'title' => 'گنج نهایی کاوشگر', 'instruction' => 'در رواق تجاری، استند گنج پایانی اکسپلوریا را پیدا و QR آن را اسکن کنید.'],
        ],
    ];

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'modes' => [
                ['key' => 'individual', 'title' => 'انفرادی', 'description' => 'یک مسیر شخصی، با همان پنج چالش و امتیاز کامل.'],
                ['key' => 'family', 'title' => 'خانوادگی', 'description' => 'اعضای خانواده در پیدا کردن نشانه‌ها سهیم می‌شوند؛ بدون ثبت اطلاعات کودک.'],
                ['key' => 'team', 'title' => 'تیمی', 'description' => 'با کد دعوت به دوستان بپیوندید و پیشرفت مشترک بسازید.'],
            ],
            'routes' => [
                ['key' => 'quick', 'title' => 'مسیر سریع', 'duration' => '۴۵ دقیقه', 'description' => 'سه نقطه نزدیک و مناسب بازدید کوتاه.'],
                ['key' => 'family', 'title' => 'مسیر خانوادگی', 'duration' => '۷۵ دقیقه', 'description' => 'ریتم آرام‌تر، توقف بیشتر و راهنمای ساده‌تر.'],
                ['key' => 'explorer', 'title' => 'مسیر کاوشگر', 'duration' => '۹۰ دقیقه', 'description' => 'سرنخ‌های عمیق‌تر برای گروه‌های ماجراجو.'],
            ],
            'hotspots' => [
                ['key' => 'mina', 'title' => 'گنبد مینا', 'description' => 'مرکز علمی آسمان و ستاره‌ها', 'x' => 72, 'y' => 24],
                ['key' => 'nature', 'title' => 'پل طبیعت', 'description' => 'پیونددهنده دو بوستان', 'x' => 35, 'y' => 42],
                ['key' => 'fire-water', 'title' => 'میدان آب‌وآتش', 'description' => 'میدان رویدادهای شهری', 'x' => 60, 'y' => 68],
                ['key' => 'book-garden', 'title' => 'باغ کتاب', 'description' => 'مجموعه فرهنگی و مطالعه', 'x' => 18, 'y' => 73],
                ['key' => 'art-lake', 'title' => 'دریاچه هنر', 'description' => 'فضای آرام کنار آب', 'x' => 82, 'y' => 78],
                ['key' => 'taleghani', 'title' => 'بوستان طالقانی', 'description' => 'بوستان جنگلی مسیر', 'x' => 20, 'y' => 20],
            ],
            'clues' => [
                'quick' => [
                    'question' => 'رمز سه‌رقمی مسیر سریع چیست؟',
                    'instruction' => 'سه تکه‌ای را که به ترتیب کشف کرده‌اید، بدون فاصله وارد کنید.',
                ],
                'family' => [
                    'question' => 'رمز سه‌رقمی مسیر خانوادگی چیست؟',
                    'instruction' => 'تکه‌های کشف‌شده را به همان ترتیب کنار هم بگذارید؛ تصمیم را با همراهان بررسی کنید.',
                ],
                'explorer' => [
                    'question' => 'رمز سه‌رقمی مسیر کاوشگر چیست؟',
                    'instruction' => 'سه رقم به‌دست‌آمده از نقشه را با ترتیب کشف وارد کنید.',
                ],
            ],
            'steps' => [
                ['index' => 1, 'title' => 'ساخت گروه بازی', 'instruction' => 'حالت بازی را انتخاب کنید تا مسیر شما ساخته شود.', 'verification' => 'ورود معتبر و ساخت گروه برای همین دوره'],
                ['index' => 2, 'title' => 'انتخاب مسیر', 'instruction' => 'یکی از سه مسیر را با توجه به زمان و همراهان انتخاب کنید.', 'verification' => 'ثبت یک انتخاب مشخص برای همه اعضا'],
                ['index' => 3, 'title' => 'کشف سه نقطه روی نقشه آنلاین', 'instruction' => 'این مرحله در همین صفحه انجام می‌شود؛ راهنمای جاری را بخوانید و از میان شش نقطه فقط پاسخ منطبق را انتخاب کنید.', 'verification' => 'ترتیب و پاسخ هر کشف در سرور بررسی می‌شود؛ کلیک شانسی مرحله را جلو نمی‌برد'],
                ['index' => 4, 'title' => 'ساخت رمز از تکه‌های سرنخ', 'instruction' => 'سه رقم ثبت‌شده را به ترتیب کشف کنار هم بگذارید و رمز را وارد کنید.', 'verification' => 'رمز ساخته‌شده با مسیر انتخابی در سرور تطبیق داده می‌شود'],
                ['index' => 5, 'title' => 'دریافت مجوز حضور', 'instruction' => 'مجوز یک‌بارمصرف را بسازید و در اکوپارک QR حضور را اسکن کنید.', 'verification' => 'مجوز زمان‌دار و قابل استفاده فقط یک‌بار'],
            ],
            'physicalSteps' => [
                ['index' => 6, 'title' => 'ورود به مرحله حضوری', 'instruction' => 'دروازه حضور بازی را در اکوپارک پیدا و QR آن را اسکن کنید.', 'verification' => 'اسکن QR فیزیکی دروازه با مجوز فعال'],
                ['index' => 7, 'title' => 'ایستگاه حضوری اول', 'instruction' => 'طبق مسیر انتخابی به نخستین ایستگاه بروید و QR همان نقطه را اسکن کنید.', 'verification' => 'QR نقطه درست و مطابق ترتیب مسیر'],
                ['index' => 8, 'title' => 'ایستگاه حضوری دوم', 'instruction' => 'راهنمای مرحله را دنبال و QR ایستگاه بعدی را اسکن کنید.', 'verification' => 'QR نقطه درست و مطابق ترتیب مسیر'],
                ['index' => 9, 'title' => 'گنج پایانی حضوری', 'instruction' => 'به مقصد پایانی بروید و QR گنج را برای تکمیل کل کمپین اسکن کنید.', 'verification' => 'QR پایانی معتبر و تکمیل همه مراحل پیشین'],
            ],
            'rules' => [
                'هر حساب در هر دوره فقط یک مشارکت امتیازدار دارد.',
                'تبلیغ اسپانسری کاملاً اختیاری است و مسیر را قفل نمی‌کند.',
                'در حالت خانوادگی هیچ نام، شماره یا داده شخصی از کودک دریافت نمی‌شود.',
            ],
        ];
    }

    public function campaign(): ?Campaign
    {
        return Campaign::query()
            ->where('status', 'active')
            ->where(function ($query): void {
                $query->where('code', self::CAMPAIGN_CODE)
                    ->orWhere('metadata->blueprint_code', self::BLUEPRINT_CODE)
                    ->orWhere('code', 'ecopark-pilot-1405');
            })
            ->orderByRaw('case when code = ? then 0 when code = ? then 2 else 1 end', [self::CAMPAIGN_CODE, 'ecopark-pilot-1405'])
            ->first();
    }

    public function cycleKey(Campaign $campaign): string
    {
        return (string) data_get($campaign->metadata, 'online_game_cycle_key', 'launch-1405');
    }

    public function partyFor(User $user, Campaign $campaign): ?GameParty
    {
        return GameParty::query()
            ->where('campaign_id', $campaign->id)
            ->where('cycle_key', $this->cycleKey($campaign))
            ->whereHas('members', fn ($query) => $query->where('user_id', $user->id)->where('status', 'active'))
            ->with($this->partyRelations())
            ->first();
    }

    /** @param array<string, mixed> $data */
    public function createParty(User $user, Campaign $campaign, Visit $visit, array $data): GameParty
    {
        if ($visit->user_id !== $user->id || $visit->campaign_id !== $campaign->id) {
            throw ValidationException::withMessages(['visit_id' => 'بازدید انتخاب‌شده متعلق به این کاربر و کمپین نیست.']);
        }

        if ($this->partyFor($user, $campaign)) {
            throw ValidationException::withMessages(['mode' => 'مشارکت امتیازدار شما در این دوره قبلاً ساخته شده است.']);
        }

        $mode = (string) $data['mode'];

        return DB::transaction(function () use ($user, $campaign, $visit, $data, $mode): GameParty {
            $party = GameParty::query()->create([
                'campaign_id' => $campaign->id,
                'visit_id' => $visit->id,
                'owner_user_id' => $user->id,
                'mode' => $mode,
                'name' => $mode === 'individual' ? 'مسیر '.$user->name : ($data['name'] ?? null),
                'invite_code' => $mode === 'team' ? $this->uniqueInviteCode() : null,
                'cycle_key' => $this->cycleKey($campaign),
                'status' => 'active',
                'score' => self::STEP_POINTS[1],
                'expires_at' => $campaign->end_at,
                'metadata' => ['privacy' => $mode === 'family' ? 'anonymous_companions' : null],
            ]);

            GamePartyMember::query()->create([
                'game_party_id' => $party->id,
                'user_id' => $user->id,
                'display_name' => $user->name,
                'member_type' => 'registered',
                'role' => 'leader',
                'status' => 'active',
                'joined_at' => now(),
            ]);

            if ($mode === 'family') {
                $count = (int) ($data['companion_count'] ?? 1);
                foreach (range(1, $count) as $number) {
                    GamePartyMember::query()->create([
                        'game_party_id' => $party->id,
                        'display_name' => 'همراه خانواده '.$number,
                        'member_type' => 'companion',
                        'role' => 'companion',
                        'status' => 'active',
                        'joined_at' => now(),
                    ]);
                }
            }

            foreach (range(1, 5) as $step) {
                GameChallengeProgress::query()->create([
                    'game_party_id' => $party->id,
                    'step_index' => $step,
                    'status' => $step === 1 ? 'completed' : ($step === 2 ? 'available' : 'locked'),
                    'points_awarded' => $step === 1 ? self::STEP_POINTS[1] : 0,
                    'completed_at' => $step === 1 ? now() : null,
                    'metadata' => $step === 1 ? ['verified_by' => 'authenticated_visit'] : null,
                ]);
            }

            return $party->load($this->partyRelations());
        });
    }

    public function joinParty(User $user, Campaign $campaign, string $inviteCode): GameParty
    {
        if ($this->partyFor($user, $campaign)) {
            throw ValidationException::withMessages(['invite_code' => 'شما در این دوره عضو یک گروه بازی هستید.']);
        }

        return DB::transaction(function () use ($user, $campaign, $inviteCode): GameParty {
            $party = GameParty::query()
                ->where('campaign_id', $campaign->id)
                ->where('cycle_key', $this->cycleKey($campaign))
                ->where('mode', 'team')
                ->where('invite_code', Str::upper($inviteCode))
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            if (! $party) {
                throw ValidationException::withMessages(['invite_code' => 'کد دعوت معتبر یا فعال نیست.']);
            }

            if ($party->members()->where('member_type', 'registered')->count() >= 8) {
                throw ValidationException::withMessages(['invite_code' => 'ظرفیت این تیم تکمیل شده است.']);
            }

            GamePartyMember::query()->create([
                'game_party_id' => $party->id,
                'user_id' => $user->id,
                'display_name' => $user->name,
                'member_type' => 'registered',
                'role' => 'member',
                'status' => 'active',
                'joined_at' => now(),
            ]);

            return $party->load($this->partyRelations());
        });
    }

    public function selectRoute(User $user, GameParty $party, string $routeKey): GameParty
    {
        $this->assertMember($user, $party);
        $this->assertOwner($user, $party);
        $this->assertCurrentStep($party, 2);

        if (! in_array($routeKey, self::ROUTE_KEYS, true)) {
            throw ValidationException::withMessages(['route_key' => 'مسیر انتخاب‌شده معتبر نیست.']);
        }

        return DB::transaction(function () use ($party, $routeKey): GameParty {
            $party->update(['route_key' => $routeKey, 'score' => $party->score + self::STEP_POINTS[2]]);
            $this->completeStep($party, 2, self::STEP_POINTS[2], ['route_key' => $routeKey]);
            $this->unlockStep($party, 3);

            return $party->load($this->partyRelations());
        });
    }

    public function discoverHotspot(User $user, GameParty $party, string $hotspotKey, ?string $memberId = null): GameParty
    {
        $actorMember = $this->assertMember($user, $party);
        $this->assertCurrentStep($party, 3);

        if (! in_array($hotspotKey, self::HOTSPOT_KEYS, true)) {
            throw ValidationException::withMessages(['hotspot_key' => 'این نقطه روی نقشه معتبر نیست.']);
        }

        $contributor = $actorMember;
        if ($party->mode === 'family' && $memberId) {
            $contributor = $party->members()->whereKey($memberId)->where('status', 'active')->first();
            if (! $contributor) {
                throw ValidationException::withMessages(['member_id' => 'همراه انتخاب‌شده عضو این خانواده نیست.']);
            }
        }

        return DB::transaction(function () use ($party, $hotspotKey, $contributor): GameParty {
            $progress = $party->progress()->where('step_index', 3)->lockForUpdate()->firstOrFail();
            $found = $this->hotspotFinds($progress);
            $foundKeys = array_column($found, 'key');
            $expected = collect($this->routePuzzle((string) $party->route_key))
                ->first(fn (array $candidate): bool => ! in_array($candidate['key'], $foundKeys, true));

            if (in_array($hotspotKey, $foundKeys, true)) {
                throw ValidationException::withMessages(['hotspot_key' => 'این نقطه قبلاً کشف شده است؛ یک نشانه دیگر را پیدا کنید.']);
            }
            if (! $expected || $expected['key'] !== $hotspotKey) {
                $progress->increment('attempts');
                throw ValidationException::withMessages([
                    'hotspot_key' => 'این نقطه با راهنمای جاری تطبیق ندارد. متن راهنما را دوباره بخوانید و یک نقطه دیگر را امتحان کنید.',
                ]);
            }

            $found[] = [
                'key' => $hotspotKey,
                'fragment' => $expected['fragment'],
                'member_id' => $contributor->id,
                'member_name' => $contributor->display_name,
                'found_at' => now()->toIso8601String(),
            ];
            $progress->update(['attempts' => $progress->attempts + 1, 'metadata' => ['found' => $found]]);

            if (count($found) === 3) {
                $contributors = count(array_unique(array_column($found, 'member_id')));
                $bonus = $party->mode !== 'individual' && $contributors >= 2 ? self::COLLABORATION_BONUS : 0;
                $progress->update([
                    'status' => 'completed',
                    'points_awarded' => self::STEP_POINTS[3] + $bonus,
                    'completed_at' => now(),
                    'metadata' => ['found' => $found, 'collaboration_bonus' => $bonus],
                ]);
                $party->update([
                    'score' => $party->score + self::STEP_POINTS[3] + $bonus,
                    'collaboration_bonus_awarded' => $bonus > 0,
                ]);
                $this->unlockStep($party, 4);
            }

            return $party->load($this->partyRelations());
        });
    }

    public function submitClue(User $user, GameParty $party, string $answerKey): GameParty
    {
        $this->assertMember($user, $party);
        $this->assertCurrentStep($party, 4);
        $answerKey = $this->normalizeDigits($answerKey);
        $expectedAnswer = collect($this->routePuzzle((string) $party->route_key))
            ->pluck('fragment')
            ->map(fn (string $fragment): string => $this->normalizeDigits($fragment))
            ->implode('');
        $progress = $party->progress()->where('step_index', 4)->firstOrFail();
        $progress->increment('attempts');

        if ($expectedAnswer !== $answerKey) {
            throw ValidationException::withMessages(['answer_key' => 'این رمز درست نیست؛ رقم‌های سه نشانه را دقیقاً به ترتیب کشف کنار هم بگذارید.']);
        }

        return DB::transaction(function () use ($party, $answerKey): GameParty {
            $party->increment('score', self::STEP_POINTS[4]);
            $this->completeStep($party, 4, self::STEP_POINTS[4], ['answer_key' => $answerKey, 'verified_by' => 'server']);
            $this->unlockStep($party, 5);

            return $party->load($this->partyRelations());
        });
    }

    public function issuePass(User $user, GameParty $party): GameParty
    {
        $this->assertMember($user, $party);
        $this->assertOwner($user, $party);
        $this->assertCurrentStep($party, 5);

        return DB::transaction(function () use ($user, $party): GameParty {
            $plainToken = Str::random(48);
            $code = 'ECO-'.Str::upper(Str::random(8));

            GameEntryPass::query()->create([
                'game_party_id' => $party->id,
                'issued_to_user_id' => $user->id,
                'code' => $code,
                'token_hash' => hash('sha256', $plainToken),
                'status' => 'active',
                'expires_at' => now()->addDays(7),
                'metadata' => ['onsite_bonus_points' => 150],
            ]);

            $party->update([
                'status' => 'ready_for_visit',
                'score' => $party->score + self::STEP_POINTS[5],
                'completed_at' => null,
            ]);
            $this->completeStep($party, 5, self::STEP_POINTS[5], ['verified_by' => 'signed_entry_pass']);
            $this->ensurePhysicalProgress($party);

            return $party->load($this->partyRelations());
        });
    }

    public function startSponsorBonus(User $user, GameParty $party, string $adRequestId): GameParty
    {
        $this->assertMember($user, $party);
        $ad = $this->approvedGameAd($party, $adRequestId);

        if ($party->bonusClaims()->where('ad_request_id', $ad->id)->exists()) {
            throw ValidationException::withMessages(['ad_request_id' => 'این پیشنهاد اختیاری قبلاً برای گروه شما آغاز یا دریافت شده است.']);
        }

        GameBonusClaim::query()->create([
            'game_party_id' => $party->id,
            'ad_request_id' => $ad->id,
            'started_by_user_id' => $user->id,
            'status' => 'started',
            'started_at' => now(),
            'metadata' => ['required_seconds' => 10],
        ]);
        $this->recordAdEvent($ad, 'game_offer_view', $party);

        return $party->load($this->partyRelations());
    }

    public function completeSponsorBonus(User $user, GameParty $party, string $adRequestId): GameParty
    {
        $this->assertMember($user, $party);
        $ad = $this->approvedGameAd($party, $adRequestId);
        $claim = $party->bonusClaims()->where('ad_request_id', $ad->id)->first();

        if (! $claim || $claim->status !== 'started') {
            throw ValidationException::withMessages(['ad_request_id' => 'ابتدا نمایش اختیاری این پیشنهاد را آغاز کنید.']);
        }
        if ($claim->started_at->isAfter(now()->subSeconds(10))) {
            throw ValidationException::withMessages(['ad_request_id' => 'برای دریافت امتیاز، محتوای کوتاه را تا پایان مشاهده کنید.']);
        }

        return DB::transaction(function () use ($party, $claim, $ad): GameParty {
            $claim->update(['status' => 'completed', 'points_awarded' => self::SPONSOR_BONUS, 'completed_at' => now()]);
            $party->increment('score', self::SPONSOR_BONUS);
            $this->recordAdEvent($ad, 'game_clue_complete', $party);

            return $party->load($this->partyRelations());
        });
    }

    public function assertPhysicalQrAvailable(User $user, QrCode $qr): void
    {
        $role = data_get($qr->metadata, 'online_game_role');

        if (! in_array($role, ['onsite_gate', 'physical_checkpoint'], true)) {
            return;
        }

        $party = GameParty::query()
            ->where('campaign_id', $qr->campaign_id)
            ->whereIn('status', ['ready_for_visit', 'onsite_active', 'completed'])
            ->whereHas('members', fn ($query) => $query->where('user_id', $user->id))
            ->with($this->partyRelations())
            ->first();

        if (! $party) {
            throw ValidationException::withMessages([
                'qr_code' => 'برای استفاده از این QR باید ابتدا بخش آنلاین همین کمپین را تکمیل و مجوز حضور دریافت کنید.',
            ]);
        }

        $this->ensurePhysicalProgress($party);
        $party->refresh()->load($this->partyRelations());

        if ($role === 'onsite_gate') {
            $pass = $party->entryPass;

            if (
                $party->status !== 'ready_for_visit'
                || ! $pass
                || $pass->status !== 'active'
                || $pass->expires_at->isPast()
            ) {
                throw ValidationException::withMessages([
                    'qr_code' => $party->status === 'onsite_active'
                        ? 'ورود حضوری قبلاً تأیید شده است؛ راهنمای ایستگاه بعدی را در صفحه بازی دنبال کنید.'
                        : 'مجوز حضور فعال و معتبری برای این گروه وجود ندارد.',
                ]);
            }

            return;
        }

        if ($party->status !== 'onsite_active') {
            throw ValidationException::withMessages([
                'qr_code' => $party->status === 'completed'
                    ? 'این مسیر حضوری قبلاً به‌طور کامل انجام شده است.'
                    : 'ابتدا QR دروازه حضور بازی را اسکن کنید تا مرحله حضوری فعال شود.',
            ]);
        }

        $checkpointKey = (string) data_get($qr->metadata, 'checkpoint_key');
        $expected = $this->nextPhysicalCheckpoint($party);

        if (! $expected || $expected['key'] !== $checkpointKey) {
            throw ValidationException::withMessages([
                'qr_code' => 'این QR مربوط به ایستگاه جاری شما نیست. راهنمای مرحله حضوری را ببینید و به نقطه درست بروید.',
            ]);
        }
    }

    public function redeemOnsiteVisit(User $user, Visit $visit): void
    {
        $qr = $visit->qrCode;
        $role = data_get($qr?->metadata, 'online_game_role');

        if (! $qr || ! in_array($role, ['onsite_gate', 'physical_checkpoint'], true)) {
            return;
        }

        $this->assertPhysicalQrAvailable($user, $qr);

        DB::transaction(function () use ($user, $qr, $role, $visit): void {
            $party = GameParty::query()
                ->where('campaign_id', $visit->campaign_id)
                ->whereHas('members', fn ($query) => $query->where('user_id', $user->id))
                ->lockForUpdate()
                ->firstOrFail();
            $this->ensurePhysicalProgress($party);
            $party->refresh()->load($this->partyRelations());

            if ($role === 'onsite_gate') {
                $pass = $party->entryPass;

                if (
                    $party->status !== 'ready_for_visit'
                    || ! $pass
                    || $pass->status !== 'active'
                    || $pass->expires_at->isPast()
                ) {
                    throw ValidationException::withMessages(['qr_code' => 'مجوز حضور فعال و قابل‌مصرفی برای این گروه وجود ندارد.']);
                }

                $pass->update([
                    'status' => 'redeemed',
                    'redeemed_at' => now(),
                    'metadata' => array_merge($pass->metadata ?? [], ['redeemed_visit_id' => $visit->id]),
                ]);
                $this->completeStep($party, 6, self::STEP_POINTS[6], [
                    'verified_by' => 'onsite_gate_qr',
                    'visit_id' => $visit->id,
                    'qr_code' => $qr->code,
                ]);
                $this->unlockStep($party, 7);
                $party->update([
                    'status' => 'onsite_active',
                    'score' => $party->score + self::STEP_POINTS[6],
                    'completed_at' => null,
                    'metadata' => array_merge($party->metadata ?? [], [
                        'onsite_visit_id' => $visit->id,
                        'onsite_bonus_points' => self::STEP_POINTS[6],
                        'physical_started_at' => now()->toIso8601String(),
                    ]),
                ]);

                return;
            }

            $checkpoint = $this->nextPhysicalCheckpoint($party);
            $checkpointKey = (string) data_get($qr->metadata, 'checkpoint_key');

            if (
                $party->status !== 'onsite_active'
                || ! $checkpoint
                || $checkpoint['key'] !== $checkpointKey
            ) {
                throw ValidationException::withMessages(['qr_code' => 'این QR ایستگاه جاری مسیر شما نیست یا قبلاً ثبت شده است.']);
            }

            $points = self::STEP_POINTS[$checkpoint['step']];
            $this->completeStep($party, $checkpoint['step'], $points, [
                'verified_by' => 'physical_checkpoint_qr',
                'checkpoint_key' => $checkpoint['key'],
                'visit_id' => $visit->id,
                'qr_code' => $qr->code,
            ]);
            $party->increment('score', $points);

            if ($checkpoint['step'] === 9) {
                $party->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'metadata' => array_merge($party->metadata ?? [], [
                        'physical_completed_at' => now()->toIso8601String(),
                        'final_visit_id' => $visit->id,
                    ]),
                ]);

                return;
            }

            $this->unlockStep($party, $checkpoint['step'] + 1);
        });
    }

    /** @return array<string, mixed>|null */
    public function serializeParty(?GameParty $party, ?User $viewer = null): ?array
    {
        if (! $party) {
            return null;
        }

        $party->loadMissing($this->partyRelations());
        $hotspotProgress = $party->progress->firstWhere('step_index', 3);
        $found = $hotspotProgress instanceof GameChallengeProgress
            ? $this->hotspotFinds($hotspotProgress)
            : [];
        $puzzle = $this->routePuzzle((string) $party->route_key);
        $foundKeys = array_column($found, 'key');
        $foundFragments = collect($puzzle)
            ->filter(fn (array $item): bool => in_array($item['key'], $foundKeys, true))
            ->pluck('fragment')
            ->values()
            ->all();
        $nextHotspot = collect($puzzle)
            ->first(fn (array $item): bool => ! in_array($item['key'], $foundKeys, true));
        $physicalJourney = $this->physicalJourneySnapshot($party);
        $physicalFinalStep = collect($physicalJourney['steps'])->firstWhere('index', 9);
        $physicalFinalCompleted = ($physicalFinalStep['status'] ?? null) === 'completed';
        $effectiveStatus = $party->status === 'completed'
            && $party->entryPass?->status === 'redeemed'
            && ! $physicalFinalCompleted
                ? 'onsite_active'
                : $party->status;

        return [
            'id' => $party->id,
            'campaignId' => $party->campaign_id,
            'mode' => $party->mode,
            'name' => $party->name,
            'inviteCode' => $party->invite_code,
            'routeKey' => $party->route_key,
            'status' => $effectiveStatus,
            'score' => $party->score,
            'isLeader' => $viewer?->id === $party->owner_user_id,
            'collaborationBonusAwarded' => $party->collaboration_bonus_awarded,
            'members' => $party->members->map(fn (GamePartyMember $member): array => [
                'id' => $member->id,
                'displayName' => $member->display_name,
                'memberType' => $member->member_type,
                'role' => $member->role,
                'isViewer' => $viewer && $member->user_id === $viewer->id,
            ])->values()->all(),
            'steps' => $party->progress->sortBy('step_index')->map(fn (GameChallengeProgress $progress): array => [
                'index' => $progress->step_index,
                'status' => $progress->status,
                'points' => $progress->points_awarded,
                'attempts' => $progress->attempts,
                'metadata' => $progress->metadata,
            ])->values()->all(),
            'foundHotspots' => array_column($found, 'key'),
            'foundFragments' => $foundFragments,
            'nextHotspotHint' => $nextHotspot['hint'] ?? null,
            'physicalJourney' => $physicalJourney,
            'entryPass' => $party->entryPass ? [
                'code' => $party->entryPass->code,
                'status' => $party->entryPass->status,
                'expiresAt' => $party->entryPass->expires_at->toIso8601String(),
            ] : null,
            'bonusClaims' => $party->bonusClaims->map(fn (GameBonusClaim $claim): array => [
                'adRequestId' => $claim->ad_request_id,
                'status' => $claim->status,
                'points' => $claim->points_awarded,
                'startedAt' => $claim->started_at->toIso8601String(),
            ])->values()->all(),
        ];
    }

    /** @return list<string> */
    private function partyRelations(): array
    {
        return ['members', 'progress', 'entryPass', 'bonusClaims'];
    }

    /** @return list<array{key: string, fragment: string, hint: string}> */
    private function routePuzzle(string $routeKey): array
    {
        return self::ROUTE_PUZZLES[$routeKey] ?? [];
    }

    /** @return list<array{step: int, key: string, title: string, instruction: string}> */
    private function physicalRoute(string $routeKey): array
    {
        return self::PHYSICAL_ROUTE_CHECKPOINTS[$routeKey] ?? self::PHYSICAL_ROUTE_CHECKPOINTS['quick'];
    }

    private function ensurePhysicalProgress(GameParty $party): void
    {
        $party->loadMissing(['progress', 'entryPass']);
        $hasCompletedFinalStep = $party->progress->firstWhere('step_index', 9)?->status === 'completed';
        $legacyOnsiteCompletion = $party->status === 'completed'
            && $party->entryPass?->status === 'redeemed'
            && ! $hasCompletedFinalStep;

        if ($legacyOnsiteCompletion) {
            $party->update(['status' => 'onsite_active', 'completed_at' => null]);
        }

        $definitions = [
            ['step' => 6, 'key' => 'onsite-gate', 'title' => 'ورود به مرحله حضوری'],
            ...$this->physicalRoute((string) $party->route_key),
        ];

        foreach ($definitions as $index => $definition) {
            $existing = $party->progress()->where('step_index', $definition['step'])->first();

            if ($existing) {
                continue;
            }

            $previousCompleted = $index === 0
                ? false
                : $party->progress()
                    ->where('step_index', $definitions[$index - 1]['step'])
                    ->where('status', 'completed')
                    ->exists();
            $status = 'locked';
            $points = 0;
            $completedAt = null;

            if ($definition['step'] === 6 && $party->status === 'ready_for_visit') {
                $status = 'available';
            } elseif ($definition['step'] === 6 && $legacyOnsiteCompletion) {
                $status = 'completed';
                $points = self::STEP_POINTS[6];
                $completedAt = $party->entryPass->redeemed_at ?? now();
            } elseif ($previousCompleted) {
                $status = 'available';
            }

            GameChallengeProgress::query()->create([
                'game_party_id' => $party->id,
                'step_index' => $definition['step'],
                'status' => $status,
                'points_awarded' => $points,
                'completed_at' => $completedAt,
                'metadata' => [
                    'phase' => 'physical',
                    'checkpoint_key' => $definition['key'],
                    'title' => $definition['title'],
                ],
            ]);
        }

        $party->unsetRelation('progress');
        $party->load('progress');
    }

    /** @return array{phase: string, steps: list<array<string, mixed>>, nextCheckpointKey: string|null} */
    private function physicalJourneySnapshot(GameParty $party): array
    {
        $finalProgress = $party->progress->firstWhere('step_index', 9);
        $legacyOnsiteCompletion = $party->status === 'completed'
            && $party->entryPass?->status === 'redeemed'
            && (! $finalProgress instanceof GameChallengeProgress || $finalProgress->status !== 'completed');
        $definitions = [
            [
                'step' => 6,
                'key' => 'onsite-gate',
                'title' => 'ورود به مرحله حضوری',
                'instruction' => 'QR دروازه حضور بازی را در ورودی اصلی اکوپارک اسکن کنید.',
            ],
            ...$this->physicalRoute((string) $party->route_key),
        ];
        $steps = [];
        $nextCheckpointKey = null;
        $gateCompleted = false;
        $finalCompleted = false;

        foreach ($definitions as $index => $definition) {
            $progress = $party->progress->firstWhere('step_index', $definition['step']);
            $status = $progress instanceof GameChallengeProgress ? $progress->status : 'locked';

            if (! $progress instanceof GameChallengeProgress && $party->entryPass) {
                if ($definition['step'] === 6 && $party->status === 'ready_for_visit') {
                    $status = 'available';
                } elseif ($definition['step'] === 6 && $legacyOnsiteCompletion) {
                    $status = 'completed';
                } elseif ($index === 1 && $legacyOnsiteCompletion) {
                    $status = 'available';
                }
            }

            if ($status === 'available' && $nextCheckpointKey === null) {
                $nextCheckpointKey = $definition['key'];
            }
            if ($definition['step'] === 6) {
                $gateCompleted = $status === 'completed';
            }
            if ($definition['step'] === 9) {
                $finalCompleted = $status === 'completed';
            }

            $steps[] = [
                'index' => $definition['step'],
                'key' => $definition['key'],
                'title' => $definition['title'],
                'instruction' => $definition['instruction'],
                'status' => $status,
                'points' => self::STEP_POINTS[$definition['step']],
                'completedAt' => $progress instanceof GameChallengeProgress
                    ? $progress->completed_at?->toIso8601String()
                    : null,
            ];
        }

        return [
            'phase' => $finalCompleted
                ? 'completed'
                : ($gateCompleted ? 'active' : 'awaiting_gate'),
            'steps' => $steps,
            'nextCheckpointKey' => $nextCheckpointKey,
        ];
    }

    /** @return array{step: int, key: string, title: string, instruction: string}|null */
    private function nextPhysicalCheckpoint(GameParty $party): ?array
    {
        $availableStep = $party->progress()
            ->whereBetween('step_index', [7, 9])
            ->where('status', 'available')
            ->orderBy('step_index')
            ->value('step_index');

        if (! is_numeric($availableStep)) {
            return null;
        }

        return collect($this->physicalRoute((string) $party->route_key))
            ->firstWhere('step', (int) $availableStep);
    }

    private function normalizeDigits(string $value): string
    {
        return trim(strtr($value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]));
    }

    private function assertMember(User $user, GameParty $party): GamePartyMember
    {
        $member = $party->members()->where('user_id', $user->id)->where('status', 'active')->first();
        if (! $member) {
            abort(403);
        }

        return $member;
    }

    private function assertOwner(User $user, GameParty $party): void
    {
        if ($party->owner_user_id !== $user->id) {
            throw ValidationException::withMessages(['party' => 'فقط راهبر گروه می‌تواند این تصمیم مشترک را ثبت کند.']);
        }
    }

    private function assertCurrentStep(GameParty $party, int $step): void
    {
        $progress = $party->progress()->where('step_index', $step)->first();
        if (! $progress || $progress->status !== 'available') {
            throw ValidationException::withMessages(['step' => 'این مرحله اکنون قابل انجام نیست. ابتدا مرحله جاری را کامل کنید.']);
        }
    }

    /** @param array<string, mixed> $metadata */
    private function completeStep(GameParty $party, int $step, int $points, array $metadata): void
    {
        $party->progress()->where('step_index', $step)->update([
            'status' => 'completed',
            'points_awarded' => $points,
            'completed_at' => now(),
            'metadata' => $metadata,
        ]);
    }

    private function unlockStep(GameParty $party, int $step): void
    {
        $party->progress()->where('step_index', $step)->where('status', 'locked')->update(['status' => 'available']);
    }

    private function uniqueInviteCode(): string
    {
        do {
            $code = Str::upper(Str::random(6));
        } while (GameParty::query()->where('invite_code', $code)->exists());

        return $code;
    }

    /**
     * @return list<array{key: string, member_id: string, member_name: string, found_at: string}>
     */
    private function hotspotFinds(GameChallengeProgress $progress): array
    {
        $found = data_get($progress->metadata, 'found', []);

        if (! is_array($found)) {
            return [];
        }

        return array_values(collect($found)
            ->filter(fn (mixed $item): bool => is_array($item)
                && is_string($item['key'] ?? null)
                && is_string($item['member_id'] ?? null)
                && is_string($item['member_name'] ?? null)
                && is_string($item['found_at'] ?? null))
            ->map(fn (array $item): array => [
                'key' => $item['key'],
                'member_id' => $item['member_id'],
                'member_name' => $item['member_name'],
                'found_at' => $item['found_at'],
            ])
            ->values()
            ->all());
    }

    private function approvedGameAd(GameParty $party, string $adRequestId): AdRequest
    {
        $party->loadMissing('campaign');

        $ad = AdRequest::query()
            ->whereKey($adRequestId)
            ->where('venue_id', $party->campaign->venue_id)
            ->where('status', 'approved')
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->whereHas('placements', fn ($query) => $query->where('status', 'approved')->whereIn('placement_type', ['map_route', 'post_mission', 'reward_page']))
            ->first();

        if (! $ad) {
            throw ValidationException::withMessages(['ad_request_id' => 'این پیشنهاد اسپانسری فعال یا تأییدشده نیست.']);
        }

        return $ad;
    }

    private function recordAdEvent(AdRequest $ad, string $type, GameParty $party): void
    {
        AdEvent::query()->create([
            'ad_request_id' => $ad->id,
            'event_type' => $type,
            'occurred_at' => now(),
            'metadata' => ['source' => 'online_game_rewarded', 'party_id' => $party->id],
        ]);
    }
}
