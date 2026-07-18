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
 * @property string $venue_id
 * @property string|null $hub_id
 * @property string|null $partner_account_id
 * @property string $participant_type
 * @property string $participation_role
 * @property RecordStatus $status
 * @property string $onboarding_status
 * @property CarbonImmutable|null $joined_at
 * @property array<string, mixed>|null $metadata
 */
class CampaignParticipant extends Model
{
    use HasUuids;

    protected $fillable = [
        'campaign_id',
        'venue_id',
        'hub_id',
        'partner_account_id',
        'participant_type',
        'participation_role',
        'status',
        'onboarding_status',
        'joined_at',
        'metadata',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => RecordStatus::class,
            'joined_at' => 'immutable_datetime',
            'metadata' => 'array',
        ];
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

    /** @return BelongsTo<Hub, $this> */
    public function hub(): BelongsTo
    {
        return $this->belongsTo(Hub::class);
    }

    /** @return BelongsTo<PartnerAccount, $this> */
    public function partnerAccount(): BelongsTo
    {
        return $this->belongsTo(PartnerAccount::class);
    }
}
