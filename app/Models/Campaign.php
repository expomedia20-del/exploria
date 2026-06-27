<?php

namespace App\Models;

use App\Enums\RecordStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $venue_id
 * @property string $code
 * @property string $name
 * @property string $campaign_type
 * @property RecordStatus $status
 * @property CarbonImmutable|null $start_at
 * @property CarbonImmutable|null $end_at
 * @property array<string, mixed>|null $metadata
 */
class Campaign extends Model
{
    use HasUuids;

    protected $fillable = ['venue_id', 'code', 'name', 'campaign_type', 'status', 'start_at', 'end_at', 'metadata'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['status' => RecordStatus::class, 'start_at' => 'immutable_datetime', 'end_at' => 'immutable_datetime', 'metadata' => 'array'];
    }

    /** @return BelongsTo<Venue, $this> */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /** @return HasMany<QrCode, $this> */
    public function qrCodes(): HasMany
    {
        return $this->hasMany(QrCode::class);
    }

    /** @return HasMany<Visit, $this> */
    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    /** @return HasMany<MissionInstance, $this> */
    public function missionInstances(): HasMany
    {
        return $this->hasMany(MissionInstance::class);
    }

    /** @return HasMany<Treasure, $this> */
    public function treasures(): HasMany
    {
        return $this->hasMany(Treasure::class);
    }

    /** @return HasMany<RewardDefinition, $this> */
    public function rewardDefinitions(): HasMany
    {
        return $this->hasMany(RewardDefinition::class);
    }

    /** @return HasMany<UserReward, $this> */
    public function userRewards(): HasMany
    {
        return $this->hasMany(UserReward::class);
    }
    /** @return HasMany<CampaignParticipant, $this> */
    public function campaignParticipants(): HasMany
    {
        return $this->hasMany(CampaignParticipant::class);
    }
}
