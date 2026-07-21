<?php

namespace App\Http\Responses;

use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Models\UserAccessScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\RegisterResponse;

class RoleAwareAuthResponse implements LoginResponse, RegisterResponse
{
    public function toResponse($request): RedirectResponse
    {
        $fallback = $this->fallbackPath($request);

        if ($this->canUseIntendedUrl($request)) {
            return redirect()->intended($fallback);
        }

        $request->session()->forget('url.intended');

        return redirect()->to($fallback);
    }

    private function canUseIntendedUrl(Request $request): bool
    {
        return in_array($request->user()?->role, [
            UserRole::Admin,
            UserRole::RegionalAdmin,
            UserRole::Operator,
            UserRole::Viewer,
        ], true);
    }

    private function fallbackPath(Request $request): string
    {
        $user = $request->user();
        $scopedDestination = $user ? $this->scopedDestination((int) $user->id) : null;

        if ($scopedDestination !== null) {
            return $scopedDestination;
        }

        return match ($user?->role) {
            UserRole::Visitor => route('participant.dashboard', absolute: false),
            UserRole::ShopPartner => route('partner.dashboard', absolute: false),
            UserRole::Sponsor => route('sponsor.dashboard', absolute: false),
            UserRole::HubManager => route('ravaq.dashboard', absolute: false),
            default => config('fortify.home', '/dashboard'),
        };
    }

    private function scopedDestination(int $userId): ?string
    {
        $roleKeys = UserAccessScope::query()
            ->where('user_id', $userId)
            ->where('status', RecordStatus::Active)
            ->pluck('role_key')
            ->all();

        $destinations = [
            'venue_executive' => route('venue.dashboard', absolute: false),
            'ravaq_manager' => route('ravaq.dashboard', absolute: false),
            'hub_manager' => route('hub.dashboard', absolute: false),
            'shop_manager' => route('partner.dashboard', absolute: false),
            'internal_sponsor' => route('sponsor.dashboard', absolute: false),
            'external_sponsor' => route('sponsor.dashboard', absolute: false),
            'display_ads_manager' => route('admin.display-operations.page', absolute: false),
            'field_operator' => route('admin.campaign-operations.page', absolute: false),
            'treasure_assistant' => route('admin.campaign-operations.page', absolute: false),
            'project_admin' => route('admin.internal-operations.page', absolute: false),
            'regional_admin' => config('fortify.home', '/dashboard'),
            'super_admin' => config('fortify.home', '/dashboard'),
        ];

        foreach ($destinations as $roleKey => $destination) {
            if (in_array($roleKey, $roleKeys, true)) {
                return $destination;
            }
        }

        return null;
    }
}
