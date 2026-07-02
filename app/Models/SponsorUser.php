<?php

namespace App\Models;

use App\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $sponsor_account_id
 * @property int $user_id
 * @property string $role
 * @property RecordStatus $status
 * @property array<string, mixed>|null $metadata
 */
class SponsorUser extends Model
{
    use HasUuids;

    protected $fillable = ['sponsor_account_id', 'user_id', 'role', 'status', 'metadata'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['status' => RecordStatus::class, 'metadata' => 'array'];
    }

    /** @return BelongsTo<SponsorAccount, $this> */
    public function sponsorAccount(): BelongsTo
    {
        return $this->belongsTo(SponsorAccount::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
