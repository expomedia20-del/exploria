<?php

namespace App\Models;

use App\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrCode extends Model
{
    use HasUuids;

    protected $fillable = [
        'code', 'venue_id', 'touchpoint_id', 'campaign_id', 'destination_url', 'label', 'status',
        'valid_from', 'valid_until', 'max_scans_per_user_per_window', 'duplicate_window_seconds', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => RecordStatus::class,
            'valid_from' => 'immutable_datetime',
            'valid_until' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function touchpoint(): BelongsTo
    {
        return $this->belongsTo(Touchpoint::class);
    }

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
