import { Form, Head, Link, usePage } from '@inertiajs/react';
import {
    ArrowLeft,
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
    Store,
    TicketCheck,
    UsersRound,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';

const demoQrCode = 'ep1405-a7f3k9m2q8x4';
const proposalImages = {
    hero: '/images/ecopark/proposal/abbasabad-nature-bridge-demo.jpg',
    path: '/images/ecopark/proposal/ecopark-night-path-16-9.jpg',
    roadmap: '/images/ecopark/proposal/ecopark-roadmap-night-21-9.jpg',
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

const primaryRoutes: Array<{
    title: string;
    body: string;
    href: string;
    icon: LucideIcon;
    tone: string;
}> = [
    {
        title: 'شروع تجربه بازدیدکننده',
        body: 'ورود با QR، موبایل، رضایت‌نامه و مسیر مشارکت.',
        href: `/scan/${demoQrCode}`,
        icon: QrCode,
        tone: 'border-emerald-200 bg-emerald-50 text-emerald-950',
    },
    {
        title: 'ورود مدیریتی',
        body: 'ورود ادمین، تیم داخلی، مدیر مکان، رواق، فروشگاه یا اسپانسر.',
        href: '/login',
        icon: ShieldCheck,
        tone: 'border-cyan-200 bg-cyan-50 text-cyan-950',
    },
    {
        title: 'چرخه دمو و فروش داخلی',
        body: 'مشاهده آمادگی دمو، بسته‌های فروش و مسیر مذاکره با حساب داخلی.',
        href: '/admin/commercialization',
        icon: BadgeDollarSign,
        tone: 'border-amber-200 bg-amber-50 text-amber-950',
    },
];

const valueLoop = [
    ['۱', 'ورود', 'کاربر از QR یا صفحه کمپین وارد تجربه می‌شود.'],
    ['۲', 'مشارکت', 'ماموریت، مسیر، گنج، امتیاز و پاداش را دنبال می‌کند.'],
    [
        '۳',
        'تعامل تجاری',
        'به واحد عضو، فروشگاه، رستوران یا پیشنهاد اسپانسر وصل می‌شود.',
    ],
    ['۴', 'گزارش', 'مدیر مکان، اسپانسر و واحد عضو خروجی عددی می‌گیرند.'],
];

const audiences = [
    {
        title: 'برای مدیر مکان',
        body: 'یک پایلوت قابل اجرا با QR، ماموریت، پاداش، کنترل ریسک و گزارش پایان اجرا.',
        icon: Building2,
        items: ['نمای کل مکان', 'هماهنگی زون‌ها', 'گزارش ROI'],
    },
    {
        title: 'برای رواق، هاب و واحدها',
        body: 'مدیریت پیشنهاد، کد مصرف، حضور در کمپین و مشاهده اثر مراجعه کاربران.',
        icon: Store,
        items: ['پاداش و تخفیف', 'مصرف کد', 'گزارش مراجعه'],
    },
    {
        title: 'برای اسپانسر',
        body: 'اتصال برند به جایزه، گنج، مسیر خانوادگی، تبلیغ و گزارش تعامل قابل ارائه.',
        icon: Handshake,
        items: ['جایزه برنددار', 'نمایش و تعامل', 'گزارش claim'],
    },
    {
        title: 'برای بازدیدکننده',
        body: 'تجربه ساده، قابل فهم و سرگرم‌کننده برای مشارکت فردی، خانوادگی یا تیمی.',
        icon: UsersRound,
        items: ['مسیر کمپین', 'کیف پاداش', 'ادامه مشارکت'],
    },
];

const capabilities: Array<[string, string, LucideIcon]> = [
    [
        'مدیریت کمپین',
        'ثبت، ساخت، انتخاب الگو و نقشه عملیات کمپین.',
        LayoutDashboard,
    ],
    ['QR و ورود', 'کدهای ورودی، نقاط تماس، رضایت‌نامه و ثبت بازدید.', QrCode],
    [
        'ماموریت و پاداش',
        'تعریف ماموریت، گنج، امتیاز، کوپن، هدیه و مصرف پاداش.',
        Gift,
    ],
    [
        'تبلیغات و نمایشگر',
        'تبلیغ مستقل، زمان‌بندی نمایشگر و کنترل محتوای میدانی.',
        MonitorPlay,
    ],
    [
        'پنل‌های نقش‌محور',
        'ادمین، مدیر مکان، رواق، فروشگاه، اسپانسر و مشارکت‌کننده.',
        ShieldCheck,
    ],
    [
        'داشبورد فروش',
        'ROI، بسته قیمت، مدارک مذاکره و قیف تبدیل دمو به قرارداد.',
        BarChart3,
    ],
];

const revenuePacks = [
    {
        title: 'پکیج پایلوت مکان',
        buyer: 'مدیر اجرایی مکان',
        price: '۱۲۰ تا ۲۵۰ میلیون تومان',
    },
    {
        title: 'پکیج اسپانسر کمپین',
        buyer: 'اسپانسر داخلی یا بیرونی',
        price: '۸۰ تا ۳۰۰ میلیون تومان',
    },
    {
        title: 'پکیج واحد عضو',
        buyer: 'فروشگاه، فودکورت، رستوران یا واحد فرهنگی',
        price: 'اشتراک ماهانه + کارمزد مصرف پاداش',
    },
];

const focusProfiles: Record<
    string,
    {
        eyebrow: string;
        headline: string;
        summary: string;
        audienceType: string;
        demoLabel: string;
        demoHint: string;
    }
> = {
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

export default function Welcome({
    marketingFocus = 'home',
    seo = defaultSeo,
}: WelcomeProps) {
    const { auth, flash } = usePage<SharedProps>().props;
    const userRole = auth?.user?.role;
    const focus = focusProfiles[marketingFocus] ?? focusProfiles.home;
    const commercializationHref = roleAwareHref(
        '/admin/commercialization',
        userRole,
    );

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

            <main dir="rtl" className="min-h-screen bg-stone-50 text-zinc-950">
                <section className="relative overflow-hidden bg-[#061033] text-white">
                    <img
                        src={proposalImages.hero}
                        alt=""
                        className="absolute inset-0 h-full w-full object-cover object-[56%_center] sm:object-center"
                    />
                    <div className="absolute inset-0 bg-[linear-gradient(90deg,rgba(6,16,51,0.18)_0%,rgba(6,16,51,0.38)_44%,rgba(6,16,51,0.86)_100%)]" />
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_64%_22%,rgba(221,82,255,0.22),transparent_34%),linear-gradient(180deg,rgba(6,16,51,0.18),rgba(6,16,51,0.72))]" />
                    <div className="absolute inset-x-0 bottom-0 h-40 bg-gradient-to-t from-stone-50 to-transparent" />
                    <div className="relative mx-auto flex min-h-[94vh] max-w-7xl flex-col px-5 py-5 sm:px-8 lg:px-10">
                        <header className="flex flex-col items-center gap-4 pt-1">
                            <Link
                                href="/"
                                aria-label="EXPLORIA"
                                className="flex items-center"
                            >
                                <span
                                    dir="ltr"
                                    className="flex items-center text-4xl leading-none font-black tracking-[0.08em] text-white sm:text-5xl lg:text-6xl"
                                    style={{
                                        fontFamily:
                                            '"Palatino Linotype", "Cinzel", "Trajan Pro", Georgia, serif',
                                    }}
                                >
                                    <span>E</span>
                                    <span
                                        aria-hidden="true"
                                        className="relative mx-1 inline-flex h-11 w-10 shrink-0 items-center justify-center sm:h-14 sm:w-12 lg:h-16 lg:w-14"
                                    >
                                        <span className="absolute top-1/2 left-1/2 h-[3.9rem] w-1 origin-center -translate-x-1/2 -translate-y-1/2 rotate-45 rounded-full bg-gradient-to-b from-white via-emerald-200 to-emerald-500 shadow-[0_0_22px_rgba(16,185,129,0.95)] sm:h-[4.9rem] sm:w-1.5 lg:h-[5.6rem]" />
                                        <span className="absolute top-1/2 left-1/2 h-[3.1rem] w-1 origin-center -translate-x-1/2 -translate-y-1/2 -rotate-45 rounded-full bg-gradient-to-b from-fuchsia-100 via-cyan-200 to-emerald-300 shadow-[0_0_16px_rgba(34,211,238,0.7)] sm:h-[3.9rem] sm:w-1.5 lg:h-[4.35rem]" />
                                        <span className="absolute top-[12%] left-[15%] h-2 w-3 rotate-45 rounded-full bg-white/85 blur-[1px] sm:h-2.5 sm:w-4" />
                                    </span>
                                    <span className="sr-only">X</span>
                                    <span>PLORIA</span>
                                </span>
                            </Link>
                            <nav className="flex flex-wrap justify-center gap-2 text-sm">
                                <Link
                                    href="/offers"
                                    className="inline-flex h-10 items-center rounded-md border border-white/20 px-4 hover:bg-white/10"
                                >
                                    پیشنهادهای امروز
                                </Link>
                                <Link
                                    href="/login"
                                    className="inline-flex h-10 items-center rounded-md border border-white/20 px-4 hover:bg-white/10"
                                >
                                    ورود مدیریتی
                                </Link>
                                <a
                                    href="#demo-request"
                                    className="inline-flex h-10 items-center rounded-md border border-white/20 px-4 hover:bg-white/10"
                                >
                                    درخواست دمو
                                </a>
                                <Link
                                    href={`/scan/${demoQrCode}`}
                                    className="inline-flex h-10 items-center gap-2 rounded-md bg-emerald-400 px-4 font-semibold text-zinc-950 hover:bg-emerald-300"
                                >
                                    {focus.demoLabel}
                                    <ArrowLeft className="size-4" />
                                </Link>
                            </nav>
                        </header>

                        <div className="flex flex-1 items-center justify-center py-10 text-center">
                            <div className="mx-auto max-w-4xl">
                                <p className="mx-auto flex w-fit rounded-full border border-fuchsia-200/50 bg-white/[0.07] px-5 py-2.5 text-center text-base font-semibold shadow-[0_0_24px_rgba(217,70,239,0.18)] sm:text-lg">
                                    <span className="bg-gradient-to-l from-emerald-200 via-fuchsia-100 to-cyan-200 bg-clip-text text-transparent">
                                        {focus.eyebrow}
                                    </span>
                                </p>
                                <h1
                                    aria-label={focus.headline}
                                    className="mt-6 text-4xl leading-tight font-semibold sm:text-6xl"
                                >
                                    {focus.headline}
                                </h1>
                                <p className="mx-auto mt-6 max-w-2xl text-base leading-8 text-zinc-300">
                                    {focus.summary}
                                </p>
                                <div className="mx-auto mt-7 grid max-w-3xl gap-2 sm:grid-cols-2">
                                    {[
                                        'شروع تجربه با تصویر واقعی مکان',
                                        'ورود بازدیدکننده از QR اکوپارک',
                                        'مسیر، ماموریت و پاداش قابل نمایش',
                                        'خروجی فروش و ROI برای مذاکره',
                                    ].map((item) => (
                                        <div
                                            key={item}
                                            className="flex items-center gap-2 rounded-md border border-white/10 bg-white/[0.04] px-3 py-2 text-sm text-zinc-200"
                                        >
                                            <CheckCircle2 className="size-4 text-fuchsia-200" />
                                            <span>{item}</span>
                                        </div>
                                    ))}
                                </div>
                                <div className="mt-8 flex flex-wrap justify-center gap-3">
                                    <Link
                                        href={`/scan/${demoQrCode}`}
                                        className="inline-flex h-12 items-center gap-2 rounded-md bg-emerald-400 px-5 text-sm font-semibold text-zinc-950 hover:bg-emerald-300"
                                    >
                                        {focus.demoLabel}
                                        <QrCode className="size-4" />
                                    </Link>
                                    <Link
                                        href={commercializationHref}
                                        className="inline-flex h-12 items-center gap-2 rounded-md bg-white px-5 text-sm font-semibold text-zinc-950 hover:bg-zinc-100"
                                    >
                                        صفحه تجاری‌سازی
                                        <BadgeDollarSign className="size-4" />
                                    </Link>
                                    <Link
                                        href="/offers"
                                        className="inline-flex h-12 items-center gap-2 rounded-md border border-white/25 px-5 text-sm font-semibold hover:bg-white/10"
                                    >
                                        پیشنهادهای امروز
                                        <TicketCheck className="size-4" />
                                    </Link>
                                    <Link
                                        href="/dashboard"
                                        className="inline-flex h-12 items-center gap-2 rounded-md border border-white/25 px-5 text-sm font-semibold hover:bg-white/10"
                                    >
                                        داشبورد عملیاتی
                                        <BarChart3 className="size-4" />
                                    </Link>
                                </div>
                                <p className="mx-auto mt-3 max-w-2xl text-xs leading-6 text-zinc-400">
                                    {focus.demoHint} صفحه تجاری‌سازی و چرخه دمو
                                    با حساب داخلی باز می‌شوند؛ حساب بازدیدکننده
                                    به تجربه QR و داشبورد خودش هدایت می‌شود.
                                </p>
                            </div>
                        </div>

                        <div className="grid gap-3 pb-4 sm:grid-cols-2 lg:grid-cols-4">
                            {valueLoop.map(([number, title, body]) => (
                                <article
                                    key={title}
                                    className="rounded-lg border border-white/15 bg-zinc-950/45 p-4 backdrop-blur-sm"
                                >
                                    <span className="text-sm text-emerald-300">
                                        {number}
                                    </span>
                                    <h2 className="mt-2 font-semibold">
                                        {title}
                                    </h2>
                                    <p className="mt-2 text-sm leading-6 text-zinc-300">
                                        {body}
                                    </p>
                                </article>
                            ))}
                        </div>
                    </div>
                </section>

                <section className="mx-auto max-w-7xl px-5 py-8 sm:px-8 lg:px-10">
                    <div className="grid gap-4 md:grid-cols-3">
                        {primaryRoutes.map((route) => (
                            <Link
                                key={route.title}
                                href={roleAwareHref(route.href, userRole)}
                                className={`rounded-lg border p-5 transition hover:-translate-y-0.5 hover:shadow-md ${route.tone}`}
                            >
                                <route.icon className="size-6" />
                                <h2 className="mt-4 text-lg font-semibold">
                                    {route.title}
                                </h2>
                                <p className="mt-2 text-sm leading-7 opacity-80">
                                    {route.body}
                                </p>
                            </Link>
                        ))}
                    </div>
                </section>

                <section className="bg-white">
                    <div className="mx-auto max-w-7xl px-5 py-10 sm:px-8 lg:px-10">
                        <div className="grid gap-5 lg:grid-cols-[0.8fr_1.2fr] lg:items-end">
                            <div>
                                <p className="text-sm font-medium text-emerald-700">
                                    معماری محصول
                                </p>
                                <h2 className="mt-2 text-3xl font-semibold">
                                    یک پلتفرم، چند نقش، یک چرخه درآمدی
                                </h2>
                            </div>
                            <p className="text-sm leading-7 text-zinc-600">
                                اکسپلوریا برای هر نقش، سطح دسترسی و خروجی خودش
                                را جدا می‌کند؛ مدیر مکان فقط نمای کل و ریسک‌ها
                                را می‌بیند، رواق هماهنگی محدوده را مدیریت
                                می‌کند، فروشگاه پیشنهاد و مصرف کد را می‌بیند، و
                                اسپانسر گزارش تعامل برند را دریافت می‌کند.
                            </p>
                        </div>

                        <div className="mt-6 grid gap-4 lg:grid-cols-4">
                            {audiences.map((item) => (
                                <article
                                    key={item.title}
                                    className="rounded-lg border border-zinc-200 bg-stone-50 p-5"
                                >
                                    <item.icon className="size-6 text-emerald-700" />
                                    <h3 className="mt-4 text-lg font-semibold">
                                        {item.title}
                                    </h3>
                                    <p className="mt-2 text-sm leading-7 text-zinc-600">
                                        {item.body}
                                    </p>
                                    <div className="mt-4 flex flex-wrap gap-2">
                                        {item.items.map((tag) => (
                                            <span
                                                key={tag}
                                                className="rounded-full bg-white px-3 py-1 text-xs text-zinc-700"
                                            >
                                                {tag}
                                            </span>
                                        ))}
                                    </div>
                                </article>
                            ))}
                        </div>
                    </div>
                </section>

                <section className="bg-white">
                    <div className="mx-auto grid max-w-7xl gap-4 px-5 pb-10 sm:px-8 lg:grid-cols-2 lg:px-10">
                        <img
                            src={proposalImages.path}
                            alt=""
                            className="h-64 w-full rounded-lg object-cover shadow-sm"
                        />
                        <img
                            src={proposalImages.roadmap}
                            alt=""
                            className="h-64 w-full rounded-lg object-cover shadow-sm"
                        />
                    </div>
                </section>

                <section className="mx-auto max-w-7xl px-5 py-10 sm:px-8 lg:px-10">
                    <div className="grid gap-7 lg:grid-cols-[0.82fr_1.18fr] lg:items-center">
                        <div>
                            <p className="text-sm font-medium text-cyan-700">
                                قابلیت‌ها
                            </p>
                            <h2 className="mt-2 text-3xl font-semibold">
                                از دمو تا اجرای واقعی
                            </h2>
                            <p className="mt-3 text-sm leading-7 text-zinc-600">
                                صفحه ورودی باید نشان دهد که اکسپلوریا فقط یک
                                بازی یا یک پنل نیست؛ یک جریان کامل اجرایی و
                                تجاری است که می‌تواند برای مکان، واحد عضو و
                                اسپانسر عدد بسازد.
                            </p>
                        </div>
                        <div className="grid gap-3 md:grid-cols-2">
                            {capabilities.map(([title, body, Icon]) => (
                                <article
                                    key={title}
                                    className="rounded-lg border border-zinc-200 bg-white p-4"
                                >
                                    <Icon className="size-5 text-cyan-700" />
                                    <h3 className="mt-3 font-semibold">
                                        {title}
                                    </h3>
                                    <p className="mt-2 text-sm leading-7 text-zinc-600">
                                        {body}
                                    </p>
                                </article>
                            ))}
                        </div>
                    </div>
                </section>

                <section className="bg-zinc-950 text-white">
                    <div className="mx-auto max-w-7xl px-5 py-10 sm:px-8 lg:px-10">
                        <div className="grid gap-7 lg:grid-cols-[0.74fr_1.26fr] lg:items-center">
                            <div>
                                <p className="text-sm font-medium text-amber-300">
                                    مدل درآمدی
                                </p>
                                <h2 className="mt-2 text-3xl font-semibold">
                                    سه پیشنهاد ساده برای شروع فروش
                                </h2>
                                <p className="mt-3 text-sm leading-7 text-zinc-300">
                                    برای مذاکره واقعی، همه قابلیت‌ها یکجا فروخته
                                    نمی‌شوند. محصول به سه بسته قابل فهم تبدیل
                                    شده است.
                                </p>
                            </div>
                            <div className="grid gap-3 md:grid-cols-3">
                                {revenuePacks.map((pack) => (
                                    <article
                                        key={pack.title}
                                        className="flex min-h-44 flex-col rounded-lg border border-white/10 bg-white/10 p-4"
                                    >
                                        <TicketCheck className="size-5 text-amber-300" />
                                        <h3 className="mt-4 font-semibold">
                                            {pack.title}
                                        </h3>
                                        <p className="mt-2 text-sm leading-6 text-zinc-300">
                                            {pack.buyer}
                                        </p>
                                        <p className="mt-auto rounded-md bg-amber-300 px-3 py-2 text-sm font-semibold text-zinc-950">
                                            {pack.price}
                                        </p>
                                    </article>
                                ))}
                            </div>
                        </div>
                    </div>
                </section>

                <section id="demo-request" className="bg-white">
                    <div className="mx-auto grid max-w-7xl gap-6 px-5 py-9 sm:px-8 lg:grid-cols-[0.9fr_1.1fr] lg:items-start lg:px-10">
                        <div>
                            <p className="text-sm font-medium text-emerald-700">
                                قدم بعدی
                            </p>
                            <h2 className="mt-2 text-3xl font-semibold">
                                اکسپلوریا آماده نمایش، پایلوت و مذاکره است.
                            </h2>
                            <p className="mt-3 text-sm leading-7 text-zinc-600">
                                برای جلسه فروش با حساب داخلی از صفحه تجاری‌سازی
                                شروع کنید؛ برای تست تجربه کاربر، QR دمو را باز
                                کنید.
                            </p>
                            <div className="mt-5 flex flex-wrap gap-3">
                                <Link
                                    href={commercializationHref}
                                    className="inline-flex h-11 items-center gap-2 rounded-md bg-zinc-950 px-4 text-sm font-semibold text-white hover:bg-zinc-800"
                                >
                                    تجاری‌سازی
                                    <ArrowLeft className="size-4" />
                                </Link>
                                <Link
                                    href={`/scan/${demoQrCode}`}
                                    className="inline-flex h-11 items-center gap-2 rounded-md border border-zinc-300 px-4 text-sm font-semibold hover:bg-zinc-50"
                                >
                                    QR دمو
                                    <QrCode className="size-4" />
                                </Link>
                            </div>
                        </div>
                        <Form
                            action="/marketing/leads"
                            method="post"
                            options={{ preserveScroll: true }}
                            className="rounded-lg border border-zinc-200 bg-stone-50 p-5"
                        >
                            {({ errors, processing, recentlySuccessful }) => (
                                <div className="grid gap-3">
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
                                    <div className="grid gap-1.5">
                                        <label
                                            htmlFor="audience_type"
                                            className="text-sm font-medium"
                                        >
                                            نوع همکاری
                                        </label>
                                        <select
                                            id="audience_type"
                                            name="audience_type"
                                            defaultValue={focus.audienceType}
                                            className="h-11 rounded-md border border-zinc-300 bg-white px-3 text-sm"
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
                                            <p className="text-xs text-red-600">
                                                {errors.audience_type}
                                            </p>
                                        ) : null}
                                    </div>
                                    <div className="grid gap-3 sm:grid-cols-2">
                                        <div className="grid gap-1.5">
                                            <label
                                                htmlFor="contact_name"
                                                className="text-sm font-medium"
                                            >
                                                نام مسئول پیگیری
                                            </label>
                                            <input
                                                id="contact_name"
                                                name="contact_name"
                                                required
                                                className="h-11 rounded-md border border-zinc-300 bg-white px-3 text-sm"
                                                placeholder="مثلا مدیر بازاریابی"
                                            />
                                            {errors.contact_name ? (
                                                <p className="text-xs text-red-600">
                                                    {errors.contact_name}
                                                </p>
                                            ) : null}
                                        </div>
                                        <div className="grid gap-1.5">
                                            <label
                                                htmlFor="mobile"
                                                className="text-sm font-medium"
                                            >
                                                شماره تماس
                                            </label>
                                            <input
                                                id="mobile"
                                                name="mobile"
                                                required
                                                dir="ltr"
                                                className="h-11 rounded-md border border-zinc-300 bg-white px-3 text-sm"
                                                placeholder="0912..."
                                            />
                                            {errors.mobile ? (
                                                <p className="text-xs text-red-600">
                                                    {errors.mobile}
                                                </p>
                                            ) : null}
                                        </div>
                                    </div>
                                    <div className="grid gap-3 sm:grid-cols-2">
                                        <input
                                            name="organization_name"
                                            className="h-11 rounded-md border border-zinc-300 bg-white px-3 text-sm"
                                            placeholder="نام مجموعه یا برند"
                                        />
                                        <input
                                            name="city"
                                            className="h-11 rounded-md border border-zinc-300 bg-white px-3 text-sm"
                                            placeholder="شهر"
                                        />
                                    </div>
                                    <input
                                        name="project_hint"
                                        className="h-11 rounded-md border border-zinc-300 bg-white px-3 text-sm"
                                        placeholder="نمونه پروژه: برج، پارک، مرکز خرید، فودکورت..."
                                    />
                                    <textarea
                                        name="notes"
                                        rows={3}
                                        className="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm"
                                        placeholder="اگر هدف خاصی دارید بنویسید: جذب بازدید، اسپانسر، فروش واحدها، SEO یا اجرای پایلوت..."
                                    />
                                    {flash?.success || recentlySuccessful ? (
                                        <p className="rounded-md bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
                                            {flash?.success ??
                                                'درخواست ثبت شد.'}
                                        </p>
                                    ) : null}
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="inline-flex h-11 items-center justify-center gap-2 rounded-md bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-60"
                                    >
                                        ثبت درخواست دمو
                                        <ArrowLeft className="size-4" />
                                    </button>
                                </div>
                            )}
                        </Form>
                    </div>
                </section>
            </main>
        </>
    );
}
