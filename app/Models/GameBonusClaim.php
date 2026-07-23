<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $game_party_id
 * @property string $ad_request_id
 * @property int $started_by_user_id
 * @property string $status
 * @property int $points_awarded
 * @property CarbonImmutable $started_at
 * @property CarbonImmutable|null $completed_at
 * @property array<string, mixed>|null $metadata
 */
class GameBonusClaim extends Model
{
    use HasUuids;

    protected $fillable = [
        'game_party_id', 'ad_request_id', 'started_by_user_id', 'status',
        'points_awarded', 'started_at', 'completed_at', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'points_awarded' => 'integer',
            'started_at' => 'immutable_datetime',
            'completed_at' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<GameParty, $this> */
    public function party(): BelongsTo
    {
        return $this->belongsTo(GameParty::class, 'game_party_id');
    }

    /** @return BelongsTo<AdRequest, $this> */
    public function adRequest(): BelongsTo
    {
        return $this->belongsTo(AdRequest::class);
    }

    /** @return BelongsTo<User, $this> */
    public function startedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by_user_id');
    }
}
