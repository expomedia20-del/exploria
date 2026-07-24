import { Form, Head, Link, usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import {
    BadgeCheck,
    Check,
    Compass,
    Gift,
    History,
    LockKeyhole,
    LogOut,
    Megaphone,
    Play,
    QrCode,
    Sparkles,
    Store,
    TicketCheck,
    UsersRound,
    WalletCards,
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
    qrLandingUrl: string | null;
    venueName: string | null;
    city: string | null;
    hubName: string | null;
    campaignName: string | null;
};

type MissionItem = {
    id: string;
    title: string;
    status: 'available' | 'started' | 'completed' | 'locked';
    points: number;
};

type MissionFlow = {
    stats: {
        totalPoints: number;
        completedMissions: number;
        availableMissions: number;
        rewards: number;
    };
    missions: MissionItem[];
} | null;

type ViewerMode = {
    canPreviewVisitors: boolean;
    isAdminPreview: boolean;
    currentVisitorId: number | null;
    previewOptions: {
        id: number;
        name: string;
        email: string;
        visitsCount: number;
    }[];
};

type TimelineStep = {
    index: number;
    title: string;
    phase: 'online' | 'physical';
    status: 'locked' | 'available' | 'completed';
};

type OnlineGame = {
    id: string;
    status: 'active' | 'ready_for_visit' | 'onsite_active' | 'completed';
    mode: 'individual' | 'family' | 'team';
    name: string | null;
    score: number;
    members: { displayName: string; role: string }[];
    journeyTimeline: TimelineStep[];
    currentStage: {
        index: number | null;
        title: string;
        phase: 'online' | 'physical' | 'completed';
        phaseLabel: string;
        instruction: string;
        completedSteps: number;
        totalSteps: number;
    };
    commerce: {
        optionalAdsCompleted: number;
        commercialRedemptions: number;
        issuedStageRewards: number;
        finalTier: 'base' | 'boosted' | 'premium';
        finalTierLabel: string;
        nextBoostRequirement: string | null;
    };
};

type CurrentOffer = {
    id: string;
    kind: 'ad';
    title: string;
    bodyCopy: string | null;
    partnerName: string | null;
    bonusPoints: number | null;
    requiredSeconds: number | null;
    stageIndex: number | null;
    checkpointKey: string | null;
    commercialModel: string | null;
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
        isOnlineGame: boolean;
        hasVisit: boolean;
        latestVisitId: string | null;
        experienceUrl: string | null;
        experienceLabel: string;
        lastVisitedAt: string | null;
        completedMissions: number;
        totalMissions: number;
        progressPercent: number;
    }[];
    rewardCatalog: {
        id: string;
        name: string;
        rewardTypeLabel: string;
        campaignName: string | null;
        partnerName: string | null;
        pointCost: number | null;
        remainingStock: number | null;
        tier: string | null;
    }[];
    rewardWallet: {
        id: string;
        status: string;
        awardedAt: string | null;
        expiresAt: string | null;
        campaignName: string | null;
        rewardName: string | null;
        rewardTypeLabel: string;
        pointCost: number | null;
        partnerName: string | null;
        redemptionCode: string | null;
        redemptionStatus: string | null;
        redeemedAt: string | null;
    }[];
    history: {
        id: string;
        venueName: string | null;
        campaignName: string | null;
        hubName: string | null;
        status: string;
        occurredAt: string;
        points: number;
    }[];
    partners: {
        name: string;
        rewardName: string | null;
        rewardTypeLabel: string;
        campaignName: string | null;
        status: string;
        redeemedAt: string | null;
    }[];
    nextAction: {
        label: string;
        description: string;
        href: string | null;
    };
    currentOffer: CurrentOffer | null;
};

type Props = {
    participant: Participant;
    latestVisit: LatestVisit | null;
    missionFlow: MissionFlow;
    onlineGame: OnlineGame | null;
    journey: Journey;
    viewerMode: ViewerMode;
};

type SharedProps = {
    flash?: { success?: string };
};

const heroImage = '/images/ecopark/proposal/participant-route-card-3-2.jpg';

const rewardStatusLabels: Record<string, string> = {
    awarded: 'آماده استفاده',
    reserved: 'رزرو شده',
    redeemed: 'مصرف شده',
    confirmed: 'تحویل شده',
    expired: 'منقضی شده',
};

function faNumber(value: number) {
    return value.toLocaleString('fa-IR');
}

function formatDate(value: string) {
    return new Intl.DateTimeFormat('fa-IR', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
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
    const otherCampaigns = journey.activeCampaigns.filter(
        (campaign) => !campaign.isOnlineGame,
    );

    return (
        <main
            dir="rtl"
            className="mx-auto flex w-full max-w-[1500px] flex-1 flex-col gap-5 overflow-x-hidden p-3 sm:p-5"
        >
            <Head title="پنل من" />

            <header className="flex flex-col gap-3 rounded-2xl border border-sidebar-border/70 bg-background p-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p className="text-xs font-medium text-emerald-700">
                        پنل من در اکسپلوریا
                    </p>
                    <h1 className="mt-1 text-xl font-black sm:text-2xl">
                        سلام {participant.name}؛ اینجا وضعیت واقعی مسیر شماست
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        اقدام جاری، پاداش‌ها و سوابق از یکدیگر جدا شده‌اند تا
                        دقیقاً بدانید قدم بعدی چیست.
                    </p>
                </div>
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
            </header>

            {flash?.success ? (
                <div className="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-900">
                    {flash.success}
                </div>
            ) : null}

            {viewerMode.canPreviewVisitors ? (
                <AdminPreview viewerMode={viewerMode} />
            ) : null}

            {participant.publicStatus !== 'participant' ? (
                <ParticipationSetup />
            ) : null}

            {onlineGame && latestVisit ? (
                <CurrentJourney
                    game={onlineGame}
                    visit={latestVisit}
                    participant={participant}
                    action={journey.nextAction}
                    offer={journey.currentOffer}
                />
            ) : (
                <NoCurrentJourney
                    latestVisit={latestVisit}
                    action={journey.nextAction}
                />
            )}

            <section className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <StatCard
                    icon={<Sparkles className="size-5 text-amber-600" />}
                    label="امتیاز کسب‌شده"
                    value={journey.points.earned}
                    hint="امتیاز مراحل و پیشنهادهای اختیاری"
                />
                <StatCard
                    icon={<WalletCards className="size-5 text-emerald-700" />}
                    label="اعتبار فعلی"
                    value={journey.points.stored}
                    hint="پس از کسر پاداش‌های مصرف‌شده"
                />
                <StatCard
                    icon={<Gift className="size-5 text-rose-600" />}
                    label="پاداش صادرشده"
                    value={journey.rewardWallet.length}
                    hint="کدها و مشوق‌های داخل کیف"
                />
                <StatCard
                    icon={<TicketCheck className="size-5 text-sky-700" />}
                    label="مصرف تأییدشده"
                    value={journey.points.redeemedRewards}
                    hint="تبدیل واقعی در واحد عضو"
                />
            </section>

            <section className="grid gap-5 xl:grid-cols-[1.05fr_0.95fr]">
                <RewardWallet rewards={journey.rewardWallet} />
                <RewardEconomy
                    game={onlineGame}
                    catalog={journey.rewardCatalog}
                />
            </section>

            <Campaigns campaigns={otherCampaigns} />

            <section className="grid gap-5 xl:grid-cols-2">
                <HistoryPanel
                    history={journey.history}
                    latestVisitId={latestVisit?.id ?? null}
                />
                <ProfileAndGuide
                    participant={participant}
                    partners={journey.partners}
                    missionFlow={missionFlow}
                />
            </section>
        </main>
    );
}

function CurrentJourney({
    game,
    visit,
    participant,
    action,
    offer,
}: {
    game: OnlineGame;
    visit: LatestVisit;
    participant: Participant;
    action: Journey['nextAction'];
    offer: CurrentOffer | null;
}) {
    const progress = Math.round(
        (game.currentStage.completedSteps / game.currentStage.totalSteps) * 100,
    );

    return (
        <section className="overflow-hidden rounded-3xl border border-emerald-200 bg-white shadow-sm">
            <div className="grid lg:grid-cols-[1.25fr_0.75fr]">
                <div className="order-2 p-4 sm:p-6 lg:order-1">
                    <div className="flex flex-wrap items-center gap-2">
                        <span className="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                            کمپین جاری
                        </span>
                        <span className="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-700">
                            {game.currentStage.phaseLabel}
                        </span>
                        <span className="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-700">
                            {participant.modeLabel}
                        </span>
                    </div>

                    <p className="mt-5 text-sm text-slate-500">قدم بعدی شما</p>
                    <h2 className="mt-1 text-2xl font-black text-slate-950 sm:text-3xl">
                        {game.currentStage.title}
                    </h2>
                    <p className="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                        {game.currentStage.instruction}
                    </p>

                    <div className="mt-5">
                        <div className="flex items-center justify-between text-xs text-slate-600">
                            <span>
                                {faNumber(game.currentStage.completedSteps)} از{' '}
                                {faNumber(game.currentStage.totalSteps)} مرحله
                            </span>
                            <span>{faNumber(progress)}٪</span>
                        </div>
                        <div className="mt-2 h-2 overflow-hidden rounded-full bg-slate-100">
                            <div
                                className="h-full rounded-full bg-emerald-600 transition-all"
                                style={{ width: `${progress}%` }}
                            />
                        </div>
                    </div>

                    {action.href ? (
                        <Button
                            asChild
                            size="lg"
                            className="mt-6 min-h-12 w-full text-base sm:w-auto"
                            data-test="current-journey-action"
                        >
                            <Link href={action.href}>
                                {game.status === 'completed' ? (
                                    <Gift className="size-5" />
                                ) : (
                                    <Play className="size-5" />
                                )}
                                {action.label}
                            </Link>
                        </Button>
                    ) : null}

                    <Timeline steps={game.journeyTimeline} />

                    {offer ? (
                        <StageOffer offer={offer} actionHref={action.href} />
                    ) : null}
                </div>

                <div className="relative order-1 min-h-52 overflow-hidden bg-slate-950 lg:order-2 lg:min-h-full">
                    <img
                        src={heroImage}
                        alt=""
                        className="absolute inset-0 size-full object-cover opacity-70"
                    />
                    <div className="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent" />
                    <div className="absolute inset-x-0 bottom-0 p-5 text-white">
                        <p className="text-xs text-emerald-200">
                            {visit.venueName ?? 'مکان کمپین'}
                        </p>
                        <p className="mt-1 text-xl font-black">
                            {visit.campaignName ?? 'مسیر اکسپلوریا'}
                        </p>
                        <p className="mt-2 text-xs text-slate-200">
                            امتیاز گروه: {faNumber(game.score)} · اعضا:{' '}
                            {faNumber(game.members.length)}
                        </p>
                    </div>
                </div>
            </div>
        </section>
    );
}

function Timeline({ steps }: { steps: TimelineStep[] }) {
    return (
        <div className="mt-7">
            <div className="mb-3 flex items-center gap-2">
                <Compass className="size-4 text-emerald-700" />
                <h3 className="text-sm font-bold">نقشه پیشرفت واقعی</h3>
            </div>
            <ol className="grid gap-2 sm:grid-cols-3 xl:grid-cols-5">
                {steps.map((step) => (
                    <li
                        key={step.index}
                        className={`flex min-h-16 items-center gap-2 rounded-xl border p-2.5 ${
                            step.status === 'available'
                                ? 'border-amber-300 bg-amber-50 text-amber-950'
                                : step.status === 'completed'
                                  ? 'border-emerald-200 bg-emerald-50 text-emerald-950'
                                  : 'border-slate-200 bg-slate-50 text-slate-400'
                        }`}
                    >
                        <span
                            className={`grid size-8 shrink-0 place-items-center rounded-full text-xs font-black ${
                                step.status === 'available'
                                    ? 'bg-amber-400 text-slate-950'
                                    : step.status === 'completed'
                                      ? 'bg-emerald-600 text-white'
                                      : 'bg-slate-200 text-slate-500'
                            }`}
                        >
                            {step.status === 'completed' ? (
                                <Check className="size-4" />
                            ) : step.status === 'locked' ? (
                                <LockKeyhole className="size-3.5" />
                            ) : (
                                faNumber(step.index)
                            )}
                        </span>
                        <span className="min-w-0">
                            <span className="block text-[10px] opacity-70">
                                {step.phase === 'online' ? 'آنلاین' : 'حضوری'}
                            </span>
                            <span className="line-clamp-2 text-xs font-bold">
                                {step.title}
                            </span>
                        </span>
                    </li>
                ))}
            </ol>
        </div>
    );
}

function StageOffer({
    offer,
    actionHref,
}: {
    offer: CurrentOffer;
    actionHref: string | null;
}) {
    return (
        <aside className="mt-5 rounded-2xl border border-violet-200 bg-violet-50 p-4">
            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div className="flex gap-3">
                    <span className="grid size-10 shrink-0 place-items-center rounded-xl bg-violet-600 text-white">
                        <Megaphone className="size-5" />
                    </span>
                    <div>
                        <div className="flex flex-wrap items-center gap-2">
                            <p className="text-sm font-black text-violet-950">
                                پیشنهاد حمایت‌شده همین مرحله
                            </p>
                            <span className="rounded-full bg-white px-2 py-1 text-[10px] text-violet-700">
                                کاملاً اختیاری
                            </span>
                        </div>
                        <p className="mt-1 text-sm font-bold text-violet-950">
                            {offer.title}
                        </p>
                        {offer.bodyCopy ? (
                            <p className="mt-1 line-clamp-2 text-xs leading-6 text-violet-800">
                                {offer.bodyCopy}
                            </p>
                        ) : null}
                        <p className="mt-1 text-xs leading-6 text-violet-800">
                            {offer.partnerName ?? 'حامی کمپین'} ·{' '}
                            {offer.requiredSeconds
                                ? `${faNumber(offer.requiredSeconds)} ثانیه`
                                : 'محتوای کوتاه'}{' '}
                            · تا {faNumber(offer.bonusPoints ?? 0)} امتیاز
                        </p>
                    </div>
                </div>
                {actionHref ? (
                    <Button asChild variant="outline" className="bg-white">
                        <Link href={actionHref}>مشاهده در همین مرحله</Link>
                    </Button>
                ) : null}
            </div>
        </aside>
    );
}

function RewardEconomy({
    game,
    catalog,
}: {
    game: OnlineGame | null;
    catalog: Journey['rewardCatalog'];
}) {
    return (
        <section className="rounded-2xl border border-sidebar-border/70 bg-background p-4 sm:p-5">
            <div className="flex items-center gap-2">
                <Store className="size-5 text-amber-700" />
                <h2 className="font-black">چگونه پاداش قوی‌تر می‌شود؟</h2>
            </div>
            <p className="mt-2 text-sm leading-7 text-muted-foreground">
                کشف مکان‌ها امتیاز اصلی را می‌دهد. مشاهده تبلیغ یا خرید اجباری
                نیست؛ اما تعامل اختیاری و مصرف واقعی در واحد عضو، سطح پاداش
                پایانی را تقویت می‌کند.
            </p>

            {game ? (
                <>
                    <div className="mt-4 grid grid-cols-3 gap-2 text-center">
                        <MiniMetric
                            value={game.commerce.optionalAdsCompleted}
                            label="پیشنهاد کامل"
                        />
                        <MiniMetric
                            value={game.commerce.commercialRedemptions}
                            label="مصرف فروشگاهی"
                        />
                        <MiniMetric
                            value={game.commerce.issuedStageRewards}
                            label="مشوق صادرشده"
                        />
                    </div>
                    <div className="mt-4 rounded-xl bg-amber-50 p-3 text-sm text-amber-950">
                        <p className="font-black">
                            سطح فعلی: {game.commerce.finalTierLabel}
                        </p>
                        {game.commerce.nextBoostRequirement ? (
                            <p className="mt-1 text-xs leading-6 text-amber-800">
                                {game.commerce.nextBoostRequirement}
                            </p>
                        ) : (
                            <p className="mt-1 text-xs text-amber-800">
                                بالاترین سطح سبد مشوق برای این مسیر فعال است.
                            </p>
                        )}
                    </div>
                </>
            ) : null}

            <details className="mt-4 rounded-xl border border-sidebar-border/70 p-3">
                <summary className="cursor-pointer text-sm font-bold">
                    مشاهده پاداش‌های قابل دستیابی ({faNumber(catalog.length)})
                </summary>
                <div className="mt-3 grid gap-2">
                    {catalog.length === 0 ? (
                        <EmptyBox text="هنوز پاداش فعالی برای این کمپین منتشر نشده است." />
                    ) : (
                        catalog.map((reward) => (
                            <div
                                key={reward.id}
                                className="rounded-xl bg-muted/40 p-3 text-sm"
                            >
                                <div className="flex items-start justify-between gap-3">
                                    <div>
                                        <p className="font-bold">
                                            {reward.name}
                                        </p>
                                        <p className="mt-1 text-xs text-muted-foreground">
                                            {reward.rewardTypeLabel} ·{' '}
                                            {reward.partnerName ?? 'اکسپلوریا'}
                                        </p>
                                    </div>
                                    <span className="shrink-0 rounded-full bg-background px-2 py-1 text-xs">
                                        {reward.pointCost
                                            ? `${faNumber(reward.pointCost)} امتیاز`
                                            : 'مرحله‌ای'}
                                    </span>
                                </div>
                            </div>
                        ))
                    )}
                </div>
            </details>
        </section>
    );
}

function RewardWallet({ rewards }: { rewards: Journey['rewardWallet'] }) {
    return (
        <section className="rounded-2xl border border-sidebar-border/70 bg-background p-4 sm:p-5">
            <div className="flex items-center gap-2">
                <WalletCards className="size-5 text-emerald-700" />
                <h2 className="font-black">کیف پاداش من</h2>
                <span className="mr-auto rounded-full bg-emerald-50 px-2.5 py-1 text-xs text-emerald-800">
                    {faNumber(rewards.length)} مورد
                </span>
            </div>
            <p className="mt-2 text-sm text-muted-foreground">
                فقط پاداش‌های واقعاً صادرشده و کد قابل مصرف در این بخش هستند.
            </p>
            <div className="mt-4 grid gap-3 sm:grid-cols-2">
                {rewards.length === 0 ? (
                    <div className="sm:col-span-2">
                        <EmptyBox text="هنوز پاداشی صادر نشده است؛ مسیر جاری را ادامه دهید." />
                    </div>
                ) : (
                    rewards.map((reward) => (
                        <article
                            key={reward.id}
                            className="rounded-xl border border-sidebar-border/70 p-3"
                        >
                            <div className="flex items-start gap-2">
                                <BadgeCheck className="mt-0.5 size-5 shrink-0 text-emerald-600" />
                                <div className="min-w-0">
                                    <p className="font-bold">
                                        {reward.rewardName ?? 'پاداش کمپین'}
                                    </p>
                                    <p className="mt-1 text-xs leading-6 text-muted-foreground">
                                        {reward.rewardTypeLabel} · محل مصرف:{' '}
                                        {reward.partnerName ?? 'اکسپلوریا'} ·{' '}
                                        {rewardStatusLabels[reward.status] ??
                                            reward.status}
                                    </p>
                                </div>
                            </div>
                            {reward.redemptionCode ? (
                                <div className="mt-3 rounded-lg bg-slate-950 px-3 py-2 text-center text-white">
                                    <p className="text-[10px] text-slate-300">
                                        کد ارائه به واحد عضو
                                    </p>
                                    <p
                                        dir="ltr"
                                        className="mt-1 font-mono text-lg font-black tracking-wider"
                                    >
                                        {reward.redemptionCode}
                                    </p>
                                </div>
                            ) : null}
                            {reward.expiresAt ? (
                                <p className="mt-2 text-[11px] text-muted-foreground">
                                    اعتبار تا {formatDate(reward.expiresAt)}
                                </p>
                            ) : null}
                        </article>
                    ))
                )}
            </div>
        </section>
    );
}

function Campaigns({ campaigns }: { campaigns: Journey['activeCampaigns'] }) {
    if (campaigns.length === 0) {
        return null;
    }

    return (
        <section className="rounded-2xl border border-sidebar-border/70 bg-background p-4 sm:p-5">
            <div className="flex items-center gap-2">
                <QrCode className="size-5 text-sky-700" />
                <div>
                    <h2 className="font-black">کمپین‌های دیگر</h2>
                    <p className="mt-1 text-xs text-muted-foreground">
                        این بخش برای شروع یک تجربه دیگر است؛ با مسیر جاری اشتباه
                        نگیرید.
                    </p>
                </div>
            </div>
            <div className="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                {campaigns.map((campaign) => (
                    <article
                        key={campaign.id}
                        className="rounded-xl border border-sidebar-border/70 p-4"
                    >
                        <p className="font-bold">{campaign.name}</p>
                        <p className="mt-1 text-xs text-muted-foreground">
                            {campaign.venueName ?? 'مکان کمپین'} ·{' '}
                            {campaign.city ?? 'تهران'}
                        </p>
                        {campaign.hasVisit && campaign.totalMissions > 0 ? (
                            <div className="mt-3">
                                <div className="h-1.5 overflow-hidden rounded-full bg-muted">
                                    <div
                                        className="h-full bg-sky-600"
                                        style={{
                                            width: `${campaign.progressPercent}%`,
                                        }}
                                    />
                                </div>
                                <p className="mt-1 text-[11px] text-muted-foreground">
                                    سابقه شما:{' '}
                                    {faNumber(campaign.progressPercent)}٪
                                </p>
                            </div>
                        ) : null}
                        <div className="mt-4">
                            {campaign.experienceUrl ? (
                                <Button asChild size="sm" variant="outline">
                                    <Link href={campaign.experienceUrl}>
                                        {campaign.experienceLabel}
                                    </Link>
                                </Button>
                            ) : campaign.scanUrl ? (
                                <Button asChild size="sm" variant="outline">
                                    <Link href={campaign.scanUrl}>
                                        شروع با راهنمای QR
                                    </Link>
                                </Button>
                            ) : (
                                <span className="text-xs text-muted-foreground">
                                    شروع این کمپین فعلاً در دسترس نیست
                                </span>
                            )}
                        </div>
                    </article>
                ))}
            </div>
        </section>
    );
}

function HistoryPanel({
    history,
    latestVisitId,
}: {
    history: Journey['history'];
    latestVisitId: string | null;
}) {
    return (
        <InfoPanel
            icon={<History className="size-5 text-slate-700" />}
            title="سوابق من"
            subtitle="این بخش فقط سابقه است و دکمه ادامه مسیر ندارد."
        >
            {history.length === 0 ? (
                <EmptyBox text="هنوز سابقه‌ای ثبت نشده است." />
            ) : (
                history.map((visit) => (
                    <div
                        key={visit.id}
                        className="rounded-xl border border-sidebar-border/70 p-3 text-sm"
                    >
                        <div className="flex items-start justify-between gap-2">
                            <p className="font-bold">
                                {visit.campaignName ?? 'کمپین'} ·{' '}
                                {visit.venueName ?? 'مکان'}
                            </p>
                            {visit.id === latestVisitId ? (
                                <span className="shrink-0 rounded-full bg-emerald-50 px-2 py-1 text-[10px] text-emerald-800">
                                    آخرین ثبت
                                </span>
                            ) : null}
                        </div>
                        <p className="mt-1 text-xs text-muted-foreground">
                            {visit.hubName ?? 'مسیر عمومی'} ·{' '}
                            {formatDate(visit.occurredAt)} ·{' '}
                            {faNumber(visit.points)} امتیاز
                        </p>
                    </div>
                ))
            )}
        </InfoPanel>
    );
}

function ProfileAndGuide({
    participant,
    partners,
    missionFlow,
}: {
    participant: Participant;
    partners: Journey['partners'];
    missionFlow: MissionFlow;
}) {
    return (
        <InfoPanel
            icon={<UsersRound className="size-5 text-violet-700" />}
            title="پروفایل، راهنما و تعامل تجاری"
            subtitle="اطلاعات زمینه‌ای؛ اقدام اصلی همیشه در بالای پنل است."
        >
            <div className="grid gap-2 rounded-xl bg-muted/40 p-3 text-sm sm:grid-cols-2">
                <InfoRow label="نوع شرکت" value={participant.modeLabel} />
                <InfoRow
                    label="تیم/خانواده"
                    value={participant.teamName ?? 'ثبت نشده'}
                />
                <InfoRow
                    label="تعداد اعضا"
                    value={faNumber(Math.max(1, participant.members.length))}
                />
                <InfoRow label="وضعیت" value={participant.publicStatusLabel} />
            </div>

            <details className="rounded-xl border border-sidebar-border/70 p-3">
                <summary className="cursor-pointer text-sm font-bold">
                    راهنمای خیلی کوتاه پنل
                </summary>
                <ol className="mt-3 grid gap-2 text-xs leading-6 text-muted-foreground">
                    <li>۱. فقط دکمه بزرگ بالای پنل، ادامه مسیر جاری است.</li>
                    <li>
                        ۲. QR حضوری باید از استند واقعیِ اعلام‌شده در همان مرحله
                        اسکن شود.
                    </li>
                    <li>
                        ۳. پیشنهاد حمایت‌شده اختیاری است و بدون مشاهده آن نیز
                        بازی ادامه دارد.
                    </li>
                    <li>
                        ۴. کدهای صادرشده در «کیف پاداش من» به واحد عضو ارائه
                        می‌شوند.
                    </li>
                </ol>
            </details>

            {partners.length > 0 ? (
                <details className="rounded-xl border border-sidebar-border/70 p-3">
                    <summary className="cursor-pointer text-sm font-bold">
                        سوابق مصرف در واحدهای عضو ({faNumber(partners.length)})
                    </summary>
                    <div className="mt-3 grid gap-2">
                        {partners.map((partner, index) => (
                            <div
                                key={`${partner.name}-${index}`}
                                className="rounded-lg bg-muted/40 p-2.5 text-xs"
                            >
                                <p className="font-bold">{partner.name}</p>
                                <p className="mt-1 text-muted-foreground">
                                    {partner.rewardName ??
                                        partner.rewardTypeLabel}{' '}
                                    · وضعیت: {partner.status}
                                </p>
                            </div>
                        ))}
                    </div>
                </details>
            ) : null}

            {!missionFlow ? null : (
                <p className="rounded-xl bg-sky-50 p-3 text-xs leading-6 text-sky-900">
                    کمپین عمومی شما{' '}
                    {faNumber(missionFlow.stats.completedMissions)} مأموریت
                    تکمیل‌شده دارد.
                </p>
            )}
        </InfoPanel>
    );
}

function ParticipationSetup() {
    return (
        <section className="rounded-2xl border border-sky-200 bg-sky-50 p-4">
            <div className="grid gap-4 lg:grid-cols-[1fr_auto] lg:items-center">
                <div>
                    <h2 className="font-black">فعال‌سازی مشارکت</h2>
                    <p className="mt-1 text-sm leading-7 text-sky-900">
                        نوع مشارکت را یک‌بار انتخاب کنید. تنظیم دقیق فردی،
                        خانوادگی یا تیمی داخل خود کمپین نیز قابل تکمیل است.
                    </p>
                </div>
                <Form
                    action="/participant/participation"
                    method="post"
                    options={{ preserveScroll: true }}
                    className="grid gap-2 sm:grid-cols-[12rem_auto]"
                >
                    {({ processing, errors }) => (
                        <>
                            <div>
                                <select
                                    name="mode"
                                    defaultValue="individual"
                                    className="min-h-10 w-full rounded-md border border-input bg-background px-3 text-sm"
                                >
                                    <option value="individual">انفرادی</option>
                                    <option value="family">خانوادگی</option>
                                    <option value="team">تیمی / گروهی</option>
                                </select>
                                {errors.mode ? (
                                    <p className="mt-1 text-xs text-destructive">
                                        {errors.mode}
                                    </p>
                                ) : null}
                            </div>
                            <Button type="submit" disabled={processing}>
                                تأیید مشارکت
                            </Button>
                        </>
                    )}
                </Form>
            </div>
        </section>
    );
}

function NoCurrentJourney({
    latestVisit,
    action,
}: {
    latestVisit: LatestVisit | null;
    action: Journey['nextAction'];
}) {
    return (
        <section className="rounded-3xl border border-dashed border-sidebar-border bg-background p-6 text-center">
            <span className="mx-auto grid size-14 place-items-center rounded-2xl bg-muted">
                <QrCode className="size-7 text-muted-foreground" />
            </span>
            <h2 className="mt-4 text-xl font-black">
                {latestVisit
                    ? 'مسیر عمومی آماده ادامه است'
                    : 'هنوز کمپین جاری ندارید'}
            </h2>
            <p className="mx-auto mt-2 max-w-xl text-sm leading-7 text-muted-foreground">
                {action.description}
            </p>
            {action.href ? (
                <Button asChild className="mt-4">
                    <Link href={action.href}>{action.label}</Link>
                </Button>
            ) : null}
        </section>
    );
}

function AdminPreview({ viewerMode }: { viewerMode: ViewerMode }) {
    return (
        <section className="rounded-xl border border-sky-200 bg-sky-50 p-3 text-sm">
            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p className="font-bold">پیش‌نمایش پشتیبانی</p>
                    <p className="mt-1 text-xs text-sky-800">
                        وضعیت یک بازدیدکننده واقعی را بدون خروج از حساب مدیر
                        بررسی کنید.
                    </p>
                </div>
                <select
                    className="min-h-10 rounded-md border border-input bg-background px-3 text-sm"
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
                    {viewerMode.previewOptions.map((option) => (
                        <option key={option.id} value={option.id}>
                            {option.name} ({faNumber(option.visitsCount)}{' '}
                            بازدید)
                        </option>
                    ))}
                </select>
            </div>
        </section>
    );
}

function StatCard({
    icon,
    label,
    value,
    hint,
}: {
    icon: ReactNode;
    label: string;
    value: number;
    hint: string;
}) {
    return (
        <article className="rounded-2xl border border-sidebar-border/70 bg-background p-4">
            <div className="flex items-center gap-2">
                {icon}
                <p className="text-sm font-bold">{label}</p>
            </div>
            <p className="mt-3 text-2xl font-black">{faNumber(value)}</p>
            <p className="mt-1 text-xs text-muted-foreground">{hint}</p>
        </article>
    );
}

function MiniMetric({ value, label }: { value: number; label: string }) {
    return (
        <div className="rounded-xl bg-muted/40 p-2">
            <p className="text-lg font-black">{faNumber(value)}</p>
            <p className="mt-1 text-[10px] text-muted-foreground">{label}</p>
        </div>
    );
}

function InfoRow({ label, value }: { label: string; value: string }) {
    return (
        <div className="flex items-center justify-between gap-3">
            <span className="text-muted-foreground">{label}</span>
            <span className="font-bold">{value}</span>
        </div>
    );
}

function EmptyBox({ text }: { text: string }) {
    return (
        <p className="rounded-xl border border-dashed border-sidebar-border/70 p-3 text-sm leading-7 text-muted-foreground">
            {text}
        </p>
    );
}

function InfoPanel({
    icon,
    title,
    subtitle,
    children,
}: {
    icon: ReactNode;
    title: string;
    subtitle: string;
    children: ReactNode;
}) {
    return (
        <section className="rounded-2xl border border-sidebar-border/70 bg-background p-4 sm:p-5">
            <div className="flex items-start gap-2">
                {icon}
                <div>
                    <h2 className="font-black">{title}</h2>
                    <p className="mt-1 text-xs text-muted-foreground">
                        {subtitle}
                    </p>
                </div>
            </div>
            <div className="mt-4 grid gap-3">{children}</div>
        </section>
    );
}
