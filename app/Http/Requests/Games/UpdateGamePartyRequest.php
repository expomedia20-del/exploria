<?php

namespace App\Http\Requests\Games;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGamePartyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'mode' => ['required', Rule::in(['individual', 'family', 'team'])],
            'name' => ['nullable', 'required_unless:mode,individual', 'string', 'min:2', 'max:80'],
            'companion_count' => ['nullable', 'required_if:mode,family', 'integer', 'min:1', 'max:7'],
        ];
    }
}
