<?php

namespace App\Http\Controllers;

use App\Actions\Consent\AcceptConsentAction;
use App\Actions\Visits\RecordVisitAction;
use App\Http\Requests\Consent\AcceptConsentRequest;
use App\Models\ConsentVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConsentController extends Controller
{
    public function current(Request $request): JsonResponse
    {
        $language = $request->string('language', 'fa')->toString();
        $version = ConsentVersion::query()
            ->where('language', $language)
            ->where('is_active', true)
            ->latest('published_at')
            ->first();

        if (! $version) {
            return response()->json([
                'status' => 'error',
                'message' => 'رضایت‌نامه فعالی برای نمایش وجود ندارد.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                ...$version->only(['id', 'version', 'language', 'title', 'body', 'is_demo']),
                'accepted' => $request->user()
                    ? $request->user()->consentLogs()
                        ->where('consent_version_id', $version->id)
                        ->exists()
                    : false,
            ],
        ]);
    }

    public function accept(
        AcceptConsentRequest $request,
        AcceptConsentAction $acceptConsent,
        RecordVisitAction $recordVisit,
    ): JsonResponse {
        $log = $acceptConsent->execute(
            $request->user(),
            $request->string('consentVersionId')->toString(),
            $request->session()->getId(),
            $request->string('source', 'pwa')->toString(),
            $request->input('venueId'),
        );
        $sourceQrCode = $request->string('sourceQrCode')->toString();
        $visit = $sourceQrCode !== ''
            ? $recordVisit->execute($request->user(), $sourceQrCode, $log, $request->session()->getId())
            : null;

        return response()->json([
            'status' => 'success',
            'message' => 'رضایت شما با موفقیت ثبت شد.',
            'data' => [
                'id' => $log->id,
                'consentVersionId' => $log->consent_version_id,
                'acceptedAt' => $log->accepted_at->toIso8601String(),
                'visitId' => $visit?->id,
                'nextUrl' => $visit ? route('visits.show', $visit) : route('dashboard'),
            ],
        ], 201);
    }
}
