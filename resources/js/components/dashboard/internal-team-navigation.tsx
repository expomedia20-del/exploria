import { Link } from '@inertiajs/react';
import {
    BadgeDollarSign,
    CheckCircle2,
    ClipboardCheck,
    MapPinned,
    MonitorCheck,
} from 'lucide-react';

type InternalStage = {
    key: string;
    label: string;
    description: string;
    icon: typeof ClipboardCheck;
    pages: {
        href: string;
        label: string;
    }[];
};

const stages: InternalStage[] = [
    {
        key: 'readiness',
        label: 'فرماندهی و آمادگی',
        description: 'نقش‌ها، مکان و کنترل دموی کامل',
        icon: ClipboardCheck,
        pages: [
            { href: '/admin/internal-operations', label: 'عملیات داخلی' },
            { href: '/admin/venues', label: 'ارزیابی مکان' },
            { href: '/admin/demo-cycle', label: 'چرخه دمو' },
        ],
    },
    {
        key: 'design',
        label: 'طراحی و ثبت',
        description: 'الگو، ثبت و آماده‌سازی کمپین',
        icon: MapPinned,
        pages: [
            { href: '/admin/mission-blueprints', label: 'گنجینه الگوها' },
            { href: '/admin/campaigns', label: 'ثبت کمپین' },
            { href: '/admin/campaign-builder', label: 'ساخت کمپین' },
        ],
    },
    {
        key: 'execution',
        label: 'اجرا و مسیر',
        description: 'مأموریت، QR و نقشه عملیات',
        icon: CheckCircle2,
        pages: [
            { href: '/admin/missions', label: 'مأموریت و پاداش' },
            { href: '/admin/qr-codes', label: 'مدیریت QR' },
            { href: '/admin/campaign-operations', label: 'نقشه عملیات' },
        ],
    },
    {
        key: 'monitoring',
        label: 'پایش و رسانه',
        description: 'رویداد اسکن و اجرای نمایشگر',
        icon: MonitorCheck,
        pages: [
            { href: '/admin/events/scan-log', label: 'پایش رویدادها' },
            { href: '/admin/display-operations', label: 'عملیات نمایشگرها' },
        ],
    },
    {
        key: 'business',
        label: 'فروش و اقتصاد',
        description: 'تجاری‌سازی، قرارداد و دفترکل',
        icon: BadgeDollarSign,
        pages: [
            { href: '/admin/commercialization', label: 'تجاری‌سازی' },
            { href: '/admin/marketing-leads', label: 'صندوق درخواست دمو' },
            { href: '/admin/finance-wallets', label: 'اقتصاد و کیف پول' },
        ],
    },
];

export function InternalTeamNavigation({ activeHref }: { activeHref: string }) {
    const activeStageIndex = Math.max(
        0,
        stages.findIndex((stage) =>
            stage.pages.some((page) => page.href === activeHref),
        ),
    );
    const activeStage = stages[activeStageIndex];
    const activePageIndex = Math.max(
        0,
        activeStage.pages.findIndex((page) => page.href === activeHref),
    );
    const nextPage =
        activeStage.pages[activePageIndex + 1] ??
        stages[activeStageIndex + 1]?.pages[0] ??
        null;

    return (
        <section
            aria-label="نقشه عملیات تیم داخلی اکسپلوریا"
            className="rounded-xl border border-sidebar-border/70 bg-card p-3 shadow-sm dark:border-sidebar-border"
        >
            <div className="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <p className="text-sm font-semibold">
                    نقشه عملیات تیم داخلی اکسپلوریا
                </p>
                <p className="text-xs leading-5 text-muted-foreground">
                    {nextPage
                        ? `گام پیشنهادی بعدی: ${nextPage.label}`
                        : 'گردش‌کار اصلی این مرحله تکمیل شده است.'}
                </p>
            </div>

            <nav className="grid grid-cols-2 gap-2 lg:grid-cols-5">
                {stages.map((stage, index) => {
                    const Icon = stage.icon;
                    const isActive = stage.key === activeStage.key;
                    const target =
                        isActive && activeHref
                            ? activeHref
                            : stage.pages[0].href;

                    return (
                        <Link
                            key={stage.key}
                            href={target}
                            aria-current={isActive ? 'step' : undefined}
                            className={`rounded-lg border p-3 transition-colors ${
                                isActive
                                    ? 'border-primary bg-primary text-primary-foreground'
                                    : 'border-sidebar-border/70 bg-background hover:border-primary/50 hover:bg-muted/40 dark:border-sidebar-border'
                            }`}
                        >
                            <span className="flex items-center gap-2 text-sm font-medium">
                                <span
                                    className={`flex size-6 shrink-0 items-center justify-center rounded-full text-xs ${
                                        isActive
                                            ? 'bg-primary-foreground/15'
                                            : 'bg-muted'
                                    }`}
                                >
                                    {(index + 1).toLocaleString('fa-IR')}
                                </span>
                                <Icon className="size-4 shrink-0" />
                                <span>{stage.label}</span>
                            </span>
                            <span
                                className={`mt-1 hidden text-xs leading-5 sm:block ${
                                    isActive
                                        ? 'text-primary-foreground/80'
                                        : 'text-muted-foreground'
                                }`}
                            >
                                {stage.description}
                            </span>
                        </Link>
                    );
                })}
            </nav>

            <nav
                aria-label={`صفحات مرحله ${activeStage.label}`}
                className="mt-3 flex flex-wrap gap-2 border-t border-sidebar-border/70 pt-3 dark:border-sidebar-border"
            >
                <span className="py-1.5 text-xs text-muted-foreground">
                    در این مرحله:
                </span>
                {activeStage.pages.map((page) => {
                    const isActive = page.href === activeHref;

                    return (
                        <Link
                            key={page.href}
                            href={page.href}
                            aria-current={isActive ? 'page' : undefined}
                            className={`rounded-full px-3 py-1.5 text-xs font-medium ${
                                isActive
                                    ? 'bg-foreground text-background'
                                    : 'bg-muted text-muted-foreground hover:text-foreground'
                            }`}
                        >
                            {page.label}
                        </Link>
                    );
                })}
            </nav>
        </section>
    );
}
