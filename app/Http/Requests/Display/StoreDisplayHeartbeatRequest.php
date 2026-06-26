<?php

namespace App\Http\Requests\Display;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDisplayHeartbeatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'playback_status' => ['required', 'string', Rule::in(['online', 'idle', 'playing', 'error'])],
            'current_slot' => ['nullable', 'string', 'max:128'],
            'current_ad_request_id' => ['nullable', 'uuid', 'exists:ad_requests,id'],
            'current_placement_id' => ['nullable', 'uuid', 'exists:ad_placements,id'],
            'last_playback_result' => ['nullable', 'string', Rule::in(['ok', 'skipped', 'failed'])],
            'last_playback_error' => ['nullable', 'string', 'max:2000'],
            'reported_at' => ['nullable', 'date'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
