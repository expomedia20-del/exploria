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
 * @property string $placement_type
 * @property string $status
 * @property CarbonImmutable|null $starts_at
 * @property CarbonImmutable|null $ends_at
 * @property int $priority
 * @property array<string, mixed>|null $metadata
 */
class AdPlacement extends Model
{
    use HasUuids;

    protected $fillable = [
        'ad_request_id',
        'display_device_id',
        'placement_type',
        'status',
        'starts_at',
        'ends_at',
        'priority',
        'metadata',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
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
