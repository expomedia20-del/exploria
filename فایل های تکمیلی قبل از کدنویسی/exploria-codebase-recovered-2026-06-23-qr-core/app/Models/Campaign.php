<?php

namespace App\Models;

use App\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    use HasUuids;

    protected $fillable = ['venue_id', 'code', 'name', 'campaign_type', 'status', 'start_at', 'end_at', 'metadata'];

    protected function casts(): array
    {
        return ['status' => RecordStatus::class, 'start_at' => 'immutable_datetime', 'end_at' => 'immutable_datetime', 'metadata' => 'array'];
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function qrCodes(): HasMany
    {
        return $this->hasMany(QrCode::class);
    }
}
