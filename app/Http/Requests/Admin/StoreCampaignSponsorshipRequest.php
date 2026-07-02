<?php

namespace App\Http\Requests\Admin;

use App\Enums\RecordStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCampaignSponsorshipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'sponsorship_id' => ['nullable', 'uuid', 'exists:campaign_sponsorships,id'],
            'campaign_id' => ['required', 'uuid', 'exists:campaigns,id'],
            'sponsor_account_id' => ['required', 'uuid', 'exists:sponsor_accounts,id'],
            'sponsorship_goal' => ['required', 'string', Rule::in(['awareness', 'footfall', 'lead_generation', 'sales', 'engagement'])],
            'package_type' => ['required', 'string', Rule::in(['pilot_activation', 'display_media', 'treasure_sponsor', 'family_team_challenge', 'scientific_cultural_challenge'])],
            'status' => ['required', Rule::enum(RecordStatus::class)],
            'budget_amount' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
            'contract_value' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
