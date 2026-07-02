<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $sponsor_proposal_id
 * @property string $item_type
 * @property string $title
 * @property int|null $quantity
 * @property int|null $estimated_unit_value_amount
 * @property array<int, string>|null $target_partner_account_ids
 * @property string|null $description
 * @property array<string, mixed>|null $metadata
 */
class SponsorProposalItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'sponsor_proposal_id',
        'item_type',
        'title',
        'quantity',
        'estimated_unit_value_amount',
        'target_partner_account_ids',
        'description',
        'metadata',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'target_partner_account_ids' => 'array',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<SponsorProposal, $this> */
    public function sponsorProposal(): BelongsTo
    {
        return $this->belongsTo(SponsorProposal::class);
    }
}
