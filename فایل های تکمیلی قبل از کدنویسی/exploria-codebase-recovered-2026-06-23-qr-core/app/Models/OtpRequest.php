<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class OtpRequest extends Model
{
    use HasUuids;

    protected $fillable = [
        'mobile', 'mobile_hash', 'code_hash', 'source_qr_code', 'attempts',
        'expires_at', 'verified_at',
    ];

    protected $hidden = ['mobile', 'mobile_hash', 'code_hash'];

    protected function casts(): array
    {
        return [
            'mobile' => 'encrypted',
            'expires_at' => 'immutable_datetime',
            'verified_at' => 'immutable_datetime',
        ];
    }
}
