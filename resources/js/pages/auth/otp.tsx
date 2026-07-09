import { Head } from '@inertiajs/react';
import { MapPin, ShieldCheck, Sparkles } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import AppLogoIcon from '@/components/app-logo-icon';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

type Step = 'mobile' | 'code';

type ApiResponse = {
    message?: string;
    data?: {
        otpRequestId?: string;
        consentRequired?: boolean;
        nextUrl?: string;
    };
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
                'خطایی رخ داد. لطفا دوباره تلاش کنید.',
        );
    }

    return payload;
}

function consentUrl(sourceQrCode: string | null) {
    return sourceQrCode
        ? `/consent?sourceQrCode=${encodeURIComponent(sourceQrCode)}`
        : '/consent';
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
                    : 'خطایی رخ داد. لطفا دوباره تلاش کنید.',
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
            const response = await postJson('/api/v1/auth/otp/verify', {
                otpRequestId,
                code,
            });

            window.location.assign(
                response.data?.nextUrl ?? consentUrl(sourceQrCode),
            );
        } catch (verifyError) {
            setError(
                verifyError instanceof Error
                    ? verifyError.message
                    : 'خطایی رخ داد. لطفا دوباره تلاش کنید.',
            );
            setProcessing(false);
        }
    }

    return (
        <main
            dir="rtl"
            className="min-h-screen bg-[radial-gradient(circle_at_top_left,#fed7aa_0,#f8fafc_34%,#ecfeff_72%,#eef2ff_100%)] px-4 py-6 text-slate-950 dark:bg-slate-950 dark:text-slate-50"
        >
            <Head title="ورود به اکسپلوریا" />

            <section className="mx-auto flex min-h-[calc(100vh-3rem)] w-full max-w-5xl items-center">
                <div className="grid w-full gap-5 overflow-hidden rounded-2xl border border-white/70 bg-white/82 shadow-xl shadow-slate-900/10 backdrop-blur md:grid-cols-[1.05fr_0.95fr] dark:border-slate-800 dark:bg-slate-900/90">
                    <div className="flex flex-col justify-between gap-8 p-6 sm:p-8">
                        <div>
                            <div className="mb-6 flex items-center gap-3">
                                <div className="flex size-11 items-center justify-center rounded-xl bg-slate-950 text-white shadow-sm">
                                    <AppLogoIcon className="size-8" />
                                </div>
                                <div>
                                    <p className="text-sm text-slate-500 dark:text-slate-300">
                                        Exploria
                                    </p>
                                    <h1 className="text-2xl font-semibold">
                                        ورود سریع به تجربه مکان
                                    </h1>
                                </div>
                            </div>
                            <p className="max-w-md text-sm leading-7 text-slate-600 dark:text-slate-300">
                                شماره موبایل را وارد کنید، کد تایید را بزنید و
                                بدون توقف اضافه وارد رضایت نامه یا ادامه مسیر
                                بازدید شوید.
                            </p>
                        </div>

                        <div className="grid gap-3 text-sm sm:grid-cols-3">
                            <div className="rounded-xl bg-teal-50 p-3 text-teal-900 dark:bg-teal-950/40 dark:text-teal-100">
                                <MapPin className="mb-2 size-5" />
                                ورود با QR
                            </div>
                            <div className="rounded-xl bg-blue-50 p-3 text-blue-900 dark:bg-blue-950/40 dark:text-blue-100">
                                <ShieldCheck className="mb-2 size-5" />
                                رضایت امن
                            </div>
                            <div className="rounded-xl bg-orange-50 p-3 text-orange-900 dark:bg-orange-950/40 dark:text-orange-100">
                                <Sparkles className="mb-2 size-5" />
                                شروع بازی
                            </div>
                        </div>
                    </div>

                    <div className="bg-slate-950 p-5 text-white sm:p-6">
                        <div className="rounded-xl border border-white/10 bg-white/8 p-5">
                            {step === 'mobile' && (
                                <form
                                    onSubmit={requestOtp}
                                    className="space-y-5"
                                >
                                    <div>
                                        <p className="text-sm text-teal-200">
                                            مرحله اول
                                        </p>
                                        <h2 className="mt-1 text-xl font-semibold">
                                            شماره موبایل
                                        </h2>
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="mobile">
                                            شماره موبایل
                                        </Label>
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
                                            className="h-12 bg-white text-left text-slate-950"
                                        />
                                    </div>
                                    <InputError message={error} />
                                    <Button
                                        className="h-12 w-full bg-teal-500 text-white hover:bg-teal-600"
                                        disabled={processing}
                                    >
                                        {processing && <Spinner />}
                                        {processing
                                            ? 'در حال ارسال...'
                                            : 'دریافت کد تایید'}
                                    </Button>
                                </form>
                            )}

                            {step === 'code' && (
                                <form
                                    onSubmit={verifyOtp}
                                    className="space-y-5"
                                >
                                    <div>
                                        <p className="text-sm text-orange-200">
                                            مرحله دوم
                                        </p>
                                        <h2 className="mt-1 text-xl font-semibold">
                                            کد تایید
                                        </h2>
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="code">
                                            کد شش رقمی
                                        </Label>
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
                                            className="h-12 bg-white text-center text-lg tracking-[0.5em] text-slate-950"
                                        />
                                        <p className="text-xs text-slate-300">
                                            کد آزمایشی محیط محلی: 123456
                                        </p>
                                    </div>
                                    <InputError message={error} />
                                    <Button
                                        className="h-12 w-full bg-orange-500 text-white hover:bg-orange-600"
                                        disabled={processing}
                                    >
                                        {processing && <Spinner />}
                                        {processing
                                            ? 'در حال ورود...'
                                            : 'تایید و ادامه'}
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        className="w-full text-slate-200 hover:bg-white/10 hover:text-white"
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
                        </div>
                    </div>
                </div>
            </section>
        </main>
    );
}
