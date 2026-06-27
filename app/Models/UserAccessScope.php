<?php

namespace App\Models;

use App\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property int $user_id
 * @property string $role_key
 * @property string $scope_type
 * @property string|null $scope_id
 * @property RecordStatus $status
 * @property array<string, mixed>|null $metadata
 * @property-read User|null $user
 */
class UserAccessScope extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'role_key',
        'scope_type',
        'scope_id',
        'status',
        'metadata',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['status' => RecordStatus::class, 'metadata' => 'array'];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
