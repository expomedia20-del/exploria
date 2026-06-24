<?php

namespace App\Models;

use App\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $hub_id
 * @property int $user_id
 * @property string $assignment_role
 * @property RecordStatus $status
 * @property array<string, mixed>|null $metadata
 * @property-read Hub|null $hub
 * @property-read User|null $user
 */
class HubManagementAssignment extends Model
{
    use HasUuids;

    protected $fillable = ['hub_id', 'user_id', 'assignment_role', 'status', 'metadata'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['status' => RecordStatus::class, 'metadata' => 'array'];
    }

    /** @return BelongsTo<Hub, $this> */
    public function hub(): BelongsTo
    {
        return $this->belongsTo(Hub::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
