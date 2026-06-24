<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
