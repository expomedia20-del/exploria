<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFinancialLedgerEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'financial_account_id' => ['required', 'uuid', 'exists:financial_accounts,id'],
            'entry_type' => ['required', 'string', Rule::in([
                'commitment',
                'sponsor_budget',
                'reward_redemption',
                'platform_fee',
                'ad_display_revenue',
                'venue_share',
                'partner_settlement',
                'adjustment',
            ])],
            'direction' => ['required', 'string', Rule::in(['credit', 'debit'])],
            'amount' => ['required', 'integer', 'min:1', 'max:999999999999'],
            'status' => ['nullable', 'string', Rule::in(['draft', 'posted'])],
            'contract_type' => ['nullable', 'string', 'max:96'],
            'description' => ['nullable', 'string', 'max:1000'],
            'occurred_on' => ['nullable', 'date'],
        ];
    }
}
