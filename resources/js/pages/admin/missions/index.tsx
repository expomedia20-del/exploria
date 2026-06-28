import { Form, Head, Link } from '@inertiajs/react';
import {
    BadgeCheck,
    BookOpenCheck,
    CalendarClock,
    CheckCircle2,
    Gift,
    MapPin,
    Sparkles,
    Trophy,
    XCircle,
} from 'lucide-react';
import { Button } from '@/components/ui/button';

type SelectedCampaign = {
    id: string;
    code: string;
    name: string;
    campaignType: string;
    blueprintCode: string | null;
    status: string;
    venue: { id: string; code: string; name: string } | null;
};

type RegistryEntity = {
    id: string;
    code: string;
    name?: string;
    label?: string;
};

type MissionItem = {
    id: string;
    code: string;
    title: string | null;
    status: string;
    missionType: string | null;
    triggerType: string | null;
    points: number;
    startsAt: string | null;
    endsAt: string | null;
    unlockRule: Record<string, unknown> | null;
    progressCount: number;
    campaign: RegistryEntity | null;
    venue: RegistryEntity | null;
    hub: RegistryEntity | null;
    touchpoint: RegistryEntity | null;
    treasure: (RegistryEntity & { treasureType: string }) | null;
};

type RewardItem = {
    id: string;
    code: string;
    name: string;
    rewardType: string;
    status: string;
    approvalStatus: string;
    description: string | null;
    terms: string | null;
    reviewNotes: string | null;
    availabilityStatus: string;
    availableFrom: string | null;
    availableUntil: string | null;
    submittedAt: string | null;
    reviewedAt: string | null;
    pointCost: number | null;
    stockQuantity: number | null;
    awardedCount: number;
    campaign: RegistryEntity | null;
    venue: RegistryEntity | null;
    partner: (RegistryEntity & { partnerType: string }) | null;
};


type RewardBasketTier = {
    level: string;
    items: string[];
};

type SelectedBlueprint = {
    code: string;
    title: string;
    missionGoal: string;
    evidenceType: string;
    userSteps: string[];
    navigationHint: string;
    points: { base: number; bonus: string };
    rewardIdeas: string[];
    stakeholders: string[];
    connectedSurfaces: string[];
    rewardBasket: RewardBasketTier[];
    nextBuildAction: string;
};

type TreasureItem = {
    id: string;
    code: string;
    name: string;
    treasureType: string;
    status: string;
    revealRule: Record<string, unknown> | null;
    campaign: RegistryEntity | null;
    venue: RegistryEntity | null;
    missionCode: string | null;
};

type Props = {
    stats: {
        missions: number;
        activeMissions: number;
        totalPoints: number;
        rewards: number;
        pendingRewards: number;
        approvedRewards: number;
        rejectedRewards: number;
        treasures: number;
    };
    missions: MissionItem[];
    rewards: RewardItem[];
    treasures: TreasureItem[];
    selectedBlueprint: SelectedBlueprint | null;
    selectedCampaign: SelectedCampaign | null;
};

const statusLabels: Record<string, string> = {
    active: 'فعال',
    draft: 'پیش نویس',
    inactive: 'غیرفعال',
    placeholder: 'نمونه کنترل شده',
    pending_review: 'در انتظار تایید',
    approved: 'تایید شده',
    rejected: 'رد شده',
};

const statusClasses: Record<string, string> = {
    active: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200',
    draft: 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200',
    inactive: 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200',
    placeholder: 'bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-200',
};

function formatDate(value: string | null) {
    if (!value) {
        return 'بدون محدودیت';
    }

    return new Intl.DateTimeFormat('fa-IR', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

function Stat({
    label,
    value,
    icon: Icon,
}: {
    label: string;
    value: number;
    icon: typeof Trophy;
}) {
    return (
        <div className="rounded-lg border border-sidebar-border/70 px-3 py-2 dark:border-sidebar-border">
            <div className="flex items-center gap-2 text-muted-foreground">
                <Icon className="size-4" />
                <p>{label}</p>
            </div>
            <p className="mt-1 font-semibold">
                {value.toLocaleString('fa-IR')}
            </p>
        </div>
    );
}

export default function MissionRewardRegistryIndex({
    stats,
    missions,
    rewards,
    treasures,
    selectedBlueprint,
    selectedCampaign,
}: Props) {

    return (
        <>
            <Head title="ماموریت و پاداش" />
            <div
                dir="rtl"
                className="flex h-full flex-1 flex-col gap-5 overflow-x-auto p-4"
            >
                <header className="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p className="text-sm text-muted-foreground">
                            اسکلت واقعی Sprint 1.4
                        </p>
                        <h1 className="mt-1 text-2xl font-semibold">
                            ماموریت، گنج، امتیاز و پاداش
                        </h1>
                    </div>
                    <Button asChild variant="outline">
                        <Link href="/admin/mission-blueprints">
                            <BookOpenCheck className="size-4" />
                            گنجینه ماموریت‌ها
                        </Link>
                    </Button>
                    <div className="grid grid-cols-2 gap-3 text-sm sm:grid-cols-3 xl:grid-cols-6">
                        <Stat
                            icon={Trophy}
                            label="ماموریت"
                            value={stats.missions}
                        />
                        <Stat
                            icon={BadgeCheck}
                            label="فعال"
                            value={stats.activeMissions}
                        />
                        <Stat
                            icon={Sparkles}
                            label="امتیاز"
                            value={stats.totalPoints}
                        />
                        <Stat icon={Gift} label="پاداش" value={stats.rewards} />
                        <Stat
                            icon={CalendarClock}
                            label="در انتظار"
                            value={stats.pendingRewards}
                        />
                        <Stat
                            icon={MapPin}
                            label="گنج"
                            value={stats.treasures}
                        />
                    </div>
                </header>

                {selectedCampaign ? (
                    <section className="rounded-lg border border-primary/25 bg-primary/5 p-4 text-sm shadow-sm">
                        <div className="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                            <div>
                                <p className="text-xs text-muted-foreground">زمینه کمپین فعال</p>
                                <h2 className="mt-1 font-semibold">{selectedCampaign.name}</h2>
                                <p className="mt-1 text-muted-foreground">داده‌های این صفحه فقط برای همین کمپین فیلتر شده‌اند؛ اگر از منوی اصلی وارد شوید، نمای کلی نمایش داده می‌شود.</p>
                            </div>
                            <div className="flex flex-wrap gap-2">
                                <span className="rounded-full bg-background px-3 py-1 text-xs" dir="ltr">{selectedCampaign.code}</span>
                                {selectedCampaign.blueprintCode ? <span className="rounded-full bg-background px-3 py-1 text-xs" dir="ltr">{selectedCampaign.blueprintCode}</span> : null}
                            </div>
                        </div>
                    </section>
                ) : null}

                {selectedBlueprint ? (
                    <section className="rounded-lg border border-primary/25 bg-primary/5 p-4 text-sm shadow-sm">
                        <div className="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                            <div>
                                <p className="text-xs text-muted-foreground">الگوی آماده برای اقدام</p>
                                <h2 className="mt-1 text-lg font-semibold">{selectedBlueprint.title}</h2>
                                <p className="mt-1 text-muted-foreground">داده الگو به این صفحه آمده تا بر اساس آن مأموریت، امتیاز، مدرک و پاداش واقعی تعریف شود.</p>
                            </div>
                            <span className="rounded-full bg-background px-3 py-1 text-xs" dir="ltr">{selectedBlueprint.code}</span>
                        </div>
                        <div className="mt-4 grid gap-3 lg:grid-cols-3">
                            <div className="rounded-lg bg-background/75 p-3"><p className="font-medium">هدف</p><p className="mt-1 text-muted-foreground">{selectedBlueprint.missionGoal}</p></div>
                            <div className="rounded-lg bg-background/75 p-3"><p className="font-medium">مدرک انجام</p><p className="mt-1 text-muted-foreground">{selectedBlueprint.evidenceType}</p></div>
                            <div className="rounded-lg bg-background/75 p-3"><p className="font-medium">امتیاز پیشنهادی</p><p className="mt-1 text-muted-foreground">{selectedBlueprint.points.base.toLocaleString('fa-IR')} + {selectedBlueprint.points.bonus}</p></div>
                        </div>
                        <div className="mt-4 grid gap-3 lg:grid-cols-2">
                            <div className="rounded-lg bg-background/75 p-3"><p className="font-medium">مراحل کاربر</p><ol className="mt-2 space-y-1 text-muted-foreground">{selectedBlueprint.userSteps.map((step, index) => <li key={step}>{(index + 1).toLocaleString('fa-IR')}. {step}</li>)}</ol></div>
                            <div className="rounded-lg bg-background/75 p-3"><p className="font-medium">سطوح پاداش</p><div className="mt-2 flex flex-wrap gap-2">{selectedBlueprint.rewardBasket.slice(0, 4).map((tier) => <span key={tier.level} className="rounded-full bg-muted px-2 py-1 text-xs">{tier.level}: {tier.items.slice(0, 2).join(' / ')}</span>)}</div></div>
                        </div>
                        <p className="mt-3 rounded-lg bg-background/75 p-3 text-muted-foreground"><span className="font-medium text-foreground">اقدام بعدی: </span>{selectedBlueprint.nextBuildAction}</p>
                    </section>
                ) : null}

                <section className="rounded-lg border border-sidebar-border/70 bg-background dark:border-sidebar-border">
                    <div className="grid min-w-[980px] grid-cols-[1.3fr_0.9fr_0.9fr_0.9fr_0.75fr_1fr_0.8fr] gap-3 border-b border-sidebar-border/70 px-4 py-3 text-xs font-medium text-muted-foreground dark:border-sidebar-border">
                        <span>ماموریت</span>
                        <span>کمپین</span>
                        <span>مکان/هاب</span>
                        <span>نوع/تریگر</span>
                        <span>امتیاز</span>
                        <span>اعتبار</span>
                        <span>گنج</span>
                    </div>

                    {missions.length === 0 ? (
                        <div className="p-8 text-center text-sm text-muted-foreground">
                            هنوز ماموریتی ثبت نشده است.
                        </div>
                    ) : (
                        <div className="min-w-[980px] divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                            {missions.map((mission) => (
                                <article
                                    key={mission.id}
                                    className="grid grid-cols-[1.3fr_0.9fr_0.9fr_0.9fr_0.75fr_1fr_0.8fr] items-center gap-3 px-4 py-3 text-sm"
                                >
                                    <div className="min-w-0">
                                        <div className="flex items-center gap-2">
                                            <Trophy className="size-4 shrink-0 text-muted-foreground" />
                                            <span className="truncate font-medium">
                                                {mission.title}
                                            </span>
                                        </div>
                                        <div className="mt-2 flex items-center gap-2">
                                            <span
                                                className={`inline-flex rounded-full px-2.5 py-1 text-xs font-medium ${
                                                    statusClasses[
                                                        mission.status
                                                    ] ?? statusClasses.inactive
                                                }`}
                                            >
                                                {statusLabels[mission.status] ??
                                                    mission.status}
                                            </span>
                                            <span
                                                className="truncate text-xs text-muted-foreground"
                                                dir="ltr"
                                            >
                                                {mission.code}
                                            </span>
                                        </div>
                                    </div>

                                    <div className="min-w-0">
                                        <p className="truncate">
                                            {mission.campaign?.name ?? '-'}
                                        </p>
                                        <p
                                            className="mt-1 truncate text-xs text-muted-foreground"
                                            dir="ltr"
                                        >
                                            {mission.campaign?.code ?? '-'}
                                        </p>
                                    </div>

                                    <div className="min-w-0">
                                        <p className="truncate">
                                            {mission.venue?.name ?? '-'}
                                        </p>
                                        <p className="mt-1 truncate text-xs text-muted-foreground">
                                            {mission.hub?.name ??
                                                mission.touchpoint?.label ??
                                                '-'}
                                        </p>
                                    </div>

                                    <div className="min-w-0 text-xs">
                                        <p dir="ltr">{mission.missionType}</p>
                                        <p
                                            className="mt-1 text-muted-foreground"
                                            dir="ltr"
                                        >
                                            {mission.triggerType}
                                        </p>
                                    </div>

                                    <div>
                                        <p className="font-semibold">
                                            {mission.points.toLocaleString(
                                                'fa-IR',
                                            )}
                                        </p>
                                        <p className="mt-1 text-xs text-muted-foreground">
                                            انجام:{' '}
                                            {mission.progressCount.toLocaleString(
                                                'fa-IR',
                                            )}
                                        </p>
                                    </div>

                                    <div className="min-w-0">
                                        <div className="flex items-center gap-2 text-xs">
                                            <CalendarClock className="size-4 shrink-0 text-muted-foreground" />
                                            <span className="truncate">
                                                {formatDate(mission.startsAt)}
                                            </span>
                                        </div>
                                        <p className="mt-1 truncate text-xs text-muted-foreground">
                                            تا {formatDate(mission.endsAt)}
                                        </p>
                                    </div>

                                    <div className="min-w-0">
                                        <p className="truncate">
                                            {mission.treasure?.name ?? '-'}
                                        </p>
                                        <p
                                            className="mt-1 truncate text-xs text-muted-foreground"
                                            dir="ltr"
                                        >
                                            {mission.treasure?.treasureType ??
                                                '-'}
                                        </p>
                                    </div>
                                </article>
                            ))}
                        </div>
                    )}
                </section>

                <section className="grid gap-4 lg:grid-cols-2">
                    <div className="rounded-lg border border-sidebar-border/70 bg-background dark:border-sidebar-border">
                        <div className="border-b border-sidebar-border/70 px-4 py-3 dark:border-sidebar-border">
                            <h2 className="font-semibold">تعریف پاداش‌ها</h2>
                        </div>
                        <div className="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                            {rewards.map((reward) => (
                                <article
                                    key={reward.id}
                                    className="grid gap-2 px-4 py-3 text-sm"
                                >
                                    <div className="flex items-center justify-between gap-3">
                                        <div className="min-w-0">
                                            <p className="truncate font-medium">
                                                {reward.name}
                                            </p>
                                            <p
                                                className="mt-1 truncate text-xs text-muted-foreground"
                                                dir="ltr"
                                            >
                                                {reward.code} ·{' '}
                                                {reward.rewardType}
                                            </p>
                                        </div>
                                        <div className="flex shrink-0 items-center gap-2">
                                            <span
                                                className={`rounded-full px-2.5 py-1 text-xs font-medium ${
                                                    statusClasses[
                                                        reward.status
                                                    ] ?? statusClasses.inactive
                                                }`}
                                            >
                                                {statusLabels[
                                                    reward.approvalStatus
                                                ] ??
                                                    statusLabels[
                                                        reward.status
                                                    ] ??
                                                    reward.status}
                                            </span>
                                            <span className="text-xs text-muted-foreground">
                                                {reward.pointCost
                                                    ? `${reward.pointCost.toLocaleString('fa-IR')} امتیاز`
                                                    : 'بدون هزینه'}
                                            </span>
                                        </div>
                                    </div>
                                    {reward.description ? (
                                        <p className="line-clamp-2 text-xs text-muted-foreground">
                                            {reward.description}
                                        </p>
                                    ) : null}
                                    {reward.terms ? (
                                        <p className="line-clamp-2 text-xs text-muted-foreground">
                                            شرایط: {reward.terms}
                                        </p>
                                    ) : null}
                                    <p className="text-xs text-muted-foreground">
                                        شریک: {reward.partner?.name ?? 'پلتفرم'}{' '}
                                        · موجودی:{' '}
                                        {reward.stockQuantity?.toLocaleString(
                                            'fa-IR',
                                        ) ?? 'نامحدود'}{' '}
                                        · وضعیت ارائه:{' '}
                                        {statusLabels[reward.availabilityStatus] ??
                                            reward.availabilityStatus}{' '}
                                        · ثبت: {formatDate(reward.submittedAt)}
                                    </p>
                                    {reward.reviewNotes ? (
                                        <p className="rounded-md bg-muted/40 px-3 py-2 text-xs text-muted-foreground">
                                            یادداشت بازبینی: {reward.reviewNotes}
                                        </p>
                                    ) : null}
                                    {reward.approvalStatus ===
                                    'pending_review' ? (
                                        <div className="grid gap-3 rounded-md bg-muted/30 p-3 pt-3">
                                            <Form
                                                action={`/admin/rewards/${reward.id}/approve`}
                                                method="post"
                                                options={{
                                                    preserveScroll: true,
                                                }}
                                                className="grid gap-2 md:grid-cols-[1fr_auto]"
                                            >
                                                {({ processing }) => (
                                                    <>
                                                        <textarea
                                                            name="notes"
                                                            className="min-h-16 rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                                            placeholder="یادداشت تایید برای تیم عملیات"
                                                        />
                                                        <div className="flex items-end">
                                                            <Button
                                                                size="sm"
                                                                disabled={processing}
                                                            >
                                                                <CheckCircle2 className="size-4" />
                                                                تایید
                                                            </Button>
                                                        </div>
                                                    </>
                                                )}
                                            </Form>
                                            <Form
                                                action={`/admin/rewards/${reward.id}/reject`}
                                                method="post"
                                                options={{
                                                    preserveScroll: true,
                                                }}
                                                className="grid gap-2 md:grid-cols-[1fr_auto]"
                                            >
                                                {({ processing }) => (
                                                    <>
                                                        <textarea
                                                            name="notes"
                                                            className="min-h-16 rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                                            placeholder="دلیل رد یا اصلاح موردنیاز"
                                                        />
                                                        <div className="flex items-end">
                                                            <Button
                                                                size="sm"
                                                                variant="outline"
                                                                disabled={processing}
                                                            >
                                                                <XCircle className="size-4" />
                                                                رد
                                                            </Button>
                                                        </div>
                                                    </>
                                                )}
                                            </Form>
                                        </div>
                                    ) : null}
                                </article>
                            ))}
                        </div>
                    </div>

                    <div className="rounded-lg border border-sidebar-border/70 bg-background dark:border-sidebar-border">
                        <div className="border-b border-sidebar-border/70 px-4 py-3 dark:border-sidebar-border">
                            <h2 className="font-semibold">گنج‌ها</h2>
                        </div>
                        <div className="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                            {treasures.map((treasure) => (
                                <article
                                    key={treasure.id}
                                    className="grid gap-2 px-4 py-3 text-sm"
                                >
                                    <div className="flex items-center justify-between gap-3">
                                        <div className="min-w-0">
                                            <p className="truncate font-medium">
                                                {treasure.name}
                                            </p>
                                            <p
                                                className="mt-1 truncate text-xs text-muted-foreground"
                                                dir="ltr"
                                            >
                                                {treasure.code} ·{' '}
                                                {treasure.treasureType}
                                            </p>
                                        </div>
                                        <span
                                            className={`shrink-0 rounded-full px-2.5 py-1 text-xs font-medium ${
                                                statusClasses[
                                                    treasure.status
                                                ] ?? statusClasses.inactive
                                            }`}
                                        >
                                            {statusLabels[treasure.status] ??
                                                treasure.status}
                                        </span>
                                    </div>
                                    <p className="text-xs text-muted-foreground">
                                        ماموریت:{' '}
                                        <span dir="ltr">
                                            {treasure.missionCode ?? '-'}
                                        </span>
                                    </p>
                                </article>
                            ))}
                        </div>
                    </div>
                </section>
            </div>
        </>
    );
}

MissionRewardRegistryIndex.layout = {
    breadcrumbs: [
        {
            title: 'ماموریت و پاداش',
            href: '/admin/missions',
        },
    ],
};
