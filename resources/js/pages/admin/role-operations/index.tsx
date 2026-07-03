import { Head } from '@inertiajs/react';
import {
    CalendarCheck2,
    ClipboardList,
    Layers3,
    Network,
    ShieldCheck,
    UserCog,
    UsersRound,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';

type RoleItem = {
    key: string;
    group: 'exploria_team' | 'external_partner' | 'public';
    label: string;
    scope: string;
    reportsTo: string | null;
    responsibilities: string[];
    dailyOperations: string[];
};

type Props = {
    roles: RoleItem[];
    scopeTypes: string[];
    stats: {
        totalRoles: number;
        exploriaTeamRoles: number;
        externalPartnerRoles: number;
        publicRoles: number;
        scopeTypes: number;
    };
};

const groupLabels: Record<RoleItem['group'], string> = {
    exploria_team: 'تیم اکسپلوریا',
    external_partner: 'نقش‌های بیرونی و شرکا',
    public: 'کاربران و مشارکت‌کنندگان',
};

const groupDescriptions: Record<RoleItem['group'], string> = {
    exploria_team:
        'نقش‌هایی که عملیات، سیاست‌گذاری، پشتیبانی و اجرای کمپین را از سمت اکسپلوریا پیش می‌برند.',
    external_partner:
        'مدیران مکان، هاب، فروشگاه و اسپانسرهایی که در اجرای تجاری و محلی پروژه نقش دارند.',
    public: 'بازدیدکننده یا مشارکت‌کننده‌ای که تجربه کمپین، ماموریت، گنج و پاداش را طی می‌کند.',
};

const scopeLabels: Record<string, string> = {
    global: 'کل اکسپلوریا',
    region: 'منطقه یا استان',
    venue: 'مکان پروژه',
    project: 'پروژه اجرایی',
    hub: 'هاب یا رواق',
    partner: 'فروشگاه یا واحد',
    campaign: 'کمپین',
    display_network: 'شبکه نمایشگرها',
    team: 'تیم یا خانواده',
};

const roleLabels: Record<string, string> = {
    super_admin: 'ادمین اصلی کل اکسپلوریا',
    regional_admin: 'ادمین منطقه‌ای',
    project_admin: 'مدیر پروژه مکانی اکسپلوریا',
    field_operator: 'مجری میدانی کمپین',
    treasure_assistant: 'یاریگر کاشفان گنج',
    display_ads_manager: 'مدیر تبلیغات و نمایشگرها',
    venue_executive: 'مدیر مکان',
    ravaq_manager: 'مدیر رواق / زون تجاری',
    hub_manager: 'مدیر هاب',
    shop_manager: 'مدیر فروشگاه / واحد شریک',
    internal_sponsor: 'اسپانسر داخلی مکان یا هاب',
    external_sponsor: 'اسپانسر مستقل / بیرونی',
    participant: 'بازدیدکننده / مشارکت‌کننده',
};

function labelForRole(key: string) {
    return roleLabels[key] ?? key;
}

function labelForScope(scope: string) {
    return scopeLabels[scope] ?? scope;
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
            <p className="mt-2 text-xl font-semibold">
                {value.toLocaleString('fa-IR')}
            </p>
        </div>
    );
}

function RoleCard({ role }: { role: RoleItem }) {
    return (
        <article className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
            <div className="flex flex-col gap-3 border-b border-sidebar-border/70 pb-4 sm:flex-row sm:items-start sm:justify-between dark:border-sidebar-border">
                <div className="min-w-0">
                    <div className="flex flex-wrap items-center gap-2">
                        <UserCog className="size-5 text-muted-foreground" />
                        <h3 className="text-lg font-semibold">
                            {labelForRole(role.key)}
                        </h3>
                    </div>
                    <p className="mt-1 text-sm text-muted-foreground">
                        {role.label}
                    </p>
                </div>
                <div className="flex flex-wrap gap-2 text-xs">
                    <span className="rounded-full bg-emerald-100 px-2.5 py-1 font-medium text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">
                        {labelForScope(role.scope)}
                    </span>
                    <span className="rounded-full bg-sky-100 px-2.5 py-1 font-medium text-sky-800 dark:bg-sky-950 dark:text-sky-200">
                        گزارش به:{' '}
                        {role.reportsTo
                            ? labelForRole(role.reportsTo)
                            : 'مستقل'}
                    </span>
                </div>
            </div>

            <div className="mt-4 grid gap-4 lg:grid-cols-2">
                <section>
                    <div className="mb-2 flex items-center gap-2 text-sm font-semibold">
                        <ClipboardList className="size-4 text-muted-foreground" />
                        وظایف اصلی
                    </div>
                    <ul className="space-y-2 text-sm text-muted-foreground">
                        {role.responsibilities.map((item) => (
                            <li key={item} className="leading-7">
                                {item}
                            </li>
                        ))}
                    </ul>
                </section>

                <section>
                    <div className="mb-2 flex items-center gap-2 text-sm font-semibold">
                        <CalendarCheck2 className="size-4 text-muted-foreground" />
                        برنامه عملیاتی روزانه
                    </div>
                    <ol className="space-y-2 text-sm text-muted-foreground">
                        {role.dailyOperations.map((item, index) => (
                            <li key={item} className="flex gap-2 leading-7">
                                <span className="mt-1 flex size-5 shrink-0 items-center justify-center rounded-full bg-muted text-xs font-semibold text-foreground">
                                    {(index + 1).toLocaleString('fa-IR')}
                                </span>
                                <span>{item}</span>
                            </li>
                        ))}
                    </ol>
                </section>
            </div>
        </article>
    );
}

export default function RoleOperationsIndex({
    roles,
    scopeTypes,
    stats,
}: Props) {
    const groupedRoles = {
        exploria_team: roles.filter((role) => role.group === 'exploria_team'),
        external_partner: roles.filter(
            (role) => role.group === 'external_partner',
        ),
        public: roles.filter((role) => role.group === 'public'),
    };

    return (
        <>
            <Head title="نقش‌ها و عملیات روزانه" />
            <div
                dir="rtl"
                className="flex h-full flex-1 flex-col gap-5 overflow-x-auto p-4"
            >
                <header className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p className="text-sm text-muted-foreground">
                            مدل Role + Scope برای اکوسیستم اکسپلوریا
                        </p>
                        <h1 className="mt-1 text-2xl font-semibold">
                            نقش‌ها، وظایف و برنامه عملیاتی روزانه
                        </h1>
                    </div>
                    <div className="grid grid-cols-2 gap-3 text-sm lg:grid-cols-5">
                        <Stat
                            icon={ShieldCheck}
                            label="کل نقش‌ها"
                            value={stats.totalRoles}
                        />
                        <Stat
                            icon={UsersRound}
                            label="تیم اکسپلوریا"
                            value={stats.exploriaTeamRoles}
                        />
                        <Stat
                            icon={Network}
                            label="شرکا"
                            value={stats.externalPartnerRoles}
                        />
                        <Stat
                            icon={UserCog}
                            label="عمومی"
                            value={stats.publicRoles}
                        />
                        <Stat
                            icon={Layers3}
                            label="دامنه دسترسی"
                            value={stats.scopeTypes}
                        />
                    </div>
                </header>

                <section className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
                    <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 className="text-base font-semibold">
                                دامنه‌های دسترسی قابل تخصیص
                            </h2>
                            <p className="mt-1 text-sm text-muted-foreground">
                                هر کاربر یک نقش دارد، اما دسترسی واقعی او با
                                دامنه مشخص می‌شود.
                            </p>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            {scopeTypes.map((scope) => (
                                <span
                                    key={scope}
                                    className="rounded-full bg-muted px-3 py-1 text-xs font-medium"
                                >
                                    {labelForScope(scope)}
                                </span>
                            ))}
                        </div>
                    </div>
                </section>

                {(
                    Object.keys(groupedRoles) as Array<
                        keyof typeof groupedRoles
                    >
                ).map((group) => (
                    <section key={group} className="grid gap-3">
                        <div>
                            <h2 className="text-xl font-semibold">
                                {groupLabels[group]}
                            </h2>
                            <p className="mt-1 text-sm text-muted-foreground">
                                {groupDescriptions[group]}
                            </p>
                        </div>
                        <div className="grid gap-4">
                            {groupedRoles[group].map((role) => (
                                <RoleCard key={role.key} role={role} />
                            ))}
                        </div>
                    </section>
                ))}
            </div>
        </>
    );
}

RoleOperationsIndex.layout = {
    breadcrumbs: [
        {
            title: 'نقش‌ها و عملیات',
            href: '/admin/role-operations',
        },
    ],
};
