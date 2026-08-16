<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class ResumeCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::Admin;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'incident_reference' => ['required', 'string', 'min:3', 'max:128'],
            'corrective_action' => ['required', 'string', 'min:10', 'max:2000'],
            'recovery_evidence' => ['required', 'string', 'min:10', 'max:2000'],
            'approval_note' => ['required', 'string', 'min:10', 'max:1000'],
            'approval_confirmed' => ['accepted'],
        ];
    }

    /**
     * @return array{
     *     incident_reference: string,
     *     corrective_action: string,
     *     recovery_evidence: string,
     *     approval_note: string,
     *     approval_confirmed: bool
     * }
     */
    public function operationalData(): array
    {
        return [
            'incident_reference' => $this->string('incident_reference')->toString(),
            'corrective_action' => $this->string('corrective_action')->toString(),
            'recovery_evidence' => $this->string('recovery_evidence')->toString(),
            'approval_note' => $this->string('approval_note')->toString(),
            'approval_confirmed' => $this->boolean('approval_confirmed'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'incident_reference.required' => 'مرجع رخداد توقف الزامی است.',
            'corrective_action.required' => 'اقدام اصلاحی باید ثبت شود.',
            'corrective_action.min' => 'اقدام اصلاحی باید حداقل ۱۰ نویسه داشته باشد.',
            'recovery_evidence.required' => 'شاهد بازیابی یا نتیجه Smoke Test باید ثبت شود.',
            'recovery_evidence.min' => 'شاهد بازیابی باید حداقل ۱۰ نویسه داشته باشد.',
            'approval_note.required' => 'یادداشت تأیید ازسرگیری الزامی است.',
            'approval_confirmed.accepted' => 'تأیید صریح مسئول مجاز برای ازسرگیری الزامی است.',
        ];
    }
}
