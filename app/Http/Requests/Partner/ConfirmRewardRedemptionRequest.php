<?php

namespace App\Http\Requests\Partner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class ConfirmRewardRedemptionRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $redemptionCode = $this->input('redemption_code');

        if (is_string($redemptionCode) || is_numeric($redemptionCode)) {
            $this->merge([
                'redemption_code' => Str::upper(trim((string) $redemptionCode)),
            ]);
        }
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
        ];
    }
}
