import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';

type Visit = {
    id: string;
    status: string;
    occurredAt: string;
    qrCode: string | null;
    venueName: string | null;
    city: string | null;
    zoneName: string | null;
    hubName: string | null;
    touchpointLabel: string | null;
    campaignName: string | null;
    isDemo: boolean;
};

type Props = {
    visit: Visit;
};

function formatDate(value: string) {
    return new Intl.DateTimeFormat('fa-IR', {
        dateStyle: 'full',
        timeStyle: 'short',
    }).format(new Date(value));
}

export default function VisitShow({ visit }: Props) {
    return (
        <main className="flex min-h-screen items-center justify-center bg-slate-50 px-4 py-10 text-slate-950 dark:bg-slate-950 dark:text-slate-50">
            <Head title={`بازدید ${visit.venueName ?? 'پایلوت'}`} />

            <section className="w-full max-w-2xl rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 dark:border-slate-800 dark:bg-slate-900">
                <div className="flex flex-wrap items-center gap-2">
                    <span className="rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">
                        بازدید ثبت شد
                    </span>
                    {visit.isDemo && (
                        <span className="rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-800 dark:bg-amber-950 dark:text-amber-200">
                            داده آزمایشی
                        </span>
                    )}
                </div>

                <div className="mt-8">
                    <p className="text-sm text-slate-500">
                        تجربه پایلوت اکسپلوریا
                    </p>
                    <h1 className="mt-2 text-3xl font-bold">
                        {visit.venueName}
                    </h1>
                    <p className="mt-2 text-sm text-slate-600 dark:text-slate-300">
                        {visit.city} · {visit.campaignName}
                    </p>
                </div>

                <dl className="mt-8 grid gap-4 rounded-2xl bg-slate-50 p-5 text-sm dark:bg-slate-950/60">
                    <div className="flex justify-between gap-4">
                        <dt className="text-slate-500">زمان ثبت</dt>
                        <dd className="font-medium">
                            {formatDate(visit.occurredAt)}
                        </dd>
                    </div>
                    <div className="flex justify-between gap-4">
                        <dt className="text-slate-500">محدوده</dt>
                        <dd className="font-medium">{visit.zoneName}</dd>
                    </div>
                    <div className="flex justify-between gap-4">
                        <dt className="text-slate-500">هاب</dt>
                        <dd className="font-medium">{visit.hubName}</dd>
                    </div>
                    <div className="flex justify-between gap-4">
                        <dt className="text-slate-500">نقطه تعامل</dt>
                        <dd className="font-medium">
                            {visit.touchpointLabel}
                        </dd>
                    </div>
                    <div className="flex justify-between gap-4">
                        <dt className="text-slate-500">کد QR</dt>
                        <dd className="font-mono text-xs">{visit.qrCode}</dd>
                    </div>
                </dl>

                <div className="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-5 text-sm leading-7 text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-200">
                    حضور شما برای این نقطه پایلوت ثبت شد. در نسخه عملیاتی بعدی،
                    این صفحه می‌تواند راهنمای تجربه، پیشنهادهای مسیر و وضعیت
                    امتیازهای بازدید را نمایش دهد.
                </div>

                <div className="mt-6 grid gap-3 sm:grid-cols-2">
                    <Button asChild className="h-11">
                        <Link href="/dashboard">مشاهده داشبورد</Link>
                    </Button>
                    <Button asChild variant="outline" className="h-11">
                        <Link href={`/scan/${visit.qrCode ?? ''}`}>
                            بازگشت به صفحه QR
                        </Link>
                    </Button>
                </div>
            </section>
        </main>
    );
}
