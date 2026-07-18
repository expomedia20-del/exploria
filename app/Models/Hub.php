<?php

namespace App\Models;

use App\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $zone_id
 * @property string $code
 * @property string $name
 * @property string $hub_type
 * @property RecordStatus $status
 * @property array<string, mixed>|null $metadata
 * @property-read Zone|null $zone
 */
class Hub extends Model
{
    use HasUuids;

    protected $fillable = ['zone_id', 'code', 'name', 'hub_type', 'status', 'metadata'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['status' => RecordStatus::class, 'metadata' => 'array'];
    }

    /** @return BelongsTo<Zone, $this> */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    /** @return HasMany<Touchpoint, $this> */
    public function touchpoints(): HasMany
    {
        return $this->hasMany(Touchpoint::class);
    }

    /** @return HasMany<PartnerLocation, $this> */
    public function partnerLocations(): HasMany
    {
        return $this->hasMany(PartnerLocation::class);
    }

    /** @return HasMany<HubManagementAssignment, $this> */
    public function managementAssignments(): HasMany
    {
        return $this->hasMany(HubManagementAssignment::class);
    }

    /** @return HasMany<MissionInstance, $this> */
    public function missionInstances(): HasMany
    {
        return $this->hasMany(MissionInstance::class);
    }

    /** @return HasMany<AdRequest, $this> */
    public function adRequests(): HasMany
    {
        return $this->hasMany(AdRequest::class);
    }

    /** @return HasMany<DisplayDevice, $this> */
    public function displayDevices(): HasMany
    {
        return $this->hasMany(DisplayDevice::class);
    }

    /** @return HasMany<CampaignParticipant, $this> */
    public function campaignParticipants(): HasMany
    {
        return $this->hasMany(CampaignParticipant::class);
    }
}
