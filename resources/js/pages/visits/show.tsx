import { Form, Head, Link, usePage } from '@inertiajs/react';
import { CheckCircle2, Gift, Lock, Play, Sparkles, Trophy } from 'lucide-react';
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

export default function VisitShow({ visit, missionFlow }: Props) {
    const { flash } = usePage<SharedProps>().props;

    return (
        <main className="min-h-screen bg-slate-50 px-4 py-10 text-slate-950 dark:bg-slate-950 dark:text-slate-50">
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

                        <div className="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 p-5 text-sm leading-7 text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-200">
                            این بخش حالا به دیتابیس واقعی ماموریت، پیشرفت کاربر
                            و کیف پاداش وصل است.
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
                                        {reward.reward?.partnerName ?? 'پلتفرم'}{' '}
                                        · وضعیت: {reward.status}
                                    </p>
                                </article>
                            ))}
                        </div>
                    )}
                </section>

                <div className="mt-6 grid gap-3 sm:grid-cols-2">
                    <Button asChild className="h-11">
                        <Link href="/dashboard">مشاهده داشبورد</Link>
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
