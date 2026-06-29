<?php

namespace App\Http\Requests\Admin;

use App\Enums\RecordStatus;
use App\Models\Campaign;
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
            'campaign_id' => ['nullable', 'uuid', 'exists:campaigns,id'],
            'venue_id' => ['required', 'uuid', 'exists:venues,id'],
            'code' => [
                'required',
                'string',
                'max:64',
                'alpha_dash:ascii',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $campaignId = $this->string('campaign_id')->toString();
                    $venueId = $this->string('venue_id')->toString();

                    $exists = Campaign::query()
                        ->where('venue_id', $venueId)
                        ->where('code', $value)
                        ->when($campaignId !== '', fn ($query) => $query->whereKeyNot($campaignId))
                        ->exists();

                    if ($exists) {
                        $fail('کد کمپین برای این مکان قبلا استفاده شده است.');
                    }
                },
            ],
            'name' => ['required', 'string', 'max:255'],
            'campaign_type' => ['required', 'string', 'max:64', 'alpha_dash:ascii'],
            'blueprint_code' => ['nullable', 'string', 'max:128', 'alpha_dash:ascii'],
            'status' => ['required', Rule::enum(RecordStatus::class)],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
        ];
    }
}
