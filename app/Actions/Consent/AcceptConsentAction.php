<?php

namespace App\Actions\Consent;

use App\Actions\Events\RecordDomainEventAction;
use App\Models\ConsentLog;
use App\Models\ConsentVersion;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AcceptConsentAction
{
    public function __construct(private readonly RecordDomainEventAction $recordEvent) {}

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

        $log = ConsentLog::query()->firstOrCreate(
            ['consent_version_id' => $version->id, 'user_id' => $user->id],
            [
                'session_hash' => hash('sha256', $sessionId),
                'source' => $source,
                'venue_id' => $venueId,
                'accepted_at' => now(),
            ],
        );

        if ($log->wasRecentlyCreated) {
            $this->recordEvent->execute('consent_accepted', $user, $sessionId, 'consent_version', $version->id, [
                'consent_version_id' => $version->id,
                'accepted_at' => $log->accepted_at->toIso8601String(),
                'source' => $source,
            ]);
        }

        return $log;
    }
}
