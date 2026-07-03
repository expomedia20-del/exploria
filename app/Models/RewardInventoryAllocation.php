<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $reward_definition_id
 * @property string|null $treasure_id
 * @property string $campaign_id
 * @property string|null $sponsor_proposal_activation_id
 * @property string $partner_account_id
 * @property string|null $mission_instance_id
 * @property int $allocated_quantity
 * @property int $reserved_quantity
 * @property int $redeemed_quantity
 * @property string $status
 * @property array<string, mixed>|null $metadata
 */
class RewardInventoryAllocation extends Model
{
    use HasUuids;

    protected $fillable = [
        'reward_definition_id',
        'treasure_id',
        'campaign_id',
        'sponsor_proposal_activation_id',
        'partner_account_id',
        'mission_instance_id',
        'allocated_quantity',
        'reserved_quantity',
        'redeemed_quantity',
        'status',
        'metadata',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    /** @return BelongsTo<RewardDefinition, $this> */
    public function rewardDefinition(): BelongsTo
    {
        return $this->belongsTo(RewardDefinition::class);
    }

    /** @return BelongsTo<Treasure, $this> */
    public function treasure(): BelongsTo
    {
        return $this->belongsTo(Treasure::class);
    }

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /** @return BelongsTo<SponsorProposalActivation, $this> */
    public function sponsorProposalActivation(): BelongsTo
    {
        return $this->belongsTo(SponsorProposalActivation::class);
    }

    /** @return BelongsTo<PartnerAccount, $this> */
    public function partnerAccount(): BelongsTo
    {
        return $this->belongsTo(PartnerAccount::class);
    }

    /** @return BelongsTo<MissionInstance, $this> */
    public function missionInstance(): BelongsTo
    {
        return $this->belongsTo(MissionInstance::class);
    }
}
