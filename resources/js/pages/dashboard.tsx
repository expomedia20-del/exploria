import { Head } from '@inertiajs/react';
import { dashboard } from '@/routes';

type Stats = {
    venues: number;
    activeQrCodes: number;
    otpRequests: number;
    consents: number;
    visits: number;
};

type LatestVisit = {
    id: string;
    venueName: string;
    touchpointLabel: string;
    campaignName: string;
    visitorName: string;
    status: string;
    occurredAt: string;
};

type Props = {
    stats: Stats;
    latestVisits: LatestVisit[];
};

const statLabels: Array<[keyof Stats, string, string]> = [
    ['venues', 'مکان‌های پایلوت', 'کل مکان‌های ثبت‌شده در محدوده پایلوت'],
    ['activeQrCodes', 'QR فعال', 'کدهای آماده برای اسکن و ورود'],
    ['otpRequests', 'درخواست OTP', 'تعداد تلاش‌های ورود سریع'],
    ['consents', 'رضایت ثبت‌شده', 'پذیرش‌های معتبر رضایت‌نامه'],
    ['visits', 'بازدید ثبت‌شده', 'رخدادهای تاییدشده پس از رضایت'],
];

function formatDate(value: string) {
    return new Intl.DateTimeFormat('fa-IR', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

export default function Dashboard({ stats, latestVisits }: Props) {
    return (
        <>
            <Head title="داشبورد پایلوت" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
                <header>
                    <p className="text-sm text-muted-foreground">
                        پایلوت اکوپارک عباس‌آباد
                    </p>
                    <h1 className="mt-1 text-2xl font-semibold">
                        داشبورد عملیاتی اکسپلوریا
                    </h1>
                </header>

                <div className="grid auto-rows-min gap-4 md:grid-cols-5">
                    {statLabels.map(([key, label, description]) => (
                        <section
                            key={key}
                            className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border"
                        >
                            <p className="text-sm text-muted-foreground">
                                {label}
                            </p>
                            <p className="mt-3 text-3xl font-semibold">
                                {stats[key].toLocaleString('fa-IR')}
                            </p>
                            <p className="mt-3 min-h-10 text-xs leading-5 text-muted-foreground">
                                {description}
                            </p>
                        </section>
                    ))}
                </div>

                <section className="rounded-lg border border-sidebar-border/70 bg-background dark:border-sidebar-border">
                    <div className="border-b border-sidebar-border/70 p-4 dark:border-sidebar-border">
                        <h2 className="text-lg font-semibold">
                            آخرین بازدیدهای ثبت‌شده
                        </h2>
                        <p className="mt-1 text-sm text-muted-foreground">
                            این فهرست پس از پذیرش رضایت‌نامه و ثبت QR تکمیل
                            می‌شود.
                        </p>
                    </div>

                    {latestVisits.length === 0 ? (
                        <div className="p-8 text-center text-sm text-muted-foreground">
                            هنوز بازدیدی ثبت نشده است.
                        </div>
                    ) : (
                        <div className="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                            {latestVisits.map((visit) => (
                                <article
                                    key={visit.id}
                                    className="grid gap-3 p-4 md:grid-cols-[1fr_auto]"
                                >
                                    <div>
                                        <p className="font-medium">
                                            {visit.venueName}
                                        </p>
                                        <p className="mt-1 text-sm text-muted-foreground">
                                            {visit.touchpointLabel} ·{' '}
                                            {visit.campaignName}
                                        </p>
                                        <p className="mt-2 text-xs text-muted-foreground">
                                            بازدیدکننده: {visit.visitorName}
                                        </p>
                                    </div>
                                    <div className="text-sm text-muted-foreground md:text-left">
                                        <p>{formatDate(visit.occurredAt)}</p>
                                        <p className="mt-2 inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">
                                            {visit.status === 'confirmed'
                                                ? 'تاییدشده'
                                                : visit.status}
                                        </p>
                                    </div>
                                </article>
                            ))}
                        </div>
                    )}
                </section>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
