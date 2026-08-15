<?php

namespace App\Models;

use App\Enums\RecordStatus;
use App\Enums\RewardInventoryMode;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $campaign_id
 * @property string $venue_id
 * @property string|null $partner_account_id
 * @property string|null $cost_owner_financial_account_id
 * @property string $code
 * @property string $name
 * @property string $reward_type
 * @property RewardInventoryMode|null $inventory_mode
 * @property int|null $point_cost
 * @property int|null $stock_quantity
 * @property CarbonImmutable|null $available_from
 * @property CarbonImmutable|null $available_until
 * @property int|null $expires_after_minutes
 * @property int|null $per_user_award_limit
 * @property RecordStatus $status
 * @property array<string, mixed>|null $metadata
 */
class RewardDefinition extends Model
{
    use HasUuids;

    protected $fillable = [
        'campaign_id',
        'venue_id',
        'partner_account_id',
        'cost_owner_financial_account_id',
        'code',
        'name',
        'reward_type',
        'inventory_mode',
        'point_cost',
        'stock_quantity',
        'available_from',
        'available_until',
        'expires_after_minutes',
        'per_user_award_limit',
        'status',
        'metadata',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => RecordStatus::class,
            'inventory_mode' => RewardInventoryMode::class,
            'available_from' => 'immutable_datetime',
            'available_until' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * @param  Builder<RewardDefinition>  $query
     * @return Builder<RewardDefinition>
     */
    public function scopeAvailableForIssuance(Builder $query, ?CarbonInterface $at = null): Builder
    {
        $at ??= now();

        return $query
            ->where('status', RecordStatus::Active->value)
            ->whereNotNull('cost_owner_financial_account_id')
            ->whereNotNull('inventory_mode')
            ->where('per_user_award_limit', 1)
            ->where('available_from', '<=', $at)
            ->where('available_until', '>', $at);
    }

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
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

    /** @return BelongsTo<FinancialAccount, $this> */
    public function costOwnerFinancialAccount(): BelongsTo
    {
        return $this->belongsTo(FinancialAccount::class, 'cost_owner_financial_account_id');
    }

    /** @return HasMany<UserReward, $this> */
    public function userRewards(): HasMany
    {
        return $this->hasMany(UserReward::class);
    }

    /** @return HasMany<RewardInventoryAllocation, $this> */
    public function inventoryAllocations(): HasMany
    {
        return $this->hasMany(RewardInventoryAllocation::class);
    }
}
