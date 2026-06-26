<?php

namespace App\Http\Requests\Partner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePartnerOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'stock_quantity' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'point_cost' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'availability_status' => ['required', 'string', Rule::in(['active', 'paused'])],
            'available_from' => ['nullable', 'date'],
            'available_until' => ['nullable', 'date', 'after_or_equal:available_from'],
            'description' => ['nullable', 'string', 'max:1000'],
            'terms' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
