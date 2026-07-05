import { Head, Link } from '@inertiajs/react';
import {
    CheckCircle2,
    Gift,
    MapPin,
    Play,
    QrCode,
    Sparkles,
    Trophy,
    UsersRound,
} from 'lucide-react';
import { Button } from '@/components/ui/button';

type Participant = {
    name: string;
    email: string;
    mode: string;
    modeLabel: string;
    members: string[];
    teamName: string | null;
};

type LatestVisit = {
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

type MissionFlow = {
    stats: {
        totalPoints: number;
        completedMissions: number;
        availableMissions: number;
        rewards: number;
    };
    missions: MissionItem[];
    rewards: UserRewardItem[];
} | null;

type MissionItem = {
    id: string;
    title: string;
    status: 'available' | 'started' | 'completed' | 'locked';
    isLocked: boolean;
    points: number;
    cycleStep: { index: number | null; label: string | null };
    hubName: string | null;
    treasureName: string | null;
};

type UserRewardItem = {
    id: string;
    status: string;
    redemption: {
        redemptionCode: string;
        status: string;
        partnerName: string | null;
    } | null;
    reward: {
        name: string;
        partnerName: string | null;
    } | null;
};

type Props = {
    participant: Participant;
    latestVisit: LatestVisit | null;
    missionFlow: MissionFlow;
};

const missionStatusLabels: Record<MissionItem['status'], string> = {
    available: 'آماده شروع',
    started: 'در حال انجام',
    completed: 'تکمیل شده',
    locked: 'قفل',
};

const rewardStatusLabels: Record<string, string> = {
    awarded: 'صادر شده',
    reserved: 'رزرو شده',
    redeemed: 'مصرف شده',
    expired: 'منقضی شده',
};

function formatDate(value: string) {
    return new Intl.DateTimeFormat('fa-IR', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

function progressPercent(flow: MissionFlow) {
    if (!flow || flow.missions.length === 0) {
        return 0;
    }

    return Math.round(
        (flow.stats.completedMissions / flow.missions.length) * 100,
    );
}

export default function ParticipantDashboard({
    participant,
    latestVisit,
    missionFlow,
}: Props) {
    const progress = progressPercent(missionFlow);
    const nextMission =
        missionFlow?.missions.find((mission) => mission.status === 'started') ??
        missionFlow?.missions.find(
            (mission) => mission.status === 'available',
        ) ??
        missionFlow?.missions.find((mission) => mission.status === 'locked') ??
        null;

    return (
        <main
            dir="rtl"
            className="flex h-full flex-1 flex-col gap-5 overflow-x-auto p-4"
        >
            <Head title="پنل مشارکت‌کننده" />

            <header className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p className="text-sm text-muted-foreground">
                        پنل بازدیدکننده و مشارکت‌کننده کمپین
                    </p>
                    <h1 className="mt-1 text-2xl font-semibold">
                        مسیر اکسپلوریا شما
                    </h1>
                    <p className="mt-2 text-sm text-muted-foreground">
                        برای شرکت فردی، خانوادگی یا تیمی در بازی آنلاین و مسیرهای
                        میدانی اکسپلوریا.
                    </p>
                </div>
                <div className="grid gap-3 text-sm sm:grid-cols-3">
                    <div className="rounded-lg border border-sidebar-border/70 p-3 dark:border-sidebar-border">
                        <p className="text-muted-foreground">امتیاز</p>
                        <p className="mt-1 text-xl font-semibold">
                            {(
                                missionFlow?.stats.totalPoints ?? 0
                            ).toLocaleString('fa-IR')}
                        </p>
                    </div>
                    <div className="rounded-lg border border-sidebar-border/70 p-3 dark:border-sidebar-border">
                        <p className="text-muted-foreground">ماموریت کامل</p>
                        <p className="mt-1 text-xl font-semibold">
                            {(
                                missionFlow?.stats.completedMissions ?? 0
                            ).toLocaleString('fa-IR')}
                        </p>
                    </div>
                    <div className="rounded-lg border border-sidebar-border/70 p-3 dark:border-sidebar-border">
                        <p className="text-muted-foreground">پاداش</p>
                        <p className="mt-1 text-xl font-semibold">
                            {(
                                missionFlow?.stats.rewards ?? 0
                            ).toLocaleString('fa-IR')}
                        </p>
                    </div>
                </div>
            </header>

            {!latestVisit ? (
                <section className="rounded-lg border border-dashed border-sidebar-border/70 bg-background p-6 text-sm leading-7 dark:border-sidebar-border">
                    <div className="flex items-center gap-2 font-semibold">
                        <QrCode className="size-5 text-muted-foreground" />
                        هنوز بازدید فعالی ثبت نشده است
                    </div>
                    <p className="mt-2 text-muted-foreground">
                        با اسکن QR کمپین، مسیر بازدید، ماموریت‌ها و کیف پاداش در
                        همین پنل فعال می‌شود.
                    </p>
                </section>
            ) : (
                <>
                    <section className="grid gap-4 xl:grid-cols-[0.9fr_1.1fr]">
                        <div className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
                            <div className="flex items-center gap-2">
                                <UsersRound className="size-5 text-sky-600" />
                                <h2 className="font-semibold">
                                    پروفایل مشارکت
                                </h2>
                            </div>
                            <dl className="mt-4 grid gap-3 text-sm">
                                <div className="flex justify-between gap-4">
                                    <dt className="text-muted-foreground">
                                        نام
                                    </dt>
                                    <dd className="font-medium">
                                        {participant.name}
                                    </dd>
                                </div>
                                <div className="flex justify-between gap-4">
                                    <dt className="text-muted-foreground">
                                        نوع شرکت
                                    </dt>
                                    <dd className="font-medium">
                                        {participant.modeLabel}
                                    </dd>
                                </div>
                                <div className="flex justify-between gap-4">
                                    <dt className="text-muted-foreground">
                                        تیم/خانواده
                                    </dt>
                                    <dd className="font-medium">
                                        {participant.teamName ?? 'ثبت نشده'}
                                    </dd>
                                </div>
                            </dl>
                            <div className="mt-4 flex flex-wrap gap-2">
                                {participant.members.map((member) => (
                                    <span
                                        key={member}
                                        className="rounded-full bg-muted px-3 py-1 text-xs"
                                    >
                                        {member}
                                    </span>
                                ))}
                            </div>
                        </div>

                        <div className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
                            <div className="flex items-center gap-2">
                                <MapPin className="size-5 text-emerald-600" />
                                <h2 className="font-semibold">
                                    آخرین کمپین فعال
                                </h2>
                            </div>
                            <div className="mt-4 grid gap-3 text-sm md:grid-cols-2">
                                <p>
                                    <span className="text-muted-foreground">
                                        مکان:
                                    </span>{' '}
                                    {latestVisit.venueName}
                                </p>
                                <p>
                                    <span className="text-muted-foreground">
                                        کمپین:
                                    </span>{' '}
                                    {latestVisit.campaignName}
                                </p>
                                <p>
                                    <span className="text-muted-foreground">
                                        هاب:
                                    </span>{' '}
                                    {latestVisit.hubName}
                                </p>
                                <p>
                                    <span className="text-muted-foreground">
                                        زمان:
                                    </span>{' '}
                                    {formatDate(latestVisit.occurredAt)}
                                </p>
                            </div>
                            <div className="mt-4 h-2 overflow-hidden rounded-full bg-muted">
                                <div
                                    className="h-full rounded-full bg-emerald-600"
                                    style={{ width: `${progress}%` }}
                                />
                            </div>
                            <p className="mt-2 text-xs text-muted-foreground">
                                {progress.toLocaleString('fa-IR')}٪ از مسیر این
                                بازدید تکمیل شده است.
                            </p>
                            <div className="mt-4 flex flex-wrap gap-2">
                                <Button asChild>
                                    <Link href={`/visits/${latestVisit.id}`}>
                                        ادامه ماموریت‌ها
                                    </Link>
                                </Button>
                                {latestVisit.qrCode ? (
                                    <Button asChild variant="outline">
                                        <Link
                                            href={`/scan/${latestVisit.qrCode}`}
                                        >
                                            صفحه QR کمپین
                                        </Link>
                                    </Button>
                                ) : null}
                            </div>
                        </div>
                    </section>

                    <section className="grid gap-4 xl:grid-cols-[1.1fr_0.9fr]">
                        <div className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
                            <div className="flex items-center gap-2">
                                <Trophy className="size-5 text-amber-500" />
                                <h2 className="font-semibold">
                                    ماموریت‌ها و قدم بعدی
                                </h2>
                            </div>
                            {nextMission ? (
                                <div className="mt-4 rounded-md bg-muted/40 p-3 text-sm">
                                    <p className="font-medium">
                                        قدم بعدی: {nextMission.title}
                                    </p>
                                    <p className="mt-1 text-xs text-muted-foreground">
                                        وضعیت:{' '}
                                        {
                                            missionStatusLabels[
                                                nextMission.status
                                            ]
                                        }{' '}
                                        · امتیاز:{' '}
                                        {nextMission.points.toLocaleString(
                                            'fa-IR',
                                        )}
                                    </p>
                                </div>
                            ) : null}
                            <div className="mt-4 grid gap-2">
                                {(missionFlow?.missions ?? []).map(
                                    (mission) => (
                                        <div
                                            key={mission.id}
                                            className="flex items-center justify-between gap-3 rounded-md border border-sidebar-border/70 p-3 text-sm dark:border-sidebar-border"
                                        >
                                            <div className="min-w-0">
                                                <div className="flex items-center gap-2">
                                                    {mission.status ===
                                                    'completed' ? (
                                                        <CheckCircle2 className="size-4 text-emerald-600" />
                                                    ) : (
                                                        <Sparkles className="size-4 text-sky-600" />
                                                    )}
                                                    <p className="truncate font-medium">
                                                        {mission.title}
                                                    </p>
                                                </div>
                                                <p className="mt-1 text-xs text-muted-foreground">
                                                    {mission.cycleStep.label ??
                                                        mission.hubName ??
                                                        'مسیر اصلی'}
                                                </p>
                                            </div>
                                            <span className="rounded-full bg-muted px-2.5 py-1 text-xs">
                                                {
                                                    missionStatusLabels[
                                                        mission.status
                                                    ]
                                                }
                                            </span>
                                        </div>
                                    ),
                                )}
                            </div>
                        </div>

                        <div className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
                            <div className="flex items-center gap-2">
                                <Gift className="size-5 text-rose-500" />
                                <h2 className="font-semibold">کیف پاداش</h2>
                            </div>
                            {(missionFlow?.rewards ?? []).length === 0 ? (
                                <p className="mt-4 rounded-md border border-dashed border-sidebar-border/70 p-3 text-sm text-muted-foreground dark:border-sidebar-border">
                                    هنوز پاداشی صادر نشده است.
                                </p>
                            ) : (
                                <div className="mt-4 grid gap-3">
                                    {missionFlow?.rewards.map((reward) => (
                                        <div
                                            key={reward.id}
                                            className="rounded-md border border-sidebar-border/70 p-3 text-sm dark:border-sidebar-border"
                                        >
                                            <p className="font-medium">
                                                {reward.reward?.name}
                                            </p>
                                            <p className="mt-1 text-xs text-muted-foreground">
                                                وضعیت:{' '}
                                                {rewardStatusLabels[
                                                    reward.status
                                                ] ?? reward.status}
                                            </p>
                                            {reward.redemption ? (
                                                <p
                                                    className="mt-3 rounded-md bg-amber-50 px-3 py-2 font-mono text-base font-semibold text-amber-900 dark:bg-amber-950 dark:text-amber-100"
                                                    dir="ltr"
                                                >
                                                    {
                                                        reward.redemption
                                                            .redemptionCode
                                                    }
                                                </p>
                                            ) : null}
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    </section>
                </>
            )}
        </main>
    );
}
