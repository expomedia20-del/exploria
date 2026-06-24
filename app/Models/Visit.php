<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property int $user_id
 * @property string $qr_code_id
 * @property string $venue_id
 * @property string $touchpoint_id
 * @property string $campaign_id
 * @property string|null $consent_log_id
 * @property string $source
 * @property string $status
 * @property string|null $session_hash
 * @property CarbonImmutable $occurred_at
 * @property array<string, mixed>|null $metadata
 * @property-read User|null $user
 * @property-read QrCode|null $qrCode
 * @property-read Venue|null $venue
 * @property-read Touchpoint|null $touchpoint
 * @property-read Campaign|null $campaign
 * @property-read ConsentLog|null $consentLog
 */
class Visit extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'qr_code_id',
        'venue_id',
        'touchpoint_id',
        'campaign_id',
        'consent_log_id',
        'source',
        'status',
        'session_hash',
        'occurred_at',
        'metadata',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'occurred_at' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<QrCode, $this> */
    public function qrCode(): BelongsTo
    {
        return $this->belongsTo(QrCode::class);
    }

    /** @return BelongsTo<Venue, $this> */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /** @return BelongsTo<Touchpoint, $this> */
    public function touchpoint(): BelongsTo
    {
        return $this->belongsTo(Touchpoint::class);
    }

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /** @return BelongsTo<ConsentLog, $this> */
    public function consentLog(): BelongsTo
    {
        return $this->belongsTo(ConsentLog::class);
    }
}
