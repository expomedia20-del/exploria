<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdCreative extends Model
{
    use HasUuids;

    protected $fillable = [
        'ad_request_id',
        'creative_type',
        'asset_url',
        'headline',
        'body_copy',
        'cta_text',
        'status',
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
}
