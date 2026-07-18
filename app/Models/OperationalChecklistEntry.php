<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $item_key
 * @property string $status
 * @property string|null $owner_name
 * @property string|null $note
 * @property Carbon|null $due_date
 * @property Carbon|null $completed_at
 * @property int|null $updated_by
 * @property array<string, mixed>|null $metadata
 * @property-read User|null $updatedBy
 */
class OperationalChecklistEntry extends Model
{
    use HasUuids;

    protected $fillable = [
        'item_key',
        'status',
        'owner_name',
        'note',
        'due_date',
        'completed_at',
        'updated_by',
        'metadata',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
