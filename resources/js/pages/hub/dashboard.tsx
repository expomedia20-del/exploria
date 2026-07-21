import { Head } from '@inertiajs/react';
import {
    AlertTriangle,
    Building2,
    ClipboardCheck,
    Gift,
    Megaphone,
    MonitorPlay,
    Store,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import type { ReactNode } from 'react';

type HubItem = {
    id: string;
    code: string;
    name: string;
    hubType: string;
    venueName: string | null;
};

type PartnerItem = {
    id: string | null;
    code: string | null;
    name: string | null;
    partnerType: string | null;
    status: string | null;
    hubName: string | null;
    venueName: string | null;
    locationRole: string;
};

type AdRequestItem = {
    id: string;
    code: string;
    title: string;
    status: string;
    partnerName: string | null;
    hubName: string | null;
    venueName: string | null;
    creativeType: string | null;
    placementType: string | null;
    placementStatus: string | null;
    displayDeviceId: string | null;
    displayDeviceName: string | null;
    displayDeviceCode: string | null;
    startsAt: string | null;
    endsAt: string | null;
    priority: number | null;
    reviewNotes: string | null;
    reviewedAt: string | null;
};

type RewardItem = {
    id: string;
    code: string;
    name: string;
    rewardType: string;
    status: string;
    approvalStatus: string;
    partnerName: string | null;
    campaignName: string | null;
    reviewNotes: string | null;
    reviewedAt: string | null;
};

type DisplayDeviceItem = {
    id: string;
    code: string;
    name: string;
    deviceType: string;
    status: string;
    hubName: string | null;
    venueName: string | null;
};

type DisplayScheduleItem = {
    id: string;
    adRequestId: string;
    adTitle: string | null;
    adCode: string | null;
    partnerName: string | null;
    displayDeviceId: string | null;
    displayDeviceName: string | null;
    displayDeviceCode: string | null;
    placementType: string;
    status: string;
    priority: number;
    startsAt: string | null;
    endsAt: string | null;
};

type Props = {
    panelContext?: {
        title: string;
        subtitle: string;
        areaLabel: string;
        scopeNote: string;
    };
    stats: {
        hubs: number;
        partners: number;
        pendingAds: number;
        pendingRewards: number;
        displayDevices: number;
    };
    hubs: HubItem[];
    partners: PartnerItem[];
    adRequests: AdRequestItem[];
    rewards: RewardItem[];
    displayDevices: DisplayDeviceItem[];
    displayScheduleItems: DisplayScheduleItem[];
};

const statusLabels: Record<string, string> = {
    active: 'فعال',
    inactive: 'غیرفعال',
    draft: 'پیش‌نویس',
    pending_review: 'نیازمند بررسی',
    approved: 'تایید شده',
    rejected: 'رد شده',
    scheduled: 'زمان‌بندی شده',
};

function formatDate(value: string | null) {
    if (!value) {
        return null;
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
                <p className="min-w-0 text-xs leading-5">{label}</p>
            </div>
            <p className="mt-1 font-semibold">{formatNumber(value)}</p>
        </div>
    );
}

function OperationalBoundaryNote({
    tone = 'info',
    title,
    children,
}: {
    tone?: 'info' | 'warning';
    title: string;
    children: ReactNode;
}) {
    const Icon = tone === 'warning' ? AlertTriangle : ClipboardCheck;

    return (
        <div className="flex gap-2 rounded-md border border-sidebar-border/70 bg-muted/40 px-3 py-2 text-xs text-muted-foreground dark:border-sidebar-border">
            <Icon className="mt-0.5 size-4 shrink-0 text-foreground" />
            <div className="grid gap-1">
                <p className="font-medium text-foreground">{title}</p>
                <p className="leading-6">{children}</p>
            </div>
        </div>
    );
}

function ReviewTrail({
    notes,
    reviewedAt,
}: {
    notes: string | null;
    reviewedAt: string | null;
}) {
    const reviewedAtLabel = formatDate(reviewedAt);

    if (!notes && !reviewedAtLabel) {
        return null;
    }

    return (
        <div className="rounded-md bg-muted/50 px-3 py-2 text-xs text-muted-foreground">
            {notes ? <p>یادداشت بررسی اکسپلوریا: {notes}</p> : null}
            {reviewedAtLabel ? (
                <p className="mt-1">زمان ثبت بررسی: {reviewedAtLabel}</p>
            ) : null}
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

function DisplayScheduleQueue({ items }: { items: DisplayScheduleItem[] }) {
    return (
        <Panel
            title="برنامه فعال نمایشگرها"
            description="برای اطلاع مدیر رواق از نظم پخش در محدوده؛ تغییر زمان‌بندی در اختیار تیم تبلیغات اکسپلوریا است."
            isEmpty={items.length === 0}
        >
            {items.map((item) => (
                <article key={item.id} className="grid gap-2 px-4 py-3 text-sm">
                    <div className="flex items-center justify-between gap-3">
                        <div className="min-w-0">
                            <p className="truncate font-medium">
                                {item.adTitle ?? '-'}
                            </p>
                            <p
                                className="mt-1 truncate text-xs text-muted-foreground"
                                dir="ltr"
                            >
                                {item.adCode ?? '-'} · {item.placementType}
                            </p>
                        </div>
                        <span className="shrink-0 rounded-full bg-muted px-2.5 py-1 text-xs">
                            {labelForStatus(item.status)}
                        </span>
                    </div>
                    <p className="text-xs text-muted-foreground">
                        نمایشگر: {item.displayDeviceName ?? '-'} · شریک:{' '}
                        {item.partnerName ?? '-'} · اولویت:{' '}
                        {formatNumber(item.priority)}
                    </p>
                    <p className="text-xs text-muted-foreground">
                        شروع: {formatDate(item.startsAt) ?? '-'} · پایان:{' '}
                        {formatDate(item.endsAt) ?? '-'}
                    </p>
                    <OperationalBoundaryNote title="حد اختیار مدیر رواق">
                        مدیر رواق می‌تواند مشکل اجرایی، ازدحام، مغایرت با قوانین
                        مجموعه یا خرابی نمایشگر را گزارش کند؛ لغو یا تغییر پخش
                        از این پنل انجام نمی‌شود.
                    </OperationalBoundaryNote>
                </article>
            ))}
        </Panel>
    );
}

export default function HubDashboard({
    panelContext = {
        title: 'پنل مدیر هاب',
        subtitle: 'نظارت اجرایی بر هاب‌ها، مجموعه‌های تخصصی و واحدهای وابسته',
        areaLabel: 'هاب',
        scopeNote: 'در این نما همه هاب‌های تحت مدیریت همین حساب نمایش داده می‌شوند.',
    },
    stats,
    hubs,
    partners,
    adRequests,
    rewards,
    displayDevices,
    displayScheduleItems,
}: Props) {
    return (
        <>
            <Head title={panelContext.title} />
            <div
                dir="rtl"
                className="flex h-full min-w-0 flex-1 flex-col gap-5 overflow-x-hidden p-3 sm:p-4"
            >
                <header className="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div className="min-w-0">
                        <p className="text-sm text-muted-foreground">
                            {panelContext.subtitle}
                        </p>
                        <h1 className="mt-1 text-2xl leading-tight font-semibold">
                            {panelContext.title}
                        </h1>
                    </div>
                    <div className="grid w-full grid-cols-2 gap-2 text-sm sm:grid-cols-3 md:w-auto xl:grid-cols-5">
                        <Stat
                            icon={Building2}
                            label="محدوده"
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
                            icon={Gift}
                            label="پاداش نیازمند بررسی"
                            value={stats.pendingRewards}
                        />
                        <Stat
                            icon={MonitorPlay}
                            label="نمایشگر"
                            value={stats.displayDevices}
                        />
                    </div>
                </header>

                <OperationalBoundaryNote title="تعریف نقش این پنل">
                    این پنل برای نظم، آمادگی، هماهنگی و اعلام مغایرت‌های اجرایی
                    {` ${panelContext.areaLabel} `}است. تصمیم تجاری هر
                    فروشگاه، قیمت‌گذاری، درآمد، نوع پاداش، قرارداد اسپانسر و
                    تایید نهایی تبلیغ از این پنل انجام نمی‌شود.
                </OperationalBoundaryNote>

                <Panel
                    title="محدوده‌های تحت مدیریت"
                    description={panelContext.scopeNote}
                    isEmpty={hubs.length === 0}
                >
                    {hubs.map((hub) => (
                        <article
                            key={hub.id}
                            className="grid gap-1 px-4 py-3 text-sm"
                        >
                            <p className="font-medium">{hub.name}</p>
                            <p className="text-xs text-muted-foreground">
                                {hub.venueName ?? '-'}
                            </p>
                        </article>
                    ))}
                </Panel>

                <section className="grid gap-4 lg:grid-cols-2">
                    <Panel
                        title="واحدها و شرکای محدوده"
                        description="برای هماهنگی اجرایی و آمادگی روز اجرا؛ نه بررسی درآمد یا تصمیم تجاری واحد."
                        isEmpty={partners.length === 0}
                    >
                        {partners.map((partner) => (
                            <article
                                key={partner.id ?? partner.code}
                                className="grid gap-1 px-4 py-3 text-sm"
                            >
                                <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                    <p className="truncate font-medium">
                                        {partner.name ?? '-'}
                                    </p>
                                    <span className="w-fit shrink-0 text-xs text-muted-foreground">
                                        {labelForStatus(partner.status)}
                                    </span>
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    محدوده: {partner.hubName ?? '-'}
                                </p>
                            </article>
                        ))}
                    </Panel>

                    <Panel
                        title="نمایشگرهای محدوده"
                        description="پایش سلامت و محل نمایشگر؛ برنامه‌ریزی محتوا با تیم تبلیغات اکسپلوریا است."
                        isEmpty={displayDevices.length === 0}
                    >
                        {displayDevices.map((device) => (
                            <article
                                key={device.id}
                                className="grid gap-1 px-4 py-3 text-sm"
                            >
                                <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                    <p className="truncate font-medium">
                                        {device.name}
                                    </p>
                                    <span className="w-fit shrink-0 text-xs text-muted-foreground">
                                        {labelForStatus(device.status)}
                                    </span>
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    {device.hubName ?? '-'}
                                </p>
                            </article>
                        ))}
                    </Panel>
                </section>

                <DisplayScheduleQueue items={displayScheduleItems} />

                <section className="grid gap-4 lg:grid-cols-2">
                    <Panel
                        title={`تبلیغات محدوده ${panelContext.areaLabel} - پایش اجرایی`}
                        description={`مدیر ${panelContext.areaLabel} مغایرت با قوانین مجموعه و مشکلات اجرایی را اعلام می‌کند؛ تایید نهایی تبلیغ با اکسپلوریا است.`}
                        isEmpty={adRequests.length === 0}
                    >
                        {adRequests.map((adRequest) => (
                            <article
                                key={adRequest.id}
                                className="grid gap-2 px-4 py-3 text-sm"
                            >
                                <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                    <div className="min-w-0">
                                        <p className="truncate font-medium">
                                            {adRequest.title}
                                        </p>
                                    </div>
                                    <span className="w-fit shrink-0 rounded-full bg-muted px-2.5 py-1 text-xs">
                                        {labelForStatus(adRequest.status)}
                                    </span>
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    شریک: {adRequest.partnerName ?? '-'} ·
                                    محدوده: {adRequest.hubName ?? '-'} · جایگاه:{' '}
                                    {adRequest.placementType ?? '-'}
                                </p>
                                {adRequest.displayDeviceName ? (
                                    <p className="text-xs text-muted-foreground">
                                        نمایشگر زمان‌بندی‌شده:{' '}
                                        {adRequest.displayDeviceName} · اولویت:{' '}
                                        {formatNumber(adRequest.priority)}
                                    </p>
                                ) : null}
                                {adRequest.status === 'pending_review' ? (
                                    <OperationalBoundaryNote
                                        tone="warning"
                                        title="نیازمند بررسی اکسپلوریا"
                                    >
                                        مدیر {panelContext.areaLabel} فقط
                                        محدودیت‌های اجرایی، تعارض با قوانین
                                        مجموعه، ازدحام یا مشکل محل نمایش را
                                        گزارش می‌کند. تایید یا رد تبلیغ تصمیم
                                        تجاری این پنل نیست.
                                    </OperationalBoundaryNote>
                                ) : null}
                                <ReviewTrail
                                    notes={adRequest.reviewNotes}
                                    reviewedAt={adRequest.reviewedAt}
                                />
                            </article>
                        ))}
                    </Panel>

                    <Panel
                        title={`پیشنهادها و پاداش‌های واحدها - پایش مقررات ${panelContext.areaLabel}`}
                        description={`مدیر ${panelContext.areaLabel} مالک نوع پاداش یا ارزش اقتصادی پیشنهاد نیست؛ فقط آمادگی و مغایرت اجرایی را پایش می‌کند.`}
                        isEmpty={rewards.length === 0}
                    >
                        {rewards.map((reward) => (
                            <article
                                key={reward.id}
                                className="grid gap-2 px-4 py-3 text-sm"
                            >
                                <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                    <div className="min-w-0">
                                        <p className="truncate font-medium">
                                            {reward.name}
                                        </p>
                                    </div>
                                    <span className="w-fit shrink-0 rounded-full bg-muted px-2.5 py-1 text-xs">
                                        {labelForStatus(reward.approvalStatus)}
                                    </span>
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    شریک: {reward.partnerName ?? '-'} · کمپین:{' '}
                                    {reward.campaignName ?? '-'}
                                </p>
                                {reward.approvalStatus === 'pending_review' ? (
                                    <OperationalBoundaryNote
                                        tone="warning"
                                        title="نظر رواق، نه تصمیم تجاری"
                                    >
                                        نوع پاداش، ارزش اقتصادی و شرایط فروشگاهی
                                        در اختیار واحد تجاری و اکسپلوریا است.
                                        مدیر {panelContext.areaLabel} فقط
                                        مغایرت با مقررات مجموعه یا مانع اجرایی
                                        را اعلام می‌کند.
                                    </OperationalBoundaryNote>
                                ) : null}
                                <ReviewTrail
                                    notes={reward.reviewNotes}
                                    reviewedAt={reward.reviewedAt}
                                />
                            </article>
                        ))}
                    </Panel>
                </section>
            </div>
        </>
    );
}

HubDashboard.layout = {
    breadcrumbs: [
        {
            title: 'پنل مدیر هاب / رواق',
            href: '/hub/dashboard',
        },
    ],
};
