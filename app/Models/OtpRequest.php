<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $mobile
 * @property string $mobile_hash
 * @property string $code_hash
 * @property string|null $source_qr_code
 * @property int $attempts
 * @property CarbonImmutable|null $expires_at
 * @property CarbonImmutable|null $verified_at
 */
class OtpRequest extends Model
{
    use HasUuids;

    protected $fillable = [
        'mobile', 'mobile_hash', 'code_hash', 'source_qr_code', 'attempts',
        'expires_at', 'verified_at',
    ];

    protected $hidden = ['mobile', 'mobile_hash', 'code_hash'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'mobile' => 'encrypted',
            'expires_at' => 'immutable_datetime',
            'verified_at' => 'immutable_datetime',
        ];
    }
}
