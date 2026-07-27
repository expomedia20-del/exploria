<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SuggestVenueFacilitiesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'source_text' => ['nullable', 'string', 'max:30000'],
            'official_website_url' => ['nullable', 'url', 'max:500'],
            'venue_type' => ['nullable', 'string', 'max:64'],
            'primary_audience' => ['nullable', 'string', 'max:255'],
        ];
    }
}
