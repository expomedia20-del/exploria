<?php

namespace App\Http\Requests\Offers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGameOfferEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'ad_request_id' => ['required', 'uuid', 'exists:ad_requests,id'],
            'event_type' => ['required', 'string', Rule::in(['game_offer_view', 'game_offer_click', 'game_clue_complete'])],
            'mission_code' => ['nullable', 'string', 'max:96'],
            'choice' => ['nullable', 'string', 'max:160'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
