<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ActivateSponsorProposalRequest extends FormRequest
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
            'activation_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
