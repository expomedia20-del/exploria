<?php

namespace App\Http\Requests\Partner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ConfirmRewardRedemptionRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $redemptionCode = $this->input('redemption_code');
        $purchaseStatus = $this->input('purchase_status');
        $receiptReference = $this->input('receipt_reference');

        if (is_string($redemptionCode) || is_numeric($redemptionCode)) {
            $this->merge([
                'redemption_code' => Str::upper(trim((string) $redemptionCode)),
            ]);
        }

        $this->merge([
            'purchase_status' => is_string($purchaseStatus)
                ? Str::lower(trim($purchaseStatus))
                : 'reward_only',
            'receipt_reference' => is_string($receiptReference)
                ? trim($receiptReference)
                : null,
        ]);
    }

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'redemption_code' => ['required', 'string', 'max:64'],
            'purchase_status' => ['required', 'string', Rule::in(['reward_only', 'purchase_confirmed'])],
            'purchase_amount' => ['nullable', 'integer', 'min:1', 'max:999999999999', 'required_if:purchase_status,purchase_confirmed'],
            'receipt_reference' => ['nullable', 'string', 'max:100'],
        ];
    }
}
