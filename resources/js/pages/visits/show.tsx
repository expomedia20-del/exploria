import { Form, Head, Link, usePage } from '@inertiajs/react';
import {
    CheckCircle2,
    Gift,
    Lock,
    MapPin,
    Play,
    Sparkles,
    Trophy,
} from 'lucide-react';
import { Button } from '@/components/ui/button';

type Visit = {
    id: string;
    status: string;
    occurredAt: string;
    qrCode: string | null;
    venueName: string | null;
    city: string | null;
    zoneName: string | null;
    hubName: string | null;
    touchpointLabel: string | null;
    campaignName: string | null;
    isDemo: boolean;
};

type Props = {
    visit: Visit;
    missionFlow: MissionFlow;
};

type MissionFlow = {
    stats: {
        totalPoints: number;
        completedMissions: number;
        availableMissions: number;
        rewards: number;
    };
    missions: MissionItem[];
    rewards: UserRewardItem[];
};

type MissionItem = {
    id: string;
    code: string;
    title: string;
    description: string | null;
    templateDescription: string | null;
    completionEvidence: string;
    successMessage: string | null;
    cycleStep: { index: number | null; label: string | null };
    status: 'available' | 'started' | 'completed' | 'locked';
    isLocked: boolean;
    canStart: boolean;
    canComplete: boolean;
    points: number;
    missionType: string;
    triggerType: string;
    hubName: string | null;
    touchpointLabel: string | null;
    treasureName: string | null;
    rewardCode: string | null;
    startedAt: string | null;
    completedAt: string | null;
};

type UserRewardItem = {
    id: string;
    status: string;
    awardedAt: string | null;
    expiresAt: string | null;
    redemption: {
        id: string;
        redemptionCode: string;
        status: string;
        redeemedAt: string | null;
        partnerName: string | null;
    } | null;
    reward: {
        id: string;
        code: string;
        name: string;
        rewardType: string;
        partnerName: string | null;
    } | null;
};

type SharedProps = {
    flash?: {
        success?: string;
    };
};

function formatDate(value: string) {
    return new Intl.DateTimeFormat('fa-IR', {
        dateStyle: 'full',
        timeStyle: 'short',
    }).format(new Date(value));
}

const missionStatusLabels: Record<MissionItem['status'], string> = {
    available: 'آماده شروع',
    started: 'در حال انجام',
    completed: 'تکمیل شده',
    locked: 'قفل',
};

const rewardStatusLabels: Record<string, string> = {
    awarded: 'صادر شده',
    reserved: 'رزرو شده',
    redeemed: 'تحویل شده',
    expired: 'منقضی شده',
};

const redemptionStatusLabels: Record<string, string> = {
    pending: 'در انتظار تحویل',
    confirmed: 'تحویل شده',
    redeemed: 'مصرف شده',
    expired: 'منقضی شده',
};

export default function VisitShow({ visit, missionFlow }: Props) {
    const { flash } = usePage<SharedProps>().props;
    const totalMissions = missionFlow.missions.length;
    const progress =
        totalMissions > 0
            ? Math.round(
                  (missionFlow.stats.completedMissions / totalMissions) * 100,
              )
            : 0;
    const nextMission =
        missionFlow.missions.find((mission) => mission.status === 'started') ??
        missionFlow.missions.find(
            (mission) => mission.status === 'available',
        ) ??
        missionFlow.missions.find((mission) => mission.status === 'locked') ??
        null;
    const allDone =
        totalMissions > 0 &&
        missionFlow.stats.completedMissions === totalMissions;

    return (
        <main
            dir="rtl"
            className="min-h-screen bg-slate-50 px-4 py-10 text-slate-950 dark:bg-slate-950 dark:text-slate-50"
        >
            <Head title={`بازدید ${visit.venueName ?? 'پایلوت'}`} />

            <section className="mx-auto w-full max-w-5xl rounded-lg border border-slate-200 bg-white p-6 shadow-sm sm:p-8 dark:border-slate-800 dark:bg-slate-900">
                <div className="flex flex-wrap items-center gap-2">
                    <span className="rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">
                        بازدید ثبت شد
                    </span>
                    {visit.isDemo && (
                        <span className="rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-800 dark:bg-amber-950 dark:text-amber-200">
                            داده آزمایشی
                        </span>
                    )}
                </div>

                <div className="mt-8 grid gap-8 lg:grid-cols-[0.9fr_1.1fr]">
                    <div>
                        <p className="text-sm text-slate-500">
                            تجربه پایلوت اکسپلوریا
                        </p>
                        <h1 className="mt-2 text-3xl font-bold">
                            {visit.venueName}
                        </h1>
                        <p className="mt-2 text-sm text-slate-600 dark:text-slate-300">
                            {visit.city} · {visit.campaignName}
                        </p>

                        <dl className="mt-8 grid gap-4 rounded-lg bg-slate-50 p-5 text-sm dark:bg-slate-950/60">
                            <div className="flex justify-between gap-4">
                                <dt className="text-slate-500">زمان ثبت</dt>
                                <dd className="font-medium">
                                    {formatDate(visit.occurredAt)}
                                </dd>
                            </div>
                            <div className="flex justify-between gap-4">
                                <dt className="text-slate-500">محدوده</dt>
                                <dd className="font-medium">
                                    {visit.zoneName}
                                </dd>
                            </div>
                            <div className="flex justify-between gap-4">
                                <dt className="text-slate-500">هاب</dt>
                                <dd className="font-medium">{visit.hubName}</dd>
                            </div>
                            <div className="flex justify-between gap-4">
                                <dt className="text-slate-500">نقطه تعامل</dt>
                                <dd className="font-medium">
                                    {visit.touchpointLabel}
                                </dd>
                            </div>
                            <div className="flex justify-between gap-4">
                                <dt className="text-slate-500">کد QR</dt>
                                <dd className="font-mono text-xs">
                                    {visit.qrCode}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div>
                        <div className="grid grid-cols-3 gap-3 text-sm">
                            <div className="rounded-lg border border-slate-200 p-3 dark:border-slate-800">
                                <p className="text-slate-500">امتیاز</p>
                                <p className="mt-1 text-2xl font-semibold">
                                    {missionFlow.stats.totalPoints.toLocaleString(
                                        'fa-IR',
                                    )}
                                </p>
                            </div>
                            <div className="rounded-lg border border-slate-200 p-3 dark:border-slate-800">
                                <p className="text-slate-500">تکمیل شده</p>
                                <p className="mt-1 text-2xl font-semibold">
                                    {missionFlow.stats.completedMissions.toLocaleString(
                                        'fa-IR',
                                    )}
                                </p>
                            </div>
                            <div className="rounded-lg border border-slate-200 p-3 dark:border-slate-800">
                                <p className="text-slate-500">پاداش</p>
                                <p className="mt-1 text-2xl font-semibold">
                                    {missionFlow.stats.rewards.toLocaleString(
                                        'fa-IR',
                                    )}
                                </p>
                            </div>
                        </div>

                        {flash?.success ? (
                            <div className="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-200">
                                {flash.success}
                            </div>
                        ) : null}

                        <div className="mt-4 rounded-lg border border-sky-200 bg-sky-50 p-5 text-sm leading-7 text-sky-950 dark:border-sky-900 dark:bg-sky-950/40 dark:text-sky-100">
                            <div className="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p className="font-semibold">
                                        {allDone
                                            ? 'مسیر این بازدید کامل شد'
                                            : nextMission
                                              ? 'قدم بعدی شما'
                                              : 'مسیر آماده‌سازی نشده است'}
                                    </p>
                                    <p className="mt-1 text-xs leading-6 text-sky-900/80 dark:text-sky-100/80">
                                        {allDone
                                            ? 'همه مأموریت‌های این کمپین را تکمیل کرده‌اید؛ کیف پاداش را بررسی کنید.'
                                            : nextMission
                                              ? nextMission.isLocked
                                                  ? 'برای باز شدن مأموریت بعدی، امتیاز یا شرط مرحله‌های قبل را کامل کنید.'
                                                  : `${nextMission.title} را انجام دهید و امتیاز آن را دریافت کنید.`
                                              : 'برای این بازدید هنوز مأموریتی ثبت نشده است.'}
                                    </p>
                                </div>
                                {nextMission ? (
                                    <span className="rounded-full bg-white px-3 py-1 text-xs font-medium text-sky-800 shadow-xs dark:bg-slate-900 dark:text-sky-100">
                                        {
                                            missionStatusLabels[
                                                nextMission.status
                                            ]
                                        }
                                    </span>
                                ) : null}
                            </div>
                            <div className="mt-4 h-2 overflow-hidden rounded-full bg-white/80 dark:bg-slate-900">
                                <div
                                    className="h-full rounded-full bg-sky-600"
                                    style={{ width: `${progress}%` }}
                                />
                            </div>
                            <p className="mt-2 text-xs text-sky-900/80 dark:text-sky-100/80">
                                {missionFlow.stats.completedMissions.toLocaleString(
                                    'fa-IR',
                                )}{' '}
                                از {totalMissions.toLocaleString('fa-IR')}{' '}
                                مأموریت تکمیل شده است.
                            </p>
                        </div>
                    </div>
                </div>

                <section className="mt-8">
                    <div className="flex items-center gap-2">
                        <Trophy className="size-5 text-amber-500" />
                        <h2 className="text-lg font-semibold">
                            ماموریت‌های این بازدید
                        </h2>
                    </div>
                    {missionFlow.missions.length === 0 ? (
                        <div className="mt-4 rounded-lg border border-dashed border-slate-300 p-5 text-sm leading-7 text-slate-600 dark:border-slate-700 dark:text-slate-300">
                            برای این بازدید هنوز مأموریتی تعریف نشده است. بعد از
                            تکمیل تنظیمات کمپین، مأموریت‌ها در همین بخش نمایش
                            داده می‌شوند.
                        </div>
                    ) : (
                        <div className="mt-4 grid gap-3">
                            {missionFlow.missions.map((mission) => (
                                <article
                                    key={mission.id}
                                    className="grid gap-4 rounded-lg border border-slate-200 p-4 md:grid-cols-[1fr_auto] md:items-center dark:border-slate-800"
                                >
                                    <div className="min-w-0">
                                        <div className="flex flex-wrap items-center gap-2">
                                            {mission.status === 'completed' ? (
                                                <CheckCircle2 className="size-5 text-emerald-600" />
                                            ) : mission.isLocked ? (
                                                <Lock className="size-5 text-slate-400" />
                                            ) : (
                                                <Sparkles className="size-5 text-sky-600" />
                                            )}
                                            <h3 className="font-semibold">
                                                {mission.title}
                                            </h3>
                                            <span className="rounded-full bg-slate-100 px-2.5 py-1 text-xs text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                                {missionStatusLabels[
                                                    mission.status
                                                ] ?? mission.status}
                                            </span>
                                        </div>
                                        <p className="mt-2 text-sm leading-7 text-slate-600 dark:text-slate-300">
                                            {mission.description}
                                        </p>
                                        {mission.templateDescription &&
                                        mission.templateDescription !==
                                            mission.description ? (
                                            <p className="mt-1 text-xs leading-6 text-slate-500 dark:text-slate-400">
                                                {mission.templateDescription}
                                            </p>
                                        ) : null}
                                        <div className="mt-3 grid gap-2 rounded-lg bg-slate-50 p-3 text-xs text-slate-600 sm:grid-cols-2 dark:bg-slate-950/60 dark:text-slate-300">
                                            <p>
                                                گام:{' '}
                                                <span className="font-medium text-slate-900 dark:text-slate-100">
                                                    {mission.cycleStep.label ??
                                                        'مسیر اصلی'}
                                                </span>
                                            </p>
                                            <p>
                                                مدرک انجام:{' '}
                                                <span className="font-medium text-slate-900 dark:text-slate-100">
                                                    {mission.completionEvidence}
                                                </span>
                                            </p>
                                            {mission.status === 'completed' &&
                                            mission.successMessage ? (
                                                <p className="sm:col-span-2">
                                                    {mission.successMessage}
                                                </p>
                                            ) : null}
                                        </div>
                                        <div className="mt-3 flex flex-wrap gap-2 text-xs">
                                            <span className="rounded-full bg-amber-100 px-2.5 py-1 text-amber-900">
                                                {mission.points.toLocaleString(
                                                    'fa-IR',
                                                )}{' '}
                                                امتیاز
                                            </span>
                                            <span className="rounded-full bg-slate-100 px-2.5 py-1 text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                                {mission.hubName ??
                                                    mission.touchpointLabel}
                                            </span>
                                            {mission.hubName ||
                                            mission.touchpointLabel ? (
                                                <span className="inline-flex items-center gap-1 rounded-full bg-sky-100 px-2.5 py-1 text-sky-900 dark:bg-sky-950 dark:text-sky-100">
                                                    <MapPin className="size-3" />
                                                    مسیر همین بازدید
                                                </span>
                                            ) : null}
                                            {mission.treasureName ? (
                                                <span className="rounded-full bg-rose-100 px-2.5 py-1 text-rose-900">
                                                    گنج: {mission.treasureName}
                                                </span>
                                            ) : null}
                                        </div>
                                    </div>

                                    <div className="flex gap-2">
                                        {mission.canStart ? (
                                            <Form
                                                action={`/visits/${visit.id}/missions/${mission.id}/start`}
                                                method="post"
                                            >
                                                {({ processing }) => (
                                                    <Button
                                                        disabled={processing}
                                                        variant="outline"
                                                    >
                                                        <Play className="size-4" />
                                                        شروع
                                                    </Button>
                                                )}
                                            </Form>
                                        ) : null}
                                        <Form
                                            action={`/visits/${visit.id}/missions/${mission.id}/complete`}
                                            method="post"
                                        >
                                            {({ processing }) => (
                                                <Button
                                                    disabled={
                                                        processing ||
                                                        !mission.canComplete
                                                    }
                                                >
                                                    <CheckCircle2 className="size-4" />
                                                    تکمیل
                                                </Button>
                                            )}
                                        </Form>
                                    </div>
                                </article>
                            ))}
                        </div>
                    )}
                </section>

                <section className="mt-8">
                    <div className="flex items-center gap-2">
                        <Gift className="size-5 text-rose-500" />
                        <h2 className="text-lg font-semibold">کیف پاداش</h2>
                    </div>
                    {missionFlow.rewards.length === 0 ? (
                        <p className="mt-3 rounded-lg border border-slate-200 p-4 text-sm text-slate-600 dark:border-slate-800 dark:text-slate-300">
                            هنوز پاداشی صادر نشده است.
                        </p>
                    ) : (
                        <div className="mt-4 grid gap-3 sm:grid-cols-2">
                            {missionFlow.rewards.map((reward) => (
                                <article
                                    key={reward.id}
                                    className="rounded-lg border border-slate-200 p-4 text-sm dark:border-slate-800"
                                >
                                    <p className="font-semibold">
                                        {reward.reward?.name}
                                    </p>
                                    <p
                                        className="mt-1 text-xs text-slate-500"
                                        dir="ltr"
                                    >
                                        {reward.reward?.code}
                                    </p>
                                    <p className="mt-3 text-xs text-slate-500">
                                        شریک:{' '}
                                        {reward.redemption?.partnerName ??
                                            reward.reward?.partnerName ??
                                            'پلتفرم'}{' '}
                                        · وضعیت:{' '}
                                        {rewardStatusLabels[reward.status] ??
                                            reward.status}
                                    </p>
                                    {reward.redemption ? (
                                        <div className="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs text-amber-950 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-100">
                                            <p className="font-medium">
                                                کد تحویل به فروشگاه
                                            </p>
                                            <p
                                                className="mt-2 font-mono text-lg font-semibold tracking-wider"
                                                dir="ltr"
                                            >
                                                {
                                                    reward.redemption
                                                        .redemptionCode
                                                }
                                            </p>
                                            <p className="mt-2">
                                                وضعیت تحویل:{' '}
                                                {redemptionStatusLabels[
                                                    reward.redemption.status
                                                ] ?? reward.redemption.status}
                                            </p>
                                        </div>
                                    ) : null}
                                </article>
                            ))}
                        </div>
                    )}
                </section>

                <div className="mt-6 grid gap-3 sm:grid-cols-2">
                    <Button asChild className="h-11">
                        <Link href="/participant/dashboard">
                            پنل مشارکت‌کننده
                        </Link>
                    </Button>
                    <Button asChild variant="outline" className="h-11">
                        <Link href={`/scan/${visit.qrCode ?? ''}`}>
                            بازگشت به صفحه QR
                        </Link>
                    </Button>
                </div>
            </section>
        </main>
    );
}
