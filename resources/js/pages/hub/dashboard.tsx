import { Form, Head } from '@inertiajs/react';
import {
    Building2,
    CheckCircle2,
    Gift,
    Megaphone,
    MonitorPlay,
    Store,
    XCircle,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { useState } from 'react';
import type { ReactNode } from 'react';
import { DateTimePickerField } from '@/components/date-time-picker-field';
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
    draft: 'پیش نویس',
    pending_review: 'در انتظار تایید',
    approved: 'تایید شده',
    rejected: 'رد شده',
    scheduled: 'زمان‌بندی شده',
};

function toDateTimeLocal(value: string | null) {
    if (!value) {
        return '';
    }

    const date = new Date(value);
    const offsetDate = new Date(
        date.getTime() - date.getTimezoneOffset() * 60000,
    );

    return offsetDate.toISOString().slice(0, 16);
}

function formatDate(value: string | null) {
    if (!value) {
        return null;
    }

    return new Intl.DateTimeFormat('fa-IR', {
        dateStyle: 'short',
        timeStyle: 'short',
    }).format(new Date(value));
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
                <p>{label}</p>
            </div>
            <p className="mt-1 font-semibold">
                {value.toLocaleString('fa-IR')}
            </p>
        </div>
    );
}

function ReviewActions({
    approveAction,
    rejectAction,
    placeholder,
}: {
    approveAction: string;
    rejectAction: string;
    placeholder: string;
}) {
    const [notes, setNotes] = useState('');

    return (
        <div className="grid gap-2 pt-1">
            <textarea
                name="review_notes"
                value={notes}
                onChange={(event) => setNotes(event.target.value)}
                maxLength={1000}
                rows={2}
                className="min-h-16 w-full resize-none rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                placeholder={placeholder}
            />
            <div className="flex flex-wrap gap-2">
                <Form
                    action={approveAction}
                    method="post"
                    options={{ preserveScroll: true }}
                >
                    {({ processing }) => (
                        <>
                            <input
                                type="hidden"
                                name="notes"
                                value={notes}
                                readOnly
                            />
                            <Button
                                size="sm"
                                type="submit"
                                disabled={processing}
                            >
                                <CheckCircle2 className="size-4" />
                                تایید
                            </Button>
                        </>
                    )}
                </Form>
                <Form
                    action={rejectAction}
                    method="post"
                    options={{ preserveScroll: true }}
                >
                    {({ processing }) => (
                        <>
                            <input
                                type="hidden"
                                name="notes"
                                value={notes}
                                readOnly
                            />
                            <Button
                                size="sm"
                                type="submit"
                                variant="outline"
                                disabled={processing}
                            >
                                <XCircle className="size-4" />
                                رد
                            </Button>
                        </>
                    )}
                </Form>
            </div>
        </div>
    );
}

function ScheduleActions({
    adRequest,
    displayDevices,
}: {
    adRequest: AdRequestItem;
    displayDevices: DisplayDeviceItem[];
}) {
    if (displayDevices.length === 0) {
        return (
            <p className="rounded-md bg-muted/50 px-3 py-2 text-xs text-muted-foreground">
                نمایشگر فعالی با نوع جایگاه این تبلیغ در محدوده شما ثبت نشده
                است.
            </p>
        );
    }

    return (
        <Form
            action={`/hub/ads/${adRequest.id}/schedule`}
            method="post"
            options={{ preserveScroll: true }}
        >
            {({ processing }) => (
                <div className="grid gap-2 rounded-md border border-sidebar-border/70 p-3 dark:border-sidebar-border">
                    <div className="grid gap-2 sm:grid-cols-2">
                        <label className="grid gap-1 text-xs text-muted-foreground">
                            نمایشگر
                            <select
                                name="display_device_id"
                                defaultValue={
                                    adRequest.displayDeviceId ??
                                    displayDevices[0]?.id ??
                                    ''
                                }
                                className="h-9 rounded-md border border-input bg-background px-3 text-sm text-foreground outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                            >
                                {displayDevices.map((device) => (
                                    <option key={device.id} value={device.id}>
                                        {device.name}
                                    </option>
                                ))}
                            </select>
                        </label>
                        <label className="grid gap-1 text-xs text-muted-foreground">
                            اولویت
                            <input
                                name="priority"
                                type="number"
                                min="1"
                                max="10"
                                defaultValue={adRequest.priority ?? 5}
                                className="h-9 rounded-md border border-input bg-background px-3 text-sm text-foreground outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                            />
                        </label>
                        <DateTimePickerField
                            name="starts_at"
                            label="شروع نمایش"
                            defaultValue={toDateTimeLocal(adRequest.startsAt)}
                            hint={null}
                            wrapperClassName="gap-1 text-xs text-muted-foreground"
                        />
                        <DateTimePickerField
                            name="ends_at"
                            label="پایان نمایش"
                            defaultValue={toDateTimeLocal(adRequest.endsAt)}
                            hint={null}
                            wrapperClassName="gap-1 text-xs text-muted-foreground"
                        />
                    </div>
                    <Button size="sm" type="submit" disabled={processing}>
                        <MonitorPlay className="size-4" />
                        زمان‌بندی روی نمایشگر
                    </Button>
                </div>
            )}
        </Form>
    );
}
function DecisionNote({
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
            {notes ? <p>یادداشت تصمیم: {notes}</p> : null}
            {reviewedAtLabel ? (
                <p className="mt-1">زمان تصمیم: {reviewedAtLabel}</p>
            ) : null}
        </div>
    );
}

function DisplayScheduleQueue({ items }: { items: DisplayScheduleItem[] }) {
    return (
        <Panel title="برنامه فعال نمایشگرها" isEmpty={items.length === 0}>
            {items.map((item) => (
                <article key={item.id} className="grid gap-2 px-4 py-3 text-sm">
                    <div className="flex items-center justify-between gap-3">
                        <div className="min-w-0">
                            <p className="truncate font-medium">
                                {item.adTitle}
                            </p>
                            <p
                                className="mt-1 truncate text-xs text-muted-foreground"
                                dir="ltr"
                            >
                                {item.adCode} · {item.placementType}
                            </p>
                        </div>
                        <span className="shrink-0 rounded-full bg-muted px-2.5 py-1 text-xs">
                            {statusLabels[item.status] ?? item.status}
                        </span>
                    </div>
                    <p className="text-xs text-muted-foreground">
                        نمایشگر: {item.displayDeviceName ?? '-'} · شریک:{' '}
                        {item.partnerName ?? '-'} · اولویت:{' '}
                        {item.priority.toLocaleString('fa-IR')}
                    </p>
                    <p className="text-xs text-muted-foreground">
                        شروع: {formatDate(item.startsAt) ?? '-'} · پایان:{' '}
                        {formatDate(item.endsAt) ?? '-'}
                    </p>
                    <Form
                        action={`/hub/ad-placements/${item.id}/cancel`}
                        method="post"
                        options={{ preserveScroll: true }}
                    >
                        {({ processing }) => (
                            <Button
                                size="sm"
                                type="submit"
                                variant="outline"
                                disabled={processing}
                            >
                                <XCircle className="size-4" />
                                لغو زمان‌بندی
                            </Button>
                        )}
                    </Form>
                </article>
            ))}
        </Panel>
    );
}
export default function HubDashboard({
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
            <Head title="پنل مدیر رواق / زون" />
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
                            پنل مدیر رواق / زون
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
                        {hubs.length === 0 ? <EmptyState /> : null}
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
                    <Panel title="شرکای محدوده" isEmpty={partners.length === 0}>
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

                    <Panel
                        title="نمایشگرهای محدوده"
                        isEmpty={displayDevices.length === 0}
                    >
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

                <DisplayScheduleQueue items={displayScheduleItems} />

                <section className="grid gap-4 lg:grid-cols-2">
                    <Panel
                        title="تبلیغات در محدوده رواق"
                        isEmpty={adRequests.length === 0}
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
                                {adRequest.displayDeviceName ? (
                                    <p className="text-xs text-muted-foreground">
                                        نمایشگر زمان‌بندی‌شده:{' '}
                                        {adRequest.displayDeviceName} · اولویت:{' '}
                                        {adRequest.priority?.toLocaleString(
                                            'fa-IR',
                                        ) ?? '-'}
                                    </p>
                                ) : null}
                                {adRequest.status === 'pending_review' ? (
                                    <ReviewActions
                                        approveAction={`/admin/ads/${adRequest.id}/approve`}
                                        rejectAction={`/admin/ads/${adRequest.id}/reject`}
                                        placeholder="یادداشت تایید یا دلیل رد تبلیغ"
                                    />
                                ) : (
                                    <>
                                        <DecisionNote
                                            notes={adRequest.reviewNotes}
                                            reviewedAt={adRequest.reviewedAt}
                                        />
                                        {adRequest.status === 'approved' ? (
                                            <ScheduleActions
                                                adRequest={adRequest}
                                                displayDevices={displayDevices.filter(
                                                    (device) =>
                                                        device.deviceType ===
                                                        adRequest.placementType,
                                                )}
                                            />
                                        ) : null}
                                    </>
                                )}
                            </article>
                        ))}
                    </Panel>

                    <Panel
                        title="پیشنهادها و پاداش‌های فروشگاهی"
                        isEmpty={rewards.length === 0}
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
                                        {statusLabels[reward.approvalStatus] ??
                                            reward.approvalStatus}
                                    </span>
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    شریک: {reward.partnerName ?? '-'} · کمپین:{' '}
                                    {reward.campaignName ?? '-'}
                                </p>
                                {reward.approvalStatus === 'pending_review' ? (
                                    <ReviewActions
                                        approveAction={`/admin/rewards/${reward.id}/approve`}
                                        rejectAction={`/admin/rewards/${reward.id}/reject`}
                                        placeholder="یادداشت تایید یا دلیل رد پیشنهاد"
                                    />
                                ) : (
                                    <DecisionNote
                                        notes={reward.reviewNotes}
                                        reviewedAt={reward.reviewedAt}
                                    />
                                )}
                            </article>
                        ))}
                    </Panel>
                </section>
            </div>
        </>
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
        <div className="rounded-lg border border-sidebar-border/70 bg-background dark:border-sidebar-border">
            <div className="border-b border-sidebar-border/70 px-4 py-3 dark:border-sidebar-border">
                <h2 className="font-semibold">{title}</h2>
            </div>
            <div className="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                {isEmpty ? <EmptyState /> : children}
            </div>
        </div>
    );
}

function EmptyState() {
    return (
        <p className="px-4 py-4 text-sm text-muted-foreground">
            موردی برای نمایش وجود ندارد.
        </p>
    );
}

HubDashboard.layout = {
    breadcrumbs: [
        {
            title: 'پنل مدیر رواق / زون',
            href: '/ravaq/dashboard',
        },
    ],
};
