<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class PauseCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, [UserRole::Admin, UserRole::Operator], true);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
            'incident_reference' => ['required', 'string', 'min:3', 'max:128'],
        ];
    }

    /** @return array{reason: string, incident_reference: string} */
    public function operationalData(): array
    {
        return [
            'reason' => $this->string('reason')->toString(),
            'incident_reference' => $this->string('incident_reference')->toString(),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'reason.required' => 'دلیل توقف باید ثبت شود.',
            'reason.min' => 'دلیل توقف باید حداقل ۱۰ نویسه داشته باشد.',
            'incident_reference.required' => 'مرجع رخداد یا تیکت برای توقف الزامی است.',
        ];
    }
}
