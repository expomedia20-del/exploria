<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVenueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'nullable',
                'string',
                'max:64',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('venues', 'code'),
            ],
            'city' => ['nullable', 'string', 'max:120'],
            'venue_type' => ['required', 'string', 'max:64'],
            'primary_audience' => ['nullable', 'string', 'max:255'],
            'official_website_url' => ['nullable', 'url', 'max:500'],
        ];
    }
}
