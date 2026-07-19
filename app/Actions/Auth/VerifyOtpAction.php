<?php

namespace App\Actions\Auth;

use App\Actions\Events\RecordDomainEventAction;
use App\Enums\UserRole;
use App\Models\OtpRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VerifyOtpAction
{
    public function __construct(private readonly RecordDomainEventAction $recordEvent) {}

    public function execute(string $requestId, string $code): User
    {
        return DB::transaction(function () use ($requestId, $code): User {
            $otp = OtpRequest::query()->lockForUpdate()->find($requestId);

            if (! $otp || $otp->verified_at || $otp->expires_at->isPast() || $otp->attempts >= config('otp.max_attempts')) {
                throw ValidationException::withMessages(['code' => 'درخواست کد تأیید معتبر نیست یا منقضی شده است.']);
            }

            $otp->increment('attempts');

            if (! Hash::check($code, $otp->code_hash)) {
                throw ValidationException::withMessages(['code' => 'کد تأیید صحیح نیست.']);
            }

            $user = User::firstOrCreate(
                ['mobile_hash' => $otp->mobile_hash],
                [
                    'name' => 'کاربر اکسپلوریا',
                    'mobile' => $otp->mobile,
                    'email' => 'visitor-'.substr($otp->mobile_hash, 0, 24).'@local.invalid',
                    'password' => Str::password(32),
                    'role' => UserRole::Visitor,
                ],
            );

            $otp->update(['verified_at' => now()]);
            Auth::login($user);
            request()->session()->regenerate();

            $this->recordEvent->execute('otp_verified', $user, request()->session()->getId(), 'otp_request', $otp->id, [
                'mobile_hash' => $otp->mobile_hash,
                'result' => 'verified',
            ]);

            return $user;
        });
    }
}
