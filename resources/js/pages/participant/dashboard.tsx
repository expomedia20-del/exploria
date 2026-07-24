import { Form, Head, Link, usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import {
    CheckCircle2,
    Compass,
    Gem,
    Gift,
    History,
    LogOut,
    MapPin,
    Play,
    QrCode,
    Sparkles,
    Store,
    Trophy,
    UsersRound,
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { logout } from '@/routes';

type Participant = {
    name: string;
    email: string;
    mode: string;
    modeLabel: string;
    members: string[];
    teamName: string | null;
    publicStatus: string;
    publicStatusLabel: string;
};

type LatestVisit = {
    id: string;
    status: string;
    occurredAt: string;
    qrCode: string | null;
    qrLandingUrl: string | null;
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

type VisitorPreviewOption = {
    id: number;
    name: string;
    email: string;
    visitsCount: number;
};

type ViewerMode = {
    canPreviewVisitors: boolean;
    isAdminPreview: boolean;
    currentVisitorId: number | null;
    previewOptions: VisitorPreviewOption[];
};

type Journey = {
    points: {
        earned: number;
        spent: number;
        stored: number;
        redeemedRewards: number;
        nextPotential: number;
    };
    activeCampaigns: {
        id: string;
        name: string;
        code: string;
        venueName: string | null;
        city: string | null;
        scanUrl: string | null;
        hasVisit: boolean;
        latestVisitId: string | null;
        experienceUrl: string | null;
        lastVisitedAt: string | null;
        completedMissions: number;
        totalMissions: number;
        progressPercent: number;
    }[];
    rewardCatalog: {
        id: string;
        name: string;
        rewardType: string;
        rewardTypeLabel: string;
        campaignName: string | null;
        campaignCode: string | null;
        partnerName: string | null;
        partnerType: string | null;
        pointCost: number | null;
        stockQuantity: number | null;
        remainingStock: number | null;
        source: string | null;
        tier: string | null;
    }[];
    rewardWallet: {
        id: string;
        status: string;
        awardedAt: string | null;
        expiresAt: string | null;
        campaignName: string | null;
        campaignCode: string | null;
        rewardName: string | null;
        rewardType: string | null;
        rewardTypeLabel: string;
        pointCost: number | null;
        partnerName: string | null;
        redemptionCode: string | null;
        redemptionStatus: string | null;
        redeemedAt: string | null;
        redemption?: { partnerName: string | null } | null;
        reward?: { partnerName: string | null } | null;
    }[];
    history: {
        id: string;
        venueName: string | null;
        city: string | null;
        campaignName: string | null;
        campaignCode: string | null;
        hubName: string | null;
        status: string;
        occurredAt: string;
        points: number;
    }[];
    partners: {
        name: string;
        type: string | null;
        rewardName: string | null;
        rewardType: string | null;
        rewardTypeLabel: string;
        campaignName: string | null;
        pointCost: number | null;
        status: string;
        redeemedAt: string | null;
    }[];
    treasures: {
        name: string;
        type: string;
        campaignName: string | null;
    }[];
    nextAction: {
        label: string;
        description: string;
        href: string | null;
    };
};

type Props = {
    participant: Participant;
    latestVisit: LatestVisit | null;
    missionFlow: MissionFlow;
    onlineGame: {
        id: string;
        status: 'active' | 'ready_for_visit' | 'completed';
        mode: 'individual' | 'family' | 'team';
        name: string | null;
        score: number;
        members: { displayName: string; role: string }[];
        steps: {
            index: number;
            status: 'locked' | 'available' | 'completed';
            points: number;
        }[];
        entryPass: {
            code: string;
            status: 'active' | 'redeemed' | 'expired';
        } | null;
    } | null;
    journey: Journey;
    viewerMode: ViewerMode;
};

type SharedProps = {
    flash?: {
        success?: string;
    };
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
    confirmed: 'تحویل شده',
    expired: 'منقضی شده',
};

const participantHeroImage =
    '/images/ecopark/proposal/participant-route-card-3-2.jpg';

const journeySegments = [
    {
        title: 'چالش',
        body: 'ماموریت‌های مکان را مرحله‌به‌مرحله دنبال کنید.',
        icon: Compass,
        tone: 'border-cyan-200 bg-cyan-50 text-cyan-950 dark:border-cyan-900 dark:bg-cyan-950 dark:text-cyan-100',
    },
    {
        title: 'گنج',
        body: 'با پیشرفت در مسیر، گنج‌ها و نقاط ویژه باز می‌شوند.',
        icon: Gem,
        tone: 'border-fuchsia-200 bg-fuchsia-50 text-fuchsia-950 dark:border-fuchsia-900 dark:bg-fuchsia-950 dark:text-fuchsia-100',
    },
    {
        title: 'پاداش',
        body: 'امتیازها به کوپن، هدیه یا تخفیف قابل استفاده تبدیل می‌شوند.',
        icon: Gift,
        tone: 'border-amber-200 bg-amber-50 text-amber-950 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-100',
    },
    {
        title: 'فروشگاه',
        body: 'پاداش در واحدهای عضو مصرف می‌شود و فرصت فروش می‌سازد.',
        icon: Store,
        tone: 'border-emerald-200 bg-emerald-50 text-emerald-950 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-100',
    },
];

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
    onlineGame,
    journey,
    viewerMode,
}: Props) {
    const { flash } = usePage<SharedProps>().props;
    const progress = onlineGame
        ? Math.round(
              (onlineGame.steps.filter((step) => step.status === 'completed')
                  .length /
                  5) *
                  100,
          )
        : progressPercent(missionFlow);
    const activeCampaignsCount = journey.activeCampaigns.length;
    const rewardCatalogCount = journey.rewardCatalog.length;
    const discoveredTreasuresCount = journey.treasures.length;
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

            <div className="flex justify-end">
                <Form {...logout.form()}>
                    {({ processing }) => (
                        <Button
                            type="submit"
                            variant="outline"
                            disabled={processing}
                            data-test="logout-button"
                        >
                            <LogOut className="size-4" />
                            خروج
                        </Button>
                    )}
                </Form>
            </div>

            {flash?.success && (
                <div className="rounded-md border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">
                    {flash.success}
                </div>
            )}

            <header className="relative overflow-hidden rounded-lg border border-white/10 bg-zinc-950 text-white shadow-sm dark:border-sidebar-border">
                <img
                    src={participantHeroImage}
                    alt=""
                    className="absolute inset-0 h-full w-full object-cover opacity-50"
                />
                <div className="absolute inset-0 bg-[linear-gradient(90deg,rgba(8,13,34,0.78),rgba(8,13,34,0.9)_48%,rgba(8,13,34,0.64))]" />
                <div className="absolute inset-0 bg-[radial-gradient(circle_at_72%_20%,rgba(16,185,129,0.26),transparent_28%),radial-gradient(circle_at_18%_70%,rgba(217,70,239,0.18),transparent_34%)]" />
                <div className="relative grid gap-6 p-5 sm:p-6 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
                    <div className="max-w-3xl">
                        <p className="inline-flex rounded-full border border-emerald-200/35 bg-white/10 px-4 py-2 text-sm font-medium text-emerald-100">
                            خانه بازدیدکننده اکسپلوریا
                        </p>
                        <h1 className="mt-5 text-3xl leading-tight font-semibold sm:text-4xl">
                            مسیر چالش، گنج و پاداش شما آماده است
                        </h1>
                        <p className="mt-3 text-sm leading-7 text-zinc-200 sm:text-base">
                            اینجا ادامه تجربه مکان را می‌بینید: کمپین فعال، قدم
                            بعدی، امتیازها، کیف پاداش، گنج‌های کشف‌شده و
                            پیشنهادهایی که به فروش واحدهای عضو وصل می‌شوند.
                        </p>
                        <div className="mt-5 flex flex-wrap gap-2">
                            {journey.nextAction.href ? (
                                <Button asChild>
                                    <Link href={journey.nextAction.href}>
                                        <Play className="size-4" />
                                        ادامه تجربه
                                    </Link>
                                </Button>
                            ) : null}
                            {latestVisit?.qrLandingUrl ? (
                                <Button asChild variant="secondary">
                                    <Link href={latestVisit.qrLandingUrl}>
                                        <QrCode className="size-4" />
                                        صفحه QR کمپین
                                    </Link>
                                </Button>
                            ) : null}
                        </div>
                    </div>
                    <div className="grid gap-3 text-sm sm:grid-cols-2">
                        {[
                            ['کمپین فعال', activeCampaignsCount],
                            [
                                'ماموریت کامل',
                                onlineGame
                                    ? onlineGame.steps.filter(
                                          (step) => step.status === 'completed',
                                      ).length
                                    : (missionFlow?.stats.completedMissions ??
                                      0),
                            ],
                            ['پاداش قابل انتخاب', rewardCatalogCount],
                            ['گنج کشف‌شده', discoveredTreasuresCount],
                        ].map(([label, value]) => (
                            <div
                                key={label}
                                className="rounded-lg border border-white/15 bg-white/10 p-4 backdrop-blur-sm"
                            >
                                <p className="text-zinc-300">{label}</p>
                                <p className="mt-2 text-2xl font-semibold">
                                    {Number(value).toLocaleString('fa-IR')}
                                </p>
                            </div>
                        ))}
                    </div>
                </div>
            </header>

            <section className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
                <div className="grid gap-4 lg:grid-cols-[1fr_auto] lg:items-center">
                    <div>
                        <div className="flex flex-wrap items-center gap-2">
                            <span className="rounded-full bg-muted px-2.5 py-1 text-xs text-muted-foreground">
                                وضعیت عمومی
                            </span>
                            <span className="rounded-full bg-cyan-100 px-2.5 py-1 text-xs font-medium text-cyan-800 dark:bg-cyan-950 dark:text-cyan-200">
                                {participant.publicStatusLabel}
                            </span>
                        </div>
                        <h2 className="mt-3 font-semibold">
                            شروع مشارکت در کمپین
                        </h2>
                        <p className="mt-1 text-sm leading-7 text-muted-foreground">
                            ورود با موبایل شما را به کاربر عادی تبدیل می‌کند.
                            برای دریافت ماموریت، امتیاز، گنج و پاداش، نوع مشارکت
                            را انتخاب و تایید کنید. این کار نیازی به تایید ادمین
                            ندارد.
                        </p>
                    </div>

                    {participant.publicStatus === 'participant' ? (
                        <div className="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-100">
                            مشارکت فعال است. حالا می‌توانید کمپین را انتخاب کنید
                            یا QR همان کمپین را اسکن کنید.
                        </div>
                    ) : (
                        <Form
                            action="/participant/participation"
                            method="post"
                            options={{ preserveScroll: true }}
                            className="grid min-w-72 gap-3"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <select
                                        name="mode"
                                        defaultValue="individual"
                                        className="min-h-10 rounded-md border border-input bg-background px-3 text-sm"
                                    >
                                        <option value="individual">
                                            مشارکت فردی
                                        </option>
                                        <option value="family">
                                            مشارکت خانوادگی
                                        </option>
                                        <option value="team">
                                            مشارکت تیمی / گروهی
                                        </option>
                                    </select>
                                    {errors.mode && (
                                        <p className="text-xs text-destructive">
                                            {errors.mode}
                                        </p>
                                    )}
                                    <Button type="submit" disabled={processing}>
                                        تایید و شروع مشارکت
                                    </Button>
                                </>
                            )}
                        </Form>
                    )}
                </div>
            </section>

            <section className="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                {journeySegments.map((segment) => (
                    <article
                        key={segment.title}
                        className={`rounded-lg border p-4 text-sm ${segment.tone}`}
                    >
                        <div className="flex items-center gap-2">
                            <segment.icon className="size-5" />
                            <h2 className="font-semibold">{segment.title}</h2>
                        </div>
                        <p className="mt-3 leading-7 opacity-80">
                            {segment.body}
                        </p>
                    </article>
                ))}
            </section>

            {viewerMode.canPreviewVisitors ? (
                <section className="rounded-lg border border-sidebar-border/70 bg-background p-4 text-sm dark:border-sidebar-border">
                    <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 className="font-semibold">
                                پیش‌نمایش پنل مشارکت‌کننده برای ادمین
                            </h2>
                            <p className="mt-1 text-muted-foreground">
                                برای پشتیبانی یا دمو، یک بازدیدکننده واقعی را
                                انتخاب کنید؛ نیازی به خروج از اکانت ادمین نیست.
                            </p>
                        </div>
                        <select
                            className="min-h-10 rounded-md border border-input bg-background px-3 py-2 text-sm"
                            value={viewerMode.currentVisitorId ?? ''}
                            onChange={(event) => {
                                const visitorId = event.currentTarget.value;

                                if (visitorId) {
                                    window.location.href = `/participant/dashboard?visitor_id=${visitorId}`;
                                }
                            }}
                        >
                            <option value="" disabled>
                                انتخاب بازدیدکننده
                            </option>
                            {viewerMode.previewOptions.length === 0 ? (
                                <option value="" disabled>
                                    هنوز بازدیدکننده دارای بازدید ثبت‌شده وجود
                                    ندارد
                                </option>
                            ) : null}
                            {viewerMode.previewOptions.map((option) => (
                                <option key={option.id} value={option.id}>
                                    {option.name} - {option.email} (
                                    {option.visitsCount.toLocaleString('fa-IR')}{' '}
                                    بازدید)
                                </option>
                            ))}
                        </select>
                    </div>
                    {viewerMode.isAdminPreview ? (
                        <p className="mt-3 rounded-md bg-sky-50 px-3 py-2 text-xs text-sky-900 dark:bg-sky-950 dark:text-sky-100">
                            این صفحه در حالت پیش‌نمایش ادمین نمایش داده می‌شود؛
                            عملیات واقعی همچنان متعلق به اکانت بازدیدکننده
                            انتخاب‌شده است.
                        </p>
                    ) : null}
                </section>
            ) : null}

            <section className="grid gap-4 xl:grid-cols-[1fr_1.2fr]">
                <div className="overflow-hidden rounded-lg border border-sidebar-border/70 bg-background dark:border-sidebar-border">
                    <div className="grid gap-0 lg:grid-cols-[0.95fr_1.05fr]">
                        <div className="relative min-h-44">
                            <img
                                src={participantHeroImage}
                                alt=""
                                className="absolute inset-0 h-full w-full object-cover"
                            />
                            <div className="absolute inset-0 bg-gradient-to-l from-background via-background/30 to-transparent" />
                        </div>
                        <div className="p-4">
                            <div className="flex items-center gap-2">
                                <Compass className="size-5 text-emerald-600" />
                                <h2 className="font-semibold">قدم بعدی شما</h2>
                            </div>
                            <p className="mt-3 text-sm font-medium">
                                {journey.nextAction.label}
                            </p>
                            <p className="mt-2 text-sm leading-7 text-muted-foreground">
                                {journey.nextAction.description}
                            </p>
                            <div className="mt-4 flex flex-wrap gap-2">
                                {journey.nextAction.href ? (
                                    <Button asChild>
                                        <Link href={journey.nextAction.href}>
                                            <Play className="size-4" />
                                            ادامه مسیر
                                        </Link>
                                    </Button>
                                ) : null}
                                {latestVisit?.qrLandingUrl ? (
                                    <Button asChild variant="outline">
                                        <Link href={latestVisit.qrLandingUrl}>
                                            <QrCode className="size-4" />
                                            راهنمای QR کمپین
                                        </Link>
                                    </Button>
                                ) : null}
                            </div>
                        </div>
                    </div>
                </div>

                <div className="grid gap-3 sm:grid-cols-4">
                    <StatCard label="کسب‌شده" value={journey.points.earned} />
                    <StatCard label="مصرف‌شده" value={journey.points.spent} />
                    <StatCard
                        label="ذخیره فعلی"
                        value={journey.points.stored}
                    />
                    <StatCard
                        label="قابل دریافت"
                        value={journey.points.nextPotential}
                    />
                </div>
            </section>

            <section className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
                <div className="flex items-center gap-2">
                    <QrCode className="size-5 text-sky-600" />
                    <h2 className="font-semibold">
                        کمپین‌های فعال و مسیرهای قابل شروع
                    </h2>
                </div>
                <div className="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                    {journey.activeCampaigns.length === 0 ? (
                        <EmptyBox text="کمپین فعالی برای انتخاب مستقیم ثبت نشده است؛ با اسکن QRهای محیطی، مسیر مشارکت فعال می‌شود." />
                    ) : (
                        journey.activeCampaigns.map((campaign) => (
                            <article
                                key={campaign.id}
                                className="rounded-md border border-sidebar-border/70 p-3 text-sm dark:border-sidebar-border"
                            >
                                <div className="flex flex-wrap items-start justify-between gap-2">
                                    <div>
                                        <p className="font-medium">
                                            {campaign.name}
                                        </p>
                                        <p className="mt-1 text-xs text-muted-foreground">
                                            {campaign.venueName ?? 'مکان پروژه'}{' '}
                                            - {campaign.city ?? 'شهر'}
                                        </p>
                                    </div>
                                    <span
                                        className={`rounded-full px-2.5 py-1 text-xs ${campaign.hasVisit ? 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-100' : 'bg-sky-50 text-sky-800 dark:bg-sky-950 dark:text-sky-100'}`}
                                    >
                                        {campaign.hasVisit
                                            ? 'قبلا شروع شده'
                                            : 'شروع جدید'}
                                    </span>
                                </div>
                                <p className="mt-3 text-xs leading-6 text-muted-foreground">
                                    {campaign.hasVisit
                                        ? 'این کمپین در سابقه شما وجود دارد؛ می‌توانید از همان مسیر ادامه دهید و امتیازها، ماموریت‌ها و پاداش‌های باقی‌مانده را دنبال کنید.'
                                        : 'برای شروع از صفر، راهنمای QR را ببینید؛ مسیر، ماموریت‌ها، پاداش‌ها و واحدهای تجاری همان کمپین مشخص می‌شود.'}
                                </p>
                                {campaign.hasVisit ? (
                                    <div className="mt-3">
                                        <div className="h-2 overflow-hidden rounded-full bg-muted">
                                            <div
                                                className="h-full rounded-full bg-emerald-600"
                                                style={{
                                                    width: `${campaign.progressPercent}%`,
                                                }}
                                            />
                                        </div>
                                        <p className="mt-2 text-xs text-muted-foreground">
                                            {campaign.completedMissions.toLocaleString(
                                                'fa-IR',
                                            )}{' '}
                                            از{' '}
                                            {campaign.totalMissions.toLocaleString(
                                                'fa-IR',
                                            )}{' '}
                                            ماموریت تکمیل شده
                                            {campaign.lastVisitedAt
                                                ? ` - آخرین مراجعه: ${formatDate(campaign.lastVisitedAt)}`
                                                : ''}
                                        </p>
                                    </div>
                                ) : null}
                                <div className="mt-3 flex flex-wrap gap-2">
                                    {campaign.hasVisit &&
                                    campaign.latestVisitId ? (
                                        <Button asChild size="sm">
                                            <Link
                                                href={
                                                    campaign.experienceUrl ??
                                                    `/visits/${campaign.latestVisitId}`
                                                }
                                            >
                                                ادامه مشارکت
                                            </Link>
                                        </Button>
                                    ) : null}
                                    {campaign.scanUrl ? (
                                        <Button
                                            asChild
                                            variant={
                                                campaign.hasVisit
                                                    ? 'outline'
                                                    : 'default'
                                            }
                                            size="sm"
                                        >
                                            <Link href={campaign.scanUrl}>
                                                {campaign.hasVisit
                                                    ? 'راهنمای QR'
                                                    : 'شروع با راهنمای QR'}
                                            </Link>
                                        </Button>
                                    ) : (
                                        <p className="rounded-md bg-muted px-3 py-2 text-xs text-muted-foreground">
                                            QR فعال برای شروع مستقیم ندارد.
                                        </p>
                                    )}
                                </div>
                            </article>
                        ))
                    )}
                </div>
            </section>

            <section className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
                <div className="flex items-center gap-2">
                    <Gift className="size-5 text-rose-500" />
                    <h2 className="font-semibold">
                        پاداش‌ها و مشوق‌های قابل دریافت
                    </h2>
                </div>
                <p className="mt-2 text-sm leading-7 text-muted-foreground">
                    این بخش نشان می‌دهد در کمپین‌های فعال چه نوع مشوقی ممکن است
                    دریافت شود؛ کوپن فروشگاهی، تخفیف اسپانسری، هدیه محصولی یا
                    جایزه.
                </p>
                <div className="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                    {journey.rewardCatalog.length === 0 ? (
                        <EmptyBox text="هنوز پاداش فعالی برای کمپین‌های قابل انتخاب ثبت نشده است." />
                    ) : (
                        journey.rewardCatalog.map((reward) => (
                            <article
                                key={reward.id}
                                className="rounded-md border border-sidebar-border/70 p-3 text-sm dark:border-sidebar-border"
                            >
                                <div className="flex flex-wrap items-start justify-between gap-2">
                                    <p className="font-medium">{reward.name}</p>
                                    <span className="rounded-full bg-rose-50 px-2.5 py-1 text-xs text-rose-800 dark:bg-rose-950 dark:text-rose-100">
                                        {reward.rewardTypeLabel}
                                    </span>
                                </div>
                                <p className="mt-2 text-xs leading-6 text-muted-foreground">
                                    {reward.campaignName ?? 'کمپین'} -{' '}
                                    {reward.partnerName ?? 'اکسپلوریا'}
                                </p>
                                <p className="mt-2 text-xs text-muted-foreground">
                                    {reward.pointCost !== null
                                        ? `${reward.pointCost.toLocaleString('fa-IR')} امتیاز`
                                        : 'بدون هزینه امتیازی'}
                                    {reward.remainingStock !== null
                                        ? ` - موجودی قابل صدور: ${reward.remainingStock.toLocaleString('fa-IR')}`
                                        : ''}
                                </p>
                            </article>
                        ))
                    )}
                </div>
            </section>

            {!latestVisit ? (
                <section className="rounded-lg border border-dashed border-sidebar-border/70 bg-background p-6 text-sm leading-7 dark:border-sidebar-border">
                    <div className="flex items-center gap-2 font-semibold">
                        <QrCode className="size-5 text-muted-foreground" />
                        هنوز بازدید فعالی ثبت نشده است
                    </div>
                    <p className="mt-2 text-muted-foreground">
                        با اسکن QR کمپین، مسیر بازدید، ماموریت‌ها، کیف پاداش و
                        امتیازهای شما در همین پنل فعال می‌شود.
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
                                <InfoRow label="نام" value={participant.name} />
                                <InfoRow
                                    label="نوع شرکت"
                                    value={participant.modeLabel}
                                />
                                <InfoRow
                                    label="تیم/خانواده"
                                    value={participant.teamName ?? 'ثبت نشده'}
                                />
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
                                    <Link
                                        href={
                                            onlineGame
                                                ? `/games/ecopark-treasure?visit=${latestVisit.id}`
                                                : `/visits/${latestVisit.id}`
                                        }
                                    >
                                        {onlineGame
                                            ? 'ادامه بازی آنلاین'
                                            : 'ادامه ماموریت‌ها'}
                                    </Link>
                                </Button>
                                {latestVisit.qrLandingUrl ? (
                                    <Button asChild variant="outline">
                                        <Link href={latestVisit.qrLandingUrl}>
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
                                    {onlineGame
                                        ? 'بازی آنلاین و قدم بعدی'
                                        : 'ماموریت‌ها و قدم بعدی'}
                                </h2>
                            </div>
                            {onlineGame ? (
                                <div className="mt-4 rounded-md border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-950 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-100">
                                    <p className="font-semibold">
                                        {onlineGame.name ??
                                            'مسیر بازی آنلاین اکوپارک'}
                                    </p>
                                    <p className="mt-2 leading-7">
                                        این کمپین یک جریان واحد پنج‌مرحله‌ای
                                        دارد. دکمه‌های عمومی «شروع/تکمیل
                                        مأموریت» برای آن نمایش داده نمی‌شوند؛ هر
                                        مرحله فقط داخل صفحه بازی و با اعتبارسنجی
                                        خودش کامل می‌شود.
                                    </p>
                                    <p className="mt-2 text-xs">
                                        اعضا:{' '}
                                        {onlineGame.members
                                            .map((member) => member.displayName)
                                            .join('، ')}{' '}
                                        · امتیاز:{' '}
                                        {onlineGame.score.toLocaleString(
                                            'fa-IR',
                                        )}
                                    </p>
                                    <Button asChild className="mt-4">
                                        <Link
                                            href={`/games/ecopark-treasure?visit=${latestVisit.id}`}
                                        >
                                            {onlineGame.status === 'completed'
                                                ? 'مشاهده نتیجه و مجوز'
                                                : 'بازگشت به مرحله جاری بازی'}
                                        </Link>
                                    </Button>
                                </div>
                            ) : nextMission ? (
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
                            {!onlineGame ? (
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
                                                        {mission.cycleStep
                                                            .label ??
                                                            mission.hubName ??
                                                            'مسیر اصلی'}
                                                        {mission.treasureName
                                                            ? ` · گنج: ${mission.treasureName}`
                                                            : ''}
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
                            ) : null}
                        </div>

                        <div className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
                            <div className="flex items-center gap-2">
                                <Gift className="size-5 text-rose-500" />
                                <h2 className="font-semibold">کیف پاداش</h2>
                            </div>
                            {journey.rewardWallet.length === 0 ? (
                                <EmptyBox text="هنوز پاداشی صادر نشده است." />
                            ) : (
                                <div className="mt-4 grid gap-3">
                                    {journey.rewardWallet.map((reward) => (
                                        <div
                                            key={reward.id}
                                            className="rounded-md border border-sidebar-border/70 p-3 text-sm dark:border-sidebar-border"
                                        >
                                            <p className="font-medium">
                                                {reward.rewardName ?? 'پاداش'}
                                            </p>
                                            <p className="mt-1 text-xs text-muted-foreground">
                                                محل مصرف:{' '}
                                                {reward.redemption
                                                    ?.partnerName ??
                                                    reward.reward
                                                        ?.partnerName ??
                                                    'پلتفرم'}{' '}
                                                · وضعیت:{' '}
                                                {rewardStatusLabels[
                                                    reward.status
                                                ] ?? reward.status}
                                            </p>
                                            {reward.redemptionCode ? (
                                                <p
                                                    className="mt-3 rounded-md bg-amber-50 px-3 py-2 font-mono text-base font-semibold text-amber-900 dark:bg-amber-950 dark:text-amber-100"
                                                    dir="ltr"
                                                >
                                                    {reward.redemptionCode}
                                                </p>
                                            ) : null}
                                            <p className="mt-2 text-xs leading-6 text-muted-foreground">
                                                {reward.rewardTypeLabel} -{' '}
                                                {reward.campaignName ?? 'کمپین'}{' '}
                                                -{' '}
                                                {reward.partnerName ??
                                                    'اکسپلوریا'}
                                                {reward.pointCost !== null
                                                    ? ` - ${reward.pointCost.toLocaleString('fa-IR')} امتیاز`
                                                    : ''}
                                                {reward.awardedAt
                                                    ? ` - صدور: ${formatDate(reward.awardedAt)}`
                                                    : ''}
                                                {reward.redeemedAt
                                                    ? ` - مصرف: ${formatDate(reward.redeemedAt)}`
                                                    : ''}
                                            </p>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    </section>
                </>
            )}

            <section className="grid gap-4 xl:grid-cols-3">
                <InfoPanel
                    icon={<History className="size-5 text-slate-600" />}
                    title="سوابق مراجعه، مکان و کمپین"
                >
                    {journey.history.length === 0 ? (
                        <EmptyBox text="هنوز سابقه‌ای ثبت نشده است." />
                    ) : (
                        journey.history.map((visit) => (
                            <Link
                                key={visit.id}
                                href={`/visits/${visit.id}`}
                                className="rounded-md border border-sidebar-border/70 p-3 text-sm hover:bg-muted/40 dark:border-sidebar-border"
                            >
                                <div className="flex items-start justify-between gap-2">
                                    <p className="font-medium">
                                        {visit.campaignName ?? 'کمپین'} -{' '}
                                        {visit.venueName ?? 'مکان'}
                                    </p>
                                    <span className="shrink-0 rounded-full bg-muted px-2.5 py-1 text-xs">
                                        ادامه
                                    </span>
                                </div>
                                <p className="mt-1 text-xs text-muted-foreground">
                                    {visit.hubName ?? 'مسیر عمومی'} -{' '}
                                    {formatDate(visit.occurredAt)} -{' '}
                                    {visit.points.toLocaleString('fa-IR')}{' '}
                                    امتیاز
                                </p>
                            </Link>
                        ))
                    )}
                </InfoPanel>

                <InfoPanel
                    icon={<Store className="size-5 text-emerald-700" />}
                    title="واحدهای تجاری و مشوق‌ها"
                >
                    {journey.partners.length === 0 ? (
                        <EmptyBox text="هنوز مراجعه یا مصرف پاداش در واحد تجاری ثبت نشده است." />
                    ) : (
                        journey.partners.map((partner, index) => (
                            <div
                                key={`${partner.name}-${index}`}
                                className="rounded-md border border-sidebar-border/70 p-3 text-sm dark:border-sidebar-border"
                            >
                                <p className="font-medium">{partner.name}</p>
                                <p className="mt-1 text-xs text-muted-foreground">
                                    وضعیت مشوق: {partner.status}
                                    {partner.redeemedAt
                                        ? ` · ${formatDate(partner.redeemedAt)}`
                                        : ''}
                                </p>
                                <p className="mt-2 text-xs leading-6 text-muted-foreground">
                                    {partner.rewardName ?? 'مشوق ثبت‌شده'} -{' '}
                                    {partner.rewardTypeLabel} -{' '}
                                    {partner.campaignName ?? 'کمپین'}
                                    {partner.pointCost !== null
                                        ? ` - ${partner.pointCost.toLocaleString('fa-IR')} امتیاز`
                                        : ''}
                                </p>
                            </div>
                        ))
                    )}
                </InfoPanel>

                <InfoPanel
                    icon={<Gem className="size-5 text-rose-600" />}
                    title="گنج‌ها و انگیزه ادامه"
                >
                    {journey.treasures.length === 0 ? (
                        <EmptyBox text="هنوز گنجی کشف نشده است؛ با ادامه ماموریت‌ها گنج و پاداش‌های بعدی فعال می‌شوند." />
                    ) : (
                        journey.treasures.map((treasure, index) => (
                            <div
                                key={`${treasure.name}-${index}`}
                                className="rounded-md border border-sidebar-border/70 p-3 text-sm dark:border-sidebar-border"
                            >
                                <p className="font-medium">{treasure.name}</p>
                                <p className="mt-1 text-xs text-muted-foreground">
                                    {treasure.campaignName ?? 'کمپین'} ·{' '}
                                    {treasure.type}
                                </p>
                            </div>
                        ))
                    )}
                    <p className="rounded-md bg-muted/50 px-3 py-2 text-xs leading-6 text-muted-foreground">
                        ادامه مشارکت فردی، خانوادگی یا تیمی می‌تواند امتیاز
                        مرحله بعدی، پاداش فروشگاهی و گنج‌های اسپانسری بیشتری
                        فعال کند.
                    </p>
                </InfoPanel>
            </section>
        </main>
    );
}

function StatCard({ label, value }: { label: string; value: number }) {
    return (
        <div className="rounded-lg border border-sidebar-border/70 bg-background p-3 text-sm dark:border-sidebar-border">
            <p className="text-muted-foreground">{label}</p>
            <p className="mt-2 text-xl font-semibold">
                {value.toLocaleString('fa-IR')}
            </p>
        </div>
    );
}

function InfoRow({ label, value }: { label: string; value: string }) {
    return (
        <div className="flex justify-between gap-4">
            <dt className="text-muted-foreground">{label}</dt>
            <dd className="font-medium">{value}</dd>
        </div>
    );
}

function EmptyBox({ text }: { text: string }) {
    return (
        <p className="rounded-md border border-dashed border-sidebar-border/70 p-3 text-sm leading-7 text-muted-foreground dark:border-sidebar-border">
            {text}
        </p>
    );
}

function InfoPanel({
    icon,
    title,
    children,
}: {
    icon: ReactNode;
    title: string;
    children: ReactNode;
}) {
    return (
        <div className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
            <div className="flex items-center gap-2">
                {icon}
                <h2 className="font-semibold">{title}</h2>
            </div>
            <div className="mt-4 grid gap-2">{children}</div>
        </div>
    );
}
