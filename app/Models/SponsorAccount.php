<?php

namespace App\Models;

use App\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string|null $venue_id
 * @property string $code
 * @property string $name
 * @property string $sponsor_type
 * @property RecordStatus $status
 * @property string|null $contact_name
 * @property string|null $contact_mobile
 * @property string|null $website_url
 * @property array<string, mixed>|null $metadata
 */
class SponsorAccount extends Model
{
    use HasUuids;

    protected $fillable = [
        'venue_id',
        'code',
        'name',
        'sponsor_type',
        'status',
        'contact_name',
        'contact_mobile',
        'website_url',
        'metadata',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['status' => RecordStatus::class, 'metadata' => 'array'];
    }

    /** @return BelongsTo<Venue, $this> */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /** @return HasMany<CampaignSponsorship, $this> */
    public function campaignSponsorships(): HasMany
    {
        return $this->hasMany(CampaignSponsorship::class);
    }
}
