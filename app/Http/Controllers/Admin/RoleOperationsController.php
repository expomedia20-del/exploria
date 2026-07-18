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
}
