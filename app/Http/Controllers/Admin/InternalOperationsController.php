<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RecordStatus;
use App\Http\Controllers\Controller;
use App\Models\Hub;
use App\Models\PartnerAccount;
use App\Models\UserAccessScope;
use App\Models\Venue;
use Inertia\Inertia;
use Inertia\Response;

class InternalOperationsController extends Controller
{
    public function page(): Response
    {
        $configuredRoles = config('exploria_roles.roles', []);
        $roles = collect(is_array($configuredRoles) ? $configuredRoles : []);
        $internalRoleKeys = $roles
            ->filter(fn (array $role): bool => ($role['group'] ?? null) === 'exploria_team')
            ->keys()
            ->values();

        $teamMembers = UserAccessScope::query()
            ->with('user:id,name,email,role')
            ->where('status', RecordStatus::Active)
            ->whereIn('role_key', $internalRoleKeys)
            ->latest('updated_at')
            ->get()
            ->map(fn (UserAccessScope $scope): array => $this->teamMember($scope))
            ->values();

        $roleCounts = $teamMembers
            ->groupBy('roleKey')
            ->map(fn ($items): int => $items->count())
            ->all();

        return Inertia::render('admin/internal-operations/index', [
            'stats' => [
                'internalUsers' => $teamMembers->pluck('user.id')->filter()->unique()->count(),
                'activeAssignments' => $teamMembers->count(),
                'supervisorRoles' => $roles->where('group', 'exploria_team')->whereNotNull('reports_to')->count(),
                'unassignedSupervisorLinks' => $teamMembers
                    ->filter(fn (array $member): bool => $member['reportsToKey'] !== null && ($roleCounts[$member['reportsToKey']] ?? 0) === 0)
                    ->count(),
            ],
            'teamMembers' => $teamMembers,
            'supervisionLines' => $roles
                ->filter(fn (array $role): bool => ($role['group'] ?? null) === 'exploria_team')
                ->map(fn (array $role, string $key): array => [
                    'key' => $key,
                    'label' => $role['label'],
                    'reportsToKey' => $role['reports_to'],
                    'reportsToLabel' => $this->roleLabel($role['reports_to']),
                    'defaultAccountRole' => $this->defaultAccountRole($key),
                    'entryHref' => $this->entryHref($key),
                    'scopeLabel' => $this->scopeTypeLabel($role['scope'] ?? ''),
                    'activeCount' => $roleCounts[$key] ?? 0,
                ])
                ->values(),
        ]);
    }

    /** @return array<string, mixed> */
    private function teamMember(UserAccessScope $scope): array
    {
        $reportsToKey = config("exploria_roles.roles.{$scope->role_key}.reports_to");
        $configuredRoles = config('exploria_roles.roles', []);
        $subordinateRoleKeys = collect(is_array($configuredRoles) ? $configuredRoles : [])
            ->filter(fn (array $role): bool => ($role['reports_to'] ?? null) === $scope->role_key)
            ->keys();

        return [
            'id' => $scope->id,
            'user' => [
                'id' => $scope->user?->id,
                'name' => $scope->user->name ?? 'کاربر بدون نام',
                'email' => $scope->user?->email,
                'accountRole' => $scope->user?->role?->value,
                'accountRoleLabel' => $this->accountRoleLabel($scope->user?->role?->value),
            ],
            'roleKey' => $scope->role_key,
            'roleLabel' => $this->roleLabel($scope->role_key),
            'scopeType' => $scope->scope_type,
            'scopeTypeLabel' => $this->scopeTypeLabel($scope->scope_type),
            'scopeId' => $scope->scope_id,
            'scopeLabel' => $this->scopeLabel($scope->scope_type, $scope->scope_id),
            'reportsToKey' => $reportsToKey,
            'reportsToLabel' => $this->roleLabel($reportsToKey),
            'defaultAccountRole' => $this->defaultAccountRole($scope->role_key),
            'entryHref' => $this->entryHref($scope->role_key),
            'entryLabel' => $this->entryLabel($scope->role_key),
            'subordinateCount' => UserAccessScope::query()
                ->where('status', RecordStatus::Active)
                ->whereIn('role_key', $subordinateRoleKeys)
                ->count(),
            'updatedAt' => $scope->updated_at?->toIso8601String(),
        ];
    }

    private function entryHref(string $roleKey): string
    {
        return match ($roleKey) {
            'display_ads_manager' => '/admin/display-operations',
            'field_operator', 'treasure_assistant' => '/admin/campaign-operations',
            default => '/admin/internal-operations',
        };
    }

    private function entryLabel(string $roleKey): string
    {
        return match ($roleKey) {
            'display_ads_manager' => 'عملیات تبلیغات و نمایشگرها',
            'field_operator', 'treasure_assistant' => 'نقشه عملیات کمپین',
            default => 'پنل عملیات داخلی',
        };
    }

    private function defaultAccountRole(string $roleKey): string
    {
        return match ($roleKey) {
            'super_admin' => 'admin',
            'regional_admin', 'project_admin', 'field_operator', 'display_ads_manager' => 'operator',
            'treasure_assistant' => 'viewer یا operator محدود',
            default => 'operator',
        };
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

    private function roleLabel(?string $roleKey): ?string
    {
        if ($roleKey === null) {
            return null;
        }

        return config("exploria_roles.roles.{$roleKey}.label") ?? $roleKey;
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

    private function accountRoleLabel(?string $role): string
    {
        return match ($role) {
            'admin' => 'ادمین',
            'operator' => 'اپراتور',
            'viewer' => 'مشاهده‌گر',
            'hub_manager' => 'مدیر هاب',
            'shop_partner' => 'شریک فروشگاهی',
            'sponsor' => 'اسپانسر',
            'visitor' => 'بازدیدکننده',
            default => $role ?? '-',
        };
    }
}
