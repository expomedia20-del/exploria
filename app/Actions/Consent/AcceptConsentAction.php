<?php

namespace App\Actions\Consent;

use App\Models\ConsentLog;
use App\Models\ConsentVersion;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AcceptConsentAction
{
    public function execute(
        User $user,
        string $consentVersionId,
        string $sessionId,
        string $source = 'pwa',
        ?string $venueId = null,
    ): ConsentLog {
        $version = ConsentVersion::query()->find($consentVersionId);

        if (! $version?->is_active) {
            throw ValidationException::withMessages([
                'consentVersionId' => 'این نسخه رضایت‌نامه فعال نیست.',
            ]);
        }

        return ConsentLog::query()->firstOrCreate(
            ['consent_version_id' => $version->id, 'user_id' => $user->id],
            [
                'session_hash' => hash('sha256', $sessionId),
                'source' => $source,
                'venue_id' => $venueId,
                'accepted_at' => now(),
            ],
        );
    }
}
