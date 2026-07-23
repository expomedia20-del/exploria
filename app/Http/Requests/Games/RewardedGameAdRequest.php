<?php

namespace App\Http\Requests\Games;

use Illuminate\Foundation\Http\FormRequest;

class RewardedGameAdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['ad_request_id' => ['required', 'uuid', 'exists:ad_requests,id']];
    }
}
