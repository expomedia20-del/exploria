<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $sponsor_proposal_id
 * @property string $partner_account_id
 * @property int $sort_order
 * @property array<string, mixed>|null $metadata
 */
class SponsorProposalPartnerAccount extends Model
{
    use HasUuids;

    protected $fillable = [
        'sponsor_proposal_id',
        'partner_account_id',
        'sort_order',
        'metadata',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    /** @return BelongsTo<SponsorProposal, $this> */
    public function sponsorProposal(): BelongsTo
    {
        return $this->belongsTo(SponsorProposal::class);
    }

    /** @return BelongsTo<PartnerAccount, $this> */
    public function partnerAccount(): BelongsTo
    {
        return $this->belongsTo(PartnerAccount::class);
    }
}
