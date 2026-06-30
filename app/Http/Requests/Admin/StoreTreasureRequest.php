<?php

namespace App\Http\Requests\Admin;

use App\Enums\RecordStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTreasureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'campaign_id' => ['required', 'uuid', 'exists:campaigns,id'],
            'mission_instance_id' => ['nullable', 'uuid', 'exists:mission_instances,id'],
            'code' => [
                'required',
                'string',
                'max:96',
                'alpha_dash:ascii',
                Rule::unique('treasures', 'code')->where('campaign_id', $this->string('campaign_id')->toString()),
            ],
            'name' => ['required', 'string', 'max:255'],
            'treasure_type' => ['required', 'string', 'max:64', 'alpha_dash:ascii'],
            'treasure_tier' => ['nullable', 'string', 'max:64', 'alpha_dash:ascii'],
            'cycle_step_index' => ['nullable', 'integer', 'min:1', 'max:50'],
            'cycle_step_label' => ['nullable', 'string', 'max:255'],
            'reveal_mode' => ['nullable', 'string', 'max:64', 'alpha_dash:ascii'],
            'reveal_description' => ['nullable', 'string', 'max:1000'],
            'discovery_hint' => ['nullable', 'string', 'max:500'],
            'required_min_points' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'status' => ['required', Rule::enum(RecordStatus::class)],
            'required_completed_missions' => ['nullable', 'integer', 'min:0', 'max:1000'],
        ];
    }
}
