<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property string $id
 * @property string $consent_version_id
 * @property int $user_id
 * @property string $session_hash
 * @property string $source
 * @property string|null $venue_id
 * @property CarbonImmutable|null $accepted_at
 */
class ConsentLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'consent_version_id', 'user_id', 'session_hash', 'source', 'venue_id', 'accepted_at',
    ];

    protected $hidden = ['session_hash'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['accepted_at' => 'immutable_datetime'];
    }

    /** @return BelongsTo<ConsentVersion, $this> */
    public function consentVersion(): BelongsTo
    {
        return $this->belongsTo(ConsentVersion::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasOne<Visit, $this> */
    public function visit(): HasOne
    {
        return $this->hasOne(Visit::class);
    }
}
