<?php

namespace App\Models;

use App\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $hub_id
 * @property string $code
 * @property string $label
 * @property string $type
 * @property string $owner_type
 * @property RecordStatus $status
 * @property string|null $install_notes
 * @property array<string, mixed>|null $metadata
 * @property-read Hub|null $hub
 */
class Touchpoint extends Model
{
    use HasUuids;

    protected $fillable = ['hub_id', 'code', 'label', 'type', 'owner_type', 'status', 'install_notes', 'metadata'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['status' => RecordStatus::class, 'metadata' => 'array'];
    }

    /** @return BelongsTo<Hub, $this> */
    public function hub(): BelongsTo
    {
        return $this->belongsTo(Hub::class);
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
}
