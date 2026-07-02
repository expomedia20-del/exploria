<?php

namespace App\Http\Requests\Sponsor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSponsorProposalRequest extends FormRequest
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
            'preferred_partner_account_id' => ['nullable', 'uuid', 'exists:partner_accounts,id'],
            'partner_account_ids' => ['nullable', 'array', 'max:20'],
            'partner_account_ids.*' => ['uuid', 'distinct', 'exists:partner_accounts,id'],
            'title' => ['required', 'string', 'max:255'],
            'proposal_type' => ['required', 'string', Rule::in(['campaign_sponsorship', 'reward_offer', 'discount_offer', 'display_media', 'family_challenge', 'scientific_cultural_content', 'product_sampling'])],
            'objective' => ['required', 'string', Rule::in(['awareness', 'footfall', 'lead_generation', 'sales', 'engagement', 'social_impact'])],
            'proposed_budget_amount' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
            'estimated_value_amount' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
            'items' => ['nullable', 'array', 'max:12'],
            'items.*.item_type' => ['required_with:items', 'string', Rule::in(['reward', 'discount', 'product', 'sample', 'media', 'content', 'cash_support'])],
            'items.*.title' => ['required_with:items', 'string', 'max:255'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'items.*.estimated_unit_value_amount' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
            'items.*.description' => ['nullable', 'string', 'max:2000'],
            'items.*.target_partner_account_ids' => ['nullable', 'array', 'max:20'],
            'items.*.target_partner_account_ids.*' => ['uuid', 'exists:partner_accounts,id'],
            'items.*.partner_allocations' => ['nullable', 'array', 'max:20'],
            'items.*.partner_allocations.*.partner_account_id' => ['nullable', 'uuid', 'exists:partner_accounts,id'],
            'items.*.partner_allocations.*.quantity' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'reward_offer' => ['nullable', 'string', 'max:2000'],
            'discount_offer' => ['nullable', 'string', 'max:2000'],
            'asset_url' => ['nullable', 'url', 'max:255'],
            'target_audience' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
