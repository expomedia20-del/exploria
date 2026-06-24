<?php

namespace App\Http\Requests\Consent;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;

class AcceptConsentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, list<string|In>> */
    public function rules(): array
    {
        return [
            'consentVersionId' => ['required', 'uuid', 'exists:consent_versions,id'],
            'source' => ['nullable', Rule::in(['pwa', 'qr_landing'])],
            'venueId' => ['nullable', 'uuid'],
            'sourceQrCode' => ['nullable', 'string', 'max:128', 'exists:qr_codes,code'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'consentVersionId.required' => 'نسخه رضایت‌نامه مشخص نشده است.',
            'consentVersionId.exists' => 'نسخه رضایت‌نامه معتبر نیست.',
            'source.in' => 'منبع ثبت رضایت معتبر نیست.',
        ];
    }
}
