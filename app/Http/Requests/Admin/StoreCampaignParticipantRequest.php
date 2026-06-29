<?php

namespace App\Http\Requests\Admin;

use App\Enums\RecordStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCampaignParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'participant_id' => ['nullable', 'uuid', 'exists:campaign_participants,id'],
            'campaign_id' => ['required', 'uuid', 'exists:campaigns,id'],
            'hub_id' => ['nullable', 'uuid', 'exists:hubs,id'],
            'partner_account_id' => ['nullable', 'uuid', 'exists:partner_accounts,id'],
            'participant_type' => ['required', 'string', Rule::in(['member_shop', 'sponsor', 'external_brand', 'hub_subunit'])],
            'participation_role' => ['required', 'string', Rule::in(['reward_redemption', 'commercial_activation', 'route_sponsor', 'content_partner', 'display_partner'])],
            'status' => ['required', Rule::enum(RecordStatus::class)],
            'onboarding_status' => ['required', 'string', Rule::in(['invited', 'ready', 'pending_review', 'paused'])],
            'joined_at' => ['nullable', 'date'],
            'connections_rewards' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'connections_ads' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'connections_qr_codes' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'connections_missions' => ['nullable', 'integer', 'min:0', 'max:1000000'],
        ];
    }
}
