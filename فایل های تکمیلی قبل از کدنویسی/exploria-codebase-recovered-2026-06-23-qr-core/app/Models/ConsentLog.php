<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsentLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'consent_version_id', 'user_id', 'session_hash', 'source', 'venue_id', 'accepted_at',
    ];

    protected $hidden = ['session_hash'];

    protected function casts(): array
    {
        return ['accepted_at' => 'immutable_datetime'];
    }

    public function consentVersion(): BelongsTo
    {
        return $this->belongsTo(ConsentVersion::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
