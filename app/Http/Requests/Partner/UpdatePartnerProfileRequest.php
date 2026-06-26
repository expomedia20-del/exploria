<?php

namespace App\Http\Requests\Partner;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePartnerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'contact_name' => ['nullable', 'string', 'max:160'],
            'contact_mobile' => ['nullable', 'string', 'max:32'],
            'category' => ['nullable', 'string', 'max:80'],
            'operating_notes' => ['nullable', 'string', 'max:1000'],
            'display_visibility' => ['required', 'boolean'],
        ];
    }
}
