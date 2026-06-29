<?php

namespace App\Http\Requests\Admin;

use App\Enums\RecordStatus;
use App\Models\RewardDefinition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRewardDefinitionRequest extends FormRequest
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
            'partner_account_id' => ['nullable', 'uuid', 'exists:partner_accounts,id'],
            'code' => [
                'required',
                'string',
                'max:96',
                'alpha_dash:ascii',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $campaignId = $this->string('campaign_id')->toString();
                    $cycleStepIndex = $this->integer('cycle_step_index');

                    $conflict = RewardDefinition::query()
                        ->where('campaign_id', $campaignId)
                        ->where('code', $value)
                        ->get(['metadata'])
                        ->contains(fn (RewardDefinition $reward): bool => (int) ($reward->metadata['cycle_step_index'] ?? 0) !== $cycleStepIndex);

                    if ($conflict) {
                        $fail('کد پاداش برای همین کمپین قبلا در گام دیگری استفاده شده است.');
                    }
                },
            ],
            'name' => ['required', 'string', 'max:255'],
            'reward_type' => ['required', 'string', 'max:64', 'alpha_dash:ascii'],
            'reward_tier' => ['nullable', 'string', 'max:64', 'alpha_dash:ascii'],
            'reward_option' => ['nullable', 'string', 'max:255'],
            'cycle_step_index' => ['nullable', 'integer', 'min:1', 'max:50'],
            'cycle_step_label' => ['nullable', 'string', 'max:255'],
            'point_cost' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'stock_quantity' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'status' => ['required', Rule::enum(RecordStatus::class)],
            'available_from' => ['nullable', 'date'],
            'available_until' => ['nullable', 'date', 'after_or_equal:available_from'],
            'fulfillment_window' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'terms' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
