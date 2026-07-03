<?php

namespace App\Http\Requests\Admin;

use App\Enums\RecordStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignSponsorIncentiveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'mission_instance_id' => ['required', 'uuid', 'exists:mission_instances,id'],
            'treasure_id' => ['nullable', 'uuid', 'exists:treasures,id'],
            'reward_tier' => ['required', 'string', 'max:64', 'alpha_dash:ascii'],
            'reward_option' => ['nullable', 'string', 'max:255'],
            'claim_condition' => [
                'required',
                'string',
                Rule::in([
                    'mission_completion',
                    'treasure_discovery',
                    'qr_scan',
                    'purchase_validation',
                    'family_team_completion',
                    'referral_activation',
                    'manual_admin',
                ]),
            ],
            'point_cost' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'status' => ['required', Rule::enum(RecordStatus::class)],
            'availability_status' => ['required', 'string', Rule::in(['active', 'paused', 'pending_campaign_assignment'])],
            'fulfillment_window' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
