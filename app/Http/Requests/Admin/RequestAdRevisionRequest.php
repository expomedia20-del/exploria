<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RequestAdRevisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'notes' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'notes.required' => 'دلیل و موارد لازم برای اصلاح تبلیغ را بنویسید.',
            'notes.min' => 'توضیح اصلاح باید دست‌کم ۵ نویسه داشته باشد.',
        ];
    }
}
