import { Head } from '@inertiajs/react';
import {
    Building2,
    Gem,
    Gift,
    Megaphone,
    MonitorPlay,
    Route,
    Store,
    TicketCheck,
    Trophy,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import type { ReactNode } from 'react';

type VenueItem = {
    id: string;
    code: string;
    name: string;
    city: string;
    status: string;
    profileStatus: string;
};

type CampaignItem = {
    id: string;
    code: string;
    name: string;
    campaignType: string;
    status: string;
    startsAt: string | null;
    endsAt: string | null;
    missionCount: number;
    rewardCount: number;
    treasureCount: number;
    participantCount: number;
};

type HubItem = {
    id: string;
    code: string;
    name: string;
    hubType: string;
    status: string;
    venueName: string | null;
    zoneName: string | null;
    partnerCount: number;
    displayCount: number;
    missionCount: number;
};

type PartnerItem = {
    id: string;
    code: string;
    name: string;
    partnerType: string;
    status: string;
    venueName: string | null;
    hubName: string | null;
    rewardCount: number;
    redemptionCount: number;
    adCount: number;
};

type AdRequestItem = {
    id: string;
    code: string;
    title: string;
    advertiserType: string;
    adType: string;
    status: string;
    partnerName: string | null;
    hubName: string | null;
    placementStatus: string | null;
    displayDeviceName: string | null;
    startsAt: string | null;
    endsAt: string | null;
};

type DisplayDeviceItem = {
    id: string;
    code: string;
    name: string;
    deviceType: string;
    status: string;
    hubName: string | null;
    venueName: string | null;
    playbackStatus: string | null;
    lastHeartbeatAt: string | null;
};

type DisplayScheduleItem = {
    id: string;
    adTitle: string | null;
    adCode: string | null;
    partnerName: string | null;
    displayDeviceName: string | null;
    placementType: string;
    status: string;
    priority: number;
    startsAt: string | null;
    endsAt: string | null;
};

type RewardItem = {
    id: string;
    code: string;
    name: string;
    rewardType: string;
    status: string;
    approvalStatus: string;
    stockQuantity: number | null;
    pointCost: number | null;
    campaignName: string | null;
    partnerName: string | null;
};

type TreasureItem = {
    id: string;
    code: string;
    name: string;
    treasureType: string;
    status: string;
    campaignName: string | null;
    missionCode: string | null;
};

type Props = {
    stats: {
        venues: number;
        activeCampaigns: number;
        hubs: number;
        partners: number;
        pendingAds: number;
        displayDevices: number;
        rewards: number;
        treasures: number;
        redemptions: number;
    };
    venues: VenueItem[];
    campaigns: CampaignItem[];
    hubs: HubItem[];
    partners: PartnerItem[];
    adRequests: AdRequestItem[];
    displayDevices: DisplayDeviceItem[];
    displayScheduleItems: DisplayScheduleItem[];
    rewards: RewardItem[];
    treasures: TreasureItem[];
};

const statusLabels: Record<string, string> = {
    active: 'فعال',
    inactive: 'غیرفعال',
    draft: 'پیش‌نویس',
    placeholder: 'رزرو / جای‌نگهدار',
    pending_review: 'در انتظار بررسی',
    approved: 'تایید شده',
    rejected: 'رد شده',
    scheduled: 'زمان‌بندی شده',
};

function formatDate(value: string | null) {
    if (!value) {
        return '-';
    }

    return new Intl.DateTimeFormat('fa-IR', {
        dateStyle: 'short',
        timeStyle: 'short',
    }).format(new Date(value));
}

function formatNumber(value: number | null) {
    return (value ?? 0).toLocaleString('fa-IR');
}

function labelForStatus(status: string | null) {
    if (!status) {
        return '-';
    }

    return statusLabels[status] ?? status;
}

function Stat({
    icon: Icon,
    label,
    value,
}: {
    icon: LucideIcon;
    label: string;
    value: number;
}) {
    return (
        <div className="rounded-lg border border-sidebar-border/70 px-3 py-2 dark:border-sidebar-border">
            <div className="flex items-center gap-2 text-muted-foreground">
                <Icon className="size-4" />
                <span className="text-sm">{label}</span>
            </div>
            <p className="mt-2 text-xl font-semibold">
                {value.toLocaleString('fa-IR')}
            </p>
        </div>
    );
}

function Panel({
    title,
    children,
    isEmpty = false,
}: {
    title: string;
    children: ReactNode;
    isEmpty?: boolean;
}) {
    return (
        <section className="rounded-lg border border-sidebar-border/70 bg-background dark:border-sidebar-border">
            <div className="border-b border-sidebar-border/70 px-4 py-3 dark:border-sidebar-border">
                <h2 className="font-semibold">{title}</h2>
            </div>
            <div className="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                {isEmpty ? <EmptyState /> : children}
            </div>
        </section>
    );
}

function EmptyState() {
    return (
        <p className="px-4 py-4 text-sm text-muted-foreground">
            موردی برای نمایش وجود ندارد.
        </p>
    );
}

export default function VenueDashboard({
    stats,
    venues,
    campaigns,
    hubs,
    partners,
    adRequests,
    displayDevices,
    displayScheduleItems,
    rewards,
    treasures,
}: Props) {
    return (
        <>
            <Head title="پنل مدیر مکان" />
            <div
                dir="rtl"
                className="flex h-full flex-1 flex-col gap-5 overflow-x-auto p-4"
            >
                <header className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p className="text-sm text-muted-foreground">
                            نمای read-only برای مدیریت مکان و هماهنگی ذی‌نفعان
                        </p>
                        <h1 className="mt-1 text-2xl font-semibold">
                            پنل مدیر مکان
                        </h1>
                    </div>
                    <div className="grid grid-cols-2 gap-3 text-sm md:grid-cols-3 xl:grid-cols-9">
                        <Stat
                            icon={Building2}
                            label="مکان"
                            value={stats.venues}
                        />
                        <Stat
                            icon={Trophy}
                            label="کمپین فعال"
                            value={stats.activeCampaigns}
                        />
                        <Stat
                            icon={Route}
                            label="هاب/رواق"
                            value={stats.hubs}
                        />
                        <Stat
                            icon={Store}
                            label="شریک"
                            value={stats.partners}
                        />
                        <Stat
                            icon={Megaphone}
                            label="تبلیغ منتظر"
                            value={stats.pendingAds}
                        />
                        <Stat
                            icon={MonitorPlay}
                            label="نمایشگر"
                            value={stats.displayDevices}
                        />
                        <Stat icon={Gift} label="پاداش" value={stats.rewards} />
                        <Stat icon={Gem} label="گنج" value={stats.treasures} />
                        <Stat
                            icon={TicketCheck}
                            label="مصرف پاداش"
                            value={stats.redemptions}
                        />
                    </div>
                </header>

                <Panel
                    title="مکان‌های تحت مدیریت"
                    isEmpty={venues.length === 0}
                >
                    {venues.map((venue) => (
                        <article
                            key={venue.id}
                            className="grid gap-2 px-4 py-3 text-sm md:grid-cols-[1fr_auto]"
                        >
                            <div className="min-w-0">
                                <p className="font-medium">{venue.name}</p>
                                <p
                                    className="mt-1 truncate text-xs text-muted-foreground"
                                    dir="ltr"
                                >
                                    {venue.code} · {venue.city}
                                </p>
                            </div>
                            <div className="flex flex-wrap gap-2 text-xs">
                                <span className="rounded-full bg-emerald-100 px-2.5 py-1 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">
                                    {labelForStatus(venue.status)}
                                </span>
                                <span className="rounded-full bg-muted px-2.5 py-1">
                                    پروفایل:{' '}
                                    {labelForStatus(venue.profileStatus)}
                                </span>
                            </div>
                        </article>
                    ))}
                </Panel>

                <section className="grid gap-4 xl:grid-cols-2">
                    <Panel
                        title="کمپین‌های مکان"
                        isEmpty={campaigns.length === 0}
                    >
                        {campaigns.map((campaign) => (
                            <article
                                key={campaign.id}
                                className="grid gap-2 px-4 py-3 text-sm"
                            >
                                <div className="flex items-center justify-between gap-3">
                                    <div className="min-w-0">
                                        <p className="truncate font-medium">
                                            {campaign.name}
                                        </p>
                                        <p
                                            className="mt-1 truncate text-xs text-muted-foreground"
                                            dir="ltr"
                                        >
                                            {campaign.code} ·{' '}
                                            {campaign.campaignType}
                                        </p>
                                    </div>
                                    <span className="shrink-0 rounded-full bg-muted px-2.5 py-1 text-xs">
                                        {labelForStatus(campaign.status)}
                                    </span>
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    ماموریت:{' '}
                                    {formatNumber(campaign.missionCount)} ·
                                    پاداش: {formatNumber(campaign.rewardCount)}{' '}
                                    · گنج:{' '}
                                    {formatNumber(campaign.treasureCount)} ·
                                    شریک:{' '}
                                    {formatNumber(campaign.participantCount)}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    شروع: {formatDate(campaign.startsAt)} ·
                                    پایان: {formatDate(campaign.endsAt)}
                                </p>
                            </article>
                        ))}
                    </Panel>

                    <Panel title="هاب‌ها و رواق‌ها" isEmpty={hubs.length === 0}>
                        {hubs.map((hub) => (
                            <article
                                key={hub.id}
                                className="grid gap-2 px-4 py-3 text-sm"
                            >
                                <div className="flex items-center justify-between gap-3">
                                    <div className="min-w-0">
                                        <p className="truncate font-medium">
                                            {hub.name}
                                        </p>
                                        <p
                                            className="mt-1 truncate text-xs text-muted-foreground"
                                            dir="ltr"
                                        >
                                            {hub.code} · {hub.hubType}
                                        </p>
                                    </div>
                                    <span className="shrink-0 rounded-full bg-muted px-2.5 py-1 text-xs">
                                        {labelForStatus(hub.status)}
                                    </span>
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    زون: {hub.zoneName ?? '-'} · شریک:{' '}
                                    {formatNumber(hub.partnerCount)} · نمایشگر:{' '}
                                    {formatNumber(hub.displayCount)} · ماموریت:{' '}
                                    {formatNumber(hub.missionCount)}
                                </p>
                            </article>
                        ))}
                    </Panel>
                </section>

                <section className="grid gap-4 xl:grid-cols-2">
                    <Panel
                        title="خلاصه مدیریتی واحدها و شرکا"
                        isEmpty={partners.length === 0}
                    >
                        {partners.map((partner) => (
                            <article
                                key={partner.id}
                                className="grid gap-2 px-4 py-3 text-sm"
                            >
                                <div className="flex items-center justify-between gap-3">
                                    <div className="min-w-0">
                                        <p className="truncate font-medium">
                                            {partner.name}
                                        </p>
                                        <p
                                            className="mt-1 truncate text-xs text-muted-foreground"
                                            dir="ltr"
                                        >
                                            {partner.code} ·{' '}
                                            {partner.partnerType}
                                        </p>
                                    </div>
                                    <span className="shrink-0 rounded-full bg-muted px-2.5 py-1 text-xs">
                                        {labelForStatus(partner.status)}
                                    </span>
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    محدوده: {partner.hubName ?? '-'} · پاداش:{' '}
                                    {formatNumber(partner.rewardCount)} · مصرف:{' '}
                                    {formatNumber(partner.redemptionCount)} ·
                                    تبلیغ: {formatNumber(partner.adCount)}
                                </p>
                            </article>
                        ))}
                    </Panel>

                    <Panel
                        title="نمایشگرها و وضعیت پخش"
                        isEmpty={displayDevices.length === 0}
                    >
                        {displayDevices.map((device) => (
                            <article
                                key={device.id}
                                className="grid gap-2 px-4 py-3 text-sm"
                            >
                                <div className="flex items-center justify-between gap-3">
                                    <div className="min-w-0">
                                        <p className="truncate font-medium">
                                            {device.name}
                                        </p>
                                        <p
                                            className="mt-1 truncate text-xs text-muted-foreground"
                                            dir="ltr"
                                        >
                                            {device.code} · {device.deviceType}
                                        </p>
                                    </div>
                                    <span className="shrink-0 rounded-full bg-muted px-2.5 py-1 text-xs">
                                        {labelForStatus(device.status)}
                                    </span>
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    هاب: {device.hubName ?? '-'} · پخش:{' '}
                                    {device.playbackStatus ?? '-'} · آخرین
                                    heartbeat:{' '}
                                    {formatDate(device.lastHeartbeatAt)}
                                </p>
                            </article>
                        ))}
                    </Panel>
                </section>

                <section className="grid gap-4 xl:grid-cols-2">
                    <Panel
                        title="تبلیغات و زمان‌بندی نمایشگر"
                        isEmpty={
                            adRequests.length === 0 &&
                            displayScheduleItems.length === 0
                        }
                    >
                        {adRequests.map((adRequest) => (
                            <article
                                key={adRequest.id}
                                className="grid gap-2 px-4 py-3 text-sm"
                            >
                                <div className="flex items-center justify-between gap-3">
                                    <div className="min-w-0">
                                        <p className="truncate font-medium">
                                            {adRequest.title}
                                        </p>
                                        <p
                                            className="mt-1 truncate text-xs text-muted-foreground"
                                            dir="ltr"
                                        >
                                            {adRequest.code} ·{' '}
                                            {adRequest.adType}
                                        </p>
                                    </div>
                                    <span className="shrink-0 rounded-full bg-muted px-2.5 py-1 text-xs">
                                        {labelForStatus(adRequest.status)}
                                    </span>
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    مالک:{' '}
                                    {adRequest.partnerName ??
                                        adRequest.advertiserType}{' '}
                                    · هاب: {adRequest.hubName ?? '-'} · نمایشگر:{' '}
                                    {adRequest.displayDeviceName ?? '-'}
                                </p>
                            </article>
                        ))}
                        {displayScheduleItems.map((item) => (
                            <article
                                key={item.id}
                                className="grid gap-2 px-4 py-3 text-sm"
                            >
                                <p className="font-medium">
                                    زمان‌بندی: {item.adTitle ?? '-'}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    نمایشگر: {item.displayDeviceName ?? '-'} ·
                                    شریک: {item.partnerName ?? '-'} · اولویت:{' '}
                                    {formatNumber(item.priority)}
                                </p>
                            </article>
                        ))}
                    </Panel>

                    <Panel
                        title="پاداش‌ها و گنج‌ها"
                        isEmpty={rewards.length === 0 && treasures.length === 0}
                    >
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
                                            {reward.code} · {reward.rewardType}
                                        </p>
                                    </div>
                                    <span className="shrink-0 rounded-full bg-muted px-2.5 py-1 text-xs">
                                        {labelForStatus(reward.approvalStatus)}
                                    </span>
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    کمپین: {reward.campaignName ?? '-'} · شریک:{' '}
                                    {reward.partnerName ?? '-'} · موجودی:{' '}
                                    {reward.stockQuantity === null
                                        ? 'نامحدود'
                                        : formatNumber(reward.stockQuantity)}
                                </p>
                            </article>
                        ))}
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
                                    <span className="shrink-0 rounded-full bg-amber-100 px-2.5 py-1 text-xs text-amber-800 dark:bg-amber-950 dark:text-amber-200">
                                        گنج
                                    </span>
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    کمپین: {treasure.campaignName ?? '-'} ·
                                    ماموریت: {treasure.missionCode ?? '-'} ·
                                    وضعیت: {labelForStatus(treasure.status)}
                                </p>
                            </article>
                        ))}
                    </Panel>
                </section>

                <section className="rounded-lg border border-sidebar-border/70 bg-muted/30 px-4 py-3 text-sm text-muted-foreground dark:border-sidebar-border">
                    این پنل در این فاز فقط برای مشاهده و هماهنگی است. تایید
                    مالی، قرارداد اسپانسر، تغییر نقش‌ها و تنظیمات سراسری از این
                    صفحه انجام نمی‌شود.
                </section>
            </div>
        </>
    );
}

VenueDashboard.layout = {
    breadcrumbs: [
        {
            title: 'پنل مدیر مکان',
            href: '/venue/dashboard',
        },
    ],
};
