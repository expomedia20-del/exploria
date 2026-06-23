<?php

namespace App\Models;

use App\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $venue_id
 * @property string $code
 * @property string $name
 * @property RecordStatus $status
 * @property array<string, mixed>|null $metadata
 */
class Zone extends Model
{
    use HasUuids;

    protected $fillable = ['venue_id', 'code', 'name', 'status', 'metadata'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['status' => RecordStatus::class, 'metadata' => 'array'];
    }

    /** @return BelongsTo<Venue, $this> */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /** @return HasMany<Hub, $this> */
    public function hubs(): HasMany
    {
        return $this->hasMany(Hub::class);
    }
}
