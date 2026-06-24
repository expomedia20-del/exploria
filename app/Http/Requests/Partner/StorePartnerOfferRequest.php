<?php

namespace App\Http\Requests\Partner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePartnerOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'reward_type' => ['required', 'string', Rule::in(['partner_coupon', 'discount', 'gift', 'service_credit', 'sponsor_reward'])],
            'point_cost' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'stock_quantity' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'description' => ['nullable', 'string', 'max:1000'],
            'terms' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
