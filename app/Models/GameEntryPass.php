<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $game_party_id
 * @property int $issued_to_user_id
 * @property string $code
 * @property string $token_hash
 * @property string $status
 * @property CarbonImmutable $expires_at
 * @property CarbonImmutable|null $redeemed_at
 * @property array<string, mixed>|null $metadata
 */
class GameEntryPass extends Model
{
    use HasUuids;

    protected $fillable = [
        'game_party_id', 'issued_to_user_id', 'code', 'token_hash', 'status',
        'expires_at', 'redeemed_at', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'immutable_datetime',
            'redeemed_at' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<GameParty, $this> */
    public function party(): BelongsTo
    {
        return $this->belongsTo(GameParty::class, 'game_party_id');
    }

    /** @return BelongsTo<User, $this> */
    public function issuedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_to_user_id');
    }
}
