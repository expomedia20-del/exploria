<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\RequestOtpAction;
use App\Actions\Auth\VerifyOtpAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RequestOtpRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use Illuminate\Http\JsonResponse;

class OtpController extends Controller
{
    public function request(RequestOtpRequest $request, RequestOtpAction $action): JsonResponse
    {
        $otp = $action->execute($request->string('mobile')->toString(), $request->input('sourceQrCode'));

        return response()->json(['status' => 'success', 'message' => 'کد تأیید ارسال شد.', 'data' => ['otpRequestId' => $otp->id, 'expiresAt' => $otp->expires_at->toIso8601String(), 'status' => 'pending']]);
    }

    public function verify(VerifyOtpRequest $request, VerifyOtpAction $action): JsonResponse
    {
        $user = $action->execute($request->string('otpRequestId')->toString(), $request->string('code')->toString());

        return response()->json(['status' => 'success', 'message' => 'ورود با موفقیت انجام شد.', 'data' => ['userId' => $user->id, 'consentRequired' => true]]);
    }
}
