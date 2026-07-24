<?php

namespace App\Http\Requests\Games;

use Illuminate\Foundation\Http\FormRequest;

class InviteGamePartyMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'mobile' => ['required', 'string', 'regex:/^09\d{9}$/'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'mobile.required' => 'شماره موبایل عضو موردنظر را وارد کنید.',
            'mobile.regex' => 'شماره موبایل معتبر نیست.',
        ];
    }
}
