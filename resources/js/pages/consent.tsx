import { Head } from '@inertiajs/react';
import { FileCheck2, ShieldCheck } from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';
import AppLogoIcon from '@/components/app-logo-icon';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

type PageState = 'loading' | 'ready' | 'empty' | 'error' | 'success';

type ConsentVersion = {
    id: string;
    version: string;
    language: string;
    title: string;
    body: string;
    is_demo: boolean;
    accepted: boolean;
};

type ApiResponse = {
    message?: string;
    data?:
        | ConsentVersion
        | {
              nextUrl?: string;
          }
        | null;
    errors?: Record<string, string[]>;
};

function csrfToken() {
    return (
        document
            .querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? ''
    );
}

function sourceQrCode() {
    return new URLSearchParams(window.location.search).get('sourceQrCode');
}

export default function Consent() {
    const [state, setState] = useState<PageState>('loading');
    const [version, setVersion] = useState<ConsentVersion | null>(null);
    const [agreed, setAgreed] = useState(false);
    const [processing, setProcessing] = useState(false);
    const [message, setMessage] = useState('');

    const submitConsent = useCallback(
        async (
            selectedVersion: ConsentVersion,
            skipAgreement = false,
            isAgreed = false,
        ) => {
            if (!skipAgreement && !isAgreed) {
                return;
            }

            setProcessing(true);
            setMessage('');

            try {
                const qrCode = sourceQrCode();
                const response = await fetch('/api/v1/consents/accept', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                    },
                    body: JSON.stringify({
                        consentVersionId: selectedVersion.id,
                        source: qrCode ? 'qr_landing' : 'pwa',
                        sourceQrCode: qrCode,
                    }),
                });
                const payload = (await response.json()) as ApiResponse;

                if (!response.ok) {
                    throw new Error(
                        payload.errors?.consentVersionId?.[0] ??
                            payload.message ??
                            'ثبت رضایت انجام نشد. لطفا دوباره تلاش کنید.',
                    );
                }

                if (
                    payload.data &&
                    'nextUrl' in payload.data &&
                    payload.data.nextUrl
                ) {
                    window.location.assign(payload.data.nextUrl);

                    return;
                }

                setState('success');
            } catch (error) {
                setMessage(
                    error instanceof Error
                        ? error.message
                        : 'ثبت رضایت انجام نشد. لطفا دوباره تلاش کنید.',
                );
                setState('error');
            } finally {
                setProcessing(false);
            }
        },
        [],
    );

    const loadConsent = useCallback(async () => {
        setState('loading');
        setMessage('');

        try {
            const response = await fetch('/api/v1/consents/current?language=fa', {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            const payload = (await response.json()) as ApiResponse;

            if (response.status === 404) {
                setState('empty');

                return;
            }

            if (!response.ok || !payload.data || !('body' in payload.data)) {
                throw new Error(
                    payload.message ??
                        'دریافت رضایت نامه ممکن نشد. لطفا دوباره تلاش کنید.',
                );
            }

            setVersion(payload.data);

            if (payload.data.accepted) {
                await submitConsent(payload.data, true, true);

                return;
            }

            setState('ready');
        } catch (error) {
            setMessage(
                error instanceof Error
                    ? error.message
                    : 'دریافت رضایت نامه ممکن نشد. لطفا دوباره تلاش کنید.',
            );
            setState('error');
        }
    }, [submitConsent]);

    useEffect(() => {
        const initialLoad = window.setTimeout(() => void loadConsent(), 0);

        return () => window.clearTimeout(initialLoad);
    }, [loadConsent]);

    return (
        <main
            dir="rtl"
            className="min-h-screen bg-[radial-gradient(circle_at_top_left,#d1fae5_0,#f8fafc_42%,#ffedd5_100%)] px-4 py-6 text-slate-950 dark:bg-slate-950 dark:text-slate-50"
        >
            <Head title="رضایت نامه پایلوت" />

            <section className="mx-auto flex min-h-[calc(100vh-3rem)] w-full max-w-4xl items-center">
                <div className="w-full overflow-hidden rounded-2xl border border-white/70 bg-white/88 shadow-xl shadow-slate-900/10 backdrop-blur dark:border-slate-800 dark:bg-slate-900/90">
                    <div className="grid gap-0 md:grid-cols-[0.9fr_1.1fr]">
                        <aside className="bg-slate-950 p-6 text-white sm:p-8">
                            <div className="mb-8 flex items-center gap-3">
                                <div className="flex size-11 items-center justify-center rounded-xl bg-white text-slate-950">
                                    <AppLogoIcon className="size-8" />
                                </div>
                                <div>
                                    <p className="text-sm text-teal-200">
                                        Exploria
                                    </p>
                                    <h1 className="text-xl font-semibold">
                                        رضایت نامه تجربه
                                    </h1>
                                </div>
                            </div>
                            <div className="space-y-3 text-sm leading-7 text-slate-200">
                                <p>
                                    این مرحله فقط برای ثبت شفاف و امن اطلاعات
                                    ضروری پایلوت است.
                                </p>
                                <div className="rounded-xl bg-white/8 p-4">
                                    <ShieldCheck className="mb-3 size-6 text-teal-300" />
                                    شماره موبایل و نشست شما در گزارش عمومی
                                    نمایش داده نمی شود.
                                </div>
                                <div className="rounded-xl bg-white/8 p-4">
                                    <FileCheck2 className="mb-3 size-6 text-orange-300" />
                                    اگر قبلا نسخه فعال را پذیرفته باشید، این
                                    صفحه خودکار رد می شود.
                                </div>
                            </div>
                        </aside>

                        <div className="p-5 sm:p-8">
                            {state === 'loading' && (
                                <div className="flex min-h-80 flex-col items-center justify-center gap-4 text-slate-600 dark:text-slate-300">
                                    <Spinner className="size-6" />
                                    <p>در حال بررسی رضایت نامه...</p>
                                </div>
                            )}

                            {state === 'empty' && (
                                <div className="py-12 text-center">
                                    <h1 className="text-xl font-bold">
                                        رضایت نامه فعالی وجود ندارد
                                    </h1>
                                    <p className="mt-3 text-sm text-slate-600 dark:text-slate-300">
                                        ادامه مسیر تا انتشار نسخه معتبر متوقف
                                        شده است.
                                    </p>
                                </div>
                            )}

                            {state === 'error' && (
                                <div className="py-12 text-center">
                                    <h1 className="text-xl font-bold text-red-700 dark:text-red-300">
                                        دریافت اطلاعات ناموفق بود
                                    </h1>
                                    <p className="mt-3 text-sm text-slate-600 dark:text-slate-300">
                                        {message}
                                    </p>
                                    <Button
                                        className="mt-6"
                                        onClick={() => void loadConsent()}
                                    >
                                        تلاش دوباره
                                    </Button>
                                </div>
                            )}

                            {state === 'ready' && version && (
                                <>
                                    <div className="mb-6">
                                        <div className="flex flex-wrap items-center gap-2">
                                            <h1 className="text-2xl font-bold">
                                                {version.title}
                                            </h1>
                                            {version.is_demo && (
                                                <span className="rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-800 dark:bg-amber-950 dark:text-amber-200">
                                                    متن آزمایشی و غیرنهایی
                                                </span>
                                            )}
                                        </div>
                                        <p className="mt-2 text-xs text-slate-500">
                                            نسخه {version.version}
                                        </p>
                                    </div>

                                    <div className="max-h-80 overflow-y-auto rounded-xl bg-slate-50 p-5 text-sm leading-8 whitespace-pre-line dark:bg-slate-950/60">
                                        {version.body}
                                    </div>

                                    <div className="mt-6 flex items-start gap-3 rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                                        <Checkbox
                                            id="consent-agreement"
                                            checked={agreed}
                                            onCheckedChange={(checked) =>
                                                setAgreed(checked === true)
                                            }
                                        />
                                        <Label
                                            htmlFor="consent-agreement"
                                            className="cursor-pointer leading-6"
                                        >
                                            متن بالا را مطالعه کردم و با ثبت
                                            اطلاعات ضروری پایلوت موافقم.
                                        </Label>
                                    </div>

                                    {message && (
                                        <p className="mt-4 text-sm text-red-600">
                                            {message}
                                        </p>
                                    )}

                                    <Button
                                        className="mt-6 h-11 w-full bg-teal-600 text-white hover:bg-teal-700"
                                        disabled={!agreed || processing}
                                        onClick={() =>
                                            void submitConsent(
                                                version,
                                                false,
                                                agreed,
                                            )
                                        }
                                    >
                                        {processing && <Spinner />}
                                        {processing
                                            ? 'در حال ثبت...'
                                            : 'پذیرش و ادامه'}
                                    </Button>
                                </>
                            )}

                            {state === 'success' && (
                                <div className="rounded-xl bg-emerald-50 p-6 text-center dark:bg-emerald-950/40">
                                    <h1 className="text-xl font-bold text-emerald-800 dark:text-emerald-200">
                                        رضایت شما ثبت شد
                                    </h1>
                                    <p className="mt-3 text-sm leading-7 text-emerald-700 dark:text-emerald-300">
                                        اکنون مسیر ثبت بازدید مکان پایلوت می
                                        تواند ادامه پیدا کند.
                                    </p>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </section>
        </main>
    );
}
