<?php

namespace App\Http\Requests\Partner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'body_copy' => ['nullable', 'string', 'max:1200'],
            'cta_text' => ['nullable', 'string', 'max:80'],
            'target_url' => ['nullable', 'url', 'max:2048'],
            'hub_id' => ['nullable', 'uuid'],
            'ad_type' => ['required', 'string', Rule::in(['standalone', 'sponsor_message', 'display_takeover', 'route_sponsor', 'reward_moment'])],
            'creative_type' => ['required', 'string', Rule::in(['image', 'video', 'text_card', 'display_banner'])],
            'placement_type' => ['required', 'string', Rule::in(['fixed_display', 'mobile_display', 'qr_landing', 'reward_page', 'map_route', 'post_mission'])],
            'asset_url' => ['nullable', 'url', 'max:2048'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'budget_amount' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'impression_cap' => ['nullable', 'integer', 'min:1', 'max:1000000000'],
            'click_cap' => ['nullable', 'integer', 'min:1', 'max:1000000000'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:10'],
        ];
    }
}
