<?php

namespace App\Actions\Auth;

use App\Actions\Visits\RecordVisitAction;
use App\Actions\Visits\ResolvePostVisitDestinationAction;
use App\Models\ConsentVersion;
use App\Models\OtpRequest;
use App\Models\User;

class ResolvePostOtpDestinationAction
{
    public function __construct(
        private readonly RecordVisitAction $recordVisit,
        private readonly ResolvePostVisitDestinationAction $resolvePostVisitDestination,
    ) {}

    /** @return array{consentRequired: bool, nextUrl: string} */
    public function execute(
        User $user,
        string $otpRequestId,
        string $sessionId,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): array {
        $otp = OtpRequest::query()->findOrFail($otpRequestId);
        $sourceQrCode = $otp->source_qr_code;
        $consentVersion = ConsentVersion::query()
            ->where('language', 'fa')
            ->where('is_active', true)
            ->latest('published_at')
            ->first();
        $consentLog = $consentVersion
            ? $user->consentLogs()->where('consent_version_id', $consentVersion->id)->first()
            : null;

        if (! $consentLog) {
            return [
                'consentRequired' => true,
                'nextUrl' => route('visitor.consent', array_filter(['sourceQrCode' => $sourceQrCode])),
            ];
        }

        if ($sourceQrCode) {
            $visit = $this->recordVisit->execute($user, $sourceQrCode, $consentLog, $sessionId, $ipAddress, $userAgent);

            return [
                'consentRequired' => false,
                'nextUrl' => $this->resolvePostVisitDestination->execute($visit),
            ];
        }

        return [
            'consentRequired' => false,
            'nextUrl' => route('dashboard'),
        ];
    }
}
