<?php

namespace App\Http\Requests\Games;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DiscoverGameHotspotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'hotspot_key' => ['required', Rule::in(['mina', 'nature', 'fire-water', 'book-garden', 'art-lake', 'taleghani'])],
            'member_id' => ['nullable', 'uuid'],
        ];
    }
}
