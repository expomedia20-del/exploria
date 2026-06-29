<?php

namespace App\Http\Requests\Admin;

use App\Enums\RecordStatus;
use App\Models\QrCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQrCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'qr_code_id' => ['nullable', 'uuid', 'exists:qr_codes,id'],
            'code' => [
                'required',
                'string',
                'max:128',
                'alpha_dash:ascii',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $qrCodeId = $this->string('qr_code_id')->toString();

                    $exists = QrCode::query()
                        ->where('code', $value)
                        ->when($qrCodeId !== '', fn ($query) => $query->whereKeyNot($qrCodeId))
                        ->exists();

                    if ($exists) {
                        $fail('کد QR قبلا استفاده شده است.');
                    }
                },
            ],
            'venue_id' => ['required', 'uuid', 'exists:venues,id'],
            'touchpoint_id' => ['required', 'uuid', 'exists:touchpoints,id'],
            'campaign_id' => ['required', 'uuid', 'exists:campaigns,id'],
            'label' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(RecordStatus::class)],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'max_scans_per_user_per_window' => ['required', 'integer', 'min:1', 'max:1000'],
            'duplicate_window_seconds' => ['required', 'integer', 'min:30', 'max:86400'],
        ];
    }
}
