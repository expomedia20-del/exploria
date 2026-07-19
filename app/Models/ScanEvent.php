<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * @property string $id
 * @property string $qr_code_id
 * @property string $venue_id
 * @property string $touchpoint_id
 * @property string $campaign_id
 * @property int|null $user_id
 * @property string|null $session_hash
 * @property string $result
 * @property bool $risk_flag
 * @property string|null $risk_reason
 * @property string|null $ip_hash
 * @property string|null $user_agent_hash
 * @property array<string, mixed>|null $payload_json
 * @property CarbonImmutable $scanned_at
 */
class ScanEvent extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'qr_code_id', 'venue_id', 'touchpoint_id', 'campaign_id', 'user_id', 'session_hash',
        'result', 'risk_flag', 'risk_reason', 'ip_hash', 'user_agent_hash', 'payload_json', 'scanned_at',
    ];

    protected static function booted(): void
    {
        static::updating(fn (): never => throw new LogicException('Scan events are append-only.'));
        static::deleting(fn (): never => throw new LogicException('Scan events are append-only.'));
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'risk_flag' => 'boolean',
            'payload_json' => 'array',
            'scanned_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<QrCode, $this> */
    public function qrCode(): BelongsTo
    {
        return $this->belongsTo(QrCode::class);
    }
}
