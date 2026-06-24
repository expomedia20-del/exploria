import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    BarChart3,
    Building2,
    CheckCircle2,
    Crown,
    Gift,
    LogIn,
    MapPinned,
    QrCode,
    Route,
    ShieldCheck,
    Smartphone,
    Sparkles,
    Store,
} from 'lucide-react';

const demoQrCode = 'ep1405-a7f3k9m2q8x4';

const entryActions = [
    {
        title: 'ورود هیئت‌مدیره',
        description: 'نمای محصول، پوشش پروپوزال، مدل ارزش و مسیر پایلوت',
        href: '/demo/proposal',
        icon: Crown,
        tone: 'border-emerald-200 bg-emerald-50 text-emerald-950',
    },
    {
        title: 'ورود بازدیدکننده',
        description: 'احراز موبایل، رضایت‌نامه، ثبت بازدید و ادامه تجربه',
        href: `/access?sourceQrCode=${demoQrCode}`,
        icon: Smartphone,
        tone: 'border-sky-200 bg-sky-50 text-sky-950',
    },
    {
        title: 'شروع از QR واقعی',
        description: 'همان مسیری که پس از اسکن کد محیطی دیده می‌شود',
        href: `/scan/${demoQrCode}`,
        icon: QrCode,
        tone: 'border-amber-200 bg-amber-50 text-amber-950',
    },
];

const productLayers = [
    {
        title: 'سفر بازدیدکننده',
        body: 'از مواجهه با QR تا انتخاب مسیر، رضایت‌نامه، ثبت بازدید و ادامه تجربه.',
        href: `/scan/${demoQrCode}`,
        icon: Route,
    },
    {
        title: 'ماموریت و پاداش',
        body: 'نمایش زنده امتیاز، پاداش، سطح کاربر و باز شدن لایه‌های بعدی.',
        href: '/demo/missions',
        icon: Gift,
    },
    {
        title: 'داشبورد مدیریتی',
        body: 'شاخص‌های عملیاتی، بازدیدهای ثبت‌شده و داده‌های قابل تصمیم‌گیری.',
        href: '/dashboard',
        icon: BarChart3,
    },
    {
        title: 'مدیریت نقاط تماس',
        body: 'رجیستری QR، وضعیت کدها، اتصال به مکان و کمپین پایلوت.',
        href: '/admin/qr-codes',
        icon: MapPinned,
    },
];

const boardChecks = [
    'ورود جلسه از یک صفحه رسمی و قابل ارائه آغاز می‌شود.',
    'دمو فقط صفحه داخلی نیست؛ نقش بازدیدکننده و نقش مدیریتی کنار هم دیده می‌شوند.',
    'حداقل‌های پروپوزال شامل مسیر، کمپین گنج، پاداش، KPI و QR قابل نمایش است.',
    'برای اجرای حضوری، موبایل نمونه 09120000000 و کد محلی 123456 آماده است.',
];

function QrMark() {
    const cells = [
        1, 1, 1, 0, 1, 0, 1, 1, 1, 0, 0, 1, 1, 0, 1, 0, 1, 1, 1, 1, 0, 1, 0, 1,
        0, 1, 0, 0, 1, 1, 1, 0, 1, 0, 1, 1, 0, 0, 1, 1, 1, 0, 0, 1, 1, 0, 1, 0,
        0, 1, 1, 0, 1, 0, 1, 1, 1, 1, 0, 1, 0, 0, 1, 1,
    ];

    return (
        <div className="grid size-32 grid-cols-8 gap-1 rounded-lg bg-white p-3 shadow-sm">
            {cells.map((cell, index) => (
                <span
                    key={index}
                    className={
                        cell
                            ? 'rounded-sm bg-slate-950'
                            : 'rounded-sm bg-slate-100'
                    }
                />
            ))}
        </div>
    );
}

export default function BoardEntry() {
    return (
        <>
            <Head title="ورود رسمی هیئت‌مدیره اکسپلوریا" />
            <main dir="rtl" className="min-h-screen bg-stone-50 text-slate-950">
                <section className="bg-slate-950 text-white">
                    <div className="mx-auto flex min-h-[88vh] max-w-7xl flex-col px-5 py-5 sm:px-8 lg:px-10">
                        <header className="flex flex-wrap items-center justify-between gap-3">
                            <Link href="/" className="flex items-center gap-3">
                                <span className="flex size-10 items-center justify-center rounded-md bg-emerald-400 text-slate-950">
                                    <Sparkles className="size-5" />
                                </span>
                                <span>
                                    <span className="block text-sm text-slate-300">
                                        EXPLORIA
                                    </span>
                                    <span className="block font-semibold">
                                        اکوپارک عباس‌آباد
                                    </span>
                                </span>
                            </Link>
                            <nav className="flex flex-wrap gap-2 text-sm">
                                <Link
                                    href="/demo/proposal"
                                    className="inline-flex h-9 items-center gap-2 rounded-md bg-white px-3 text-slate-950"
                                >
                                    <Building2 className="size-4" />
                                    نمای جلسه
                                </Link>
                                <Link
                                    href="/demo/missions"
                                    className="inline-flex h-9 items-center gap-2 rounded-md border border-white/20 px-3 text-white hover:bg-white/10"
                                >
                                    <Gift className="size-4" />
                                    ماموریت‌ها
                                </Link>
                            </nav>
                        </header>

                        <div className="grid flex-1 gap-8 py-10 lg:grid-cols-[1.05fr_0.95fr] lg:items-center">
                            <div>
                                <div className="inline-flex items-center gap-2 rounded-md border border-emerald-300/40 px-3 py-1 text-sm text-emerald-200">
                                    <ShieldCheck className="size-4" />
                                    نسخه نمایشی قابل ارائه به هیئت‌مدیره
                                </div>
                                <h1 className="mt-5 max-w-3xl text-4xl leading-tight font-semibold sm:text-5xl">
                                    ورود به سایت اصلی اکسپلوریا برای پایلوت
                                    اکوپارک
                                </h1>
                                <p className="mt-5 max-w-2xl text-base leading-8 text-slate-300">
                                    این صفحه ورودی جلسه است؛ از اینجا هیئت‌مدیره
                                    می‌تواند محصول را مثل یک سایت کامل ببیند:
                                    مسیر کاربر، ماموریت‌ها، پاداش، نقاط QR،
                                    داشبورد و پوشش پروپوزال.
                                </p>
                                <div className="mt-7 flex flex-wrap gap-3">
                                    <Link
                                        href="/demo/proposal"
                                        className="inline-flex h-11 items-center gap-2 rounded-md bg-emerald-400 px-4 text-sm font-semibold text-slate-950 hover:bg-emerald-300"
                                    >
                                        ورود به نسخه جلسه
                                        <ArrowLeft className="size-4" />
                                    </Link>
                                    <Link
                                        href={`/access?sourceQrCode=${demoQrCode}`}
                                        className="inline-flex h-11 items-center gap-2 rounded-md border border-white/20 px-4 text-sm font-semibold text-white hover:bg-white/10"
                                    >
                                        ورود موبایلی دمو
                                        <LogIn className="size-4" />
                                    </Link>
                                </div>
                            </div>

                            <div className="rounded-lg border border-white/15 bg-white/5 p-4">
                                <div className="grid gap-5 sm:grid-cols-[auto_1fr] sm:items-center">
                                    <QrMark />
                                    <div>
                                        <p className="text-sm text-slate-300">
                                            کد پایلوت جلسه
                                        </p>
                                        <p
                                            className="mt-1 font-mono text-lg"
                                            dir="ltr"
                                        >
                                            {demoQrCode}
                                        </p>
                                        <p className="mt-4 flex items-center gap-2 text-sm text-emerald-300">
                                            <MapPinned className="size-4" />
                                            متصل به اکوپارک عباس‌آباد
                                        </p>
                                    </div>
                                </div>
                                <div className="mt-6 grid gap-3 rounded-md bg-slate-900 p-4 text-sm">
                                    <div className="flex items-center justify-between gap-4">
                                        <span className="text-slate-400">
                                            موبایل دمو
                                        </span>
                                        <span dir="ltr">09120000000</span>
                                    </div>
                                    <div className="flex items-center justify-between gap-4">
                                        <span className="text-slate-400">
                                            کد ورود محلی
                                        </span>
                                        <span dir="ltr">123456</span>
                                    </div>
                                    <div className="flex items-center justify-between gap-4">
                                        <span className="text-slate-400">
                                            نقش جلسه
                                        </span>
                                        <span>هیئت‌مدیره و بازدیدکننده</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section className="border-b border-slate-200 bg-white">
                    <div className="mx-auto grid max-w-7xl gap-3 px-5 py-5 sm:grid-cols-3 sm:px-8 lg:px-10">
                        {entryActions.map((action) => (
                            <Link
                                key={action.title}
                                href={action.href}
                                className={`rounded-lg border p-4 transition hover:-translate-y-0.5 ${action.tone}`}
                            >
                                <action.icon className="size-5" />
                                <p className="mt-3 font-semibold">
                                    {action.title}
                                </p>
                                <p className="mt-2 text-sm leading-6 opacity-80">
                                    {action.description}
                                </p>
                            </Link>
                        ))}
                    </div>
                </section>

                <section className="mx-auto max-w-7xl px-5 py-10 sm:px-8 lg:px-10">
                    <div className="flex flex-col gap-2">
                        <p className="text-sm font-medium text-emerald-700">
                            نمای محصول کامل
                        </p>
                        <h2 className="text-2xl font-semibold">
                            بخش‌هایی که در جلسه باید دیده شوند
                        </h2>
                    </div>
                    <div className="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        {productLayers.map((item) => (
                            <Link
                                key={item.title}
                                href={item.href}
                                className="rounded-lg border border-slate-200 bg-white p-5 transition hover:-translate-y-0.5 hover:border-slate-300"
                            >
                                <item.icon className="size-5 text-slate-600" />
                                <h3 className="mt-4 font-semibold">
                                    {item.title}
                                </h3>
                                <p className="mt-2 text-sm leading-7 text-slate-600">
                                    {item.body}
                                </p>
                            </Link>
                        ))}
                    </div>
                </section>

                <section className="bg-white">
                    <div className="mx-auto grid max-w-7xl gap-6 px-5 py-10 sm:px-8 lg:grid-cols-[0.85fr_1.15fr] lg:px-10">
                        <div>
                            <p className="text-sm font-medium text-sky-700">
                                کنترل ارائه
                            </p>
                            <h2 className="mt-2 text-2xl font-semibold">
                                این دمو برای جلسه چه چیزی را ثابت می‌کند؟
                            </h2>
                        </div>
                        <div className="grid gap-3">
                            {boardChecks.map((item) => (
                                <div
                                    key={item}
                                    className="flex gap-3 rounded-lg border border-slate-200 p-4"
                                >
                                    <CheckCircle2 className="mt-0.5 size-5 shrink-0 text-emerald-600" />
                                    <p className="text-sm leading-7 text-slate-700">
                                        {item}
                                    </p>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                <section className="mx-auto grid max-w-7xl gap-4 px-5 py-10 sm:px-8 md:grid-cols-3 lg:px-10">
                    {[
                        [
                            'پیشنهاد ارزش',
                            'تبدیل حضور در پارک به تعامل قابل سنجش',
                        ],
                        [
                            'همکاری شرکا',
                            'قابل اتصال به رستوران، فروشگاه و جاذبه‌ها',
                        ],
                        ['داده مدیریتی', 'مسیر تصمیم‌گیری برای پایلوت و توسعه'],
                    ].map(([title, body]) => (
                        <article
                            key={title}
                            className="rounded-lg border border-slate-200 bg-stone-50 p-5"
                        >
                            <Store className="size-5 text-amber-700" />
                            <h3 className="mt-4 font-semibold">{title}</h3>
                            <p className="mt-2 text-sm leading-7 text-slate-600">
                                {body}
                            </p>
                        </article>
                    ))}
                </section>

                <footer className="border-t border-slate-200 bg-white px-5 py-6 text-center text-sm text-slate-500">
                    EXPLORIA · نسخه نمایشی اکوپارک عباس‌آباد · آماده ارائه
                </footer>
            </main>
        </>
    );
}
