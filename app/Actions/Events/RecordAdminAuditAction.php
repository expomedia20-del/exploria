<?php

namespace App\Actions\Events;

use App\Models\User;

class RecordAdminAuditAction
{
    public function __construct(private readonly RecordDomainEventAction $recordEvent) {}

    /**
     * @param  array<string, mixed>  $payload
     * @param  array{venue_id?: string|null, touchpoint_id?: string|null, campaign_id?: string|null}  $attribution
     */
    public function execute(User $actor, string $action, string $objectType, string $objectId, string $sessionId, array $payload = [], array $attribution = []): void
    {
        $this->recordEvent->execute('audit.'.$action, $actor, $sessionId, $objectType, $objectId, $payload, $attribution);
    }
}
