<?php

namespace App\Http\Requests\Admin;

use App\Enums\RecordStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMissionInstanceRequest extends FormRequest
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
            'mission_template_id' => ['required', 'uuid', 'exists:mission_templates,id'],
            'hub_id' => ['nullable', 'uuid', 'exists:hubs,id'],
            'touchpoint_id' => ['nullable', 'uuid', 'exists:touchpoints,id'],
            'code' => [
                'required',
                'string',
                'max:96',
                'alpha_dash:ascii',
                Rule::unique('mission_instances', 'code')->where('campaign_id', $this->string('campaign_id')->toString()),
            ],
            'title_override' => ['nullable', 'string', 'max:255'],
            'cycle_step_index' => ['nullable', 'integer', 'min:1', 'max:20'],
            'cycle_step_label' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(RecordStatus::class)],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'unlock_min_points' => ['nullable', 'integer', 'min:0', 'max:1000000'],
        ];
    }
}
