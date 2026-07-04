import { Head, Link } from '@inertiajs/react';
import {
    ClipboardCheck,
    Megaphone,
    MonitorPlay,
    QrCode,
    Route,
    ShieldCheck,
    Trophy,
    UsersRound,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import type { ReactNode } from 'react';

type Workstream = {
    title: string;
    owner: string;
    description: string;
    icon: LucideIcon;
    links: Array<{
        label: string;
        href: string;
    }>;
    guardrails: string[];
};

const workstreams: Workstream[] = [
    {
        title: 'فرماندهی پایلوت و readiness',
        owner: 'مدیر پروژه مکانی اکسپلوریا',
        description:
            'مرکز هماهنگی کمپین، مکان، مدیران زون، تیم میدانی، فروشگاه‌ها، اسپانسرها و خروجی روز اجرا.',
        icon: ClipboardCheck,
        links: [
            { label: 'کارگاه ساخت کمپین', href: '/admin/campaign-builder' },
            { label: 'نقشه عملیات کمپین', href: '/admin/campaign-operations' },
            { label: 'ارزیابی مکان', href: '/admin/venues' },
        ],
        guardrails: [
            'تصمیم تجاری فروشگاه یا اسپانسر را به جای مالک آن نمی‌گیرد.',
            'تغییر سیاست کلان یا قرارداد مادر را به ادمین مرکزی ارجاع می‌دهد.',
        ],
    },
    {
        title: 'طراحی تجربه، مسیر و ماموریت',
        owner: 'تیم طراحی تجربه اکسپلوریا',
        description:
            'تنظیم مسیر، QR، ماموریت‌ها، گنج‌ها و پاداش‌ها برای اینکه دمو از ابتدا تا انتها اجرا شود.',
        icon: Trophy,
        links: [
            { label: 'ماموریت، گنج و پاداش', href: '/admin/missions' },
            { label: 'مدیریت QR و ورود', href: '/admin/qr-codes' },
            { label: 'ثبت و انتخاب کمپین', href: '/admin/campaigns' },
        ],
        guardrails: [
            'فقط چیزهایی را جلو می‌برد که به دمو، فروش یا اجرای واقعی کمک کند.',
            'جزئیات اجرایی مکان و رواق را با مالک همان محدوده هماهنگ می‌کند.',
        ],
    },
    {
        title: 'اجرای میدانی و پشتیبانی کاربر',
        owner: 'مجری میدانی و یاریگر کاربران',
        description:
            'جذب مشارکت، راهنمایی بازدیدکننده، ثبت خطاها و رفع مانع‌های روز اجرا در میدان.',
        icon: UsersRound,
        links: [
            { label: 'اعضا، فروشگاه‌ها و شرکا', href: '/admin/campaign-participants' },
            { label: 'نقشه عملیات کمپین', href: '/admin/campaign-operations' },
            { label: 'داشبورد عملیاتی', href: '/dashboard' },
        ],
        guardrails: [
            'به تنظیمات مالی، پاداش، قرارداد یا تبلیغ دست نمی‌زند.',
            'خطا و بازخورد را ثبت و به مدیر پروژه اکسپلوریا ارجاع می‌دهد.',
        ],
    },
    {
        title: 'تبلیغات، نمایشگر و شواهد پخش',
        owner: 'مدیر تبلیغات و نمایشگرهای اکسپلوریا',
        description:
            'بررسی محتوا، زمان‌بندی، سلامت نمایشگرها و ثبت شواهد قابل گزارش برای تبلیغات.',
        icon: MonitorPlay,
        links: [
            { label: 'عملیات تبلیغات و نمایشگرها', href: '/admin/display-operations' },
            { label: 'تبلیغات مستقل', href: '/admin/ads' },
            { label: 'اسپانسرها و درآمد', href: '/admin/sponsors' },
        ],
        guardrails: [
            'قرارداد و قیمت بسته اسپانسری را تغییر نمی‌دهد.',
            'محدودیت‌های مکان و رواق را برای پخش رعایت می‌کند.',
        ],
    },
];

const principles = [
    'هر کار باید به دمو، فروش یا اجرای واقعی کمک کند.',
    'تیم داخلی اکسپلوریا مالک هماهنگی و اجراست، نه مالک تصمیم تجاری فروشگاه‌ها.',
    'تصمیم پخش تبلیغ با تیم نمایشگر است؛ نظر رواق و مکان برای محدودیت اجرایی ثبت می‌شود.',
    'گزارش ROI و readiness باید خروجی مشترک همه جریان‌ها باشد.',
];

function Panel({
    title,
    children,
    description,
}: {
    title: string;
    children: ReactNode;
    description?: string;
}) {
    return (
        <section className="rounded-lg border border-sidebar-border/70 bg-background dark:border-sidebar-border">
            <div className="border-b border-sidebar-border/70 px-4 py-3 dark:border-sidebar-border">
                <h2 className="font-semibold">{title}</h2>
                {description ? (
                    <p className="mt-1 text-sm leading-6 text-muted-foreground">
                        {description}
                    </p>
                ) : null}
            </div>
            {children}
        </section>
    );
}

export default function InternalOperationsIndex() {
    return (
        <>
            <Head title="پنل عملیات داخلی اکسپلوریا" />
            <div
                dir="rtl"
                className="flex h-full flex-1 flex-col gap-5 overflow-x-auto p-4"
            >
                <header className="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p className="text-sm text-muted-foreground">
                            مرکز فرمان سبک برای تیم داخلی اکسپلوریا
                        </p>
                        <h1 className="mt-1 text-2xl font-semibold">
                            پنل عملیات داخلی اکسپلوریا
                        </h1>
                    </div>
                    <div className="grid grid-cols-2 gap-2 text-sm md:grid-cols-4">
                        {[
                            ['پروژه', 'readiness'],
                            ['میدان', 'اجرا'],
                            ['نمایشگر', 'پخش'],
                            ['گزارش', 'ROI'],
                        ].map(([label, value]) => (
                            <div
                                key={label}
                                className="rounded-lg border border-sidebar-border/70 px-3 py-2 dark:border-sidebar-border"
                            >
                                <p className="text-muted-foreground">{label}</p>
                                <p className="mt-1 font-semibold">{value}</p>
                            </div>
                        ))}
                    </div>
                </header>

                <Panel
                    title="قاعده کار تیم داخلی"
                    description="این پنل جایگزین پنل ادمین مرکزی نیست؛ فقط مسیرهای اجرایی تیم داخلی را در یک نگاه جمع می‌کند."
                >
                    <div className="grid gap-3 p-4 md:grid-cols-2 xl:grid-cols-4">
                        {principles.map((principle) => (
                            <div
                                key={principle}
                                className="rounded-md bg-muted/40 p-3 text-sm leading-7 text-muted-foreground"
                            >
                                {principle}
                            </div>
                        ))}
                    </div>
                </Panel>

                <section className="grid gap-4 xl:grid-cols-2">
                    {workstreams.map((stream) => (
                        <article
                            key={stream.title}
                            className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border"
                        >
                            <div className="flex gap-3">
                                <div className="flex size-10 shrink-0 items-center justify-center rounded-md bg-muted">
                                    <stream.icon className="size-5" />
                                </div>
                                <div className="min-w-0">
                                    <h2 className="font-semibold">{stream.title}</h2>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        مسئول: {stream.owner}
                                    </p>
                                </div>
                            </div>
                            <p className="mt-3 text-sm leading-7 text-muted-foreground">
                                {stream.description}
                            </p>

                            <div className="mt-4 flex flex-wrap gap-2">
                                {stream.links.map((link) => (
                                    <Link
                                        key={link.href}
                                        href={link.href}
                                        className="inline-flex h-9 items-center rounded-md border border-input bg-background px-3 text-sm font-medium hover:bg-accent hover:text-accent-foreground"
                                    >
                                        {link.label}
                                    </Link>
                                ))}
                            </div>

                            <div className="mt-4 rounded-md bg-muted/35 p-3">
                                <div className="mb-2 flex items-center gap-2 text-sm font-medium">
                                    <ShieldCheck className="size-4" />
                                    مرز اختیار
                                </div>
                                <ul className="space-y-2 text-sm text-muted-foreground">
                                    {stream.guardrails.map((guardrail) => (
                                        <li key={guardrail} className="leading-7">
                                            {guardrail}
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        </article>
                    ))}
                </section>

                <Panel
                    title="میان‌برهای روز اجرا"
                    description="برای اجرای پایلوت، این مسیرها بیشترین استفاده را برای تیم داخلی دارند."
                >
                    <div className="grid gap-3 p-4 sm:grid-cols-2 xl:grid-cols-4">
                        {[
                            {
                                icon: Route,
                                label: 'نقشه عملیات',
                                href: '/admin/campaign-operations',
                            },
                            {
                                icon: QrCode,
                                label: 'QR و ورود',
                                href: '/admin/qr-codes',
                            },
                            {
                                icon: Megaphone,
                                label: 'تبلیغات',
                                href: '/admin/ads',
                            },
                            {
                                icon: ClipboardCheck,
                                label: 'ساخت کمپین',
                                href: '/admin/campaign-builder',
                            },
                        ].map((item) => (
                            <Link
                                key={item.href}
                                href={item.href}
                                className="flex items-center gap-3 rounded-md border border-sidebar-border/70 p-3 text-sm font-medium hover:bg-muted/50 dark:border-sidebar-border"
                            >
                                <item.icon className="size-4" />
                                {item.label}
                            </Link>
                        ))}
                    </div>
                </Panel>
            </div>
        </>
    );
}

InternalOperationsIndex.layout = {
    breadcrumbs: [
        {
            title: 'پنل عملیات داخلی اکسپلوریا',
            href: '/admin/internal-operations',
        },
    ],
};
