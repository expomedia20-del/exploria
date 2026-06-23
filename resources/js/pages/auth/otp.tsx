import { Head } from '@inertiajs/react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

type Step = 'mobile' | 'code' | 'success';

type ApiResponse = {
    message?: string;
    data?: { otpRequestId?: string };
    errors?: Record<string, string[]>;
};

async function postJson(url: string, body: Record<string, string | null>) {
    const csrfToken = document
        .querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
        ?.getAttribute('content');

    const response = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken ?? '',
        },
        body: JSON.stringify(body),
    });

    const payload = (await response.json()) as ApiResponse;

    if (!response.ok) {
        throw new Error(
            payload.errors?.mobile?.[0] ??
                payload.errors?.code?.[0] ??
                payload.message ??
                'خطایی رخ داد. لطفاً دوباره تلاش کنید.',
        );
    }

    return payload;
}

export default function OtpAccess() {
    const [step, setStep] = useState<Step>('mobile');
    const [mobile, setMobile] = useState('');
    const [code, setCode] = useState('');
    const [otpRequestId, setOtpRequestId] = useState('');
    const [processing, setProcessing] = useState(false);
    const [error, setError] = useState('');

    const sourceQrCode =
        typeof window === 'undefined'
            ? null
            : new URLSearchParams(window.location.search).get('sourceQrCode');

    async function requestOtp(event: FormEvent) {
        event.preventDefault();
        setProcessing(true);
        setError('');

        try {
            const response = await postJson('/api/v1/auth/otp/request', {
                mobile,
                sourceQrCode,
            });

            setOtpRequestId(response.data?.otpRequestId ?? '');
            setStep('code');
        } catch (requestError) {
            setError(
                requestError instanceof Error
                    ? requestError.message
                    : 'خطایی رخ داد. لطفاً دوباره تلاش کنید.',
            );
        } finally {
            setProcessing(false);
        }
    }

    async function verifyOtp(event: FormEvent) {
        event.preventDefault();
        setProcessing(true);
        setError('');

        try {
            await postJson('/api/v1/auth/otp/verify', {
                otpRequestId,
                code,
            });
            setStep('success');
        } catch (verifyError) {
            setError(
                verifyError instanceof Error
                    ? verifyError.message
                    : 'خطایی رخ داد. لطفاً دوباره تلاش کنید.',
            );
        } finally {
            setProcessing(false);
        }
    }

    return (
        <main className="flex min-h-screen items-center justify-center bg-slate-50 px-4 py-10 text-slate-950 dark:bg-slate-950 dark:text-slate-50">
            <Head title="ورود به اکسپلوریا" />

            <section className="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 dark:border-slate-800 dark:bg-slate-900">
                <div className="mb-8 text-center">
                    <p className="mb-2 text-sm font-medium text-blue-700 dark:text-blue-300">
                        پایلوت اکسپلوریا
                    </p>
                    <h1 className="text-2xl font-bold">ورود سریع با موبایل</h1>
                    <p className="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-300">
                        برای ادامه تجربه مکان و ثبت بازدید، شماره موبایل خود را
                        وارد کنید.
                    </p>
                </div>

                {step === 'mobile' && (
                    <form onSubmit={requestOtp} className="space-y-5">
                        <div className="space-y-2">
                            <Label htmlFor="mobile">شماره موبایل</Label>
                            <Input
                                id="mobile"
                                inputMode="numeric"
                                autoComplete="tel"
                                dir="ltr"
                                value={mobile}
                                onChange={(event) =>
                                    setMobile(event.target.value)
                                }
                                placeholder="09120000000"
                                maxLength={11}
                                required
                                autoFocus
                                className="text-left"
                            />
                        </div>
                        <InputError message={error} />
                        <Button className="h-11 w-full" disabled={processing}>
                            {processing && <Spinner />}
                            {processing ? 'در حال ارسال…' : 'دریافت کد تأیید'}
                        </Button>
                    </form>
                )}

                {step === 'code' && (
                    <form onSubmit={verifyOtp} className="space-y-5">
                        <div className="space-y-2">
                            <Label htmlFor="code">کد تأیید شش‌رقمی</Label>
                            <Input
                                id="code"
                                inputMode="numeric"
                                autoComplete="one-time-code"
                                dir="ltr"
                                value={code}
                                onChange={(event) =>
                                    setCode(event.target.value)
                                }
                                placeholder="123456"
                                maxLength={6}
                                required
                                autoFocus
                                className="text-center text-lg tracking-[0.5em]"
                            />
                            <p className="text-xs text-slate-500">
                                کد آزمایشی فقط در محیط محلی: ۱۲۳۴۵۶
                            </p>
                        </div>
                        <InputError message={error} />
                        <Button className="h-11 w-full" disabled={processing}>
                            {processing && <Spinner />}
                            {processing ? 'در حال بررسی…' : 'تأیید و ادامه'}
                        </Button>
                        <Button
                            type="button"
                            variant="ghost"
                            className="w-full"
                            onClick={() => {
                                setStep('mobile');
                                setCode('');
                                setError('');
                            }}
                        >
                            اصلاح شماره موبایل
                        </Button>
                    </form>
                )}

                {step === 'success' && (
                    <div className="rounded-2xl bg-emerald-50 p-5 text-center dark:bg-emerald-950/40">
                        <h2 className="font-bold text-emerald-800 dark:text-emerald-200">
                            ورود با موفقیت انجام شد
                        </h2>
                        <p className="mt-2 text-sm leading-6 text-emerald-700 dark:text-emerald-300">
                            مرحله بعدی، نمایش و ثبت رضایت‌نامه پایلوت است.
                        </p>
                        <Button
                            className="mt-5 w-full"
                            onClick={() => {
                                window.location.assign(
                                    sourceQrCode
                                        ? `/consent?sourceQrCode=${encodeURIComponent(sourceQrCode)}`
                                        : '/consent',
                                );
                            }}
                        >
                            مشاهده رضایت‌نامه
                        </Button>
                    </div>
                )}
            </section>
        </main>
    );
}
