import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    BadgeCheck,
    BadgePercent,
    BarChart3,
    Building2,
    ChevronLeft,
    Crown,
    Gift,
    Megaphone,
    MapPinned,
    Settings2,
    ShieldCheck,
    ShoppingBag,
    Smartphone,
    Sparkles,
    Store,
    TicketPercent,
    Timer,
    Trophy,
    UsersRound,
    WalletCards,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { useMemo, useState } from 'react';

type RoleId = 'visitor' | 'shop' | 'ravaq' | 'admin';

type Role = {
    id: RoleId;
    title: string;
    subtitle: string;
    icon: LucideIcon;
    tone: string;
};

const roles: Role[] = [
    {
        id: 'visitor',
        title: 'پنل کاربر',
        subtitle: 'ماموریت، کیف پاداش، سطح و مسیر بعدی',
        icon: Smartphone,
        tone: 'border-emerald-200 bg-emerald-50 text-emerald-950',
    },
    {
        id: 'shop',
        title: 'پنل فروشگاه',
        subtitle: 'تعریف تخفیف، اعتبارسنجی کد و گزارش مراجعه',
        icon: Store,
        tone: 'border-amber-200 bg-amber-50 text-amber-950',
    },
    {
        id: 'ravaq',
        title: 'پنل مدیر رواق',
        subtitle: 'کنترل کمپین، ظرفیت پذیرش و عملکرد شرکا',
        icon: Building2,
        tone: 'border-sky-200 bg-sky-50 text-sky-950',
    },
    {
        id: 'admin',
        title: 'پنل مدیر کل',
        subtitle: 'تعریف گنج، قوانین امتیاز، KPI و QRها',
        icon: Crown,
        tone: 'border-violet-200 bg-violet-50 text-violet-950',
    },
];

const kpis = [
    ['بازدیدکننده فعال', '۲,۸۴۰', '۱۲٪ رشد نسبت به هفته قبل'],
    ['ماموریت انجام‌شده', '۱۸,۶۲۰', '۴.۳ ماموریت برای هر کاربر فعال'],
    ['پاداش مصرف‌شده', '۶,۹۱۰', 'نرخ تبدیل ۳۷٪'],
    ['درآمد قابل انتساب', '۴۸۲ م', 'از رواق، فروشگاه و جاذبه‌ها'],
];

const visitorMissions = [
    {
        title: 'اسکن QR ورودی اکوپارک',
        place: 'دروازه اصلی',
        points: 120,
        reward: 'نشان شروع مسیر',
        status: 'آماده انجام',
    },
    {
        title: 'کشف سرنخ گنبد مینا',
        place: 'هاب علمی',
        points: 180,
        reward: '۱۰٪ تخفیف کافه شریک',
        status: 'در حال انجام',
    },
    {
        title: 'خرید از فروشگاه رواق',
        place: 'رواق تجاری',
        points: 260,
        reward: 'گنج نقره‌ای',
        status: 'قفل تا سطح کاوشگر',
    },
];

const partnerOffers = [
    {
        shop: 'کافه اکو',
        offer: '۱۰٪ تخفیف نوشیدنی',
        budget: '۱۲۰ کد',
        used: '۸۴ مصرف',
        approval: 'فعال',
    },
    {
        shop: 'فروشگاه رواق',
        offer: 'هدیه خرید بالای ۴۰۰ هزار',
        budget: '۷۵ کد',
        used: '۳۱ مصرف',
        approval: 'نیازمند تایید رواق',
    },
    {
        shop: 'سینمای طبیعت',
        offer: 'بلیط نیم‌بها برای ماموریت خانوادگی',
        budget: '۴۰ کد',
        used: '۱۲ مصرف',
        approval: 'فعال',
    },
];

const adPlacements = [
    {
        title: 'بنر رواق تجاری',
        surface: 'صفحه نقشه و مسیر',
        owner: 'فروشگاه عضو',
        format: 'کارت تصویری + CTA',
        status: 'فعال',
    },
    {
        title: 'اسپانسر مسیر خانوادگی',
        surface: 'پس از تکمیل ماموریت خانوادگی',
        owner: 'اسپانسر رسمی',
        format: 'نام‌گذاری مسیر + نشان برند',
        status: 'نیازمند تایید ادمین',
    },
    {
        title: 'تبلیغ برند غیرعضو',
        surface: 'اسلات پیشنهادی در صفحه پاداش',
        owner: 'برند غیرعضو',
        format: 'تبلیغ زمان‌دار با بودجه روزانه',
        status: 'در انتظار بررسی',
    },
];

const adRequests = [
    {
        advertiser: 'کافه اکو',
        type: 'فروشگاه عضو',
        goal: 'افزایش مراجعه عصرگاهی',
        budget: '۱۸ م',
        schedule: '۷ روز',
        approval: 'تایید مدیر رواق',
    },
    {
        advertiser: 'برند نوشیدنی کوهستان',
        type: 'برند غیرعضو',
        goal: 'نمایش در مسیر طبیعت',
        budget: '۴۵ م',
        schedule: '۱۴ روز',
        approval: 'در انتظار ادمین',
    },
    {
        advertiser: 'اسپانسر رویداد خانوادگی',
        type: 'اسپانسر',
        goal: 'نام‌گذاری گنج خانوادگی',
        budget: '۱۲۰ م',
        schedule: '۳۰ روز',
        approval: 'نیازمند قرارداد',
    },
];

const adReports = [
    ['نمایش تبلیغ', '۹۶,۴۰۰', 'کل نمایش در جایگاه‌های فعال'],
    ['کلیک/تعامل', '۸,۷۳۰', 'تعامل با CTA یا مشاهده جزئیات'],
    [
        'مراجعه قابل انتساب',
        '۱,۱۸۰',
        'کاربرانی که به فروشگاه یا مسیر هدایت شدند',
    ],
    ['درآمد تبلیغاتی', '۱۸۳ م', 'درآمد ماک از تبلیغات و اسپانسرینگ'],
];

const treasures = [
    {
        title: 'گنج سبز',
        type: 'گنج ساده',
        trigger: 'اسکن QR و تکمیل یک ماموریت',
        reward: 'امتیاز و نشان دیجیتال',
        owner: 'اکسپلوریا',
    },
    {
        title: 'گنج خانوادگی',
        type: 'تیمی',
        trigger: 'ثبت حضور دو عضو خانواده در یک مسیر',
        reward: 'کوپن فودکورت',
        owner: 'مدیر رواق',
    },
    {
        title: 'گنج خرید',
        type: 'فروشگاهی',
        trigger: 'خرید تایید شده از شریک',
        reward: 'تخفیف یا هدیه لحظه‌ای',
        owner: 'فروشگاه شریک',
    },
    {
        title: 'گنج طلایی',
        type: 'ویژه',
        trigger: 'اتمام همه لایه‌های کمپین',
        reward: 'قرعه‌کشی و جایزه ویژه',
        owner: 'مدیر کل',
    },
];

const ravaqRows = [
    ['رواق تجاری', '۲۴ شریک', '۷۱٪ ظرفیت کمپین', '۳۸٪ تبدیل به خرید'],
    ['فودکورت', '۱۲ شریک', '۵۸٪ ظرفیت کمپین', '۳۲٪ تبدیل به خرید'],
    ['گنبد مینا', '۴ نقطه تجربه', '۸۶٪ تعامل محتوایی', '۱۹٪ ارجاع به فروش'],
];

const adminRules = [
    ['قانون امتیاز', 'هر QR معتبر ۱۲۰ امتیاز', 'فعال'],
    ['سقف تخفیف', 'حداکثر ۲ کد برای هر کاربر در روز', 'فعال'],
    ['ضدتقلب', 'عدم پذیرش اسکن تکراری در کمتر از ۱۵ دقیقه', 'فعال'],
    ['تایید شریک', 'پاداش مالی قبل از انتشار نیازمند تایید مدیر رواق', 'فعال'],
];

const productMilestones = [
    {
        title: 'پنل فروشگاه عملیاتی',
        status: 'متصل به مسیر واقعی',
        body: 'خلاصه پاداش‌ها، مصرف کدها، درخواست تبلیغ، پیشنهاد تخفیف و اقدام سریع برای فروشگاه شریک.',
        href: '/partner/dashboard',
        icon: Store,
    },
    {
        title: 'ثبت تبلیغات فروشگاه و اسپانسر',
        status: 'خارج از کمپین هم پوشش داده شد',
        body: 'درخواست تبلیغ توسط فروشگاه، برند عضو، برند غیرعضو یا اسپانسر ثبت می‌شود و در صف تایید ادمین قرار می‌گیرد.',
        href: '/partner/ads',
        icon: Megaphone,
    },
    {
        title: 'پنل مدیر رواق',
        status: 'کنترل محدوده تحت مدیریت',
        body: 'مدیر رواق فقط شرکا، پیشنهادها، تبلیغات تاییدشده و نمایشگرهای محدوده خودش را می‌بیند و زمان‌بندی می‌کند.',
        href: '/hub/dashboard',
        icon: Building2,
    },
    {
        title: 'عملیات نمایشگرها',
        status: 'کنسول ادمین آماده است',
        body: 'ادمین موجودی نمایشگرها، صف پخش، جایگاه‌های آماده، آمار نمایش/کلیک و لغو زمان‌بندی را مدیریت می‌کند.',
        href: '/admin/display-operations',
        icon: BarChart3,
    },
];
const roleContent: Record<
    RoleId,
    {
        title: string;
        summary: string;
        metrics: string[];
        actions: string[];
    }
> = {
    visitor: {
        title: 'نمای موبایلی بازدیدکننده',
        summary:
            'کاربر ماموریت‌های نزدیک، کیف پاداش، سطح، گنج‌های بازشده و مسیر پیشنهادی بعدی را می‌بیند.',
        metrics: [
            'سطح: کاوشگر',
            'امتیاز: ۶۸۰',
            'پاداش فعال: ۳',
            'مسیر بعدی: رواق تجاری',
        ],
        actions: [
            'شروع ماموریت نزدیک',
            'مشاهده کیف پاداش',
            'باز کردن نقشه گنج',
        ],
    },
    shop: {
        title: 'داشبورد فروشگاه شریک',
        summary:
            'فروشگاه تخفیف تعریف می‌کند، کد مصرف‌شده را تایید می‌کند و اثر کمپین روی مراجعه و فروش را می‌بیند.',
        metrics: [
            'کد فعال: ۱۲۰',
            'مصرف امروز: ۳۴',
            'مراجعه قابل انتساب: ۹۸',
            'فروش ثبت‌شده: ۲۸ م',
        ],
        actions: [
            'تعریف تخفیف جدید',
            'اعتبارسنجی کد مشتری',
            'ارسال گزارش فروش',
        ],
    },
    ravaq: {
        title: 'پنل مدیر رواق',
        summary:
            'مدیر رواق ظرفیت کمپین، عملکرد فروشگاه‌ها، تایید پاداش‌ها و جریان بازدید در محدوده خود را کنترل می‌کند.',
        metrics: [
            'شریک فعال: ۲۴',
            'پاداش در انتظار تایید: ۵',
            'نرخ تبدیل: ۳۸٪',
            'ازدحام فعلی: متوسط',
        ],
        actions: [
            'تایید پیشنهاد فروشگاه',
            'تغییر ظرفیت کمپین',
            'دیدن نقشه تراکم',
        ],
    },
    admin: {
        title: 'پنل مدیر کل اکسپلوریا',
        summary:
            'مدیر کل گنج‌ها، قوانین امتیاز، تبلیغات مستقل، QRها، سطوح دسترسی، KPI و گزارش مالی کل اکوسیستم را مدیریت می‌کند.',
        metrics: [
            'QR فعال: ۴۸',
            'کمپین فعال: ۳',
            'تبلیغ در انتظار تایید: ۲',
            'نرخ تکمیل سفر: ۴۶٪',
            'درآمد قابل انتساب: ۴۸۲ م',
        ],
        actions: [
            'تعریف گنج جدید',
            'بررسی تبلیغ مستقل',
            'تنظیم قانون امتیاز',
            'خروجی گزارش هیئت‌مدیره',
        ],
    },
};

function StatusPill({ value }: { value: string }) {
    const isActive =
        value.includes('فعال') ||
        value.includes('آماده') ||
        value.includes('در حال');

    return (
        <span
            className={`inline-flex rounded-md px-2 py-1 text-xs font-medium ${
                isActive
                    ? 'bg-emerald-50 text-emerald-800'
                    : 'bg-amber-50 text-amber-800'
            }`}
        >
            {value}
        </span>
    );
}

export default function EcosystemDemo() {
    const [activeRole, setActiveRole] = useState<RoleId>('visitor');
    const selectedRole = useMemo(
        () => roles.find((role) => role.id === activeRole) ?? roles[0],
        [activeRole],
    );
    const content = roleContent[activeRole];

    return (
        <>
            <Head title="دموی اکوسیستم کامل اکسپلوریا" />
            <main dir="rtl" className="min-h-screen bg-stone-50 text-slate-950">
                <section className="border-b border-slate-200 bg-white">
                    <div className="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-5 py-4 sm:px-8 lg:px-10">
                        <Link href="/board" className="flex items-center gap-3">
                            <span className="flex size-10 items-center justify-center rounded-md bg-slate-950 text-white">
                                <Sparkles className="size-5" />
                            </span>
                            <span>
                                <span className="block text-sm text-slate-500">
                                    EXPLORIA
                                </span>
                                <span className="block font-semibold">
                                    دموی اکوسیستم اکوپارک
                                </span>
                            </span>
                        </Link>
                        <nav className="flex flex-wrap gap-2 text-sm">
                            <Link
                                href="/demo/missions"
                                className="inline-flex h-9 items-center gap-2 rounded-md border border-slate-200 px-3 hover:bg-slate-50"
                            >
                                <Trophy className="size-4" />
                                شبیه‌ساز ماموریت
                            </Link>
                            <Link
                                href="/demo/proposal"
                                className="inline-flex h-9 items-center gap-2 rounded-md bg-slate-950 px-3 text-white hover:bg-slate-800"
                            >
                                پوشش پروپوزال
                                <ArrowLeft className="size-4" />
                            </Link>
                        </nav>
                    </div>
                </section>

                <section className="bg-slate-950 text-white">
                    <div className="mx-auto grid max-w-7xl gap-8 px-5 py-8 sm:px-8 lg:grid-cols-[0.9fr_1.1fr] lg:px-10">
                        <div>
                            <div className="inline-flex items-center gap-2 rounded-md border border-emerald-300/40 px-3 py-1 text-sm text-emerald-200">
                                <ShieldCheck className="size-4" />
                                فرانت نمایشی با دیتای ماک
                            </div>
                            <h1 className="mt-5 text-3xl leading-tight font-semibold sm:text-4xl">
                                نمای کامل‌تر محصول: کاربر، فروشگاه، مدیر رواق و
                                مدیر کل
                            </h1>
                            <p className="mt-4 max-w-2xl text-sm leading-7 text-slate-300">
                                این صفحه برای جلسه طراحی شده است تا بخش‌هایی را
                                که هنوز backend کامل ندارند، به شکل محصول قابل
                                لمس نشان دهد: تعریف گنج، پاداش، تخفیف، کمپین،
                                نقش شرکا و کنترل مدیریتی.
                            </p>
                        </div>
                        <div className="grid gap-3 sm:grid-cols-2">
                            {kpis.map(([label, value, note]) => (
                                <div
                                    key={label}
                                    className="rounded-lg border border-white/10 bg-white/5 p-4"
                                >
                                    <p className="text-sm text-slate-300">
                                        {label}
                                    </p>
                                    <p className="mt-2 text-2xl font-semibold">
                                        {value}
                                    </p>
                                    <p className="mt-2 text-xs text-emerald-200">
                                        {note}
                                    </p>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                <section className="mx-auto max-w-7xl px-5 py-6 sm:px-8 lg:px-10">
                    <div className="mb-4 flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <p className="text-sm font-medium text-emerald-700">
                                به‌روزرسانی پس از توسعه واقعی
                            </p>
                            <h2 className="mt-1 text-2xl font-semibold">
                                بخش‌هایی که از دمو صرف عبور کرده‌اند و مسیر عملیاتی دارند
                            </h2>
                        </div>
                        <Link
                            href="/admin/display-operations"
                            className="inline-flex h-10 items-center gap-2 rounded-md bg-slate-950 px-3 text-sm text-white hover:bg-slate-800"
                        >
                            کنسول نمایشگرها
                            <ArrowLeft className="size-4" />
                        </Link>
                    </div>
                    <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                        {productMilestones.map((item) => (
                            <Link
                                key={item.title}
                                href={item.href}
                                className="rounded-lg border border-slate-200 bg-white p-4 transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-sm"
                            >
                                <div className="flex items-center justify-between gap-3">
                                    <span className="flex size-10 items-center justify-center rounded-md bg-emerald-50 text-emerald-800">
                                        <item.icon className="size-5" />
                                    </span>
                                    <span className="rounded-md bg-slate-100 px-2 py-1 text-xs text-slate-600">
                                        {item.status}
                                    </span>
                                </div>
                                <h3 className="mt-4 font-semibold">{item.title}</h3>
                                <p className="mt-2 text-sm leading-6 text-slate-600">
                                    {item.body}
                                </p>
                            </Link>
                        ))}
                    </div>
                </section>
                <section className="mx-auto max-w-7xl px-5 py-6 sm:px-8 lg:px-10">
                    <div className="grid gap-3 md:grid-cols-4">
                        {roles.map((role) => (
                            <button
                                key={role.id}
                                type="button"
                                onClick={() => setActiveRole(role.id)}
                                className={`rounded-lg border p-4 text-right transition hover:-translate-y-0.5 ${
                                    activeRole === role.id
                                        ? role.tone
                                        : 'border-slate-200 bg-white text-slate-800'
                                }`}
                            >
                                <role.icon className="size-5" />
                                <span className="mt-3 block font-semibold">
                                    {role.title}
                                </span>
                                <span className="mt-2 block text-sm leading-6 opacity-75">
                                    {role.subtitle}
                                </span>
                            </button>
                        ))}
                    </div>
                </section>

                <section className="mx-auto grid max-w-7xl gap-5 px-5 pb-10 sm:px-8 lg:grid-cols-[0.95fr_1.05fr] lg:px-10">
                    <article className="rounded-lg border border-slate-200 bg-white p-5">
                        <div className="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p className="text-sm text-slate-500">
                                    {selectedRole.title}
                                </p>
                                <h2 className="mt-1 text-2xl font-semibold">
                                    {content.title}
                                </h2>
                            </div>
                            <span className="inline-flex items-center gap-2 rounded-md bg-slate-950 px-3 py-1 text-sm text-white">
                                <selectedRole.icon className="size-4" />
                                نقش فعال
                            </span>
                        </div>
                        <p className="mt-4 text-sm leading-7 text-slate-600">
                            {content.summary}
                        </p>
                        <div className="mt-5 grid gap-3 sm:grid-cols-2">
                            {content.metrics.map((metric) => (
                                <div
                                    key={metric}
                                    className="rounded-md border border-slate-200 bg-stone-50 p-3 text-sm font-medium"
                                >
                                    {metric}
                                </div>
                            ))}
                        </div>
                        <div className="mt-5 grid gap-2">
                            {content.actions.map((action) => (
                                <button
                                    key={action}
                                    type="button"
                                    className="flex h-10 items-center justify-between rounded-md border border-slate-200 px-3 text-sm hover:bg-slate-50"
                                >
                                    <span>{action}</span>
                                    <ChevronLeft className="size-4 text-slate-400" />
                                </button>
                            ))}
                        </div>
                    </article>

                    <article className="rounded-lg border border-slate-200 bg-white p-5">
                        <div className="flex items-center justify-between gap-3">
                            <div>
                                <p className="text-sm text-slate-500">
                                    چرخه کاربر
                                </p>
                                <h2 className="mt-1 text-2xl font-semibold">
                                    ماموریت، امتیاز و کیف پاداش
                                </h2>
                            </div>
                            <WalletCards className="size-6 text-emerald-700" />
                        </div>
                        <div className="mt-5 grid gap-3">
                            {visitorMissions.map((mission) => (
                                <div
                                    key={mission.title}
                                    className="grid gap-3 rounded-md border border-slate-200 p-4 md:grid-cols-[1fr_auto]"
                                >
                                    <div>
                                        <p className="font-semibold">
                                            {mission.title}
                                        </p>
                                        <p className="mt-1 text-sm text-slate-500">
                                            {mission.place} · {mission.reward}
                                        </p>
                                    </div>
                                    <div className="flex flex-wrap items-center gap-2 md:justify-end">
                                        <span className="rounded-md bg-slate-100 px-2 py-1 text-xs font-medium">
                                            {mission.points.toLocaleString(
                                                'fa-IR',
                                            )}{' '}
                                            امتیاز
                                        </span>
                                        <StatusPill value={mission.status} />
                                    </div>
                                </div>
                            ))}
                        </div>
                    </article>
                </section>

                <section className="border-y border-slate-200 bg-white">
                    <div className="mx-auto grid max-w-7xl gap-6 px-5 py-10 sm:px-8 lg:grid-cols-[0.9fr_1.1fr] lg:px-10">
                        <article>
                            <div className="flex items-center gap-2">
                                <Megaphone className="size-5 text-rose-700" />
                                <h2 className="text-2xl font-semibold">
                                    تبلیغات مستقل از کمپین
                                </h2>
                            </div>
                            <p className="mt-3 text-sm leading-7 text-slate-600">
                                فروشگاه‌های عضو، برندهای غیرعضو و اسپانسرها
                                می‌توانند خارج از کمپین اصلی درخواست تبلیغ ثبت
                                کنند؛ انتشار نهایی با تایید مدیر رواق یا ادمین
                                انجام می‌شود.
                            </p>
                            <div className="mt-5 grid gap-3">
                                {adRequests.map((request) => (
                                    <div
                                        key={request.advertiser}
                                        className="rounded-lg border border-slate-200 p-4"
                                    >
                                        <div className="flex flex-wrap items-start justify-between gap-3">
                                            <div>
                                                <p className="font-semibold">
                                                    {request.advertiser}
                                                </p>
                                                <p className="mt-1 text-sm text-slate-500">
                                                    {request.type} ·{' '}
                                                    {request.goal}
                                                </p>
                                            </div>
                                            <StatusPill
                                                value={request.approval}
                                            />
                                        </div>
                                        <div className="mt-4 grid gap-2 text-sm sm:grid-cols-2">
                                            <span className="rounded-md bg-stone-50 px-3 py-2">
                                                بودجه: {request.budget}
                                            </span>
                                            <span className="rounded-md bg-stone-50 px-3 py-2">
                                                زمان‌بندی: {request.schedule}
                                            </span>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </article>

                        <article>
                            <div className="flex items-center gap-2">
                                <BadgeCheck className="size-5 text-emerald-700" />
                                <h2 className="text-2xl font-semibold">
                                    جایگاه، تایید و گزارش تبلیغ
                                </h2>
                            </div>
                            <div className="mt-5 overflow-hidden rounded-lg border border-slate-200">
                                <table className="w-full min-w-[720px] text-right text-sm">
                                    <thead className="bg-stone-50 text-slate-500">
                                        <tr>
                                            <th className="px-4 py-3 font-medium">
                                                جایگاه
                                            </th>
                                            <th className="px-4 py-3 font-medium">
                                                سطح نمایش
                                            </th>
                                            <th className="px-4 py-3 font-medium">
                                                مالک
                                            </th>
                                            <th className="px-4 py-3 font-medium">
                                                قالب
                                            </th>
                                            <th className="px-4 py-3 font-medium">
                                                وضعیت
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-slate-200">
                                        {adPlacements.map((placement) => (
                                            <tr key={placement.title}>
                                                <td className="px-4 py-3 font-semibold">
                                                    {placement.title}
                                                </td>
                                                <td className="px-4 py-3 text-slate-600">
                                                    {placement.surface}
                                                </td>
                                                <td className="px-4 py-3">
                                                    {placement.owner}
                                                </td>
                                                <td className="px-4 py-3">
                                                    {placement.format}
                                                </td>
                                                <td className="px-4 py-3">
                                                    <StatusPill
                                                        value={placement.status}
                                                    />
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                            <div className="mt-4 grid gap-3 sm:grid-cols-2">
                                {adReports.map(([label, value, note]) => (
                                    <div
                                        key={label}
                                        className="rounded-md border border-slate-200 bg-stone-50 p-3"
                                    >
                                        <div className="flex items-center gap-2 text-sm text-slate-500">
                                            <Timer className="size-4" />
                                            {label}
                                        </div>
                                        <p className="mt-2 text-xl font-semibold">
                                            {value}
                                        </p>
                                        <p className="mt-1 text-xs text-slate-500">
                                            {note}
                                        </p>
                                    </div>
                                ))}
                            </div>
                        </article>
                    </div>
                </section>

                <section className="border-y border-slate-200 bg-white">
                    <div className="mx-auto grid max-w-7xl gap-5 px-5 py-10 sm:px-8 lg:grid-cols-[1.05fr_0.95fr] lg:px-10">
                        <article>
                            <div className="flex items-center gap-2">
                                <Gift className="size-5 text-violet-700" />
                                <h2 className="text-2xl font-semibold">
                                    تعریف گنج‌ها و پاداش‌ها
                                </h2>
                            </div>
                            <div className="mt-5 overflow-hidden rounded-lg border border-slate-200">
                                <table className="w-full min-w-[680px] text-right text-sm">
                                    <thead className="bg-stone-50 text-slate-500">
                                        <tr>
                                            <th className="px-4 py-3 font-medium">
                                                گنج
                                            </th>
                                            <th className="px-4 py-3 font-medium">
                                                نوع
                                            </th>
                                            <th className="px-4 py-3 font-medium">
                                                شرط فعال‌سازی
                                            </th>
                                            <th className="px-4 py-3 font-medium">
                                                پاداش
                                            </th>
                                            <th className="px-4 py-3 font-medium">
                                                مالک
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-slate-200">
                                        {treasures.map((treasure) => (
                                            <tr key={treasure.title}>
                                                <td className="px-4 py-3 font-semibold">
                                                    {treasure.title}
                                                </td>
                                                <td className="px-4 py-3">
                                                    {treasure.type}
                                                </td>
                                                <td className="px-4 py-3 text-slate-600">
                                                    {treasure.trigger}
                                                </td>
                                                <td className="px-4 py-3">
                                                    {treasure.reward}
                                                </td>
                                                <td className="px-4 py-3">
                                                    {treasure.owner}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </article>

                        <article>
                            <div className="flex items-center gap-2">
                                <TicketPercent className="size-5 text-amber-700" />
                                <h2 className="text-2xl font-semibold">
                                    تخفیف‌ها و پیشنهاد فروشگاه‌ها
                                </h2>
                            </div>
                            <div className="mt-5 grid gap-3">
                                {partnerOffers.map((offer) => (
                                    <div
                                        key={offer.shop}
                                        className="rounded-lg border border-slate-200 p-4"
                                    >
                                        <div className="flex flex-wrap items-start justify-between gap-3">
                                            <div>
                                                <p className="font-semibold">
                                                    {offer.shop}
                                                </p>
                                                <p className="mt-1 text-sm text-slate-600">
                                                    {offer.offer}
                                                </p>
                                            </div>
                                            <StatusPill
                                                value={offer.approval}
                                            />
                                        </div>
                                        <div className="mt-4 grid gap-2 text-sm sm:grid-cols-2">
                                            <span className="rounded-md bg-stone-50 px-3 py-2">
                                                بودجه: {offer.budget}
                                            </span>
                                            <span className="rounded-md bg-stone-50 px-3 py-2">
                                                مصرف: {offer.used}
                                            </span>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </article>
                    </div>
                </section>

                <section className="mx-auto grid max-w-7xl gap-5 px-5 py-10 sm:px-8 lg:grid-cols-2 lg:px-10">
                    <article className="rounded-lg border border-slate-200 bg-white p-5">
                        <div className="flex items-center gap-2">
                            <MapPinned className="size-5 text-sky-700" />
                            <h2 className="text-2xl font-semibold">
                                کنترل رواق‌ها و شرکا
                            </h2>
                        </div>
                        <div className="mt-5 grid gap-3">
                            {ravaqRows.map(
                                ([name, partners, capacity, conversion]) => (
                                    <div
                                        key={name}
                                        className="grid gap-3 rounded-md border border-slate-200 p-4 sm:grid-cols-4"
                                    >
                                        <p className="font-semibold">{name}</p>
                                        <p className="text-sm text-slate-600">
                                            {partners}
                                        </p>
                                        <p className="text-sm text-slate-600">
                                            {capacity}
                                        </p>
                                        <p className="text-sm text-emerald-700">
                                            {conversion}
                                        </p>
                                    </div>
                                ),
                            )}
                        </div>
                    </article>

                    <article className="rounded-lg border border-slate-200 bg-white p-5">
                        <div className="flex items-center gap-2">
                            <Settings2 className="size-5 text-violet-700" />
                            <h2 className="text-2xl font-semibold">
                                قوانین مدیریتی و ضدتقلب
                            </h2>
                        </div>
                        <div className="mt-5 grid gap-3">
                            {adminRules.map(([title, rule, status]) => (
                                <div
                                    key={title}
                                    className="grid gap-3 rounded-md border border-slate-200 p-4 sm:grid-cols-[0.65fr_1fr_auto]"
                                >
                                    <p className="font-semibold">{title}</p>
                                    <p className="text-sm text-slate-600">
                                        {rule}
                                    </p>
                                    <StatusPill value={status} />
                                </div>
                            ))}
                        </div>
                    </article>
                </section>

                <section className="bg-slate-950 text-white">
                    <div className="mx-auto grid max-w-7xl gap-4 px-5 py-8 sm:px-8 md:grid-cols-4 lg:px-10">
                        {[
                            [UsersRound, 'کاربران', 'سفر، سطح، کیف پاداش'],
                            [
                                ShoppingBag,
                                'فروشگاه‌ها',
                                'تخفیف، تایید کد، گزارش فروش',
                            ],
                            [BadgePercent, 'پاداش‌ها', 'کوپن، هدیه، قرعه‌کشی'],
                            [Megaphone, 'تبلیغات', 'عضو، غیرعضو، اسپانسر'],
                            [BarChart3, 'مدیریت', 'KPI، ضدتقلب، تصمیم‌سازی'],
                        ].map(([Icon, title, body]) => {
                            const TypedIcon = Icon as LucideIcon;

                            return (
                                <div
                                    key={title as string}
                                    className="rounded-lg border border-white/10 bg-white/5 p-4"
                                >
                                    <TypedIcon className="size-5 text-emerald-300" />
                                    <p className="mt-3 font-semibold">
                                        {title as string}
                                    </p>
                                    <p className="mt-2 text-sm text-slate-300">
                                        {body as string}
                                    </p>
                                </div>
                            );
                        })}
                    </div>
                </section>
            </main>
        </>
    );
}
