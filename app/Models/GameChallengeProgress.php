<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $game_party_id
 * @property int $step_index
 * @property string $status
 * @property int $points_awarded
 * @property int $attempts
 * @property array<string, mixed>|null $metadata
 */
class GameChallengeProgress extends Model
{
    use HasUuids;

    protected $table = 'game_challenge_progress';

    protected $fillable = [
        'game_party_id', 'step_index', 'status', 'points_awarded',
        'attempts', 'completed_at', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'step_index' => 'integer',
            'points_awarded' => 'integer',
            'attempts' => 'integer',
            'completed_at' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<GameParty, $this> */
    public function party(): BelongsTo
    {
        return $this->belongsTo(GameParty::class, 'game_party_id');
    }
}
