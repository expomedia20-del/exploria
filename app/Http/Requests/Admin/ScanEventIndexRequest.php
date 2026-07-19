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
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['result.in' => 'فیلتر نتیجه اسکن معتبر نیست.'];
    }
}
