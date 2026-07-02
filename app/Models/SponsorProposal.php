<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $sponsor_account_id
 * @property string|null $campaign_id
 * @property string|null $preferred_partner_account_id
 * @property string $code
 * @property string $title
 * @property string $proposal_type
 * @property string $objective
 * @property string $status
 * @property int|null $proposed_budget_amount
 * @property int|null $estimated_value_amount
 * @property string|null $reward_offer
 * @property string|null $discount_offer
 * @property string|null $asset_url
 * @property string|null $target_audience
 * @property string|null $notes
 * @property array<string, mixed>|null $metadata
 */
class SponsorProposal extends Model
{
    use HasUuids;

    protected $fillable = [
        'sponsor_account_id',
        'campaign_id',
        'preferred_partner_account_id',
        'code',
        'title',
        'proposal_type',
        'objective',
        'status',
        'proposed_budget_amount',
        'estimated_value_amount',
        'reward_offer',
        'discount_offer',
        'asset_url',
        'target_audience',
        'notes',
        'metadata',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    /** @return BelongsTo<SponsorAccount, $this> */
    public function sponsorAccount(): BelongsTo
    {
        return $this->belongsTo(SponsorAccount::class);
    }

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /** @return BelongsTo<PartnerAccount, $this> */
    public function preferredPartnerAccount(): BelongsTo
    {
        return $this->belongsTo(PartnerAccount::class, 'preferred_partner_account_id');
    }

    /** @return HasMany<SponsorProposalPartnerAccount, $this> */
    public function partnerAccounts(): HasMany
    {
        return $this->hasMany(SponsorProposalPartnerAccount::class);
    }

    /** @return HasMany<SponsorProposalItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(SponsorProposalItem::class);
    }
}
