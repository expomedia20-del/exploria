<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RecordStatus;
use App\Http\Controllers\Controller;
use App\Models\Hub;
use App\Models\PartnerAccount;
use App\Models\User;
use App\Models\UserAccessScope;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'roleOptions' => $this->roleOptions(),
            'scopeOptions' => $this->scopeOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'role_key' => ['required', 'string', Rule::in(array_keys(config('exploria_roles.roles', [])))],
            'scope_type' => ['required', 'string', Rule::in(config('exploria_roles.scope_types', []))],
            'scope_id' => ['nullable', 'string', 'max:64'],
        ]);

        $this->validateScopeId($data['scope_type'], $data['scope_id'] ?? null);

        UserAccessScope::query()->updateOrCreate(
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

        return back()->with('success', 'دامنه دسترسی کاربر ثبت شد.');
    }

    public function deactivate(UserAccessScope $accessScope): RedirectResponse
    {
        $accessScope->update(['status' => RecordStatus::Inactive]);

        return back()->with('success', 'دامنه دسترسی غیرفعال شد.');
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
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role?->value,
            ])
            ->all();
    }

    /** @return array<int, array<string, string>> */
    private function roleOptions(): array
    {
        return collect(config('exploria_roles.roles', []))
            ->map(fn (array $role, string $key): array => [
                'key' => $key,
                'label' => $this->roleLabel($key),
                'defaultScope' => $role['scope'],
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
            'region' => [],
            'project' => [],
            'campaign' => [],
            'display_network' => [],
            'team' => [],
        ];
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
            'hub' => Hub::query()->whereKey($scopeId)->value('name') ?? $scopeId ?? '-',
            'partner' => PartnerAccount::query()->whereKey($scopeId)->value('name') ?? $scopeId ?? '-',
            default => $scopeId ?? '-',
        };
    }

    private function roleLabel(string $roleKey): string
    {
        return match ($roleKey) {
            'super_admin' => 'ادمین اصلی کل اکسپلوریا',
            'regional_admin' => 'ادمین منطقه‌ای',
            'project_admin' => 'ادمین پروژه مکانی',
            'field_operator' => 'مجری میدانی کمپین',
            'treasure_assistant' => 'یاریگر کاشفان گنج',
            'display_ads_manager' => 'مدیر تبلیغات نمایشگرها',
            'venue_executive' => 'مدیر اجرایی مکان پروژه',
            'hub_manager' => 'مدیر هاب یا رواق',
            'shop_manager' => 'مدیر فروشگاه یا واحد',
            'internal_sponsor' => 'اسپانسر داخلی',
            'external_sponsor' => 'اسپانسر خارجی',
            'participant' => 'کاربر یا مشارکت‌کننده',
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
            'partner' => 'فروشگاه یا شریک',
            'campaign' => 'کمپین',
            'display_network' => 'شبکه نمایشگرها',
            'team' => 'تیم یا خانواده',
            default => $scopeType,
        };
    }
}
