<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $venue_id
 * @property string|null $partner_account_id
 * @property string|null $hub_id
 * @property string|null $touchpoint_id
 * @property int|null $submitted_by_user_id
 * @property string $code
 * @property string $title
 * @property string|null $body_copy
 * @property string|null $cta_text
 * @property string|null $target_url
 * @property string $advertiser_type
 * @property string $ad_type
 * @property string $status
 * @property CarbonImmutable|null $starts_at
 * @property CarbonImmutable|null $ends_at
 * @property int|null $budget_amount
 * @property int|null $impression_cap
 * @property int|null $click_cap
 * @property array<string, mixed>|null $metadata
 */
class AdRequest extends Model
{
    use HasUuids;

    protected $fillable = [
        'venue_id',
        'partner_account_id',
        'hub_id',
        'touchpoint_id',
        'submitted_by_user_id',
        'code',
        'title',
        'body_copy',
        'cta_text',
        'target_url',
        'advertiser_type',
        'ad_type',
        'status',
        'starts_at',
        'ends_at',
        'budget_amount',
        'impression_cap',
        'click_cap',
        'metadata',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<Venue, $this> */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /** @return BelongsTo<PartnerAccount, $this> */
    public function partnerAccount(): BelongsTo
    {
        return $this->belongsTo(PartnerAccount::class);
    }

    /** @return BelongsTo<Hub, $this> */
    public function hub(): BelongsTo
    {
        return $this->belongsTo(Hub::class);
    }

    /** @return BelongsTo<Touchpoint, $this> */
    public function touchpoint(): BelongsTo
    {
        return $this->belongsTo(Touchpoint::class);
    }

    /** @return BelongsTo<User, $this> */
    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    /** @return HasMany<AdCreative, $this> */
    public function creatives(): HasMany
    {
        return $this->hasMany(AdCreative::class);
    }

    /** @return HasMany<AdPlacement, $this> */
    public function placements(): HasMany
    {
        return $this->hasMany(AdPlacement::class);
    }

    /** @return HasMany<AdApproval, $this> */
    public function approvals(): HasMany
    {
        return $this->hasMany(AdApproval::class);
    }

    /** @return HasMany<AdEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(AdEvent::class);
    }
}
