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
    pending_review: 'نیازمند بررسی',
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

function formatNumber(value: number | null | undefined) {
    return (value ?? 0).toLocaleString('fa-IR');
}

function labelForStatus(status: string | null | undefined) {
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
        <div className="min-w-0 rounded-lg border border-sidebar-border/70 bg-background px-3 py-2 dark:border-sidebar-border">
            <div className="flex min-w-0 items-center gap-2 text-muted-foreground">
                <Icon className="size-4 shrink-0" />
                <span className="min-w-0 text-xs leading-5">{label}</span>
            </div>
            <p className="mt-2 text-lg font-semibold">{formatNumber(value)}</p>
        </div>
    );
}

function Panel({
    title,
    description,
    children,
    isEmpty = false,
}: {
    title: string;
    description?: string;
    children: ReactNode;
    isEmpty?: boolean;
}) {
    return (
        <section className="overflow-hidden rounded-lg border border-sidebar-border/70 bg-background dark:border-sidebar-border">
            <div className="border-b border-sidebar-border/70 px-4 py-3 dark:border-sidebar-border">
                <h2 className="font-semibold">{title}</h2>
                {description ? (
                    <p className="mt-1 text-xs text-muted-foreground">
                        {description}
                    </p>
                ) : null}
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

function ManagementNote({ children }: { children: ReactNode }) {
    return (
        <section className="rounded-lg border border-sidebar-border/70 bg-muted/30 px-4 py-3 text-sm leading-7 text-muted-foreground dark:border-sidebar-border">
            {children}
        </section>
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
            <Head title="پنل مدیر اجرایی مکان" />
            <div
                dir="rtl"
                className="flex h-full min-w-0 flex-1 flex-col gap-5 overflow-x-hidden p-3 sm:p-4"
            >
                <header className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div className="min-w-0">
                        <p className="text-sm text-muted-foreground">
                            نمای مدیریتی و read-only برای آمادگی مکان، هماهنگی
                            ذی‌نفعان و ریسک‌های روز اجرا
                        </p>
                        <h1 className="mt-1 text-2xl leading-tight font-semibold">
                            پنل مدیر اجرایی مکان
                        </h1>
                    </div>
                    <div className="grid w-full grid-cols-2 gap-2 text-sm sm:grid-cols-3 lg:w-auto xl:grid-cols-5 2xl:grid-cols-9">
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
                            label="واحد/شریک"
                            value={stats.partners}
                        />
                        <Stat
                            icon={Megaphone}
                            label="تبلیغ نیازمند بررسی"
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

                <ManagementNote>
                    مدیر اجرایی مکان دید کلان دارد: آمادگی مکان، جریان
                    بازدیدکننده، وضعیت هاب‌ها، ریسک‌های اجرایی و اثر کمپین. این
                    پنل وارد تصمیم تجاری هر فروشگاه، قیمت، درآمد، موجودی یا نوع
                    پاداش اختصاصی واحدها نمی‌شود.
                </ManagementNote>

                <Panel
                    title="مکان‌های تحت مدیریت"
                    description="سطح دسترسی کلان برای خود مکان پروژه."
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
                        description="برای پایش اثر کلی کمپین، حجم عملیات و آمادگی روز اجرا."
                        isEmpty={campaigns.length === 0}
                    >
                        {campaigns.map((campaign) => (
                            <article
                                key={campaign.id}
                                className="grid gap-2 px-4 py-3 text-sm"
                            >
                                <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
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
                                    <span className="w-fit shrink-0 rounded-full bg-muted px-2.5 py-1 text-xs">
                                        {labelForStatus(campaign.status)}
                                    </span>
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    ماموریت:{' '}
                                    {formatNumber(campaign.missionCount)} ·
                                    پاداش: {formatNumber(campaign.rewardCount)}{' '}
                                    · گنج:{' '}
                                    {formatNumber(campaign.treasureCount)} ·
                                    مشارکت:{' '}
                                    {formatNumber(campaign.participantCount)}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    شروع: {formatDate(campaign.startsAt)} ·
                                    پایان: {formatDate(campaign.endsAt)}
                                </p>
                            </article>
                        ))}
                    </Panel>

                    <Panel
                        title="هاب‌ها و رواق‌ها"
                        description="نمای کلان از زیرمجموعه‌های مکان؛ نه مدیریت جزئی واحدهای تابعه."
                        isEmpty={hubs.length === 0}
                    >
                        {hubs.map((hub) => (
                            <article
                                key={hub.id}
                                className="grid gap-2 px-4 py-3 text-sm"
                            >
                                <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
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
                                    <span className="w-fit shrink-0 rounded-full bg-muted px-2.5 py-1 text-xs">
                                        {labelForStatus(hub.status)}
                                    </span>
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    زون: {hub.zoneName ?? '-'} · واحد/شریک:{' '}
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
                        description="نمای تجمیعی بر اساس محدوده؛ جزئیات مالی یا تصمیم تجاری هر واحد نمایش داده نمی‌شود."
                        isEmpty={partners.length === 0}
                    >
                        {partners.map((partner) => (
                            <article
                                key={partner.id}
                                className="grid gap-2 px-4 py-3 text-sm"
                            >
                                <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
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
                                    <span className="w-fit shrink-0 rounded-full bg-muted px-2.5 py-1 text-xs">
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
                        description="برای پایش سلامت اجرای میدانی؛ تنظیم محتوای تبلیغاتی با تیم مربوطه است."
                        isEmpty={displayDevices.length === 0}
                    >
                        {displayDevices.map((device) => (
                            <article
                                key={device.id}
                                className="grid gap-2 px-4 py-3 text-sm"
                            >
                                <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
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
                                    <span className="w-fit shrink-0 rounded-full bg-muted px-2.5 py-1 text-xs">
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
                        title="خلاصه تبلیغات و زمان‌بندی نمایشگر"
                        description="نمای مدیریتی برای ریسک، تراکم و هماهنگی؛ تایید محتوا و زمان‌بندی در پنل‌های عملیاتی اکسپلوریا انجام می‌شود."
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
                                <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                    <div className="min-w-0">
                                        <p className="truncate font-medium">
                                            {adRequest.hubName ?? 'تبلیغ مکان'}
                                        </p>
                                        <p
                                            className="mt-1 truncate text-xs text-muted-foreground"
                                            dir="ltr"
                                        >
                                            {adRequest.adType} ·{' '}
                                            {adRequest.advertiserType}
                                        </p>
                                    </div>
                                    <span className="w-fit shrink-0 rounded-full bg-muted px-2.5 py-1 text-xs">
                                        {labelForStatus(adRequest.status)}
                                    </span>
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    محدوده: {adRequest.hubName ?? '-'} ·
                                    نمایشگر:{' '}
                                    {adRequest.displayDeviceName ?? '-'} · بازه:{' '}
                                    {formatDate(adRequest.startsAt)} تا{' '}
                                    {formatDate(adRequest.endsAt)}
                                </p>
                            </article>
                        ))}
                        {displayScheduleItems.map((item) => (
                            <article
                                key={item.id}
                                className="grid gap-2 px-4 py-3 text-sm"
                            >
                                <p className="font-medium">
                                    پخش زمان‌بندی‌شده در نمایشگر:{' '}
                                    {item.displayDeviceName ?? '-'}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    جایگاه: {item.placementType} · وضعیت:{' '}
                                    {labelForStatus(item.status)} · بازه:{' '}
                                    {formatDate(item.startsAt)} تا{' '}
                                    {formatDate(item.endsAt)}
                                </p>
                            </article>
                        ))}
                    </Panel>

                    <Panel
                        title="خلاصه پاداش‌ها و گنج‌ها"
                        description="برای سنجش آمادگی کمپین و اثر بازدیدکننده؛ جزئیات موجودی و ارزش اقتصادی واحدها در این پنل تصمیم‌گیری نمی‌شود."
                        isEmpty={rewards.length === 0 && treasures.length === 0}
                    >
                        {rewards.map((reward) => (
                            <article
                                key={reward.id}
                                className="grid gap-2 px-4 py-3 text-sm"
                            >
                                <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                    <div className="min-w-0">
                                        <p className="truncate font-medium">
                                            {reward.campaignName ??
                                                'پاداش کمپین'}
                                        </p>
                                        <p
                                            className="mt-1 truncate text-xs text-muted-foreground"
                                            dir="ltr"
                                        >
                                            {reward.rewardType} ·{' '}
                                            {reward.status}
                                        </p>
                                    </div>
                                    <span className="w-fit shrink-0 rounded-full bg-muted px-2.5 py-1 text-xs">
                                        {labelForStatus(reward.approvalStatus)}
                                    </span>
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    محدوده تصمیم‌گیری تجاری با واحد/اسپانسر و
                                    اکسپلوریا است؛ اینجا فقط وضعیت کلی برای
                                    آمادگی مکان نمایش داده می‌شود.
                                </p>
                            </article>
                        ))}
                        {treasures.map((treasure) => (
                            <article
                                key={treasure.id}
                                className="grid gap-2 px-4 py-3 text-sm"
                            >
                                <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                    <div className="min-w-0">
                                        <p className="truncate font-medium">
                                            {treasure.campaignName ??
                                                'گنج کمپین'}
                                        </p>
                                        <p
                                            className="mt-1 truncate text-xs text-muted-foreground"
                                            dir="ltr"
                                        >
                                            {treasure.treasureType} ·{' '}
                                            {treasure.missionCode ?? '-'}
                                        </p>
                                    </div>
                                    <span className="w-fit shrink-0 rounded-full bg-amber-100 px-2.5 py-1 text-xs text-amber-800 dark:bg-amber-950 dark:text-amber-200">
                                        گنج
                                    </span>
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    وضعیت: {labelForStatus(treasure.status)}
                                </p>
                            </article>
                        ))}
                    </Panel>
                </section>

                <ManagementNote>
                    این پنل فقط برای مشاهده و هماهنگی مدیریتی است. تایید مالی،
                    قرارداد اسپانسر، تصمیم پاداش فروشگاهی، قیمت‌گذاری، تغییر
                    نقش‌ها و تنظیمات سراسری از این صفحه انجام نمی‌شود.
                </ManagementNote>
            </div>
        </>
    );
}

VenueDashboard.layout = {
    breadcrumbs: [
        {
            title: 'پنل مدیر اجرایی مکان',
            href: '/venue/dashboard',
        },
    ],
};
