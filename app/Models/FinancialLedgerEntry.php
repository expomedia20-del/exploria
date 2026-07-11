<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $financial_account_id
 * @property string $entry_type
 * @property string $direction
 * @property int $amount
 * @property string $currency
 * @property string $status
 * @property string|null $contract_type
 * @property string|null $source_type
 * @property string|null $source_id
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $occurred_on
 * @property int|null $created_by
 * @property array<string, mixed>|null $metadata
 * @property-read FinancialAccount $financialAccount
 * @property-read User|null $createdBy
 */
class FinancialLedgerEntry extends Model
{
    use HasUuids;

    protected $fillable = [
        'financial_account_id',
        'entry_type',
        'direction',
        'amount',
        'currency',
        'status',
        'contract_type',
        'source_type',
        'source_id',
        'description',
        'occurred_on',
        'created_by',
        'metadata',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'occurred_on' => 'date',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<FinancialAccount, $this> */
    public function financialAccount(): BelongsTo
    {
        return $this->belongsTo(FinancialAccount::class);
    }

    /** @return BelongsTo<User, $this> */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
