<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCampaignRouteReviewRequest extends FormRequest
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
            'route_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
