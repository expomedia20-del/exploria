<?php

namespace App\Http\Requests\Games;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitGameClueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['answer_key' => ['required', Rule::in(['light', 'tree', 'clock', 'together', 'faster', 'silent', 'story', 'wall', 'exit'])]];
    }
}
