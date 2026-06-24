<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdApproval extends Model
{
    use HasUuids;

    protected $fillable = [
        'ad_request_id',
        'reviewer_user_id',
        'action',
        'notes',
        'metadata',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    /** @return BelongsTo<AdRequest, $this> */
    public function adRequest(): BelongsTo
    {
        return $this->belongsTo(AdRequest::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }
}
