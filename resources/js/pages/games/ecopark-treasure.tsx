import { Form, Head, Link, usePage } from '@inertiajs/react';
import {
    BadgeCheck,
    Check,
    ChevronLeft,
    CircleDot,
    Clock3,
    Compass,
    Copy,
    Gift,
    Home,
    LoaderCircle,
    Lock,
    Map,
    MapPin,
    QrCode,
    Route,
    ShieldCheck,
    Sparkles,
    TicketCheck,
    Trophy,
    UserRound,
    UsersRound,
    X,
} from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';

type Mode = {
    key: 'individual' | 'family' | 'team';
    title: string;
    description: string;
};

type RouteOption = {
    key: 'quick' | 'family' | 'explorer';
    title: string;
    duration: string;
    description: string;
};

type Hotspot = {
    key: string;
    title: string;
    description: string;
    x: number;
    y: number;
};

type Clue = {
    question: string;
    instruction: string;
};

type StepDefinition = {
    index: number;
    title: string;
    instruction: string;
    verification: string;
};

type PartyStep = {
    index: number;
    status: 'locked' | 'available' | 'completed';
    points: number;
    attempts: number;
    metadata: Record<string, unknown> | null;
};

type Party = {
    id: string;
    mode: Mode['key'];
    name: string | null;
    inviteCode: string | null;
    routeKey: RouteOption['key'] | null;
    status: 'active' | 'ready_for_visit' | 'onsite_active' | 'completed';
    score: number;
    isLeader: boolean;
    collaborationBonusAwarded: boolean;
    members: {
        id: string;
        displayName: string;
        memberType: 'registered' | 'companion';
        role: 'leader' | 'member' | 'companion';
        isViewer: boolean;
    }[];
    steps: PartyStep[];
    foundHotspots: string[];
    foundFragments: string[];
    nextHotspotHint: string | null;
    entryPass: {
        code: string;
        status: 'active' | 'redeemed' | 'expired';
        expiresAt: string;
    } | null;
    physicalJourney: {
        phase: 'awaiting_gate' | 'active' | 'completed';
        steps: {
            index: number;
            key: string;
            title: string;
            instruction: string;
            status: 'locked' | 'available' | 'completed';
            points: number;
            completedAt: string | null;
        }[];
        nextCheckpointKey: string | null;
    };
    bonusClaims: {
        adRequestId: string;
        status: 'started' | 'completed';
        points: number;
        startedAt: string;
    }[];
};

type GameOffer = {
    id: string;
    kind: 'ad' | 'reward';
    adRequestId: string | null;
    title: string;
    partnerName: string | null;
    bodyCopy: string | null;
    ctaText: string;
    assetUrl: string | null;
    points: number | null;
};

type Props = {
    game: {
        campaign: {
            id: string;
            name: string;
            venueName: string | null;
            city: string | null;
        } | null;
        entryQr: { code: string; label: string | null; scanUrl: string } | null;
        onsiteGate: {
            label: string;
            location: string | null;
            findingInstruction: string | null;
            isDemo: boolean;
            demoScanUrl: string | null;
        } | null;
        physicalCheckpoints: {
            key: string;
            label: string;
            location: string | null;
            findingInstruction: string | null;
            isDemo: boolean;
            demoScanUrl: string | null;
        }[];
        latestVisit: { id: string; occurredAt: string; showUrl: string } | null;
        visitorState: {
            isAuthenticated: boolean;
            hasLinkedVisit: boolean;
            participantDashboardUrl: string | null;
        };
        definition: {
            modes: Mode[];
            routes: RouteOption[];
            hotspots: Hotspot[];
            clues: Record<RouteOption['key'], Clue>;
            steps: StepDefinition[];
            physicalSteps: StepDefinition[];
            rules: string[];
        };
        party: Party | null;
        gameOffers: GameOffer[];
    };
};

const modeIcons = {
    individual: UserRound,
    family: Home,
    team: UsersRound,
};

const faNumber = (value: number) => value.toLocaleString('fa-IR');

function SubmitButton({
    processing,
    disabled = false,
    children,
    className = '',
}: {
    processing: boolean;
    disabled?: boolean;
    children: React.ReactNode;
    className?: string;
}) {
    return (
        <button
            type="submit"
            disabled={processing || disabled}
            className={`inline-flex min-h-12 items-center justify-center gap-2 rounded-2xl bg-slate-950 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60 ${className}`}
        >
            {processing ? (
                <LoaderCircle className="size-4 animate-spin" />
            ) : (
                <ChevronLeft className="size-4" />
            )}
            {children}
        </button>
    );
}

function FieldError({ message }: { message?: string }) {
    return message ? (
        <p className="mt-2 text-xs font-medium text-rose-700">{message}</p>
    ) : null;
}

function JourneyProgress({
    steps,
    definitions,
}: {
    steps: PartyStep[];
    definitions: StepDefinition[];
}) {
    return (
        <ol className="grid gap-2 lg:gap-3">
            {definitions.map((definition) => {
                const progress = steps.find(
                    (item) => item.index === definition.index,
                );
                const status = progress?.status ?? 'locked';
                const Icon =
                    status === 'completed'
                        ? Check
                        : status === 'available'
                          ? CircleDot
                          : Lock;

                return (
                    <li
                        key={definition.index}
                        className={`flex items-center gap-3 rounded-2xl border p-3 transition ${
                            status === 'available'
                                ? 'border-slate-950 bg-slate-950 text-white shadow-lg'
                                : status === 'completed'
                                  ? 'border-emerald-200 bg-emerald-50 text-emerald-950'
                                  : 'border-slate-200 bg-white/70 text-slate-400'
                        }`}
                    >
                        <span
                            className={`grid size-10 shrink-0 place-items-center rounded-xl ${
                                status === 'available'
                                    ? 'bg-amber-300 text-slate-950'
                                    : status === 'completed'
                                      ? 'bg-emerald-600 text-white'
                                      : 'bg-slate-100'
                            }`}
                        >
                            <Icon className="size-5" />
                        </span>
                        <span className="min-w-0">
                            <span className="block text-xs opacity-70">
                                مرحله {faNumber(definition.index)}
                            </span>
                            <span className="block truncate text-sm font-bold">
                                {definition.title}
                            </span>
                        </span>
                        {progress?.points ? (
                            <span className="mr-auto text-xs font-bold">
                                +{faNumber(progress.points)}
                            </span>
                        ) : null}
                    </li>
                );
            })}
        </ol>
    );
}

function ParticipationSetup({ game }: { game: Props['game'] }) {
    const [mode, setMode] = useState<Mode['key']>('individual');
    const [showJoin, setShowJoin] = useState(false);

    if (!game.visitorState.isAuthenticated) {
        return (
            <section className="rounded-[2rem] border border-emerald-200 bg-white p-6 shadow-xl shadow-emerald-950/5 sm:p-10">
                <div className="mx-auto max-w-xl text-center">
                    <span className="mx-auto grid size-16 place-items-center rounded-2xl bg-emerald-100 text-emerald-800">
                        <QrCode className="size-8" />
                    </span>
                    <h2 className="mt-5 text-2xl font-black">
                        ورود امن، نقطه شروع بازی است
                    </h2>
                    <p className="mt-3 leading-8 text-slate-600">
                        QR شروع را اسکن کنید و با شماره موبایل وارد شوید. انتخاب
                        حالت بازی در گام بعد انجام می‌شود و هنوز هیچ چالشی
                        خودکار تکمیل نخواهد شد.
                    </p>
                    {game.entryQr ? (
                        <Link
                            href={game.entryQr.scanUrl}
                            className="mt-6 inline-flex min-h-12 items-center gap-2 rounded-2xl bg-slate-950 px-6 font-bold text-white hover:bg-emerald-700"
                        >
                            <QrCode className="size-5" />
                            ورود با QR شروع
                        </Link>
                    ) : (
                        <p className="mt-6 rounded-2xl bg-amber-50 p-4 text-sm text-amber-900">
                            QR شروع این کمپین هنوز فعال نشده است.
                        </p>
                    )}
                </div>
            </section>
        );
    }

    if (!game.latestVisit) {
        return (
            <section className="rounded-[2rem] border border-amber-200 bg-amber-50 p-8 text-center">
                <QrCode className="mx-auto size-10 text-amber-700" />
                <h2 className="mt-4 text-xl font-black">
                    ابتدا QR شروع کمپین را اسکن کنید
                </h2>
                <p className="mt-2 text-sm leading-7 text-amber-900">
                    ورود حساب شما معتبر است، اما هنوز بازدیدی به این کمپین متصل
                    نشده؛ اسکن QR شروع تنها راه بازشدن مرحله اول است.
                </p>
                {game.entryQr ? (
                    <Link
                        href={game.entryQr.scanUrl}
                        className="mt-5 inline-flex rounded-xl bg-amber-900 px-5 py-3 text-sm font-bold text-white"
                    >
                        اسکن QR شروع
                    </Link>
                ) : null}
            </section>
        );
    }

    return (
        <section className="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-xl shadow-slate-950/5 sm:p-8">
            <div className="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <span className="text-xs font-bold text-emerald-700">
                        مرحله ۱ از ۵
                    </span>
                    <h2 className="mt-1 text-2xl font-black">
                        با چه کسانی بازی می‌کنید؟
                    </h2>
                    <p className="mt-2 max-w-2xl text-sm leading-7 text-slate-600">
                        هر سه حالت، پنج چالش یکسان دارند. تفاوت فقط در شیوه
                        همکاری و ثبت پیشرفت مشترک است.
                    </p>
                </div>
                <button
                    type="button"
                    onClick={() => setShowJoin((value) => !value)}
                    className="rounded-xl border border-slate-300 px-4 py-2 text-sm font-bold hover:border-emerald-500"
                >
                    {showJoin ? 'بازگشت و ساخت تیم' : 'پیوستن به تیم موجود'}
                </button>
            </div>

            {showJoin ? (
                <Form
                    action="/games/ecopark-treasure/parties/join"
                    method="post"
                    options={{ preserveScroll: true }}
                    className="mt-7 rounded-2xl bg-slate-50 p-5"
                >
                    {({ processing, errors }) => (
                        <div className="mx-auto max-w-md">
                            <div className="mb-5 rounded-xl border border-sky-200 bg-sky-50 p-4 text-xs leading-6 text-sky-950">
                                این بخش فقط برای عضوی است که یک راهبر قبلاً تیم
                                را ساخته و کد واقعی را برای او فرستاده است. اگر
                                هنوز کدی نگرفته‌اید، «بازگشت و ساخت تیم» را
                                بزنید؛ حالت تیمی را انتخاب کنید و تیم را بسازید.
                            </div>
                            <label
                                htmlFor="invite_code"
                                className="text-sm font-bold"
                            >
                                کد ۶ حرفی دعوت تیم
                            </label>
                            <input
                                id="invite_code"
                                name="invite_code"
                                maxLength={6}
                                dir="ltr"
                                placeholder="کد دریافتی"
                                className="mt-2 h-12 w-full rounded-xl border border-slate-300 px-4 text-center text-lg font-black tracking-[0.25em] uppercase outline-none focus:border-emerald-500"
                            />
                            <p className="mt-2 text-[11px] leading-5 text-slate-500">
                                این کد شش‌حرفی نمونه یا پیش‌فرض ندارد و فقط پس
                                از ساخت یک تیم واقعی تولید می‌شود.
                            </p>
                            <FieldError message={errors.invite_code} />
                            <SubmitButton
                                processing={processing}
                                className="mt-4 w-full"
                            >
                                پیوستن با کد تیم موجود
                            </SubmitButton>
                        </div>
                    )}
                </Form>
            ) : (
                <Form
                    action="/games/ecopark-treasure/parties"
                    method="post"
                    options={{ preserveScroll: true }}
                    className="mt-7"
                >
                    {({ processing, errors }) => (
                        <>
                            <input
                                type="hidden"
                                name="visit_id"
                                value={game.latestVisit?.id}
                            />
                            <input type="hidden" name="mode" value={mode} />
                            <div className="grid gap-3 md:grid-cols-3">
                                {game.definition.modes.map((item) => {
                                    const Icon = modeIcons[item.key];
                                    const selected = mode === item.key;

                                    return (
                                        <button
                                            key={item.key}
                                            type="button"
                                            onClick={() => setMode(item.key)}
                                            className={`rounded-2xl border p-5 text-right transition ${
                                                selected
                                                    ? 'border-emerald-600 bg-emerald-50 ring-2 ring-emerald-100'
                                                    : 'border-slate-200 hover:border-slate-400'
                                            }`}
                                        >
                                            <span className="flex items-center gap-3">
                                                <span
                                                    className={`grid size-11 place-items-center rounded-xl ${selected ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700'}`}
                                                >
                                                    <Icon className="size-5" />
                                                </span>
                                                <strong>{item.title}</strong>
                                            </span>
                                            <span className="mt-4 block text-sm leading-7 text-slate-600">
                                                {item.description}
                                            </span>
                                        </button>
                                    );
                                })}
                            </div>
                            <FieldError message={errors.mode} />

                            {mode !== 'individual' ? (
                                <div className="mt-5 grid gap-4 sm:grid-cols-2">
                                    <label className="grid gap-2 text-sm font-bold">
                                        نام{' '}
                                        {mode === 'family' ? 'خانواده' : 'تیم'}
                                        <input
                                            name="name"
                                            placeholder={
                                                mode === 'family'
                                                    ? 'مثلاً خانواده کاوشگر'
                                                    : 'مثلاً تیم ستاره شمال'
                                            }
                                            className="h-12 rounded-xl border border-slate-300 px-4 font-normal outline-none focus:border-emerald-500"
                                        />
                                        <FieldError message={errors.name} />
                                    </label>
                                    {mode === 'family' ? (
                                        <label className="grid gap-2 text-sm font-bold">
                                            تعداد همراهان
                                            <select
                                                name="companion_count"
                                                defaultValue="2"
                                                className="h-12 rounded-xl border border-slate-300 bg-white px-4 font-normal"
                                            >
                                                {[1, 2, 3, 4, 5].map(
                                                    (count) => (
                                                        <option
                                                            key={count}
                                                            value={count}
                                                        >
                                                            {faNumber(count)}{' '}
                                                            همراه
                                                        </option>
                                                    ),
                                                )}
                                            </select>
                                            <FieldError
                                                message={errors.companion_count}
                                            />
                                        </label>
                                    ) : (
                                        <p className="self-end rounded-xl bg-sky-50 p-3 text-xs leading-6 text-sky-900">
                                            پس از ساخت، کد دعوت می‌گیرید. حداکثر
                                            ۸ حساب می‌توانند عضو تیم شوند.
                                        </p>
                                    )}
                                </div>
                            ) : null}

                            <div className="mt-6 flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:items-center sm:justify-between">
                                <p className="text-xs leading-6 text-slate-500">
                                    این انتخاب برای مشارکت امتیازدار همین دوره
                                    ثبت می‌شود.
                                </p>
                                <SubmitButton
                                    processing={processing}
                                    className="sm:min-w-52"
                                >
                                    ساخت مسیر و شروع
                                </SubmitButton>
                            </div>
                        </>
                    )}
                </Form>
            )}
        </section>
    );
}

function RouteChallenge({
    game,
    party,
}: {
    game: Props['game'];
    party: Party;
}) {
    return (
        <div className="grid gap-4 md:grid-cols-3">
            {game.definition.routes.map((routeOption) => (
                <Form
                    key={routeOption.key}
                    action={`/games/ecopark-treasure/parties/${party.id}/route`}
                    method="post"
                    options={{ preserveScroll: true }}
                >
                    {({ processing, errors }) => (
                        <div className="flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-5 hover:border-emerald-400">
                            <input
                                type="hidden"
                                name="route_key"
                                value={routeOption.key}
                            />
                            <span className="flex items-center justify-between">
                                <Route className="size-6 text-emerald-700" />
                                <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold">
                                    {routeOption.duration}
                                </span>
                            </span>
                            <h3 className="mt-5 text-lg font-black">
                                {routeOption.title}
                            </h3>
                            <p className="mt-2 flex-1 text-sm leading-7 text-slate-600">
                                {routeOption.description}
                            </p>
                            <FieldError message={errors.route_key} />
                            <SubmitButton
                                processing={processing}
                                className="mt-5 w-full"
                            >
                                انتخاب این مسیر
                            </SubmitButton>
                        </div>
                    )}
                </Form>
            ))}
        </div>
    );
}

function MapChallenge({ game, party }: { game: Props['game']; party: Party }) {
    const { errors: pageErrors } = usePage<{
        errors: Record<string, string>;
    }>().props;
    const [contributor, setContributor] = useState(
        party.members.find((member) => member.isViewer)?.id ??
            party.members[0]?.id,
    );
    const currentClueNumber = Math.min(party.foundFragments.length + 1, 3);

    return (
        <div className="grid gap-5 lg:grid-cols-[1fr_18rem]">
            <div className="relative min-h-[32rem] overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-950 via-teal-900 to-slate-950 p-5 text-white shadow-inner">
                <div className="absolute inset-0 [background-image:radial-gradient(circle_at_center,white_1px,transparent_1px)] [background-size:24px_24px] opacity-20" />
                <div className="relative z-10 flex items-start justify-between">
                    <div>
                        <p className="text-xs font-bold text-emerald-200">
                            مرحله کاملاً آنلاین
                        </p>
                        <h3 className="mt-1 text-xl font-black">
                            پاسخ راهنمای جاری را روی نقشه بزنید
                        </h3>
                        <p className="mt-2 max-w-xl text-xs leading-6 text-slate-200">
                            لازم نیست اکنون در پارک باشید. از میان شش مکان فقط
                            نقطه‌ای را انتخاب کنید که با متن راهنما تطبیق دارد.
                        </p>
                    </div>
                    <span className="rounded-full bg-white/10 px-3 py-1 text-xs backdrop-blur">
                        {faNumber(party.foundHotspots.length)} از ۳
                    </span>
                </div>

                {game.definition.hotspots.map((hotspot) => {
                    const found = party.foundHotspots.includes(hotspot.key);

                    return (
                        <Form
                            key={hotspot.key}
                            action={`/games/ecopark-treasure/parties/${party.id}/hotspots`}
                            method="post"
                            options={{ preserveScroll: true }}
                            className="absolute z-20"
                            style={{
                                right: `${hotspot.x}%`,
                                top: `${hotspot.y}%`,
                            }}
                        >
                            {({ processing }) => (
                                <>
                                    <input
                                        type="hidden"
                                        name="hotspot_key"
                                        value={hotspot.key}
                                    />
                                    {party.mode === 'family' ? (
                                        <input
                                            type="hidden"
                                            name="member_id"
                                            value={contributor}
                                        />
                                    ) : null}
                                    <button
                                        type="submit"
                                        disabled={processing || found}
                                        aria-label={`${hotspot.title}: ${hotspot.description}`}
                                        className={`group grid min-h-16 w-24 -translate-x-1/2 -translate-y-1/2 place-items-center rounded-2xl border-2 px-2 py-2 text-center shadow-xl transition ${
                                            found
                                                ? 'border-emerald-200 bg-emerald-500'
                                                : 'border-white/40 bg-slate-950/80 hover:scale-105 hover:border-amber-300 hover:bg-amber-300 hover:text-slate-950'
                                        }`}
                                    >
                                        {processing ? (
                                            <LoaderCircle className="size-5 animate-spin" />
                                        ) : found ? (
                                            <>
                                                <Check className="size-5" />
                                                <span className="text-[10px] font-bold">
                                                    کشف شد
                                                </span>
                                            </>
                                        ) : (
                                            <>
                                                <MapPin className="size-4" />
                                                <span className="text-[10px] leading-4 font-bold">
                                                    {hotspot.title}
                                                </span>
                                            </>
                                        )}
                                    </button>
                                </>
                            )}
                        </Form>
                    );
                })}
                <div className="absolute right-[12%] bottom-[12%] left-[12%] h-24 rounded-[50%] border-t-4 border-dashed border-emerald-300/40" />
            </div>

            <aside className="space-y-3">
                <div className="rounded-2xl border-2 border-amber-300 bg-amber-50 p-4 text-slate-950">
                    <span className="text-xs font-bold text-amber-800">
                        راهنمای {faNumber(currentClueNumber)} از ۳
                    </span>
                    <p className="mt-2 text-sm leading-7 font-bold">
                        {party.nextHotspotHint}
                    </p>
                    <p className="mt-3 text-[11px] leading-5 text-amber-900">
                        انتخاب نادرست فقط یک پیام راهنما نشان می‌دهد و شما را به
                        مرحله بعد نمی‌برد.
                    </p>
                    <FieldError message={pageErrors.hotspot_key} />
                </div>
                {party.mode === 'family' ? (
                    <label className="block rounded-2xl border border-violet-200 bg-violet-50 p-4 text-sm font-bold">
                        چه کسی این نشانه را پیدا کرد؟
                        <select
                            value={contributor}
                            onChange={(event) =>
                                setContributor(event.target.value)
                            }
                            className="mt-2 h-11 w-full rounded-xl border border-violet-200 bg-white px-3 font-normal"
                        >
                            {party.members.map((member) => (
                                <option key={member.id} value={member.id}>
                                    {member.displayName}
                                </option>
                            ))}
                        </select>
                    </label>
                ) : null}
                <div className="rounded-2xl border border-slate-200 bg-white p-4">
                    <strong className="text-sm">تکه‌های رمز شما</strong>
                    <div className="mt-3 flex gap-2" dir="ltr">
                        {[0, 1, 2].map((index) => (
                            <span
                                key={index}
                                className={`grid size-11 place-items-center rounded-xl text-lg font-black ${
                                    party.foundFragments[index]
                                        ? 'bg-emerald-100 text-emerald-900'
                                        : 'bg-slate-100 text-slate-400'
                                }`}
                            >
                                {party.foundFragments[index] ?? '؟'}
                            </span>
                        ))}
                    </div>
                    <p className="mt-3 text-[11px] leading-5 text-slate-500">
                        ترتیب رقم‌ها را نگه دارید؛ در مرحله بعد به آن‌ها نیاز
                        دارید.
                    </p>
                </div>
                {party.mode !== 'individual' ? (
                    <p className="rounded-2xl bg-amber-50 p-3 text-xs leading-6 text-amber-900">
                        اگر حداقل دو عضو در کشف‌ها سهیم باشند، ۳۰ امتیاز همکاری
                        می‌گیرید.
                    </p>
                ) : null}
            </aside>
        </div>
    );
}

function ClueChallenge({ game, party }: { game: Props['game']; party: Party }) {
    const clue = party.routeKey ? game.definition.clues[party.routeKey] : null;

    if (!clue) {
        return null;
    }

    return (
        <div>
            <div className="rounded-2xl bg-slate-950 p-5 text-white">
                <p className="text-xs text-emerald-300">سرنخ نهایی مسیر</p>
                <h3 className="mt-2 text-lg leading-8 font-black">
                    {clue.question}
                </h3>
                <p className="mt-2 text-sm leading-7 text-slate-300">
                    {clue.instruction}
                </p>
            </div>
            <div className="mt-4 rounded-2xl border border-slate-200 bg-white p-5">
                <div className="flex justify-center gap-2" dir="ltr">
                    {party.foundFragments.map((fragment, index) => (
                        <span
                            key={`${fragment}-${index}`}
                            className="grid size-12 place-items-center rounded-xl bg-emerald-100 text-xl font-black text-emerald-900"
                        >
                            {fragment}
                        </span>
                    ))}
                </div>
                <Form
                    action={`/games/ecopark-treasure/parties/${party.id}/clue`}
                    method="post"
                    options={{ preserveScroll: true }}
                    className="mx-auto mt-5 max-w-md"
                >
                    {({ processing, errors }) => (
                        <>
                            <label
                                htmlFor="answer_key"
                                className="block text-sm font-bold"
                            >
                                رمز سه‌رقمی
                            </label>
                            <input
                                id="answer_key"
                                name="answer_key"
                                inputMode="numeric"
                                maxLength={3}
                                autoComplete="off"
                                dir="ltr"
                                className="mt-2 h-14 w-full rounded-xl border border-slate-300 px-4 text-center text-2xl font-black tracking-[0.35em] outline-none focus:border-emerald-500"
                            />
                            <FieldError message={errors.answer_key} />
                            <SubmitButton
                                processing={processing}
                                className="mt-4 w-full"
                            >
                                بررسی رمز و ادامه
                            </SubmitButton>
                        </>
                    )}
                </Form>
            </div>
        </div>
    );
}

function PassChallenge({ party }: { party: Party }) {
    return (
        <div className="rounded-3xl border border-amber-200 bg-gradient-to-br from-amber-50 to-white p-6">
            <TicketCheck className="size-10 text-amber-700" />
            <h3 className="mt-4 text-xl font-black">چالش‌های آنلاین کامل شد</h3>
            <p className="mt-2 max-w-xl text-sm leading-7 text-slate-600">
                اکنون راهبر گروه می‌تواند مجوز حضور هفت‌روزه و یک‌بارمصرف را
                بسازد. این دکمه یک مرحله را «ادعا» نمی‌کند؛ یک مجوز امضاشده
                واقعی تولید می‌کند که فقط با QR حضور اکوپارک مصرف می‌شود.
            </p>
            {party.isLeader ? (
                <Form
                    action={`/games/ecopark-treasure/parties/${party.id}/pass`}
                    method="post"
                    options={{ preserveScroll: true }}
                    className="mt-5"
                >
                    {({ processing, errors }) => (
                        <>
                            <SubmitButton processing={processing}>
                                ساخت مجوز حضور
                            </SubmitButton>
                            <FieldError message={errors.party} />
                        </>
                    )}
                </Form>
            ) : (
                <p className="mt-5 rounded-xl bg-sky-50 p-4 text-sm font-bold text-sky-900">
                    منتظر بمانید تا راهبر تیم مجوز مشترک را بسازد.
                </p>
            )}
        </div>
    );
}

function EntryPass({ game, party }: { game: Props['game']; party: Party }) {
    const pass = party.entryPass;
    const [copied, setCopied] = useState(false);
    const modeTitle =
        game.definition.modes.find((mode) => mode.key === party.mode)?.title ??
        party.mode;
    const routeTitle =
        game.definition.routes.find(
            (routeOption) => routeOption.key === party.routeKey,
        )?.title ?? 'مسیر ثبت‌شده';
    const redeemed = pass?.status === 'redeemed';
    const campaignCompleted = party.status === 'completed';

    if (!pass) {
        return null;
    }

    return (
        <div className="overflow-hidden rounded-3xl bg-slate-950 text-white shadow-2xl">
            <div className="grid gap-6 p-6 sm:grid-cols-[1fr_auto] sm:p-8">
                <div>
                    <span className="inline-flex items-center gap-2 rounded-full bg-emerald-400/15 px-3 py-1 text-xs font-bold text-emerald-300">
                        <BadgeCheck className="size-4" />
                        {redeemed
                            ? campaignCompleted
                                ? 'کمپین آنلاین و حضوری تکمیل شد'
                                : 'مرحله حضوری فعال است'
                            : 'بخش آنلاین تکمیل شد'}
                    </span>
                    <h2 className="mt-4 text-2xl font-black">
                        {redeemed
                            ? campaignCompleted
                                ? 'گنج حضوری با موفقیت کشف شد'
                                : 'حضور تأیید شد؛ مسیر را ادامه دهید'
                            : `مجوز ${modeTitle} حضور اکوپارک`}
                    </h2>
                    <p className="mt-2 text-sm leading-7 text-slate-300">
                        {redeemed
                            ? campaignCompleted
                                ? 'همه ایستگاه‌های فیزیکی به ترتیب معتبر اسکن شده‌اند. نتیجه و پاداش‌ها در «پنل من» باقی می‌مانند.'
                                : 'اسکن دروازه ثبت و ۱۵۰ امتیاز حضور اضافه شد. اکنون راهنمای ایستگاه جاری را در بخش «ادامه مسیر فیزیکی» دنبال کنید.'
                            : 'این کد، شماره پیگیری مجوز شماست و قرار نیست آن را اسکن کنید. برای تأیید حضور باید QR نصب‌شده روی استند اکسپلوریا را در محل اسکن کنید.'}
                    </p>
                    {campaignCompleted ? (
                        <p className="mt-3 rounded-xl bg-white/10 p-3 text-xs leading-6 text-slate-200">
                            شرکت امتیازدار دوباره با همین حساب در همین دوره ممکن
                            نیست. امکان ساخت مسیر جدید با آغاز دوره بعدی فعال
                            می‌شود؛ حالت تمرینیِ بدون امتیاز فعلاً تعریف نشده
                            است.
                        </p>
                    ) : null}
                    <div className="mt-5 grid gap-2 text-xs text-slate-300 sm:grid-cols-2">
                        <p>
                            <strong className="text-white">نام مسیر: </strong>
                            {routeTitle}
                        </p>
                        <p>
                            <strong className="text-white">نام گروه: </strong>
                            {party.name ?? 'بازیکن انفرادی'}
                        </p>
                        <p>
                            <strong className="text-white">
                                تعداد نفرات:{' '}
                            </strong>
                            {faNumber(party.members.length)}
                        </p>
                        <p>
                            <strong className="text-white">اعضا: </strong>
                            {party.members
                                .map((member) => member.displayName)
                                .join('، ')}
                        </p>
                    </div>
                </div>
                <div className="rounded-2xl bg-white p-5 text-center text-slate-950">
                    <TicketCheck className="mx-auto size-12" />
                    <p className="mt-2 text-xs font-bold text-slate-500">
                        شماره پیگیری مجوز
                    </p>
                    <p className="mt-3 font-mono text-xl font-black tracking-wider">
                        {pass.code}
                    </p>
                    <button
                        type="button"
                        onClick={async () => {
                            await navigator.clipboard.writeText(pass.code);
                            setCopied(true);
                        }}
                        className="mt-3 inline-flex items-center gap-1 text-xs font-bold text-emerald-700"
                    >
                        {copied ? (
                            <Check className="size-3" />
                        ) : (
                            <Copy className="size-3" />
                        )}
                        {copied ? 'کپی شد' : 'کپی کد'}
                    </button>
                </div>
            </div>
            {!redeemed ? (
                <div className="border-t border-white/10 bg-white/5 p-6 sm:p-8">
                    <h3 className="font-black">حالا دقیقاً چه کار کنید؟</h3>
                    <ol className="mt-4 grid gap-3 text-sm leading-7 text-slate-200">
                        <li>
                            <strong className="text-white">
                                ۱. به محل بروید:{' '}
                            </strong>
                            {game.onsiteGate?.location ??
                                'ورودی اصلی اکوپارک عباس‌آباد، کنار میز راهنما'}
                        </li>
                        <li>
                            <strong className="text-white">
                                ۲. استند را پیدا کنید:{' '}
                            </strong>
                            {game.onsiteGate?.findingInstruction ??
                                'استند سبز با عنوان «دروازه حضور بازی اکسپلوریا» را پیدا کنید.'}
                        </li>
                        <li>
                            <strong className="text-white">
                                ۳. QR همان استند را اسکن کنید:{' '}
                            </strong>
                            QR شروع بازی را دوباره اسکن نکنید. با همین حساب وارد
                            بمانید؛ در حالت تیمی یا خانوادگی، یک اسکن توسط یکی
                            از اعضای ثبت‌شده برای مجوز مشترک کافی است.
                        </li>
                        <li>
                            <strong className="text-white">۴. نتیجه: </strong>
                            مجوز یک‌بار مصرف می‌شود، ۱۵۰ امتیاز حضور ثبت می‌شود
                            و نخستین ایستگاه فیزیکی مسیر انتخابی باز می‌شود.
                        </li>
                    </ol>
                    <a
                        href="#physical-journey"
                        className="mt-5 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-2xl bg-emerald-400 px-5 py-3 text-sm font-black text-emerald-950 transition hover:bg-emerald-300 sm:w-auto"
                    >
                        <QrCode className="size-5" />
                        اتصال به ادامه مرحله فیزیکی
                    </a>
                </div>
            ) : null}
            <div className="border-t border-white/10 bg-white/5 px-6 py-4 text-xs text-slate-300 sm:px-8">
                اعتبار تا{' '}
                {new Intl.DateTimeFormat('fa-IR', {
                    dateStyle: 'medium',
                    timeStyle: 'short',
                }).format(new Date(pass.expiresAt))}
                {' • '}
                وضعیت:{' '}
                {pass.status === 'redeemed'
                    ? 'مصرف‌شده'
                    : pass.status === 'expired'
                      ? 'منقضی‌شده'
                      : 'فعال'}
            </div>
        </div>
    );
}

function PhysicalJourney({
    game,
    party,
}: {
    game: Props['game'];
    party: Party;
}) {
    const journey = party.physicalJourney;
    const currentStep = journey.steps.find(
        (step) => step.status === 'available',
    );
    const currentLocation =
        currentStep?.key === 'onsite-gate'
            ? game.onsiteGate
            : game.physicalCheckpoints.find(
                  (checkpoint) => checkpoint.key === currentStep?.key,
              );

    return (
        <section
            id="physical-journey"
            className="scroll-mt-6 rounded-3xl border border-emerald-200 bg-white p-5 shadow-xl shadow-emerald-950/5 sm:p-8"
        >
            <header className="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <span className="text-xs font-black text-emerald-700">
                        بخش دوم کمپین
                    </span>
                    <h2 className="mt-2 text-2xl font-black">
                        {journey.phase === 'completed'
                            ? 'مسیر فیزیکی کامل شد'
                            : 'ادامه مسیر فیزیکی در اکوپارک'}
                    </h2>
                    <p className="mt-2 max-w-2xl text-sm leading-7 text-slate-600">
                        هر ایستگاه فقط با QR فیزیکی درست و به ترتیب مسیر تأیید
                        می‌شود. کلیک روی کارت‌ها مرحله‌ای را کامل نمی‌کند.
                    </p>
                </div>
                <span className="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                    {journey.steps
                        .filter((step) => step.status === 'completed')
                        .length.toLocaleString('fa-IR')}{' '}
                    از {journey.steps.length.toLocaleString('fa-IR')} گام حضوری
                </span>
            </header>

            <div className="mt-6 grid gap-3 sm:grid-cols-2">
                {journey.steps.map((step) => (
                    <article
                        key={step.index}
                        className={`rounded-2xl border p-4 ${
                            step.status === 'completed'
                                ? 'border-emerald-200 bg-emerald-50'
                                : step.status === 'available'
                                  ? 'border-slate-950 bg-slate-950 text-white'
                                  : 'border-slate-200 bg-slate-50 text-slate-400'
                        }`}
                    >
                        <div className="flex items-start gap-3">
                            <span className="grid size-9 shrink-0 place-items-center rounded-full bg-white/90 font-black text-slate-950">
                                {step.status === 'completed' ? (
                                    <Check className="size-5 text-emerald-700" />
                                ) : step.status === 'locked' ? (
                                    <Lock className="size-4" />
                                ) : (
                                    faNumber(step.index)
                                )}
                            </span>
                            <div>
                                <h3 className="font-black">{step.title}</h3>
                                <p className="mt-1 text-xs leading-6 opacity-80">
                                    {step.status === 'locked'
                                        ? 'پس از تأیید ایستگاه قبلی باز می‌شود.'
                                        : step.instruction}
                                </p>
                            </div>
                        </div>
                    </article>
                ))}
            </div>

            {currentStep && currentLocation ? (
                <div className="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-5">
                    <span className="text-xs font-black text-amber-800">
                        کار بعدی شما
                    </span>
                    <h3 className="mt-2 text-xl font-black">
                        {currentStep.title}
                    </h3>
                    <dl className="mt-4 grid gap-3 text-sm leading-7">
                        <div>
                            <dt className="font-black">کجا برویم؟</dt>
                            <dd className="text-slate-700">
                                {currentLocation.location ??
                                    'نشانی روی استند همین مرحله درج شده است.'}
                            </dd>
                        </div>
                        <div>
                            <dt className="font-black">چه چیزی پیدا کنیم؟</dt>
                            <dd className="text-slate-700">
                                {currentLocation.findingInstruction ??
                                    currentStep.instruction}
                            </dd>
                        </div>
                        <div>
                            <dt className="font-black">چگونه تأیید می‌شود؟</dt>
                            <dd className="text-slate-700">
                                QR روی همان استند را با دوربین گوشی باز کنید و
                                دکمه تأیید را بزنید. QR اشتباه یا خارج از ترتیب
                                پذیرفته نمی‌شود.
                            </dd>
                        </div>
                    </dl>
                    {currentLocation.demoScanUrl ? (
                        <Link
                            href={currentLocation.demoScanUrl}
                            className="mt-5 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-2xl bg-slate-950 px-5 py-3 text-sm font-black text-white sm:w-auto"
                        >
                            <QrCode className="size-5" />
                            اجرای اسکن QR دمو
                        </Link>
                    ) : (
                        <p className="mt-5 rounded-xl bg-white p-4 text-xs leading-6 font-bold text-slate-700">
                            این دکمه در اجرای واقعی عمداً وجود ندارد؛ QR نصب‌شده
                            در محل را با دوربین گوشی اسکن کنید.
                        </p>
                    )}
                </div>
            ) : journey.phase === 'completed' ? (
                <div className="mt-6 flex items-start gap-3 rounded-2xl bg-emerald-100 p-5 text-emerald-950">
                    <Trophy className="size-6 shrink-0" />
                    <div>
                        <h3 className="font-black">کمپین با موفقیت تمام شد</h3>
                        <p className="mt-1 text-sm leading-7">
                            گنج پایانی ثبت شده است. نتیجه، امتیاز و پاداش‌های
                            دریافت‌شده را می‌توانید در پنل خود ببینید.
                        </p>
                    </div>
                </div>
            ) : (
                <p className="mt-6 rounded-xl bg-rose-50 p-4 text-sm text-rose-800">
                    اطلاعات QR ایستگاه جاری هنوز ثبت نشده است؛ مدیر کمپین باید
                    استند این مرحله را فعال کند.
                </p>
            )}
        </section>
    );
}

function SponsorBonus({ party, offer }: { party: Party; offer: GameOffer }) {
    const claim = party.bonusClaims.find(
        (item) => item.adRequestId === offer.adRequestId,
    );
    const [remaining, setRemaining] = useState(10);
    const [isOpen, setIsOpen] = useState(claim?.status === 'started');

    useEffect(() => {
        if (claim?.status !== 'started') {
            return;
        }

        const updateRemaining = () => {
            const elapsed = Math.floor(
                (Date.now() - new Date(claim.startedAt).getTime()) / 1000,
            );
            setRemaining(Math.max(0, 10 - elapsed));
        };

        updateRemaining();
        const timer = window.setInterval(updateRemaining, 1000);

        return () => window.clearInterval(timer);
    }, [claim?.startedAt, claim?.status]);

    if (!offer.adRequestId) {
        return null;
    }

    return (
        <>
            <aside className="rounded-3xl border border-amber-200 bg-amber-50 p-5">
                <div className="flex items-start gap-3">
                    <span className="grid size-11 shrink-0 place-items-center rounded-xl bg-amber-200 text-amber-900">
                        <Gift className="size-5" />
                    </span>
                    <div>
                        <span className="text-[11px] font-bold text-amber-800">
                            محتوای تبلیغاتی • کاملاً اختیاری • ۳۰ امتیاز
                        </span>
                        <h3 className="mt-1 font-black">{offer.title}</h3>
                        <p className="mt-1 text-xs text-slate-600">
                            {offer.partnerName}
                        </p>
                    </div>
                </div>
                <p className="mt-4 text-sm leading-7 text-slate-700">
                    با انتخاب مشاهده، محتوای واقعی اسپانسر در یک پنجره جدا نمایش
                    داده می‌شود. بستن آن هیچ اثری بر مسیر اصلی ندارد.
                </p>
                {claim?.status === 'completed' ? (
                    <p className="mt-4 flex items-center gap-2 rounded-xl bg-emerald-100 p-3 text-sm font-bold text-emerald-900">
                        <BadgeCheck className="size-5" />
                        امتیاز اختیاری دریافت شد
                    </p>
                ) : claim?.status === 'started' ? (
                    <button
                        type="button"
                        onClick={() => setIsOpen(true)}
                        className="mt-4 w-full rounded-xl border border-amber-800 px-4 py-3 text-sm font-bold text-amber-900"
                    >
                        ادامه مشاهده پیشنهاد
                    </button>
                ) : (
                    <Form
                        action={`/games/ecopark-treasure/parties/${party.id}/sponsor-bonus/start`}
                        method="post"
                        options={{ preserveScroll: true }}
                        className="mt-4"
                    >
                        {({ processing, errors }) => (
                            <>
                                <input
                                    type="hidden"
                                    name="ad_request_id"
                                    value={offer.adRequestId ?? ''}
                                />
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="w-full rounded-xl border border-amber-800 px-4 py-3 text-sm font-bold text-amber-900 disabled:opacity-60"
                                >
                                    {processing
                                        ? 'در حال بازکردن...'
                                        : 'مشاهده اختیاری پیشنهاد'}
                                </button>
                                <FieldError message={errors.ad_request_id} />
                            </>
                        )}
                    </Form>
                )}
                <p className="mt-3 text-[11px] leading-5 text-amber-900">
                    رد کردن این بخش هیچ مرحله‌ای را قفل نمی‌کند و از امتیاز اصلی
                    شما کم نمی‌شود.
                </p>
            </aside>

            {isOpen && claim?.status === 'started' ? (
                <div
                    role="dialog"
                    aria-modal="true"
                    aria-label={`پیشنهاد اختیاری ${offer.title}`}
                    className="fixed inset-0 z-50 grid place-items-center bg-slate-950/75 p-4 backdrop-blur-sm"
                >
                    <div className="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-3xl bg-white shadow-2xl">
                        <header className="flex items-start justify-between gap-4 border-b border-slate-100 p-5">
                            <div>
                                <p className="text-xs font-bold text-amber-700">
                                    محتوای تبلیغاتی اختیاری
                                </p>
                                <h2 className="mt-1 text-xl font-black">
                                    {offer.title}
                                </h2>
                                <p className="mt-1 text-xs text-slate-500">
                                    ارائه‌شده توسط{' '}
                                    {offer.partnerName ?? 'اسپانسر کمپین'}
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={() => setIsOpen(false)}
                                aria-label="بستن پیشنهاد"
                                className="grid size-10 shrink-0 place-items-center rounded-full bg-slate-100 hover:bg-slate-200"
                            >
                                <X className="size-5" />
                            </button>
                        </header>
                        {offer.assetUrl ? (
                            <img
                                src={offer.assetUrl}
                                alt=""
                                className="aspect-video w-full bg-slate-100 object-cover"
                            />
                        ) : (
                            <div className="grid aspect-video place-items-center bg-gradient-to-br from-amber-200 via-orange-100 to-emerald-100">
                                <Gift className="size-16 text-amber-800" />
                            </div>
                        )}
                        <div className="p-5">
                            <p className="text-sm leading-8 text-slate-700">
                                {offer.bodyCopy ??
                                    'پیشنهاد کوتاه اسپانسر این کمپین را مشاهده می‌کنید.'}
                            </p>
                            <div className="mt-5 h-2 overflow-hidden rounded-full bg-slate-100">
                                <div
                                    className="h-full bg-emerald-600 transition-all"
                                    style={{
                                        width: `${Math.min(100, ((10 - remaining) / 10) * 100)}%`,
                                    }}
                                />
                            </div>
                            <p className="mt-2 text-center text-xs text-slate-500">
                                {remaining > 0
                                    ? `${faNumber(remaining)} ثانیه تا فعال شدن امتیاز`
                                    : 'مشاهده کامل شد؛ می‌توانید امتیاز را دریافت کنید.'}
                            </p>
                            <Form
                                action={`/games/ecopark-treasure/parties/${party.id}/sponsor-bonus/complete`}
                                method="post"
                                options={{ preserveScroll: true }}
                                className="mt-4"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <input
                                            type="hidden"
                                            name="ad_request_id"
                                            value={offer.adRequestId ?? ''}
                                        />
                                        <SubmitButton
                                            processing={processing}
                                            disabled={remaining > 0}
                                            className="w-full"
                                        >
                                            {remaining > 0
                                                ? 'در حال مشاهده'
                                                : 'دریافت ۳۰ امتیاز'}
                                        </SubmitButton>
                                        <FieldError
                                            message={errors.ad_request_id}
                                        />
                                    </>
                                )}
                            </Form>
                            <button
                                type="button"
                                onClick={() => setIsOpen(false)}
                                className="mt-3 w-full py-2 text-xs font-bold text-slate-500"
                            >
                                فعلاً رد می‌کنم؛ مسیر اصلی باز می‌ماند
                            </button>
                        </div>
                    </div>
                </div>
            ) : null}
        </>
    );
}

function ActiveGame({ game, party }: { game: Props['game']; party: Party }) {
    const currentStep =
        party.steps.find((step) => step.status === 'available') ?? null;
    const currentDefinition = currentStep
        ? game.definition.steps.find((step) => step.index === currentStep.index)
        : null;
    const sponsorOffer = game.gameOffers.find(
        (offer) => offer.kind === 'ad' && offer.adRequestId,
    );
    const routeTitle = game.definition.routes.find(
        (routeOption) => routeOption.key === party.routeKey,
    )?.title;

    return (
        <div className="grid gap-6 lg:grid-cols-[19rem_minmax(0,1fr)]">
            <aside className="space-y-5 lg:sticky lg:top-5 lg:self-start">
                <div className="rounded-3xl bg-slate-950 p-5 text-white">
                    <div className="flex items-center justify-between">
                        <span className="text-xs text-slate-300">
                            امتیاز مشترک
                        </span>
                        <Sparkles className="size-5 text-amber-300" />
                    </div>
                    <strong className="mt-2 block text-3xl font-black">
                        {faNumber(party.score)}
                    </strong>
                    <div className="mt-4 flex flex-wrap gap-2 text-[11px]">
                        <span className="rounded-full bg-white/10 px-3 py-1">
                            {game.definition.modes.find(
                                (mode) => mode.key === party.mode,
                            )?.title ?? party.mode}
                        </span>
                        {routeTitle ? (
                            <span className="rounded-full bg-emerald-400/15 px-3 py-1 text-emerald-300">
                                {routeTitle}
                            </span>
                        ) : null}
                    </div>
                </div>

                <JourneyProgress
                    steps={party.steps}
                    definitions={game.definition.steps}
                />

                {party.mode === 'team' && party.inviteCode ? (
                    <div className="rounded-2xl border border-sky-200 bg-sky-50 p-4">
                        <span className="text-xs font-bold text-sky-800">
                            کد دعوت تیم
                        </span>
                        <strong className="mt-2 block font-mono text-2xl tracking-[0.2em] text-sky-950">
                            {party.inviteCode}
                        </strong>
                        <p className="mt-2 text-xs leading-5 text-sky-800">
                            هر عضو با حساب خودش وارد می‌شود؛ پیشرفت برای همه
                            مشترک است.
                        </p>
                    </div>
                ) : null}

                {sponsorOffer ? (
                    <SponsorBonus
                        key={`${sponsorOffer.id}-${
                            party.bonusClaims.find(
                                (claim) =>
                                    claim.adRequestId ===
                                    sponsorOffer.adRequestId,
                            )?.status ?? 'idle'
                        }`}
                        party={party}
                        offer={sponsorOffer}
                    />
                ) : null}
            </aside>

            <main className="min-w-0 space-y-5">
                {party.entryPass ? (
                    <>
                        <EntryPass game={game} party={party} />
                        <PhysicalJourney game={game} party={party} />
                    </>
                ) : currentDefinition ? (
                    <section className="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-xl shadow-slate-950/5 sm:p-8">
                        <header className="border-b border-slate-100 pb-5">
                            <div className="flex flex-wrap items-center justify-between gap-3">
                                <span className="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                                    کار بعدی شما • مرحله{' '}
                                    {faNumber(currentDefinition.index)}
                                </span>
                                <span className="text-xs text-slate-500">
                                    اعضا: {faNumber(party.members.length)}
                                </span>
                            </div>
                            <h1 className="mt-4 text-2xl font-black sm:text-3xl">
                                {currentDefinition.title}
                            </h1>
                            <p className="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                                {currentDefinition.instruction}
                            </p>
                            <div className="mt-4 flex items-start gap-2 rounded-2xl bg-violet-50 p-3 text-xs leading-6 text-violet-900">
                                <ShieldCheck className="mt-0.5 size-4 shrink-0" />
                                <span>
                                    <strong>چگونه تأیید می‌شود؟ </strong>
                                    {currentDefinition.verification}
                                </span>
                            </div>
                        </header>

                        <div className="pt-6">
                            {currentStep?.index === 2 ? (
                                party.isLeader ? (
                                    <RouteChallenge game={game} party={party} />
                                ) : (
                                    <p className="rounded-2xl bg-sky-50 p-5 text-sm font-bold text-sky-900">
                                        راهبر تیم در حال انتخاب مسیر مشترک است.
                                        پس از ثبت، نقشه برای همه اعضا باز
                                        می‌شود.
                                    </p>
                                )
                            ) : null}
                            {currentStep?.index === 3 ? (
                                <MapChallenge game={game} party={party} />
                            ) : null}
                            {currentStep?.index === 4 ? (
                                <ClueChallenge game={game} party={party} />
                            ) : null}
                            {currentStep?.index === 5 ? (
                                <PassChallenge party={party} />
                            ) : null}
                        </div>
                    </section>
                ) : (
                    <EntryPass game={game} party={party} />
                )}

                <section className="rounded-3xl border border-slate-200 bg-white p-5">
                    <h2 className="flex items-center gap-2 font-black">
                        <UsersRound className="size-5 text-emerald-700" />
                        اعضای {party.mode === 'family' ? 'خانواده' : 'گروه'}
                    </h2>
                    <div className="mt-4 flex flex-wrap gap-2">
                        {party.members.map((member) => (
                            <span
                                key={member.id}
                                className="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-2 text-xs font-bold"
                            >
                                <UserRound className="size-3.5" />
                                {member.displayName}
                                {member.role === 'leader' ? ' • راهبر' : ''}
                            </span>
                        ))}
                    </div>
                    {party.collaborationBonusAwarded ? (
                        <p className="mt-4 flex items-center gap-2 rounded-xl bg-emerald-50 p-3 text-xs font-bold text-emerald-900">
                            <Sparkles className="size-4" />
                            پاداش همکاری ۳۰ امتیازی با مشارکت چند عضو فعال شد.
                        </p>
                    ) : null}
                </section>
            </main>
        </div>
    );
}

export default function EcoParkTreasureGame({ game }: Props) {
    const completedSteps = useMemo(
        () =>
            game.party?.steps.filter((step) => step.status === 'completed')
                .length ?? 0,
        [game.party],
    );

    return (
        <>
            <Head title="مسیر گنج اکوپارک | EXPLORIA" />
            <div dir="rtl" className="min-h-screen bg-[#f4f7f1] text-slate-950">
                <header className="border-b border-emerald-950/10 bg-emerald-950 text-white">
                    <div className="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
                        <div className="flex items-center gap-3">
                            <span className="grid size-11 place-items-center rounded-2xl bg-emerald-400 text-emerald-950">
                                <Compass className="size-6" />
                            </span>
                            <div>
                                <strong className="block tracking-wide">
                                    EXPLORIA
                                </strong>
                                <span className="text-xs text-emerald-200">
                                    {game.campaign?.name ?? 'مسیر گنج اکوپارک'}
                                </span>
                            </div>
                        </div>
                        <div className="flex items-center gap-2">
                            {game.party ? (
                                <span className="hidden rounded-full bg-white/10 px-3 py-2 text-xs sm:inline-flex">
                                    {faNumber(completedSteps)} از ۹ مرحله
                                </span>
                            ) : null}
                            {game.visitorState.participantDashboardUrl ? (
                                <Link
                                    href={
                                        game.visitorState
                                            .participantDashboardUrl
                                    }
                                    className="rounded-xl border border-white/20 px-3 py-2 text-xs font-bold"
                                >
                                    پنل من
                                </Link>
                            ) : null}
                        </div>
                    </div>
                </header>

                <section className="overflow-hidden bg-gradient-to-l from-emerald-950 via-teal-900 to-slate-950 text-white">
                    <div className="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[1fr_23rem] lg:py-14">
                        <div>
                            <span className="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs text-emerald-100">
                                <MapPin className="size-3.5" />
                                {game.campaign?.venueName ?? 'اکوپارک'}
                                {game.campaign?.city
                                    ? ` • ${game.campaign.city}`
                                    : ''}
                            </span>
                            <h1 className="mt-5 max-w-3xl text-3xl leading-tight font-black sm:text-5xl">
                                یک مسیر روشن؛ آنلاین تا گنج حضوری
                            </h1>
                            <p className="mt-4 max-w-2xl text-sm leading-8 text-emerald-100 sm:text-base">
                                پنج گام آنلاین را کامل کنید، مجوز بگیرید و چهار
                                گام حضوری را با اسکن QRهای واقعی و به ترتیب مسیر
                                ادامه دهید.
                            </p>
                        </div>
                        <div className="grid grid-cols-3 gap-2 self-end">
                            {[
                                [Map, '۳', 'نقطه نقشه'],
                                [Clock3, '۹', 'گام روشن'],
                                [ShieldCheck, '۴', 'اسکن حضوری'],
                            ].map(([Icon, value, label]) => {
                                const StatIcon = Icon as typeof Map;

                                return (
                                    <div
                                        key={String(label)}
                                        className="rounded-2xl bg-white/10 p-3 text-center backdrop-blur"
                                    >
                                        <StatIcon className="mx-auto size-5 text-amber-300" />
                                        <strong className="mt-2 block text-xl">
                                            {value as string}
                                        </strong>
                                        <span className="text-[10px] text-emerald-100">
                                            {label as string}
                                        </span>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </section>

                <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-10">
                    {game.party ? (
                        <ActiveGame game={game} party={game.party} />
                    ) : (
                        <ParticipationSetup game={game} />
                    )}

                    <section className="mt-6 grid gap-3 rounded-3xl border border-slate-200 bg-white p-5 sm:grid-cols-3">
                        {game.definition.rules.map((rule, index) => (
                            <div
                                key={rule}
                                className="flex items-start gap-3 text-xs leading-6 text-slate-600"
                            >
                                <span className="grid size-7 shrink-0 place-items-center rounded-lg bg-emerald-100 font-black text-emerald-800">
                                    {faNumber(index + 1)}
                                </span>
                                {rule}
                            </div>
                        ))}
                    </section>
                </div>
            </div>
        </>
    );
}
