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

export default function CommercializationIndex({
    summary,
    salesMetrics,
    packages,
    roiCards,
    salesPipeline,
    documents,
    nextActions,
}: Props) {
    return (
        <>
            <Head title="تجاری‌سازی اکسپلوریا" />

            <main className="space-y-4 p-4" dir="rtl">
                <section className="rounded-lg border border-sidebar-border/70 bg-gradient-to-l from-emerald-50 to-background p-4 dark:border-sidebar-border dark:from-emerald-950/30">
                    <p className="text-sm text-muted-foreground">
                        تبدیل دمو به فروش، قرارداد و درآمد
                    </p>
                    <div className="mt-2 flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <h1 className="text-2xl font-semibold">{summary.title}</h1>
                            <p className="mt-2 max-w-4xl text-sm leading-7 text-muted-foreground">
                                {summary.positioning}
                            </p>
                        </div>
                        <div className="grid min-w-72 gap-2 rounded-lg border border-sidebar-border/70 bg-card p-3 text-sm">
                            <div className="flex justify-between gap-3">
                                <span className="text-muted-foreground">مکان</span>
                                <strong>{summary.venue}</strong>
                            </div>
                            <div className="flex justify-between gap-3">
                                <span className="text-muted-foreground">کمپین</span>
                                <strong>{summary.campaign}</strong>
                            </div>
                            <div className="flex justify-between gap-3">
                                <span className="text-muted-foreground">وضعیت</span>
                                <strong>{summary.status}</strong>
                            </div>
                        </div>
                    </div>
                </section>

                <section className="grid gap-3 md:grid-cols-3 xl:grid-cols-5">
                    {salesMetrics.map((metric) => (
                        <article
                            key={metric.label}
                            className="rounded-lg border border-sidebar-border/70 bg-card p-3 dark:border-sidebar-border"
                        >
                            <p className="text-sm text-muted-foreground">
                                {metric.label}
                            </p>
                            <p className="mt-2 text-2xl font-semibold">
                                {metric.value.toLocaleString('fa-IR')}
                            </p>
                        </article>
                    ))}
                </section>

                <section className="grid gap-3 xl:grid-cols-3">
                    {packages.map((item) => (
                        <article
                            key={item.title}
                            className="rounded-lg border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
                        >
                            <div className="flex items-start justify-between gap-3">
                                <div>
                                    <div className="flex items-center gap-2">
                                        <BriefcaseBusiness className="size-5 text-primary" />
                                        <h2 className="text-lg font-semibold">
                                            {item.title}
                                        </h2>
                                    </div>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        خریدار هدف: {item.buyer}
                                    </p>
                                </div>
                                <BadgeDollarSign className="size-5 text-emerald-700" />
                            </div>

                            <div className="mt-4 rounded-md bg-muted/40 p-3 text-sm font-medium leading-7">
                                {item.priceRange}
                            </div>

                            <ul className="mt-4 space-y-2 text-sm leading-7 text-muted-foreground">
                                {item.deliverables.map((deliverable) => (
                                    <li
                                        key={deliverable}
                                        className="flex gap-2"
                                    >
                                        <CheckCircle2 className="mt-1 size-4 shrink-0 text-primary" />
                                        <span>{deliverable}</span>
                                    </li>
                                ))}
                            </ul>

                            <div className="mt-4 rounded-md border border-border/70 bg-background p-3 text-sm">
                                <strong>شاخص موفقیت: </strong>
                                <span className="text-muted-foreground">
                                    {item.successMetric}
                                </span>
                            </div>
                        </article>
                    ))}
                </section>

                <section className="grid gap-3 xl:grid-cols-3">
                    {roiCards.map((item) => (
                        <article
                            key={item.title}
                            className="rounded-lg border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
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
                            <div className="mt-4 rounded-md bg-muted/40 p-3 text-sm leading-7">
                                <strong>مدرک قابل ارائه: </strong>
                                {item.evidence}
                            </div>
                        </article>
                    ))}
                </section>

                <section className="rounded-lg border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border">
                    <div className="flex items-center gap-2">
                        <Handshake className="size-5 text-primary" />
                        <h2 className="text-lg font-semibold">قیف فروش پیشنهادی</h2>
                    </div>

                    <div className="mt-4 grid gap-3 xl:grid-cols-5">
                        {salesPipeline.map((item) => (
                            <article
                                key={item.step}
                                className="rounded-lg border border-border/70 bg-background p-3"
                            >
                                <span className="inline-flex size-8 items-center justify-center rounded-full bg-primary text-sm font-semibold text-primary-foreground">
                                    {item.step}
                                </span>
                                <h3 className="mt-3 font-semibold">{item.title}</h3>
                                <p className="mt-2 text-sm leading-7 text-muted-foreground">
                                    {item.output}
                                </p>
                            </article>
                        ))}
                    </div>
                </section>

                <section className="grid gap-3 xl:grid-cols-2">
                    <article className="rounded-lg border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border">
                        <div className="flex items-center gap-2">
                            <FileText className="size-5 text-primary" />
                            <h2 className="text-lg font-semibold">مدارک مذاکره</h2>
                        </div>
                        <div className="mt-4 space-y-2">
                            {documents.map((item) => (
                                <div
                                    key={item.title}
                                    className="rounded-md border border-border/70 bg-background p-3"
                                >
                                    <h3 className="font-medium">{item.title}</h3>
                                    <p className="mt-1 text-sm leading-7 text-muted-foreground">
                                        {item.status}
                                    </p>
                                </div>
                            ))}
                        </div>
                    </article>

                    <article className="rounded-lg border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border">
                        <div className="flex items-center gap-2">
                            <Target className="size-5 text-primary" />
                            <h2 className="text-lg font-semibold">اقدام‌های بعدی</h2>
                        </div>
                        <ul className="mt-4 space-y-2 text-sm leading-7 text-muted-foreground">
                            {nextActions.map((action) => (
                                <li key={action} className="flex gap-2">
                                    <CheckCircle2 className="mt-1 size-4 shrink-0 text-primary" />
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
                                <BarChart3 className="size-4" />
                            </Link>
                        </div>
                    </article>
                </section>
            </main>
        </>
    );
}
