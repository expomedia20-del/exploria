import { Head, Link } from '@inertiajs/react';
import {
    BotMessageSquare,
    CircleHelp,
    ClipboardCheck,
    MessageSquareText,
    ShieldAlert,
} from 'lucide-react';

const quickActions = [
    {
        title: 'مشکل اجرای کمپین',
        body: 'اگر QR، ماموریت، پاداش یا مسیر کاربر درست کار نمی‌کند، اول نقشه عملیات کمپین را بررسی کنید.',
        href: '/admin/campaign-operations',
        label: 'نقشه عملیات',
    },
    {
        title: 'مشکل نمایشگر یا تبلیغ',
        body: 'برای خطای پخش، آفلاین بودن نمایشگر، یا تبلیغ تاییدشده بدون زمان‌بندی از این مسیر شروع کنید.',
        href: '/admin/display-operations',
        label: 'عملیات نمایشگرها',
    },
    {
        title: 'مشکل کاربر یا دسترسی',
        body: 'برای تغییر نقش، بستن دسترسی، یا تشخیص کاربر عادی و مشارکت‌کننده به مدیریت کاربران بروید.',
        href: '/admin/users',
        label: 'مدیریت کاربران',
    },
];

const botPrompts = [
    'چرا کاربر بعد از ورود موبایل کمپین فعال نمی‌بیند؟',
    'کدام تبلیغ تایید شده ولی هنوز روی نمایشگر زمان‌بندی نشده است؟',
    'برای یک فروشگاه جدید چه اکانت و چه دسترسی باید بسازم؟',
    'کدام پاداش‌ها در انتظار مصرف یا تایید فروشگاه هستند؟',
];

export default function SupportCenterIndex() {
    return (
        <>
            <Head title="پشتیبانی و چت‌بات" />

            <main className="space-y-4 p-4" dir="rtl">
                <section className="rounded-lg border border-sidebar-border/70 bg-gradient-to-l from-cyan-50 to-background p-4 dark:border-sidebar-border dark:from-cyan-950/30">
                    <p className="text-sm text-muted-foreground">
                        مرکز پشتیبانی عملیاتی برای دمو، اجرا و رفع خطای سریع
                    </p>
                    <h1 className="mt-1 text-2xl font-semibold">
                        پشتیبانی و چت‌بات اکسپلوریا
                    </h1>
                    <p className="mt-2 max-w-4xl text-sm leading-7 text-muted-foreground">
                        این نسخه، مرکز راهنمای سریع و مسیرهای عیب‌یابی را آماده می‌کند.
                        اتصال چت‌بات هوشمند در مرحله بعد روی همین صفحه انجام می‌شود تا از داده‌های
                        کمپین، کاربر، پاداش و نمایشگر پاسخ عملیاتی بدهد.
                    </p>
                </section>

                <section className="grid gap-3 lg:grid-cols-[1.2fr_0.8fr]">
                    <article className="rounded-lg border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border">
                        <div className="flex items-center gap-2">
                            <BotMessageSquare className="size-5 text-primary" />
                            <h2 className="font-semibold">جایگاه چت‌بات عملیاتی</h2>
                        </div>
                        <div className="mt-3 rounded-lg border border-dashed border-sidebar-border/70 bg-muted/30 p-4">
                            <div className="flex items-start gap-3">
                                <MessageSquareText className="mt-1 size-5 text-muted-foreground" />
                                <div>
                                    <p className="font-medium">چت‌بات هنوز به موتور پاسخ‌گو وصل نشده است.</p>
                                    <p className="mt-2 text-sm leading-7 text-muted-foreground">
                                        فعلا این صفحه به عنوان نقطه ورود پشتیبانی استفاده می‌شود. در نسخه بعد،
                                        همین بخش می‌تواند به موتور پرسش و پاسخ متصل شود و از وضعیت واقعی
                                        کمپین‌ها، کاربران، پاداش‌ها و نمایشگرها پاسخ بدهد.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div className="mt-4 grid gap-2 md:grid-cols-2">
                            {botPrompts.map((prompt) => (
                                <div
                                    key={prompt}
                                    className="rounded-md border border-border/70 bg-background p-3 text-sm"
                                >
                                    {prompt}
                                </div>
                            ))}
                        </div>
                    </article>

                    <article className="rounded-lg border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border">
                        <div className="flex items-center gap-2">
                            <ShieldAlert className="size-5 text-primary" />
                            <h2 className="font-semibold">اولویت پشتیبانی</h2>
                        </div>
                        <ol className="mt-3 space-y-2 text-sm leading-7 text-muted-foreground">
                            <li>۱. خطایی که مانع ورود کاربر یا شروع کمپین می‌شود.</li>
                            <li>۲. پاداشی که صادر شده ولی فروشگاه نمی‌تواند مصرف کند.</li>
                            <li>۳. تبلیغ تاییدشده‌ای که روی نمایشگر پخش نمی‌شود.</li>
                            <li>۴. دسترسی مدیریتی یا اکانتی که اشتباه تعریف شده است.</li>
                        </ol>
                    </article>
                </section>

                <section className="grid gap-3 md:grid-cols-3">
                    {quickActions.map((action) => (
                        <article
                            key={action.title}
                            className="rounded-lg border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
                        >
                            <CircleHelp className="size-5 text-primary" />
                            <h2 className="mt-3 font-semibold">{action.title}</h2>
                            <p className="mt-2 min-h-20 text-sm leading-7 text-muted-foreground">
                                {action.body}
                            </p>
                            <Link
                                href={action.href}
                                className="mt-3 inline-flex rounded-md border border-input bg-background px-3 py-2 text-sm font-medium"
                            >
                                {action.label}
                            </Link>
                        </article>
                    ))}
                </section>

                <section className="rounded-lg border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border">
                    <div className="flex items-center gap-2">
                        <ClipboardCheck className="size-5 text-primary" />
                        <h2 className="font-semibold">چک‌لیست قبل از ارجاع به پشتیبانی فنی</h2>
                    </div>
                    <div className="mt-3 grid gap-3 md:grid-cols-4">
                        {[
                            'آیا کاربر با نقش درست وارد شده است؟',
                            'آیا کمپین فعال و دارای QR معتبر است؟',
                            'آیا پاداش به فروشگاه یا اسپانسر درست وصل شده است؟',
                            'آیا نمایشگر آنلاین و دارای زمان‌بندی فعال است؟',
                        ].map((item) => (
                            <div key={item} className="rounded-md bg-muted/40 p-3 text-sm">
                                {item}
                            </div>
                        ))}
                    </div>
                </section>
            </main>
        </>
    );
}
