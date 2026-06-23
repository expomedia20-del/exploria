<?php

namespace App\Models;

use App\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venue extends Model
{
    use HasUuids;

    protected $fillable = ['code', 'name', 'city', 'status', 'profile_status', 'metadata'];

    protected function casts(): array
    {
        return ['status' => RecordStatus::class, 'profile_status' => RecordStatus::class, 'metadata' => 'array'];
    }

    public function zones(): HasMany
    {
        return $this->hasMany(Zone::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function qrCodes(): HasMany
    {
        return $this->hasMany(QrCode::class);
    }
}
