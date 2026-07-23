<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $game_party_id
 * @property int|null $user_id
 * @property string $display_name
 * @property string $member_type
 * @property string $role
 * @property string $status
 * @property array<string, mixed>|null $metadata
 */
class GamePartyMember extends Model
{
    use HasUuids;

    protected $fillable = [
        'game_party_id', 'user_id', 'display_name', 'member_type', 'role',
        'status', 'joined_at', 'metadata',
    ];

    protected function casts(): array
    {
        return ['joined_at' => 'immutable_datetime', 'metadata' => 'array'];
    }

    /** @return BelongsTo<GameParty, $this> */
    public function party(): BelongsTo
    {
        return $this->belongsTo(GameParty::class, 'game_party_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
