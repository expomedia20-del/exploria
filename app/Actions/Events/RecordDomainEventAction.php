<?php

namespace App\Actions\Events;

use App\Models\EventLog;
use App\Models\User;

class RecordDomainEventAction
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  array{venue_id?: string|null, touchpoint_id?: string|null, campaign_id?: string|null}  $attribution
     */
    public function execute(
        string $eventType,
        ?User $actor,
        string $sessionId,
        ?string $objectType = null,
        ?string $objectId = null,
        array $payload = [],
        array $attribution = [],
    ): EventLog {
        return EventLog::query()->create([
            'event_type' => $eventType,
            'actor_user_id' => $actor?->id,
            'session_hash' => $sessionId !== '' ? hash('sha256', $sessionId) : null,
            'object_type' => $objectType,
            'object_id' => $objectId,
            'venue_id' => $attribution['venue_id'] ?? null,
            'touchpoint_id' => $attribution['touchpoint_id'] ?? null,
            'campaign_id' => $attribution['campaign_id'] ?? null,
            'payload_json' => $payload,
            'occurred_at' => now(),
        ]);
    }
}
