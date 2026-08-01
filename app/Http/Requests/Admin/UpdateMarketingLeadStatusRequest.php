<?php

namespace App\Http\Requests\Admin;

use App\Services\MarketingLeadService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMarketingLeadStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(MarketingLeadService::STATUSES)],
            'internal_notes' => ['nullable', 'string', 'max:1200'],
        ];
    }

    /** @return array{status: string, internal_notes: string|null} */
    public function payload(): array
    {
        $validated = $this->validated();

        return [
            'status' => (string) $validated['status'],
            'internal_notes' => isset($validated['internal_notes'])
                ? (string) $validated['internal_notes']
                : null,
        ];
    }
}
