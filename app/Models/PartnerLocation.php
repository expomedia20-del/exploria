<?php

namespace App\Models;

use App\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $partner_account_id
 * @property string $venue_id
 * @property string|null $zone_id
 * @property string|null $hub_id
 * @property string|null $touchpoint_id
 * @property string $location_role
 * @property RecordStatus $status
 * @property array<string, mixed>|null $metadata
 * @property-read PartnerAccount|null $partnerAccount
 * @property-read Venue|null $venue
 * @property-read Zone|null $zone
 * @property-read Hub|null $hub
 * @property-read Touchpoint|null $touchpoint
 */
class PartnerLocation extends Model
{
    use HasUuids;

    protected $fillable = [
        'partner_account_id',
        'venue_id',
        'zone_id',
        'hub_id',
        'touchpoint_id',
        'location_role',
        'status',
        'metadata',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['status' => RecordStatus::class, 'metadata' => 'array'];
    }

    /** @return BelongsTo<PartnerAccount, $this> */
    public function partnerAccount(): BelongsTo
    {
        return $this->belongsTo(PartnerAccount::class);
    }

    /** @return BelongsTo<Venue, $this> */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /** @return BelongsTo<Zone, $this> */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    /** @return BelongsTo<Hub, $this> */
    public function hub(): BelongsTo
    {
        return $this->belongsTo(Hub::class);
    }

    /** @return BelongsTo<Touchpoint, $this> */
    public function touchpoint(): BelongsTo
    {
        return $this->belongsTo(Touchpoint::class);
    }
}
