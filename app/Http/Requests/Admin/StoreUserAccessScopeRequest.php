<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserAccessScopeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'role_key' => ['required', 'string', Rule::in(array_keys(config('exploria_roles.roles', [])))],
            'scope_type' => ['required', 'string', Rule::in(config('exploria_roles.scope_types', []))],
            'scope_id' => ['nullable', 'string', 'max:64'],
        ];
    }
}
