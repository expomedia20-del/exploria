<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return ['otpRequestId' => ['required', 'uuid'], 'code' => ['required', 'digits:6'], 'deviceId' => ['nullable', 'string', 'max:100']];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['otpRequestId.required' => 'شناسه درخواست الزامی است.', 'code.required' => 'وارد کردن کد تأیید الزامی است.', 'code.digits' => 'کد تأیید باید شش رقم باشد.'];
    }
}
