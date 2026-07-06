import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    CheckCircle2,
    ClipboardCheck,
    Flag,
    Route,
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

type Props = {
    summary: DemoSummary;
    stages: DemoStage[];
    stageHealth: StageHealth[];
    commercialPackages: CommercialPackage[];
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

export default function DemoCycleIndex({
    summary,
    stages,
    stageHealth,
    commercialPackages,
}: Props) {
    return (
        <>
            <Head title="چرخه دمو اکوپارک" />

            <main className="space-y-4 p-4" dir="rtl">
                <section className="rounded-lg border border-sidebar-border/70 bg-gradient-to-l from-cyan-50 to-background p-4 dark:border-sidebar-border dark:from-cyan-950/30">
                    <p className="text-sm text-muted-foreground">
                        نقشه اجرای دمو از صفر تا گزارش فروش
                    </p>
                    <div className="mt-2 flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h1 className="text-2xl font-semibold">{summary.title}</h1>
                            <p className="mt-2 max-w-4xl text-sm leading-7 text-muted-foreground">
                                این صفحه، چرخه کامل دمو را به ۵ مرحله قابل اجرا تبدیل می کند:
                                آماده سازی سناریو، داده و دسترسی، اجرای کاربر، مصرف پاداش و تبلیغ،
                                سپس گزارش تجاری. بازی آنلاین در این نسخه عمدا از ارزیابی اصلی جدا نگه داشته شده است.
                            </p>
                        </div>
                        <div className="grid min-w-64 gap-2 rounded-lg border border-sidebar-border/70 bg-card p-3 text-sm">
                            <div className="flex justify-between gap-3">
                                <span className="text-muted-foreground">کمپین</span>
                                <strong>{summary.campaign}</strong>
                            </div>
                            <div className="flex justify-between gap-3">
                                <span className="text-muted-foreground">مکان</span>
                                <strong>{summary.venue}</strong>
                            </div>
                            <div className="flex justify-between gap-3">
                                <span className="text-muted-foreground">وضعیت</span>
                                <strong>{summary.status}</strong>
                            </div>
                        </div>
                    </div>
                </section>

                <section className="grid gap-3 md:grid-cols-5">
                    {stages.map((stage, index) => (
                        <article
                            key={stage.title}
                            className="rounded-lg border border-sidebar-border/70 bg-card p-3 dark:border-sidebar-border"
                        >
                            <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                <Flag className="size-4 text-primary" />
                                مرحله {index + 1}
                            </div>
                            <h2 className="mt-2 font-semibold">{stage.title}</h2>
                            <p className="mt-2 text-sm leading-7 text-muted-foreground">
                                {stage.goal}
                            </p>
                            <div className="mt-3 inline-flex rounded-full bg-cyan-50 px-3 py-1 text-xs font-medium text-cyan-900">
                                {stage.status}
                            </div>
                        </article>
                    ))}
                </section>

                <section className="grid gap-3 xl:grid-cols-2">
                    {stageHealth.map((stage) => (
                        <article
                            key={stage.stage}
                            className="rounded-lg border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
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
                                        className="rounded-md border border-border/70 bg-background p-3"
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

                            <div className="mt-4 rounded-md bg-muted/40 p-3">
                                <h3 className="text-sm font-semibold">اقدام بعدی</h3>
                                {stage.nextActions.length > 0 ? (
                                    <ul className="mt-2 space-y-2 text-sm leading-7 text-muted-foreground">
                                        {stage.nextActions.map((action) => (
                                            <li key={action}>• {action}</li>
                                        ))}
                                    </ul>
                                ) : (
                                    <p className="mt-2 text-sm text-muted-foreground">
                                        مورد بحرانی ثبت نشده است؛ این مرحله برای دمو قابل عبور است.
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
                            className="rounded-lg border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
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
                                        className="flex min-h-20 gap-2 rounded-md bg-muted/40 p-3 text-sm leading-7"
                                    >
                                        <CheckCircle2 className="mt-1 size-4 shrink-0 text-primary" />
                                        <span>{check}</span>
                                    </div>
                                ))}
                            </div>
                        </article>
                    ))}
                </section>

                <section className="rounded-lg border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border">
                    <div className="flex items-center gap-2">
                        <ClipboardCheck className="size-5 text-primary" />
                        <h2 className="font-semibold">معیار عبور به تجاری سازی</h2>
                    </div>
                    <p className="mt-2 text-sm leading-7 text-muted-foreground">
                        وقتی هر ۵ مرحله بدون صفحه سفید، خطای ۴۰۴، ابهام نقش، یا پاداش غیرقابل مصرف اجرا شد،
                        اکسپلوریا از حالت «پلتفرم جذاب» به «پایلوت قابل فروش» می رسد. بعد از آن باید
                        بسته قیمت گذاری، قرارداد کوتاه و گزارش ROI را برای مذاکره آماده کنیم.
                    </p>
                </section>

                <section className="grid gap-3 md:grid-cols-3">
                    {commercialPackages.map((item) => (
                        <article
                            key={item.title}
                            className="rounded-lg border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
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
