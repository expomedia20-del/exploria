<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $game_party_id
 * @property int $inviter_user_id
 * @property int|null $invitee_user_id
 * @property string $mobile_hash
 * @property string $status
 * @property CarbonImmutable $invited_at
 * @property CarbonImmutable|null $accepted_at
 * @property CarbonImmutable|null $closed_at
 * @property array<string, mixed>|null $metadata
 * @property-read GameParty $party
 * @property-read User $inviter
 * @property-read User|null $invitee
 */
class GamePartyInvitation extends Model
{
    use HasUuids;

    protected $fillable = [
        'game_party_id', 'inviter_user_id', 'invitee_user_id', 'mobile_hash',
        'status', 'invited_at', 'accepted_at', 'closed_at', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'invited_at' => 'immutable_datetime',
            'accepted_at' => 'immutable_datetime',
            'closed_at' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<GameParty, $this> */
    public function party(): BelongsTo
    {
        return $this->belongsTo(GameParty::class, 'game_party_id');
    }

    /** @return BelongsTo<User, $this> */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inviter_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function invitee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invitee_user_id');
    }
}
