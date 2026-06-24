<?php

namespace App\Models;

use App\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $venue_id
 * @property string $code
 * @property string $name
 * @property string $partner_type
 * @property RecordStatus $status
 * @property string|null $contact_name
 * @property string|null $contact_mobile
 * @property array<string, mixed>|null $metadata
 * @property-read Venue|null $venue
 */
class PartnerAccount extends Model
{
    use HasUuids;

    protected $fillable = [
        'venue_id',
        'code',
        'name',
        'partner_type',
        'status',
        'contact_name',
        'contact_mobile',
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

    /** @return HasMany<PartnerLocation, $this> */
    public function locations(): HasMany
    {
        return $this->hasMany(PartnerLocation::class);
    }

    /** @return HasMany<PartnerUser, $this> */
    public function partnerUsers(): HasMany
    {
        return $this->hasMany(PartnerUser::class);
    }

    /** @return HasMany<RewardDefinition, $this> */
    public function rewardDefinitions(): HasMany
    {
        return $this->hasMany(RewardDefinition::class);
    }

    /** @return HasMany<RewardRedemption, $this> */
    public function rewardRedemptions(): HasMany
    {
        return $this->hasMany(RewardRedemption::class);
    }
}
