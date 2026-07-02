<?php

namespace App\Models;

use App\Enums\RecordStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $campaign_id
 * @property string $sponsor_account_id
 * @property string $sponsorship_goal
 * @property string $package_type
 * @property RecordStatus $status
 * @property int|null $budget_amount
 * @property int|null $contract_value
 * @property CarbonImmutable|null $starts_at
 * @property CarbonImmutable|null $ends_at
 * @property string|null $notes
 * @property array<string, mixed>|null $metadata
 */
class CampaignSponsorship extends Model
{
    use HasUuids;

    protected $fillable = [
        'campaign_id',
        'sponsor_account_id',
        'sponsorship_goal',
        'package_type',
        'status',
        'budget_amount',
        'contract_value',
        'starts_at',
        'ends_at',
        'notes',
        'metadata',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => RecordStatus::class,
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /** @return BelongsTo<SponsorAccount, $this> */
    public function sponsorAccount(): BelongsTo
    {
        return $this->belongsTo(SponsorAccount::class);
    }
}
