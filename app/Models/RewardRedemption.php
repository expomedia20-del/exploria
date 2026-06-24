<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $user_reward_id
 * @property int $user_id
 * @property string|null $partner_account_id
 * @property string $redemption_code
 * @property string $status
 * @property CarbonImmutable|null $redeemed_at
 * @property array<string, mixed>|null $metadata
 */
class RewardRedemption extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_reward_id',
        'user_id',
        'partner_account_id',
        'redemption_code',
        'status',
        'redeemed_at',
        'metadata',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['redeemed_at' => 'immutable_datetime', 'metadata' => 'array'];
    }

    /** @return BelongsTo<UserReward, $this> */
    public function userReward(): BelongsTo
    {
        return $this->belongsTo(UserReward::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<PartnerAccount, $this> */
    public function partnerAccount(): BelongsTo
    {
        return $this->belongsTo(PartnerAccount::class);
    }
}
