import { Form, Head, Link } from '@inertiajs/react';
import {
    BadgeCheck,
    CheckCircle2,
    Compass,
    Gift,
    Lock,
    MapPin,
    Play,
    QrCode,
    Route,
    ShieldCheck,
    Sparkles,
    Target,
    Trophy,
} from 'lucide-react';
import { useMemo, useState } from 'react';

type MissionStatus =
    | 'available'
    | 'started'
    | 'completed'
    | 'locked'
    | 'preview';

type ServerMissionNode = {
    id: string;
    code: string;
    title: string;
    place: string | null;
    hubName: string | null;
    touchpointLabel: string | null;
    clue: string;
    mission: string | null;
    missionType: string;
    triggerType: string;
    completionEvidence: string | null;
    reward: string | null;
    points: number;
    treasureName: string | null;
    cycleStep: { index: number | null; label: string | null };
    unlockMinPoints: number | null;
};

type MissionItem = {
    id: string;
    code: string;
    title: string;
    description: string | null;
    completionEvidence: string;
    successMessage: string | null;
    cycleStep: { index: number | null; label: string | null };
    status: Exclude<MissionStatus, 'preview'>;
    isLocked: boolean;
    canStart: boolean;
    canComplete: boolean;
    points: number;
    missionType: string;
    triggerType: string;
    hubName: string | null;
    touchpointLabel: string | null;
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

type GameOffer = {
    id: string;
    kind: 'ad' | 'reward';
    adRequestId: string | null;
    title: string;
    partnerName: string | null;
    bodyCopy: string | null;
    ctaText: string;
    targetUrl: string | null;
    assetUrl: string | null;
    placementType: string | null;
    points: number | null;
    terms: string | null;
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

type GamePayload = {
    campaign: {
        id: string;
        code: string;
        name: string;
        venueName: string | null;
        city: string | null;
        scanUrl: string | null;
    } | null;
    entryQr: {
        code: string;
        label: string | null;
        scanUrl: string;
    } | null;
    missionNodes: ServerMissionNode[];
    gameOffers: GameOffer[];
    latestVisit: {
        id: string;
        occurredAt: string;
        showUrl: string;
    } | null;
    missionFlow: MissionFlow | null;
    visitorState: {
        isAuthenticated: boolean;
        hasLinkedVisit: boolean;
        participantDashboardUrl: string | null;
    };
};

type JourneyNode = {
    id: string;
    code: string;
    title: string;
    step: number;
    stepLabel: string | null;
    place: string;
    hubName: string | null;
    touchpointLabel: string | null;
    description: string;
    completionEvidence: string;
    reward: string;
    points: number;
    missionType: string;
    triggerType: string;
    status: MissionStatus;
    canStart: boolean;
    canComplete: boolean;
    gameOffer: GameOffer | null;
};

type Props = {
    game: GamePayload;
};

const statusLabels: Record<MissionStatus, string> = {
    available: 'مرحله جاری',
    started: 'در حال انجام',
    completed: 'تکمیل‌شده',
    locked: 'مرحله آینده',
    preview: 'پیش‌نمایش',
};

function formatFa(value: number) {
    return value.toLocaleString('fa-IR');
}

function offerForIndex(offers: GameOffer[], index: number) {
    if (offers.length === 0) {
        return null;
    }

    return offers[index % offers.length];
}

function buildJourneyNodes(game: GamePayload): JourneyNode[] {
    const progressByCode = new Map(
        (game.missionFlow?.missions ?? []).map((mission) => [
            mission.code,
            mission,
        ]),
    );

    return game.missionNodes.map((node, index) => {
        const progress = progressByCode.get(node.code);
        const touchpointLabel =
            progress?.touchpointLabel ?? node.touchpointLabel;
        const hubName = progress?.hubName ?? node.hubName;
        const step =
            progress?.cycleStep.index ?? node.cycleStep.index ?? index + 1;

        return {
            id: node.id,
            code: node.code,
            title: progress?.title ?? node.title,
            step,
            stepLabel:
                progress?.cycleStep.label ?? node.cycleStep.label ?? null,
            place:
                touchpointLabel ??
                hubName ??
                node.place ??
                game.campaign?.venueName ??
                'مسیر کمپین',
            hubName,
            touchpointLabel,
            description:
                progress?.description ??
                node.clue ??
                node.mission ??
                'راهنمای این مرحله در همین صفحه نمایش داده می‌شود.',
            completionEvidence:
                progress?.completionEvidence ??
                node.completionEvidence ??
                'انجام اقدام مرحله و ثبت آن در اکسپلوریا',
            reward:
                progress?.successMessage ??
                node.reward ??
                (node.treasureName
                    ? `باز شدن گنج: ${node.treasureName}`
                    : `${formatFa(node.points)} امتیاز مسیر`),
            points: progress?.points ?? node.points,
            missionType: progress?.missionType ?? node.missionType,
            triggerType: progress?.triggerType ?? node.triggerType,
            status: progress?.status ?? 'preview',
            canStart: progress?.canStart ?? false,
            canComplete: progress?.canComplete ?? false,
            gameOffer: offerForIndex(game.gameOffers, index),
        };
    });
}

function locationInstruction(node: JourneyNode) {
    if (node.touchpointLabel && node.hubName) {
        return `${node.hubName}، ${node.touchpointLabel}`;
    }

    return node.touchpointLabel ?? node.hubName ?? node.place;
}

export default function EcoParkTreasureGame({ game }: Props) {
    const nodes = useMemo(() => buildJourneyNodes(game), [game]);
    const currentNode =
        nodes.find(
            (node) => node.status === 'available' || node.status === 'started',
        ) ?? null;
    const allCompleted =
        nodes.length > 0 && nodes.every((node) => node.status === 'completed');
    const [reviewedNodeId, setReviewedNodeId] = useState<string | null>(null);
    const reviewedNode = reviewedNodeId
        ? (nodes.find(
              (node) =>
                  node.id === reviewedNodeId && node.status === 'completed',
          ) ?? null)
        : null;
    const displayNode =
        reviewedNode ??
        currentNode ??
        nodes.findLast((node) => node.status === 'completed') ??
        nodes[0] ??
        null;
    const completedCount =
        game.missionFlow?.stats.completedMissions ??
        nodes.filter((node) => node.status === 'completed').length;
    const progress =
        nodes.length > 0
            ? Math.round((completedCount / nodes.length) * 100)
            : 0;
    const hasLinkedVisit =
        game.visitorState.hasLinkedVisit && game.latestVisit !== null;

    return (
        <>
            <Head title="مسیر گنج اکوپارک" />
            <main className="min-h-svh bg-[#f6f7f2] text-zinc-950" dir="rtl">
                <header className="border-b border-zinc-200 bg-white/95">
                    <div className="mx-auto flex max-w-6xl items-center justify-between gap-3 px-4 py-3 sm:px-6">
                        <div>
                            <p className="text-xs font-semibold tracking-[0.18em] text-emerald-700">
                                EXPLORIA
                            </p>
                            <p className="mt-1 text-sm font-semibold">
                                {game.campaign?.venueName ?? 'اکوپارک'}
                            </p>
                        </div>
                        {game.visitorState.participantDashboardUrl ? (
                            <Link
                                href={game.visitorState.participantDashboardUrl}
                                className="inline-flex min-h-10 items-center gap-2 rounded-lg border border-zinc-200 bg-white px-3 text-sm font-medium text-zinc-700 hover:bg-zinc-50"
                            >
                                <Compass className="size-4" />
                                پنل من
                            </Link>
                        ) : null}
                    </div>
                </header>

                <div className="mx-auto grid max-w-6xl gap-5 px-4 py-5 sm:px-6 sm:py-7">
                    <section className="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
                        <div className="grid lg:grid-cols-[1.1fr_0.9fr]">
                            <div className="p-5 sm:p-7">
                                <div className="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-900">
                                    <Route className="size-4" />
                                    مسیر رسمی کمپین
                                </div>
                                <h1 className="mt-4 text-2xl leading-10 font-bold sm:text-4xl">
                                    نقشه گنج اکوپارک
                                </h1>
                                <p className="mt-3 max-w-2xl text-sm leading-7 text-zinc-600 sm:text-base sm:leading-8">
                                    در هر لحظه فقط یک مرحله فعال است. کارت «کار
                                    بعدی شما» مکان، اقدام لازم و روش تأیید همان
                                    مرحله را نشان می‌دهد.
                                </p>
                            </div>
                            <div className="relative min-h-44 overflow-hidden bg-zinc-900 lg:min-h-full">
                                <img
                                    src="/images/ecopark/hero.webp"
                                    alt="نمای اکوپارک"
                                    className="absolute inset-0 h-full w-full object-cover"
                                />
                                <div className="absolute inset-0 bg-gradient-to-l from-emerald-950/75 to-zinc-950/35" />
                                <div className="relative flex h-full min-h-44 items-end p-5 text-white">
                                    <div className="grid grid-cols-3 gap-5">
                                        <div>
                                            <p className="text-xs text-white/70">
                                                پیشرفت
                                            </p>
                                            <p className="mt-1 text-xl font-bold">
                                                {formatFa(progress)}٪
                                            </p>
                                        </div>
                                        <div>
                                            <p className="text-xs text-white/70">
                                                مرحله
                                            </p>
                                            <p className="mt-1 text-xl font-bold">
                                                {formatFa(completedCount)} /{' '}
                                                {formatFa(nodes.length)}
                                            </p>
                                        </div>
                                        <div>
                                            <p className="text-xs text-white/70">
                                                امتیاز
                                            </p>
                                            <p className="mt-1 text-xl font-bold">
                                                {formatFa(
                                                    game.missionFlow?.stats
                                                        .totalPoints ?? 0,
                                                )}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    {!hasLinkedVisit ? (
                        <GuestStart game={game} nodes={nodes} />
                    ) : (
                        <>
                            <section className="grid gap-5 lg:grid-cols-[1.15fr_0.85fr] lg:items-start">
                                {allCompleted ? (
                                    <JourneyCompleted game={game} />
                                ) : displayNode ? (
                                    <CurrentMissionCard
                                        game={game}
                                        node={displayNode}
                                        currentNode={currentNode}
                                        onReturnToCurrent={() =>
                                            setReviewedNodeId(null)
                                        }
                                    />
                                ) : null}

                                <JourneyMap
                                    nodes={nodes}
                                    currentNode={currentNode}
                                    selectedNode={displayNode}
                                    onSelectCompleted={setReviewedNodeId}
                                />
                            </section>

                            <RewardWallet
                                rewards={game.missionFlow?.rewards ?? []}
                            />
                        </>
                    )}
                </div>
            </main>
        </>
    );
}

function GuestStart({
    game,
    nodes,
}: {
    game: GamePayload;
    nodes: JourneyNode[];
}) {
    return (
        <section className="grid gap-5 lg:grid-cols-[1.05fr_0.95fr]">
            <div className="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 sm:p-7">
                <div className="flex size-11 items-center justify-center rounded-xl bg-emerald-700 text-white">
                    <QrCode className="size-6" />
                </div>
                <h2 className="mt-4 text-xl font-bold">
                    برای شروع، QR ورودی را اسکن کنید
                </h2>
                <p className="mt-2 text-sm leading-7 text-emerald-950/75">
                    پیشرفت و امتیاز فقط پس از ثبت یک Visit معتبر ذخیره می‌شود.
                    QR در «{game.entryQr?.label ?? 'استند ورودی اصلی'}» قرار
                    دارد.
                </p>
                {game.entryQr ? (
                    <Link
                        href={game.entryQr.scanUrl}
                        className="mt-5 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-emerald-700 px-5 text-sm font-bold text-white hover:bg-emerald-800 sm:w-auto"
                    >
                        <QrCode className="size-5" />
                        ورود با QR رسمی کمپین
                    </Link>
                ) : (
                    <div className="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm leading-7 text-amber-950">
                        QR فعال برای این کمپین در دسترس نیست. از سفیر مستقر در
                        ورودی کمک بگیرید.
                    </div>
                )}
            </div>

            <div className="rounded-2xl border border-zinc-200 bg-white p-5 sm:p-7">
                <h2 className="font-bold">پیش‌نمایش مراحل</h2>
                <p className="mt-2 text-sm leading-7 text-zinc-600">
                    این فهرست فقط برای آشنایی است و هیچ مرحله‌ای را تکمیل
                    نمی‌کند.
                </p>
                <div className="mt-4 grid gap-2">
                    {nodes.map((node) => (
                        <div
                            key={node.id}
                            className="flex items-center gap-3 rounded-xl bg-zinc-50 p-3"
                        >
                            <span className="flex size-9 shrink-0 items-center justify-center rounded-full bg-white text-sm font-bold shadow-sm">
                                {formatFa(node.step)}
                            </span>
                            <div>
                                <p className="text-sm font-semibold">
                                    {node.title}
                                </p>
                                <p className="mt-1 text-xs text-zinc-500">
                                    {node.place}
                                </p>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}

function CurrentMissionCard({
    game,
    node,
    currentNode,
    onReturnToCurrent,
}: {
    game: GamePayload;
    node: JourneyNode;
    currentNode: JourneyNode | null;
    onReturnToCurrent: () => void;
}) {
    const isReviewingCompleted =
        node.status === 'completed' && currentNode?.id !== node.id;

    return (
        <article className="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <div
                className={`border-b p-5 sm:p-6 ${
                    node.status === 'completed'
                        ? 'border-emerald-200 bg-emerald-50'
                        : 'border-amber-200 bg-amber-50'
                }`}
            >
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p className="text-xs font-semibold text-zinc-600">
                            {node.status === 'completed'
                                ? `مرحله ${formatFa(node.step)}`
                                : 'کار بعدی شما'}
                        </p>
                        <h2 className="mt-1 text-xl leading-8 font-bold sm:text-2xl">
                            {node.title}
                        </h2>
                    </div>
                    <span className="inline-flex items-center gap-2 rounded-full bg-white px-3 py-2 text-xs font-semibold shadow-sm">
                        {node.status === 'completed' ? (
                            <CheckCircle2 className="size-4 text-emerald-700" />
                        ) : (
                            <Target className="size-4 text-amber-700" />
                        )}
                        {statusLabels[node.status]}
                    </span>
                </div>
            </div>

            <div className="grid gap-4 p-5 sm:p-6">
                <div className="rounded-xl border border-emerald-200 bg-emerald-50/60 p-4">
                    <div className="flex items-start gap-3">
                        <MapPin className="mt-0.5 size-5 shrink-0 text-emerald-700" />
                        <div>
                            <p className="text-xs font-semibold text-emerald-800">
                                کجا برویم؟
                            </p>
                            <p className="mt-1 font-bold text-emerald-950">
                                {locationInstruction(node)}
                            </p>
                        </div>
                    </div>
                </div>

                <div className="grid gap-3 sm:grid-cols-2">
                    <div className="rounded-xl border border-zinc-200 p-4">
                        <div className="flex items-center gap-2 text-sm font-bold">
                            <Route className="size-4 text-sky-700" />
                            چه کاری انجام دهیم؟
                        </div>
                        <p className="mt-2 text-sm leading-7 text-zinc-600">
                            {node.description}
                        </p>
                    </div>
                    <div className="rounded-xl border border-zinc-200 p-4">
                        <div className="flex items-center gap-2 text-sm font-bold">
                            <ShieldCheck className="size-4 text-violet-700" />
                            چگونه تأیید می‌شود؟
                        </div>
                        <p className="mt-2 text-sm leading-7 text-zinc-600">
                            {node.completionEvidence}
                        </p>
                    </div>
                </div>

                <div className="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4">
                    <Gift className="mt-0.5 size-5 shrink-0 text-amber-700" />
                    <div>
                        <p className="text-xs font-semibold text-amber-800">
                            پاداش این مرحله
                        </p>
                        <p className="mt-1 text-sm font-bold text-amber-950">
                            {node.reward} · {formatFa(node.points)} امتیاز
                        </p>
                    </div>
                </div>

                {node.status === 'completed' && node.gameOffer ? (
                    <CompletedOffer offer={node.gameOffer} />
                ) : null}

                <div className="border-t border-zinc-200 pt-4">
                    {isReviewingCompleted && currentNode ? (
                        <button
                            type="button"
                            onClick={onReturnToCurrent}
                            className="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-zinc-950 px-5 text-sm font-bold text-white hover:bg-zinc-800 sm:w-auto"
                        >
                            <Play className="size-5" />
                            ادامه مرحله {formatFa(currentNode.step)}
                        </button>
                    ) : (
                        <MissionPrimaryAction game={game} node={node} />
                    )}
                </div>
            </div>
        </article>
    );
}

function MissionPrimaryAction({
    game,
    node,
}: {
    game: GamePayload;
    node: JourneyNode;
}) {
    if (node.status === 'completed') {
        return (
            <div className="inline-flex min-h-12 items-center gap-2 rounded-xl bg-emerald-100 px-4 text-sm font-bold text-emerald-900">
                <CheckCircle2 className="size-5" />
                این مرحله تکمیل شده است
            </div>
        );
    }

    if (node.status === 'locked' || node.status === 'preview') {
        return (
            <div className="inline-flex min-h-12 items-center gap-2 rounded-xl bg-zinc-100 px-4 text-sm font-bold text-zinc-600">
                <Lock className="size-5" />
                ابتدا مرحله قبلی را کامل کنید
            </div>
        );
    }

    if (node.triggerType === 'qr_scan') {
        if (node.code === 'scan-entry-qr' && game.entryQr) {
            return (
                <Link
                    href={game.entryQr.scanUrl}
                    className="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-zinc-950 px-5 text-sm font-bold text-white hover:bg-zinc-800 sm:w-auto"
                >
                    <QrCode className="size-5" />
                    اسکن QR همین نقطه
                </Link>
            );
        }

        return (
            <div className="rounded-xl border border-sky-200 bg-sky-50 p-4 text-sm leading-7 text-sky-950">
                QR نصب‌شده در «{node.place}» را با دوربین گوشی اسکن کنید؛ این
                مرحله با دکمه دستی تکمیل نمی‌شود.
            </div>
        );
    }

    if (node.triggerType === 'admin_approval') {
        if (node.status === 'started') {
            return (
                <div className="rounded-xl border border-violet-200 bg-violet-50 p-4 text-sm leading-7 text-violet-950">
                    درخواست شما ثبت شده و این مرحله پس از بررسی مجری کمپین تکمیل
                    می‌شود.
                </div>
            );
        }

        return (
            <Form
                action={`/visits/${game.latestVisit?.id}/missions/${node.id}/start`}
                method="post"
            >
                {({ processing }) => (
                    <button
                        type="submit"
                        disabled={processing}
                        className="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-violet-700 px-5 text-sm font-bold text-white hover:bg-violet-800 disabled:opacity-60 sm:w-auto"
                    >
                        <BadgeCheck className="size-5" />
                        {processing
                            ? 'در حال ثبت...'
                            : 'ارسال مرحله برای تأیید مجری'}
                    </button>
                )}
            </Form>
        );
    }

    const actionLabel =
        node.triggerType === 'manual_check'
            ? 'راهنمای مسیر را پیدا کردم'
            : node.triggerType === 'content_complete'
              ? 'روایت این نقطه را مشاهده کردم'
              : node.status === 'available'
                ? 'شروع این مرحله'
                : 'ثبت تکمیل مرحله';
    const action =
        node.status === 'available' &&
        !['manual_check', 'content_complete'].includes(node.triggerType)
            ? 'start'
            : 'complete';

    return (
        <Form
            action={`/visits/${game.latestVisit?.id}/missions/${node.id}/${action}`}
            method="post"
        >
            {({ processing }) => (
                <button
                    type="submit"
                    disabled={
                        processing ||
                        (action === 'complete' && !node.canComplete)
                    }
                    className="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-zinc-950 px-5 text-sm font-bold text-white hover:bg-zinc-800 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto"
                >
                    {action === 'start' ? (
                        <Play className="size-5" />
                    ) : (
                        <BadgeCheck className="size-5" />
                    )}
                    {processing ? 'در حال ثبت...' : actionLabel}
                </button>
            )}
        </Form>
    );
}

function JourneyMap({
    nodes,
    currentNode,
    selectedNode,
    onSelectCompleted,
}: {
    nodes: JourneyNode[];
    currentNode: JourneyNode | null;
    selectedNode: JourneyNode | null;
    onSelectCompleted: (id: string | null) => void;
}) {
    return (
        <aside className="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm sm:p-6">
            <div className="flex items-center gap-2">
                <Route className="size-5 text-emerald-700" />
                <h2 className="font-bold">نقشه پیشرفت</h2>
            </div>
            <p className="mt-2 text-sm leading-7 text-zinc-600">
                فقط مرحله جاری قابل انجام است. مراحل آینده پس از تکمیل مرحله
                قبلی باز می‌شوند.
            </p>

            <div className="mt-5 grid gap-3">
                {nodes.map((node, index) => {
                    const isCurrent = currentNode?.id === node.id;
                    const isSelected = selectedNode?.id === node.id;
                    const canReview = node.status === 'completed' || isCurrent;

                    return (
                        <button
                            key={node.id}
                            type="button"
                            disabled={!canReview}
                            onClick={() =>
                                onSelectCompleted(
                                    node.status === 'completed'
                                        ? node.id
                                        : null,
                                )
                            }
                            className={`relative flex w-full items-start gap-3 rounded-xl border p-3 text-right transition ${
                                isSelected
                                    ? 'border-zinc-950 bg-zinc-950 text-white'
                                    : node.status === 'completed'
                                      ? 'border-emerald-200 bg-emerald-50 hover:border-emerald-300'
                                      : isCurrent
                                        ? 'border-amber-300 bg-amber-50'
                                        : 'cursor-not-allowed border-zinc-200 bg-zinc-50 text-zinc-500'
                            }`}
                        >
                            {index < nodes.length - 1 ? (
                                <span className="absolute top-11 right-[1.85rem] h-[calc(100%+0.75rem)] w-px bg-zinc-200" />
                            ) : null}
                            <span
                                className={`relative z-10 flex size-9 shrink-0 items-center justify-center rounded-full text-sm font-bold ${
                                    isSelected
                                        ? 'bg-white text-zinc-950'
                                        : node.status === 'completed'
                                          ? 'bg-emerald-600 text-white'
                                          : isCurrent
                                            ? 'bg-amber-400 text-zinc-950'
                                            : 'bg-zinc-200 text-zinc-600'
                                }`}
                            >
                                {node.status === 'completed' ? (
                                    <CheckCircle2 className="size-5" />
                                ) : node.status === 'locked' ? (
                                    <Lock className="size-4" />
                                ) : (
                                    formatFa(node.step)
                                )}
                            </span>
                            <span className="min-w-0 flex-1">
                                <span className="block text-sm font-bold">
                                    {node.title}
                                </span>
                                <span
                                    className={`mt-1 block text-xs ${
                                        isSelected
                                            ? 'text-white/70'
                                            : 'text-zinc-500'
                                    }`}
                                >
                                    {statusLabels[node.status]} · {node.place}
                                </span>
                            </span>
                        </button>
                    );
                })}
            </div>
        </aside>
    );
}

function CompletedOffer({ offer }: { offer: GameOffer }) {
    return (
        <div className="rounded-xl border border-amber-200 bg-amber-50 p-4">
            <div className="flex items-start gap-3">
                <Sparkles className="mt-0.5 size-5 shrink-0 text-amber-700" />
                <div className="min-w-0 flex-1">
                    <p className="text-xs font-semibold text-amber-800">
                        پیشنهاد پس از تکمیل مرحله
                    </p>
                    <p className="mt-1 font-bold text-amber-950">
                        {offer.title}
                    </p>
                    {offer.bodyCopy ? (
                        <p className="mt-2 text-sm leading-7 text-amber-950/75">
                            {offer.bodyCopy}
                        </p>
                    ) : null}
                    {offer.targetUrl ? (
                        <a
                            href={offer.targetUrl}
                            className="mt-3 inline-flex min-h-10 items-center justify-center rounded-lg bg-amber-600 px-4 text-xs font-bold text-white hover:bg-amber-700"
                        >
                            {offer.ctaText}
                        </a>
                    ) : null}
                </div>
            </div>
        </div>
    );
}

function JourneyCompleted({ game }: { game: GamePayload }) {
    return (
        <section className="rounded-2xl border border-emerald-200 bg-emerald-50 p-6 shadow-sm sm:p-8">
            <div className="flex size-12 items-center justify-center rounded-full bg-emerald-700 text-white">
                <Trophy className="size-6" />
            </div>
            <h2 className="mt-4 text-2xl font-bold">مسیر شما کامل شد</h2>
            <p className="mt-2 text-sm leading-7 text-emerald-950/75">
                همه مراحل ثبت شده‌اند. امتیازها و پاداش‌های دریافت‌شده را در کیف
                پاداش یا پنل مشارکت مشاهده کنید.
            </p>
            {game.visitorState.participantDashboardUrl ? (
                <Link
                    href={game.visitorState.participantDashboardUrl}
                    className="mt-5 inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-emerald-700 px-5 text-sm font-bold text-white hover:bg-emerald-800"
                >
                    <Trophy className="size-5" />
                    مشاهده پنل و پاداش‌ها
                </Link>
            ) : null}
        </section>
    );
}

function RewardWallet({ rewards }: { rewards: UserRewardItem[] }) {
    return (
        <section className="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm sm:p-6">
            <div className="flex items-center gap-2">
                <Gift className="size-5 text-amber-700" />
                <h2 className="font-bold">پاداش‌های دریافت‌شده</h2>
            </div>
            {rewards.length > 0 ? (
                <div className="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    {rewards.map((reward) => (
                        <div
                            key={reward.id}
                            className="rounded-xl border border-amber-200 bg-amber-50 p-4"
                        >
                            <p className="font-bold">
                                {reward.reward?.name ?? 'پاداش مسیر'}
                            </p>
                            <p className="mt-1 text-xs text-amber-900/70">
                                {reward.reward?.partnerName ??
                                    reward.redemption?.partnerName ??
                                    'اکسپلوریا'}
                            </p>
                            {reward.redemption ? (
                                <p
                                    className="mt-3 font-mono text-sm font-bold"
                                    dir="ltr"
                                >
                                    {reward.redemption.redemptionCode}
                                </p>
                            ) : null}
                        </div>
                    ))}
                </div>
            ) : (
                <p className="mt-3 text-sm leading-7 text-zinc-500">
                    پس از تکمیل نخستین مرحله، پاداش‌های شما در این بخش نمایش
                    داده می‌شوند.
                </p>
            )}
        </section>
    );
}
