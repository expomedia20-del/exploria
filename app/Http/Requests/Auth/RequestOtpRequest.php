<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RequestOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['mobile' => ['required', 'string', 'regex:/^09\d{9}$/'], 'sourceQrCode' => ['nullable', 'string', 'max:100']];
    }

    public function messages(): array
    {
        return ['mobile.required' => 'وارد کردن شماره موبایل الزامی است.', 'mobile.regex' => 'شماره موبایل معتبر نیست.'];
    }
}
