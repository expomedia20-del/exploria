<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Events\RecordAdminAuditAction;
use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRoleRequest;
use App\Models\Hub;
use App\Models\PartnerAccount;
use App\Models\User;
use App\Models\UserAccessScope;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserManagementController extends Controller
{
    public function page(): Response
    {
        return Inertia::render('admin/users/index', [
            'users' => $this->users(),
            'stats' => $this->stats(),
            'roleOptions' => $this->roleOptions(),
            'filters' => $this->filters(),
        ]);
    }

    public function updateRole(UpdateUserRoleRequest $request, User $user, RecordAdminAuditAction $audit): RedirectResponse
    {
        $data = $request->validated();

        if ($request->user()?->is($user) && $data['role'] !== UserRole::Admin->value) {
            return back()->withErrors([
                'role' => 'ادمین مرکزی نمی‌تواند نقش پایه خودش را از این صفحه کاهش دهد.',
            ]);
        }

        $previousRole = $user->role->value;
        $user->update(['role' => UserRole::from($data['role'])]);
        $audit->execute($request->user(), 'user_role_updated', 'user', (string) $user->id, $request->session()->getId(), [
            'previous_role' => $previousRole,
            'new_role' => $user->role->value,
        ]);

        return back()->with('success', 'نقش پایه کاربر به‌روزرسانی شد.');
    }

    public function deactivateAccess(Request $request, User $user, RecordAdminAuditAction $audit): RedirectResponse
    {
        $updated = UserAccessScope::query()
            ->where('user_id', $user->id)
            ->where('status', RecordStatus::Active)
            ->update(['status' => RecordStatus::Inactive]);

        if ($updated > 0) {
            $audit->execute($request->user(), 'user_access_deactivated', 'user', (string) $user->id, $request->session()->getId(), [
                'scopes_deactivated' => $updated,
            ]);
        }

        return back()->with(
            'success',
            $updated > 0
                ? 'همه دسترسی‌های فعال این کاربر غیرفعال شد.'
                : 'این کاربر دسترسی فعال برای غیرفعال‌سازی ندارد.',
        );
    }

    public function destroy(Request $request, User $user, RecordAdminAuditAction $audit): RedirectResponse
    {
        if ($request->user()?->is($user)) {
            return back()->withErrors([
                'delete' => 'حذف اکانت فعلی شما مجاز نیست.',
            ]);
        }

        $blockers = $this->safeDeleteBlockers($user);

        if ($blockers !== []) {
            return back()->withErrors([
                'delete' => 'این کاربر سابقه عملیاتی دارد و حذف ایمن نیست. ابتدا دسترسی‌های فعال را غیرفعال کنید و سوابق را نگه دارید.',
            ]);
        }

        $userId = (string) $user->id;
        $role = $user->role->value;
        $user->delete();
        $audit->execute($request->user(), 'user_deleted', 'user', $userId, $request->session()->getId(), ['role' => $role]);

        return back()->with('success', 'کاربر بدون سابقه عملیاتی حذف شد.');
    }

    /** @return array<int, array<string, mixed>> */
    private function users(): array
    {
        return User::query()
            ->with([
                'accessScopes' => fn ($query) => $query
                    ->latest('updated_at'),
            ])
            ->withCount([
                'accessScopes',
                'accessScopes as active_access_scopes_count' => fn ($query) => $query
                    ->where('status', RecordStatus::Active),
                'visits',
                'missionProgress',
                'rewards',
                'rewardRedemptions',
                'consentLogs',
                'partnerUsers',
                'sponsorUsers',
                'hubManagementAssignments',
            ])
            ->orderBy('role')
            ->orderBy('name')
            ->get()
            ->map(function (User $user): array {
                $activeScopes = $user->accessScopes
                    ->where('status', RecordStatus::Active);
                $kind = $this->userKind($user, $activeScopes->pluck('role_key')->all());
                $blockers = $this->safeDeleteBlockers($user);

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role->value,
                    'roleLabel' => $this->accountRoleLabel($user->role->value),
                    'kind' => $kind,
                    'kindLabel' => $this->userKindLabel($kind),
                    'statusLabel' => $this->statusLabel($user, $activeScopes->count()),
                    'publicStatus' => $this->publicStatus($user),
                    'publicStatusLabel' => $this->publicStatusLabel($user),
                    'publicParticipationMode' => (string) ($user->public_participation_mode ?? 'individual'),
                    'isStressDemo' => str_contains((string) $user->email, 'stress-demo'),
                    'counts' => [
                        'accessScopes' => (int) $user->access_scopes_count,
                        'activeScopes' => $activeScopes->count(),
                        'visits' => (int) $user->visits_count,
                        'missionProgress' => (int) $user->mission_progress_count,
                        'rewards' => (int) $user->rewards_count,
                        'redemptions' => (int) $user->reward_redemptions_count,
                        'consents' => (int) $user->consent_logs_count,
                    ],
                    'canDelete' => $blockers === [],
                    'deleteBlockers' => $blockers,
                    'scopes' => $user->accessScopes
                        ->map(fn (UserAccessScope $scope): array => [
                            'id' => $scope->id,
                            'roleKey' => $scope->role_key,
                            'roleLabel' => $this->roleLabel($scope->role_key),
                            'scopeType' => $scope->scope_type,
                            'scopeTypeLabel' => $this->scopeTypeLabel($scope->scope_type),
                            'scopeId' => $scope->scope_id,
                            'scopeLabel' => $this->scopeLabel($scope->scope_type, $scope->scope_id),
                            'status' => $scope->status->value,
                            'statusLabel' => $scope->status === RecordStatus::Active ? 'فعال' : 'غیرفعال',
                        ])
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }

    /** @return array<string, int> */
    private function stats(): array
    {
        return [
            'total' => User::query()->count(),
            'internal' => User::query()->whereIn('role', [
                UserRole::Admin,
                UserRole::Operator,
                UserRole::Viewer,
            ])->count(),
            'partners' => User::query()->whereIn('role', [
                UserRole::ShopPartner,
                UserRole::HubManager,
                UserRole::Sponsor,
            ])->count(),
            'visitors' => User::query()->where('role', UserRole::Visitor)->count(),
            'publicRegistered' => User::query()
                ->where('role', UserRole::Visitor)
                ->where('public_participation_status', 'registered')
                ->doesntHave('visits')
                ->count(),
            'publicParticipants' => User::query()
                ->where('role', UserRole::Visitor)
                ->where(fn ($query) => $query
                    ->where('public_participation_status', 'participant')
                    ->orHas('visits'))
                ->count(),
            'activeScopedUsers' => UserAccessScope::query()
                ->where('status', RecordStatus::Active)
                ->distinct('user_id')
                ->count('user_id'),
        ];
    }

    /** @return array<int, array<string, string>> */
    private function roleOptions(): array
    {
        return collect(UserRole::cases())
            ->map(fn (UserRole $role): array => [
                'key' => $role->value,
                'label' => $this->accountRoleLabel($role->value),
            ])
            ->values()
            ->all();
    }

    /** @return array<int, array<string, string>> */
    private function filters(): array
    {
        return [
            ['key' => 'all', 'label' => 'همه کاربران'],
            ['key' => 'exploria_team', 'label' => 'تیم داخلی اکسپلوریا'],
            ['key' => 'venue_management', 'label' => 'مدیریت مکان و زون'],
            ['key' => 'commercial_partner', 'label' => 'واحدها و اسپانسرها'],
            ['key' => 'public', 'label' => 'بازدیدکنندگان'],
            ['key' => 'public_registered', 'label' => 'کاربران عادی'],
            ['key' => 'public_participant', 'label' => 'مشارکت‌کنندگان'],
        ];
    }

    /** @param array<int, string> $activeRoleKeys */
    private function userKind(User $user, array $activeRoleKeys): string
    {
        foreach ($activeRoleKeys as $roleKey) {
            $group = config("exploria_roles.roles.{$roleKey}.group");
            if (is_string($group)) {
                return $group;
            }
        }

        return match ($user->role) {
            UserRole::Admin, UserRole::Operator, UserRole::Viewer => 'exploria_team',
            UserRole::HubManager => 'venue_management',
            UserRole::ShopPartner, UserRole::Sponsor => 'commercial_partner',
            UserRole::Visitor => 'public',
        };
    }

    private function userKindLabel(string $kind): string
    {
        return match ($kind) {
            'exploria_team' => 'تیم داخلی اکسپلوریا',
            'venue_management' => 'مدیریت مکان / زون / هاب',
            'commercial_partner' => 'واحد تجاری / اسپانسر',
            'public' => 'بازدیدکننده / مشارکت‌کننده',
            default => 'دسته‌بندی نشده',
        };
    }

    private function accountRoleLabel(?string $role): string
    {
        return match ($role) {
            'admin' => 'ادمین مرکزی',
            'operator' => 'اپراتور داخلی',
            'viewer' => 'مشاهده‌گر',
            'visitor' => 'بازدیدکننده / مشارکت‌کننده',
            'shop_partner' => 'مدیر فروشگاه / واحد تجاری',
            'hub_manager' => 'مدیر هاب / زون',
            'sponsor' => 'اسپانسر',
            default => '-',
        };
    }

    private function roleLabel(string $roleKey): string
    {
        return config("exploria_roles.roles.{$roleKey}.label", $roleKey);
    }

    private function scopeTypeLabel(string $scopeType): string
    {
        return match ($scopeType) {
            'global' => 'کل سیستم',
            'region' => 'منطقه / عاملیت',
            'venue' => 'مکان پروژه',
            'project' => 'پروژه',
            'hub' => 'هاب / رواق',
            'partner' => 'فروشگاه / شریک',
            'campaign' => 'کمپین',
            'display_network' => 'شبکه نمایشگرها',
            'team' => 'تیم / خانواده',
            default => $scopeType,
        };
    }

    private function scopeLabel(string $scopeType, ?string $scopeId): string
    {
        if ($scopeType === 'global') {
            return 'کل اکسپلوریا';
        }

        if (! $scopeId) {
            return 'بدون محدوده مشخص';
        }

        return match ($scopeType) {
            'venue' => Venue::query()->whereKey($scopeId)->value('name') ?? $scopeId,
            'hub' => Hub::query()->whereKey($scopeId)->value('name') ?? $scopeId,
            'partner' => PartnerAccount::query()->whereKey($scopeId)->value('name') ?? $scopeId,
            default => $scopeId,
        };
    }

    private function statusLabel(User $user, int $activeScopeCount): string
    {
        if ($user->role === UserRole::Visitor) {
            return $this->publicStatusLabel($user);
        }

        if ($activeScopeCount > 0) {
            return 'دارای دسترسی فعال';
        }

        return 'بدون دسترسی فعال';
    }

    private function publicStatus(User $user): string
    {
        if ($user->role !== UserRole::Visitor) {
            return 'not_public';
        }

        if ((int) ($user->visits_count ?? $user->visits()->count()) > 0) {
            return 'participant';
        }

        return (string) ($user->public_participation_status ?? 'registered');
    }

    private function publicStatusLabel(User $user): string
    {
        $status = $this->publicStatus($user);

        if ($status !== 'participant') {
            return $user->role === UserRole::Visitor ? 'کاربر عادی' : '-';
        }

        return match ((string) ($user->public_participation_mode ?? 'individual')) {
            'family' => 'مشارکت‌کننده خانوادگی',
            'team' => 'مشارکت‌کننده تیمی',
            default => 'مشارکت‌کننده فردی',
        };
    }

    /** @return array<int, string> */
    private function safeDeleteBlockers(User $user): array
    {
        $counts = [
            'دسترسی ثبت‌شده' => $user->access_scopes_count ?? $user->accessScopes()->count(),
            'بازدید' => $user->visits_count ?? $user->visits()->count(),
            'رضایت‌نامه' => $user->consent_logs_count ?? $user->consentLogs()->count(),
            'پیشرفت ماموریت' => $user->mission_progress_count ?? $user->missionProgress()->count(),
            'پاداش' => $user->rewards_count ?? $user->rewards()->count(),
            'مصرف پاداش' => $user->reward_redemptions_count ?? $user->rewardRedemptions()->count(),
            'اتصال فروشگاه' => $user->partner_users_count ?? $user->partnerUsers()->count(),
            'اتصال اسپانسر' => $user->sponsor_users_count ?? $user->sponsorUsers()->count(),
            'مدیریت هاب' => $user->hub_management_assignments_count ?? $user->hubManagementAssignments()->count(),
        ];

        return collect($counts)
            ->filter(fn (int $count): bool => $count > 0)
            ->keys()
            ->values()
            ->all();
    }
}
