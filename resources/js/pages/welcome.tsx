import { Head, Link, usePage } from '@inertiajs/react';
import {
    ArrowLeft,
    BadgeDollarSign,
    BarChart3,
    Building2,
    CheckCircle2,
    Gift,
    Handshake,
    LayoutDashboard,
    MapPinned,
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
const internalRoles = ['admin', 'operator', 'viewer'];

type SharedProps = {
    auth?: {
        user?: {
            role?: string;
        } | null;
    };
};

function roleAwareHref(path: string, role?: string) {
    if (!role || internalRoles.includes(role)) {
        return path;
    }

    return {
        visitor: '/participant/dashboard',
        shop_partner: '/partner/dashboard',
        sponsor: '/sponsor/dashboard',
        hub_manager: '/ravaq/dashboard',
    }[role] ?? '/dashboard';
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
    ['۳', 'تعامل تجاری', 'به واحد عضو، فروشگاه، رستوران یا پیشنهاد اسپانسر وصل می‌شود.'],
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
    ['مدیریت کمپین', 'ثبت، ساخت، انتخاب الگو و نقشه عملیات کمپین.', LayoutDashboard],
    ['QR و ورود', 'کدهای ورودی، نقاط تماس، رضایت‌نامه و ثبت بازدید.', QrCode],
    ['ماموریت و پاداش', 'تعریف ماموریت، گنج، امتیاز، کوپن، هدیه و مصرف پاداش.', Gift],
    ['تبلیغات و نمایشگر', 'تبلیغ مستقل، زمان‌بندی نمایشگر و کنترل محتوای میدانی.', MonitorPlay],
    ['پنل‌های نقش‌محور', 'ادمین، مدیر مکان، رواق، فروشگاه، اسپانسر و مشارکت‌کننده.', ShieldCheck],
    ['داشبورد فروش', 'ROI، بسته قیمت، مدارک مذاکره و قیف تبدیل دمو به قرارداد.', BarChart3],
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

function HeroScene() {
    return (
        <div className="rounded-lg border border-white/12 bg-white/[0.06] p-3 shadow-2xl shadow-black/30 backdrop-blur-sm">
            <div className="rounded-md border border-white/10 bg-zinc-900 p-4">
                <div className="flex flex-wrap items-center justify-between gap-3 border-b border-white/10 pb-4">
                    <div>
                        <p className="text-xs text-zinc-400">نمونه دمو اکوپارک</p>
                        <p className="mt-1 font-mono text-xs text-zinc-200" dir="ltr">
                            {demoQrCode}
                        </p>
                    </div>
                    <span className="rounded-full bg-emerald-400 px-3 py-1 text-xs font-semibold text-zinc-950">
                        آماده اجرا
                    </span>
                </div>

                <div className="mt-4 grid gap-3 md:grid-cols-[1fr_0.82fr]">
                    <div className="rounded-md bg-white p-4 text-zinc-950">
                        <div className="flex items-center justify-between gap-2">
                            <strong>مسیر کمپین</strong>
                            <MapPinned className="size-5 text-emerald-700" />
                        </div>
                        <div className="mt-4 grid gap-2">
                            {['QR ورودی', 'ماموریت خانوادگی', 'گنج اسپانسری', 'مصرف پاداش'].map((item) => (
                                <div key={item} className="flex items-center justify-between rounded-md bg-zinc-100 p-2 text-sm">
                                    <span>{item}</span>
                                    <CheckCircle2 className="size-4 text-emerald-700" />
                                </div>
                            ))}
                        </div>
                    </div>

                    <div className="rounded-md border border-white/10 bg-zinc-950 p-4">
                        <div className="flex items-center justify-between gap-2">
                            <strong>گزارش فروش</strong>
                            <BarChart3 className="size-5 text-amber-300" />
                        </div>
                        <div className="mt-5 flex h-28 items-end gap-3">
                            {[64, 92, 48, 76, 58].map((height, index) => (
                                <span
                                    key={index}
                                    className="flex-1 rounded-t-md"
                                    style={{
                                        height: `${height}%`,
                                        backgroundColor: ['#10b981', '#06b6d4', '#f59e0b', '#f43f5e', '#8b5cf6'][index],
                                    }}
                                />
                            ))}
                        </div>
                        <p className="mt-4 text-xs leading-6 text-zinc-400">تعامل، مصرف کد، مراجعه و خروجی قابل ارائه به اسپانسر.</p>
                    </div>
                </div>

                <div className="mt-3 grid grid-cols-2 gap-3 text-sm text-white sm:grid-cols-4">
                    {[
                        ['مدیر مکان', 'نمای کل و ریسک'],
                        ['رواق / واحد', 'پیشنهاد و مصرف'],
                        ['اسپانسر', 'جایزه و ROI'],
                        ['بازدیدکننده', 'مسیر و پاداش'],
                    ].map(([title, body]) => (
                        <div key={title} className="rounded-md border border-white/10 bg-white/10 p-3">
                            <p className="font-semibold">{title}</p>
                            <p className="mt-1 text-xs text-zinc-400">{body}</p>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
}

export default function Welcome() {
    const { auth } = usePage<SharedProps>().props;
    const userRole = auth?.user?.role;
    const commercializationHref = roleAwareHref('/admin/commercialization', userRole);

    return (
        <>
            <Head title="اکسپلوریا | پلتفرم تجربه، کمپین و درآمد مکان" />

            <main dir="rtl" className="min-h-screen bg-stone-50 text-zinc-950">
                <section className="bg-zinc-950 text-white">
                    <div className="mx-auto flex min-h-[88vh] max-w-7xl flex-col px-5 py-5 sm:px-8 lg:px-10">
                        <header className="flex flex-wrap items-center justify-between gap-3">
                            <Link href="/" className="flex items-center gap-3">
                                <span className="inline-flex size-10 items-center justify-center rounded-md bg-emerald-500 text-zinc-950">
                                    <Sparkles className="size-5" />
                                </span>
                                <span>
                                    <span className="block text-lg font-semibold">EXPLORIA</span>
                                    <span className="block text-xs text-zinc-300">تجربه، کمپین، پاداش و گزارش</span>
                                </span>
                            </Link>
                            <nav className="flex flex-wrap gap-2 text-sm">
                                <Link href="/login" className="inline-flex h-10 items-center rounded-md border border-white/20 px-4 hover:bg-white/10">
                                    ورود مدیریتی
                                </Link>
                                <Link href={`/scan/${demoQrCode}`} className="inline-flex h-10 items-center gap-2 rounded-md bg-emerald-400 px-4 font-semibold text-zinc-950 hover:bg-emerald-300">
                                    شروع دمو
                                    <ArrowLeft className="size-4" />
                                </Link>
                            </nav>
                        </header>

                        <div className="grid flex-1 gap-10 py-12 lg:grid-cols-[0.92fr_1.08fr] lg:items-center">
                            <div className="order-2 lg:order-1">
                                <HeroScene />
                            </div>

                            <div className="order-1 lg:order-2">
                                <p className="inline-flex rounded-full border border-emerald-300/40 bg-emerald-300/10 px-4 py-2 text-sm text-emerald-200">
                                    آماده برای دمو، پایلوت و مذاکره تجاری
                                </p>
                                <h1 className="mt-6 max-w-3xl text-4xl font-semibold leading-tight sm:text-6xl">
                                    اکسپلوریا مکان را به تجربه، فروش و گزارش تبدیل می‌کند.
                                </h1>
                                <p className="mt-6 max-w-2xl text-base leading-8 text-zinc-300">
                                    از اسکن QR و ورود موبایلی تا ماموریت، گنج، پاداش، مصرف کد، تبلیغ اسپانسر و گزارش ROI؛
                                    یک چرخه اجرایی برای مکان‌های گردشگری، فرهنگی، تجاری و تفریحی.
                                </p>
                                <div className="mt-7 grid gap-2 sm:grid-cols-2">
                                    {['بازدیدکننده مسیر و پاداش می‌گیرد', 'واحد عضو پیشنهاد و مصرف را می‌بیند', 'اسپانسر جایزه و ROI دریافت می‌کند', 'مدیر مکان نمای کل اجرا را دارد'].map((item) => (
                                        <div key={item} className="flex items-center gap-2 rounded-md border border-white/10 bg-white/[0.04] px-3 py-2 text-sm text-zinc-200">
                                            <CheckCircle2 className="size-4 text-emerald-300" />
                                            <span>{item}</span>
                                        </div>
                                    ))}
                                </div>
                                <div className="mt-8 flex flex-wrap gap-3">
                                    <Link href={`/scan/${demoQrCode}`} className="inline-flex h-12 items-center gap-2 rounded-md bg-emerald-400 px-5 text-sm font-semibold text-zinc-950 hover:bg-emerald-300">
                                        شروع دموی اکوپارک
                                        <QrCode className="size-4" />
                                    </Link>
                                    <Link href={commercializationHref} className="inline-flex h-12 items-center gap-2 rounded-md bg-white px-5 text-sm font-semibold text-zinc-950 hover:bg-zinc-100">
                                        صفحه تجاری‌سازی
                                        <BadgeDollarSign className="size-4" />
                                    </Link>
                                    <Link href="/dashboard" className="inline-flex h-12 items-center gap-2 rounded-md border border-white/25 px-5 text-sm font-semibold hover:bg-white/10">
                                        داشبورد عملیاتی
                                        <BarChart3 className="size-4" />
                                    </Link>
                                </div>
                                <p className="mt-3 text-xs leading-6 text-zinc-400">
                                    صفحه تجاری‌سازی و چرخه دمو با حساب داخلی باز می‌شوند؛ حساب بازدیدکننده به تجربه QR و داشبورد خودش هدایت می‌شود.
                                </p>
                            </div>
                        </div>

                        <div className="grid gap-3 pb-4 sm:grid-cols-2 lg:grid-cols-4">
                            {valueLoop.map(([number, title, body]) => (
                                <article key={title} className="rounded-lg border border-white/10 bg-white/10 p-4 backdrop-blur-sm">
                                    <span className="text-sm text-emerald-300">{number}</span>
                                    <h2 className="mt-2 font-semibold">{title}</h2>
                                    <p className="mt-2 text-sm leading-6 text-zinc-300">{body}</p>
                                </article>
                            ))}
                        </div>
                    </div>
                </section>

                <section className="mx-auto max-w-7xl px-5 py-8 sm:px-8 lg:px-10">
                    <div className="grid gap-4 md:grid-cols-3">
                        {primaryRoutes.map((route) => (
                            <Link key={route.title} href={roleAwareHref(route.href, userRole)} className={`rounded-lg border p-5 transition hover:-translate-y-0.5 hover:shadow-md ${route.tone}`}>
                                <route.icon className="size-6" />
                                <h2 className="mt-4 text-lg font-semibold">{route.title}</h2>
                                <p className="mt-2 text-sm leading-7 opacity-80">{route.body}</p>
                            </Link>
                        ))}
                    </div>
                </section>

                <section className="bg-white">
                    <div className="mx-auto max-w-7xl px-5 py-10 sm:px-8 lg:px-10">
                        <div className="grid gap-5 lg:grid-cols-[0.8fr_1.2fr] lg:items-end">
                            <div>
                                <p className="text-sm font-medium text-emerald-700">معماری محصول</p>
                                <h2 className="mt-2 text-3xl font-semibold">یک پلتفرم، چند نقش، یک چرخه درآمدی</h2>
                            </div>
                            <p className="text-sm leading-7 text-zinc-600">
                                اکسپلوریا برای هر نقش، سطح دسترسی و خروجی خودش را جدا می‌کند؛ مدیر مکان فقط نمای کل و ریسک‌ها را می‌بیند، رواق هماهنگی محدوده را مدیریت می‌کند، فروشگاه پیشنهاد و مصرف کد را می‌بیند، و اسپانسر گزارش تعامل برند را دریافت می‌کند.
                            </p>
                        </div>

                        <div className="mt-6 grid gap-4 lg:grid-cols-4">
                            {audiences.map((item) => (
                                <article key={item.title} className="rounded-lg border border-zinc-200 bg-stone-50 p-5">
                                    <item.icon className="size-6 text-emerald-700" />
                                    <h3 className="mt-4 text-lg font-semibold">{item.title}</h3>
                                    <p className="mt-2 text-sm leading-7 text-zinc-600">{item.body}</p>
                                    <div className="mt-4 flex flex-wrap gap-2">
                                        {item.items.map((tag) => (
                                            <span key={tag} className="rounded-full bg-white px-3 py-1 text-xs text-zinc-700">
                                                {tag}
                                            </span>
                                        ))}
                                    </div>
                                </article>
                            ))}
                        </div>
                    </div>
                </section>

                <section className="mx-auto max-w-7xl px-5 py-10 sm:px-8 lg:px-10">
                    <div className="grid gap-7 lg:grid-cols-[0.82fr_1.18fr] lg:items-center">
                        <div>
                            <p className="text-sm font-medium text-cyan-700">قابلیت‌ها</p>
                            <h2 className="mt-2 text-3xl font-semibold">از دمو تا اجرای واقعی</h2>
                            <p className="mt-3 text-sm leading-7 text-zinc-600">
                                صفحه ورودی باید نشان دهد که اکسپلوریا فقط یک بازی یا یک پنل نیست؛ یک جریان کامل اجرایی و تجاری است که می‌تواند برای مکان، واحد عضو و اسپانسر عدد بسازد.
                            </p>
                        </div>
                        <div className="grid gap-3 md:grid-cols-2">
                            {capabilities.map(([title, body, Icon]) => (
                                <article key={title} className="rounded-lg border border-zinc-200 bg-white p-4">
                                    <Icon className="size-5 text-cyan-700" />
                                    <h3 className="mt-3 font-semibold">{title}</h3>
                                    <p className="mt-2 text-sm leading-7 text-zinc-600">{body}</p>
                                </article>
                            ))}
                        </div>
                    </div>
                </section>

                <section className="bg-zinc-950 text-white">
                    <div className="mx-auto max-w-7xl px-5 py-10 sm:px-8 lg:px-10">
                        <div className="grid gap-7 lg:grid-cols-[0.74fr_1.26fr] lg:items-center">
                            <div>
                                <p className="text-sm font-medium text-amber-300">مدل درآمدی</p>
                                <h2 className="mt-2 text-3xl font-semibold">سه پیشنهاد ساده برای شروع فروش</h2>
                                <p className="mt-3 text-sm leading-7 text-zinc-300">
                                    برای مذاکره واقعی، همه قابلیت‌ها یکجا فروخته نمی‌شوند. محصول به سه بسته قابل فهم تبدیل شده است.
                                </p>
                            </div>
                            <div className="grid gap-3 md:grid-cols-3">
                                {revenuePacks.map((pack) => (
                                    <article key={pack.title} className="flex min-h-44 flex-col rounded-lg border border-white/10 bg-white/10 p-4">
                                        <TicketCheck className="size-5 text-amber-300" />
                                        <h3 className="mt-4 font-semibold">{pack.title}</h3>
                                        <p className="mt-2 text-sm leading-6 text-zinc-300">{pack.buyer}</p>
                                        <p className="mt-auto rounded-md bg-amber-300 px-3 py-2 text-sm font-semibold text-zinc-950">
                                            {pack.price}
                                        </p>
                                    </article>
                                ))}
                            </div>
                        </div>
                    </div>
                </section>

                <section className="bg-white">
                    <div className="mx-auto grid max-w-7xl gap-5 px-5 py-9 sm:px-8 lg:grid-cols-[1fr_auto] lg:items-center lg:px-10">
                        <div>
                            <p className="text-sm font-medium text-emerald-700">قدم بعدی</p>
                            <h2 className="mt-2 text-3xl font-semibold">اکسپلوریا آماده نمایش، پایلوت و مذاکره است.</h2>
                            <p className="mt-3 text-sm leading-7 text-zinc-600">
                                برای جلسه فروش با حساب داخلی از صفحه تجاری‌سازی شروع کنید؛ برای تست تجربه کاربر، QR دمو را باز کنید.
                            </p>
                        </div>
                        <div className="flex flex-wrap gap-3">
                            <Link href={commercializationHref} className="inline-flex h-11 items-center gap-2 rounded-md bg-zinc-950 px-4 text-sm font-semibold text-white hover:bg-zinc-800">
                                تجاری‌سازی
                                <ArrowLeft className="size-4" />
                            </Link>
                            <Link href={`/scan/${demoQrCode}`} className="inline-flex h-11 items-center gap-2 rounded-md border border-zinc-300 px-4 text-sm font-semibold hover:bg-zinc-50">
                                QR دمو
                                <QrCode className="size-4" />
                            </Link>
                        </div>
                    </div>
                </section>
            </main>
        </>
    );
}
