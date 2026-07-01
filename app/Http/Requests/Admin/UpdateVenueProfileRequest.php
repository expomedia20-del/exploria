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
            'facilities_text' => ['nullable', 'string', 'max:20000'],
            'facilities_file' => ['nullable', 'file', 'max:2048', 'extensions:csv,txt,tsv'],
            'facilities' => ['nullable', 'array', 'max:250'],
            'facilities.*.name' => ['nullable', 'string', 'max:120'],
            'facilities.*.function' => ['nullable', 'string', 'max:120'],
            'facilities.*.campaign_uses' => ['nullable', 'array', 'max:8'],
            'facilities.*.campaign_uses.*' => ['string', 'max:64'],
            'facilities.*.priority' => ['nullable', 'string', 'in:primary,secondary,low'],
            'facilities.*.notes' => ['nullable', 'string', 'max:500'],
            'constraints_text' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
