import { Head } from '@inertiajs/react';
import { Button } from '@/components/ui/button';

type Props = {
    qr: {
        code: string;
        label: string;
        venueName: string;
        city: string;
        zoneName: string;
        hubName: string;
        touchpointLabel: string;
        campaignName: string;
        isDemo: boolean;
    };
};

export default function ScanLanding({ qr }: Props) {
    return (
        <main className="flex min-h-screen items-center justify-center bg-slate-50 px-4 py-10 text-slate-950 dark:bg-slate-950 dark:text-slate-50">
            <Head title={`بازدید ${qr.venueName}`} />

            <section className="w-full max-w-lg rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 dark:border-slate-800 dark:bg-slate-900">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <span className="rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800 dark:bg-blue-950 dark:text-blue-200">
                        پایلوت اکسپلوریا
                    </span>
                    {qr.isDemo && (
                        <span className="rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-800 dark:bg-amber-950 dark:text-amber-200">
                            داده آزمایشی
                        </span>
                    )}
                </div>

                <div className="mt-8">
                    <p className="text-sm text-slate-500">
                        به تجربه مکان خوش آمدید
                    </p>
                    <h1 className="mt-2 text-3xl font-bold">{qr.venueName}</h1>
                    <p className="mt-2 text-sm text-slate-600 dark:text-slate-300">
                        {qr.city} · {qr.campaignName}
                    </p>
                </div>

                <dl className="mt-8 grid gap-4 rounded-2xl bg-slate-50 p-5 text-sm dark:bg-slate-950/60">
                    <div className="flex justify-between gap-4">
                        <dt className="text-slate-500">محدوده</dt>
                        <dd className="font-medium">{qr.zoneName}</dd>
                    </div>
                    <div className="flex justify-between gap-4">
                        <dt className="text-slate-500">هاب</dt>
                        <dd className="font-medium">{qr.hubName}</dd>
                    </div>
                    <div className="flex justify-between gap-4">
                        <dt className="text-slate-500">نقطه تعامل</dt>
                        <dd className="font-medium">{qr.touchpointLabel}</dd>
                    </div>
                </dl>

                <p className="mt-6 text-sm leading-7 text-slate-600 dark:text-slate-300">
                    برای ثبت بازدید و ادامه تجربه پایلوت، ورود سریع و پذیرش
                    رضایت‌نامه لازم است.
                </p>

                <Button
                    className="mt-6 h-11 w-full"
                    onClick={() =>
                        window.location.assign(
                            `/access?sourceQrCode=${encodeURIComponent(qr.code)}`,
                        )
                    }
                >
                    شروع تجربه
                </Button>
            </section>
        </main>
    );
}
