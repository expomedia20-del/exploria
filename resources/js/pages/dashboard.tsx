import { Head, Link } from '@inertiajs/react';
import { Activity, BarChart3, Gauge, MapPin } from 'lucide-react';
import { dashboard } from '@/routes';

type Stats = {
    venues: number;
    activeQrCodes: number;
    otpRequests: number;
    consents: number;
    scans: number;
    acceptedScans: number;
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
    scopeSummary: ScopeSummary;
    stats: Stats;
    latestVisits: LatestVisit[];
    latestRedemptions: LatestRedemption[];
    operationalAlerts: OperationalAlert[];
    campaignPerformance: CampaignPerformance[];
};

type ScopeSummary = {
    isGlobal: boolean;
    label: string;
    regions: string[];
    venuesCount: number;
    campaignsCount: number;
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
    ['scans', 'کل اسکن‌ها', 'اسکن‌های معتبر، نامعتبر و تکراری ثبت‌شده'],
    ['acceptedScans', 'اسکن پذیرفته‌شده', 'اسکن‌های معتبر پس از رضایت کاربر'],
    ['visits', 'بازدید ثبت‌شده', 'رخدادهای تاییدشده پس از رضایت'],
    ['activeCampaigns', 'کمپین فعال', 'کمپین‌هایی که وارد مرحله اجرا شده‌اند'],
    ['activeMissions', 'مأموریت فعال', 'مأموریت‌های قابل اجرا برای کاربران'],
    [
        'missionCompletions',
        'تکمیل مأموریت',
        'کل مأموریت‌های انجام‌شده توسط کاربران',
    ],
    [
        'issuedRewards',
        'پاداش صادرشده',
        'پاداش‌هایی که در کیف کاربران ثبت شده‌اند',
    ],
    [
        'pendingRedemptions',
        'در انتظار تحویل',
        'پاداش‌هایی که فروشگاه یا واحد تحویل باید تحویل دهد',
    ],
    [
        'confirmedRedemptions',
        'تحویل‌شده',
        'پاداش‌هایی که شریک تحویل آن‌ها را تایید کرده است',
    ],
];

const dashboardChartItems: Array<[keyof Stats, string, string]> = [
    ['venues', 'مکان پایلوت', '#0f766e'],
    ['activeCampaigns', 'کمپین فعال', '#0891b2'],
    ['activeQrCodes', 'QR فعال', '#d97706'],
    ['scans', 'کل اسکن', '#0284c7'],
    ['visits', 'بازدید', '#e11d48'],
    ['activeMissions', 'ماموریت فعال', '#4f46e5'],
    ['missionCompletions', 'تکمیل ماموریت', '#16a34a'],
    ['issuedRewards', 'پاداش صادرشده', '#ea580c'],
    ['confirmedRedemptions', 'تحویل‌شده', '#7c3aed'],
];

const flowChartItems: Array<[keyof Stats, string]> = [
    ['visits', 'بازدید'],
    ['missionCompletions', 'ماموریت'],
    ['issuedRewards', 'پاداش'],
    ['confirmedRedemptions', 'تحویل'],
];

const flowColors = ['#0891b2', '#16a34a', '#f59e0b', '#e11d48'];

function chartHeight(value: number, max: number) {
    if (value === 0) {
        return '10px';
    }

    return `${Math.max(22, Math.round((value / max) * 150))}px`;
}

function shareWidth(value: number, total: number) {
    if (total === 0) {
        return '25%';
    }

    return `${Math.max(8, (value / total) * 100)}%`;
}

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

export default function Dashboard({
    scopeSummary,
    stats,
    latestVisits,
    latestRedemptions,
    operationalAlerts,
    campaignPerformance,
}: Props) {
    const chartMax = Math.max(
        ...dashboardChartItems.map(([key]) => stats[key]),
        1,
    );
    const flowTotal = flowChartItems.reduce(
        (total, [key]) => total + stats[key],
        0,
    );
    const readinessScore = Math.min(
        100,
        Math.round(
            (stats.activeCampaigns > 0 ? 20 : 0) +
                (stats.activeQrCodes > 0 ? 20 : 0) +
                (stats.visits > 0 ? 20 : 0) +
                (stats.missionCompletions > 0 ? 20 : 0) +
                (stats.issuedRewards > 0 ? 20 : 0),
        ),
    );

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

                <section className="flex flex-col gap-3 rounded-lg border border-emerald-200 bg-emerald-50/75 p-4 text-sm dark:border-emerald-900/60 dark:bg-emerald-950/20 md:flex-row md:items-center md:justify-between">
                    <div className="flex items-start gap-3">
                        <span className="inline-flex size-9 shrink-0 items-center justify-center rounded-md bg-emerald-600 text-white">
                            <MapPin className="size-5" />
                        </span>
                        <div>
                            <p className="font-semibold">
                                {scopeSummary.label}
                            </p>
                            <p className="mt-1 leading-6 text-muted-foreground">
                                داده‌های این داشبورد بر اساس دامنه دسترسی نقش شما
                                نمایش داده می‌شود؛ ادمین مرکزی نمای کل و ادمین
                                استانی فقط نمای استان/منطقه خودش را می‌بیند.
                            </p>
                        </div>
                    </div>
                    <div className="grid grid-cols-2 gap-2 md:min-w-64">
                        <div className="rounded-md bg-white/75 p-3 dark:bg-background/45">
                            <p className="text-xs text-muted-foreground">
                                مکان‌های قابل مشاهده
                            </p>
                            <strong className="mt-1 block text-lg">
                                {scopeSummary.venuesCount.toLocaleString(
                                    'fa-IR',
                                )}
                            </strong>
                        </div>
                        <div className="rounded-md bg-white/75 p-3 dark:bg-background/45">
                            <p className="text-xs text-muted-foreground">
                                کمپین‌های فعال
                            </p>
                            <strong className="mt-1 block text-lg">
                                {scopeSummary.campaignsCount.toLocaleString(
                                    'fa-IR',
                                )}
                            </strong>
                        </div>
                    </div>
                </section>

                <section className="grid gap-3 md:grid-cols-4">
                    {[
                        [
                            'مدیریت شرکا',
                            '/admin/partners',
                            'وضعیت فروشگاه‌ها، اسپانسرها و اتصال اکانت‌ها',
                        ],
                        [
                            'تبلیغات مستقل',
                            '/admin/ads',
                            'صف تایید تبلیغات و درخواست‌های آماده انتشار',
                        ],
                        [
                            'عملیات نمایشگرها',
                            '/admin/display-operations',
                            'سلامت نمایشگرها و زمان‌بندی پخش',
                        ],
                        [
                            'پشتیبانی و چت‌بات',
                            '/admin/support',
                            'مسیر سریع عیب‌یابی و سوالات عملیاتی',
                        ],
                    ].map(([title, href, body]) => (
                        <Link
                            key={href}
                            href={href}
                            className="rounded-lg border border-sidebar-border/70 bg-background p-4 transition hover:border-primary/50 dark:border-sidebar-border"
                        >
                            <p className="font-semibold">{title}</p>
                            <p className="mt-2 text-sm leading-6 text-muted-foreground">
                                {body}
                            </p>
                        </Link>
                    ))}
                </section>

                <section className="grid gap-4 xl:grid-cols-[1.55fr_0.9fr]">
                    <article className="rounded-lg border border-cyan-200 bg-cyan-50/70 p-4 dark:border-cyan-900/60 dark:bg-cyan-950/20">
                        <div className="flex flex-wrap items-start justify-between gap-3">
                            <div className="flex items-center gap-2">
                                <span className="inline-flex size-9 items-center justify-center rounded-md bg-cyan-600 text-white">
                                    <BarChart3 className="size-5" />
                                </span>
                                <div>
                                    <h2 className="text-lg font-semibold">
                                        نمودار وضعیت عملیاتی پایلوت
                                    </h2>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        همین اعداد بالای داشبورد، اینجا برای
                                        تصمیم سریع‌تر به شکل نمودار دیده
                                        می‌شوند.
                                    </p>
                                </div>
                            </div>
                            <span className="rounded-full bg-white px-3 py-1 text-sm font-medium text-cyan-900 dark:bg-background dark:text-cyan-200">
                                امتیاز آمادگی{' '}
                                {readinessScore.toLocaleString('fa-IR')}٪
                            </span>
                        </div>

                        <div className="mt-6 flex h-56 items-end gap-3 overflow-x-auto rounded-md bg-white/70 p-4 dark:bg-background/40">
                            {dashboardChartItems.map(([key, label, color]) => (
                                <div
                                    key={key}
                                    className="flex min-w-20 flex-1 flex-col items-center gap-2 text-center"
                                >
                                    <div className="flex h-40 items-end">
                                        <div
                                            className="w-9 rounded-t-md shadow-sm"
                                            style={{
                                                height: chartHeight(
                                                    stats[key],
                                                    chartMax,
                                                ),
                                                backgroundColor: color,
                                            }}
                                        />
                                    </div>
                                    <strong className="text-lg">
                                        {stats[key].toLocaleString('fa-IR')}
                                    </strong>
                                    <span className="text-xs leading-5 text-muted-foreground">
                                        {label}
                                    </span>
                                </div>
                            ))}
                        </div>
                    </article>

                    <article className="rounded-lg border border-amber-200 bg-amber-50/80 p-4 dark:border-amber-900/60 dark:bg-amber-950/20">
                        <div className="flex items-center gap-2">
                            <span className="inline-flex size-9 items-center justify-center rounded-md bg-amber-500 text-zinc-950">
                                <Gauge className="size-5" />
                            </span>
                            <div>
                                <h2 className="text-lg font-semibold">
                                    جریان مشارکت تا تحویل
                                </h2>
                                <p className="mt-1 text-sm text-muted-foreground">
                                    مسیر کلیدی از ورود کاربر تا تحویل پاداش.
                                </p>
                            </div>
                        </div>

                        <div className="mt-5 overflow-hidden rounded-full bg-white shadow-inner dark:bg-background/60">
                            <div className="flex h-5">
                                {flowChartItems.map(([key], index) => (
                                    <div
                                        key={key}
                                        style={{
                                            width: shareWidth(
                                                stats[key],
                                                flowTotal,
                                            ),
                                            backgroundColor: flowColors[index],
                                        }}
                                    />
                                ))}
                            </div>
                        </div>

                        <div className="mt-5 grid gap-2">
                            {flowChartItems.map(([key, label], index) => (
                                <div
                                    key={key}
                                    className="flex items-center justify-between gap-3 rounded-md bg-white/70 p-3 text-sm dark:bg-background/40"
                                >
                                    <span className="flex items-center gap-2">
                                        <span
                                            className="size-3 rounded-full"
                                            style={{
                                                backgroundColor:
                                                    flowColors[index],
                                            }}
                                        />
                                        {label}
                                    </span>
                                    <strong>
                                        {stats[key].toLocaleString('fa-IR')}
                                    </strong>
                                </div>
                            ))}
                        </div>

                        <div className="mt-4 rounded-md border border-amber-200 bg-white/70 p-3 text-sm leading-7 text-muted-foreground dark:border-amber-900/60 dark:bg-background/40">
                            <Activity className="ml-2 inline size-4 text-amber-700" />
                            اگر بازدید بالا باشد اما مصرف پاداش پایین بماند،
                            گلوگاه فروش در مسیر فروشگاه یا مشوق‌هاست.
                        </div>
                    </article>
                </section>

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
                        <h2 className="text-lg font-semibold">
                            هشدارهای عملیاتی
                        </h2>
                        <p className="mt-1 text-sm text-muted-foreground">
                            مواردی که ممکن است اجرای کمپین را کند کند یا نیازمند
                            پیگیری سریع ادمین باشد.
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
                                    className={
                                        alert.severity === 'attention'
                                            ? 'rounded-lg border border-amber-300 bg-amber-50 p-4 dark:border-amber-900/60 dark:bg-amber-950/30'
                                            : 'rounded-lg border border-sky-200 bg-sky-50 p-4 dark:border-sky-900/60 dark:bg-sky-950/30'
                                    }
                                >
                                    <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <h3 className="font-semibold">
                                                {alert.title}
                                            </h3>
                                            <p className="mt-1 text-sm leading-6 text-muted-foreground">
                                                {alert.message}
                                            </p>
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
                        <h2 className="text-lg font-semibold">
                            پایش اجرای کمپین‌ها
                        </h2>
                        <p className="mt-1 text-sm text-muted-foreground">
                            این بخش نشان می‌دهد کمپین فعال بعد از QR و ورود
                            کاربر چقدر به مأموریت، پاداش و پیشرفت واقعی رسیده
                            است.
                        </p>
                    </div>

                    {campaignPerformance.length === 0 ? (
                        <div className="p-8 text-center text-sm text-muted-foreground">
                            هنوز کمپین فعالی برای پایش اجرا وجود ندارد.
                        </div>
                    ) : (
                        <div className="grid gap-3 p-4 lg:grid-cols-2">
                            {campaignPerformance.map((campaign) => (
                                <article
                                    key={campaign.id}
                                    className="rounded-lg border border-border/80 bg-card/75 p-4 shadow-sm"
                                >
                                    <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <h3 className="font-semibold">
                                                {campaign.name}
                                            </h3>
                                            <p className="mt-1 text-sm text-muted-foreground">
                                                {campaign.venueName ??
                                                    'مکان ثبت نشده'}
                                            </p>
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
                                            [
                                                'تکمیل',
                                                campaign.completedMissions,
                                            ],
                                            ['پاداش', campaign.rewards],
                                            [
                                                'در انتظار تحویل',
                                                campaign.pendingRedemptions,
                                            ],
                                            [
                                                'تحویل‌شده',
                                                campaign.confirmedRedemptions,
                                            ],
                                        ].map(([label, value]) => (
                                            <div
                                                key={String(label)}
                                                className="rounded-md bg-muted/45 p-2"
                                            >
                                                <p className="text-xs text-muted-foreground">
                                                    {label}
                                                </p>
                                                <p className="mt-1 font-semibold">
                                                    {Number(
                                                        value,
                                                    ).toLocaleString('fa-IR')}
                                                </p>
                                            </div>
                                        ))}
                                    </div>

                                    <div className="mt-4">
                                        <div className="flex items-center justify-between gap-3 text-xs text-muted-foreground">
                                            <span>پیشرفت اجرای مأموریت‌ها</span>
                                            <span>
                                                {campaign.progressPercent.toLocaleString(
                                                    'fa-IR',
                                                )}
                                                ٪
                                            </span>
                                        </div>
                                        <div className="mt-2 h-2 overflow-hidden rounded-full bg-muted">
                                            <div
                                                className="h-full rounded-full bg-primary"
                                                style={{
                                                    width: `${campaign.progressPercent}%`,
                                                }}
                                            />
                                        </div>
                                    </div>

                                    <div className="mt-4 flex flex-wrap gap-2">
                                        <Link
                                            className="inline-flex h-9 items-center rounded-md border border-input bg-background px-3 text-sm font-medium hover:bg-accent hover:text-accent-foreground"
                                            href={`/admin/campaign-builder?campaign=${campaign.code}`}
                                        >
                                            ساخت کمپین
                                        </Link>
                                        <Link
                                            className="inline-flex h-9 items-center rounded-md border border-input bg-background px-3 text-sm font-medium hover:bg-accent hover:text-accent-foreground"
                                            href={`/admin/campaign-operations?campaign=${campaign.code}`}
                                        >
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
                        <h2 className="text-lg font-semibold">
                            آخرین وضعیت تحویل پاداش
                        </h2>
                        <p className="mt-1 text-sm text-muted-foreground">
                            این لیست برای پیگیری سریع کدهای تحویل، فروشگاه مسئول
                            و وضعیت مصرف پاداش است.
                        </p>
                    </div>

                    {latestRedemptions.length === 0 ? (
                        <div className="p-8 text-center text-sm text-muted-foreground">
                            هنوز پاداشی برای تحویل به فروشگاه یا واحد تحویل
                            نرسیده است.
                        </div>
                    ) : (
                        <div className="divide-y divide-border/70">
                            {latestRedemptions.map((redemption) => (
                                <article
                                    key={redemption.id}
                                    className="grid gap-3 p-4 lg:grid-cols-[1fr_auto]"
                                >
                                    <div>
                                        <div className="flex flex-wrap items-center gap-2">
                                            <p className="font-medium">
                                                {redemption.rewardName ??
                                                    'پاداش ثبت نشده'}
                                            </p>
                                            <span
                                                className={
                                                    redemption.status ===
                                                    'confirmed'
                                                        ? 'rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200'
                                                        : 'rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-900 dark:bg-amber-950 dark:text-amber-100'
                                                }
                                            >
                                                {redemptionStatusLabels[
                                                    redemption.status
                                                ] ?? redemption.status}
                                            </span>
                                        </div>
                                        <p className="mt-1 text-sm text-muted-foreground">
                                            {redemption.partnerName ??
                                                'واحد مرتبط ثبت نشده'}{' '}
                                            ·{' '}
                                            {redemption.visitorName ??
                                                'کاربر ثبت نشده'}{' '}
                                            ·{' '}
                                            {redemption.campaignName ??
                                                'کمپین ثبت نشده'}
                                        </p>
                                        <p
                                            className="mt-2 font-mono text-xs text-muted-foreground"
                                            dir="ltr"
                                        >
                                            {redemption.redemptionCode}
                                        </p>
                                    </div>
                                    <div className="flex flex-col gap-2 text-sm text-muted-foreground lg:items-end">
                                        <p>
                                            {formatDate(
                                                redemption.redeemedAt ??
                                                    redemption.createdAt ??
                                                    new Date().toISOString(),
                                            )}
                                        </p>
                                        {redemption.campaignCode ? (
                                            <Link
                                                className="inline-flex h-8 items-center rounded-md border border-input bg-background px-3 text-xs font-medium hover:bg-accent hover:text-accent-foreground"
                                                href={`/admin/campaign-operations?campaign=${redemption.campaignCode}`}
                                            >
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
            title: 'داشبورد',
            href: dashboard(),
        },
    ],
};
