<?php

namespace App\Http\Requests\Consent;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AcceptConsentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'consentVersionId' => ['required', 'uuid', 'exists:consent_versions,id'],
            'source' => ['nullable', Rule::in(['pwa', 'qr_landing'])],
            'venueId' => ['nullable', 'uuid'],
        ];
    }

    public function messages(): array
    {
        return [
            'consentVersionId.required' => 'نسخه رضایت‌نامه مشخص نشده است.',
            'consentVersionId.exists' => 'نسخه رضایت‌نامه معتبر نیست.',
            'source.in' => 'منبع ثبت رضایت معتبر نیست.',
        ];
    }
}
