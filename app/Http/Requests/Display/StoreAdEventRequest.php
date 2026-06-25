<?php

namespace App\Http\Requests\Display;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdEventRequest extends FormRequest
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
            'event_type' => ['required', 'string', Rule::in(['impression', 'click', 'playback_start', 'playback_complete', 'scan'])],
            'occurred_at' => ['nullable', 'date'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
