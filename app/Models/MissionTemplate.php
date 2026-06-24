<?php

namespace App\Models;

use App\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $code
 * @property string $title
 * @property string|null $description
 * @property string $mission_type
 * @property string $trigger_type
 * @property int $point_value
 * @property RecordStatus $status
 * @property array<string, mixed>|null $metadata
 */
class MissionTemplate extends Model
{
    use HasUuids;

    protected $fillable = [
        'code',
        'title',
        'description',
        'mission_type',
        'trigger_type',
        'point_value',
        'status',
        'metadata',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['status' => RecordStatus::class, 'metadata' => 'array'];
    }

    /** @return HasMany<MissionInstance, $this> */
    public function instances(): HasMany
    {
        return $this->hasMany(MissionInstance::class);
    }
}
