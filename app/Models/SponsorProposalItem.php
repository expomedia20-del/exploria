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
 * @property array<int, array{partner_account_id: string, quantity: int}>|null $partner_allocations
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
        'partner_allocations',
        'description',
        'metadata',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'target_partner_account_ids' => 'array',
            'partner_allocations' => 'array',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<SponsorProposal, $this> */
    public function sponsorProposal(): BelongsTo
    {
        return $this->belongsTo(SponsorProposal::class);
    }
}
