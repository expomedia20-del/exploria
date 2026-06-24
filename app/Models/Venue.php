<?php

namespace App\Models;

use App\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $code
 * @property string $name
 * @property string $city
 * @property RecordStatus $status
 * @property RecordStatus $profile_status
 * @property array<string, mixed>|null $metadata
 */
class Venue extends Model
{
    use HasUuids;

    protected $fillable = ['code', 'name', 'city', 'status', 'profile_status', 'metadata'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['status' => RecordStatus::class, 'profile_status' => RecordStatus::class, 'metadata' => 'array'];
    }

    /** @return HasMany<Zone, $this> */
    public function zones(): HasMany
    {
        return $this->hasMany(Zone::class);
    }

    /** @return HasMany<Campaign, $this> */
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    /** @return HasMany<QrCode, $this> */
    public function qrCodes(): HasMany
    {
        return $this->hasMany(QrCode::class);
    }

    /** @return HasMany<Visit, $this> */
    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    /** @return HasMany<PartnerAccount, $this> */
    public function partnerAccounts(): HasMany
    {
        return $this->hasMany(PartnerAccount::class);
    }

    /** @return HasMany<MissionInstance, $this> */
    public function missionInstances(): HasMany
    {
        return $this->hasMany(MissionInstance::class);
    }

    /** @return HasMany<Treasure, $this> */
    public function treasures(): HasMany
    {
        return $this->hasMany(Treasure::class);
    }

    /** @return HasMany<RewardDefinition, $this> */
    public function rewardDefinitions(): HasMany
    {
        return $this->hasMany(RewardDefinition::class);
    }
}
