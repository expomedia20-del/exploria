<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDemoCycleChecklistItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'item_key' => [
                'required',
                'string',
                Rule::in([
                    'venue_route',
                    'campaign_blueprint',
                    'qr_entry',
                    'mission_reward_inventory',
                    'stress_data',
                    'role_briefing',
                    'visitor_execution',
                    'reward_redemption',
                    'roi_report',
                    'sponsor_media_evidence',
                    'sales_package',
                    'scope_guardrail',
                ]),
            ],
            'status' => ['required', 'string', Rule::in(['done', 'needs_action', 'blocked'])],
            'owner_name' => ['nullable', 'string', 'max:160'],
            'note' => ['nullable', 'string', 'max:1000'],
            'due_date' => ['nullable', 'date'],
        ];
    }
}
