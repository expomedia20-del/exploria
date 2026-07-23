<?php

namespace App\Http\Requests\Games;

use Illuminate\Foundation\Http\FormRequest;

class JoinGamePartyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['invite_code' => ['required', 'string', 'size:6', 'alpha_num:ascii']];
    }
}
