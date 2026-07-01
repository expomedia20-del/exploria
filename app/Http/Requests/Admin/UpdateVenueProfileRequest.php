<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVenueProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'venue_type' => ['required', 'string', 'max:64'],
            'primary_audience' => ['nullable', 'string', 'max:255'],
            'official_website_url' => ['nullable', 'url', 'max:500'],
            'manual_research_notes' => ['nullable', 'string', 'max:4000'],
            'facilities_text' => ['nullable', 'string', 'max:6000'],
            'constraints_text' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
