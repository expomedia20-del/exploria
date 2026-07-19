import { Head, Link } from '@inertiajs/react';
import {
    ArrowRight,
    BadgeCheck,
    CheckCircle2,
    Compass,
    Gift,
    Home,
    MapPin,
    QrCode,
    Sparkles,
    Trophy,
    WalletCards,
} from 'lucide-react';
import { useMemo, useState } from 'react';

type StartMode = 'home' | 'onsite';
type NodeId =
    | 'gate'
    | 'hologram'
    | 'ravaq'
    | 'food'
    | 'mina'
    | 'ocean'
    | 'final';

type TreasureNode = {
    id: NodeId;
    title: string;
    place: string;
    clue: string;
    mission: string;
    reward: string;
    points: number;
    x: string;
    y: string;
    accent: string;
};

const nodes: TreasureNode[] = [
    {
        id: 'gate',
        title: 'دروازه اکوپارک',
        place: 'نقطه ورود',
        clue: 'مسیر شما از نخستین پیام کمپین یا کد QR آغاز می‌شود.',
        mission: 'یک مسیر انتخاب کنید و اولین نشان دیجیتال را بگیرید.',
        reward: 'نشان آغاز + کد ورود سریع',
        points: 60,
        x: '12%',
        y: '74%',
        accent: 'bg-emerald-400',
    },
    {
        id: 'hologram',
        title: 'کوله‌پشتی هولوگرام',
        place: 'نقطه تعامل نمایشگر سیار',
        clue: 'نمایشگر سیار هنگام حضور بازدیدکننده نخستین سرنخ زنده را نشان می‌دهد.',
        mission: 'کد QR محیط را اسکن کنید یا با کد شروع از خانه ادامه دهید.',
        reward: 'امتیاز خوش‌آمدگویی + بازشدن مسیر',
        points: 80,
        x: '25%',
        y: '48%',
        accent: 'bg-cyan-300',
    },
    {
        id: 'ravaq',
        title: 'رواق تجاری',
        place: 'فروشگاه‌ها و واحدهای فرهنگی',
        clue: 'سرنخ بعدی در پیشنهاد یکی از واحدهای عضو پنهان شده است.',
        mission: 'از یک واحد عضو بازدید و کوپن آن را دریافت یا ذخیره کنید.',
        reward: 'کوپن فروشگاه + امتیاز وفاداری',
        points: 140,
        x: '43%',
        y: '66%',
        accent: 'bg-amber-300',
    },
    {
        id: 'food',
        title: 'گردش طعم',
        place: 'باغ غذا و کافه‌ها',
        clue: 'ثبت رأی برای یک طعم، بخش بعدی مسیر را باز می‌کند.',
        mission: 'یک ایستگاه خوراک انتخاب کنید، رأی دهید و نشان طعم بگیرید.',
        reward: 'پذیرایی کوچک + شانس سبد نقره‌ای',
        points: 130,
        x: '57%',
        y: '38%',
        accent: 'bg-rose-300',
    },
    {
        id: 'mina',
        title: 'گنبد مینا',
        place: 'هاب علم و یادگیری',
        clue: 'سرنخ ستاره پس از پاسخ به یک پرسش کوتاه آموزشی باز می‌شود.',
        mission: 'به یک پرسش علمی کوتاه پاسخ دهید و نشان ستاره را باز کنید.',
        reward: 'نشان یادگیری + امتیاز خانوادگی',
        points: 170,
        x: '72%',
        y: '56%',
        accent: 'bg-violet-300',
    },
    {
        id: 'ocean',
        title: 'مسیر خانوادگی پارک آب‌وآتش',
        place: 'مسیر کشف خانوادگی',
        clue: 'گذرنامه خانوادگی پیش از گنج نهایی به یک نشان دیگر نیاز دارد.',
        mission: 'یک ایستگاه خانوادگی یا خاطره تصویری را کامل کنید.',
        reward: 'گذرنامه خانوادگی + شانس سبد طلایی',
        points: 220,
        x: '84%',
        y: '27%',
        accent: 'bg-sky-300',
    },
    {
        id: 'final',
        title: 'گنج نهایی',
        place: 'خزانه پاداش کمپین',
        clue: 'کد نهایی از مسیر شما ساخته می‌شود و در بازدید واقعی قابل ادامه است.',
        mission: 'نشان‌های کافی جمع کنید و کد ادامه مسیر را بگیرید.',
        reward: 'سبد طلایی یا ورود به قرعه‌کشی اسپانسر',
        points: 260,
        x: '91%',
        y: '78%',
        accent: 'bg-yellow-300',
    },
];

const baskets = [
    { level: 'آغاز', items: ['نشان دیجیتال', 'کد مسیر', 'امتیاز اولیه'] },
    {
        level: 'نقره‌ای',
        items: ['کوپن فروشگاه', 'پاداش طعم', 'شانس قرعه‌کشی کوچک'],
    },
    {
        level: 'طلایی',
        items: ['سبد رواق و خوراک', 'دعوت خانوادگی', 'امتیاز دوبرابر حضوری'],
    },
    {
        level: 'ویژه',
        items: ['جایزه اسپانسر', 'بسته بازدید ویژه', 'نشان برنده عمومی'],
    },
];

function formatFa(value: number) {
    return value.toLocaleString('fa-IR');
}

function buildCode(mode: StartMode, completed: NodeId[]) {
    const prefix = mode === 'home' ? 'HOME' : 'PARK';
    const score = completed.length * 17 + (mode === 'home' ? 41 : 64);

    return `EXP-${prefix}-1405-${score}`;
}

export default function EcoParkTreasureGame() {
    const [mode, setMode] = useState<StartMode>('home');
    const [selected, setSelected] = useState<NodeId>('gate');
    const [completed, setCompleted] = useState<NodeId[]>([]);

    const selectedNode = nodes.find((node) => node.id === selected) ?? nodes[0];
    const points = useMemo(
        () =>
            nodes
                .filter((node) => completed.includes(node.id))
                .reduce((sum, node) => sum + node.points, 0),
        [completed],
    );
    const code = buildCode(mode, completed);
    const progress = Math.round((completed.length / nodes.length) * 100);

    function completeNode(id: NodeId) {
        setSelected(id);
        setCompleted((items) => (items.includes(id) ? items : [...items, id]));
    }

    return (
        <>
            <Head title="بازی نقشه گنج اکوپارک" />
            <main className="min-h-screen bg-[#f6f7f2] text-zinc-950" dir="rtl">
                <section className="border-b border-zinc-200 bg-white/90 backdrop-blur">
                    <div className="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-5 py-4 sm:px-8 lg:px-10">
                        <Link
                            href="/admin/mission-blueprints"
                            className="inline-flex items-center gap-2 text-sm font-medium text-zinc-600 hover:text-zinc-950"
                        >
                            <ArrowRight className="size-4" />
                            گنجینه الگوهای مأموریت
                        </Link>
                        <Link
                            href="/dashboard"
                            className="inline-flex h-9 items-center gap-2 rounded-md border border-zinc-200 bg-zinc-50 px-3 text-sm text-zinc-700 hover:bg-zinc-100"
                        >
                            <Compass className="size-4" />
                            داشبورد
                        </Link>
                    </div>
                </section>

                <section className="mx-auto grid max-w-7xl gap-6 px-5 py-7 sm:px-8 lg:grid-cols-[0.82fr_1.18fr] lg:px-10">
                    <div className="flex flex-col justify-between gap-6">
                        <div className="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm">
                            <div className="relative min-h-[260px]">
                                <img
                                    src="/images/ecopark/hero.webp"
                                    alt="نمای کمپین اکوپارک"
                                    className="absolute inset-0 h-full w-full object-cover"
                                />
                                <div className="absolute inset-0 bg-gradient-to-l from-zinc-950/85 via-zinc-950/45 to-transparent" />
                                <div className="relative max-w-xl p-5 text-white sm:p-6">
                                    <p className="text-sm font-semibold text-emerald-200">
                                        بازی پایلوت اکسپلوریا
                                    </p>
                                    <h1 className="mt-3 text-4xl leading-tight font-semibold sm:text-5xl">
                                        نقشه گنج اکوپارک
                                    </h1>
                                    <p className="mt-4 text-sm leading-7 text-zinc-100">
                                        یک مسیر گنج قابل بازی برای اکوپارک؛ شامل
                                        شروع از خانه، سرنخ‌های QR در محل،
                                        کوله‌پشتی هولوگرام، پاداش واحدهای عضو و
                                        سبدهای جایزه.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div className="grid gap-3 sm:grid-cols-2">
                            <button
                                type="button"
                                onClick={() => setMode('home')}
                                className={`rounded-lg border p-4 text-right transition ${mode === 'home' ? 'border-emerald-300 bg-emerald-50 text-emerald-950 shadow-sm' : 'border-zinc-200 bg-white shadow-sm hover:bg-zinc-50'}`}
                            >
                                <Home className="size-5" />
                                <p className="mt-3 font-semibold">
                                    شروع از خانه
                                </p>
                                <p className="mt-1 text-xs opacity-80">
                                    پیش‌بازی، انتخاب مسیر، نخستین سرنخ و کد
                                    ادامه در محل.
                                </p>
                            </button>
                            <button
                                type="button"
                                onClick={() => setMode('onsite')}
                                className={`rounded-lg border p-4 text-right transition ${mode === 'onsite' ? 'border-cyan-300 bg-cyan-50 text-cyan-950 shadow-sm' : 'border-zinc-200 bg-white shadow-sm hover:bg-zinc-50'}`}
                            >
                                <QrCode className="size-5" />
                                <p className="mt-3 font-semibold">
                                    شروع در اکوپارک
                                </p>
                                <p className="mt-1 text-xs opacity-80">
                                    اسکن QR، نمایشگر محیطی، بازشدن مسیر و
                                    پاداش‌های زنده.
                                </p>
                            </button>
                        </div>

                        <div className="grid gap-3 sm:grid-cols-3">
                            <div className="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm">
                                <p className="text-xs text-zinc-500">پیشرفت</p>
                                <p className="mt-2 text-2xl font-semibold">
                                    {formatFa(progress)}%
                                </p>
                            </div>
                            <div className="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm">
                                <p className="text-xs text-zinc-500">امتیاز</p>
                                <p className="mt-2 text-2xl font-semibold">
                                    {formatFa(points)}
                                </p>
                            </div>
                            <div className="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm">
                                <p className="text-xs text-zinc-500">نشان‌ها</p>
                                <p className="mt-2 text-2xl font-semibold">
                                    {formatFa(completed.length)} /{' '}
                                    {formatFa(nodes.length)}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="relative min-h-[560px] overflow-hidden rounded-lg border border-zinc-200 bg-[#eef5ed] p-4 shadow-sm">
                        <img
                            src="/images/ecopark/treasure-route.webp"
                            alt="مسیر مفهومی گنج اکوپارک"
                            className="absolute inset-0 h-full w-full object-cover opacity-30"
                        />
                        <div className="absolute inset-0 bg-gradient-to-br from-white/80 via-[#eef5ed]/82 to-white/70" />
                        <svg
                            className="absolute inset-0 h-full w-full opacity-80"
                            viewBox="0 0 100 100"
                            preserveAspectRatio="none"
                            aria-hidden="true"
                        >
                            <path
                                d="M12 74 C24 48, 35 56, 43 66 S53 36, 57 38 S66 58, 72 56 S79 31, 84 27 S89 55, 91 78"
                                fill="none"
                                stroke="rgba(63,63,70,0.42)"
                                strokeWidth="0.7"
                                strokeDasharray="2 2"
                            />
                            <path
                                d="M8 88 C26 82, 34 88, 50 82 S73 84, 95 90"
                                fill="none"
                                stroke="rgba(16,185,129,0.16)"
                                strokeWidth="8"
                            />
                            <path
                                d="M4 28 C28 20, 42 22, 66 15 S84 16, 98 10"
                                fill="none"
                                stroke="rgba(14,165,233,0.14)"
                                strokeWidth="9"
                            />
                        </svg>

                        {nodes.map((node, index) => {
                            const done = completed.includes(node.id);
                            const active = selected === node.id;

                            return (
                                <button
                                    key={node.id}
                                    type="button"
                                    onClick={() => setSelected(node.id)}
                                    className={`absolute z-10 flex -translate-x-1/2 -translate-y-1/2 flex-col items-center gap-2 rounded-full p-1 transition ${active ? 'scale-110' : 'hover:scale-105'}`}
                                    style={{ left: node.x, top: node.y }}
                                >
                                    <span
                                        className={`flex size-12 items-center justify-center rounded-full border text-sm font-bold shadow-lg ${done ? 'border-emerald-200 bg-emerald-300 text-zinc-950' : active ? 'border-white bg-white text-zinc-950' : `border-white/30 ${node.accent} text-zinc-950`}`}
                                    >
                                        {done ? (
                                            <CheckCircle2 className="size-5" />
                                        ) : (
                                            formatFa(index + 1)
                                        )}
                                    </span>
                                    <span className="max-w-28 rounded-full border border-zinc-200 bg-white/95 px-2 py-1 text-center text-[11px] leading-4 font-medium text-zinc-950 shadow-sm backdrop-blur">
                                        {node.title}
                                    </span>
                                </button>
                            );
                        })}

                        <div className="absolute right-4 bottom-4 left-4 z-20 rounded-lg border border-zinc-200 bg-white/95 p-4 text-zinc-950 shadow-lg backdrop-blur">
                            <div className="grid gap-4 lg:grid-cols-[1fr_auto] lg:items-end">
                                <div>
                                    <div className="flex flex-wrap items-center gap-2">
                                        <MapPin className="size-4 text-emerald-300" />
                                        <p className="text-xs text-zinc-500">
                                            {selectedNode.place}
                                        </p>
                                    </div>
                                    <h2 className="mt-2 text-xl font-semibold text-zinc-950">
                                        {selectedNode.title}
                                    </h2>
                                    <p className="mt-2 text-sm leading-6 text-zinc-600">
                                        {selectedNode.clue}
                                    </p>
                                    <p className="mt-2 text-sm leading-6 text-zinc-900">
                                        مأموریت: {selectedNode.mission}
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    onClick={() =>
                                        completeNode(selectedNode.id)
                                    }
                                    className="inline-flex h-11 items-center justify-center gap-2 rounded-md bg-zinc-950 px-4 text-sm font-semibold text-white hover:bg-zinc-800"
                                >
                                    <BadgeCheck className="size-4" />
                                    دریافت سرنخ
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <section className="mx-auto grid max-w-7xl gap-5 px-5 pb-8 sm:px-8 lg:grid-cols-[1fr_0.9fr] lg:px-10">
                    <div className="rounded-lg border border-zinc-200 bg-white p-4 text-zinc-950 shadow-sm">
                        <div className="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-200 pb-4">
                            <div>
                                <p className="text-sm text-zinc-500">
                                    کد ادامه مسیر
                                </p>
                                <h2
                                    className="mt-1 font-mono text-2xl font-semibold"
                                    dir="ltr"
                                >
                                    {code}
                                </h2>
                            </div>
                            <span className="rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium">
                                {mode === 'home'
                                    ? 'شروع اختیاری از خانه'
                                    : 'شروع در محل'}
                            </span>
                        </div>
                        <div className="mt-4 grid gap-3 md:grid-cols-2">
                            {nodes
                                .filter((node) => completed.includes(node.id))
                                .map((node) => (
                                    <div
                                        key={node.id}
                                        className="rounded-lg border border-zinc-200 p-3"
                                    >
                                        <div className="flex items-center gap-2">
                                            <Gift className="size-4 text-emerald-700" />
                                            <p className="font-medium">
                                                {node.reward}
                                            </p>
                                        </div>
                                        <p className="mt-1 text-sm text-zinc-500">
                                            {node.title} ·{' '}
                                            {formatFa(node.points)} امتیاز
                                        </p>
                                    </div>
                                ))}
                            {completed.length === 0 && (
                                <p className="text-sm leading-7 text-zinc-500">
                                    پس از تکمیل نخستین سرنخ، پاداش‌های
                                    جمع‌آوری‌شده اینجا نمایش داده می‌شوند.
                                </p>
                            )}
                        </div>
                    </div>

                    <aside className="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm">
                        <div className="flex items-center gap-2">
                            <Trophy className="size-5 text-amber-600" />
                            <h2 className="font-semibold">
                                سطح‌بندی سبدهای جایزه
                            </h2>
                        </div>
                        <div className="mt-4 grid gap-2">
                            {baskets.map((basket, index) => {
                                const unlocked = completed.length >= index + 1;

                                return (
                                    <div
                                        key={basket.level}
                                        className={`rounded-md border p-3 ${unlocked ? 'border-amber-200 bg-amber-50' : 'border-zinc-200 bg-zinc-50'}`}
                                    >
                                        <div className="flex items-center justify-between gap-3">
                                            <p className="font-medium">
                                                {basket.level}
                                            </p>
                                            {unlocked ? (
                                                <WalletCards className="size-4 text-amber-700" />
                                            ) : (
                                                <Sparkles className="size-4 text-zinc-500" />
                                            )}
                                        </div>
                                        <div className="mt-2 flex flex-wrap gap-2">
                                            {basket.items.map((item) => (
                                                <span
                                                    key={item}
                                                    className="rounded-full bg-zinc-100 px-2 py-1 text-xs text-zinc-700"
                                                >
                                                    {item}
                                                </span>
                                            ))}
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </aside>
                </section>
            </main>
        </>
    );
}
