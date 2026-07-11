<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $account_key
 * @property string $account_type
 * @property string $owner_name
 * @property string|null $owner_reference_type
 * @property string|null $owner_reference_id
 * @property string $currency
 * @property string $status
 * @property array<string, mixed>|null $metadata
 */
class FinancialAccount extends Model
{
    use HasUuids;

    protected $fillable = [
        'account_key',
        'account_type',
        'owner_name',
        'owner_reference_type',
        'owner_reference_id',
        'currency',
        'status',
        'metadata',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    /** @return HasMany<FinancialLedgerEntry, $this> */
    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(FinancialLedgerEntry::class);
    }
}
