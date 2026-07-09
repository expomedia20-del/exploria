import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    BadgeDollarSign,
    BarChart3,
    BriefcaseBusiness,
    CheckCircle2,
    FileText,
    Handshake,
    LineChart,
    Target,
    TrendingUp,
} from 'lucide-react';

type Metric = {
    label: string;
    value: number;
};

type PackageItem = {
    title: string;
    buyer: string;
    priceRange: string;
    deliverables: string[];
    successMetric: string;
};

type RoiCard = {
    title: string;
    formula: string;
    evidence: string;
};

type PipelineStep = {
    step: string;
    title: string;
    output: string;
};

type DocumentItem = {
    title: string;
    status: string;
};

type PricingTier = {
    title: string;
    price: string;
    bestFor: string;
    items: string[];
};

type SalesAsset = {
    title: string;
    owner: string;
    status: string;
};

type LeadTarget = {
    segment: string;
    target: string;
    firstOffer: string;
};

type FinalDemoReport = {
    isExecuted: boolean;
    title: string;
    campaignName: string | null;
    campaignCode: string | null;
    summary: Metric[];
    roi: {
        investment: number;
        estimatedValue: number;
        roiPercent: number;
        redemptionRate: number;
    };
    audiences: {
        title: string;
        audience: string;
        headline: string;
        proofPoints: string[];
        offer: string;
        nextStep: string;
    }[];
    recommendation: string;
    actionHref: string;
};

type Props = {
    summary: {
        title: string;
        positioning: string;
        venue: string;
        campaign: string;
        status: string;
    };
    salesMetrics: Metric[];
    packages: PackageItem[];
    roiCards: RoiCard[];
    salesPipeline: PipelineStep[];
    documents: DocumentItem[];
    pricingTiers: PricingTier[];
    salesAssets: SalesAsset[];
    leadTargets: LeadTarget[];
    finalDemoReport: FinalDemoReport;
    nextActions: string[];
};

const metricColors = [
    'bg-emerald-500',
    'bg-cyan-500',
    'bg-amber-500',
    'bg-rose-500',
    'bg-indigo-500',
    'bg-lime-500',
    'bg-orange-500',
    'bg-teal-500',
    'bg-sky-500',
];

const packageThemes = [
    {
        panel: 'border-emerald-200 bg-emerald-50/80 dark:border-emerald-900/60 dark:bg-emerald-950/20',
        icon: 'bg-emerald-600 text-white',
        strip: 'bg-emerald-100 text-emerald-950 dark:bg-emerald-900/40 dark:text-emerald-100',
    },
    {
        panel: 'border-cyan-200 bg-cyan-50/80 dark:border-cyan-900/60 dark:bg-cyan-950/20',
        icon: 'bg-cyan-600 text-white',
        strip: 'bg-cyan-100 text-cyan-950 dark:bg-cyan-900/40 dark:text-cyan-100',
    },
    {
        panel: 'border-amber-200 bg-amber-50/80 dark:border-amber-900/60 dark:bg-amber-950/20',
        icon: 'bg-amber-500 text-zinc-950',
        strip: 'bg-amber-100 text-amber-950 dark:bg-amber-900/40 dark:text-amber-100',
    },
];

const roiThemes = [
    'border-teal-200 bg-teal-50/80 dark:border-teal-900/60 dark:bg-teal-950/20',
    'border-rose-200 bg-rose-50/80 dark:border-rose-900/60 dark:bg-rose-950/20',
    'border-indigo-200 bg-indigo-50/80 dark:border-indigo-900/60 dark:bg-indigo-950/20',
];

const funnelThemes = [
    'bg-emerald-600',
    'bg-cyan-600',
    'bg-amber-500',
    'bg-rose-500',
    'bg-indigo-600',
];

const commercializationHeroImage = '/images/ecopark/proposal/roi-night-plaza.jpg';

export default function CommercializationIndex({
    summary,
    salesMetrics,
    packages,
    roiCards,
    salesPipeline,
    documents,
    pricingTiers,
    salesAssets,
    leadTargets,
    finalDemoReport,
    nextActions,
}: Props) {
    const maxMetric = Math.max(
        ...salesMetrics.map((metric) => metric.value),
        1,
    );

    return (
        <>
            <Head title="تجاری‌سازی اکسپلوریا" />

            <main className="space-y-5 p-4" dir="rtl">
                <section className="overflow-hidden rounded-lg border border-zinc-800 bg-zinc-950 text-white shadow-sm">
                    <div className="grid gap-0 lg:grid-cols-[1.55fr_0.75fr]">
                        <div className="p-5">
                        <div>
                            <p className="text-sm text-emerald-300">
                                تبدیل دمو به فروش، قرارداد و درآمد
                            </p>
                            <h1 className="mt-2 text-3xl font-semibold">
                                {summary.title}
                            </h1>
                            <p className="mt-3 max-w-4xl text-sm leading-7 text-zinc-300">
                                {summary.positioning}
                            </p>
                        </div>
                        </div>
                        <div className="relative min-h-56 lg:order-last">
                            <img src={commercializationHeroImage} alt="" className="absolute inset-0 h-full w-full object-cover" />
                            <div className="absolute inset-0 bg-gradient-to-r from-zinc-950 via-zinc-950/25 to-transparent" />
                        </div>
                        <div className="m-5 grid gap-2 rounded-lg border border-white/15 bg-white/10 p-4 text-sm lg:col-span-2">
                            <div className="flex justify-between gap-3">
                                <span className="text-zinc-300">مکان</span>
                                <strong>{summary.venue}</strong>
                            </div>
                            <div className="flex justify-between gap-3">
                                <span className="text-zinc-300">کمپین</span>
                                <strong>{summary.campaign}</strong>
                            </div>
                            <div className="flex justify-between gap-3">
                                <span className="text-zinc-300">وضعیت</span>
                                <strong className="text-amber-200">
                                    {summary.status}
                                </strong>
                            </div>
                        </div>
                    </div>
                </section>

                <section className="grid gap-4 xl:grid-cols-[1.1fr_0.9fr]">
                    <article className="rounded-lg border border-cyan-200 bg-cyan-50/70 p-4 dark:border-cyan-900/60 dark:bg-cyan-950/20">
                        <div className="flex items-center gap-2">
                            <BarChart3 className="size-5 text-cyan-700 dark:text-cyan-300" />
                            <h2 className="text-lg font-semibold">
                                نمودار آمادگی درآمدزایی
                            </h2>
                        </div>
                        <div className="mt-4 space-y-3">
                            {salesMetrics.map((metric, index) => (
                                <div key={metric.label} className="grid gap-2">
                                    <div className="flex items-center justify-between gap-3 text-sm">
                                        <span>{metric.label}</span>
                                        <strong>
                                            {metric.value.toLocaleString(
                                                'fa-IR',
                                            )}
                                        </strong>
                                    </div>
                                    <div className="h-3 overflow-hidden rounded-full bg-white/80 dark:bg-background/60">
                                        <div
                                            className={`h-full rounded-full ${metricColors[index % metricColors.length]}`}
                                            style={{
                                                width: `${Math.max(8, (metric.value / maxMetric) * 100)}%`,
                                            }}
                                        />
                                    </div>
                                </div>
                            ))}
                        </div>
                    </article>

                    <article className="rounded-lg border border-amber-200 bg-amber-50/80 p-4 dark:border-amber-900/60 dark:bg-amber-950/20">
                        <div className="flex items-center gap-2">
                            <Handshake className="size-5 text-amber-700 dark:text-amber-300" />
                            <h2 className="text-lg font-semibold">
                                قیف تبدیل دمو به قرارداد
                            </h2>
                        </div>
                        <div className="mt-4 space-y-2">
                            {salesPipeline.map((item, index) => (
                                <div
                                    key={item.step}
                                    className="mx-auto rounded-md px-4 py-3 text-white shadow-sm"
                                    style={{
                                        width: `${100 - index * 8}%`,
                                    }}
                                >
                                    <div
                                        className={`rounded-md ${funnelThemes[index % funnelThemes.length]} p-3`}
                                    >
                                        <div className="flex items-center justify-between gap-3">
                                            <strong>{item.title}</strong>
                                            <span className="inline-flex size-7 items-center justify-center rounded-full bg-white/20 text-sm">
                                                {item.step}
                                            </span>
                                        </div>
                                        <p className="mt-1 text-xs leading-6 text-white/90">
                                            {item.output}
                                        </p>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </article>
                </section>

                <section className="grid gap-3 md:grid-cols-3 xl:grid-cols-5">
                    {salesMetrics.map((metric, index) => (
                        <article
                            key={metric.label}
                            className="rounded-lg border border-sidebar-border/70 bg-card p-3 dark:border-sidebar-border"
                        >
                            <span
                                className={`inline-flex h-1 w-14 rounded-full ${metricColors[index % metricColors.length]}`}
                            />
                            <p className="mt-3 text-sm text-muted-foreground">
                                {metric.label}
                            </p>
                            <p className="mt-2 text-2xl font-semibold">
                                {metric.value.toLocaleString('fa-IR')}
                            </p>
                        </article>
                    ))}
                </section>

                <section className="rounded-lg border border-zinc-800 bg-zinc-950 p-4 text-white shadow-sm">
                    <div className="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p className="text-sm text-emerald-300">
                                خروجی قابل ارائه جلسه فروش
                            </p>
                            <h2 className="mt-1 text-2xl font-semibold">
                                {finalDemoReport.title}
                            </h2>
                            <p className="mt-2 max-w-4xl text-sm leading-7 text-zinc-300">
                                {finalDemoReport.recommendation}
                            </p>
                        </div>
                        <Link
                            href={finalDemoReport.actionHref}
                            className="inline-flex items-center gap-2 rounded-md bg-white px-3 py-2 text-sm font-medium text-zinc-950"
                        >
                            چرخه دمو
                            <ArrowLeft className="size-4" />
                        </Link>
                    </div>

                    <div className="mt-4 grid gap-3 lg:grid-cols-[1fr_0.8fr]">
                        <div className="rounded-lg border border-white/15 bg-white/10 p-3">
                            <div className="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p className="text-sm text-zinc-300">
                                        کمپین گزارش
                                    </p>
                                    <h3 className="mt-1 font-semibold">
                                        {finalDemoReport.campaignName ??
                                            'منتظر اجرای دمو'}
                                    </h3>
                                    {finalDemoReport.campaignCode ? (
                                        <p
                                            className="mt-1 text-xs text-zinc-400"
                                            dir="ltr"
                                        >
                                            {finalDemoReport.campaignCode}
                                        </p>
                                    ) : null}
                                </div>
                                <span
                                    className={`rounded-full px-3 py-1 text-xs font-medium ${
                                        finalDemoReport.isExecuted
                                            ? 'bg-emerald-300 text-emerald-950'
                                            : 'bg-amber-300 text-amber-950'
                                    }`}
                                >
                                    {finalDemoReport.isExecuted
                                        ? 'آماده ارائه'
                                        : 'نیازمند اجرا'}
                                </span>
                            </div>

                            <div className="mt-4 grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
                                {finalDemoReport.summary.map((metric) => (
                                    <div
                                        key={metric.label}
                                        className="rounded-md bg-white/10 p-3"
                                    >
                                        <p className="text-xs text-zinc-300">
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
                        </div>

                        <div className="rounded-lg border border-white/15 bg-white/10 p-3">
                            <div className="flex items-center gap-2">
                                <LineChart className="size-5 text-emerald-300" />
                                <h3 className="font-semibold">
                                    عددهای مذاکره ROI
                                </h3>
                            </div>
                            <div className="mt-4 grid gap-3 text-sm">
                                <div className="flex justify-between gap-3">
                                    <span className="text-zinc-300">
                                        سرمایه‌گذاری
                                    </span>
                                    <strong>
                                        {finalDemoReport.roi.investment.toLocaleString(
                                            'fa-IR',
                                        )}{' '}
                                        ریال
                                    </strong>
                                </div>
                                <div className="flex justify-between gap-3">
                                    <span className="text-zinc-300">
                                        ارزش تخمینی
                                    </span>
                                    <strong>
                                        {finalDemoReport.roi.estimatedValue.toLocaleString(
                                            'fa-IR',
                                        )}{' '}
                                        ریال
                                    </strong>
                                </div>
                                <div className="flex justify-between gap-3">
                                    <span className="text-zinc-300">ROI</span>
                                    <strong className="text-emerald-300">
                                        {finalDemoReport.roi.roiPercent.toLocaleString(
                                            'fa-IR',
                                        )}
                                        ٪
                                    </strong>
                                </div>
                                <div className="flex justify-between gap-3">
                                    <span className="text-zinc-300">
                                        نرخ مصرف پاداش
                                    </span>
                                    <strong className="text-amber-200">
                                        {finalDemoReport.roi.redemptionRate.toLocaleString(
                                            'fa-IR',
                                        )}
                                        ٪
                                    </strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="mt-4 grid gap-3 xl:grid-cols-3">
                        {finalDemoReport.audiences.map((audience) => (
                            <article
                                key={audience.title}
                                className="rounded-lg border border-white/15 bg-white/10 p-4"
                            >
                                <p className="text-xs text-emerald-300">
                                    {audience.audience}
                                </p>
                                <h3 className="mt-1 font-semibold">
                                    {audience.title}
                                </h3>
                                <p className="mt-3 text-sm leading-7 text-zinc-300">
                                    {audience.headline}
                                </p>
                                <ul className="mt-3 space-y-2 text-sm leading-7 text-zinc-300">
                                    {audience.proofPoints.map((point) => (
                                        <li key={point} className="flex gap-2">
                                            <CheckCircle2 className="mt-1 size-4 shrink-0 text-emerald-300" />
                                            <span>{point}</span>
                                        </li>
                                    ))}
                                </ul>
                                <div className="mt-4 rounded-md bg-white/10 p-3 text-sm leading-7">
                                    <strong className="text-white">
                                        پیشنهاد فروش:{' '}
                                    </strong>
                                    <span className="text-zinc-300">
                                        {audience.offer}
                                    </span>
                                </div>
                                <p className="mt-3 text-sm leading-7 text-zinc-300">
                                    قدم بعدی: {audience.nextStep}
                                </p>
                            </article>
                        ))}
                    </div>
                </section>

                <section className="rounded-lg border border-emerald-200 bg-emerald-50/60 p-4 dark:border-emerald-900/60 dark:bg-emerald-950/20">
                    <div className="mb-4 flex items-center gap-2">
                        <BriefcaseBusiness className="size-5 text-emerald-700 dark:text-emerald-300" />
                        <h2 className="text-lg font-semibold">
                            بسته‌های قابل فروش
                        </h2>
                    </div>
                    <div className="grid gap-3 xl:grid-cols-3">
                        {packages.map((item, index) => {
                            const theme =
                                packageThemes[index % packageThemes.length];

                            return (
                                <article
                                    key={item.title}
                                    className={`rounded-lg border p-4 ${theme.panel}`}
                                >
                                    <div className="flex items-start justify-between gap-3">
                                        <div>
                                            <div className="flex items-center gap-2">
                                                <span
                                                    className={`inline-flex size-9 items-center justify-center rounded-md ${theme.icon}`}
                                                >
                                                    <BriefcaseBusiness className="size-5" />
                                                </span>
                                                <h3 className="text-lg font-semibold">
                                                    {item.title}
                                                </h3>
                                            </div>
                                            <p className="mt-2 text-sm text-muted-foreground">
                                                خریدار هدف: {item.buyer}
                                            </p>
                                        </div>
                                        <BadgeDollarSign className="size-5 text-emerald-700" />
                                    </div>

                                    <div
                                        className={`mt-4 rounded-md p-3 text-sm leading-7 font-medium ${theme.strip}`}
                                    >
                                        {item.priceRange}
                                    </div>

                                    <ul className="mt-4 space-y-2 text-sm leading-7 text-muted-foreground">
                                        {item.deliverables.map(
                                            (deliverable) => (
                                                <li
                                                    key={deliverable}
                                                    className="flex gap-2"
                                                >
                                                    <CheckCircle2 className="mt-1 size-4 shrink-0 text-emerald-700" />
                                                    <span>{deliverable}</span>
                                                </li>
                                            ),
                                        )}
                                    </ul>

                                    <div className="mt-4 rounded-md border border-border/70 bg-background/80 p-3 text-sm">
                                        <strong>شاخص موفقیت: </strong>
                                        <span className="text-muted-foreground">
                                            {item.successMetric}
                                        </span>
                                    </div>
                                </article>
                            );
                        })}
                    </div>
                </section>

                <section className="grid gap-3 xl:grid-cols-3">
                    {roiCards.map((item, index) => (
                        <article
                            key={item.title}
                            className={`rounded-lg border p-4 ${roiThemes[index % roiThemes.length]}`}
                        >
                            <div className="flex items-center gap-2">
                                <LineChart className="size-5 text-primary" />
                                <h2 className="text-lg font-semibold">
                                    {item.title}
                                </h2>
                            </div>
                            <p className="mt-3 text-sm leading-7 text-muted-foreground">
                                {item.formula}
                            </p>
                            <div className="mt-4 rounded-md bg-background/70 p-3 text-sm leading-7">
                                <strong>مدرک قابل ارائه: </strong>
                                {item.evidence}
                            </div>
                        </article>
                    ))}
                </section>

                <section className="rounded-lg border border-orange-200 bg-orange-50/70 p-4 dark:border-orange-900/60 dark:bg-orange-950/20">
                    <div className="mb-4 flex items-center gap-2">
                        <BadgeDollarSign className="size-5 text-orange-700 dark:text-orange-300" />
                        <h2 className="text-lg font-semibold">
                            جدول قیمت قابل مذاکره
                        </h2>
                    </div>
                    <div className="grid gap-3 xl:grid-cols-3">
                        {pricingTiers.map((tier, index) => (
                            <article
                                key={tier.title}
                                className="rounded-lg border border-white/70 bg-background/80 p-4 shadow-sm"
                            >
                                <div className="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 className="text-lg font-semibold">
                                            {tier.title}
                                        </h3>
                                        <p className="mt-2 text-2xl font-semibold text-orange-700 dark:text-orange-300">
                                            {tier.price}
                                        </p>
                                    </div>
                                    <span className="inline-flex size-9 items-center justify-center rounded-md bg-orange-500 text-sm font-semibold text-zinc-950">
                                        {index + 1}
                                    </span>
                                </div>
                                <p className="mt-3 rounded-md bg-orange-100/80 p-3 text-sm leading-7 text-orange-950 dark:bg-orange-900/40 dark:text-orange-100">
                                    {tier.bestFor}
                                </p>
                                <ul className="mt-4 space-y-2 text-sm leading-7 text-muted-foreground">
                                    {tier.items.map((item) => (
                                        <li key={item} className="flex gap-2">
                                            <CheckCircle2 className="mt-1 size-4 shrink-0 text-orange-700" />
                                            <span>{item}</span>
                                        </li>
                                    ))}
                                </ul>
                            </article>
                        ))}
                    </div>
                </section>

                <section className="grid gap-3 xl:grid-cols-[1fr_0.9fr]">
                    <article className="rounded-lg border border-violet-200 bg-violet-50/70 p-4 dark:border-violet-900/60 dark:bg-violet-950/20">
                        <div className="flex items-center gap-2">
                            <FileText className="size-5 text-violet-700 dark:text-violet-300" />
                            <h2 className="text-lg font-semibold">
                                مدارک آماده مذاکره
                            </h2>
                        </div>
                        <div className="mt-4 grid gap-2">
                            {salesAssets.map((asset) => (
                                <div
                                    key={asset.title}
                                    className="rounded-md border border-border/70 bg-background/80 p-3"
                                >
                                    <div className="flex flex-wrap items-center justify-between gap-2">
                                        <h3 className="font-medium">
                                            {asset.title}
                                        </h3>
                                        <span className="rounded-full bg-violet-100 px-3 py-1 text-xs text-violet-900 dark:bg-violet-900/40 dark:text-violet-100">
                                            {asset.owner}
                                        </span>
                                    </div>
                                    <p className="mt-2 text-sm leading-7 text-muted-foreground">
                                        {asset.status}
                                    </p>
                                </div>
                            ))}
                        </div>
                    </article>

                    <article className="rounded-lg border border-lime-200 bg-lime-50/70 p-4 dark:border-lime-900/60 dark:bg-lime-950/20">
                        <div className="flex items-center gap-2">
                            <Target className="size-5 text-lime-700 dark:text-lime-300" />
                            <h2 className="text-lg font-semibold">
                                مشتریان هدف جلسه اول
                            </h2>
                        </div>
                        <div className="mt-4 space-y-3">
                            {leadTargets.map((lead) => (
                                <div
                                    key={lead.segment}
                                    className="rounded-md border border-border/70 bg-background/80 p-3"
                                >
                                    <h3 className="font-medium">
                                        {lead.segment}
                                    </h3>
                                    <p className="mt-2 text-sm leading-7 text-muted-foreground">
                                        {lead.target}
                                    </p>
                                    <div className="mt-2 rounded-md bg-lime-100/80 p-2 text-sm leading-7 text-lime-950 dark:bg-lime-900/40 dark:text-lime-100">
                                        پیشنهاد شروع: {lead.firstOffer}
                                    </div>
                                </div>
                            ))}
                        </div>
                    </article>
                </section>

                <section className="grid gap-3 xl:grid-cols-2">
                    <article className="rounded-lg border border-sky-200 bg-sky-50/70 p-4 dark:border-sky-900/60 dark:bg-sky-950/20">
                        <div className="flex items-center gap-2">
                            <FileText className="size-5 text-sky-700 dark:text-sky-300" />
                            <h2 className="text-lg font-semibold">
                                مدارک مذاکره
                            </h2>
                        </div>
                        <div className="mt-4 space-y-2">
                            {documents.map((item) => (
                                <div
                                    key={item.title}
                                    className="rounded-md border border-border/70 bg-background/80 p-3"
                                >
                                    <h3 className="font-medium">
                                        {item.title}
                                    </h3>
                                    <p className="mt-1 text-sm leading-7 text-muted-foreground">
                                        {item.status}
                                    </p>
                                </div>
                            ))}
                        </div>
                    </article>

                    <article className="rounded-lg border border-rose-200 bg-rose-50/70 p-4 dark:border-rose-900/60 dark:bg-rose-950/20">
                        <div className="flex items-center gap-2">
                            <Target className="size-5 text-rose-700 dark:text-rose-300" />
                            <h2 className="text-lg font-semibold">
                                اقدام‌های بعدی
                            </h2>
                        </div>
                        <ul className="mt-4 space-y-2 text-sm leading-7 text-muted-foreground">
                            {nextActions.map((action) => (
                                <li key={action} className="flex gap-2">
                                    <CheckCircle2 className="mt-1 size-4 shrink-0 text-rose-700" />
                                    <span>{action}</span>
                                </li>
                            ))}
                        </ul>
                        <div className="mt-4 flex flex-wrap gap-2">
                            <Link
                                href="/admin/demo-cycle"
                                className="inline-flex items-center gap-2 rounded-md border border-input bg-background px-3 py-2 text-sm font-medium"
                            >
                                چرخه دمو
                                <ArrowLeft className="size-4" />
                            </Link>
                            <Link
                                href="/dashboard"
                                className="inline-flex items-center gap-2 rounded-md bg-primary px-3 py-2 text-sm font-medium text-primary-foreground"
                            >
                                داشبورد عددی
                                <TrendingUp className="size-4" />
                            </Link>
                        </div>
                    </article>
                </section>
            </main>
        </>
    );
}
