<?php

namespace App\Models;

use App\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $campaign_id
 * @property string $venue_id
 * @property string|null $partner_account_id
 * @property string $code
 * @property string $name
 * @property string $reward_type
 * @property int|null $point_cost
 * @property int|null $stock_quantity
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
        'code',
        'name',
        'reward_type',
        'point_cost',
        'stock_quantity',
        'status',
        'metadata',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['status' => RecordStatus::class, 'metadata' => 'array'];
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
