<?php

namespace App\Models;

use App\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $venue_id
 * @property string|null $hub_id
 * @property string|null $touchpoint_id
 * @property string $code
 * @property string $name
 * @property string $device_type
 * @property RecordStatus $status
 * @property array<int, string>|null $supported_media_formats
 * @property Carbon|null $last_heartbeat_at
 * @property string|null $playback_status
 * @property string|null $current_slot
 * @property string|null $last_playback_result
 * @property string|null $last_playback_error
 * @property array<string, mixed>|null $metadata
 */
class DisplayDevice extends Model
{
    use HasUuids;

    protected $fillable = [
        'venue_id',
        'hub_id',
        'touchpoint_id',
        'code',
        'name',
        'device_type',
        'status',
        'supported_media_formats',
        'last_heartbeat_at',
        'playback_status',
        'current_slot',
        'last_playback_result',
        'last_playback_error',
        'metadata',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => RecordStatus::class,
            'supported_media_formats' => 'array',
            'last_heartbeat_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<Venue, $this> */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /** @return BelongsTo<Hub, $this> */
    public function hub(): BelongsTo
    {
        return $this->belongsTo(Hub::class);
    }

    /** @return BelongsTo<Touchpoint, $this> */
    public function touchpoint(): BelongsTo
    {
        return $this->belongsTo(Touchpoint::class);
    }

    /** @return HasMany<AdPlacement, $this> */
    public function placements(): HasMany
    {
        return $this->hasMany(AdPlacement::class);
    }

    /** @return HasMany<AdEvent, $this> */
    public function adEvents(): HasMany
    {
        return $this->hasMany(AdEvent::class);
    }
}
