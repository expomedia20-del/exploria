import { Head, Link } from '@inertiajs/react';
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
    accountRole: string;
    accountRoleLabel: string;
    entryHref: string;
    entryLabel: string;
    panelMode: string;
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

const panelModeLabels: Record<string, string> = {
    internal_shared: 'پنل مشترک داخلی اکسپلوریا',
    venue_panel: 'پنل اختصاصی مدیر مکان',
    hub_panel: 'پنل اختصاصی رواق/هاب',
    partner_panel: 'پنل اختصاصی فروشگاه/واحد تجاری',
    sponsor_panel: 'پنل اختصاصی اسپانسر',
    public_panel: 'پنل عمومی مشارکت‌کننده',
};

const roleLabels: Record<string, string> = {
    super_admin: 'ادمین مرکزی اکسپلوریا',
    regional_admin: 'ادمین استانی / منطقه‌ای اکسپلوریا',
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
    super_admin: {
        allowed: [
            'تعیین سیاست کلان محصول، مدل درآمد، قراردادها، نقش‌ها و استانداردهای اجرایی',
            'تایید تصمیم‌های حساس مالی، حقوقی، دسترسی، توسعه منطقه‌ای و تغییرات بزرگ محصول',
            'مشاهده گزارش‌های سراسری عملکرد، درآمد، ریسک و آمادگی پروژه‌ها',
        ],
        observeOnly: [
            'جزئیات اجرایی روزانه هر مکان تا وقتی به ریسک کلان یا درآمد تبدیل نشده است',
            'عملیات میدانی که در سطح مدیر پروژه قابل حل است',
        ],
        notAllowed: [
            'دخالت مستقیم در پیشنهاد روزانه فروشگاه‌ها مگر در موارد حقوقی، درآمدی یا بحران',
            'جایگزینی تصمیم‌های عملیاتی مدیر پروژه در موضوعات خرد روز اجرا',
        ],
    },
    regional_admin: {
        allowed: [
            'مدیریت عملیات استان یا منطقه از سمت تیم اکسپلوریا، ظرفیت فروش، مکان‌های فعال و فرصت‌های توسعه',
            'اولویت‌بندی پروژه‌های منطقه و ارجاع موضوعات مالی/حقوقی به ادمین مرکزی',
            'پایش KPI منطقه، درآمد، اسپانسرها و ریسک‌های اجرایی چند مکان',
        ],
        observeOnly: [
            'جزئیات داخلی هر فروشگاه یا پیشنهاد تجاری هر واحد',
            'اجرای لحظه‌ای کمپین که در اختیار مدیر پروژه مکانی است',
        ],
        notAllowed: [
            'تغییر قرارداد یا نقش سراسری بدون تایید ادمین مرکزی',
            'تایید مستقیم پاداش یا تبلیغ یک واحد بدون مسیر عملیاتی مربوطه',
        ],
    },
    project_admin: {
        allowed: [
            'مرکز فرمان پایلوت مکان از سمت اکسپلوریا: کمپین، QR، ماموریت، پاداش، تبلیغات، گزارش و readiness',
            'هماهنگی بین مدیر مکان، مدیران زون/هاب، تیم میدانی، فروشگاه‌ها و اسپانسرها',
            'ارجاع درخواست‌ها به مالک درست: فروشگاه، اسپانسر، مدیر نمایشگر، مدیر مکان یا ادمین مرکزی',
        ],
        observeOnly: [
            'جزئیات مالی داخلی هر فروشگاه، مگر برای قرارداد، ROI یا گزارش درآمدی اکسپلوریا',
            'تصمیم مدیریتی خود مکان که خارج از قرارداد پایلوت است',
        ],
        notAllowed: [
            'تغییر سیاست کلان محصول یا قرارداد مادر بدون ادمین مرکزی/منطقه‌ای',
            'تصمیم تجاری به جای مالک فروشگاه یا اسپانسر',
        ],
    },
    field_operator: {
        allowed: [
            'جذب مشارکت، راهنمایی بازدیدکنندگان و مدیریت اجرای میدانی کمپین',
            'ثبت خطاهای QR، مسیر، ازدحام، خرابی، مشکل پاداش و بازخورد کاربران',
            'هماهنگی سریع با یاریگر کاربران و مدیر پروژه برای رفع مانع روز اجرا',
        ],
        observeOnly: [
            'وضعیت پاداش‌ها، مسیرها و ماموریت‌ها در حد اجرای میدانی',
            'پیام‌ها و دستورالعمل‌های عملیاتی روز کمپین',
        ],
        notAllowed: [
            'تغییر تنظیمات تجاری، قیمت، پاداش، اسپانسر یا قرارداد',
            'تصمیم درباره تایید/رد تبلیغ یا پاداش واحدها',
        ],
    },
    treasure_assistant: {
        allowed: [
            'کمک به کاربر برای اسکن QR، فهم ماموریت، دریافت گنج و مصرف پاداش',
            'ثبت مشکلات پرتکرار کاربران و ارجاع موارد جدی به مجری میدانی',
            'حفظ کیفیت تجربه کاربر بدون دست زدن به تنظیمات تجاری',
        ],
        observeOnly: [
            'پیشرفت کاربر و وضعیت ساده ماموریت‌ها برای پشتیبانی',
            'راهنمای مصرف پاداش و مسیر کمپین',
        ],
        notAllowed: [
            'تغییر امتیاز، پاداش، موجودی یا شرایط فروشگاه',
            'دسترسی به گزارش مالی، قرارداد یا تنظیمات مدیریتی',
        ],
    },
    display_ads_manager: {
        allowed: [
            'بررسی محتوا، سلامت نمایشگر، زمان‌بندی، اولویت و گزارش پخش تبلیغات',
            'انتشار، توقف یا اصلاح پخش تبلیغ طبق قواعد تصویب‌شده و محدودیت‌های مکان',
            'ثبت شواهد نمایش، خطاهای playback و خروجی قابل گزارش/صورتحساب',
        ],
        observeOnly: [
            'پیشنهاد تجاری اسپانسر یا فروشگاه فقط در حد نیاز پخش و محتوای تبلیغ',
            'محدودیت‌های مکان و رواق برای جلوگیری از تعارض اجرایی',
        ],
        notAllowed: [
            'قیمت‌گذاری بسته اسپانسر یا تغییر قرارداد تجاری',
            'تغییر پاداش فروشگاه یا شرایط مالی واحدها',
        ],
    },
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
    hub_manager: {
        allowed: [
            'پایش آمادگی هاب تخصصی، ظرفیت، محدودیت‌های محلی و مشکلات اجرایی',
            'اعلام اثر تغییرات هاب بر مسیر بازدید، ماموریت یا تجربه کاربر',
            'ثبت گزارش روزانه هاب برای مدیر مکان و مدیر پروژه اکسپلوریا',
        ],
        observeOnly: [
            'واحدهای تابعه هاب در سطح آمادگی و هماهنگی اجرایی',
            'تبلیغات و پاداش‌ها فقط در حد تعارض با قواعد یا ظرفیت همان هاب',
        ],
        notAllowed: [
            'ورود به قرارداد، درآمد یا تصمیم تجاری واحدهای زیرمجموعه',
            'تایید نهایی تبلیغ، پاداش یا بسته اسپانسری',
        ],
    },
    shop_manager: {
        allowed: [
            'مدیریت پیشنهاد، تخفیف، پاداش، موجودی، مصرف کد و تبلیغ واحد خودش',
            'ثبت درخواست تبلیغ، پاداش یا اصلاح شرایط واحد برای بررسی',
            'مشاهده گزارش عملکرد واحد خودش و مشکلات اجرایی مرتبط',
        ],
        observeOnly: [
            'قوانین مکان/رواق که روی پیشنهاد یا اجرای واحد اثر دارد',
            'وضعیت تایید اکسپلوریا درباره تبلیغ، پاداش یا پیشنهاد ثبت‌شده',
        ],
        notAllowed: [
            'دیدن درآمد یا عملکرد واحدهای دیگر',
            'تغییر نمایشگر، کمپین، مسیر، نقش‌ها یا بسته اسپانسر بیرونی',
        ],
    },
    internal_sponsor: {
        allowed: [
            'ثبت یا اصلاح محتوای اسپانسری، پاداش، جایزه، حمایت مسیر یا درخواست نمایشگر',
            'مشاهده وضعیت تایید، زمان‌بندی، تعامل و خروجی عملکرد اسپانسری خودش',
            'پاسخ به یادداشت‌های بررسی اکسپلوریا یا مدیر زون درباره اجرای محلی',
        ],
        observeOnly: [
            'قواعد مکان/زون که روی اجرای اسپانسری داخل مجموعه اثر دارد',
            'گزارش تعامل و نمایش مربوط به بسته خودش',
        ],
        notAllowed: [
            'تغییر کمپین، نمایشگر یا پاداش واحدهای دیگر',
            'دسترسی به قراردادها یا درآمد سایر اسپانسرها و فروشگاه‌ها',
        ],
    },
    external_sponsor: {
        allowed: [
            'مدیریت پیشنهاد اسپانسری، هدف کمپین، جایزه، گنج، تبلیغ و بسته ROI خودش',
            'مشاهده وضعیت تایید، نمایش، تعامل، مصرف پاداش و خروجی کمپین خودش',
            'تصمیم برای تمدید، ارتقا یا اصلاح بسته اسپانسری خودش',
        ],
        observeOnly: [
            'محدودیت‌های مکان و رواق که روی اجرای اسپانسری اثر دارد',
            'خلاصه عملکرد کمپین بدون دسترسی به جزئیات مالی داخلی واحدها',
        ],
        notAllowed: [
            'دخالت در عملیات داخلی فروشگاه‌ها، مدیر رواق یا مدیر مکان',
            'تغییر مسیر، ماموریت، نمایشگر یا پاداش خارج از بسته خود',
        ],
    },
    participant: {
        allowed: [
            'شروع یا ادامه مسیر کمپین، اسکن QR، انجام ماموریت و دریافت امتیاز',
            'کشف گنج، مصرف پاداش مجاز و ثبت بازخورد یا مشکل',
            'شرکت فردی، خانوادگی یا تیمی طبق قواعد بازی و رضایت‌نامه',
        ],
        observeOnly: [
            'پیشرفت خودش، ماموریت‌های فعال و پاداش‌های قابل مصرف خودش',
            'راهنمای مسیر، قوانین ایمنی، حریم خصوصی و بازی منصفانه',
        ],
        notAllowed: [
            'دسترسی به پنل مدیران، فروشگاه‌ها، اسپانسرها یا گزارش‌های داخلی',
            'تغییر امتیاز، پاداش، موجودی یا اطلاعات سایر کاربران',
        ],
    },
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
                <AuthorityColumn
                    title="خارج از اختیار"
                    items={guide.notAllowed}
                />
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
                    <span className="rounded-full bg-violet-100 px-2.5 py-1 font-medium text-violet-800 dark:bg-violet-950 dark:text-violet-200">
                        {role.accountRoleLabel}
                    </span>
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

            <div className="mt-4 grid gap-3 rounded-md border border-sidebar-border/70 bg-muted/30 p-3 text-sm md:grid-cols-3 dark:border-sidebar-border">
                <div>
                    <p className="text-xs text-muted-foreground">
                        نوع اکانت ورود
                    </p>
                    <p className="mt-1 font-medium">{role.accountRoleLabel}</p>
                </div>
                <div>
                    <p className="text-xs text-muted-foreground">نوع پنل</p>
                    <p className="mt-1 font-medium">
                        {panelModeLabels[role.panelMode] ?? role.panelMode}
                    </p>
                </div>
                <div className="flex flex-col items-start gap-2">
                    <div>
                        <p className="text-xs text-muted-foreground">
                            مسیر شروع
                        </p>
                        <p className="mt-1 font-medium">{role.entryLabel}</p>
                    </div>
                    <Link
                        href={role.entryHref}
                        className="rounded-md border border-input bg-background px-3 py-1.5 text-xs font-medium"
                    >
                        مشاهده پنل
                    </Link>
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
            <Head title="نقش‌ها، حدود اختیار و عملیات" />
            <div
                dir="rtl"
                className="flex h-full flex-1 flex-col gap-5 overflow-x-auto p-4"
            >
                <header className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p className="text-sm text-muted-foreground">
                            مدل Role + Scope برای تبدیل اکسپلوریا به پایلوت قابل
                            اجرا و قابل فروش
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
                                هر نقش باید بداند چه کاری انجام می‌دهد، چه چیزی
                                را فقط می‌بیند و کجا نباید تصمیم بگیرد. این
                                مرزها جلوی تداخل مدیر مکان، مدیر رواق، فروشگاه،
                                اسپانسر و تیم اکسپلوریا را می‌گیرد.
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
            title: 'نقش‌ها و حدود اختیار',
            href: '/admin/role-operations',
        },
    ],
};
