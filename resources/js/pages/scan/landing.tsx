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
    rewardOptions: {
        id: string;
        name: string;
        tier: string | null;
        option: string | null;
        partnerName: string | null;
        description: string | null;
    }[];
};

const rewardTierLabels: Record<string, string> = {
    bronze: 'برنزی',
    silver: 'نقره‌ای',
    gold: 'طلایی',
    diamond: 'الماسی',
    custom: 'سفارشی',
};

export default function ScanLanding({ qr, rewardOptions }: Props) {
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

                {rewardOptions.length > 0 ? (
                    <section className="mt-6 rounded-2xl border border-emerald-100 bg-emerald-50/70 p-4 dark:border-emerald-900/60 dark:bg-emerald-950/20">
                        <h2 className="text-sm font-semibold">گزینه‌های پاداش این کمپین</h2>
                        <div className="mt-3 grid gap-2">
                            {rewardOptions.map((reward) => (
                                <article key={reward.id} className="rounded-xl bg-white px-3 py-2 text-sm shadow-xs dark:bg-slate-900">
                                    <div className="flex flex-wrap items-center justify-between gap-2">
                                        <p className="font-medium">{reward.name}</p>
                                        <span className="rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] text-emerald-800 dark:bg-emerald-900 dark:text-emerald-100">
                                            {reward.tier ? rewardTierLabels[reward.tier] ?? reward.tier : 'عمومی'}
                                        </span>
                                    </div>
                                    <p className="mt-1 text-xs text-slate-600 dark:text-slate-300">
                                        {reward.option ?? 'گزینه جایزه توسط کمپین تعیین می‌شود'}
                                        {reward.partnerName ? ` · ${reward.partnerName}` : ''}
                                    </p>
                                    {reward.description ? (
                                        <p className="mt-1 line-clamp-2 text-xs text-slate-500 dark:text-slate-400">
                                            {reward.description}
                                        </p>
                                    ) : null}
                                </article>
                            ))}
                        </div>
                    </section>
                ) : null}

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
