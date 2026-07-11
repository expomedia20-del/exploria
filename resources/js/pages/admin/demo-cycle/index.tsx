import { Form, Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import {
    AlertTriangle,
    ArrowLeft,
    CheckCircle2,
    ClipboardCheck,
    Flag,
    ListChecks,
    Play,
    Route,
    Save,
} from 'lucide-react';

type DemoLink = {
    label: string;
    href: string;
};

type DemoStage = {
    title: string;
    goal: string;
    owner: string;
    status: string;
    links: DemoLink[];
    checks: string[];
};

type DemoSummary = {
    title: string;
    campaign: string;
    venue: string;
    status: string;
    stagesCount: number;
};

type StageMetric = {
    label: string;
    value: number;
};

type StageHealth = {
    stage: number;
    title: string;
    status: 'ready' | 'warning' | 'needs_work';
    metrics: StageMetric[];
    nextActions: string[];
    links: DemoLink[];
};

type CommercialPackage = {
    title: string;
    buyer: string;
    deliverable: string;
};

type OperationalChecklistItem = {
    key: string;
    label: string;
    owner: string;
    hint: string;
    complete: boolean;
    href?: string;
};

type OperationalChecklistGroup = {
    title: string;
    subtitle: string;
    items: OperationalChecklistItem[];
};

type OperationalChecklistEntry = {
    itemKey: string;
    status: 'done' | 'needs_action' | 'blocked';
    ownerName: string | null;
    note: string | null;
    dueDate: string | null;
    completedAt: string | null;
    updatedAt: string | null;
    updatedBy: string | null;
};

type DemoStressItem = {
    key: string;
    title: string;
    owner: string;
    complete: boolean;
    status: 'complete' | 'needs_action';
    detail: string;
    actionHref: string;
    metric: string;
};

type DemoStressPlan = {
    title: string;
    selectedCampaign: {
        code: string;
        name: string;
        blueprintCode: string | null;
    } | null;
    summary: {
        completeCount: number;
        totalCount: number;
        progress: number;
        riskLevel: 'low' | 'medium' | 'high';
    };
    nextAction: DemoStressItem | null;
    items: DemoStressItem[];
};

type ExecutionReport = {
    isExecuted: boolean;
    campaign: {
        id: string;
        code: string;
        name: string;
        blueprintCode: string | null;
    } | null;
    action: {
        label: string;
        href: string;
    };
    metrics: StageMetric[];
    timeline: {
        key: string;
        title: string;
        status: 'complete' | 'pending';
        label: string;
    }[];
    roi: {
        investment: number;
        estimatedValue: number;
        roiPercent: number;
        redemptionRate: number;
        completedMissions: number;
        narrative: string;
    };
    latestRedemption: {
        code: string;
        status: string;
        partnerName: string | null;
        rewardName: string | null;
        redeemedAt: string | null;
    } | null;
};

type Props = {
    summary: DemoSummary;
    stages: DemoStage[];
    stageHealth: StageHealth[];
    demoStressPlan: DemoStressPlan | null;
    executionReport: ExecutionReport;
    commercialPackages: CommercialPackage[];
    operationalChecklistEntries: OperationalChecklistEntry[];
    canManageOperationalChecklist: boolean;
};

const statusLabel = {
    ready: 'آماده',
    warning: 'نیازمند کنترل',
    needs_work: 'نیازمند تکمیل',
};

const statusClassName = {
    ready: 'bg-emerald-50 text-emerald-900',
    warning: 'bg-amber-50 text-amber-900',
    needs_work: 'bg-rose-50 text-rose-900',
};

const stressStatusLabel = {
    complete: 'تکمیل',
    needs_action: 'نیازمند اقدام',
};

const stressStatusClassName = {
    complete: 'bg-emerald-50 text-emerald-900',
    needs_action: 'bg-rose-50 text-rose-900',
};

const operationalStatusLabel = {
    done: 'انجام شد',
    needs_action: 'نیازمند اقدام',
    blocked: 'مسدود',
};

const operationalStatusClassName = {
    done: 'bg-emerald-50 text-emerald-900',
    needs_action: 'bg-amber-50 text-amber-950',
    blocked: 'bg-rose-50 text-rose-900',
};

const riskLabel = {
    low: 'ریسک پایین',
    medium: 'ریسک متوسط',
    high: 'ریسک بالا',
};

const demoCycleHeroImage =
    '/images/ecopark/proposal/ecopark-roadmap-night-21-9.jpg';

const stageSurfaceClassName = [
    'border-cyan-200 bg-cyan-50/70 text-cyan-950',
    'border-emerald-200 bg-emerald-50/70 text-emerald-950',
    'border-amber-200 bg-amber-50/70 text-amber-950',
    'border-rose-200 bg-rose-50/70 text-rose-950',
    'border-indigo-200 bg-indigo-50/70 text-indigo-950',
];

const operationalGroupSurfaceClassName = [
    'border-cyan-200 bg-cyan-50/60',
    'border-amber-200 bg-amber-50/60',
    'border-emerald-200 bg-emerald-50/60',
];

const operationalItemToneClassName = {
    done: 'border-r-emerald-400 bg-emerald-50/40',
    needs_action: 'border-r-amber-400 bg-amber-50/45',
    blocked: 'border-r-rose-400 bg-rose-50/45',
};

const stressItemSurfaceClassName = [
    'border-sky-200 bg-sky-50/55',
    'border-lime-200 bg-lime-50/55',
    'border-amber-200 bg-amber-50/55',
    'border-rose-200 bg-rose-50/55',
];

const reportMetricSurfaceClassName = [
    'bg-cyan-50/70 text-cyan-950',
    'bg-emerald-50/70 text-emerald-950',
    'bg-amber-50/70 text-amber-950',
    'bg-indigo-50/70 text-indigo-950',
];

const commercialPackageSurfaceClassName = [
    'border-emerald-200 bg-emerald-50/60',
    'border-amber-200 bg-amber-50/60',
    'border-sky-200 bg-sky-50/60',
];

export default function DemoCycleIndex({
    summary,
    stages,
    stageHealth,
    demoStressPlan,
    executionReport,
    commercialPackages,
    operationalChecklistEntries,
    canManageOperationalChecklist,
}: Props) {
    const healthByStage = new Map(
        stageHealth.map((item) => [item.stage, item]),
    );
    const readyStages = stageHealth.filter(
        (stage) => stage.status === 'ready',
    ).length;
    const stressItemsByKey = new Map(
        (demoStressPlan?.items ?? []).map((item) => [item.key, item]),
    );
    const operationalEntriesByKey = new Map(
        operationalChecklistEntries.map((item) => [item.itemKey, item]),
    );
    const stressComplete = (key: string) =>
        stressItemsByKey.get(key)?.complete ?? false;
    const allStagesReady =
        summary.stagesCount > 0 && readyStages === summary.stagesCount;
    const operationalChecklist: OperationalChecklistGroup[] = [
        {
            title: 'چک‌لیست ۷۲ ساعت قبل از اجرا',
            subtitle: 'آماده‌سازی مکان، کمپین، QR، پاداش و نقش‌ها',
            items: [
                {
                    key: 'venue_route',
                    label: 'پروفایل مکان، زون‌ها و مسیر پایلوت تایید شده‌اند',
                    owner: 'مدیر پروژه / مدیر مکان',
                    hint: 'مبنای اجرای میدانی و روایت دمو باید قبل از روز اجرا روشن باشد.',
                    complete:
                        stressComplete('venue') &&
                        stressComplete('route_operations'),
                    href: '/admin/venues',
                },
                {
                    key: 'campaign_blueprint',
                    label: 'کمپین و الگوی متصل برای اکوپارک انتخاب شده‌اند',
                    owner: 'ادمین عملیات',
                    hint: 'کمپین باید هدف، روایت و blueprint قابل توضیح داشته باشد.',
                    complete: stressComplete('blueprint'),
                    href: demoStressPlan?.selectedCampaign
                        ? `/admin/campaign-builder?campaign=${demoStressPlan.selectedCampaign.code}`
                        : '/admin/campaigns',
                },
                {
                    key: 'qr_entry',
                    label: 'QR، ورود کاربر و صفحه فرود روی موبایل تست شده‌اند',
                    owner: 'ادمین / اپراتور میدانی',
                    hint: 'QR باید کاربر را به کمپین درست و شروع تجربه برساند.',
                    complete: stressComplete('qr_entry'),
                    href: '/admin/qr-codes',
                },
                {
                    key: 'mission_reward_inventory',
                    label: 'ماموریت، گنج، پاداش و موجودی قابل مصرف آماده‌اند',
                    owner: 'ادمین / فروشگاه / اسپانسر',
                    hint: 'حداقل یک مسیر کامل از ماموریت تا صدور پاداش باید قابل اجرا باشد.',
                    complete:
                        stressComplete('layered_incentives') &&
                        stressComplete('inventory'),
                    href: '/admin/missions',
                },
            ],
        },
        {
            title: 'چک‌لیست روز اجرا',
            subtitle: 'کنترل مسیر واقعی کاربر و مصرف پاداش',
            items: [
                {
                    key: 'stress_data',
                    label: 'داده دمو و سناریوی stress-demo در دسترس است',
                    owner: 'مدیر پروژه اکسپلوریا',
                    hint: 'اگر داده ناقص باشد، اجرای end-to-end باید از همین صفحه دوباره ساخته شود.',
                    complete: Boolean(demoStressPlan),
                    href: '/admin/demo-cycle',
                },
                {
                    key: 'role_briefing',
                    label: 'اپراتور، شریک، رواق و پشتیبانی نقش خود را می‌دانند',
                    owner: 'مدیر عملیات',
                    hint: 'ابهام نقش در روز اجرا باید به عنوان ریسک عملیاتی دیده شود.',
                    complete:
                        allStagesReady &&
                        stressComplete('partner_mix') &&
                        stressComplete('sponsor_mix'),
                    href: '/admin/access-scopes',
                },
                {
                    key: 'visitor_execution',
                    label: 'کاربر از QR وارد شده و حداقل یک ماموریت را کامل کرده است',
                    owner: 'اپراتور میدانی',
                    hint: 'این آیتم نقطه اثبات تجربه واقعی بازدیدکننده است.',
                    complete: stressComplete('visitor_execution'),
                    href: '/admin/campaign-operations',
                },
                {
                    key: 'reward_redemption',
                    label: 'مصرف پاداش توسط شریک تایید یا ثبت شده است',
                    owner: 'فروشگاه / شریک',
                    hint: 'بدون مصرف تاییدشده، گزارش فروش و ROI ناقص می‌ماند.',
                    complete: stressComplete('redemption'),
                    href: '/partner/dashboard',
                },
            ],
        },
        {
            title: 'چک‌لیست خروجی فروش',
            subtitle: 'ROI، شواهد تبلیغات، بسته قیمت‌گذاری و مرزهای Scope',
            items: [
                {
                    key: 'roi_report',
                    label: 'گزارش ROI با اسکن، ماموریت و مصرف پاداش قابل نمایش است',
                    owner: 'ادمین / تیم فروش',
                    hint: 'گزارش باید ارزش مکان، شریک و اسپانسر را جداگانه توضیح دهد.',
                    complete:
                        executionReport.isExecuted &&
                        executionReport.roi.roiPercent > 0,
                    href: '/admin/commercialization',
                },
                {
                    key: 'sponsor_media_evidence',
                    label: 'اثر اسپانسر، تبلیغات و نمایشگرها در گزارش دیده می‌شود',
                    owner: 'ادمین / اسپانسر',
                    hint: 'این بخش دمو را از ابزار داخلی به پیشنهاد درآمدی تبدیل می‌کند.',
                    complete: stressComplete('reporting'),
                    href: '/admin/commercialization',
                },
                {
                    key: 'sales_package',
                    label: 'پکیج فروش برای مکان، اسپانسر و شریک آماده ارائه است',
                    owner: 'تیم فروش',
                    hint: 'خروجی تجاری باید بعد از اجرای دمو به پیشنهاد قابل مذاکره وصل شود.',
                    complete: commercialPackages.length >= 3,
                    href: '/admin/commercialization',
                },
                {
                    key: 'scope_guardrail',
                    label: 'موارد خارج از دامنه روز اجرا مشخص و کنترل شده‌اند',
                    owner: 'مدیر پروژه',
                    hint: 'تسویه پیچیده، چندمکانی همزمان، قرعه‌کشی عمومی و offline sync در MVP اجرا نمی‌شوند.',
                    complete: true,
                },
            ],
        },
    ];
    const operationalTotal = operationalChecklist.reduce(
        (total, group) => total + group.items.length,
        0,
    );
    const operationalItems = operationalChecklist.flatMap((group) =>
        group.items.map((item) => ({
            ...item,
            groupTitle: group.title,
            entry: operationalEntriesByKey.get(item.key) ?? null,
        })),
    );
    const operationalItemStatus = (item: OperationalChecklistItem) =>
        operationalEntriesByKey.get(item.key)?.status ??
        (item.complete ? 'done' : 'needs_action');
    const operationalComplete = operationalChecklist.reduce(
        (total, group) =>
            total +
            group.items.filter((item) => operationalItemStatus(item) === 'done')
                .length,
        0,
    );
    const operationalProgress =
        operationalTotal > 0
            ? Math.round((operationalComplete / operationalTotal) * 100)
            : 0;
    const immediateAction =
        operationalItems.find((item) => item.entry?.status === 'blocked') ??
        operationalItems.find(
            (item) => operationalItemStatus(item) === 'needs_action',
        ) ??
        null;

    return (
        <>
            <Head title="چرخه دمو اکوپارک" />

            <main
                className="space-y-4 bg-[linear-gradient(180deg,#f8fafc_0%,#f6f8f1_44%,#f8fafc_100%)] p-4 dark:bg-background"
                dir="rtl"
            >
                <section className="relative overflow-hidden rounded-lg border border-sidebar-border/70 bg-zinc-950 text-white dark:border-sidebar-border">
                    <img
                        src={demoCycleHeroImage}
                        alt=""
                        className="absolute inset-0 h-full w-full object-cover opacity-45"
                    />
                    <div className="absolute inset-0 bg-gradient-to-l from-zinc-950 via-zinc-950/85 to-zinc-950/50" />
                    <div className="relative grid gap-6 p-5 lg:grid-cols-[1fr_0.82fr] lg:p-7">
                        <div>
                            <p className="text-sm text-cyan-200">
                                نقشه اجرای دمو از صفر تا گزارش فروش
                            </p>
                            <h1 className="mt-3 max-w-3xl text-3xl leading-tight font-semibold md:text-4xl">
                                {summary.title}
                            </h1>
                            <p className="mt-4 max-w-4xl text-sm leading-7 text-zinc-200">
                                این صفحه، چرخه کامل دمو را به ۵ مرحله قابل اجرا
                                تبدیل می‌کند: آماده‌سازی سناریو، داده و دسترسی،
                                اجرای کاربر، مصرف پاداش و تبلیغ، سپس گزارش
                                تجاری. بازی آنلاین در این نسخه عمدا از ارزیابی
                                اصلی جدا نگه داشته شده است.
                            </p>
                            <div className="mt-6 flex flex-wrap gap-3">
                                <Form
                                    action={executionReport.action.href}
                                    method="post"
                                    options={{ preserveScroll: true }}
                                >
                                    {({ processing }) => (
                                        <Button
                                            disabled={processing}
                                            className="bg-cyan-300 text-zinc-950 hover:bg-cyan-200"
                                        >
                                            <Play className="size-4" />
                                            {processing
                                                ? 'در حال اجرا'
                                                : executionReport.action.label}
                                        </Button>
                                    )}
                                </Form>
                                <Link
                                    href="/admin/commercialization"
                                    className="inline-flex h-10 items-center gap-2 rounded-md border border-white/25 px-3 text-sm font-medium hover:bg-white/10"
                                >
                                    خروجی فروش
                                    <ArrowLeft className="size-4" />
                                </Link>
                            </div>
                        </div>

                        <div className="grid content-end gap-3 sm:grid-cols-3 lg:grid-cols-1">
                            {[
                                ['کمپین', summary.campaign],
                                ['مکان', summary.venue],
                                [
                                    'مرحله آماده',
                                    `${readyStages.toLocaleString('fa-IR')} از ${summary.stagesCount.toLocaleString('fa-IR')}`,
                                ],
                            ].map(([label, value]) => (
                                <div
                                    key={label}
                                    className="rounded-lg border border-white/15 bg-white/10 p-4 text-sm backdrop-blur-sm"
                                >
                                    <span className="text-zinc-300">
                                        {label}
                                    </span>
                                    <strong className="mt-2 block text-base text-white">
                                        {value}
                                    </strong>
                                </div>
                            ))}
                            <div className="rounded-lg border border-amber-300/35 bg-amber-300/15 p-4 text-sm text-amber-100 backdrop-blur-sm">
                                <span>وضعیت کلی</span>
                                <strong className="mt-2 block text-base">
                                    {summary.status}
                                </strong>
                            </div>
                        </div>
                    </div>
                </section>

                <section className="grid gap-3 md:grid-cols-5">
                    {stages.map((stage, index) => {
                        const stageNumber = index + 1;
                        const currentStatus =
                            healthByStage.get(stageNumber)?.status ?? 'ready';

                        return (
                            <article
                                key={stage.title}
                                className={`rounded-lg border border-t-4 p-3 shadow-sm transition-shadow hover:shadow-md dark:border-sidebar-border ${stageSurfaceClassName[index % stageSurfaceClassName.length]}`}
                            >
                                <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                    <Flag className="size-4 text-primary" />
                                    مرحله {stageNumber}
                                </div>
                                <h2 className="mt-2 font-semibold">
                                    {stage.title}
                                </h2>
                                <p className="mt-2 text-sm leading-7 text-muted-foreground">
                                    {stage.goal}
                                </p>
                                <div
                                    className={`mt-3 inline-flex rounded-full px-3 py-1 text-xs font-medium ${statusClassName[currentStatus]}`}
                                >
                                    {statusLabel[currentStatus]}
                                </div>
                            </article>
                        );
                    })}
                </section>

                <section className="rounded-lg border border-cyan-100 bg-gradient-to-br from-white via-cyan-50/60 to-emerald-50/45 p-4 shadow-sm dark:border-sidebar-border dark:from-card dark:via-card dark:to-card">
                    <div className="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <div className="flex items-center gap-2">
                                <ListChecks className="size-5 text-primary" />
                                <h2 className="text-xl font-semibold">
                                    چک‌لیست عملیاتی Playbook پایلوت
                                </h2>
                            </div>
                            <p className="mt-2 max-w-4xl text-sm leading-7 text-muted-foreground">
                                این بخش Playbook اجرایی اکوپارک را به آیتم‌های
                                قابل مشاهده و قابل کنترل تبدیل می‌کند؛ از آمادگی
                                ۷۲ ساعت قبل از اجرا تا روز اجرا و خروجی فروش.
                            </p>
                        </div>
                        <div className="grid min-w-56 gap-1 rounded-md bg-muted/40 p-3 text-sm">
                            <span className="text-muted-foreground">
                                پیشرفت عملیاتی
                            </span>
                            <strong className="text-2xl">
                                {operationalProgress.toLocaleString('fa-IR')}٪
                            </strong>
                            <span className="text-xs text-muted-foreground">
                                {operationalComplete.toLocaleString('fa-IR')} از{' '}
                                {operationalTotal.toLocaleString('fa-IR')} مورد
                            </span>
                        </div>
                    </div>

                    {immediateAction ? (
                        <div className="mt-4 rounded-md border border-amber-200 bg-amber-50 p-3 text-sm leading-7 text-amber-950">
                            <strong>اقدام فوری بعدی:</strong>{' '}
                            {immediateAction.label}
                            <span className="mx-2 text-amber-700">·</span>
                            {immediateAction.groupTitle}
                            <span className="mx-2 text-amber-700">·</span>
                            مسئول پیشنهادی: {immediateAction.owner}
                        </div>
                    ) : (
                        <div className="mt-4 rounded-md border border-emerald-200 bg-emerald-50 p-3 text-sm leading-7 text-emerald-950">
                            همه آیتم‌های عملیاتی در وضعیت انجام‌شده هستند و مسیر
                            پایلوت برای گزارش فروش آماده است.
                        </div>
                    )}

                    <div className="mt-4 grid gap-4 xl:grid-cols-3">
                        {operationalChecklist.map((group, groupIndex) => (
                            <div
                                key={group.title}
                                className={`space-y-3 rounded-lg border p-3 ${operationalGroupSurfaceClassName[groupIndex % operationalGroupSurfaceClassName.length]}`}
                            >
                                <div>
                                    <h3 className="font-semibold">
                                        {group.title}
                                    </h3>
                                    <p className="mt-1 text-sm leading-6 text-muted-foreground">
                                        {group.subtitle}
                                    </p>
                                </div>

                                <div className="space-y-2">
                                    {group.items.map((item) => {
                                        const entry =
                                            operationalEntriesByKey.get(
                                                item.key,
                                            );
                                        const status =
                                            entry?.status ??
                                            (item.complete
                                                ? 'done'
                                                : 'needs_action');
                                        const isDone = status === 'done';
                                        const isBlocked = status === 'blocked';

                                        return (
                                            <div
                                                key={item.label}
                                                className={`rounded-md border border-r-4 p-3 shadow-sm ${operationalItemToneClassName[status]}`}
                                            >
                                                <div className="flex items-start justify-between gap-3">
                                                    <div className="flex gap-2">
                                                        {isDone ? (
                                                            <CheckCircle2 className="mt-1 size-4 shrink-0 text-emerald-600" />
                                                        ) : (
                                                            <AlertTriangle
                                                                className={`mt-1 size-4 shrink-0 ${
                                                                    isBlocked
                                                                        ? 'text-rose-600'
                                                                        : 'text-amber-600'
                                                                }`}
                                                            />
                                                        )}
                                                        <div>
                                                            <p className="text-sm leading-6 font-medium">
                                                                {item.label}
                                                            </p>
                                                            <p className="mt-1 text-xs text-muted-foreground">
                                                                مسئول:{' '}
                                                                {item.owner}
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <span
                                                        className={`shrink-0 rounded-full px-2.5 py-1 text-xs font-medium ${operationalStatusClassName[status]}`}
                                                    >
                                                        {
                                                            operationalStatusLabel[
                                                                status
                                                            ]
                                                        }
                                                    </span>
                                                </div>
                                                <p className="mt-3 text-sm leading-7 text-muted-foreground">
                                                    {item.hint}
                                                </p>
                                                <div className="mt-3 rounded-md bg-muted/40 p-2 text-xs leading-6 text-muted-foreground">
                                                    وضعیت سیستمی:{' '}
                                                    {item.complete
                                                        ? 'آماده'
                                                        : 'نیازمند تکمیل'}
                                                    {entry?.updatedBy ? (
                                                        <>
                                                            <span className="mx-2">
                                                                ·
                                                            </span>
                                                            آخرین ثبت:{' '}
                                                            {entry.updatedBy}
                                                        </>
                                                    ) : null}
                                                </div>
                                                {canManageOperationalChecklist ? (
                                                    <Form
                                                        action="/admin/demo-cycle/checklist"
                                                        method="post"
                                                        options={{
                                                            preserveScroll: true,
                                                        }}
                                                    >
                                                        {({ processing }) => (
                                                            <div className="mt-3 grid gap-2">
                                                                <input
                                                                    type="hidden"
                                                                    name="item_key"
                                                                    value={
                                                                        item.key
                                                                    }
                                                                />
                                                                <div className="grid gap-2 sm:grid-cols-2">
                                                                    <label className="grid gap-1 text-xs font-medium text-muted-foreground">
                                                                        وضعیت
                                                                        <select
                                                                            name="status"
                                                                            defaultValue={
                                                                                status
                                                                            }
                                                                            className="h-9 rounded-md border border-input bg-background px-3 text-sm text-foreground"
                                                                        >
                                                                            <option value="done">
                                                                                انجام
                                                                                شد
                                                                            </option>
                                                                            <option value="needs_action">
                                                                                نیازمند
                                                                                اقدام
                                                                            </option>
                                                                            <option value="blocked">
                                                                                مسدود
                                                                            </option>
                                                                        </select>
                                                                    </label>
                                                                    <label className="grid gap-1 text-xs font-medium text-muted-foreground">
                                                                        تاریخ
                                                                        هدف
                                                                        <input
                                                                            type="date"
                                                                            name="due_date"
                                                                            defaultValue={
                                                                                entry?.dueDate ??
                                                                                ''
                                                                            }
                                                                            className="h-9 rounded-md border border-input bg-background px-3 text-sm text-foreground"
                                                                        />
                                                                    </label>
                                                                </div>
                                                                <label className="grid gap-1 text-xs font-medium text-muted-foreground">
                                                                    مسئول اجرا
                                                                    <input
                                                                        name="owner_name"
                                                                        defaultValue={
                                                                            entry?.ownerName ??
                                                                            item.owner
                                                                        }
                                                                        className="h-9 rounded-md border border-input bg-background px-3 text-sm text-foreground"
                                                                    />
                                                                </label>
                                                                <label className="grid gap-1 text-xs font-medium text-muted-foreground">
                                                                    یادداشت
                                                                    عملیات
                                                                    <textarea
                                                                        name="note"
                                                                        defaultValue={
                                                                            entry?.note ??
                                                                            ''
                                                                        }
                                                                        className="min-h-16 rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground"
                                                                        placeholder="مانع، تصمیم، نتیجه تماس یا اقدام بعدی را ثبت کنید."
                                                                    />
                                                                </label>
                                                                <Button
                                                                    type="submit"
                                                                    disabled={
                                                                        processing
                                                                    }
                                                                    className="h-9 justify-self-start"
                                                                >
                                                                    <Save className="size-4" />
                                                                    {processing
                                                                        ? 'در حال ذخیره'
                                                                        : 'ذخیره وضعیت'}
                                                                </Button>
                                                            </div>
                                                        )}
                                                    </Form>
                                                ) : null}
                                                {item.href ? (
                                                    <Link
                                                        href={item.href}
                                                        className="mt-3 inline-flex items-center gap-2 rounded-md border border-input bg-card px-3 py-2 text-sm font-medium"
                                                    >
                                                        مشاهده مسیر
                                                        <ArrowLeft className="size-4" />
                                                    </Link>
                                                ) : null}
                                            </div>
                                        );
                                    })}
                                </div>
                            </div>
                        ))}
                    </div>
                </section>

                <section className="rounded-lg border border-sky-200 bg-gradient-to-br from-sky-50/80 via-white to-indigo-50/70 p-4 shadow-sm dark:border-sidebar-border dark:from-card dark:via-card dark:to-card">
                    <div className="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p className="text-sm text-muted-foreground">
                                اجرای عملیاتی دمو
                            </p>
                            <h2 className="mt-1 text-xl font-semibold">
                                از QR تا مصرف پاداش و ROI
                            </h2>
                            <p className="mt-2 max-w-4xl text-sm leading-7 text-muted-foreground">
                                این اجرا داده‌های واقعی دمو را می‌سازد یا
                                به‌روزرسانی می‌کند: مکان، الگو، کمپین، QR، مسیر
                                کاربر، ماموریت‌ها، گنج، پاداش اسپانسری، مصرف
                                فروشگاهی و گزارش عددی ROI.
                            </p>
                        </div>
                        <Form
                            action={executionReport.action.href}
                            method="post"
                            options={{ preserveScroll: true }}
                        >
                            {({ processing }) => (
                                <Button disabled={processing}>
                                    <Play className="size-4" />
                                    {processing
                                        ? 'در حال اجرا'
                                        : executionReport.action.label}
                                </Button>
                            )}
                        </Form>
                    </div>

                    <div className="mt-4 grid gap-3 lg:grid-cols-[1.2fr_0.8fr]">
                        <div className="rounded-md border border-sky-200 bg-white/85 p-3 shadow-sm">
                            <div className="flex flex-wrap items-center justify-between gap-3">
                                <h3 className="font-semibold">
                                    خط اجرای end-to-end
                                </h3>
                                <span
                                    className={`rounded-full px-3 py-1 text-xs font-medium ${
                                        executionReport.isExecuted
                                            ? 'bg-emerald-50 text-emerald-900'
                                            : 'bg-amber-50 text-amber-900'
                                    }`}
                                >
                                    {executionReport.isExecuted
                                        ? 'اجرا شده'
                                        : 'منتظر اجرا'}
                                </span>
                            </div>
                            <div className="mt-3 grid gap-2 md:grid-cols-3">
                                {executionReport.timeline.map((item) => (
                                    <div
                                        key={item.key}
                                        className={`flex min-h-16 items-start gap-2 rounded-md p-3 text-sm ${item.status === 'complete' ? 'bg-emerald-50/75' : 'bg-slate-100/80'}`}
                                    >
                                        <CheckCircle2
                                            className={`mt-1 size-4 shrink-0 ${
                                                item.status === 'complete'
                                                    ? 'text-emerald-600'
                                                    : 'text-muted-foreground'
                                            }`}
                                        />
                                        <div>
                                            <p className="font-medium">
                                                {item.title}
                                            </p>
                                            <p className="mt-1 text-xs text-muted-foreground">
                                                {item.label}
                                            </p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>

                        <div className="rounded-md border border-indigo-200 bg-indigo-50/65 p-3 shadow-sm">
                            <h3 className="font-semibold">گزارش ROI</h3>
                            <p className="mt-2 text-sm leading-7 text-muted-foreground">
                                {executionReport.roi.narrative}
                            </p>
                            <div className="mt-3 grid gap-2 text-sm">
                                <div className="flex justify-between gap-3">
                                    <span className="text-muted-foreground">
                                        سرمایه‌گذاری
                                    </span>
                                    <strong>
                                        {executionReport.roi.investment.toLocaleString(
                                            'fa-IR',
                                        )}{' '}
                                        ریال
                                    </strong>
                                </div>
                                <div className="flex justify-between gap-3">
                                    <span className="text-muted-foreground">
                                        ارزش تخمینی
                                    </span>
                                    <strong>
                                        {executionReport.roi.estimatedValue.toLocaleString(
                                            'fa-IR',
                                        )}{' '}
                                        ریال
                                    </strong>
                                </div>
                                <div className="flex justify-between gap-3">
                                    <span className="text-muted-foreground">
                                        ROI
                                    </span>
                                    <strong>
                                        {executionReport.roi.roiPercent.toLocaleString(
                                            'fa-IR',
                                        )}
                                        ٪
                                    </strong>
                                </div>
                                <div className="flex justify-between gap-3">
                                    <span className="text-muted-foreground">
                                        نرخ مصرف پاداش
                                    </span>
                                    <strong>
                                        {executionReport.roi.redemptionRate.toLocaleString(
                                            'fa-IR',
                                        )}
                                        ٪
                                    </strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="mt-4 grid gap-2 md:grid-cols-4">
                        {executionReport.metrics.map((metric, index) => (
                            <div
                                key={metric.label}
                                className={`rounded-md p-3 shadow-sm ${reportMetricSurfaceClassName[index % reportMetricSurfaceClassName.length]}`}
                            >
                                <p className="text-xs text-muted-foreground">
                                    {metric.label}
                                </p>
                                <p className="mt-1 text-xl font-semibold">
                                    {metric.value.toLocaleString('fa-IR')}
                                </p>
                            </div>
                        ))}
                    </div>

                    {executionReport.latestRedemption ? (
                        <div className="mt-4 rounded-md border border-emerald-200 bg-emerald-50 p-3 text-sm leading-7 text-emerald-950">
                            آخرین مصرف پاداش:{' '}
                            {executionReport.latestRedemption.rewardName ??
                                'پاداش دمو'}{' '}
                            در{' '}
                            {executionReport.latestRedemption.partnerName ??
                                'فروشگاه دمو'}{' '}
                            با کد{' '}
                            <span dir="ltr">
                                {executionReport.latestRedemption.code}
                            </span>
                        </div>
                    ) : null}
                </section>

                {demoStressPlan ? (
                    <section className="rounded-lg border border-amber-200 bg-gradient-to-br from-amber-50/80 via-white to-lime-50/65 p-4 shadow-sm dark:border-sidebar-border dark:from-card dark:via-card dark:to-card">
                        <div className="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p className="text-sm text-muted-foreground">
                                    مسیر کنترل فشار دمو
                                </p>
                                <h2 className="mt-1 text-xl font-semibold">
                                    {demoStressPlan.title}
                                </h2>
                                <p className="mt-2 max-w-4xl text-sm leading-7 text-muted-foreground">
                                    این بخش مسیر فروش‌پذیر دمو را ریزتر از
                                    چک‌لیست پنج‌مرحله‌ای نشان می‌دهد: ارزیابی
                                    مکان، انتخاب الگو، ساخت کمپین، QR، اجرای
                                    کاربر، ماموریت، گنج، پاداش فروشگاهی و
                                    اسپانسری، تایید مصرف فروشگاه و گزارش ROI.
                                </p>
                            </div>
                            <div className="grid min-w-64 gap-2 rounded-md border border-amber-200 bg-white/85 p-3 text-sm shadow-sm">
                                <div className="flex justify-between gap-3">
                                    <span className="text-muted-foreground">
                                        پیشرفت
                                    </span>
                                    <strong>
                                        {demoStressPlan.summary.progress.toLocaleString(
                                            'fa-IR',
                                        )}
                                        ٪
                                    </strong>
                                </div>
                                <div className="flex justify-between gap-3">
                                    <span className="text-muted-foreground">
                                        آیتم‌های تکمیل‌شده
                                    </span>
                                    <strong>
                                        {demoStressPlan.summary.completeCount.toLocaleString(
                                            'fa-IR',
                                        )}{' '}
                                        از{' '}
                                        {demoStressPlan.summary.totalCount.toLocaleString(
                                            'fa-IR',
                                        )}
                                    </strong>
                                </div>
                                <div className="flex justify-between gap-3">
                                    <span className="text-muted-foreground">
                                        ریسک اجرا
                                    </span>
                                    <strong>
                                        {
                                            riskLabel[
                                                demoStressPlan.summary.riskLevel
                                            ]
                                        }
                                    </strong>
                                </div>
                            </div>
                        </div>

                        {demoStressPlan.selectedCampaign ? (
                            <div className="mt-4 grid gap-3 rounded-md border border-lime-200 bg-lime-50/60 p-3 text-sm md:grid-cols-3">
                                <div>
                                    <p className="text-muted-foreground">
                                        کمپین انتخاب‌شده
                                    </p>
                                    <p className="mt-1 font-medium">
                                        {demoStressPlan.selectedCampaign.name}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-muted-foreground">
                                        کد کمپین
                                    </p>
                                    <p className="mt-1 font-medium" dir="ltr">
                                        {demoStressPlan.selectedCampaign.code}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-muted-foreground">
                                        الگوی متصل
                                    </p>
                                    <p className="mt-1 font-medium" dir="ltr">
                                        {demoStressPlan.selectedCampaign
                                            .blueprintCode ?? 'ثبت نشده'}
                                    </p>
                                </div>
                            </div>
                        ) : null}

                        {demoStressPlan.nextAction ? (
                            <div className="mt-4 rounded-md border border-amber-200 bg-amber-50 p-3 text-sm leading-7 text-amber-950">
                                اقدام بعدی مسیر کامل:{' '}
                                {demoStressPlan.nextAction.detail}
                            </div>
                        ) : (
                            <div className="mt-4 rounded-md border border-emerald-200 bg-emerald-50 p-3 text-sm leading-7 text-emerald-950">
                                مسیر کامل دمو از ارزیابی مکان تا گزارش ROI برای
                                ارائه قابل فروش آماده است.
                            </div>
                        )}

                        <div className="mt-4 grid gap-3 lg:grid-cols-2">
                            {demoStressPlan.items.map((item, index) => (
                                <article
                                    key={item.key}
                                    className={`rounded-md border p-3 shadow-sm ${stressItemSurfaceClassName[index % stressItemSurfaceClassName.length]}`}
                                >
                                    <div className="flex flex-wrap items-start justify-between gap-3">
                                        <div>
                                            <p className="text-xs text-muted-foreground">
                                                گام{' '}
                                                {(index + 1).toLocaleString(
                                                    'fa-IR',
                                                )}{' '}
                                                · {item.owner}
                                            </p>
                                            <h3 className="mt-1 font-semibold">
                                                {item.title}
                                            </h3>
                                        </div>
                                        <span
                                            className={`rounded-full px-3 py-1 text-xs font-medium ${stressStatusClassName[item.status]}`}
                                        >
                                            {stressStatusLabel[item.status]}
                                        </span>
                                    </div>
                                    <p className="mt-3 text-sm leading-7 text-muted-foreground">
                                        {item.detail}
                                    </p>
                                    <div className="mt-3 flex flex-wrap items-center justify-between gap-3">
                                        <span className="inline-flex items-center gap-2 rounded-md bg-muted px-3 py-2 text-sm font-medium">
                                            <CheckCircle2 className="size-4 text-primary" />
                                            {item.metric}
                                        </span>
                                        <Link
                                            href={item.actionHref}
                                            className="inline-flex items-center gap-2 rounded-md border border-input bg-card px-3 py-2 text-sm font-medium"
                                        >
                                            مشاهده مسیر
                                            <ArrowLeft className="size-4" />
                                        </Link>
                                    </div>
                                </article>
                            ))}
                        </div>
                    </section>
                ) : null}

                <section className="grid gap-3 xl:grid-cols-2">
                    {stageHealth.map((stage, index) => (
                        <article
                            key={stage.stage}
                            className={`rounded-lg border border-t-4 p-4 shadow-sm dark:border-sidebar-border ${stageSurfaceClassName[index % stageSurfaceClassName.length]}`}
                        >
                            <div className="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p className="text-sm text-muted-foreground">
                                        مرحله {stage.stage}
                                    </p>
                                    <h2 className="mt-1 text-lg font-semibold">
                                        {stage.title}
                                    </h2>
                                </div>
                                <span
                                    className={`rounded-full px-3 py-1 text-xs font-medium ${statusClassName[stage.status]}`}
                                >
                                    {statusLabel[stage.status]}
                                </span>
                            </div>

                            <div className="mt-4 grid gap-2 md:grid-cols-3">
                                {stage.metrics.map((metric) => (
                                    <div
                                        key={metric.label}
                                        className="rounded-md border border-white/70 bg-white/70 p-3 shadow-sm"
                                    >
                                        <p className="text-xs text-muted-foreground">
                                            {metric.label}
                                        </p>
                                        <p className="mt-1 text-xl font-semibold">
                                            {metric.value.toLocaleString(
                                                'fa-IR',
                                            )}
                                        </p>
                                    </div>
                                ))}
                            </div>

                            <div className="mt-4 rounded-md bg-white/60 p-3">
                                <h3 className="text-sm font-semibold">
                                    اقدام بعدی
                                </h3>
                                {stage.nextActions.length > 0 ? (
                                    <ul className="mt-2 space-y-2 text-sm leading-7 text-muted-foreground">
                                        {stage.nextActions.map((action) => (
                                            <li key={action}>• {action}</li>
                                        ))}
                                    </ul>
                                ) : (
                                    <p className="mt-2 text-sm text-muted-foreground">
                                        مورد بحرانی ثبت نشده است؛ این مرحله برای
                                        دمو قابل عبور است.
                                    </p>
                                )}
                            </div>

                            <div className="mt-4 flex flex-wrap gap-2">
                                {stage.links.map((link) => (
                                    <Link
                                        key={link.href}
                                        href={link.href}
                                        className="inline-flex items-center gap-2 rounded-md border border-input bg-background px-3 py-2 text-sm font-medium"
                                    >
                                        {link.label}
                                        <ArrowLeft className="size-4" />
                                    </Link>
                                ))}
                            </div>
                        </article>
                    ))}
                </section>

                <section className="space-y-3">
                    {stages.map((stage, index) => (
                        <article
                            key={stage.title}
                            className={`rounded-lg border border-r-4 p-4 shadow-sm dark:border-sidebar-border ${stageSurfaceClassName[index % stageSurfaceClassName.length]}`}
                        >
                            <div className="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <div className="flex items-center gap-2">
                                        <Route className="size-5 text-primary" />
                                        <h2 className="text-lg font-semibold">
                                            {index + 1}. {stage.title}
                                        </h2>
                                    </div>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        مسئول اجرا: {stage.owner}
                                    </p>
                                </div>
                                <div className="flex flex-wrap gap-2">
                                    {stage.links.map((link) => (
                                        <Link
                                            key={link.href}
                                            href={link.href}
                                            className="inline-flex items-center gap-2 rounded-md border border-input bg-background px-3 py-2 text-sm font-medium"
                                        >
                                            {link.label}
                                            <ArrowLeft className="size-4" />
                                        </Link>
                                    ))}
                                </div>
                            </div>

                            <div className="mt-4 grid gap-2 md:grid-cols-3">
                                {stage.checks.map((check) => (
                                    <div
                                        key={check}
                                        className="flex min-h-20 gap-2 rounded-md bg-white/65 p-3 text-sm leading-7 shadow-sm"
                                    >
                                        <CheckCircle2 className="mt-1 size-4 shrink-0 text-primary" />
                                        <span>{check}</span>
                                    </div>
                                ))}
                            </div>
                        </article>
                    ))}
                </section>

                <section className="rounded-lg border border-emerald-200 bg-gradient-to-br from-emerald-50/85 via-white to-amber-50/70 p-4 shadow-sm dark:border-sidebar-border dark:from-card dark:via-card dark:to-card">
                    <div className="flex items-center gap-2">
                        <ClipboardCheck className="size-5 text-primary" />
                        <h2 className="font-semibold">
                            معیار عبور به تجاری سازی
                        </h2>
                    </div>
                    <p className="mt-2 text-sm leading-7 text-muted-foreground">
                        وقتی هر ۵ مرحله بدون صفحه سفید، خطای ۴۰۴، ابهام نقش، یا
                        پاداش غیرقابل مصرف اجرا شد، اکسپلوریا از حالت «پلتفرم
                        جذاب» به «پایلوت قابل فروش» می رسد. بعد از آن باید بسته
                        قیمت گذاری، قرارداد کوتاه و گزارش ROI را برای مذاکره
                        آماده کنیم.
                    </p>
                </section>

                <section className="grid gap-3 md:grid-cols-3">
                    {commercialPackages.map((item, index) => (
                        <article
                            key={item.title}
                            className={`rounded-lg border border-t-4 p-4 shadow-sm dark:border-sidebar-border ${commercialPackageSurfaceClassName[index % commercialPackageSurfaceClassName.length]}`}
                        >
                            <h2 className="font-semibold">{item.title}</h2>
                            <p className="mt-2 text-sm text-muted-foreground">
                                خریدار هدف: {item.buyer}
                            </p>
                            <p className="mt-3 text-sm leading-7 text-muted-foreground">
                                {item.deliverable}
                            </p>
                        </article>
                    ))}
                </section>
            </main>
        </>
    );
}
