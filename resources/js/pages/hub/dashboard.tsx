import { Form, Head } from '@inertiajs/react';
import {
    BadgeCheck,
    Building2,
    CheckCircle2,
    Gift,
    Megaphone,
    MonitorPlay,
    Store,
    XCircle,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import type { ReactNode } from 'react';
import { Button } from '@/components/ui/button';

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

type Props = {
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
};

const statusLabels: Record<string, string> = {
    active: 'فعال',
    inactive: 'غیرفعال',
    draft: 'پیش نویس',
    pending_review: 'در انتظار تایید',
    approved: 'تایید شده',
    rejected: 'رد شده',
    scheduled: 'زمان‌بندی شده',
};

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
                <p>{label}</p>
            </div>
            <p className="mt-1 font-semibold">
                {value.toLocaleString('fa-IR')}
            </p>
        </div>
    );
}

export default function HubDashboard({
    stats,
    hubs,
    partners,
    adRequests,
    rewards,
    displayDevices,
}: Props) {
    return (
        <>
            <Head title="پنل مدیر رواق" />
            <div
                dir="rtl"
                className="flex h-full flex-1 flex-col gap-5 overflow-x-auto p-4"
            >
                <header className="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p className="text-sm text-muted-foreground">
                            دسترسی محدود به محدوده مدیریتی
                        </p>
                        <h1 className="mt-1 text-2xl font-semibold">
                            پنل مدیر رواق / هاب
                        </h1>
                    </div>
                    <div className="grid grid-cols-2 gap-3 text-sm sm:grid-cols-5">
                        <Stat icon={Building2} label="هاب" value={stats.hubs} />
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
                            icon={Gift}
                            label="پاداش منتظر"
                            value={stats.pendingRewards}
                        />
                        <Stat
                            icon={MonitorPlay}
                            label="نمایشگر"
                            value={stats.displayDevices}
                        />
                    </div>
                </header>

                <section className="rounded-lg border border-sidebar-border/70 bg-background dark:border-sidebar-border">
                    <div className="border-b border-sidebar-border/70 px-4 py-3 dark:border-sidebar-border">
                        <h2 className="font-semibold">هاب‌های تحت مدیریت</h2>
                    </div>
                    <div className="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                        {hubs.map((hub) => (
                            <article
                                key={hub.id}
                                className="grid gap-1 px-4 py-3 text-sm"
                            >
                                <p className="font-medium">{hub.name}</p>
                                <p
                                    className="text-xs text-muted-foreground"
                                    dir="ltr"
                                >
                                    {hub.code} · {hub.hubType}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    {hub.venueName ?? '-'}
                                </p>
                            </article>
                        ))}
                    </div>
                </section>

                <section className="grid gap-4 lg:grid-cols-2">
                    <Panel title="شرکای محدوده">
                        {partners.map((partner) => (
                            <article
                                key={partner.id}
                                className="grid gap-1 px-4 py-3 text-sm"
                            >
                                <div className="flex items-center justify-between gap-3">
                                    <p className="truncate font-medium">
                                        {partner.name}
                                    </p>
                                    <span className="shrink-0 text-xs text-muted-foreground">
                                        {statusLabels[partner.status ?? ''] ??
                                            partner.status}
                                    </span>
                                </div>
                                <p
                                    className="truncate text-xs text-muted-foreground"
                                    dir="ltr"
                                >
                                    {partner.code} · {partner.partnerType}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    {partner.hubName} · {partner.locationRole}
                                </p>
                            </article>
                        ))}
                    </Panel>

                    <Panel title="نمایشگرهای محدوده">
                        {displayDevices.map((device) => (
                            <article
                                key={device.id}
                                className="grid gap-1 px-4 py-3 text-sm"
                            >
                                <div className="flex items-center justify-between gap-3">
                                    <p className="truncate font-medium">
                                        {device.name}
                                    </p>
                                    <span className="shrink-0 text-xs text-muted-foreground">
                                        {statusLabels[device.status] ??
                                            device.status}
                                    </span>
                                </div>
                                <p
                                    className="truncate text-xs text-muted-foreground"
                                    dir="ltr"
                                >
                                    {device.code} · {device.deviceType}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    {device.hubName}
                                </p>
                            </article>
                        ))}
                    </Panel>
                </section>

                <section className="grid gap-4 lg:grid-cols-2">
                    <Panel title="تبلیغات در محدوده رواق">
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
                                            {adRequest.creativeType}
                                        </p>
                                    </div>
                                    <span className="shrink-0 rounded-full bg-muted px-2.5 py-1 text-xs">
                                        {statusLabels[adRequest.status] ??
                                            adRequest.status}
                                    </span>
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    شریک: {adRequest.partnerName ?? '-'} · هاب:{' '}
                                    {adRequest.hubName ?? '-'} · جایگاه:{' '}
                                    {adRequest.placementType ?? '-'}
                                </p>
                                {adRequest.status === 'pending_review' ? (
                                    <div className="flex flex-wrap gap-2 pt-1">
                                        <Form
                                            action={`/admin/ads/${adRequest.id}/approve`}
                                            method="post"
                                            options={{ preserveScroll: true }}
                                        >
                                            {({ processing }) => (
                                                <Button
                                                    size="sm"
                                                    disabled={processing}
                                                >
                                                    <CheckCircle2 className="size-4" />
                                                    تایید
                                                </Button>
                                            )}
                                        </Form>
                                        <Form
                                            action={`/admin/ads/${adRequest.id}/reject`}
                                            method="post"
                                            options={{ preserveScroll: true }}
                                        >
                                            {({ processing }) => (
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    disabled={processing}
                                                >
                                                    <XCircle className="size-4" />
                                                    رد
                                                </Button>
                                            )}
                                        </Form>
                                    </div>
                                ) : null}
                            </article>
                        ))}
                    </Panel>

                    <Panel title="پیشنهادها و پاداش‌های فروشگاهی">
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
                                        {statusLabels[reward.approvalStatus] ??
                                            reward.approvalStatus}
                                    </span>
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    شریک: {reward.partnerName ?? '-'} · کمپین:{' '}
                                    {reward.campaignName ?? '-'}
                                </p>
                                {reward.approvalStatus === 'pending_review' ? (
                                    <div className="flex flex-wrap gap-2 pt-1">
                                        <Form
                                            action={`/admin/rewards/${reward.id}/approve`}
                                            method="post"
                                            options={{ preserveScroll: true }}
                                        >
                                            {({ processing }) => (
                                                <Button
                                                    size="sm"
                                                    disabled={processing}
                                                >
                                                    <BadgeCheck className="size-4" />
                                                    تایید
                                                </Button>
                                            )}
                                        </Form>
                                        <Form
                                            action={`/admin/rewards/${reward.id}/reject`}
                                            method="post"
                                            options={{ preserveScroll: true }}
                                        >
                                            {({ processing }) => (
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    disabled={processing}
                                                >
                                                    <XCircle className="size-4" />
                                                    رد
                                                </Button>
                                            )}
                                        </Form>
                                    </div>
                                ) : null}
                            </article>
                        ))}
                    </Panel>
                </section>
            </div>
        </>
    );
}

function Panel({ title, children }: { title: string; children: ReactNode }) {
    return (
        <div className="rounded-lg border border-sidebar-border/70 bg-background dark:border-sidebar-border">
            <div className="border-b border-sidebar-border/70 px-4 py-3 dark:border-sidebar-border">
                <h2 className="font-semibold">{title}</h2>
            </div>
            <div className="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                {children}
            </div>
        </div>
    );
}

HubDashboard.layout = {
    breadcrumbs: [
        {
            title: 'پنل مدیر رواق',
            href: '/hub/dashboard',
        },
    ],
};
