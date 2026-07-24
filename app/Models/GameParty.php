<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property string $id
 * @property string $campaign_id
 * @property string|null $visit_id
 * @property int $owner_user_id
 * @property string $mode
 * @property string|null $name
 * @property string|null $invite_code
 * @property string $cycle_key
 * @property string|null $route_key
 * @property string $status
 * @property int $score
 * @property bool $collaboration_bonus_awarded
 * @property array<string, mixed>|null $metadata
 * @property-read Campaign $campaign
 * @property-read Collection<int, GamePartyMember> $members
 * @property-read Collection<int, GamePartyInvitation> $invitations
 * @property-read Collection<int, GameChallengeProgress> $progress
 * @property-read GameEntryPass|null $entryPass
 * @property-read Collection<int, GameBonusClaim> $bonusClaims
 */
class GameParty extends Model
{
    use HasUuids;

    protected $fillable = [
        'campaign_id', 'visit_id', 'owner_user_id', 'mode', 'name', 'invite_code',
        'cycle_key', 'route_key', 'status', 'score', 'collaboration_bonus_awarded',
        'completed_at', 'expires_at', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'collaboration_bonus_awarded' => 'boolean',
            'completed_at' => 'immutable_datetime',
            'expires_at' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /** @return BelongsTo<Visit, $this> */
    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /** @return HasMany<GamePartyMember, $this> */
    public function members(): HasMany
    {
        return $this->hasMany(GamePartyMember::class);
    }

    /** @return HasMany<GamePartyInvitation, $this> */
    public function invitations(): HasMany
    {
        return $this->hasMany(GamePartyInvitation::class);
    }

    /** @return HasMany<GameChallengeProgress, $this> */
    public function progress(): HasMany
    {
        return $this->hasMany(GameChallengeProgress::class);
    }

    /** @return HasOne<GameEntryPass, $this> */
    public function entryPass(): HasOne
    {
        return $this->hasOne(GameEntryPass::class);
    }

    /** @return HasMany<GameBonusClaim, $this> */
    public function bonusClaims(): HasMany
    {
        return $this->hasMany(GameBonusClaim::class);
    }
}
