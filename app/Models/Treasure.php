<?php

namespace App\Models;

use App\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $campaign_id
 * @property string $venue_id
 * @property string|null $mission_instance_id
 * @property string $code
 * @property string $name
 * @property string $treasure_type
 * @property RecordStatus $status
 * @property array<string, mixed>|null $reveal_rule
 * @property array<string, mixed>|null $metadata
 */
class Treasure extends Model
{
    use HasUuids;

    protected $fillable = [
        'campaign_id',
        'venue_id',
        'mission_instance_id',
        'code',
        'name',
        'treasure_type',
        'status',
        'reveal_rule',
        'metadata',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['status' => RecordStatus::class, 'reveal_rule' => 'array', 'metadata' => 'array'];
    }

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /** @return BelongsTo<Venue, $this> */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /** @return BelongsTo<MissionInstance, $this> */
    public function missionInstance(): BelongsTo
    {
        return $this->belongsTo(MissionInstance::class);
    }
}
