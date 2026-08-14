import { Form, Head, Link, usePage } from '@inertiajs/react';
import {
    ArrowLeft,
    ArrowUpLeft,
    BadgeDollarSign,
    BarChart3,
    Building2,
    CheckCircle2,
    Gift,
    Handshake,
    LayoutDashboard,
    MonitorPlay,
    QrCode,
    ShieldCheck,
    Sparkles,
    Store,
    TicketCheck,
    UsersRound,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';

const demoQrCode = 'ep1405-a7f3k9m2q8x4';
const proposalImages = {
    hero: '/images/ecopark/proposal/abbasabad-nature-bridge-demo.jpg',
    bridge: '/images/ecopark/proposal/ecopark-night-path-21-9.jpg',
    participant: '/images/ecopark/proposal/participant-route-card-3-2.jpg',
    revenue: '/images/ecopark/proposal/roi-night-plaza-4-5.jpg',
};
const internalRoles = ['admin', 'regional_admin', 'operator', 'viewer'];

type SharedProps = {
    flash?: {
        success?: string;
    };
    auth?: {
        user?: {
            role?: string;
        } | null;
    };
};

type SeoProfile = {
    title: string;
    description: string;
    canonicalPath: string;
};

type WelcomeProps = {
    marketingFocus?: string;
    seo?: SeoProfile;
};

type FocusProfile = {
    eyebrow: string;
    headline: string;
    summary: string;
    audienceType: string;
    demoLabel: string;
    demoHint: string;
};

function roleAwareHref(path: string, role?: string) {
    if (!role || internalRoles.includes(role)) {
        return path;
    }

    return (
        {
            visitor: '/participant/dashboard',
            shop_partner: '/partner/dashboard',
            sponsor: '/sponsor/dashboard',
            hub_manager: '/ravaq/dashboard',
        }[role] ?? '/dashboard'
    );
}

const accessRoutes: Array<{
    title: string;
    hint: string;
    href: string;
    icon: LucideIcon;
}> = [
    {
        title: 'پیشنهادهای امروز',
        hint: 'پیشنهادهای فعال و قابل استفاده',
        href: '/offers',
        icon: TicketCheck,
    },
    {
        title: 'داشبورد عملیاتی',
        hint: 'نمای زنده اجرای کمپین',
        href: '/dashboard',
        icon: BarChart3,
    },
    {
        title: 'چرخه تجاری‌سازی',
        hint: 'بسته‌ها و مسیر مذاکره داخلی',
        href: '/admin/commercialization',
        icon: BadgeDollarSign,
    },
];

const audiences = [
    {
        title: 'مدیر مکان',
        body: 'نمای کل اجرا، هماهنگی زون‌ها، کنترل ریسک و گزارش بازگشت سرمایه.',
        href: '/venue/dashboard',
        icon: Building2,
        label: 'راهبری کلان',
    },
    {
        title: 'رواق، هاب و واحدها',
        body: 'مدیریت پیشنهاد، کد مصرف و مشاهده اثر واقعی مراجعه کاربران.',
        href: '/ravaq/dashboard',
        icon: Store,
        label: 'عملیات محلی',
    },
    {
        title: 'اسپانسر',
        body: 'اتصال برند به تجربه و دریافت گزارش تعامل قابل ارائه.',
        href: '/admin/sponsors',
        icon: Handshake,
        label: 'رشد برند',
    },
    {
        title: 'بازدیدکننده',
        body: 'یک تجربه روان و سرگرم‌کننده برای مشارکت فردی، خانوادگی یا تیمی.',
        href: '/participant/dashboard',
        icon: UsersRound,
        label: 'تجربه میدانی',
    },
];

const capabilities: Array<{
    title: string;
    body: string;
    href: string;
    icon: LucideIcon;
}> = [
    {
        title: 'مدیریت کمپین',
        body: 'ثبت، ساخت، انتخاب الگو و نقشه عملیات کمپین.',
        href: '/admin/campaign-builder',
        icon: LayoutDashboard,
    },
    {
        title: 'QR و ورود',
        body: 'کدهای ورودی، نقاط تماس، رضایت‌نامه و ثبت بازدید.',
        href: '/admin/qr-codes',
        icon: QrCode,
    },
    {
        title: 'مأموریت و پاداش',
        body: 'تعریف مأموریت، گنج، امتیاز، کوپن، هدیه و مصرف پاداش.',
        href: '/admin/missions',
        icon: Gift,
    },
    {
        title: 'تبلیغات و نمایشگر',
        body: 'تبلیغ مستقل، زمان‌بندی نمایشگر و کنترل محتوای میدانی.',
        href: '/admin/display-operations',
        icon: MonitorPlay,
    },
    {
        title: 'پنل‌های نقش‌محور',
        body: 'ادمین، مدیر مکان، رواق، فروشگاه، اسپانسر و مشارکت‌کننده.',
        href: '/admin/role-operations',
        icon: ShieldCheck,
    },
    {
        title: 'داشبورد فروش',
        body: 'ROI، بسته قیمت، مدارک مذاکره و قیف تبدیل دمو به قرارداد.',
        href: '/admin/commercialization',
        icon: BarChart3,
    },
];

const venueTiers = [
    ['سبک', '۱ کمپین، ۲ QR، ۳ مأموریت و ۱ شریک'],
    ['استاندارد', 'چند QR، ۴ مأموریت، ۲ شریک و گزارش ROI'],
    ['ویژه', 'کمپین کامل، اسپانسر نمونه، نمایشگر و گزارش ارائه'],
];

const sponsorTiers = [
    ['برنزی', 'یک پاداش یا تخفیف ساده + گزارش مصرف'],
    ['نقره‌ای', 'پاداش + حضور در QR یا صفحه پاداش + گزارش'],
    ['طلایی', 'گنج اسپانسری + پاداش چندواحدی + نمایشگر + گزارش ROI'],
];

const memberOutcomes = [
    'پنل اختصاصی',
    'پیشنهاد و پاداش',
    'تأیید کد',
    'گزارش مراجعه',
];
const memberPackageLabels = ['پایه', 'رشد', 'ویژه', 'اسپانسری', 'بسته رسانه'];
const displayFormats = ['روزانه', 'هفتگی', 'کمپینی'];

const focusProfiles: Record<string, FocusProfile> = {
    home: {
        eyebrow: 'پلتفرم کمپین‌های تجربه‌سازی و درآمدزایی مکان‌ها',
        headline: 'چالش و پاداش بازدیدها، فروش و درآمد مکان‌ها',
        summary:
            'بازدیدکننده با QR وارد مسیر مأموریت، پاداش، پیشنهاد واحدها و گزارش قابل ارائه می‌شود.',
        audienceType: 'venue',
        demoLabel: 'مشاهده دموی نمونه اکوپارک',
        demoHint:
            'دمو با داده نمونه اکوپارک عباس‌آباد اجرا می‌شود تا مسیر QR، مأموریت، پاداش و گزارش را نشان دهد.',
    },
    venues: {
        eyebrow: 'راهکار اکسپلوریا برای مکان‌های گردشگری و تفریحی',
        headline: 'از برج، پارک و شهربازی یک مسیر کمپینی قابل فروش بسازید',
        summary:
            'اکسپلوریا امکانات مکان را به QR، مأموریت، گنج، پاداش، تبلیغات و گزارش اجرایی وصل می‌کند.',
        audienceType: 'venue',
        demoLabel: 'مشاهده دموی مکان اکوپارک',
        demoHint:
            'این دمو نمونه اجرایی اکوپارک عباس‌آباد است و برای ارزیابی مکان‌هایی مثل برج، پارک یا شهربازی قابل تطبیق خواهد بود.',
    },
    'commercial-units': {
        eyebrow: 'راهکار اکسپلوریا برای فروشگاه، فودکورت و واحد تجاری',
        headline:
            'مراجعه بازدیدکننده را به پیشنهاد، مصرف کد و فروش قابل پیگیری تبدیل کنید',
        summary:
            'واحد تجاری در کمپین مکان دیده می‌شود، پاداش تعریف می‌کند و اثر مراجعه و مصرف را در پنل خود می‌بیند.',
        audienceType: 'commercial_unit',
        demoLabel: 'مشاهده مسیر فروشگاه در دموی اکوپارک',
        demoHint:
            'شروع دمو کاربر را وارد نمونه اکوپارک می‌کند تا نقش فروشگاه، پیشنهاد، مصرف پاداش و اثر مراجعه را ببیند.',
    },
    visitors: {
        eyebrow: 'راهکار اکسپلوریا برای جذب و مشارکت بازدیدکننده',
        headline:
            'بازدید معمولی را به بازی، مأموریت، امتیاز و برگشت دوباره تبدیل کنید',
        summary:
            'کاربر با QR وارد تجربه می‌شود، مسیر را دنبال می‌کند، پاداش می‌گیرد و با پیشنهادهای مرتبط ادامه می‌دهد.',
        audienceType: 'visitor_growth',
        demoLabel: 'تجربه بازدیدکننده در دموی اکوپارک',
        demoHint:
            'این مسیر یک نمونه واقعی‌نما از ورود QR، رضایت، مأموریت و پاداش در اکوپارک عباس‌آباد است.',
    },
};

const defaultSeo = {
    title: 'اکسپلوریا | پلتفرم تجربه، کمپین و درآمد مکان',
    description:
        'اکسپلوریا برای مکان‌های گردشگری، مراکز تجاری و اسپانسرها کمپین QR، مأموریت، پاداش، تبلیغات و گزارش فروش‌پذیر می‌سازد.',
    canonicalPath: '/',
};

const interactiveRing =
    'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-300 focus-visible:ring-offset-2';
const fieldClass =
    'min-h-12 rounded-xl border border-stone-300 bg-white px-4 text-sm text-zinc-950 outline-none transition placeholder:text-zinc-400 focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100';

function Wordmark() {
    return (
        <span
            dir="ltr"
            className="flex items-center text-2xl leading-none font-black tracking-[0.1em] text-white sm:text-3xl"
            style={{
                fontFamily:
                    '"Palatino Linotype", "Cinzel", "Trajan Pro", Georgia, serif',
            }}
        >
            <span>E</span>
            <span
                aria-hidden="true"
                className="relative mx-0.5 inline-flex h-8 w-7 shrink-0 items-center justify-center sm:h-9 sm:w-8"
            >
                <span className="absolute top-1/2 left-1/2 h-10 w-0.5 origin-center -translate-x-1/2 -translate-y-1/2 rotate-45 rounded-full bg-gradient-to-b from-white via-emerald-200 to-emerald-500 shadow-[0_0_15px_rgba(16,185,129,0.9)] sm:h-11" />
                <span className="absolute top-1/2 left-1/2 h-8 w-0.5 origin-center -translate-x-1/2 -translate-y-1/2 -rotate-45 rounded-full bg-gradient-to-b from-fuchsia-100 via-cyan-200 to-emerald-300 sm:h-9" />
            </span>
            <span className="sr-only">X</span>
            <span>PLORIA</span>
        </span>
    );
}

function SectionHeading({
    label,
    title,
    description,
    light = false,
}: {
    label: string;
    title: string;
    description: string;
    light?: boolean;
}) {
    return (
        <div className="max-w-2xl">
            <p
                className={`text-sm font-bold tracking-wide ${light ? 'text-emerald-300' : 'text-emerald-700'}`}
            >
                {label}
            </p>
            <h2
                className={`mt-3 text-3xl leading-[1.4] font-bold sm:text-4xl lg:text-5xl ${light ? 'text-white' : 'text-zinc-950'}`}
            >
                {title}
            </h2>
            <p
                className={`mt-4 text-base leading-8 ${light ? 'text-slate-300' : 'text-zinc-600'}`}
            >
                {description}
            </p>
        </div>
    );
}

export default function Welcome({
    marketingFocus = 'home',
    seo = defaultSeo,
}: WelcomeProps) {
    const { auth, flash } = usePage<SharedProps>().props;
    const userRole = auth?.user?.role;
    const focus = focusProfiles[marketingFocus] ?? focusProfiles.home;

    return (
        <>
            <Head title={seo.title}>
                <meta name="description" content={seo.description} />
                <meta
                    name="keywords"
                    content="اکسپلوریا، کمپین مکان، QR، پاداش، گیمیفیکیشن، گردشگری، مرکز تجاری، اسپانسرینگ، جذب بازدیدکننده"
                />
                <link rel="canonical" href={seo.canonicalPath} />
                <meta property="og:title" content={seo.title} />
                <meta property="og:description" content={seo.description} />
                <meta property="og:type" content="website" />
            </Head>

            <main
                lang="fa"
                dir="rtl"
                className="min-h-screen overflow-x-hidden bg-[#f5f2eb] text-zinc-950"
            >
                <section className="relative isolate min-h-[760px] overflow-hidden bg-[#041027] text-white lg:min-h-[850px]">
                    <img
                        src={proposalImages.hero}
                        alt="پل طبیعت عباس‌آباد در شب"
                        className="absolute inset-0 -z-30 h-full w-full object-cover object-[64%_center] lg:object-center"
                    />
                    <div className="absolute inset-0 -z-20 bg-[linear-gradient(90deg,rgba(4,16,39,.06)_0%,rgba(4,16,39,.18)_40%,rgba(4,16,39,.76)_70%,rgba(4,16,39,.96)_100%)]" />
                    <div className="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_22%_38%,rgba(34,211,238,.1),transparent_30%),radial-gradient(circle_at_50%_18%,rgba(217,70,239,.08),transparent_24%),linear-gradient(180deg,rgba(4,16,39,.02),rgba(4,16,39,.12)_62%,rgba(4,16,39,.5))]" />

                    <div className="mx-auto flex min-h-[760px] max-w-[1440px] flex-col px-5 sm:px-8 lg:min-h-[850px] lg:px-14">
                        <header className="flex min-h-20 items-center justify-between gap-5 border-b border-white/12">
                            <Link
                                href="/"
                                aria-label="صفحه اصلی اکسپلوریا"
                                className={`${interactiveRing} shrink-0 rounded-sm`}
                            >
                                <Wordmark />
                            </Link>

                            <nav
                                aria-label="ناوبری صفحه"
                                className="hidden items-center gap-7 text-sm text-slate-200 lg:flex"
                            >
                                <a
                                    href="#capabilities"
                                    className={`${interactiveRing} rounded-sm transition hover:text-emerald-300`}
                                >
                                    قابلیت‌ها
                                </a>
                                <a
                                    href="#architecture"
                                    className={`${interactiveRing} rounded-sm transition hover:text-emerald-300`}
                                >
                                    معماری
                                </a>
                                <a
                                    href="#revenue"
                                    className={`${interactiveRing} rounded-sm transition hover:text-emerald-300`}
                                >
                                    مدل درآمدی
                                </a>
                                <a
                                    href="#demo-request"
                                    className={`${interactiveRing} rounded-sm transition hover:text-emerald-300`}
                                >
                                    درخواست دمو
                                </a>
                            </nav>

                            <Link
                                href="/login"
                                className={`${interactiveRing} inline-flex min-h-11 items-center gap-2 rounded-lg border border-white/20 px-4 text-sm text-slate-200 transition hover:border-white/40 hover:bg-white/8 hover:text-white`}
                            >
                                ورود مدیریتی
                                <ArrowUpLeft className="size-4" />
                            </Link>
                        </header>

                        <div className="grid flex-1 items-center gap-12 py-16 lg:grid-cols-[1.08fr_.92fr] lg:py-20">
                            <div className="max-w-3xl">
                                <div className="flex items-center gap-3 text-sm font-semibold text-emerald-200">
                                    <span className="h-px w-9 bg-emerald-300" />
                                    <span>{focus.eyebrow}</span>
                                </div>
                                <h1 className="mt-6 text-[2.7rem] leading-[1.28] font-black text-balance sm:text-6xl lg:text-[4.6rem]">
                                    {focus.headline}
                                </h1>
                                <p className="mt-6 max-w-2xl text-base leading-8 text-slate-200 sm:text-lg sm:leading-9">
                                    {focus.summary}
                                </p>

                                <div className="mt-9 flex flex-col gap-3 sm:flex-row">
                                    <Link
                                        href={`/scan/${demoQrCode}`}
                                        className={`${interactiveRing} inline-flex min-h-14 items-center justify-center gap-3 rounded-xl bg-emerald-300 px-6 text-sm font-bold text-[#041027] shadow-[0_18px_55px_rgba(52,211,153,.22)] transition hover:-translate-y-0.5 hover:bg-emerald-200`}
                                    >
                                        {focus.demoLabel}
                                        <ArrowLeft className="size-5" />
                                    </Link>
                                    <a
                                        href="#demo-request"
                                        className={`${interactiveRing} inline-flex min-h-14 items-center justify-center gap-3 rounded-xl border border-white/25 bg-white/[0.06] px-6 text-sm font-semibold text-white backdrop-blur-sm transition hover:border-white/45 hover:bg-white/10`}
                                    >
                                        درخواست جلسه دمو
                                        <ArrowLeft className="size-5" />
                                    </a>
                                </div>
                                <p className="mt-4 max-w-xl text-xs leading-6 text-slate-400">
                                    {focus.demoHint}
                                </p>
                            </div>

                            <aside className="self-end lg:pb-6">
                                <div className="relative overflow-hidden rounded-[2rem] border border-white/16 bg-[#071a33]/72 p-5 shadow-[0_28px_90px_rgba(0,0,0,.3)] backdrop-blur-md sm:p-7">
                                    <div className="absolute top-0 left-0 h-px w-2/3 bg-gradient-to-r from-transparent via-cyan-300 to-emerald-300" />
                                    <div className="flex items-center justify-between gap-4">
                                        <div>
                                            <p className="text-xs text-slate-400">
                                                نتیجه یک چرخه کامل
                                            </p>
                                            <p className="mt-1 font-bold text-white">
                                                از حضور تا تصمیم‌گیری
                                            </p>
                                        </div>
                                        <span className="flex size-11 items-center justify-center rounded-xl bg-emerald-300/12 text-emerald-300">
                                            <Sparkles className="size-5" />
                                        </span>
                                    </div>
                                    <div className="mt-6 grid grid-cols-3 divide-x divide-white/10 border-y border-white/10 py-5 divide-x-reverse">
                                        {[
                                            ['۰۱', 'تجربه یکپارچه'],
                                            ['۰۲', 'داده قابل پیگیری'],
                                            ['۰۳', 'مدل فروش‌پذیر'],
                                        ].map(([number, title]) => (
                                            <div
                                                key={title}
                                                className="px-3 first:pr-0 last:pl-0"
                                            >
                                                <span className="text-xs text-cyan-300">
                                                    {number}
                                                </span>
                                                <p className="mt-2 text-xs leading-6 text-slate-200 sm:text-sm">
                                                    {title}
                                                </p>
                                            </div>
                                        ))}
                                    </div>
                                    <p className="mt-5 text-sm leading-7 text-slate-300">
                                        یک روایت روشن برای ارائه مدیریتی؛ و یک
                                        مسیر واقعی برای تجربه بازدیدکننده.
                                    </p>
                                </div>
                            </aside>
                        </div>
                    </div>
                </section>

                <section
                    aria-label="دسترسی‌های سریع"
                    className="relative z-10 mx-auto -mt-9 max-w-7xl px-5 sm:px-8"
                >
                    <div className="grid overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-[0_22px_70px_rgba(23,23,23,.1)] md:grid-cols-3 md:divide-x md:divide-stone-200 md:divide-x-reverse">
                        {accessRoutes.map((route) => {
                            const Icon = route.icon;
                            const href = roleAwareHref(route.href, userRole);

                            return (
                                <Link
                                    key={route.title}
                                    href={href}
                                    className={`${interactiveRing} group flex min-h-24 items-center gap-4 border-b border-stone-200 px-5 py-4 transition last:border-b-0 hover:bg-emerald-50/60 md:border-b-0`}
                                >
                                    <span className="flex size-11 shrink-0 items-center justify-center rounded-xl bg-[#071a33] text-emerald-300 transition group-hover:bg-emerald-700 group-hover:text-white">
                                        <Icon className="size-5" />
                                    </span>
                                    <span className="min-w-0">
                                        <span className="block font-bold">
                                            {route.title}
                                        </span>
                                        <span className="mt-1 block text-xs leading-5 text-zinc-500">
                                            {route.hint}
                                        </span>
                                    </span>
                                    <ArrowUpLeft className="mr-auto size-4 shrink-0 text-zinc-400 transition group-hover:-translate-x-0.5 group-hover:-translate-y-0.5 group-hover:text-emerald-700" />
                                </Link>
                            );
                        })}
                    </div>
                </section>

                <section
                    id="capabilities"
                    className="scroll-mt-6 px-5 py-24 sm:px-8 lg:py-32"
                >
                    <div className="mx-auto max-w-7xl">
                        <div className="grid gap-10 lg:grid-cols-[.7fr_1.3fr] lg:gap-16">
                            <div className="lg:sticky lg:top-8 lg:self-start">
                                <SectionHeading
                                    label="قابلیت‌ها"
                                    title="از دمو تا اجرای واقعی"
                                    description="اکسپلوریا یک بازی یا پنل منفرد نیست؛ شش لایه هماهنگ، تجربه میدان را به عملیات و نتیجه تجاری پیوند می‌دهند."
                                />
                                <div className="mt-8 hidden overflow-hidden rounded-[1.75rem] lg:block">
                                    <img
                                        src={proposalImages.participant}
                                        alt="بازدیدکننده در مسیر تعاملی اکوپارک"
                                        className="aspect-[3/2] w-full object-cover transition duration-700 hover:scale-[1.02]"
                                    />
                                </div>
                            </div>

                            <ol className="grid gap-4 sm:grid-cols-2">
                                {capabilities.map((item, index) => {
                                    const Icon = item.icon;
                                    const featured = index === 0;
                                    const href = roleAwareHref(
                                        item.href,
                                        userRole,
                                    );

                                    return (
                                        <li
                                            key={item.title}
                                            className={
                                                featured
                                                    ? 'sm:col-span-2'
                                                    : undefined
                                            }
                                        >
                                            <Link
                                                href={href}
                                                className={`${interactiveRing} group relative block min-h-56 overflow-hidden rounded-[1.75rem] border p-6 transition duration-300 hover:-translate-y-1 hover:shadow-xl active:translate-y-0 active:scale-[.99] ${
                                                    featured
                                                        ? 'border-[#071a33] bg-[#071a33] text-white sm:grid sm:min-h-64 sm:grid-cols-[1fr_auto] sm:items-end sm:p-8'
                                                        : 'border-stone-200 bg-white hover:border-emerald-200'
                                                }`}
                                            >
                                                <span
                                                    className={`absolute top-5 left-6 text-5xl font-black ${featured ? 'text-white/[0.06]' : 'text-stone-100'}`}
                                                >
                                                    {String(index + 1).padStart(
                                                        2,
                                                        '0',
                                                    )}
                                                </span>
                                                <div>
                                                    <span
                                                        className={`flex size-12 items-center justify-center rounded-2xl ${featured ? 'bg-emerald-300 text-[#071a33]' : 'bg-emerald-50 text-emerald-700'}`}
                                                    >
                                                        <Icon className="size-6" />
                                                    </span>
                                                    <h3 className="mt-8 text-xl font-bold">
                                                        {item.title}
                                                    </h3>
                                                    <p
                                                        className={`mt-3 max-w-xl text-sm leading-7 ${featured ? 'text-slate-300' : 'text-zinc-600'}`}
                                                    >
                                                        {item.body}
                                                    </p>
                                                </div>
                                                <span
                                                    className={`mt-7 inline-flex items-center gap-2 text-sm font-bold ${featured ? 'text-emerald-300 sm:mt-0' : 'text-emerald-700'}`}
                                                >
                                                    ورود به بخش
                                                    <ArrowUpLeft className="size-4 transition-transform duration-300 group-hover:-translate-x-1 group-hover:-translate-y-0.5" />
                                                </span>
                                            </Link>
                                        </li>
                                    );
                                })}
                            </ol>
                        </div>
                    </div>
                </section>

                <section
                    id="architecture"
                    className="scroll-mt-6 overflow-hidden bg-[#06142b] px-5 py-24 text-white sm:px-8 lg:py-32"
                >
                    <div className="mx-auto max-w-7xl">
                        <div className="grid gap-10 lg:grid-cols-[.78fr_1.22fr] lg:items-end">
                            <SectionHeading
                                light
                                label="معماری محصول"
                                title="یک هسته، چهار منظر روشن"
                                description="هر نقش فقط اطلاعات و ابزار لازم خود را می‌بیند؛ در عین حال همه روی یک چرخه عملیاتی و قابل گزارش حرکت می‌کنند."
                            />
                            <div className="grid gap-px overflow-hidden rounded-[1.75rem] border border-white/10 bg-white/10 sm:grid-cols-2">
                                {audiences.map((item, index) => {
                                    const Icon = item.icon;

                                    return (
                                        <article
                                            key={item.title}
                                            className="min-h-60 bg-[#081a35]"
                                        >
                                            <Link
                                                href={item.href}
                                                className={`${interactiveRing} group flex min-h-60 flex-col p-6 transition hover:bg-[#0b2140] active:bg-[#0e294e] sm:p-7`}
                                            >
                                                <div className="flex items-start justify-between gap-5">
                                                    <span className="flex size-11 items-center justify-center rounded-xl border border-white/10 bg-white/[0.04] text-cyan-300 transition group-hover:border-cyan-300/30 group-hover:bg-cyan-300/10">
                                                        <Icon className="size-5" />
                                                    </span>
                                                    <span className="text-xs tracking-wider text-slate-500">
                                                        ۰{index + 1}
                                                    </span>
                                                </div>
                                                <p className="mt-7 text-xs font-semibold text-emerald-300">
                                                    {item.label}
                                                </p>
                                                <h3 className="mt-2 text-xl font-bold">
                                                    {item.title}
                                                </h3>
                                                <p className="mt-3 text-sm leading-7 text-slate-300">
                                                    {item.body}
                                                </p>
                                                <span className="mt-auto inline-flex items-center gap-2 pt-5 text-sm font-bold text-cyan-300">
                                                    ورود به پنل
                                                    <ArrowUpLeft className="size-4 transition-transform duration-300 group-hover:-translate-x-1 group-hover:-translate-y-0.5" />
                                                </span>
                                            </Link>
                                        </article>
                                    );
                                })}
                            </div>
                        </div>
                    </div>
                </section>

                <section className="bg-[#06142b] px-5 pb-24 sm:px-8 lg:pb-32">
                    <div className="mx-auto grid max-w-7xl gap-4 lg:grid-cols-[1.45fr_.55fr]">
                        <figure className="group relative min-h-[420px] overflow-hidden rounded-[2rem] lg:min-h-[570px]">
                            <img
                                src={proposalImages.bridge}
                                alt="مسیر نورپردازی‌شده اکوپارک؛ نمونه فضای اجرای تجربه اکسپلوریا"
                                className="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-[1.02]"
                            />
                            <div className="absolute inset-0 bg-gradient-to-t from-[#06142b]/95 via-[#06142b]/15 to-transparent" />
                            <figcaption className="absolute inset-x-0 bottom-0 p-7 text-white sm:p-10">
                                <p className="text-sm font-semibold text-cyan-300">
                                    تجربه در بستر واقعی مکان
                                </p>
                                <p className="mt-3 max-w-xl text-2xl leading-[1.5] font-bold sm:text-3xl">
                                    فناوری باید در خدمت خاطره‌ای باشد که
                                    بازدیدکننده با خود می‌برد.
                                </p>
                            </figcaption>
                        </figure>
                        <figure className="relative min-h-[320px] overflow-hidden rounded-[2rem] lg:min-h-[570px]">
                            <img
                                src={proposalImages.revenue}
                                alt="میدان شبانه اکوپارک با نمایش مسیر درآمد و بازگشت سرمایه"
                                className="absolute inset-0 h-full w-full object-cover"
                            />
                            <div className="absolute inset-0 bg-gradient-to-t from-[#06142b]/90 via-transparent to-transparent" />
                            <figcaption className="absolute inset-x-0 bottom-0 p-7">
                                <span className="inline-flex items-center gap-2 text-sm font-semibold text-amber-300">
                                    <CheckCircle2 className="size-4" />
                                    خروجی قابل سنجش
                                </span>
                            </figcaption>
                        </figure>
                    </div>
                </section>

                <section
                    id="revenue"
                    className="scroll-mt-6 bg-[#efe9df] px-5 py-24 sm:px-8 lg:py-32"
                >
                    <div className="mx-auto max-w-7xl">
                        <div className="grid gap-8 lg:grid-cols-[1fr_.58fr] lg:items-end">
                            <SectionHeading
                                label="مسیرهای درآمدی"
                                title="برای هر ذی‌نفع، یک مدل همکاری قابل توسعه"
                                description="مکان، اسپانسر، واحد عضو و خریدار رسانه هرکدام از مسیر تجاری متفاوتی وارد می‌شوند؛ سطح‌بندی فقط جایی نمایش داده می‌شود که مبنای مصوب دارد."
                            />
                            <aside className="border-r-2 border-amber-500 pr-5">
                                <div className="flex items-start gap-3">
                                    <ShieldCheck className="mt-1 size-5 shrink-0 text-amber-700" />
                                    <p className="text-sm leading-7 text-zinc-600">
                                        سطح و قیمت نهایی پس از تعیین دامنه اجرا،
                                        مدت، KPI و تعهد پاداش نهایی می‌شود.
                                    </p>
                                </div>
                            </aside>
                        </div>

                        <div className="mt-12 grid gap-5 lg:grid-cols-12">
                            <article className="overflow-hidden rounded-[2rem] bg-[#06142b] text-white shadow-[0_24px_70px_rgba(6,20,43,.14)] lg:col-span-7">
                                <Link
                                    href="/solutions/venues"
                                    aria-label="بررسی راهکار پایلوت مکان"
                                    className={`${interactiveRing} group flex h-full flex-col p-6 transition hover:bg-[#081a35] active:bg-[#0b2140] sm:p-8`}
                                >
                                    <div className="flex items-start justify-between gap-5">
                                        <div className="flex items-center gap-3">
                                            <span className="flex size-11 items-center justify-center rounded-xl border border-emerald-300/20 bg-emerald-300/10 text-emerald-300">
                                                <Building2 className="size-5" />
                                            </span>
                                            <div>
                                                <p className="text-xs font-bold text-emerald-300">
                                                    مکان میزبان
                                                </p>
                                                <h3 className="mt-1 text-2xl font-bold">
                                                    پایلوت مکان
                                                </h3>
                                            </div>
                                        </div>
                                        <span className="text-xs font-black tracking-wider text-slate-500">
                                            ۰۱
                                        </span>
                                    </div>

                                    <p className="mt-6 max-w-2xl text-sm leading-7 text-slate-300">
                                        پایلوت ۳۰ روزه با کمپین، QR، مأموریت،
                                        پاداش، شرکا، گزارش عملیاتی و ROI.
                                    </p>

                                    <div className="mt-6 flex flex-wrap items-baseline gap-x-3 gap-y-1 border-y border-white/10 py-4">
                                        <strong className="text-xl text-amber-300 sm:text-2xl">
                                            ۱۲۰ تا ۲۵۰ میلیون تومان
                                        </strong>
                                        <span className="text-xs text-slate-400">
                                            مبنای مذاکره
                                        </span>
                                    </div>

                                    <ol className="divide-y divide-white/10">
                                        {venueTiers.map(([title, body]) => (
                                            <li
                                                key={title}
                                                className="grid gap-1 py-3.5 sm:grid-cols-[7rem_1fr] sm:gap-4"
                                            >
                                                <strong className="text-sm text-white">
                                                    {title}
                                                </strong>
                                                <span className="text-sm leading-6 text-slate-300">
                                                    {body}
                                                </span>
                                            </li>
                                        ))}
                                    </ol>

                                    <span className="mt-auto inline-flex items-center gap-2 pt-6 text-sm font-bold text-emerald-300">
                                        بررسی راهکار مکان
                                        <ArrowUpLeft className="size-4 transition-transform duration-300 group-hover:-translate-x-1 group-hover:-translate-y-0.5" />
                                    </span>
                                </Link>
                            </article>

                            <article className="overflow-hidden rounded-[2rem] border border-amber-300/70 bg-[#f8f5ef] lg:col-span-5">
                                <Link
                                    href="/admin/sponsors"
                                    aria-label="ورود به پنل اسپانسر"
                                    className="group flex h-full flex-col p-6 transition hover:bg-amber-50/70 focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:outline-none focus-visible:ring-inset active:bg-amber-100/60 sm:p-8"
                                >
                                    <div className="flex items-start justify-between gap-5">
                                        <div className="flex items-center gap-3">
                                            <span className="flex size-11 items-center justify-center rounded-xl bg-amber-100 text-amber-800">
                                                <Handshake className="size-5" />
                                            </span>
                                            <div>
                                                <p className="text-xs font-bold text-amber-700">
                                                    برند و اسپانسر
                                                </p>
                                                <h3 className="mt-1 text-2xl font-bold text-zinc-950">
                                                    حمایت از کمپین
                                                </h3>
                                            </div>
                                        </div>
                                        <span className="text-xs font-black tracking-wider text-stone-400">
                                            ۰۲
                                        </span>
                                    </div>

                                    <div className="mt-6 flex flex-wrap items-baseline gap-x-3 gap-y-1 border-y border-stone-300 py-4">
                                        <strong className="text-xl text-zinc-950 sm:text-2xl">
                                            ۸۰ تا ۳۰۰ میلیون تومان
                                        </strong>
                                        <span className="text-xs text-zinc-500">
                                            مبنای مذاکره
                                        </span>
                                    </div>

                                    <ol className="divide-y divide-stone-300">
                                        {sponsorTiers.map(([title, body]) => (
                                            <li key={title} className="py-3.5">
                                                <strong className="text-sm text-zinc-950">
                                                    {title}
                                                </strong>
                                                <p className="mt-1 text-sm leading-6 text-zinc-600">
                                                    {body}
                                                </p>
                                            </li>
                                        ))}
                                    </ol>

                                    <span className="mt-auto inline-flex items-center gap-2 pt-6 text-sm font-bold text-amber-800">
                                        ورود به پنل اسپانسر
                                        <ArrowUpLeft className="size-4 transition-transform duration-300 group-hover:-translate-x-1 group-hover:-translate-y-0.5" />
                                    </span>
                                </Link>
                            </article>

                            <article className="overflow-hidden rounded-[2rem] border border-emerald-200 bg-white lg:col-span-7">
                                <Link
                                    href="/solutions/commercial-units"
                                    aria-label="بررسی مدل عضویت واحد تجاری"
                                    className={`${interactiveRing} group grid h-full gap-7 p-6 transition hover:bg-emerald-50/60 active:bg-emerald-100/50 sm:p-8 lg:grid-cols-[.9fr_1.1fr] lg:items-center`}
                                >
                                    <div>
                                        <div className="flex items-center gap-3">
                                            <span className="flex size-11 items-center justify-center rounded-xl bg-emerald-100 text-emerald-800">
                                                <Store className="size-5" />
                                            </span>
                                            <div>
                                                <p className="text-xs font-bold text-emerald-700">
                                                    واحد تجاری و فرهنگی
                                                </p>
                                                <h3 className="mt-1 text-xl font-bold text-zinc-950">
                                                    عضویت واحد در شبکه
                                                </h3>
                                            </div>
                                        </div>
                                        <p className="mt-6 text-lg leading-8 font-bold text-zinc-950">
                                            ۱۰ تا ۳۰ میلیون تومان ماهانه
                                        </p>
                                        <p className="text-sm leading-7 text-zinc-600">
                                            + کارمزد مصرف معتبر؛ یک مدل اشتراکی
                                            منعطف و قابل تنظیم.
                                        </p>
                                        <span className="mt-5 inline-flex items-center gap-2 text-sm font-bold text-emerald-800">
                                            بررسی مدل عضویت
                                            <ArrowUpLeft className="size-4 transition-transform duration-300 group-hover:-translate-x-1 group-hover:-translate-y-0.5" />
                                        </span>
                                    </div>

                                    <div className="border-t border-stone-200 pt-6 lg:border-t-0 lg:border-r lg:pt-0 lg:pr-7">
                                        <p className="text-xs font-bold text-zinc-500">
                                            خروجی عضویت
                                        </p>
                                        <ul className="mt-3 grid grid-cols-2 gap-x-4 gap-y-2 text-sm text-zinc-700">
                                            {memberOutcomes.map((outcome) => (
                                                <li
                                                    key={outcome}
                                                    className="flex items-center gap-2"
                                                >
                                                    <CheckCircle2 className="size-4 shrink-0 text-emerald-600" />
                                                    {outcome}
                                                </li>
                                            ))}
                                        </ul>
                                        <p className="mt-6 text-xs font-bold text-zinc-500">
                                            بسته‌های قابل تنظیم
                                        </p>
                                        <p className="mt-2 text-sm leading-7 text-zinc-700">
                                            {memberPackageLabels.join(' · ')}
                                        </p>
                                    </div>
                                </Link>
                            </article>

                            <article className="overflow-hidden rounded-[2rem] bg-[#081a35] text-white lg:col-span-5">
                                <Link
                                    href={roleAwareHref(
                                        '/admin/display-operations',
                                        userRole,
                                    )}
                                    aria-label="ورود به عملیات نمایشگر"
                                    className={`${interactiveRing} group flex h-full flex-col p-6 transition hover:bg-[#0b2140] active:bg-[#0e294e] sm:p-8`}
                                >
                                    <div className="flex items-center gap-3">
                                        <span className="flex size-11 items-center justify-center rounded-xl border border-cyan-300/20 bg-cyan-300/10 text-cyan-300">
                                            <MonitorPlay className="size-5" />
                                        </span>
                                        <div>
                                            <p className="text-xs font-bold text-cyan-300">
                                                رسانه و نمایشگر
                                            </p>
                                            <h3 className="mt-1 text-xl font-bold">
                                                بسته نمایش و تبلیغات
                                            </h3>
                                        </div>
                                    </div>

                                    <p className="mt-6 text-sm leading-7 text-slate-300">
                                        قیمت‌گذاری بر اساس جایگاه، مدت و تعداد
                                        پخش انجام می‌شود.
                                    </p>
                                    <p className="mt-4 text-sm font-bold text-white">
                                        {displayFormats.join(' · ')}
                                    </p>
                                    <p className="mt-4 border-r-2 border-cyan-300/60 pr-4 text-sm leading-7 text-slate-300">
                                        زمان‌بندی، مستند پخش و گزارش در خروجی
                                        بسته قرار می‌گیرد.
                                    </p>

                                    <span className="mt-auto inline-flex items-center gap-2 pt-7 text-sm font-bold text-cyan-300">
                                        ورود به عملیات نمایشگر
                                        <ArrowUpLeft className="size-4 transition-transform duration-300 group-hover:-translate-x-1 group-hover:-translate-y-0.5" />
                                    </span>
                                </Link>
                            </article>
                        </div>

                        <aside className="mt-5 overflow-hidden rounded-[2rem] bg-[#06142b] text-white">
                            <Link
                                href="/demo/proposal"
                                aria-label="مشاهده پروپوزال هیئت‌مدیره اکوپارک"
                                className={`${interactiveRing} group grid gap-6 p-6 transition hover:bg-[#081a35] active:bg-[#0b2140] sm:p-8 lg:grid-cols-[auto_1fr_auto] lg:items-center`}
                            >
                                <span className="flex size-12 items-center justify-center rounded-xl border border-amber-300/25 bg-amber-300/10 text-amber-300">
                                    <Sparkles className="size-5" />
                                </span>
                                <div>
                                    <p className="text-xs font-bold text-amber-300">
                                        ارائه مدیریتی
                                    </p>
                                    <h3 className="mt-2 text-xl font-bold sm:text-2xl">
                                        پروپوزال هیئت‌مدیره اکوپارک
                                    </h3>
                                    <p className="mt-2 max-w-3xl text-sm leading-7 text-slate-300">
                                        اسلایدهای کلیدی پروپوزال را به پوشش زنده
                                        و دمو متصل می‌کند و شکاف‌های پایلوت را
                                        برای تصمیم‌گیری نشان می‌دهد.
                                    </p>
                                </div>
                                <span className="inline-flex items-center gap-2 text-sm font-bold text-amber-300 lg:justify-self-end">
                                    مشاهده نمای جلسه
                                    <ArrowUpLeft className="size-4 transition-transform duration-300 group-hover:-translate-x-1 group-hover:-translate-y-0.5" />
                                </span>
                            </Link>
                        </aside>
                    </div>
                </section>

                <section
                    id="demo-request"
                    className="scroll-mt-6 bg-white px-5 py-24 sm:px-8 lg:py-32"
                >
                    <div className="mx-auto grid max-w-7xl overflow-hidden rounded-[2rem] bg-[#06142b] shadow-[0_30px_90px_rgba(6,20,43,.18)] lg:grid-cols-[.82fr_1.18fr]">
                        <div className="relative isolate overflow-hidden p-7 text-white sm:p-10 lg:p-12">
                            <img
                                src={proposalImages.participant}
                                alt=""
                                className="absolute inset-0 -z-20 h-full w-full object-cover opacity-30"
                            />
                            <div className="absolute inset-0 -z-10 bg-[linear-gradient(180deg,rgba(6,20,43,.65),rgba(6,20,43,.97))]" />
                            <p className="text-sm font-bold text-emerald-300">
                                قدم بعدی
                            </p>
                            <h2 className="mt-4 text-3xl leading-[1.45] font-bold sm:text-4xl">
                                اجرای شما می‌تواند مسیر بعدی اکسپلوریا باشد.
                            </h2>
                            <p className="mt-5 text-sm leading-8 text-slate-300 sm:text-base">
                                مشخصات مجموعه و هدف اولیه را ثبت کنید؛ تیم
                                اکسپلوریا برای یک جلسه معرفی متناسب با موقعیت
                                شما هماهنگ می‌کند.
                            </p>
                            <div className="mt-10 border-t border-white/10 pt-6">
                                <p className="text-xs text-slate-400">
                                    فرآیند پیگیری
                                </p>
                                <div className="mt-4 flex items-center gap-3 text-sm text-slate-200">
                                    <span className="flex size-8 items-center justify-center rounded-full bg-emerald-300 font-bold text-[#06142b]">
                                        ۱
                                    </span>
                                    ثبت درخواست
                                    <span className="h-px flex-1 bg-white/15" />
                                    <span className="flex size-8 items-center justify-center rounded-full border border-white/20">
                                        ۲
                                    </span>
                                    هماهنگی جلسه
                                </div>
                            </div>
                        </div>

                        <Form
                            action="/marketing/leads"
                            method="post"
                            options={{ preserveScroll: true }}
                            className="bg-[#f8f5ef] p-6 sm:p-10 lg:p-12"
                        >
                            {({ errors, processing, recentlySuccessful }) => (
                                <div className="grid gap-5">
                                    <div>
                                        <p className="text-sm font-bold text-emerald-700">
                                            فرم درخواست دمو
                                        </p>
                                        <p className="mt-2 text-sm leading-7 text-zinc-500">
                                            فیلدهای ستاره‌دار برای پیگیری
                                            درخواست ضروری‌اند.
                                        </p>
                                    </div>

                                    <input
                                        type="hidden"
                                        name="source_path"
                                        value={seo.canonicalPath}
                                    />
                                    <input
                                        type="text"
                                        name="company_url"
                                        tabIndex={-1}
                                        autoComplete="off"
                                        className="hidden"
                                    />

                                    <div className="grid gap-2">
                                        <label
                                            htmlFor="audience_type"
                                            className="text-sm font-semibold"
                                        >
                                            نوع همکاری
                                        </label>
                                        <select
                                            id="audience_type"
                                            name="audience_type"
                                            defaultValue={focus.audienceType}
                                            className={fieldClass}
                                        >
                                            <option value="venue">
                                                مکان گردشگری یا تفریحی
                                            </option>
                                            <option value="commercial_unit">
                                                واحد تجاری یا فودکورت
                                            </option>
                                            <option value="sponsor">
                                                اسپانسر یا برند
                                            </option>
                                            <option value="visitor_growth">
                                                جذب و مشارکت بازدیدکننده
                                            </option>
                                            <option value="other">سایر</option>
                                        </select>
                                        {errors.audience_type ? (
                                            <p
                                                role="alert"
                                                className="text-xs text-red-700"
                                            >
                                                {errors.audience_type}
                                            </p>
                                        ) : null}
                                    </div>

                                    <div className="grid gap-5 sm:grid-cols-2">
                                        <div className="grid gap-2">
                                            <label
                                                htmlFor="contact_name"
                                                className="text-sm font-semibold"
                                            >
                                                نام مسئول پیگیری *
                                            </label>
                                            <input
                                                id="contact_name"
                                                name="contact_name"
                                                required
                                                className={fieldClass}
                                                placeholder="مثلاً مدیر بازاریابی"
                                            />
                                            {errors.contact_name ? (
                                                <p
                                                    role="alert"
                                                    className="text-xs text-red-700"
                                                >
                                                    {errors.contact_name}
                                                </p>
                                            ) : null}
                                        </div>
                                        <div className="grid gap-2">
                                            <label
                                                htmlFor="mobile"
                                                className="text-sm font-semibold"
                                            >
                                                شماره تماس *
                                            </label>
                                            <input
                                                id="mobile"
                                                name="mobile"
                                                required
                                                dir="ltr"
                                                className={fieldClass}
                                                placeholder="0912..."
                                            />
                                            {errors.mobile ? (
                                                <p
                                                    role="alert"
                                                    className="text-xs text-red-700"
                                                >
                                                    {errors.mobile}
                                                </p>
                                            ) : null}
                                        </div>
                                    </div>

                                    <div className="grid gap-5 sm:grid-cols-2">
                                        <label className="grid gap-2 text-sm font-semibold">
                                            نام مجموعه یا برند
                                            <input
                                                name="organization_name"
                                                className={fieldClass}
                                                placeholder="نام مجموعه"
                                            />
                                        </label>
                                        <label className="grid gap-2 text-sm font-semibold">
                                            شهر
                                            <input
                                                name="city"
                                                className={fieldClass}
                                                placeholder="شهر محل اجرا"
                                            />
                                        </label>
                                    </div>

                                    <label className="grid gap-2 text-sm font-semibold">
                                        نوع پروژه
                                        <input
                                            name="project_hint"
                                            className={fieldClass}
                                            placeholder="برج، پارک، مرکز خرید یا فودکورت"
                                        />
                                    </label>

                                    <label className="grid gap-2 text-sm font-semibold">
                                        توضیحات تکمیلی
                                        <textarea
                                            name="notes"
                                            rows={3}
                                            className={`${fieldClass} resize-y py-3`}
                                            placeholder="هدف شما از اجرای پایلوت چیست؟"
                                        />
                                    </label>

                                    {flash?.success || recentlySuccessful ? (
                                        <p
                                            role="status"
                                            className="flex items-start gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm leading-7 text-emerald-900"
                                        >
                                            <CheckCircle2 className="mt-1 size-4 shrink-0" />
                                            {flash?.success ??
                                                'درخواست ثبت شد.'}
                                        </p>
                                    ) : null}

                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className={`${interactiveRing} inline-flex min-h-13 items-center justify-center gap-3 rounded-xl bg-emerald-700 px-6 text-sm font-bold text-white transition hover:bg-emerald-800 disabled:cursor-not-allowed disabled:opacity-60`}
                                    >
                                        {processing
                                            ? 'در حال ثبت درخواست…'
                                            : 'ثبت درخواست دمو'}
                                        <ArrowLeft className="size-5" />
                                    </button>
                                </div>
                            )}
                        </Form>
                    </div>
                </section>

                <footer className="border-t border-stone-200 bg-white px-5 py-8 sm:px-8">
                    <div className="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 text-center text-xs text-zinc-500 sm:flex-row sm:text-right">
                        <span>EXPLORIA — تجربه، مشارکت و رشد قابل سنجش</span>
                        <a
                            href="#"
                            className={`${interactiveRing} rounded-sm transition hover:text-emerald-700`}
                        >
                            بازگشت به ابتدای صفحه
                        </a>
                    </div>
                </footer>
            </main>
        </>
    );
}
