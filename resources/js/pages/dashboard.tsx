import { Head, Link } from '@inertiajs/react';
import { dashboard } from '@/routes';

type Stats = {
    venues: number;
    activeQrCodes: number;
    otpRequests: number;
    consents: number;
    visits: number;
    activeCampaigns: number;
    missionCompletions: number;
    issuedRewards: number;
    pendingRedemptions: number;
    confirmedRedemptions: number;
    activeMissions: number;
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
    latestRedemptions: LatestRedemption[];
    operationalAlerts: OperationalAlert[];
    campaignPerformance: CampaignPerformance[];
};

type OperationalAlert = {
    key: string;
    severity: 'attention' | 'warning';
    title: string;
    message: string;
    actionLabel: string;
    actionHref: string;
};

type LatestRedemption = {
    id: string;
    redemptionCode: string;
    status: string;
    rewardName: string | null;
    campaignName: string | null;
    campaignCode: string | null;
    partnerName: string | null;
    visitorName: string | null;
    redeemedAt: string | null;
    createdAt: string | null;
};

type CampaignPerformance = {
    id: string;
    code: string;
    name: string;
    venueName: string | null;
    visits: number;
    qrCodes: number;
    missions: number;
    completedMissions: number;
    rewards: number;
    pendingRedemptions: number;
    confirmedRedemptions: number;
    progressPercent: number;
};

const statLabels: Array<[keyof Stats, string, string]> = [
    ['venues', 'مکان‌های پایلوت', 'کل مکان‌های ثبت‌شده در محدوده پایلوت'],
    ['activeQrCodes', 'QR فعال', 'کدهای آماده برای اسکن و ورود'],
    ['otpRequests', 'درخواست OTP', 'تعداد تلاش‌های ورود سریع'],
    ['consents', 'رضایت ثبت‌شده', 'پذیرش‌های معتبر رضایت‌نامه'],
    ['visits', 'بازدید ثبت‌شده', 'رخدادهای تاییدشده پس از رضایت'],
    ['activeCampaigns', 'کمپین فعال', 'کمپین‌هایی که وارد مرحله اجرا شده‌اند'],
    ['activeMissions', 'مأموریت فعال', 'مأموریت‌های قابل اجرا برای کاربران'],
    ['missionCompletions', 'تکمیل مأموریت', 'کل مأموریت‌های انجام‌شده توسط کاربران'],
    ['issuedRewards', 'پاداش صادرشده', 'پاداش‌هایی که در کیف کاربران ثبت شده‌اند'],
    ['pendingRedemptions', 'در انتظار تحویل', 'پاداش‌هایی که فروشگاه یا اسپانسر باید تحویل دهد'],
    ['confirmedRedemptions', 'تحویل‌شده', 'پاداش‌هایی که شریک تحویل آن‌ها را تایید کرده است'],
];

const redemptionStatusLabels: Record<string, string> = {
    pending: 'در انتظار تحویل',
    confirmed: 'تحویل‌شده',
    redeemed: 'مصرف شده',
    expired: 'منقضی شده',
};

function formatDate(value: string) {
    return new Intl.DateTimeFormat('fa-IR', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

export default function Dashboard({ stats, latestVisits, latestRedemptions, operationalAlerts, campaignPerformance }: Props) {
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

                <div className="grid auto-rows-min gap-4 md:grid-cols-3 xl:grid-cols-5">
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

                <section className="exploria-panel">
                    <div className="border-b border-border/70 p-4 dark:border-sidebar-border">
                        <h2 className="text-lg font-semibold">هشدارهای عملیاتی</h2>
                        <p className="mt-1 text-sm text-muted-foreground">
                            مواردی که ممکن است اجرای کمپین را کند کند یا نیازمند پیگیری سریع ادمین باشد.
                        </p>
                    </div>

                    {operationalAlerts.length === 0 ? (
                        <div className="p-8 text-center text-sm text-muted-foreground">
                            فعلا هشدار عملیاتی فعالی وجود ندارد.
                        </div>
                    ) : (
                        <div className="grid gap-3 p-4 lg:grid-cols-2">
                            {operationalAlerts.map((alert) => (
                                <article
                                    key={alert.key}
                                    className={alert.severity === 'attention' ? 'rounded-lg border border-amber-300 bg-amber-50 p-4 dark:border-amber-900/60 dark:bg-amber-950/30' : 'rounded-lg border border-sky-200 bg-sky-50 p-4 dark:border-sky-900/60 dark:bg-sky-950/30'}
                                >
                                    <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <h3 className="font-semibold">{alert.title}</h3>
                                            <p className="mt-1 text-sm leading-6 text-muted-foreground">{alert.message}</p>
                                        </div>
                                        <Link
                                            className="inline-flex h-9 shrink-0 items-center justify-center rounded-md border border-input bg-background px-3 text-sm font-medium hover:bg-accent hover:text-accent-foreground"
                                            href={alert.actionHref}
                                        >
                                            {alert.actionLabel}
                                        </Link>
                                    </div>
                                </article>
                            ))}
                        </div>
                    )}
                </section>

                <section className="exploria-panel">
                    <div className="border-b border-border/70 p-4 dark:border-sidebar-border">
                        <h2 className="text-lg font-semibold">پایش اجرای کمپین‌ها</h2>
                        <p className="mt-1 text-sm text-muted-foreground">
                            این بخش نشان می‌دهد کمپین فعال بعد از QR و ورود کاربر چقدر به مأموریت، پاداش و پیشرفت واقعی رسیده است.
                        </p>
                    </div>

                    {campaignPerformance.length === 0 ? (
                        <div className="p-8 text-center text-sm text-muted-foreground">
                            هنوز کمپین فعالی برای پایش اجرا وجود ندارد.
                        </div>
                    ) : (
                        <div className="grid gap-3 p-4 lg:grid-cols-2">
                            {campaignPerformance.map((campaign) => (
                                <article key={campaign.id} className="rounded-lg border border-border/80 bg-card/75 p-4 shadow-sm">
                                    <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <h3 className="font-semibold">{campaign.name}</h3>
                                            <p className="mt-1 text-sm text-muted-foreground">{campaign.venueName ?? 'مکان ثبت نشده'}</p>
                                        </div>
                                        <span className="w-fit rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">
                                            فعال
                                        </span>
                                    </div>

                                    <div className="mt-4 grid grid-cols-2 gap-2 text-sm md:grid-cols-4 xl:grid-cols-7">
                                        {[
                                            ['بازدید', campaign.visits],
                                            ['QR', campaign.qrCodes],
                                            ['مأموریت', campaign.missions],
                                            ['تکمیل', campaign.completedMissions],
                                            ['پاداش', campaign.rewards],
                                            ['در انتظار تحویل', campaign.pendingRedemptions],
                                            ['تحویل‌شده', campaign.confirmedRedemptions],
                                        ].map(([label, value]) => (
                                            <div key={String(label)} className="rounded-md bg-muted/45 p-2">
                                                <p className="text-xs text-muted-foreground">{label}</p>
                                                <p className="mt-1 font-semibold">{Number(value).toLocaleString('fa-IR')}</p>
                                            </div>
                                        ))}
                                    </div>

                                    <div className="mt-4">
                                        <div className="flex items-center justify-between gap-3 text-xs text-muted-foreground">
                                            <span>پیشرفت اجرای مأموریت‌ها</span>
                                            <span>{campaign.progressPercent.toLocaleString('fa-IR')}٪</span>
                                        </div>
                                        <div className="mt-2 h-2 overflow-hidden rounded-full bg-muted">
                                            <div className="h-full rounded-full bg-primary" style={{ width: `${campaign.progressPercent}%` }} />
                                        </div>
                                    </div>

                                    <div className="mt-4 flex flex-wrap gap-2">
                                        <Link className="inline-flex h-9 items-center rounded-md border border-input bg-background px-3 text-sm font-medium hover:bg-accent hover:text-accent-foreground" href={`/admin/campaign-builder?campaign=${campaign.code}`}>
                                            ساخت کمپین
                                        </Link>
                                        <Link className="inline-flex h-9 items-center rounded-md border border-input bg-background px-3 text-sm font-medium hover:bg-accent hover:text-accent-foreground" href={`/admin/campaign-operations?campaign=${campaign.code}`}>
                                            نقشه عملیات کمپین
                                        </Link>
                                    </div>
                                </article>
                            ))}
                        </div>
                    )}
                </section>

                <section className="exploria-panel">
                    <div className="border-b border-border/70 p-4 dark:border-sidebar-border">
                        <h2 className="text-lg font-semibold">آخرین وضعیت تحویل پاداش</h2>
                        <p className="mt-1 text-sm text-muted-foreground">
                            این لیست برای پیگیری سریع کدهای تحویل، فروشگاه مسئول و وضعیت مصرف پاداش است.
                        </p>
                    </div>

                    {latestRedemptions.length === 0 ? (
                        <div className="p-8 text-center text-sm text-muted-foreground">
                            هنوز پاداشی برای تحویل به فروشگاه یا اسپانسر نرسیده است.
                        </div>
                    ) : (
                        <div className="divide-y divide-border/70">
                            {latestRedemptions.map((redemption) => (
                                <article key={redemption.id} className="grid gap-3 p-4 lg:grid-cols-[1fr_auto]">
                                    <div>
                                        <div className="flex flex-wrap items-center gap-2">
                                            <p className="font-medium">{redemption.rewardName ?? 'پاداش ثبت نشده'}</p>
                                            <span className={redemption.status === 'confirmed' ? 'rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200' : 'rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-900 dark:bg-amber-950 dark:text-amber-100'}>
                                                {redemptionStatusLabels[redemption.status] ?? redemption.status}
                                            </span>
                                        </div>
                                        <p className="mt-1 text-sm text-muted-foreground">
                                            {redemption.partnerName ?? 'شریک ثبت نشده'} · {redemption.visitorName ?? 'کاربر ثبت نشده'} · {redemption.campaignName ?? 'کمپین ثبت نشده'}
                                        </p>
                                        <p className="mt-2 font-mono text-xs text-muted-foreground" dir="ltr">
                                            {redemption.redemptionCode}
                                        </p>
                                    </div>
                                    <div className="flex flex-col gap-2 text-sm text-muted-foreground lg:items-end">
                                        <p>{formatDate(redemption.redeemedAt ?? redemption.createdAt ?? new Date().toISOString())}</p>
                                        {redemption.campaignCode ? (
                                            <Link className="inline-flex h-8 items-center rounded-md border border-input bg-background px-3 text-xs font-medium hover:bg-accent hover:text-accent-foreground" href={`/admin/campaign-operations?campaign=${redemption.campaignCode}`}>
                                                نقشه عملیات کمپین
                                            </Link>
                                        ) : null}
                                    </div>
                                </article>
                            ))}
                        </div>
                    )}
                </section>

                <section className="exploria-panel">
                    <div className="border-b border-border/70 p-4 dark:border-sidebar-border">
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
                        <div className="divide-y divide-border/70">
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
