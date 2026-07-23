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

            const nextUrl =
                response.data?.nextUrl ??
                (response.data?.consentRequired === false
                    ? '/dashboard'
                    : consentUrl(sourceQrCode));

            window.location.assign(nextUrl);
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
            className="min-h-svh w-full bg-[radial-gradient(circle_at_top_left,#fed7aa_0,#f8fafc_34%,#ecfeff_72%,#eef2ff_100%)] px-3 py-3 text-slate-950 sm:px-4 sm:py-6 dark:bg-slate-950 dark:text-slate-50"
        >
            <Head title="ورود به اکسپلوریا" />

            <section className="mx-auto flex min-h-[calc(100svh-1.5rem)] w-full max-w-5xl items-center sm:min-h-[calc(100svh-3rem)]">
                <div className="grid w-full grid-cols-1 overflow-hidden rounded-xl border border-white/70 bg-white/82 shadow-xl shadow-slate-900/10 backdrop-blur sm:rounded-2xl lg:grid-cols-[1.05fr_0.95fr] dark:border-slate-800 dark:bg-slate-900/90">
                    <div className="order-2 flex flex-col justify-between gap-6 p-5 sm:p-8 lg:order-1 lg:gap-8">
                        <div>
                            <div className="mb-4 flex items-center gap-3 sm:mb-6">
                                <div className="flex size-10 shrink-0 items-center justify-center rounded-xl bg-slate-950 text-white shadow-sm sm:size-11">
                                    <AppLogoIcon className="size-7 sm:size-8" />
                                </div>
                                <div>
                                    <p className="text-sm text-slate-500 dark:text-slate-300">
                                        Exploria
                                    </p>
                                    <h1 className="text-xl leading-8 font-semibold sm:text-2xl">
                                        ورود سریع به تجربه مکان
                                    </h1>
                                </div>
                            </div>
                            <p className="max-w-md text-sm leading-6 text-slate-600 sm:leading-7 dark:text-slate-300">
                                شماره موبایل را وارد کنید، کد تایید را بزنید و
                                بدون توقف اضافه وارد رضایت نامه یا ادامه مسیر
                                بازدید شوید.
                            </p>
                        </div>

                        <div className="grid grid-cols-3 gap-2 text-xs sm:gap-3 sm:text-sm">
                            <div className="rounded-xl bg-teal-50 p-2.5 text-teal-900 sm:p-3 dark:bg-teal-950/40 dark:text-teal-100">
                                <MapPin className="mb-2 size-5" />
                                ورود با QR
                            </div>
                            <div className="rounded-xl bg-blue-50 p-2.5 text-blue-900 sm:p-3 dark:bg-blue-950/40 dark:text-blue-100">
                                <ShieldCheck className="mb-2 size-5" />
                                رضایت امن
                            </div>
                            <div className="rounded-xl bg-orange-50 p-2.5 text-orange-900 sm:p-3 dark:bg-orange-950/40 dark:text-orange-100">
                                <Sparkles className="mb-2 size-5" />
                                شروع بازی
                            </div>
                        </div>
                    </div>

                    <div className="order-1 bg-slate-950 p-4 text-white sm:p-6 lg:order-2">
                        <div className="rounded-xl border border-white/10 bg-white/8 p-4 sm:p-5">
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
                                        <Label htmlFor="code">کد شش رقمی</Label>
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
