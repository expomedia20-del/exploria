import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    BadgeCheck,
    BarChart3,
    CircleDollarSign,
    Gift,
    Layers3,
    MapPinned,
    Megaphone,
    QrCode,
    RadioTower,
    Route,
    ShoppingBag,
    Sparkles,
    Store,
    Trophy,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';

const proposalSignals = [
    {
        slide: '۰۸',
        title: 'نقشه سفر بازدیدکننده',
        proposal: 'مواجهه اولیه، دعوت، اسکن، انتخاب مسیر، کشف، پاداش و بازگشت',
        demo: 'Demo Hub، مسیر QR و شبیه ساز ماموریت ها',
        href: '/demo/missions',
        status: 'پوشش زنده',
        icon: Route,
    },
    {
        slide: '۰۹ تا ۱۱',
        title: 'نقاط تماس و رسانه تعاملی',
        proposal: 'کوله پشتی، نمایشگر ثابت، QR Touchpoints و ماموریت محیطی',
        demo: 'رجیستری QR و روایت touchpointها؛ رسانه فیزیکی در این دمو مفهومی است',
        href: '/admin/qr-codes',
        status: 'پوشش حداقلی',
        icon: RadioTower,
    },
    {
        slide: '۱۲',
        title: 'کمپین کشف گنج های اکوپارک',
        proposal:
            'سرنخ، مسیر، صندوق گنج، ماموریت و پاداش در نقاط مختلف اکوپارک',
        demo: 'ماموریت ها، امتیاز، پاداش و باز شدن چالش قفل شده',
        href: '/demo/missions',
        status: 'پوشش زنده',
        icon: Trophy,
    },
    {
        slide: '۱۳',
        title: 'گیمیفیکیشن و انواع گنج',
        proposal: 'گنج ساده، خانوادگی، ویژه، مسابقه ای و قابل توسعه',
        demo: 'سطح کاربر، کیف پاداش، ماموریت قفل شده و لایه های بعدی تجربه',
        href: '/demo/missions',
        status: 'پوشش زنده',
        icon: Gift,
    },
    {
        slide: '۱۴ تا ۱۸',
        title: 'سناریوهای هاب و جاذبه ها',
        proposal: 'گنبد مینا، رواق تجاری، فودکورت، سینما، اسکیت و مسیر طبیعت',
        demo: 'به صورت سناریوی پایلوت و نقاط پیشنهادی در همین صفحه نمایش داده شده است',
        href: '#pilot-map',
        status: 'پوشش نمایشی',
        icon: MapPinned,
    },
    {
        slide: '۱۹ تا ۲۰',
        title: 'همکاری کسب و کارها و مدل ارزش اقتصادی',
        proposal:
            'توجه، اقدام، مراجعه، خرید، داده قابل تصمیم گیری و درآمدهای مکمل',
        demo: 'مدل ارزش اقتصادی و جایگاه شرکا به صورت board مدیریتی در همین صفحه دیده می شود',
        href: '#economic-model',
        status: 'پوشش نمایشی',
        icon: CircleDollarSign,
    },
    {
        slide: '۲۱',
        title: 'داشبورد مدیریتی و KPI',
        proposal:
            'پایش اسکن، مشارکت، ماموریت، فروش، بازگشت و شاخص های تصمیم گیری',
        demo: 'داشبورد عملیاتی فعلی با آمار واقعی دیتابیس محلی',
        href: '/dashboard',
        status: 'پوشش زنده',
        icon: BarChart3,
    },
];

const journey = [
    'مواجهه',
    'دعوت',
    'اسکن QR',
    'انتخاب مسیر',
    'کشف',
    'پاداش',
    'بازگشت',
];

const pilotPlaces = [
    ['دروازه اصلی', 'QR ورود و رضایت نامه'],
    ['گنبد مینا', 'ماموریت روایت و امتیاز'],
    ['دریاچه ملل', 'سرنخ مسیر و چالش عکس'],
    ['رواق و رستوران ها', 'مصرف پاداش و مراجعه'],
    ['باغ ایرانی', 'ماموریت خانوادگی'],
    ['پل طبیعت', 'بازگشت و وفاداری'],
];

const valueChain: Array<[string, string, LucideIcon]> = [
    ['توجه', 'رسانه و دعوت به کمپین', Megaphone],
    ['اقدام', 'اسکن، ورود و شروع ماموریت', QrCode],
    ['مراجعه', 'هدایت به هاب و کسب و کار', Store],
    ['خرید', 'پاداش و پیشنهاد قابل مصرف', ShoppingBag],
    ['داده', 'داشبورد و گزارش مدیریتی', BarChart3],
];

const nextMinimums = [
    'تعریف مدل داده ماموریت، پاداش، کیف پول و سطح کاربر',
    'اتصال touchpointهای واقعی به QRهای مستقل هر نقطه',
    'ثبت رویدادهای scan، mission_completed، reward_redeemed و merchant_referral',
    'ساخت داشبورد KPI مخصوص هیئت مدیره و گزارش اقتصادی پایلوت',
];

export default function ProposalCoverageDemo() {
    return (
        <>
            <Head title="پوشش پروپوزال اکوپارک" />
            <main dir="rtl" className="min-h-screen bg-stone-50 text-slate-950">
                <section className="bg-slate-950 text-white">
                    <div className="mx-auto max-w-7xl px-5 py-5 sm:px-8 lg:px-10">
                        <div className="flex flex-wrap items-center justify-between gap-3">
                            <Link
                                href="/demo"
                                className="inline-flex items-center gap-2 text-sm text-slate-300"
                            >
                                <ArrowLeft className="size-4" />
                                بازگشت به Demo Hub
                            </Link>
                            <Link
                                href="/demo/missions"
                                className="inline-flex h-9 items-center gap-2 rounded-md bg-white px-3 text-sm font-medium text-slate-950"
                            >
                                <Sparkles className="size-4" />
                                مشاهده ماموریت و پاداش
                            </Link>
                        </div>

                        <div className="grid gap-8 py-10 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
                            <div>
                                <p className="text-sm font-medium text-cyan-300">
                                    تطبیق با پروپوزال ۲۲ صفحه ای
                                </p>
                                <h1 className="mt-3 max-w-3xl text-4xl leading-tight font-semibold">
                                    حداقل دمو برای جلسه هیئت مدیره اکوپارک عباس
                                    آباد
                                </h1>
                                <p className="mt-4 max-w-2xl text-sm leading-8 text-slate-300">
                                    این صفحه نشان می دهد بخش های کلیدی پروپوزال
                                    در دمو کجا دیده می شوند: سفر بازدیدکننده،
                                    کمپین کشف گنج، touchpointها، کسب و کارها،
                                    مدل ارزش اقتصادی و داشبورد KPI.
                                </p>
                            </div>

                            <div className="rounded-lg border border-white/10 bg-white/5 p-5">
                                <div className="flex items-center gap-3">
                                    <Layers3 className="size-6 text-cyan-300" />
                                    <div>
                                        <p className="font-semibold">
                                            وضعیت پوشش پیشنهادی
                                        </p>
                                        <p className="text-sm text-slate-400">
                                            زنده، نمایشی یا مرحله بعد پایلوت
                                        </p>
                                    </div>
                                </div>
                                <div className="mt-5 grid gap-3 sm:grid-cols-3">
                                    {[
                                        ['۴', 'بخش زنده'],
                                        ['۳', 'بخش نمایشی'],
                                        ['۴', 'حداقل بعدی'],
                                    ].map(([value, label]) => (
                                        <div
                                            key={label}
                                            className="rounded-md border border-white/10 bg-slate-900 p-4"
                                        >
                                            <p className="text-2xl font-semibold">
                                                {value}
                                            </p>
                                            <p className="mt-1 text-sm text-slate-400">
                                                {label}
                                            </p>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section className="mx-auto max-w-7xl px-5 py-10 sm:px-8 lg:px-10">
                    <div>
                        <p className="text-sm font-medium text-cyan-700">
                            Coverage Matrix
                        </p>
                        <h2 className="mt-2 text-2xl font-semibold">
                            ردیابی اسلایدهای کلیدی تا صفحات دمو
                        </h2>
                    </div>

                    <div className="mt-6 grid gap-4">
                        {proposalSignals.map((item) => (
                            <Link
                                key={item.title}
                                href={item.href}
                                className="grid gap-4 rounded-lg border border-slate-200 bg-white p-4 transition hover:border-cyan-300 md:grid-cols-[auto_0.8fr_1fr_auto] md:items-center"
                            >
                                <div className="flex size-12 items-center justify-center rounded-md bg-slate-950 text-white">
                                    <item.icon className="size-5" />
                                </div>
                                <div>
                                    <p className="text-xs text-slate-500">
                                        اسلاید {item.slide}
                                    </p>
                                    <h3 className="mt-1 font-semibold">
                                        {item.title}
                                    </h3>
                                </div>
                                <div className="grid gap-2 text-sm leading-6 text-slate-600">
                                    <p>پروپوزال: {item.proposal}</p>
                                    <p>دمو: {item.demo}</p>
                                </div>
                                <span className="rounded-full bg-cyan-50 px-3 py-1 text-xs font-medium text-cyan-800">
                                    {item.status}
                                </span>
                            </Link>
                        ))}
                    </div>
                </section>

                <section
                    className="border-y border-slate-200 bg-white"
                    id="pilot-map"
                >
                    <div className="mx-auto max-w-7xl px-5 py-10 sm:px-8 lg:px-10">
                        <div className="grid gap-6 lg:grid-cols-[0.85fr_1.15fr]">
                            <div>
                                <p className="text-sm font-medium text-emerald-700">
                                    Pilot Journey
                                </p>
                                <h2 className="mt-2 text-2xl font-semibold">
                                    نقشه سفر پیشنهادی بازدیدکننده
                                </h2>
                                <div className="mt-5 grid gap-2">
                                    {journey.map((step, index) => (
                                        <div
                                            key={step}
                                            className="flex items-center gap-3 rounded-md border border-slate-200 p-3"
                                        >
                                            <span className="flex size-8 items-center justify-center rounded-full bg-slate-950 text-sm text-white">
                                                {(index + 1).toLocaleString(
                                                    'fa-IR',
                                                )}
                                            </span>
                                            <span className="font-medium">
                                                {step}
                                            </span>
                                        </div>
                                    ))}
                                </div>
                            </div>

                            <div className="rounded-lg border border-slate-200 bg-stone-50 p-5">
                                <div className="flex items-center gap-3">
                                    <MapPinned className="size-5 text-emerald-700" />
                                    <h3 className="font-semibold">
                                        نقاط قابل نمایش در روایت پایلوت
                                    </h3>
                                </div>
                                <div className="mt-5 grid gap-3 sm:grid-cols-2">
                                    {pilotPlaces.map(([place, use]) => (
                                        <div
                                            key={place}
                                            className="rounded-md bg-white p-4 shadow-sm"
                                        >
                                            <p className="font-semibold">
                                                {place}
                                            </p>
                                            <p className="mt-2 text-sm leading-6 text-slate-600">
                                                {use}
                                            </p>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section
                    className="mx-auto max-w-7xl px-5 py-10 sm:px-8 lg:px-10"
                    id="economic-model"
                >
                    <div className="grid gap-6 lg:grid-cols-[1fr_1fr]">
                        <div>
                            <p className="text-sm font-medium text-violet-700">
                                Economic Value
                            </p>
                            <h2 className="mt-2 text-2xl font-semibold">
                                مدل ارزش اقتصادی در حد دمو
                            </h2>
                            <p className="mt-4 text-sm leading-8 text-slate-600">
                                مطابق پروپوزال، ارزش از توجه شروع می شود و با
                                اقدام، مراجعه، خرید و داده قابل تصمیم گیری کامل
                                می شود. دمو فعلی این زنجیره را به صورت نمایشی و
                                قابل توضیح برای جلسه پوشش می دهد.
                            </p>
                        </div>

                        <div className="grid gap-3">
                            {valueChain.map(([title, body, Icon]) => (
                                <div
                                    key={title as string}
                                    className="flex gap-3 rounded-lg border border-slate-200 bg-white p-4"
                                >
                                    <Icon className="mt-1 size-5 shrink-0 text-violet-700" />
                                    <div>
                                        <p className="font-semibold">{title}</p>
                                        <p className="mt-1 text-sm leading-6 text-slate-600">
                                            {body}
                                        </p>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                <section className="bg-slate-950 text-white">
                    <div className="mx-auto grid max-w-7xl gap-6 px-5 py-10 sm:px-8 lg:grid-cols-[0.8fr_1.2fr] lg:px-10">
                        <div>
                            <p className="text-sm font-medium text-amber-300">
                                Pilot Minimums
                            </p>
                            <h2 className="mt-2 text-2xl font-semibold">
                                حداقل موارد بعدی برای نزدیک شدن به اجرای واقعی
                            </h2>
                        </div>
                        <div className="grid gap-3">
                            {nextMinimums.map((item) => (
                                <div
                                    key={item}
                                    className="flex gap-3 rounded-lg border border-white/10 bg-white/5 p-4"
                                >
                                    <BadgeCheck className="mt-0.5 size-5 shrink-0 text-emerald-300" />
                                    <p className="text-sm leading-7 text-slate-200">
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
