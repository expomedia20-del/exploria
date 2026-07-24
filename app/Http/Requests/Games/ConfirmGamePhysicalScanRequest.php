<?php

namespace App\Http\Requests\Games;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmGamePhysicalScanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['qr_code' => $this->route('code')]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'qr_code' => ['required', 'string', 'max:96', 'regex:/^[A-Za-z0-9-]+$/', 'exists:qr_codes,code'],
        ];
    }
}
