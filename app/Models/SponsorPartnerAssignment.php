<?php

namespace App\Models;

use App\Enums\RecordStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $sponsor_account_id
 * @property string $partner_account_id
 * @property string|null $campaign_id
 * @property string $activation_role
 * @property RecordStatus $status
 * @property CarbonImmutable|null $starts_at
 * @property CarbonImmutable|null $ends_at
 * @property string|null $notes
 * @property array<string, mixed>|null $metadata
 */
class SponsorPartnerAssignment extends Model
{
    use HasUuids;

    protected $fillable = [
        'sponsor_account_id',
        'partner_account_id',
        'campaign_id',
        'activation_role',
        'status',
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

    /** @return BelongsTo<SponsorAccount, $this> */
    public function sponsorAccount(): BelongsTo
    {
        return $this->belongsTo(SponsorAccount::class);
    }

    /** @return BelongsTo<PartnerAccount, $this> */
    public function partnerAccount(): BelongsTo
    {
        return $this->belongsTo(PartnerAccount::class);
    }

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}
