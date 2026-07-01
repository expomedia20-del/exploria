import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    BadgeCheck,
    CheckCircle2,
    ChevronLeft,
    Gift,
    Lock,
    MapPin,
    Medal,
    QrCode,
    ShieldCheck,
    Sparkles,
    Trophy,
} from 'lucide-react';
import { useMemo, useState } from 'react';

type Mission = {
    id: string;
    title: string;
    place: string;
    layer: string;
    points: number;
    reward: string;
    status: 'ready' | 'locked';
};

const missions: Mission[] = [
    {
        id: 'gate',
        title: 'ورود از QR در دروازه اصلی',
        place: 'اکوپارک عباس آباد',
        layer: 'لایه ۱: ورود و شناسایی',
        points: 120,
        reward: 'نشان ورود پایلوت',
        status: 'ready',
    },
    {
        id: 'map',
        title: 'کشف نقطه راهنمای مسیر',
        place: 'محدوده ورودی اصلی',
        layer: 'لایه ۲: کشف و حرکت',
        points: 180,
        reward: 'کوپن نوشیدنی کوچک',
        status: 'ready',
    },
    {
        id: 'story',
        title: 'مشاهده روایت مکان',
        place: 'هاب تجربه شهری',
        layer: 'لایه ۳: محتوا و روایت',
        points: 220,
        reward: 'باز شدن ماموریت ویژه',
        status: 'ready',
    },
    {
        id: 'challenge',
        title: 'چالش عکس و ثبت خاطره',
        place: 'نقطه تعامل کمپین',
        layer: 'لایه ۴: مشارکت و پاداش',
        points: 260,
        reward: 'قرعه کشی جایزه پایلوت',
        status: 'locked',
    },
];

const levels = [
    { title: 'بازدیدکننده', threshold: 0 },
    { title: 'کاوشگر', threshold: 250 },
    { title: 'سفیر محلی', threshold: 520 },
    { title: 'قهرمان تجربه', threshold: 780 },
];

const layers = [
    'ورود و شناسایی',
    'کشف و حرکت',
    'محتوا و روایت',
    'مشارکت و پاداش',
    'بازگشت و وفاداری',
];

function getLevel(points: number) {
    return levels.reduce(
        (current, level) => (points >= level.threshold ? level : current),
        levels[0],
    );
}

export default function MissionsDemo() {
    const [completed, setCompleted] = useState<string[]>([]);

    const points = useMemo(
        () =>
            missions
                .filter((mission) => completed.includes(mission.id))
                .reduce((total, mission) => total + mission.points, 0),
        [completed],
    );
    const currentLevel = getLevel(points);
    const unlockedChallenge = points >= 520;
    const visibleMissions: Mission[] = missions.map((mission) => ({
        ...mission,
        status:
            mission.id === 'challenge' && !unlockedChallenge
                ? 'locked'
                : 'ready',
    }));
    const rewards = visibleMissions.filter((mission) =>
        completed.includes(mission.id),
    );

    function completeMission(mission: Mission) {
        if (completed.includes(mission.id)) {
            return;
        }

        if (mission.id === 'challenge' && !unlockedChallenge) {
            return;
        }

        setCompleted((items) => [...items, mission.id]);
    }

    return (
        <>
            <Head title="دموی مأموریت‌ها و پاداش‌ها" />
            <main dir="rtl" className="min-h-screen bg-slate-950 text-white">
                <section className="border-b border-white/10">
                    <div className="mx-auto max-w-7xl px-5 py-5 sm:px-8 lg:px-10">
                        <div className="flex flex-wrap items-center justify-between gap-3">
                            <Link
                                href="/demo"
                                className="inline-flex items-center gap-2 text-sm text-slate-300"
                            >
                                <ArrowLeft className="size-4" />
                                بازگشت به دمو
                            </Link>
                            <Link
                                href="/scan/ep1405-a7f3k9m2q8x4"
                                className="inline-flex h-9 items-center gap-2 rounded-md bg-white px-3 text-sm font-medium text-slate-950"
                            >
                                <QrCode className="size-4" />
                                اجرای مسیر واقعی QR
                            </Link>
                        </div>
                    </div>
                </section>

                <section className="mx-auto grid max-w-7xl gap-6 px-5 py-8 sm:px-8 lg:grid-cols-[0.9fr_1.1fr] lg:px-10">
                    <div>
                        <p className="text-sm font-medium text-emerald-300">
                            شبیه ساز چرخه تعامل
                        </p>
                        <h1 className="mt-3 text-4xl leading-tight font-semibold">
                            ماموریت، امتیاز، پاداش و باز شدن لایه های بعدی
                        </h1>
                        <p className="mt-4 max-w-2xl text-sm leading-8 text-slate-300">
                            این صفحه، مسیر پیشنهادی تجربه کاربر در پایلوت را به
                            شکل قابل کلیک نشان می دهد: هر ماموریت تکمیل می شود،
                            امتیاز و پاداش ثبت می شود، سطح کاربر بالا می رود و
                            ماموریت های عمیق تر فعال می شوند.
                        </p>

                        <div className="mt-6 grid gap-3 sm:grid-cols-3">
                            <div className="rounded-lg border border-white/10 bg-white/5 p-4">
                                <p className="text-sm text-slate-400">
                                    امتیاز کاربر
                                </p>
                                <p className="mt-2 text-3xl font-semibold">
                                    {points.toLocaleString('fa-IR')}
                                </p>
                            </div>
                            <div className="rounded-lg border border-white/10 bg-white/5 p-4">
                                <p className="text-sm text-slate-400">
                                    سطح فعلی
                                </p>
                                <p className="mt-2 text-xl font-semibold">
                                    {currentLevel.title}
                                </p>
                            </div>
                            <div className="rounded-lg border border-white/10 bg-white/5 p-4">
                                <p className="text-sm text-slate-400">
                                    پاداش ها
                                </p>
                                <p className="mt-2 text-3xl font-semibold">
                                    {rewards.length.toLocaleString('fa-IR')}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="rounded-lg border border-white/10 bg-white/5 p-5">
                        <div className="flex items-center gap-3">
                            <Trophy className="size-6 text-amber-300" />
                            <div>
                                <p className="font-semibold">
                                    نردبان پیشرفت کاربر
                                </p>
                                <p className="text-sm text-slate-400">
                                    سطح با امتیازهای واقعی ماموریت تغییر می کند.
                                </p>
                            </div>
                        </div>
                        <div className="mt-5 grid gap-3">
                            {levels.map((level) => {
                                const active = points >= level.threshold;

                                return (
                                    <div
                                        key={level.title}
                                        className={`flex items-center justify-between rounded-md border px-4 py-3 ${
                                            active
                                                ? 'border-emerald-300/40 bg-emerald-400/10'
                                                : 'border-white/10 bg-slate-900'
                                        }`}
                                    >
                                        <span className="font-medium">
                                            {level.title}
                                        </span>
                                        <span className="text-sm text-slate-300">
                                            {level.threshold.toLocaleString(
                                                'fa-IR',
                                            )}{' '}
                                            امتیاز
                                        </span>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </section>

                <section className="mx-auto grid max-w-7xl gap-5 px-5 pb-8 sm:px-8 lg:grid-cols-[1.2fr_0.8fr] lg:px-10">
                    <div className="rounded-lg border border-white/10 bg-white p-4 text-slate-950">
                        <div className="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-4">
                            <div>
                                <p className="text-sm text-slate-500">
                                    برد ماموریت های بازدیدکننده
                                </p>
                                <h2 className="mt-1 text-xl font-semibold">
                                    مسیر پایلوت عباس آباد
                                </h2>
                            </div>
                            <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium">
                                {completed.length.toLocaleString('fa-IR')} از{' '}
                                {missions.length.toLocaleString('fa-IR')}
                            </span>
                        </div>

                        <div className="mt-4 grid gap-3">
                            {visibleMissions.map((mission) => {
                                const done = completed.includes(mission.id);
                                const locked = mission.status === 'locked';

                                return (
                                    <article
                                        key={mission.id}
                                        className={`grid gap-4 rounded-lg border p-4 md:grid-cols-[1fr_auto] md:items-center ${
                                            done
                                                ? 'border-emerald-200 bg-emerald-50'
                                                : locked
                                                  ? 'border-slate-200 bg-slate-50 text-slate-500'
                                                  : 'border-slate-200 bg-white'
                                        }`}
                                    >
                                        <div>
                                            <div className="flex flex-wrap items-center gap-2">
                                                {done ? (
                                                    <CheckCircle2 className="size-5 text-emerald-600" />
                                                ) : locked ? (
                                                    <Lock className="size-5 text-slate-400" />
                                                ) : (
                                                    <Sparkles className="size-5 text-sky-600" />
                                                )}
                                                <h3 className="font-semibold">
                                                    {mission.title}
                                                </h3>
                                            </div>
                                            <div className="mt-3 flex flex-wrap gap-2 text-xs">
                                                <span className="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1">
                                                    <MapPin className="size-3" />
                                                    {mission.place}
                                                </span>
                                                <span className="rounded-full bg-slate-100 px-2.5 py-1">
                                                    {mission.layer}
                                                </span>
                                                <span className="rounded-full bg-amber-100 px-2.5 py-1 text-amber-900">
                                                    {mission.points.toLocaleString(
                                                        'fa-IR',
                                                    )}{' '}
                                                    امتیاز
                                                </span>
                                            </div>
                                            <p className="mt-3 text-sm text-slate-600">
                                                پاداش: {mission.reward}
                                            </p>
                                        </div>

                                        <button
                                            type="button"
                                            disabled={done || locked}
                                            onClick={() =>
                                                completeMission(mission)
                                            }
                                            className="inline-flex h-10 items-center justify-center gap-2 rounded-md bg-slate-950 px-4 text-sm font-medium text-white disabled:bg-slate-200 disabled:text-slate-500"
                                        >
                                            {done
                                                ? 'انجام شد'
                                                : locked
                                                  ? 'قفل است'
                                                  : 'ثبت انجام ماموریت'}
                                            {!done && !locked ? (
                                                <ChevronLeft className="size-4" />
                                            ) : null}
                                        </button>
                                    </article>
                                );
                            })}
                        </div>
                    </div>

                    <aside className="grid gap-5">
                        <section className="rounded-lg border border-white/10 bg-white/5 p-5">
                            <div className="flex items-center gap-3">
                                <Gift className="size-5 text-rose-300" />
                                <h2 className="font-semibold">کیف پاداش</h2>
                            </div>
                            {rewards.length === 0 ? (
                                <p className="mt-4 text-sm leading-7 text-slate-300">
                                    پس از تکمیل ماموریت ها، پاداش ها در همین بخش
                                    دیده می شوند.
                                </p>
                            ) : (
                                <div className="mt-4 grid gap-2">
                                    {rewards.map((reward) => (
                                        <div
                                            key={reward.id}
                                            className="rounded-md border border-white/10 bg-slate-900 px-3 py-3"
                                        >
                                            <p className="text-sm font-medium">
                                                {reward.reward}
                                            </p>
                                            <p className="mt-1 text-xs text-slate-400">
                                                {reward.title}
                                            </p>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </section>

                        <section className="rounded-lg border border-white/10 bg-white/5 p-5">
                            <div className="flex items-center gap-3">
                                <Medal className="size-5 text-amber-300" />
                                <h2 className="font-semibold">
                                    لایه های تجربه
                                </h2>
                            </div>
                            <div className="mt-4 grid gap-2">
                                {layers.map((layer, index) => {
                                    const active = index <= completed.length;

                                    return (
                                        <div
                                            key={layer}
                                            className={`flex items-center gap-2 rounded-md px-3 py-2 text-sm ${
                                                active
                                                    ? 'bg-white text-slate-950'
                                                    : 'bg-slate-900 text-slate-500'
                                            }`}
                                        >
                                            {active ? (
                                                <BadgeCheck className="size-4 text-emerald-600" />
                                            ) : (
                                                <Lock className="size-4" />
                                            )}
                                            {layer}
                                        </div>
                                    );
                                })}
                            </div>
                        </section>

                        <section className="rounded-lg border border-emerald-300/20 bg-emerald-400/10 p-5">
                            <div className="flex items-center gap-3">
                                <ShieldCheck className="size-5 text-emerald-300" />
                                <h2 className="font-semibold">
                                    قابل اتصال به نسخه واقعی
                                </h2>
                            </div>
                            <p className="mt-3 text-sm leading-7 text-emerald-50">
                                همین منطق می تواند به جدول های mission، reward،
                                wallet، level و campaign وصل شود تا تکمیل
                                ماموریت ها از QRهای واقعی، NFC یا check-in مکانی
                                ثبت شود.
                            </p>
                        </section>
                    </aside>
                </section>
            </main>
        </>
    );
}
