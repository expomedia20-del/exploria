<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property int $user_id
 * @property string $reward_definition_id
 * @property string $campaign_id
 * @property string $status
 * @property CarbonImmutable|null $awarded_at
 * @property CarbonImmutable|null $expires_at
 * @property array<string, mixed>|null $metadata
 */
class UserReward extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'reward_definition_id',
        'campaign_id',
        'status',
        'awarded_at',
        'expires_at',
        'metadata',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'awarded_at' => 'immutable_datetime',
            'expires_at' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<RewardDefinition, $this> */
    public function rewardDefinition(): BelongsTo
    {
        return $this->belongsTo(RewardDefinition::class);
    }

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /** @return HasMany<RewardRedemption, $this> */
    public function redemptions(): HasMany
    {
        return $this->hasMany(RewardRedemption::class);
    }
}
