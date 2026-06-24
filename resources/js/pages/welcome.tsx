import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    BarChart3,
    Building2,
    CheckCircle2,
    ClipboardCheck,
    Fingerprint,
    Layers3,
    LayoutDashboard,
    MapPin,
    Medal,
    QrCode,
    RadioTower,
    ShieldCheck,
    Smartphone,
} from 'lucide-react';
import { dashboard } from '@/routes';

const demoQrCode = 'ep1405-a7f3k9m2q8x4';

const liveLinks = [
    {
        title: 'ورود رسمی هیئت‌مدیره',
        description: 'ورود شکیل به نسخه جلسه، نقش مدیریتی و مسیر کامل محصول',
        href: '/board',
        icon: Building2,
        tone: 'bg-slate-50 text-slate-950 border-slate-200',
    },
    {
        title: 'شروع تجربه بازدیدکننده',
        description: 'اسکن QR، ورود موبایلی، رضایت نامه و ثبت بازدید',
        href: `/scan/${demoQrCode}`,
        icon: Smartphone,
        tone: 'bg-emerald-50 text-emerald-900 border-emerald-200',
    },
    {
        title: 'ماموریت و پاداش',
        description: 'تکمیل ماموریت، امتیاز، پاداش، سطح و لایه های بعدی',
        href: '/demo/missions',
        icon: Medal,
        tone: 'bg-rose-50 text-rose-950 border-rose-200',
    },
    {
        title: 'پوشش پروپوزال',
        description:
            'تطبیق دمو با سفر، کمپین، touchpoint، KPI و مدل ارزش اقتصادی',
        href: '/demo/proposal',
        icon: Layers3,
        tone: 'bg-violet-50 text-violet-950 border-violet-200',
    },
    {
        title: 'داشبورد عملیاتی',
        description: 'آمار مکان ها، QR فعال، رضایت ها و بازدیدهای ثبت شده',
        href: dashboard(),
        icon: LayoutDashboard,
        tone: 'bg-sky-50 text-sky-900 border-sky-200',
    },
    {
        title: 'مدیریت QR',
        description: 'رجیستری کدها، وضعیت، مکان، نقطه تماس و کمپین',
        href: '/admin/qr-codes',
        icon: QrCode,
        tone: 'bg-amber-50 text-amber-950 border-amber-200',
    },
];

const modules = [
    {
        title: 'QR Landing',
        body: 'ورود از کد نصب شده در مکان پایلوت و نمایش زمینه بازدید.',
        icon: QrCode,
    },
    {
        title: 'Mobile OTP',
        body: 'ورود سریع با موبایل و کد آزمایشی محلی برای دمو.',
        icon: Fingerprint,
    },
    {
        title: 'Consent Gate',
        body: 'ثبت رضایت نامه فارسی قبل از ایجاد رخداد بازدید.',
        icon: ClipboardCheck,
    },
    {
        title: 'Visit Experience',
        body: 'صفحه نتیجه بعد از ثبت بازدید تایید شده از مسیر QR.',
        icon: CheckCircle2,
    },
    {
        title: 'Operations Dashboard',
        body: 'شاخص های واقعی از دیتابیس محلی، نه محتوای ثابت نمایشی.',
        icon: BarChart3,
    },
    {
        title: 'QR Registry',
        body: 'کنترل وضعیت کد، اتصال به مکان، touchpoint و campaign.',
        icon: RadioTower,
    },
];

const flow = ['QR', 'OTP', 'رضایت نامه', 'ثبت بازدید', 'داشبورد'];

function QrVisual() {
    const cells = [
        1, 1, 1, 0, 1, 0, 1, 1, 1, 0, 1, 0, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, 1, 1,
        0, 0, 1, 0, 1, 0, 0, 1, 1, 0, 0, 1, 1, 1, 0, 0, 0, 1, 1, 0, 0, 1, 1, 0,
        1, 0, 1, 1, 0, 0, 1, 1, 1, 1, 0, 0, 1, 1, 0, 1,
    ];

    return (
        <div
            aria-hidden="true"
            className="grid size-28 grid-cols-8 gap-1 rounded-lg bg-white p-3 shadow-sm"
        >
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

export default function Welcome() {
    return (
        <>
            <Head title="دموی کارفرمایی اکسپلوریا" />
            <main
                dir="rtl"
                className="min-h-screen bg-stone-50 text-slate-950 dark:bg-slate-950 dark:text-slate-50"
            >
                <section className="bg-slate-950 text-white">
                    <div className="mx-auto flex min-h-[84vh] max-w-7xl flex-col justify-between px-5 py-5 sm:px-8 lg:px-10">
                        <header className="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p className="text-sm text-slate-300">
                                    EXPLORIA
                                </p>
                                <p className="mt-1 text-lg font-semibold">
                                    پایلوت اکوپارک عباس آباد
                                </p>
                            </div>
                            <nav className="flex flex-wrap gap-2 text-sm">
                                <Link
                                    href="/board"
                                    className="inline-flex h-9 items-center gap-2 rounded-md bg-white px-3 text-slate-950"
                                >
                                    <Building2 className="size-4" />
                                    ورود جلسه
                                </Link>
                                <Link
                                    href="/admin/qr-codes"
                                    className="inline-flex h-9 items-center gap-2 rounded-md border border-white/20 px-3 text-white hover:bg-white/10"
                                >
                                    <QrCode className="size-4" />
                                    QR
                                </Link>
                            </nav>
                        </header>

                        <div className="grid gap-8 py-10 lg:grid-cols-[1.05fr_0.95fr] lg:items-center">
                            <div>
                                <p className="text-sm font-medium text-emerald-300">
                                    دموی قابل ارائه به کارفرما
                                </p>
                                <h1 className="mt-4 max-w-3xl text-4xl leading-tight font-semibold sm:text-5xl">
                                    مسیر کامل بازدیدکننده، از اسکن QR تا داشبورد
                                    عملیات
                                </h1>
                                <p className="mt-5 max-w-2xl text-base leading-8 text-slate-300">
                                    این نسخه، حلقه اصلی محصول را زنده نشان می
                                    دهد: ورود از QR، احراز سریع موبایلی، رضایت
                                    نامه فارسی، ثبت بازدید و مشاهده خروجی در پنل
                                    مدیریتی.
                                </p>
                                <div className="mt-7 flex flex-wrap gap-3">
                                    <Link
                                        href="/board"
                                        className="inline-flex h-11 items-center gap-2 rounded-md bg-emerald-400 px-4 text-sm font-semibold text-slate-950 hover:bg-emerald-300"
                                    >
                                        ورود رسمی هیئت‌مدیره
                                        <ArrowLeft className="size-4" />
                                    </Link>
                                    <Link
                                        href={dashboard()}
                                        className="inline-flex h-11 items-center gap-2 rounded-md border border-white/20 px-4 text-sm font-semibold text-white hover:bg-white/10"
                                    >
                                        مشاهده داشبورد
                                    </Link>
                                </div>
                            </div>

                            <div className="rounded-lg border border-white/15 bg-white/5 p-4">
                                <div className="grid gap-4 sm:grid-cols-[auto_1fr] sm:items-center">
                                    <QrVisual />
                                    <div>
                                        <p className="text-sm text-slate-300">
                                            کد دمو
                                        </p>
                                        <p
                                            className="mt-1 font-mono text-lg"
                                            dir="ltr"
                                        >
                                            {demoQrCode}
                                        </p>
                                        <p className="mt-3 flex items-center gap-2 text-sm text-emerald-300">
                                            <MapPin className="size-4" />
                                            متصل به اکوپارک عباس آباد
                                        </p>
                                    </div>
                                </div>
                                <div className="mt-6 grid gap-2 sm:grid-cols-5">
                                    {flow.map((item, index) => (
                                        <div
                                            key={item}
                                            className="rounded-md border border-white/10 bg-slate-900 px-3 py-3 text-center text-xs text-slate-200"
                                        >
                                            <span className="block text-emerald-300">
                                                {(index + 1).toLocaleString(
                                                    'fa-IR',
                                                )}
                                            </span>
                                            {item}
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>

                        <div className="grid gap-3 pb-4 sm:grid-cols-2 lg:grid-cols-6">
                            {liveLinks.map((item) => (
                                <Link
                                    key={item.title}
                                    href={item.href}
                                    className={`rounded-lg border p-4 transition hover:-translate-y-0.5 ${item.tone}`}
                                >
                                    <item.icon className="size-5" />
                                    <p className="mt-3 font-semibold">
                                        {item.title}
                                    </p>
                                    <p className="mt-2 text-sm leading-6 opacity-80">
                                        {item.description}
                                    </p>
                                </Link>
                            ))}
                        </div>
                    </div>
                </section>

                <section className="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                    <div className="mx-auto grid max-w-7xl gap-4 px-5 py-8 sm:grid-cols-4 sm:px-8 lg:px-10">
                        {[
                            ['۳', 'مکان پایلوت'],
                            ['۱', 'QR فعال دمو'],
                            ['OTP', 'ورود سریع محلی'],
                            ['RTL', 'رابط فارسی'],
                        ].map(([value, label]) => (
                            <div
                                key={label}
                                className="rounded-lg border border-slate-200 p-4 dark:border-slate-800"
                            >
                                <p className="text-2xl font-semibold">
                                    {value}
                                </p>
                                <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                    {label}
                                </p>
                            </div>
                        ))}
                    </div>
                </section>

                <section className="mx-auto max-w-7xl px-5 py-10 sm:px-8 lg:px-10">
                    <div className="flex flex-col gap-2">
                        <p className="text-sm font-medium text-emerald-700 dark:text-emerald-300">
                            بخش های قابل نمایش
                        </p>
                        <h2 className="text-2xl font-semibold">
                            همه اجزای آماده شده برای ارائه
                        </h2>
                    </div>

                    <div className="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        {modules.map((module) => (
                            <article
                                key={module.title}
                                className="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900"
                            >
                                <module.icon className="size-5 text-slate-600 dark:text-slate-300" />
                                <h3 className="mt-4 font-semibold">
                                    {module.title}
                                </h3>
                                <p className="mt-2 text-sm leading-7 text-slate-600 dark:text-slate-300">
                                    {module.body}
                                </p>
                            </article>
                        ))}
                    </div>
                </section>

                <section className="bg-white dark:bg-slate-900">
                    <div className="mx-auto grid max-w-7xl gap-6 px-5 py-10 sm:px-8 lg:grid-cols-[0.8fr_1.2fr] lg:px-10">
                        <div>
                            <p className="text-sm font-medium text-sky-700 dark:text-sky-300">
                                آمادگی پایلوت
                            </p>
                            <h2 className="mt-2 text-2xl font-semibold">
                                پیام های اصلی برای جلسه
                            </h2>
                        </div>
                        <div className="grid gap-3">
                            {[
                                'این نسخه دمو/پایلوت محلی است و هنوز محیط production نیست.',
                                'مسیر ارزش اصلی محصول اکنون زنده و قابل اجراست.',
                                'داشبورد و رجیستری QR به داده های دیتابیس محلی وصل هستند.',
                                'گام بعدی برای پایلوت واقعی، چاپ QR و اتصال OTP واقعی در staging است.',
                            ].map((item) => (
                                <div
                                    key={item}
                                    className="flex gap-3 rounded-lg border border-slate-200 p-4 dark:border-slate-800"
                                >
                                    <ShieldCheck className="mt-0.5 size-5 shrink-0 text-emerald-600" />
                                    <p className="text-sm leading-7 text-slate-700 dark:text-slate-200">
                                        {item}
                                    </p>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>
            </main>
        </>
    );
}
