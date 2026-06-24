<?php

namespace App\Models;

use App\Enums\RecordStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property string $id
 * @property string $mission_template_id
 * @property string $campaign_id
 * @property string $venue_id
 * @property string|null $hub_id
 * @property string|null $touchpoint_id
 * @property string $code
 * @property string|null $title_override
 * @property RecordStatus $status
 * @property CarbonImmutable|null $starts_at
 * @property CarbonImmutable|null $ends_at
 * @property array<string, mixed>|null $unlock_rule
 * @property array<string, mixed>|null $metadata
 */
class MissionInstance extends Model
{
    use HasUuids;

    protected $fillable = [
        'mission_template_id',
        'campaign_id',
        'venue_id',
        'hub_id',
        'touchpoint_id',
        'code',
        'title_override',
        'status',
        'starts_at',
        'ends_at',
        'unlock_rule',
        'metadata',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => RecordStatus::class,
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
            'unlock_rule' => 'array',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<MissionTemplate, $this> */
    public function missionTemplate(): BelongsTo
    {
        return $this->belongsTo(MissionTemplate::class);
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

    /** @return HasOne<Treasure, $this> */
    public function treasure(): HasOne
    {
        return $this->hasOne(Treasure::class);
    }

    /** @return HasMany<UserMissionProgress, $this> */
    public function progressRecords(): HasMany
    {
        return $this->hasMany(UserMissionProgress::class);
    }
}
