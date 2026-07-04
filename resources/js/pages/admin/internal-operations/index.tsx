import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    ClipboardCheck,
    Eye,
    KeyRound,
    Megaphone,
    MonitorPlay,
    Network,
    QrCode,
    Route,
    ShieldCheck,
    Trophy,
    UserCheck,
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

type TeamMember = {
    id: string;
    user: {
        id: number | null;
        name: string;
        email: string | null;
        accountRole: string | null;
        accountRoleLabel: string;
    };
    roleKey: string;
    roleLabel: string;
    scopeType: string;
    scopeTypeLabel: string;
    scopeId: string | null;
    scopeLabel: string;
    reportsToKey: string | null;
    reportsToLabel: string | null;
    defaultAccountRole: string;
    entryHref: string;
    entryLabel: string;
    subordinateCount: number;
};

type SupervisionLine = {
    key: string;
    label: string;
    reportsToKey: string | null;
    reportsToLabel: string | null;
    defaultAccountRole: string;
    entryHref: string;
    scopeLabel: string;
    activeCount: number;
};

type Props = {
    stats: {
        internalUsers: number;
        activeAssignments: number;
        supervisorRoles: number;
        unassignedSupervisorLinks: number;
    };
    teamMembers: TeamMember[];
    supervisionLines: SupervisionLine[];
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
    'هر نفر با اکانت خودش وارد می‌شود، اما نقش دقیق و محدوده کاری از تخصیص دسترسی مشخص می‌شود.',
    'مدیر بالادست، زیرمجموعه را بر اساس reports_to و scope می‌بیند، نه بر اساس حدس یا عنوان دستی.',
    'صفحه شروع هر نقش باید کوتاه و عملیاتی باشد؛ تصمیم‌های تجاری در پنل مالک همان کسب‌وکار می‌ماند.',
    'پنل داخلی مرکز کنترل تیم اکسپلوریاست، نه جایگزین پنل مدیر مکان، رواق، فروشگاه یا اسپانسر.',
];

function numberFa(value: number) {
    return value.toLocaleString('fa-IR');
}

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

function Stat({
    icon: Icon,
    label,
    value,
}: {
    icon: LucideIcon;
    label: string;
    value: number;
}) {
    return (
        <div className="rounded-lg border border-sidebar-border/70 px-3 py-2 dark:border-sidebar-border">
            <div className="flex items-center gap-2 text-muted-foreground">
                <Icon className="size-4" />
                <span className="text-sm">{label}</span>
            </div>
            <p className="mt-2 text-xl font-semibold">{numberFa(value)}</p>
        </div>
    );
}

function EmptyState() {
    return (
        <div className="p-4 text-sm leading-7 text-muted-foreground">
            هنوز نقش داخلی فعالی ثبت نشده است. از صفحه تخصیص دسترسی، برای
            کاربران داخلی نقش‌هایی مثل مدیر پروژه، مجری میدانی یا مدیر نمایشگر
            تعریف کنید.
        </div>
    );
}

function TeamMemberCard({ member }: { member: TeamMember }) {
    return (
        <article className="rounded-lg border border-sidebar-border/70 p-4 dark:border-sidebar-border">
            <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                <div>
                    <h3 className="font-semibold">{member.user.name}</h3>
                    <p className="mt-1 text-sm text-muted-foreground">
                        {member.user.email ?? 'ایمیل ثبت نشده'}
                    </p>
                </div>
                <Link
                    href={member.entryHref}
                    className="inline-flex h-9 items-center gap-2 rounded-md border border-input bg-background px-3 text-sm font-medium hover:bg-accent hover:text-accent-foreground"
                >
                    {member.entryLabel}
                    <ArrowLeft className="size-4" />
                </Link>
            </div>

            <div className="mt-4 grid gap-3 text-sm md:grid-cols-2 xl:grid-cols-4">
                <InfoTile
                    icon={KeyRound}
                    label="اکانت ورود"
                    value={`${member.user.accountRoleLabel} / پیشنهادی: ${member.defaultAccountRole}`}
                />
                <InfoTile
                    icon={UserCheck}
                    label="نقش عملیاتی"
                    value={member.roleLabel}
                />
                <InfoTile
                    icon={Network}
                    label="محدوده"
                    value={`${member.scopeTypeLabel}: ${member.scopeLabel}`}
                />
                <InfoTile
                    icon={Eye}
                    label="نظارت"
                    value={
                        member.reportsToLabel
                            ? `گزارش به ${member.reportsToLabel}`
                            : 'نقش بالادست ندارد'
                    }
                />
            </div>

            <div className="mt-3 rounded-md bg-muted/35 p-3 text-sm leading-7 text-muted-foreground">
                زیرمجموعه مستقیم این نقش:{' '}
                <span className="font-semibold text-foreground">
                    {numberFa(member.subordinateCount)}
                </span>
                . مدیر بالادست باید همین افراد و کارهای باز مربوط به محدوده خودش
                را در گزارش روزانه کنترل کند.
            </div>
        </article>
    );
}

function InfoTile({
    icon: Icon,
    label,
    value,
}: {
    icon: LucideIcon;
    label: string;
    value: string;
}) {
    return (
        <div className="rounded-md bg-muted/35 p-3">
            <div className="mb-2 flex items-center gap-2 text-muted-foreground">
                <Icon className="size-4" />
                <span>{label}</span>
            </div>
            <p className="leading-7">{value}</p>
        </div>
    );
}

export default function InternalOperationsIndex({
    stats = {
        internalUsers: 0,
        activeAssignments: 0,
        supervisorRoles: 0,
        unassignedSupervisorLinks: 0,
    },
    teamMembers = [],
    supervisionLines = [],
}: Props) {
    return (
        <>
            <Head title="پنل عملیات داخلی اکسپلوریا" />
            <div
                dir="rtl"
                className="flex h-full flex-1 flex-col gap-5 overflow-x-auto p-4"
            >
                <header className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p className="text-sm text-muted-foreground">
                            مرکز فرمان سبک برای تیم داخلی اکسپلوریا
                        </p>
                        <h1 className="mt-1 text-2xl font-semibold">
                            پنل عملیات داخلی اکسپلوریا
                        </h1>
                    </div>
                    <div className="grid grid-cols-2 gap-3 text-sm lg:grid-cols-4">
                        <Stat
                            icon={UsersRound}
                            label="کاربر داخلی"
                            value={stats.internalUsers}
                        />
                        <Stat
                            icon={KeyRound}
                            label="تخصیص فعال"
                            value={stats.activeAssignments}
                        />
                        <Stat
                            icon={Network}
                            label="خط نظارت"
                            value={stats.supervisorRoles}
                        />
                        <Stat
                            icon={ShieldCheck}
                            label="نیازمند بالادست"
                            value={stats.unassignedSupervisorLinks}
                        />
                    </div>
                </header>

                <Panel
                    title="قاعده ورود، نقش و نظارت"
                    description="اکانت ورود فقط در را باز می‌کند؛ نقش دقیق، صفحه شروع، محدوده کار و مدیر بالادست از تخصیص دسترسی تعیین می‌شود."
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

                <Panel
                    title="اعضای داخلی و مسیر شروع کار"
                    description="این بخش پاسخ عملی به این است که هر نفر با چه اکانتی وارد می‌شود، صفحه شروع او کجاست و به چه نقشی گزارش می‌دهد."
                >
                    <div className="grid gap-3 p-4">
                        {teamMembers.length > 0 ? (
                            teamMembers.map((member) => (
                                <TeamMemberCard key={member.id} member={member} />
                            ))
                        ) : (
                            <EmptyState />
                        )}
                    </div>
                </Panel>

                <Panel
                    title="نقشه سلسله‌مراتب تیم داخلی"
                    description="این جدول مدل پایه نظارت را نشان می‌دهد؛ با اضافه شدن اکانت‌های واقعی، ستون تعداد فعال پر می‌شود."
                >
                    <div className="overflow-x-auto">
                        <table className="w-full min-w-[820px] text-sm">
                            <thead className="bg-muted/40 text-muted-foreground">
                                <tr>
                                    <th className="px-4 py-3 text-right font-medium">
                                        نقش
                                    </th>
                                    <th className="px-4 py-3 text-right font-medium">
                                        اکانت پیشنهادی
                                    </th>
                                    <th className="px-4 py-3 text-right font-medium">
                                        محدوده پیش‌فرض
                                    </th>
                                    <th className="px-4 py-3 text-right font-medium">
                                        گزارش به
                                    </th>
                                    <th className="px-4 py-3 text-right font-medium">
                                        صفحه شروع
                                    </th>
                                    <th className="px-4 py-3 text-right font-medium">
                                        فعال
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {supervisionLines.map((line) => (
                                    <tr
                                        key={line.key}
                                        className="border-t border-sidebar-border/70"
                                    >
                                        <td className="px-4 py-3 font-medium">
                                            {line.label}
                                        </td>
                                        <td className="px-4 py-3 text-muted-foreground">
                                            {line.defaultAccountRole}
                                        </td>
                                        <td className="px-4 py-3 text-muted-foreground">
                                            {line.scopeLabel}
                                        </td>
                                        <td className="px-4 py-3 text-muted-foreground">
                                            {line.reportsToLabel ?? 'مستقل'}
                                        </td>
                                        <td className="px-4 py-3">
                                            <Link
                                                href={line.entryHref}
                                                className="text-primary hover:underline"
                                            >
                                                {line.entryHref}
                                            </Link>
                                        </td>
                                        <td className="px-4 py-3 font-semibold">
                                            {numberFa(line.activeCount)}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
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
