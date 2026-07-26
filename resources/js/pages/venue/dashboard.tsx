import { Head } from '@inertiajs/react';
import {
    ArrowLeft,
    Building2,
    Gem,
    Gift,
    LayoutDashboard,
    Megaphone,
    MonitorPlay,
    Route,
    Store,
    TicketCheck,
    Trophy,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { useState } from 'react';
import type { ReactNode } from 'react';
import { RoleDashboardNavigation } from '@/components/dashboard/role-dashboard-navigation';
import { Button } from '@/components/ui/button';

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

type VenueDashboardSection =
    | 'overview'
    | 'campaigns'
    | 'network'
    | 'media'
    | 'rewards';

const venueDashboardSections: VenueDashboardSection[] = [
    'overview',
    'campaigns',
    'network',
    'media',
    'rewards',
];

function initialVenueSection(): VenueDashboardSection {
    if (typeof window === 'undefined') {
        return 'overview';
    }

    const hash = window.location.hash.replace('#', '');

    return venueDashboardSections.includes(hash as VenueDashboardSection)
        ? (hash as VenueDashboardSection)
        : 'overview';
}

const statusLabels: Record<string, string> = {
    active: 'فعال',
    inactive: 'غیرفعال',
    draft: 'پیش‌نویس',
    placeholder: 'رزرو / جای‌نگهدار',
    pending_review: 'نیازمند بررسی',
    approved: 'تایید شده',
    rejected: 'رد شده',
    scheduled: 'زمان‌بندی شده',
    revision_requested: 'نیازمند اصلاح',
    paused: 'متوقف شده',
    archived: 'بایگانی شده',
};

const adTypeLabels: Record<string, string> = {
    standalone: 'تبلیغ مستقل',
    display_takeover: 'جایگاه ویژه نمایشگر',
    reward_moment: 'همراه لحظه پاداش',
    rewarded_content: 'پیشنهاد اختیاری امتیازآور',
};

const advertiserTypeLabels: Record<string, string> = {
    member_partner: 'واحد تجاری عضو',
    sponsor: 'اسپانسر',
};

const placementLabels: Record<string, string> = {
    fixed_display: 'نمایشگر ثابت',
    mobile_display: 'نمایشگر سیار',
    public_feed: 'ویترین عمومی',
    qr_landing: 'صفحه ورود QR',
    reward_page: 'صفحه پاداش',
    map_route: 'نقشه بازی',
    post_mission: 'پس از مأموریت',
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
    const [activeSection, setActiveSection] =
        useState<VenueDashboardSection>(initialVenueSection);
    const nextSection: VenueDashboardSection =
        stats.pendingAds > 0
            ? 'media'
            : stats.activeCampaigns > 0
              ? 'campaigns'
              : 'network';
    const nextAction =
        stats.pendingAds > 0
            ? `${formatNumber(stats.pendingAds)} تبلیغ نیازمند بررسی هماهنگی است.`
            : stats.activeCampaigns > 0
              ? 'آمادگی کمپین‌های فعال و حجم عملیات را مرور کنید.'
              : 'وضعیت هاب‌ها، رواق‌ها و واحدهای زیرمجموعه را بررسی کنید.';

    const navigateTo = (section: VenueDashboardSection) => {
        setActiveSection(section);

        if (typeof window !== 'undefined') {
            window.history.replaceState(null, '', `#${section}`);
            window.requestAnimationFrame(() =>
                window.scrollTo({ top: 0, behavior: 'smooth' }),
            );
        }
    };

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
                            نمای مدیریتی و فقط‌خواندنی برای آمادگی مکان، هماهنگی
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
                            label="واحد تجاری/حامی"
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

                <RoleDashboardNavigation
                    activeSection={activeSection}
                    onSelect={navigateTo}
                    items={[
                        {
                            key: 'overview',
                            label: 'نمای کلی',
                            description: 'اولویت امروز و محدوده دسترسی',
                            icon: LayoutDashboard,
                        },
                        {
                            key: 'campaigns',
                            label: 'کمپین‌ها',
                            description: 'آمادگی و حجم عملیات',
                            icon: Trophy,
                            badge: stats.activeCampaigns,
                        },
                        {
                            key: 'network',
                            label: 'شبکه مکان',
                            description: 'هاب، رواق و واحدها',
                            icon: Route,
                        },
                        {
                            key: 'media',
                            label: 'تبلیغات و پخش',
                            description: 'نمایشگر و هماهنگی',
                            icon: MonitorPlay,
                            badge: stats.pendingAds,
                        },
                        {
                            key: 'rewards',
                            label: 'پاداش و گنج',
                            description: 'اثر تجربه و مصرف',
                            icon: Gift,
                        },
                    ]}
                />

                {activeSection === 'overview' ? (
                    <div className="grid gap-4 lg:grid-cols-[1.35fr_1fr]">
                        <section className="rounded-xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900 dark:bg-emerald-950/30">
                            <p className="text-xs font-medium text-emerald-700 dark:text-emerald-300">
                                اولویت مدیریتی امروز
                            </p>
                            <h2 className="mt-1 text-lg font-semibold">
                                {nextAction}
                            </h2>
                            <p className="mt-2 text-sm leading-7 text-muted-foreground">
                                این پنل برای تصمیم کلان و تشخیص ریسک است؛ جزئیات
                                اجرایی هر بخش جداگانه در دسترس قرار دارد.
                            </p>
                            <Button
                                className="mt-3 gap-2"
                                onClick={() => navigateTo(nextSection)}
                            >
                                مشاهده جزئیات
                                <ArrowLeft className="size-4" />
                            </Button>
                        </section>
                        <ManagementNote>
                            مدیر اجرایی مکان دید کلان دارد: آمادگی مکان، جریان
                            بازدیدکننده، وضعیت هاب‌ها، ریسک‌های اجرایی و اثر
                            کمپین. این پنل وارد تصمیم تجاری هر فروشگاه، قیمت،
                            درآمد، موجودی یا نوع پاداش اختصاصی واحدها نمی‌شود.
                        </ManagementNote>
                    </div>
                ) : null}

                {activeSection === 'overview' ? (
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
                                    <p className="mt-1 text-xs text-muted-foreground">
                                        {venue.city}
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
                ) : null}

                <section className="grid gap-4 xl:grid-cols-2">
                    {activeSection === 'campaigns' ? (
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
                                        </div>
                                        <span className="w-fit shrink-0 rounded-full bg-muted px-2.5 py-1 text-xs">
                                            {labelForStatus(campaign.status)}
                                        </span>
                                    </div>
                                    <p className="text-xs text-muted-foreground">
                                        ماموریت:{' '}
                                        {formatNumber(campaign.missionCount)} ·
                                        پاداش:{' '}
                                        {formatNumber(campaign.rewardCount)} ·
                                        گنج:{' '}
                                        {formatNumber(campaign.treasureCount)} ·
                                        مشارکت:{' '}
                                        {formatNumber(
                                            campaign.participantCount,
                                        )}
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        شروع: {formatDate(campaign.startsAt)} ·
                                        پایان: {formatDate(campaign.endsAt)}
                                    </p>
                                </article>
                            ))}
                        </Panel>
                    ) : null}

                    {activeSection === 'network' ? (
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
                                        </div>
                                        <span className="w-fit shrink-0 rounded-full bg-muted px-2.5 py-1 text-xs">
                                            {labelForStatus(hub.status)}
                                        </span>
                                    </div>
                                    <p className="text-xs text-muted-foreground">
                                        زون: {hub.zoneName ?? '-'} · واحد
                                        تجاری/حامی:{' '}
                                        {formatNumber(hub.partnerCount)} ·
                                        نمایشگر:{' '}
                                        {formatNumber(hub.displayCount)} ·
                                        ماموریت:{' '}
                                        {formatNumber(hub.missionCount)}
                                    </p>
                                </article>
                            ))}
                        </Panel>
                    ) : null}
                </section>

                <section className="grid gap-4 xl:grid-cols-2">
                    {activeSection === 'network' ? (
                        <Panel
                            title="خلاصه مدیریتی واحدهای تجاری و حامیان"
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
                                        </div>
                                        <span className="w-fit shrink-0 rounded-full bg-muted px-2.5 py-1 text-xs">
                                            {labelForStatus(partner.status)}
                                        </span>
                                    </div>
                                    <p className="text-xs text-muted-foreground">
                                        محدوده: {partner.hubName ?? '-'} ·
                                        پاداش:{' '}
                                        {formatNumber(partner.rewardCount)} ·
                                        مصرف:{' '}
                                        {formatNumber(partner.redemptionCount)}{' '}
                                        · تبلیغ: {formatNumber(partner.adCount)}
                                    </p>
                                </article>
                            ))}
                        </Panel>
                    ) : null}

                    {activeSection === 'media' ? (
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
                    ) : null}
                </section>

                <section className="grid gap-4 xl:grid-cols-2">
                    {activeSection === 'media' ? (
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
                                                {adRequest.hubName ??
                                                    'تبلیغ مکان'}
                                            </p>
                                            <p
                                                className="mt-1 truncate text-xs text-muted-foreground"
                                                dir="ltr"
                                            >
                                                {adTypeLabels[
                                                    adRequest.adType
                                                ] ?? adRequest.adType}{' '}
                                                ·{' '}
                                                {advertiserTypeLabels[
                                                    adRequest.advertiserType
                                                ] ?? adRequest.advertiserType}
                                            </p>
                                        </div>
                                        <span className="w-fit shrink-0 rounded-full bg-muted px-2.5 py-1 text-xs">
                                            {labelForStatus(adRequest.status)}
                                        </span>
                                    </div>
                                    <p className="text-xs text-muted-foreground">
                                        محدوده: {adRequest.hubName ?? '-'} ·
                                        نمایشگر:{' '}
                                        {adRequest.displayDeviceName ?? '-'} ·
                                        بازه: {formatDate(adRequest.startsAt)}{' '}
                                        تا {formatDate(adRequest.endsAt)}
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
                                        جایگاه:{' '}
                                        {placementLabels[item.placementType] ??
                                            item.placementType}{' '}
                                        · وضعیت: {labelForStatus(item.status)} ·
                                        بازه: {formatDate(item.startsAt)} تا{' '}
                                        {formatDate(item.endsAt)}
                                    </p>
                                </article>
                            ))}
                        </Panel>
                    ) : null}

                    {activeSection === 'rewards' ? (
                        <Panel
                            title="خلاصه پاداش‌ها و گنج‌ها"
                            description="برای سنجش آمادگی کمپین و اثر بازدیدکننده؛ جزئیات موجودی و ارزش اقتصادی واحدها در این پنل تصمیم‌گیری نمی‌شود."
                            isEmpty={
                                rewards.length === 0 && treasures.length === 0
                            }
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
                                            {labelForStatus(
                                                reward.approvalStatus,
                                            )}
                                        </span>
                                    </div>
                                    <p className="text-xs text-muted-foreground">
                                        محدوده تصمیم‌گیری تجاری با واحد/اسپانسر
                                        و اکسپلوریا است؛ اینجا فقط وضعیت کلی
                                        برای آمادگی مکان نمایش داده می‌شود.
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
                    ) : null}
                </section>

                {activeSection === 'overview' ? (
                    <ManagementNote>
                        این پنل فقط برای مشاهده و هماهنگی مدیریتی است. تایید
                        مالی، قرارداد اسپانسر، تصمیم پاداش فروشگاهی، قیمت‌گذاری،
                        تغییر نقش‌ها و تنظیمات سراسری از این صفحه انجام نمی‌شود.
                    </ManagementNote>
                ) : null}
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
