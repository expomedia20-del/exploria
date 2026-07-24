<?php

namespace App\Services;

use App\Models\DisplayDevice;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class DisplayDeviceTokenService
{
    public function tokenFor(DisplayDevice $displayDevice): string
    {
        $applicationKey = (string) config('app.key');

        if ($applicationKey === '') {
            throw new RuntimeException('APP_KEY must be configured before provisioning display devices.');
        }

        return hash_hmac('sha256', $displayDevice->code, $applicationKey);
    }

    public function ensureAuthenticated(Request $request, DisplayDevice $displayDevice): void
    {
        $providedToken = $request->bearerToken() ?: $request->header('X-Exploria-Display-Token');

        if (! is_string($providedToken) || ! hash_equals($this->tokenFor($displayDevice), $providedToken)) {
            throw new UnauthorizedHttpException('Bearer', 'توکن احراز هویت نمایشگر معتبر نیست.');
        }
    }
}
