import { Head, Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import {
    CheckCircle2,
    Compass,
    Gem,
    Gift,
    History,
    MapPin,
    Play,
    QrCode,
    Sparkles,
    Store,
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
    journey: Journey;
    viewerMode: ViewerMode;
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

    return Math.round((flow.stats.completedMissions / flow.missions.length) * 100);
}

export default function ParticipantDashboard({
    participant,
    latestVisit,
    missionFlow,
    journey,
    viewerMode,
}: Props) {
    const progress = progressPercent(missionFlow);
    const nextMission =
        missionFlow?.missions.find((mission) => mission.status === 'started') ??
        missionFlow?.missions.find((mission) => mission.status === 'available') ??
        missionFlow?.missions.find((mission) => mission.status === 'locked') ??
        null;

    return (
        <main dir="rtl" className="flex h-full flex-1 flex-col gap-5 overflow-x-auto p-4">
            <Head title="پنل مشارکت‌کننده" />

            <header className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p className="text-sm text-muted-foreground">پنل بازدیدکننده و مشارکت‌کننده کمپین</p>
                    <h1 className="mt-1 text-2xl font-semibold">مسیر اکسپلوریا شما</h1>
                    <p className="mt-2 max-w-3xl text-sm leading-7 text-muted-foreground">
                        برای انتخاب کمپین، ادامه ماموریت‌ها، مشاهده امتیازها، پاداش‌ها، گنج‌ها و سابقه مشارکت فردی، خانوادگی یا تیمی.
                    </p>
                </div>
                <div className="grid gap-3 text-sm sm:grid-cols-3">
                    <StatCard label="امتیاز" value={missionFlow?.stats.totalPoints ?? 0} />
                    <StatCard label="ماموریت کامل" value={missionFlow?.stats.completedMissions ?? 0} />
                    <StatCard label="پاداش" value={missionFlow?.stats.rewards ?? 0} />
                </div>
            </header>

            {viewerMode.canPreviewVisitors ? (
                <section className="rounded-lg border border-sidebar-border/70 bg-background p-4 text-sm dark:border-sidebar-border">
                    <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 className="font-semibold">پیش‌نمایش پنل مشارکت‌کننده برای ادمین</h2>
                            <p className="mt-1 text-muted-foreground">
                                برای پشتیبانی یا دمو، یک بازدیدکننده واقعی را انتخاب کنید؛ نیازی به خروج از اکانت ادمین نیست.
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
                            <option value="" disabled>انتخاب بازدیدکننده</option>
                            {viewerMode.previewOptions.length === 0 ? (
                                <option value="" disabled>هنوز بازدیدکننده دارای بازدید ثبت‌شده وجود ندارد</option>
                            ) : null}
                            {viewerMode.previewOptions.map((option) => (
                                <option key={option.id} value={option.id}>
                                    {option.name} - {option.email} ({option.visitsCount.toLocaleString('fa-IR')} بازدید)
                                </option>
                            ))}
                        </select>
                    </div>
                    {viewerMode.isAdminPreview ? (
                        <p className="mt-3 rounded-md bg-sky-50 px-3 py-2 text-xs text-sky-900 dark:bg-sky-950 dark:text-sky-100">
                            این صفحه در حالت پیش‌نمایش ادمین نمایش داده می‌شود؛ عملیات واقعی همچنان متعلق به اکانت بازدیدکننده انتخاب‌شده است.
                        </p>
                    ) : null}
                </section>
            ) : null}

            <section className="grid gap-4 xl:grid-cols-[1fr_1.2fr]">
                <div className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
                    <div className="flex items-center gap-2">
                        <Compass className="size-5 text-emerald-600" />
                        <h2 className="font-semibold">قدم بعدی شما</h2>
                    </div>
                    <p className="mt-3 text-sm font-medium">{journey.nextAction.label}</p>
                    <p className="mt-2 text-sm leading-7 text-muted-foreground">{journey.nextAction.description}</p>
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

                <div className="grid gap-3 sm:grid-cols-4">
                    <StatCard label="کسب‌شده" value={journey.points.earned} />
                    <StatCard label="مصرف‌شده" value={journey.points.spent} />
                    <StatCard label="ذخیره فعلی" value={journey.points.stored} />
                    <StatCard label="قابل دریافت" value={journey.points.nextPotential} />
                </div>
            </section>

            <section className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
                <div className="flex items-center gap-2">
                    <QrCode className="size-5 text-sky-600" />
                    <h2 className="font-semibold">انتخاب مکان و کمپین</h2>
                </div>
                <div className="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                    {journey.activeCampaigns.length === 0 ? (
                        <EmptyBox text="کمپین فعالی برای انتخاب مستقیم ثبت نشده است؛ با اسکن QRهای محیطی، مسیر مشارکت فعال می‌شود." />
                    ) : (
                        journey.activeCampaigns.map((campaign) => (
                            <article key={campaign.id} className="rounded-md border border-sidebar-border/70 p-3 text-sm dark:border-sidebar-border">
                                <p className="font-medium">{campaign.name}</p>
                                <p className="mt-1 text-xs text-muted-foreground">
                                    {campaign.venueName ?? 'مکان پروژه'} · {campaign.city ?? 'شهر'} · {campaign.code}
                                </p>
                                <p className="mt-3 text-xs leading-6 text-muted-foreground">
                                    قبل از شروع، راهنمای QR را ببینید؛ مسیر، ماموریت‌ها، پاداش‌ها و فروشگاه‌های مرتبط در همان کمپین مشخص می‌شود.
                                </p>
                                {campaign.scanUrl ? (
                                    <Button asChild variant="outline" size="sm" className="mt-3">
                                        <Link href={campaign.scanUrl}>مشاهده راهنمای کمپین</Link>
                                    </Button>
                                ) : (
                                    <p className="mt-3 rounded-md bg-muted px-3 py-2 text-xs text-muted-foreground">QR فعال برای شروع مستقیم ندارد.</p>
                                )}
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
                        با اسکن QR کمپین، مسیر بازدید، ماموریت‌ها، کیف پاداش و امتیازهای شما در همین پنل فعال می‌شود.
                    </p>
                </section>
            ) : (
                <>
                    <section className="grid gap-4 xl:grid-cols-[0.9fr_1.1fr]">
                        <div className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
                            <div className="flex items-center gap-2">
                                <UsersRound className="size-5 text-sky-600" />
                                <h2 className="font-semibold">پروفایل مشارکت</h2>
                            </div>
                            <dl className="mt-4 grid gap-3 text-sm">
                                <InfoRow label="نام" value={participant.name} />
                                <InfoRow label="نوع شرکت" value={participant.modeLabel} />
                                <InfoRow label="تیم/خانواده" value={participant.teamName ?? 'ثبت نشده'} />
                            </dl>
                            <div className="mt-4 flex flex-wrap gap-2">
                                {participant.members.map((member) => (
                                    <span key={member} className="rounded-full bg-muted px-3 py-1 text-xs">{member}</span>
                                ))}
                            </div>
                        </div>

                        <div className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
                            <div className="flex items-center gap-2">
                                <MapPin className="size-5 text-emerald-600" />
                                <h2 className="font-semibold">آخرین کمپین فعال</h2>
                            </div>
                            <div className="mt-4 grid gap-3 text-sm md:grid-cols-2">
                                <p><span className="text-muted-foreground">مکان:</span> {latestVisit.venueName}</p>
                                <p><span className="text-muted-foreground">کمپین:</span> {latestVisit.campaignName}</p>
                                <p><span className="text-muted-foreground">هاب:</span> {latestVisit.hubName}</p>
                                <p><span className="text-muted-foreground">زمان:</span> {formatDate(latestVisit.occurredAt)}</p>
                            </div>
                            <div className="mt-4 h-2 overflow-hidden rounded-full bg-muted">
                                <div className="h-full rounded-full bg-emerald-600" style={{ width: `${progress}%` }} />
                            </div>
                            <p className="mt-2 text-xs text-muted-foreground">
                                {progress.toLocaleString('fa-IR')}٪ از مسیر این بازدید تکمیل شده است.
                            </p>
                            <div className="mt-4 flex flex-wrap gap-2">
                                <Button asChild>
                                    <Link href={`/visits/${latestVisit.id}`}>ادامه ماموریت‌ها</Link>
                                </Button>
                                {latestVisit.qrLandingUrl ? (
                                    <Button asChild variant="outline">
                                        <Link href={latestVisit.qrLandingUrl}>صفحه QR کمپین</Link>
                                    </Button>
                                ) : null}
                            </div>
                        </div>
                    </section>

                    <section className="grid gap-4 xl:grid-cols-[1.1fr_0.9fr]">
                        <div className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
                            <div className="flex items-center gap-2">
                                <Trophy className="size-5 text-amber-500" />
                                <h2 className="font-semibold">ماموریت‌ها و قدم بعدی</h2>
                            </div>
                            {nextMission ? (
                                <div className="mt-4 rounded-md bg-muted/40 p-3 text-sm">
                                    <p className="font-medium">قدم بعدی: {nextMission.title}</p>
                                    <p className="mt-1 text-xs text-muted-foreground">
                                        وضعیت: {missionStatusLabels[nextMission.status]} · امتیاز: {nextMission.points.toLocaleString('fa-IR')}
                                    </p>
                                </div>
                            ) : null}
                            <div className="mt-4 grid gap-2">
                                {(missionFlow?.missions ?? []).map((mission) => (
                                    <div key={mission.id} className="flex items-center justify-between gap-3 rounded-md border border-sidebar-border/70 p-3 text-sm dark:border-sidebar-border">
                                        <div className="min-w-0">
                                            <div className="flex items-center gap-2">
                                                {mission.status === 'completed' ? (
                                                    <CheckCircle2 className="size-4 text-emerald-600" />
                                                ) : (
                                                    <Sparkles className="size-4 text-sky-600" />
                                                )}
                                                <p className="truncate font-medium">{mission.title}</p>
                                            </div>
                                            <p className="mt-1 text-xs text-muted-foreground">
                                                {mission.cycleStep.label ?? mission.hubName ?? 'مسیر اصلی'}
                                                {mission.treasureName ? ` · گنج: ${mission.treasureName}` : ''}
                                            </p>
                                        </div>
                                        <span className="rounded-full bg-muted px-2.5 py-1 text-xs">
                                            {missionStatusLabels[mission.status]}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </div>

                        <div className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
                            <div className="flex items-center gap-2">
                                <Gift className="size-5 text-rose-500" />
                                <h2 className="font-semibold">کیف پاداش</h2>
                            </div>
                            {(missionFlow?.rewards ?? []).length === 0 ? (
                                <EmptyBox text="هنوز پاداشی صادر نشده است." />
                            ) : (
                                <div className="mt-4 grid gap-3">
                                    {missionFlow?.rewards.map((reward) => (
                                        <div key={reward.id} className="rounded-md border border-sidebar-border/70 p-3 text-sm dark:border-sidebar-border">
                                            <p className="font-medium">{reward.reward?.name}</p>
                                            <p className="mt-1 text-xs text-muted-foreground">
                                                شریک: {reward.redemption?.partnerName ?? reward.reward?.partnerName ?? 'پلتفرم'} · وضعیت: {rewardStatusLabels[reward.status] ?? reward.status}
                                            </p>
                                            {reward.redemption ? (
                                                <p className="mt-3 rounded-md bg-amber-50 px-3 py-2 font-mono text-base font-semibold text-amber-900 dark:bg-amber-950 dark:text-amber-100" dir="ltr">
                                                    {reward.redemption.redemptionCode}
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

            <section className="grid gap-4 xl:grid-cols-3">
                <InfoPanel icon={<History className="size-5 text-slate-600" />} title="سوابق مهر مکان و کمپین">
                    {journey.history.length === 0 ? (
                        <EmptyBox text="هنوز سابقه‌ای ثبت نشده است." />
                    ) : (
                        journey.history.map((visit) => (
                            <Link key={visit.id} href={`/visits/${visit.id}`} className="rounded-md border border-sidebar-border/70 p-3 text-sm hover:bg-muted/40 dark:border-sidebar-border">
                                <p className="font-medium">{visit.campaignName ?? 'کمپین'} · {visit.venueName ?? 'مکان'}</p>
                                <p className="mt-1 text-xs text-muted-foreground">
                                    {visit.hubName ?? 'مسیر عمومی'} · {formatDate(visit.occurredAt)} · {visit.points.toLocaleString('fa-IR')} امتیاز
                                </p>
                            </Link>
                        ))
                    )}
                </InfoPanel>

                <InfoPanel icon={<Store className="size-5 text-emerald-700" />} title="واحدهای تجاری و مشوق‌ها">
                    {journey.partners.length === 0 ? (
                        <EmptyBox text="هنوز مراجعه یا مصرف پاداش در واحد تجاری ثبت نشده است." />
                    ) : (
                        journey.partners.map((partner, index) => (
                            <div key={`${partner.name}-${index}`} className="rounded-md border border-sidebar-border/70 p-3 text-sm dark:border-sidebar-border">
                                <p className="font-medium">{partner.name}</p>
                                <p className="mt-1 text-xs text-muted-foreground">
                                    وضعیت مشوق: {partner.status}
                                    {partner.redeemedAt ? ` · ${formatDate(partner.redeemedAt)}` : ''}
                                </p>
                            </div>
                        ))
                    )}
                </InfoPanel>

                <InfoPanel icon={<Gem className="size-5 text-rose-600" />} title="گنج‌ها و انگیزه ادامه">
                    {journey.treasures.length === 0 ? (
                        <EmptyBox text="هنوز گنجی کشف نشده است؛ با ادامه ماموریت‌ها گنج و پاداش‌های بعدی فعال می‌شوند." />
                    ) : (
                        journey.treasures.map((treasure, index) => (
                            <div key={`${treasure.name}-${index}`} className="rounded-md border border-sidebar-border/70 p-3 text-sm dark:border-sidebar-border">
                                <p className="font-medium">{treasure.name}</p>
                                <p className="mt-1 text-xs text-muted-foreground">{treasure.campaignName ?? 'کمپین'} · {treasure.type}</p>
                            </div>
                        ))
                    )}
                    <p className="rounded-md bg-muted/50 px-3 py-2 text-xs leading-6 text-muted-foreground">
                        ادامه مشارکت فردی، خانوادگی یا تیمی می‌تواند امتیاز مرحله بعدی، پاداش فروشگاهی و گنج‌های اسپانسری بیشتری فعال کند.
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
            <p className="mt-2 text-xl font-semibold">{value.toLocaleString('fa-IR')}</p>
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
