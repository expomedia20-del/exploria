<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class RoleOperationsController extends Controller
{
    public function page(): Response
    {
        $configuredRoles = config('exploria_roles.roles', []);
        $roles = collect(is_array($configuredRoles) ? $configuredRoles : [])
            ->map(fn (array $role, string $key): array => [
                'key' => $key,
                'group' => $role['group'],
                'label' => $role['label'],
                'scope' => $role['scope'],
                'reportsTo' => $role['reports_to'],
                'responsibilities' => $role['responsibilities'],
                'dailyOperations' => $role['daily_operations'],
                'accountRole' => $this->accountRole($key),
                'accountRoleLabel' => $this->accountRoleLabel($this->accountRole($key)),
                'entryHref' => $this->entryHref($key),
                'entryLabel' => $this->entryLabel($key),
                'panelMode' => $this->panelMode($key),
            ])
            ->values();

        return Inertia::render('admin/role-operations/index', [
            'roles' => $roles,
            'scopeTypes' => config('exploria_roles.scope_types', []),
            'stats' => $this->stats($roles),
        ]);
    }

    /**
     * @template TRole of array<string, mixed>
     *
     * @param  Collection<int, TRole>  $roles
     * @return array<string, int>
     */
    private function stats(Collection $roles): array
    {
        return [
            'totalRoles' => $roles->count(),
            'exploriaTeamRoles' => $roles->where('group', 'exploria_team')->count(),
            'venueManagementRoles' => $roles->where('group', 'venue_management')->count(),
            'commercialPartnerRoles' => $roles->where('group', 'commercial_partner')->count(),
            'publicRoles' => $roles->where('group', 'public')->count(),
            'scopeTypes' => count(config('exploria_roles.scope_types', [])),
        ];
    }

    private function accountRole(string $roleKey): string
    {
        return match ($roleKey) {
            'super_admin' => 'admin',
            'regional_admin' => 'regional_admin',
            'project_admin', 'field_operator', 'display_ads_manager' => 'operator',
            'treasure_assistant', 'venue_executive' => 'viewer',
            'ravaq_manager', 'hub_manager' => 'hub_manager',
            'shop_manager' => 'shop_partner',
            'internal_sponsor', 'external_sponsor' => 'sponsor',
            'participant' => 'visitor',
            default => 'viewer',
        };
    }

    private function accountRoleLabel(string $role): string
    {
        return match ($role) {
            'admin' => 'اکانت ادمین مرکزی',
            'regional_admin' => 'اکانت ادمین استانی / منطقه‌ای',
            'operator' => 'اکانت اپراتور داخلی',
            'viewer' => 'اکانت مشاهده‌گر محدود',
            'hub_manager' => 'اکانت مدیر هاب/رواق',
            'shop_partner' => 'اکانت فروشگاه/واحد شریک',
            'sponsor' => 'اکانت اسپانسر',
            'visitor' => 'اکانت بازدیدکننده',
            default => $role,
        };
    }

    private function entryHref(string $roleKey): string
    {
        return match ($roleKey) {
            'super_admin', 'regional_admin' => '/dashboard',
            'display_ads_manager' => '/admin/display-operations',
            'field_operator', 'treasure_assistant' => '/admin/campaign-operations',
            'venue_executive' => '/venue/dashboard',
            'ravaq_manager' => '/ravaq/dashboard',
            'hub_manager' => '/hub/dashboard',
            'shop_manager' => '/partner/dashboard',
            'internal_sponsor', 'external_sponsor' => '/sponsor/dashboard',
            'participant' => '/participant/dashboard',
            default => '/admin/internal-operations',
        };
    }

    private function entryLabel(string $roleKey): string
    {
        return match ($roleKey) {
            'super_admin', 'regional_admin' => 'داشبورد مدیریتی',
            'display_ads_manager' => 'عملیات تبلیغات و نمایشگرها',
            'field_operator', 'treasure_assistant' => 'نقشه عملیات کمپین',
            'venue_executive' => 'پنل مدیر اجرایی مکان',
            'ravaq_manager' => 'پنل مدیر رواق تجاری',
            'hub_manager' => 'پنل مدیر هاب',
            'shop_manager' => 'پنل فروشگاه / شریک',
            'internal_sponsor', 'external_sponsor' => 'پنل اسپانسر',
            'participant' => 'پنل مشارکت‌کننده',
            default => 'پنل عملیات داخلی',
        };
    }

    private function panelMode(string $roleKey): string
    {
        return match ($roleKey) {
            'super_admin', 'regional_admin', 'project_admin', 'field_operator', 'treasure_assistant', 'display_ads_manager' => 'internal_shared',
            'venue_executive' => 'venue_panel',
            'ravaq_manager', 'hub_manager' => 'hub_panel',
            'shop_manager' => 'partner_panel',
            'internal_sponsor', 'external_sponsor' => 'sponsor_panel',
            'participant' => 'public_panel',
            default => 'internal_shared',
        };
    }
}
