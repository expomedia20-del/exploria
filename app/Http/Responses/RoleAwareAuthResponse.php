<?php

namespace App\Http\Responses;

use App\Enums\UserRole;
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
            UserRole::Operator,
            UserRole::Viewer,
        ], true);
    }

    private function fallbackPath(Request $request): string
    {
        return match ($request->user()?->role) {
            UserRole::Visitor => route('participant.dashboard', absolute: false),
            UserRole::ShopPartner => route('partner.dashboard', absolute: false),
            UserRole::Sponsor => route('sponsor.dashboard', absolute: false),
            UserRole::HubManager => route('ravaq.dashboard', absolute: false),
            default => config('fortify.home', '/dashboard'),
        };
    }
}
