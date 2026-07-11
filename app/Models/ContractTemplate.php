<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $code
 * @property string $title
 * @property string $party_type
 * @property string $pricing_model
 * @property int $base_amount
 * @property int|null $platform_fee_percent
 * @property string|null $settlement_terms
 * @property string|null $scope_summary
 * @property string $status
 * @property array<string, mixed>|null $metadata
 */
class ContractTemplate extends Model
{
    use HasUuids;

    protected $fillable = [
        'code',
        'title',
        'party_type',
        'pricing_model',
        'base_amount',
        'platform_fee_percent',
        'settlement_terms',
        'scope_summary',
        'status',
        'metadata',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'base_amount' => 'integer',
            'platform_fee_percent' => 'integer',
            'metadata' => 'array',
        ];
    }
}
