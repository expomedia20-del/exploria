<?php

namespace App\Http\Requests\Admin;

use App\Enums\RecordStatus;
use App\Models\Treasure;
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
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $campaignId = $this->string('campaign_id')->toString();
                    $cycleStepIndex = $this->integer('cycle_step_index');

                    $conflict = Treasure::query()
                        ->where('campaign_id', $campaignId)
                        ->where('code', $value)
                        ->get(['metadata'])
                        ->contains(fn (Treasure $treasure): bool => (int) ($treasure->metadata['cycle_step_index'] ?? 0) !== $cycleStepIndex);

                    if ($conflict) {
                        $fail('کد گنج برای همین کمپین قبلا در گام دیگری استفاده شده است.');
                    }
                },
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
