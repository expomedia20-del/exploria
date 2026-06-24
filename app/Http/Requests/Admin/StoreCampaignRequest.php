<?php

namespace App\Http\Requests\Admin;

use App\Enums\RecordStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'venue_id' => ['required', 'uuid', 'exists:venues,id'],
            'code' => [
                'required',
                'string',
                'max:64',
                'alpha_dash:ascii',
                Rule::unique('campaigns', 'code')->where('venue_id', $this->string('venue_id')->toString()),
            ],
            'name' => ['required', 'string', 'max:255'],
            'campaign_type' => ['required', 'string', 'max:64', 'alpha_dash:ascii'],
            'status' => ['required', Rule::enum(RecordStatus::class)],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
        ];
    }
}
