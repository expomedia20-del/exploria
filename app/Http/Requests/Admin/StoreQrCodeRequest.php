<?php

namespace App\Http\Requests\Admin;

use App\Enums\RecordStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQrCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:128', 'alpha_dash:ascii', 'unique:qr_codes,code'],
            'venue_id' => ['required', 'uuid', 'exists:venues,id'],
            'touchpoint_id' => ['required', 'uuid', 'exists:touchpoints,id'],
            'campaign_id' => ['required', 'uuid', 'exists:campaigns,id'],
            'label' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(RecordStatus::class)],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'max_scans_per_user_per_window' => ['required', 'integer', 'min:1', 'max:1000'],
            'duplicate_window_seconds' => ['required', 'integer', 'min:30', 'max:86400'],
        ];
    }
}
