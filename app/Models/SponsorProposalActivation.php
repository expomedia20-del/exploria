<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $sponsor_proposal_id
 * @property string $campaign_id
 * @property string|null $campaign_sponsorship_id
 * @property string $status
 * @property array<int, string>|null $reward_definition_ids
 * @property array<int, string>|null $partner_assignment_ids
 * @property array<string, mixed>|null $metadata
 */
class SponsorProposalActivation extends Model
{
    use HasUuids;

    protected $fillable = [
        'sponsor_proposal_id',
        'campaign_id',
        'campaign_sponsorship_id',
        'status',
        'reward_definition_ids',
        'partner_assignment_ids',
        'metadata',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'reward_definition_ids' => 'array',
            'partner_assignment_ids' => 'array',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<SponsorProposal, $this> */
    public function sponsorProposal(): BelongsTo
    {
        return $this->belongsTo(SponsorProposal::class);
    }

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /** @return BelongsTo<CampaignSponsorship, $this> */
    public function campaignSponsorship(): BelongsTo
    {
        return $this->belongsTo(CampaignSponsorship::class);
    }
}
