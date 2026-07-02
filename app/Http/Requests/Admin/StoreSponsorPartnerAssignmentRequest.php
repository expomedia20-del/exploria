<?php

namespace App\Http\Requests\Admin;

use App\Enums\RecordStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSponsorPartnerAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'assignment_id' => ['nullable', 'uuid', 'exists:sponsor_partner_assignments,id'],
            'sponsor_account_id' => ['required', 'uuid', 'exists:sponsor_accounts,id'],
            'partner_account_id' => ['required', 'uuid', 'exists:partner_accounts,id'],
            'campaign_id' => ['nullable', 'uuid', 'exists:campaigns,id'],
            'activation_role' => ['required', 'string', Rule::in(['sales_point', 'reward_redemption', 'challenge_host', 'discount_redemption', 'product_sampling', 'content_delivery'])],
            'status' => ['required', Rule::enum(RecordStatus::class)],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
