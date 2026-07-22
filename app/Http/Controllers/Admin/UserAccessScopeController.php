<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Events\RecordAdminAuditAction;
use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminAccountRequest;
use App\Http\Requests\Admin\StoreUserAccessScopeRequest;
use App\Models\Campaign;
use App\Models\Hub;
use App\Models\PartnerAccount;
use App\Models\User;
use App\Models\UserAccessScope;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserAccessScopeController extends Controller
{
    public function page(): Response
    {
        return Inertia::render('admin/access-scopes/index', [
            'accessScopes' => $this->accessScopes(),
            'stats' => $this->stats(),
            'userOptions' => $this->userOptions(),
            'accountRoleOptions' => $this->accountRoleOptions(),
            'roleOptions' => $this->roleOptions(),
            'scopeOptions' => $this->scopeOptions(),
            'assignmentTemplates' => $this->assignmentTemplates(),
        ]);
    }

    public function store(StoreUserAccessScopeRequest $request, RecordAdminAuditAction $audit): RedirectResponse
    {
        $data = $request->validated();

        $this->validateScopeId($data['scope_type'], $data['scope_id'] ?? null);

        $scope = UserAccessScope::query()->updateOrCreate(
            [
                'user_id' => $data['user_id'],
                'role_key' => $data['role_key'],
                'scope_type' => $data['scope_type'],
                'scope_id' => $data['scope_type'] === 'global' ? null : ($data['scope_id'] ?? null),
            ],
            [
                'status' => RecordStatus::Active,
                'metadata' => ['source' => 'admin_access_scope_page'],
            ],
        );
        $audit->execute($request->user(), $scope->wasRecentlyCreated ? 'access_scope_created' : 'access_scope_reactivated', 'access_scope', $scope->id, $request->session()->getId(), [
            'target_user_id' => $scope->user_id,
            'role_key' => $scope->role_key,
            'scope_type' => $scope->scope_type,
            'scope_id' => $scope->scope_id,
            'status' => $scope->status->value,
        ]);

        return back()->with('success', 'دامنه دسترسی کاربر ثبت شد.');
    }

    public function deactivate(Request $request, UserAccessScope $accessScope, RecordAdminAuditAction $audit): RedirectResponse
    {
        $accessScope->update(['status' => RecordStatus::Inactive]);
        $audit->execute($request->user(), 'access_scope_deactivated', 'access_scope', $accessScope->id, $request->session()->getId(), [
            'target_user_id' => $accessScope->user_id,
            'role_key' => $accessScope->role_key,
            'scope_type' => $accessScope->scope_type,
            'scope_id' => $accessScope->scope_id,
            'status' => $accessScope->status->value,
        ]);

        return back()->with('success', 'دامنه دسترسی غیرفعال شد.');
    }

    public function storeAccount(StoreAdminAccountRequest $request, RecordAdminAuditAction $audit): RedirectResponse
    {
        $data = $request->validated();

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Str::password(48),
            'role' => UserRole::from($data['role']),
        ]);
        $audit->execute($request->user(), 'user_created', 'user', (string) $user->id, $request->session()->getId(), [
            'role' => $user->role->value,
        ]);

        return back()->with('success', 'اکانت جدید ساخته شد. دسترسی را تعریف کنید و از کاربر بخواهید رمز خود را از مسیر بازیابی رمز تعیین کند.');
    }

    /** @return array<int, array<string, mixed>> */
    private function accessScopes(): array
    {
        return UserAccessScope::query()
            ->with('user:id,name,email,role')
            ->latest('updated_at')
            ->get()
            ->map(fn (UserAccessScope $scope): array => [
                'id' => $scope->id,
                'roleKey' => $scope->role_key,
                'roleLabel' => $this->roleLabel($scope->role_key),
                'roleGovernance' => $this->roleGovernance($scope->role_key),
                'scopeType' => $scope->scope_type,
                'scopeTypeLabel' => $this->scopeTypeLabel($scope->scope_type),
                'scopeId' => $scope->scope_id,
                'scopeLabel' => $this->scopeLabel($scope->scope_type, $scope->scope_id),
                'status' => $scope->status->value,
                'user' => [
                    'id' => $scope->user?->id,
                    'name' => $scope->user?->name,
                    'email' => $scope->user?->email,
                    'role' => $scope->user?->role?->value,
                ],
                'updatedAt' => $scope->updated_at?->toIso8601String(),
            ])
            ->all();
    }

    /** @return array<string, int> */
    private function stats(): array
    {
        return [
            'total' => UserAccessScope::query()->count(),
            'active' => UserAccessScope::query()->where('status', RecordStatus::Active)->count(),
            'users' => UserAccessScope::query()->distinct('user_id')->count('user_id'),
            'global' => UserAccessScope::query()->where('scope_type', 'global')->where('status', RecordStatus::Active)->count(),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function userOptions(): array
    {
        return User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role'])
            ->map(function (User $user): array {
                $role = $user->role->value;
                $kind = $this->userKind($user);

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $role,
                    'roleLabel' => $this->accountRoleLabel($role),
                    'kind' => $kind,
                    'kindLabel' => $this->userKindLabel($kind),
                    'isStressDemo' => str_contains((string) $user->email, 'stress-demo'),
                ];
            })
            ->all();
    }

    /** @return array<int, array{key: string, group: string, label: string, defaultScope: string, reportsTo: string|null, governance: array<string, string>}> */
    private function roleOptions(): array
    {
        $configuredRoles = config('exploria_roles.roles', []);

        return collect(is_array($configuredRoles) ? $configuredRoles : [])
            ->map(fn (array $role, string $key): array => [
                'key' => $key,
                'group' => $role['group'],
                'label' => $this->roleLabel($key),
                'defaultScope' => $role['scope'],
                'reportsTo' => $role['reports_to'],
                'governance' => $this->roleGovernance($key),
            ])
            ->values()
            ->all();
    }

    /** @return array<int, array<string, string>> */
    private function accountRoleOptions(): array
    {
        return collect(UserRole::cases())
            ->map(fn (UserRole $role): array => [
                'key' => $role->value,
                'label' => $this->accountRoleLabel($role->value),
            ])
            ->values()
            ->all();
    }

    /** @return array<string, array<int, array<string, string|null>>> */
    private function scopeOptions(): array
    {
        return [
            'global' => [['id' => '', 'label' => 'کل اکسپلوریا']],
            'venue' => Venue::query()->orderBy('name')->get(['id', 'name', 'code'])->map(
                fn (Venue $venue): array => ['id' => $venue->id, 'label' => "{$venue->name} ({$venue->code})"],
            )->all(),
            'hub' => Hub::query()->with('zone.venue:id,name')->orderBy('name')->get(['id', 'zone_id', 'name', 'code'])->map(
                fn (Hub $hub): array => ['id' => $hub->id, 'label' => "{$hub->name} - {$hub->zone?->venue?->name}"],
            )->all(),
            'partner' => PartnerAccount::query()->with('venue:id,name')->orderBy('name')->get(['id', 'venue_id', 'name', 'code'])->map(
                fn (PartnerAccount $partner): array => ['id' => $partner->id, 'label' => "{$partner->name} - {$partner->venue?->name}"],
            )->all(),
            'campaign' => Campaign::query()->with('venue:id,name')->orderBy('name')->get(['id', 'venue_id', 'name', 'code'])->map(
                fn (Campaign $campaign): array => ['id' => $campaign->id, 'label' => "{$campaign->name} - {$campaign->venue?->name}"],
            )->all(),
            'region' => Venue::query()
                ->whereNotNull('city')
                ->distinct()
                ->orderBy('city')
                ->pluck('city')
                ->filter()
                ->map(fn (string $city): array => ['id' => $city, 'label' => $city])
                ->values()
                ->all(),
            'project' => [],
            'display_network' => [],
            'team' => [],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function assignmentTemplates(): array
    {
        $configuredTemplates = config('exploria_roles.assignment_templates', []);

        return collect(is_array($configuredTemplates) ? $configuredTemplates : [])
            ->map(function (array $template): array {
                $scopeTarget = $this->scopeTargetByCode(
                    $template['scope_type'],
                    $template['scope_code'] ?? null,
                );

                return [
                    'key' => $template['key'],
                    'title' => $template['title'],
                    'description' => $template['description'],
                    'roleKey' => $template['role_key'],
                    'roleLabel' => $this->roleLabel($template['role_key']),
                    'scopeType' => $template['scope_type'],
                    'scopeTypeLabel' => $this->scopeTypeLabel($template['scope_type']),
                    'scopeCode' => $template['scope_code'] ?? null,
                    'scopeId' => $scopeTarget['id'] ?? null,
                    'scopeLabel' => $scopeTarget['label'] ?? 'محدوده پیدا نشد',
                    'available' => $template['scope_type'] === 'global' || $scopeTarget !== null,
                ];
            })
            ->values()
            ->all();
    }

    /** @return array{id: string|null, label: string}|null */
    private function scopeTargetByCode(string $scopeType, ?string $scopeCode): ?array
    {
        if ($scopeType === 'global') {
            return ['id' => null, 'label' => 'کل اکسپلوریا'];
        }

        if (! $scopeCode) {
            return null;
        }

        return match ($scopeType) {
            'venue' => ($venue = Venue::query()->where('code', $scopeCode)->first(['id', 'name', 'code']))
                ? ['id' => (string) $venue->id, 'label' => "{$venue->name} ({$venue->code})"]
                : null,
            'campaign' => ($campaign = Campaign::query()->with('venue:id,name')->where('code', $scopeCode)->first(['id', 'venue_id', 'name', 'code']))
                ? ['id' => (string) $campaign->id, 'label' => "{$campaign->name} - {$campaign->venue?->name}"]
                : null,
            'hub' => ($hub = Hub::query()->with('zone.venue:id,name')->where('code', $scopeCode)->first(['id', 'zone_id', 'name', 'code']))
                ? ['id' => (string) $hub->id, 'label' => "{$hub->name} - {$hub->zone?->venue?->name}"]
                : null,
            'partner' => ($partner = PartnerAccount::query()->with('venue:id,name')->where('code', $scopeCode)->first(['id', 'venue_id', 'name', 'code']))
                ? ['id' => (string) $partner->id, 'label' => "{$partner->name} - {$partner->venue?->name}"]
                : null,
            default => ['id' => $scopeCode, 'label' => $scopeCode],
        };
    }

    private function validateScopeId(string $scopeType, ?string $scopeId): void
    {
        if ($scopeType === 'global') {
            return;
        }

        validator(
            ['scope_id' => $scopeId],
            ['scope_id' => ['required', 'string', match ($scopeType) {
                'venue' => Rule::exists('venues', 'id'),
                'region' => Rule::exists('venues', 'city'),
                'campaign' => Rule::exists('campaigns', 'id'),
                'hub' => Rule::exists('hubs', 'id'),
                'partner' => Rule::exists('partner_accounts', 'id'),
                default => 'max:64',
            }]],
        )->validate();
    }

    private function scopeLabel(string $scopeType, ?string $scopeId): string
    {
        if ($scopeType === 'global') {
            return 'کل اکسپلوریا';
        }

        return match ($scopeType) {
            'venue' => Venue::query()->whereKey($scopeId)->value('name') ?? $scopeId ?? '-',
            'campaign' => Campaign::query()->whereKey($scopeId)->value('name') ?? $scopeId ?? '-',
            'hub' => Hub::query()->whereKey($scopeId)->value('name') ?? $scopeId ?? '-',
            'partner' => PartnerAccount::query()->whereKey($scopeId)->value('name') ?? $scopeId ?? '-',
            default => $scopeId ?? '-',
        };
    }

    private function roleLabel(string $roleKey): string
    {
        return config("exploria_roles.roles.{$roleKey}.label") ?? match ($roleKey) {
            'super_admin' => 'ادمین اصلی کل اکسپلوریا',
            'regional_admin' => 'ادمین منطقه‌ای',
            'project_admin' => 'مدیر پروژه مکانی اکسپلوریا',
            'field_operator' => 'مجری میدانی کمپین',
            'treasure_assistant' => 'یاریگر کاشفان گنج',
            'display_ads_manager' => 'مدیر تبلیغات و نمایشگرها',
            'venue_executive' => 'مدیر مکان',
            'ravaq_manager' => 'مدیر رواق / زون تجاری',
            'hub_manager' => 'مدیر هاب',
            'shop_manager' => 'مدیر فروشگاه / واحد تجاری',
            'internal_sponsor' => 'اسپانسر داخلی مکان یا هاب',
            'external_sponsor' => 'اسپانسر مستقل / بیرونی',
            'participant' => 'بازدیدکننده / مشارکت‌کننده',
            default => $roleKey,
        };
    }

    private function scopeTypeLabel(string $scopeType): string
    {
        return match ($scopeType) {
            'global' => 'کل سیستم',
            'region' => 'منطقه یا استان',
            'venue' => 'مکان پروژه',
            'project' => 'پروژه',
            'hub' => 'هاب یا رواق',
            'partner' => 'فروشگاه یا واحد تجاری',
            'campaign' => 'کمپین',
            'display_network' => 'شبکه نمایشگرها',
            'team' => 'تیم یا خانواده',
            default => $scopeType,
        };
    }

    /** @return array{accountRole: string, accountRoleLabel: string, approvalLevel: string, approvalLabel: string, risk: string, riskLabel: string, policy: string} */
    private function roleGovernance(string $roleKey): array
    {
        $accountRole = match ($roleKey) {
            'super_admin' => 'admin',
            'regional_admin' => 'regional_admin',
            'project_admin', 'field_operator', 'display_ads_manager' => 'operator',
            'treasure_assistant' => 'viewer',
            'venue_executive' => 'viewer',
            'ravaq_manager', 'hub_manager' => 'hub_manager',
            'shop_manager' => 'shop_partner',
            'internal_sponsor', 'external_sponsor' => 'sponsor',
            'participant' => 'visitor',
            default => 'viewer',
        };

        $approvalLevel = match ($roleKey) {
            'super_admin', 'regional_admin', 'project_admin', 'external_sponsor' => 'central_admin',
            'display_ads_manager', 'venue_executive', 'ravaq_manager', 'hub_manager' => 'project_or_central_admin',
            'field_operator', 'treasure_assistant', 'shop_manager', 'internal_sponsor' => 'project_admin',
            'participant' => 'system',
            default => 'project_admin',
        };

        $risk = match ($roleKey) {
            'super_admin', 'regional_admin', 'project_admin' => 'critical',
            'external_sponsor', 'display_ads_manager', 'venue_executive' => 'high',
            'ravaq_manager', 'hub_manager', 'shop_manager', 'internal_sponsor' => 'medium',
            default => 'low',
        };

        return [
            'accountRole' => $accountRole,
            'accountRoleLabel' => $this->accountRoleLabel($accountRole),
            'approvalLevel' => $approvalLevel,
            'approvalLabel' => $this->approvalLabel($approvalLevel),
            'risk' => $risk,
            'riskLabel' => $this->riskLabel($risk),
            'policy' => $this->mutationPolicy($roleKey, $approvalLevel, $risk),
        ];
    }

    private function accountRoleLabel(string $role): string
    {
        return match ($role) {
            'admin' => 'ادمین',
            'regional_admin' => 'اکانت ادمین استانی / منطقه‌ای',
            'operator' => 'اپراتور داخلی',
            'viewer' => 'مشاهده‌گر محدود',
            'visitor' => 'بازدیدکننده',
            'shop_partner' => 'اکانت فروشگاه/واحد تجاری',
            'hub_manager' => 'اکانت مدیر هاب/رواق',
            'sponsor' => 'اکانت اسپانسر',
            default => $role,
        };
    }

    private function userKind(User $user): string
    {
        if (str_contains((string) $user->email, 'stress-demo')) {
            return 'stress_demo';
        }

        return match ($user->role->value) {
            'admin', 'regional_admin', 'operator' => 'exploria_internal',
            'viewer' => 'internal_viewer',
            'hub_manager' => 'hub_manager',
            'shop_partner' => 'commercial_partner',
            'sponsor' => 'sponsor',
            'visitor' => 'visitor',
        };
    }

    private function userKindLabel(string $kind): string
    {
        return match ($kind) {
            'exploria_internal' => 'تیم داخلی اکسپلوریا',
            'internal_viewer' => 'مشاهده‌گر یا مدیر محدود',
            'hub_manager' => 'مدیر هاب/رواق',
            'commercial_partner' => 'فروشگاه/واحد تجاری',
            'sponsor' => 'اسپانسر',
            'visitor' => 'بازدیدکننده',
            'stress_demo' => 'دموی فشار',
            default => 'نامشخص',
        };
    }

    private function approvalLabel(string $approvalLevel): string
    {
        return match ($approvalLevel) {
            'central_admin' => 'تایید ادمین مرکزی',
            'project_or_central_admin' => 'تایید مدیر پروژه یا ادمین مرکزی',
            'project_admin' => 'تایید مدیر پروژه',
            'system' => 'ثبت سیستمی/کاربر عمومی',
            default => $approvalLevel,
        };
    }

    private function riskLabel(string $risk): string
    {
        return match ($risk) {
            'critical' => 'حساس بسیار بالا',
            'high' => 'حساس بالا',
            'medium' => 'حساس متوسط',
            'low' => 'حساس پایین',
            default => $risk,
        };
    }

    private function mutationPolicy(string $roleKey, string $approvalLevel, string $risk): string
    {
        if ($roleKey === 'participant') {
            return 'این نقش برای کاربر عمومی است و نباید از صفحه ادمین برای عملیات داخلی یا تجاری استفاده شود.';
        }

        if ($risk === 'critical') {
            return 'تغییر این نقش باید فقط با تایید ادمین مرکزی و ثبت دلیل انجام شود.';
        }

        if ($approvalLevel === 'project_or_central_admin') {
            return 'مدیر پروژه می‌تواند پیشنهاد یا تایید عملیاتی بدهد؛ موارد حساس به ادمین مرکزی ارجاع می‌شود.';
        }

        if ($approvalLevel === 'project_admin') {
            return 'تغییر این نقش در محدوده پروژه با تایید مدیر پروژه مجاز است.';
        }

        return 'تغییر این نقش باید با مالک محدوده و قواعد دسترسی اکسپلوریا هماهنگ باشد.';
    }
}
