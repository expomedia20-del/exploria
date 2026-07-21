import { Head, Link } from '@inertiajs/react';
import {
    BotMessageSquare,
    CircleHelp,
    ClipboardCheck,
    MessageSquareText,
    Route,
    ShieldAlert,
} from 'lucide-react';

type SupportPrompt = {
    question: string;
    answer: string;
    routeLabel: string | null;
    routeHref: string | null;
};

type PromptGroup = {
    title: string;
    prompts: SupportPrompt[];
};

type QuickAction = {
    title: string;
    body: string;
    href: string | null;
    label: string | null;
};

type SupportPayload = {
    roleContext: {
        key: string;
        title: string;
        summary: string;
        audience: string;
    };
    promptGroups: PromptGroup[];
    quickActions: QuickAction[];
    checklist: string[];
    handoffNotes: string[];
};

type Props = {
    support: SupportPayload;
};

export default function SupportCenterIndex({ support }: Props) {
    const { roleContext, promptGroups, quickActions, checklist, handoffNotes } =
        support;
    const prompts = promptGroups.flatMap((group) =>
        group.prompts.map((prompt) => ({ ...prompt, groupTitle: group.title })),
    );

    return (
        <>
            <Head title="پشتیبانی و چت‌بات" />

            <main className="space-y-4 p-4" dir="rtl">
                <section className="rounded-lg border border-sidebar-border/70 bg-gradient-to-l from-cyan-50 to-background p-4 dark:border-sidebar-border dark:from-cyan-950/30">
                    <p className="text-sm text-muted-foreground">
                        مرکز پشتیبانی عملیاتی متناسب با نقش کاربر
                    </p>
                    <h1 className="mt-1 text-2xl font-semibold">
                        {roleContext.title}
                    </h1>
                    <p className="mt-2 max-w-4xl text-sm leading-7 text-muted-foreground">
                        {roleContext.summary}
                    </p>
                    <div className="mt-3 flex flex-wrap gap-2 text-xs">
                        <span className="rounded-full bg-background px-3 py-1 font-medium text-foreground shadow-xs">
                            مخاطب: {roleContext.audience}
                        </span>
                        <span className="rounded-full bg-background px-3 py-1 font-medium text-foreground shadow-xs">
                            کد نقش: {roleContext.key}
                        </span>
                    </div>
                </section>

                <section className="grid gap-3 lg:grid-cols-[1.2fr_0.8fr]">
                    <article className="rounded-lg border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border">
                        <div className="flex items-center gap-2">
                            <BotMessageSquare className="size-5 text-primary" />
                            <h2 className="font-semibold">
                                پرسش‌های پیشنهادی چت‌بات
                            </h2>
                        </div>
                        <div className="mt-3 rounded-lg border border-dashed border-sidebar-border/70 bg-muted/30 p-4">
                            <div className="flex items-start gap-3">
                                <MessageSquareText className="mt-1 size-5 text-muted-foreground" />
                                <div>
                                    <p className="font-medium">
                                        پاسخ‌ها از بانک کنترل‌شده نقش فعلی
                                        انتخاب می‌شوند.
                                    </p>
                                    <p className="mt-2 text-sm leading-7 text-muted-foreground">
                                        این صفحه هنوز موتور گفت‌وگوی آزاد ندارد؛
                                        اما سؤال‌ها، پاسخ‌ها و مسیرهای اقدام
                                        برای هر نقش جدا شده‌اند تا در UAT و
                                        اتصال چت‌بات هوشمند، محتوای نامرتبط به
                                        نقش کاربر نمایش داده نشود.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div className="mt-4 grid gap-3 md:grid-cols-2">
                            {prompts.map((prompt) => (
                                <article
                                    key={`${prompt.groupTitle}-${prompt.question}`}
                                    className="rounded-md border border-border/70 bg-background p-3 text-sm"
                                >
                                    <p className="text-xs font-medium text-muted-foreground">
                                        {prompt.groupTitle}
                                    </p>
                                    <h3 className="mt-2 leading-7 font-semibold">
                                        {prompt.question}
                                    </h3>
                                    <p className="mt-2 leading-7 text-muted-foreground">
                                        {prompt.answer}
                                    </p>
                                    {prompt.routeHref && prompt.routeLabel ? (
                                        <Link
                                            href={prompt.routeHref}
                                            className="mt-3 inline-flex items-center gap-2 rounded-md border border-input bg-background px-3 py-2 text-xs font-medium"
                                        >
                                            <Route className="size-3.5" />
                                            {prompt.routeLabel}
                                        </Link>
                                    ) : null}
                                </article>
                            ))}
                        </div>
                    </article>

                    <article className="rounded-lg border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border">
                        <div className="flex items-center gap-2">
                            <ShieldAlert className="size-5 text-primary" />
                            <h2 className="font-semibold">اولویت پشتیبانی</h2>
                        </div>
                        <ol className="mt-3 space-y-2 text-sm leading-7 text-muted-foreground">
                            <li>
                                ۱. خطایی که مانع ورود، اسکن QR یا شروع مسیر
                                می‌شود.
                            </li>
                            <li>
                                ۲. مأموریت، پاداش یا کد مصرفی که وضعیت نامشخص
                                دارد.
                            </li>
                            <li>
                                ۳. دسترسی، نقش یا محدوده‌ای که با مسئولیت کاربر
                                همخوان نیست.
                            </li>
                            <li>
                                ۴. تبلیغ، نمایشگر یا گزارش عملیاتی که با اجرای
                                واقعی نمی‌خواند.
                            </li>
                        </ol>
                        <div className="mt-4 rounded-md bg-muted/40 p-3 text-sm leading-7 text-muted-foreground">
                            {handoffNotes.map((note) => (
                                <p key={note}>{note}</p>
                            ))}
                        </div>
                    </article>
                </section>

                <section className="grid gap-3 md:grid-cols-3">
                    {quickActions.map((action) => (
                        <article
                            key={action.title}
                            className="rounded-lg border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
                        >
                            <CircleHelp className="size-5 text-primary" />
                            <h2 className="mt-3 font-semibold">
                                {action.title}
                            </h2>
                            <p className="mt-2 min-h-20 text-sm leading-7 text-muted-foreground">
                                {action.body}
                            </p>
                            {action.href && action.label ? (
                                <Link
                                    href={action.href}
                                    className="mt-3 inline-flex rounded-md border border-input bg-background px-3 py-2 text-sm font-medium"
                                >
                                    {action.label}
                                </Link>
                            ) : null}
                        </article>
                    ))}
                </section>

                <section className="rounded-lg border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border">
                    <div className="flex items-center gap-2">
                        <ClipboardCheck className="size-5 text-primary" />
                        <h2 className="font-semibold">
                            چک‌لیست قبل از ارجاع به پشتیبانی فنی
                        </h2>
                    </div>
                    <div className="mt-3 grid gap-3 md:grid-cols-4">
                        {checklist.map((item) => (
                            <div
                                key={item}
                                className="rounded-md bg-muted/40 p-3 text-sm leading-7"
                            >
                                {item}
                            </div>
                        ))}
                    </div>
                </section>
            </main>
        </>
    );
}
