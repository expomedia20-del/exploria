<?php

namespace App\Http\Requests\Admin;

use App\Enums\RecordStatus;
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
                Rule::unique('reward_definitions', 'code')->where('campaign_id', $this->string('campaign_id')->toString()),
            ],
            'name' => ['required', 'string', 'max:255'],
            'reward_type' => ['required', 'string', 'max:64', 'alpha_dash:ascii'],
            'point_cost' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'stock_quantity' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'status' => ['required', Rule::enum(RecordStatus::class)],
            'description' => ['nullable', 'string', 'max:1000'],
            'terms' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
