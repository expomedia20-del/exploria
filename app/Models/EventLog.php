<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * @property string $id
 * @property string $event_type
 * @property int|null $actor_user_id
 * @property string|null $session_hash
 * @property string|null $venue_id
 * @property string|null $touchpoint_id
 * @property string|null $campaign_id
 * @property string|null $object_type
 * @property string|null $object_id
 * @property array<string, mixed>|null $payload_json
 * @property CarbonImmutable $occurred_at
 */
class EventLog extends Model
{
    use HasUuids;

    protected $table = 'event_log';

    public $timestamps = false;

    protected $fillable = [
        'event_type', 'actor_user_id', 'session_hash', 'venue_id', 'touchpoint_id', 'campaign_id',
        'object_type', 'object_id', 'payload_json', 'occurred_at',
    ];

    protected static function booted(): void
    {
        static::updating(fn (): never => throw new LogicException('Event logs are append-only.'));
        static::deleting(fn (): never => throw new LogicException('Event logs are append-only.'));
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'payload_json' => 'array',
            'occurred_at' => 'immutable_datetime',
        ];
    }
}
