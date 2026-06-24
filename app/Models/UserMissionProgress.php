<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property int $user_id
 * @property string $mission_instance_id
 * @property string|null $visit_id
 * @property string $status
 * @property CarbonImmutable|null $started_at
 * @property CarbonImmutable|null $completed_at
 * @property int $points_awarded
 * @property array<string, mixed>|null $metadata
 */
class UserMissionProgress extends Model
{
    use HasUuids;

    protected $table = 'user_mission_progress';

    protected $fillable = [
        'user_id',
        'mission_instance_id',
        'visit_id',
        'status',
        'started_at',
        'completed_at',
        'points_awarded',
        'metadata',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'started_at' => 'immutable_datetime',
            'completed_at' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<MissionInstance, $this> */
    public function missionInstance(): BelongsTo
    {
        return $this->belongsTo(MissionInstance::class);
    }

    /** @return BelongsTo<Visit, $this> */
    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }
}
