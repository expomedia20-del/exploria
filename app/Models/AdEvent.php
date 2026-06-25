<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $ad_request_id
 * @property string|null $display_device_id
 * @property string $event_type
 * @property CarbonImmutable $occurred_at
 * @property array<string, mixed>|null $metadata
 */
class AdEvent extends Model
{
    use HasUuids;

    protected $fillable = [
        'ad_request_id',
        'display_device_id',
        'event_type',
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

    /** @return BelongsTo<AdRequest, $this> */
    public function adRequest(): BelongsTo
    {
        return $this->belongsTo(AdRequest::class);
    }

    /** @return BelongsTo<DisplayDevice, $this> */
    public function displayDevice(): BelongsTo
    {
        return $this->belongsTo(DisplayDevice::class);
    }
}
