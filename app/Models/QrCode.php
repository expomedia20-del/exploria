<?php

namespace App\Models;

use App\Enums\RecordStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $code
 * @property string $venue_id
 * @property string $touchpoint_id
 * @property string $campaign_id
 * @property string $destination_url
 * @property string $label
 * @property RecordStatus $status
 * @property CarbonImmutable|null $valid_from
 * @property CarbonImmutable|null $valid_until
 * @property int|null $max_scans_per_user_per_window
 * @property int|null $duplicate_window_seconds
 * @property array<string, mixed>|null $metadata
 * @property-read Venue|null $venue
 * @property-read Touchpoint|null $touchpoint
 * @property-read Campaign|null $campaign
 */
class QrCode extends Model
{
    use HasUuids;

    protected $fillable = [
        'code', 'venue_id', 'touchpoint_id', 'campaign_id', 'destination_url', 'label', 'status',
        'valid_from', 'valid_until', 'max_scans_per_user_per_window', 'duplicate_window_seconds', 'metadata',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => RecordStatus::class,
            'valid_from' => 'immutable_datetime',
            'valid_until' => 'immutable_datetime',
            'metadata' => 'array',
        ];
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

    public function isAvailableForLanding(): bool
    {
        $now = now();

        return $this->status === RecordStatus::Active
            && $this->venue?->status === RecordStatus::Active
            && $this->touchpoint?->status === RecordStatus::Active
            && $this->campaign?->status === RecordStatus::Active
            && (! $this->valid_from || $this->valid_from->lessThanOrEqualTo($now))
            && (! $this->valid_until || $this->valid_until->isFuture());
    }
}
