<?php

namespace App\Http\Requests\Admin;

use App\Enums\RecordStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSponsorAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'sponsor_id' => ['nullable', 'uuid', 'exists:sponsor_accounts,id'],
            'venue_id' => ['nullable', 'uuid', 'exists:venues,id'],
            'code' => ['required', 'string', 'max:96', 'alpha_dash:ascii', Rule::unique('sponsor_accounts', 'code')->ignore($this->string('sponsor_id')->toString())],
            'name' => ['required', 'string', 'max:255'],
            'sponsor_type' => ['required', 'string', Rule::in(['brand', 'cultural', 'scientific', 'retail', 'institutional'])],
            'status' => ['required', Rule::enum(RecordStatus::class)],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_mobile' => ['nullable', 'string', 'max:32'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
