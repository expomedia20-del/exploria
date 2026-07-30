<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMarketingLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'audience_type' => [
                'required',
                'string',
                Rule::in(['venue', 'commercial_unit', 'sponsor', 'visitor_growth', 'other']),
            ],
            'organization_name' => ['nullable', 'string', 'max:255'],
            'contact_name' => ['required', 'string', 'max:120'],
            'mobile' => ['required', 'string', 'max:32', 'regex:/^[0-9+()\-\s]{8,32}$/'],
            'city' => ['nullable', 'string', 'max:120'],
            'project_hint' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1200'],
            'source_path' => ['nullable', 'string', 'max:255'],
            'company_url' => ['nullable', 'prohibited'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'audience_type.required' => 'نوع همکاری را انتخاب کنید.',
            'audience_type.in' => 'نوع همکاری انتخاب‌شده معتبر نیست.',
            'contact_name.required' => 'نام مسئول پیگیری را وارد کنید.',
            'mobile.required' => 'شماره تماس را وارد کنید.',
            'mobile.regex' => 'شماره تماس را با رقم، فاصله یا علامت + وارد کنید.',
            'company_url.prohibited' => 'درخواست ثبت نشد. دوباره فرم را ارسال کنید.',
        ];
    }
}
