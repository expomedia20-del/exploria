import { Head } from '@inertiajs/react';
import {
    Building2,
    CalendarCheck2,
    ClipboardList,
    Layers3,
    ShieldCheck,
    Store,
    UserCog,
    UsersRound,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';

type RoleGroup =
    | 'exploria_team'
    | 'venue_management'
    | 'commercial_partner'
    | 'public';

type RoleItem = {
    key: string;
    group: RoleGroup;
    label: string;
    scope: string;
    reportsTo: string | null;
    responsibilities: string[];
    dailyOperations: string[];
};

type AuthorityGuide = {
    allowed: string[];
    observeOnly: string[];
    notAllowed: string[];
};

type Props = {
    roles: RoleItem[];
    scopeTypes: string[];
    stats: {
        totalRoles: number;
        exploriaTeamRoles: number;
        venueManagementRoles: number;
        commercialPartnerRoles: number;
        publicRoles: number;
        scopeTypes: number;
    };
};

const groupLabels: Record<RoleGroup, string> = {
    exploria_team: 'تیم داخلی اکسپلوریا',
    venue_management: 'مدیریت مکان و زون‌ها',
    commercial_partner: 'واحدهای تجاری و اسپانسرها',
    public: 'بازدیدکنندگان و مشارکت‌کنندگان',
};

const groupDescriptions: Record<RoleGroup, string> = {
    exploria_team:
        'نقش‌هایی که پروژه، اجرا، تبلیغات، پشتیبانی، گزارش و درآمد را از سمت اکسپلوریا مدیریت می‌کنند.',
    venue_management:
        'نقش‌های سمت مکان که دید مدیریتی به مکان، زون، هاب یا مجموعه دارند و وارد تصمیم تجاری هر واحد نمی‌شوند.',
    commercial_partner:
        'فروشگاه‌ها، واحدهای غذایی، واحدهای تجاری و اسپانسرهایی که منبع درآمد، تبلیغ، پاداش و بده‌بستان تجاری هستند.',
    public: 'کاربرانی که به شکل فردی، خانوادگی یا تیمی در کمپین شرکت می‌کنند.',
};

const scopeLabels: Record<string, string> = {
    global: 'کل اکسپلوریا',
    region: 'منطقه یا استان',
    venue: 'مکان پروژه',
    project: 'پروژه اجرایی',
    hub: 'هاب، زون یا مجموعه',
    partner: 'فروشگاه یا واحد',
    campaign: 'کمپین',
    display_network: 'شبکه نمایشگرها',
    team: 'تیم یا خانواده',
};

const roleLabels: Record<string, string> = {
    super_admin: 'ادمین مرکزی اکسپلوریا',
    regional_admin: 'مدیر منطقه‌ای / عاملیت',
    project_admin: 'مدیر پروژه مکانی اکسپلوریا',
    field_operator: 'مجری میدانی پروژه',
    treasure_assistant: 'یاریگر کاربران',
    display_ads_manager: 'مدیر تبلیغات و نمایشگرهای اکسپلوریا',
    venue_executive: 'مدیر اجرایی مکان پروژه',
    ravaq_manager: 'مدیر مجموعه تجاری رواق / زون تجاری',
    hub_manager: 'مدیر هاب / مجموعه تخصصی مکان',
    shop_manager: 'مدیر فروشگاه / واحد تجاری یا غذایی',
    internal_sponsor: 'اسپانسر داخل مکان یا مجموعه',
    external_sponsor: 'اسپانسر مستقل / بیرونی',
    participant: 'بازدیدکننده / مشارکت‌کننده',
};

const authorityGuides: Record<string, AuthorityGuide> = {
    venue_executive: {
        allowed: [
            'مشاهده وضعیت کلان مکان، کمپین‌ها، هاب‌ها، رواق‌ها و ریسک‌های روز اجرا',
            'ثبت نظر مدیریتی درباره تغییراتی که روی کل مکان یا جریان بازدید اثر دارد',
            'دریافت خلاصه عملکرد، آمادگی و نیازهای هماهنگی از مدیران زون/هاب',
        ],
        observeOnly: [
            'خلاصه تبلیغات، پاداش‌ها، گنج‌ها، نمایشگرها و مشارکت واحدها در سطح مدیریتی',
            'وضعیت آماده/نیازمند بررسی بدون ورود به جزئیات تجاری هر فروشگاه',
        ],
        notAllowed: [
            'تصمیم درباره قیمت، درآمد، موجودی، نوع پاداش یا پیشنهاد اختصاصی هر واحد',
            'تایید قرارداد اسپانسر، تایید مالی یا تغییر نقش‌ها و دسترسی‌های اکسپلوریا',
        ],
    },
    ravaq_manager: {
        allowed: [
            'پایش نظم، آمادگی، ظرفیت، ازدحام و مشکلات اجرایی رواق و فودکورت',
            'اعلام مغایرت با قوانین مجموعه یا مانع اجرایی برای تبلیغ، پاداش یا نمایشگر',
            'تهیه گزارش کوتاه از وضعیت واحدهای داخل رواق برای مدیر مکان و اکسپلوریا',
        ],
        observeOnly: [
            'تبلیغات و پاداش‌های واحدهای داخل رواق فقط برای هماهنگی و تطبیق با مقررات مجموعه',
            'برنامه نمایشگرهای محدوده فقط برای پایش سلامت و نظم پخش',
        ],
        notAllowed: [
            'تعیین نوع پاداش، ارزش اقتصادی پیشنهاد، قیمت، درآمد یا موجودی فروشگاه‌ها',
            'تایید یا رد نهایی تبلیغ، قرارداد اسپانسر، بسته تجاری یا تصمیم مالی واحدها',
        ],
    },
};

function labelForRole(key: string) {
    return roleLabels[key] ?? key;
}

function labelForScope(scope: string) {
    return scopeLabels[scope] ?? scope;
}

function AuthorityGuideBox({ roleKey }: { roleKey: string }) {
    const guide = authorityGuides[roleKey];

    if (!guide) {
        return null;
    }

    return (
        <section className="mt-4 rounded-lg border border-sidebar-border/70 bg-muted/30 p-4 dark:border-sidebar-border">
            <div className="mb-3 flex items-center gap-2 text-sm font-semibold">
                <ShieldCheck className="size-4 text-muted-foreground" />
                حدود اختیار عملیاتی این نقش
            </div>
            <div className="grid gap-3 lg:grid-cols-3">
                <AuthorityColumn title="مجاز است" items={guide.allowed} />
                <AuthorityColumn
                    title="فقط مشاهده / اعلام نظر"
                    items={guide.observeOnly}
                />
                <AuthorityColumn title="خارج از اختیار" items={guide.notAllowed} />
            </div>
        </section>
    );
}

function AuthorityColumn({ title, items }: { title: string; items: string[] }) {
    return (
        <div className="rounded-md bg-background p-3">
            <p className="mb-2 text-sm font-medium">{title}</p>
            <ul className="space-y-2 text-sm text-muted-foreground">
                {items.map((item) => (
                    <li key={item} className="leading-7">
                        {item}
                    </li>
                ))}
            </ul>
        </div>
    );
}

function Stat({
    icon: Icon,
    label,
    value,
}: {
    icon: LucideIcon;
    label: string;
    value?: number;
}) {
    const safeValue =
        typeof value === 'number' && Number.isFinite(value) ? value : 0;

    return (
        <div className="rounded-lg border border-sidebar-border/70 px-3 py-2 dark:border-sidebar-border">
            <div className="flex items-center gap-2 text-muted-foreground">
                <Icon className="size-4" />
                <span className="text-sm">{label}</span>
            </div>
            <p className="mt-2 text-xl font-semibold">
                {safeValue.toLocaleString('fa-IR')}
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
                        عملیات روزانه
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
            <AuthorityGuideBox roleKey={role.key} />
        </article>
    );
}

export default function RoleOperationsIndex({
    roles = [],
    scopeTypes = [],
    stats = {
        totalRoles: 0,
        exploriaTeamRoles: 0,
        venueManagementRoles: 0,
        commercialPartnerRoles: 0,
        publicRoles: 0,
        scopeTypes: 0,
    },
}: Props) {
    const groupedRoles: Record<RoleGroup, RoleItem[]> = {
        exploria_team: roles.filter((role) => role.group === 'exploria_team'),
        venue_management: roles.filter(
            (role) => role.group === 'venue_management',
        ),
        commercial_partner: roles.filter(
            (role) => role.group === 'commercial_partner',
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
                            مدل Role + Scope برای تبدیل اکسپلوریا به پایلوت قابل اجرا و قابل فروش
                        </p>
                        <h1 className="mt-1 text-2xl font-semibold">
                            نقش‌ها، حدود اختیار و عملیات روزانه
                        </h1>
                    </div>
                    <div className="grid grid-cols-2 gap-3 text-sm lg:grid-cols-6">
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
                            icon={Building2}
                            label="مدیریت مکان"
                            value={stats.venueManagementRoles}
                        />
                        <Stat
                            icon={Store}
                            label="تجاری"
                            value={stats.commercialPartnerRoles}
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
                                قاعده کنترل پیچیدگی
                            </h2>
                            <p className="mt-1 text-sm text-muted-foreground">
                                مدیر مکان دید کلان دارد، مدیر زون نظم و آمادگی محدوده خودش را مدیریت می‌کند، و تصمیم تجاری هر فروشگاه یا اسپانسر در پنل همان واحد انجام می‌شود.
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
                    Object.keys(groupedRoles) as Array<keyof typeof groupedRoles>
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
