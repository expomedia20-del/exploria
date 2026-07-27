import { Link } from '@inertiajs/react';
import {
    BadgeDollarSign,
    BookOpen,
    CheckCircle2,
    ListChecks,
    Megaphone,
    ShieldCheck,
    ShoppingBag,
    Store,
    UserCog,
    UsersRound,
} from 'lucide-react';

type WorkflowKey = 'commercial' | 'access' | 'partner' | 'participant';

type WorkflowItem = {
    href: string;
    label: string;
    description: string;
    icon: typeof Store;
};

const workflowItems: Record<WorkflowKey, WorkflowItem[]> = {
    commercial: [
        {
            href: '/admin/partners',
            label: 'ثبت و اتصال واحد',
            description: 'هویت واحد، مسئول و مکان فعالیت',
            icon: Store,
        },
        {
            href: '/admin/campaign-participants',
            label: 'نقش در کمپین',
            description: 'عضویت و مسئولیت اجرایی واحد',
            icon: UsersRound,
        },
        {
            href: '/admin/sponsors',
            label: 'حمایت و درآمد',
            description: 'بسته، پیشنهاد و اتصال اسپانسر',
            icon: BadgeDollarSign,
        },
        {
            href: '/admin/ads',
            label: 'تأیید تبلیغات',
            description: 'بازبینی، انتشار و پایش محتوا',
            icon: Megaphone,
        },
    ],
    access: [
        {
            href: '/admin/role-operations',
            label: 'شناخت نقش',
            description: 'اختیار، مسئولیت و پنل هر نقش',
            icon: ShieldCheck,
        },
        {
            href: '/admin/users',
            label: 'انتخاب حساب',
            description: 'جست‌وجو و کنترل نقش پایه',
            icon: UsersRound,
        },
        {
            href: '/admin/access-scopes',
            label: 'تخصیص دسترسی',
            description: 'نقش عملیاتی و محدوده مجاز',
            icon: UserCog,
        },
        {
            href: '/admin/users/guide',
            label: 'راهنمای کنترل',
            description: 'روال امن تغییر و غیرفعال‌سازی',
            icon: BookOpen,
        },
    ],
    partner: [
        {
            href: '/partner/dashboard',
            label: 'مرکز کار فروشگاه',
            description: 'وضعیت، پیشنهاد، پاداش و مصرف',
            icon: Store,
        },
        {
            href: '/partner/ads',
            label: 'تبلیغات فروشگاه',
            description: 'ثبت محتوا و پیگیری تأیید',
            icon: Megaphone,
        },
    ],
    participant: [
        {
            href: '/participant/dashboard',
            label: 'پنل من',
            description: 'کمپین فعال، پاداش و قدم بعدی',
            icon: ListChecks,
        },
        {
            href: '/offers',
            label: 'ویترین فروشگاه‌ها',
            description: 'پیشنهادها و تبلیغات عمومی',
            icon: ShoppingBag,
        },
    ],
};

const workflowTitles: Record<WorkflowKey, string> = {
    commercial: 'گردش‌کار واحد تجاری و اسپانسر',
    access: 'گردش‌کار نقش و دسترسی',
    partner: 'گردش‌کار فروشگاه',
    participant: 'مسیر کاربر اکسپلوریا',
};

export function WorkflowPageNavigation({
    workflow,
    activeHref,
}: {
    workflow: WorkflowKey;
    activeHref: string;
}) {
    const items = workflowItems[workflow];
    const activeIndex = Math.max(
        0,
        items.findIndex((item) => item.href === activeHref),
    );
    const nextItem = items[activeIndex + 1] ?? null;

    return (
        <section
            aria-label={workflowTitles[workflow]}
            className="rounded-xl border border-sidebar-border/70 bg-card p-3 shadow-sm dark:border-sidebar-border"
        >
            <div className="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div className="flex items-center gap-2">
                    <CheckCircle2 className="size-4 text-emerald-600" />
                    <p className="text-sm font-semibold">
                        {workflowTitles[workflow]}
                    </p>
                </div>
                <p className="text-xs leading-5 text-muted-foreground">
                    {nextItem
                        ? `پس از تکمیل این بخش: ${nextItem.label}`
                        : 'این بخش، آخرین ایستگاه این گردش‌کار است.'}
                </p>
            </div>

            <nav className="grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                {items.map((item, index) => {
                    const Icon = item.icon;
                    const isActive = item.href === activeHref;

                    return (
                        <Link
                            key={item.href}
                            href={item.href}
                            aria-current={isActive ? 'page' : undefined}
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
                                <span>{item.label}</span>
                            </span>
                            <span
                                className={`mt-1 block text-xs leading-5 ${
                                    isActive
                                        ? 'text-primary-foreground/80'
                                        : 'text-muted-foreground'
                                }`}
                            >
                                {item.description}
                            </span>
                        </Link>
                    );
                })}
            </nav>
        </section>
    );
}
