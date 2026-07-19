<?php

namespace App\Actions\Events;

use App\Models\EventLog;
use App\Models\User;

class RecordDomainEventAction
{
    /** @param array<string, mixed> $payload */
    public function execute(
        string $eventType,
        ?User $actor,
        string $sessionId,
        ?string $objectType = null,
        ?string $objectId = null,
        array $payload = [],
    ): EventLog {
        return EventLog::query()->create([
            'event_type' => $eventType,
            'actor_user_id' => $actor?->id,
            'session_hash' => $sessionId !== '' ? hash('sha256', $sessionId) : null,
            'object_type' => $objectType,
            'object_id' => $objectId,
            'payload_json' => $payload,
            'occurred_at' => now(),
        ]);
    }
}
