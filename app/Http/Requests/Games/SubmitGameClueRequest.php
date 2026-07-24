<?php

namespace App\Http\Requests\Games;

use Illuminate\Foundation\Http\FormRequest;

class SubmitGameClueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['answer_key' => ['required', 'string', 'size:3', 'regex:/^[۰-۹٠-٩0-9]{3}$/u']];
    }
}
