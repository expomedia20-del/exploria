<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ScanEventIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            'result' => ['nullable', Rule::in(['accepted', 'invalid', 'duplicate'])],
            'event_type' => ['nullable', 'string', 'max:128'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'result.in' => 'فیلتر نتیجه اسکن معتبر نیست.',
            'to.after_or_equal' => 'پایان بازه نمی‌تواند قبل از شروع آن باشد.',
        ];
    }
}
