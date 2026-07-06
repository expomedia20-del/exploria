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

export default function CommercializationIndex({
    summary,
    salesMetrics,
    packages,
    roiCards,
    salesPipeline,
    documents,
    nextActions,
}: Props) {
    const maxMetric = Math.max(...salesMetrics.map((metric) => metric.value), 1);

    return (
        <>
            <Head title="تجاری‌سازی اکسپلوریا" />

            <main className="space-y-5 p-4" dir="rtl">
                <section className="overflow-hidden rounded-lg border border-zinc-800 bg-zinc-950 text-white shadow-sm">
                    <div className="grid gap-5 p-5 lg:grid-cols-[1.7fr_0.8fr]">
                        <div>
                            <p className="text-sm text-emerald-300">
                                تبدیل دمو به فروش، قرارداد و درآمد
                            </p>
                            <h1 className="mt-2 text-3xl font-semibold">{summary.title}</h1>
                            <p className="mt-3 max-w-4xl text-sm leading-7 text-zinc-300">
                                {summary.positioning}
                            </p>
                        </div>

                        <div className="grid gap-2 rounded-lg border border-white/15 bg-white/10 p-4 text-sm">
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
                                <strong className="text-amber-200">{summary.status}</strong>
                            </div>
                        </div>
                    </div>
                </section>

                <section className="grid gap-4 xl:grid-cols-[1.1fr_0.9fr]">
                    <article className="rounded-lg border border-cyan-200 bg-cyan-50/70 p-4 dark:border-cyan-900/60 dark:bg-cyan-950/20">
                        <div className="flex items-center gap-2">
                            <BarChart3 className="size-5 text-cyan-700 dark:text-cyan-300" />
                            <h2 className="text-lg font-semibold">نمودار آمادگی درآمدزایی</h2>
                        </div>
                        <div className="mt-4 space-y-3">
                            {salesMetrics.map((metric, index) => (
                                <div key={metric.label} className="grid gap-2">
                                    <div className="flex items-center justify-between gap-3 text-sm">
                                        <span>{metric.label}</span>
                                        <strong>{metric.value.toLocaleString('fa-IR')}</strong>
                                    </div>
                                    <div className="h-3 overflow-hidden rounded-full bg-white/80 dark:bg-background/60">
                                        <div
                                            className={`h-full rounded-full ${metricColors[index % metricColors.length]}`}
                                            style={{ width: `${Math.max(8, (metric.value / maxMetric) * 100)}%` }}
                                        />
                                    </div>
                                </div>
                            ))}
                        </div>
                    </article>

                    <article className="rounded-lg border border-amber-200 bg-amber-50/80 p-4 dark:border-amber-900/60 dark:bg-amber-950/20">
                        <div className="flex items-center gap-2">
                            <Handshake className="size-5 text-amber-700 dark:text-amber-300" />
                            <h2 className="text-lg font-semibold">قیف تبدیل دمو به قرارداد</h2>
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
                                    <div className={`rounded-md ${funnelThemes[index % funnelThemes.length]} p-3`}>
                                        <div className="flex items-center justify-between gap-3">
                                            <strong>{item.title}</strong>
                                            <span className="inline-flex size-7 items-center justify-center rounded-full bg-white/20 text-sm">
                                                {item.step}
                                            </span>
                                        </div>
                                        <p className="mt-1 text-xs leading-6 text-white/90">{item.output}</p>
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
                            <span className={`inline-flex h-1 w-14 rounded-full ${metricColors[index % metricColors.length]}`} />
                            <p className="mt-3 text-sm text-muted-foreground">
                                {metric.label}
                            </p>
                            <p className="mt-2 text-2xl font-semibold">
                                {metric.value.toLocaleString('fa-IR')}
                            </p>
                        </article>
                    ))}
                </section>

                <section className="rounded-lg border border-emerald-200 bg-emerald-50/60 p-4 dark:border-emerald-900/60 dark:bg-emerald-950/20">
                    <div className="mb-4 flex items-center gap-2">
                        <BriefcaseBusiness className="size-5 text-emerald-700 dark:text-emerald-300" />
                        <h2 className="text-lg font-semibold">بسته‌های قابل فروش</h2>
                    </div>
                    <div className="grid gap-3 xl:grid-cols-3">
                        {packages.map((item, index) => {
                            const theme = packageThemes[index % packageThemes.length];

                            return (
                                <article
                                    key={item.title}
                                    className={`rounded-lg border p-4 ${theme.panel}`}
                                >
                                    <div className="flex items-start justify-between gap-3">
                                        <div>
                                            <div className="flex items-center gap-2">
                                                <span className={`inline-flex size-9 items-center justify-center rounded-md ${theme.icon}`}>
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

                                    <div className={`mt-4 rounded-md p-3 text-sm font-medium leading-7 ${theme.strip}`}>
                                        {item.priceRange}
                                    </div>

                                    <ul className="mt-4 space-y-2 text-sm leading-7 text-muted-foreground">
                                        {item.deliverables.map((deliverable) => (
                                            <li key={deliverable} className="flex gap-2">
                                                <CheckCircle2 className="mt-1 size-4 shrink-0 text-emerald-700" />
                                                <span>{deliverable}</span>
                                            </li>
                                        ))}
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

                <section className="grid gap-3 xl:grid-cols-2">
                    <article className="rounded-lg border border-sky-200 bg-sky-50/70 p-4 dark:border-sky-900/60 dark:bg-sky-950/20">
                        <div className="flex items-center gap-2">
                            <FileText className="size-5 text-sky-700 dark:text-sky-300" />
                            <h2 className="text-lg font-semibold">مدارک مذاکره</h2>
                        </div>
                        <div className="mt-4 space-y-2">
                            {documents.map((item) => (
                                <div
                                    key={item.title}
                                    className="rounded-md border border-border/70 bg-background/80 p-3"
                                >
                                    <h3 className="font-medium">{item.title}</h3>
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
                            <h2 className="text-lg font-semibold">اقدام‌های بعدی</h2>
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
