import { Form, Head, Link, usePage } from '@inertiajs/react';
import {
    Activity,
    CalendarClock,
    CheckCircle2,
    MonitorPlay,
    RadioTower,
    XCircle,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { DateTimePickerField } from '@/components/date-time-picker-field';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';

type DisplayDevice = {
    id: string;
    code: string;
    name: string;
    deviceType: string;
    status: string;
    formats: string[];
    venueName: string | null;
    hubName: string | null;
    touchpointLabel: string | null;
    scheduledPlacementsCount: number;
    eventsCount: number;
    impressionsCount: number;
    clicksCount: number;
    lastEventAt: string | null;
    isOnline: boolean;
    lastHeartbeatAt: string | null;
    playbackStatus: string | null;
    currentSlot: string | null;
    lastPlaybackResult: string | null;
    lastPlaybackError: string | null;
};

type ScheduledPlacement = {
    id: string;
    adRequestId: string;
    adCode: string | null;
    adTitle: string | null;
    adStatus: string | null;
    partnerName: string | null;
    placementType: string;
    status: string;
    priority: number;
    startsAt: string | null;
    endsAt: string | null;
    displayDeviceId: string | null;
    displayDeviceCode: string | null;
    displayDeviceName: string | null;
    displayDeviceType: string | null;
    venueName: string | null;
    hubName: string | null;
    eventsCount: number;
    impressionsCount: number;
    clicksCount: number;
    impressionCap: number | null;
    clickCap: number | null;
};

type ReadyPlacement = {
    id: string;
    adRequestId: string;
    adCode: string | null;
    adTitle: string | null;
    partnerName: string | null;
    placementType: string;
    priority: number;
    startsAt: string | null;
    endsAt: string | null;
    venueName: string | null;
    hubName: string | null;
    impressionCap: number | null;
    clickCap: number | null;
    compatibleDisplayIds: string[];
};

type Props = {
    stats: {
        devices: number;
        activeDevices: number;
        scheduledPlacements: number;
        readyPlacements: number;
        eventsToday: number;
        impressions: number;
        onlineDevices: number;
        errorDevices: number;
    };
    displayDevices: DisplayDevice[];
    scheduledPlacements: ScheduledPlacement[];
    readyPlacements: ReadyPlacement[];
};

type SharedProps = {
    flash?: {
        success?: string;
    };
};

const statusLabels: Record<string, string> = {
    active: 'فعال',
    inactive: 'غیرفعال',
    approved: 'تایید شده',
    scheduled: 'زمان‌بندی شده',
    pending_review: 'در انتظار تایید',
    rejected: 'رد شده',
    online: 'آنلاین',
    idle: 'آماده',
    playing: 'در حال پخش',
    error: 'خطا',
};

const placementLabels: Record<string, string> = {
    fixed_display: 'نمایشگر ثابت',
    mobile_display: 'نمایشگر سیار',
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

function formatDate(value: string | null) {
    if (!value) {
        return '-';
    }

    return new Intl.DateTimeFormat('fa-IR', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

function toDateTimeLocal(value: string | null) {
    if (!value) {
        return '';
    }

    const date = new Date(value);
    const offset = date.getTimezoneOffset() * 60000;

    return new Date(date.getTime() - offset).toISOString().slice(0, 16);
}

function EmptyState() {
    return (
        <p className="p-4 text-sm text-muted-foreground">
            موردی برای نمایش وجود ندارد.
        </p>
    );
}

export default function AdminDisplayOperationsIndex({
    stats,
    displayDevices,
    scheduledPlacements,
    readyPlacements,
}: Props) {
    const { flash } = usePage<SharedProps>().props;

    return (
        <>
            <Head title="عملیات نمایشگرها" />
            <div
                dir="rtl"
                className="flex h-full flex-1 flex-col gap-5 overflow-x-auto p-4"
            >
                <header className="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p className="text-sm text-muted-foreground">
                            کنترل سراسری انتشار تبلیغات روی نمایشگرهای ثابت و
                            سیار
                        </p>
                        <h1 className="mt-1 text-2xl font-semibold">
                            عملیات نمایشگرها
                        </h1>
                    </div>
                    <div className="grid grid-cols-2 gap-3 text-sm sm:grid-cols-4 xl:grid-cols-8">
                        <Stat
                            icon={MonitorPlay}
                            label="نمایشگر"
                            value={stats.devices}
                        />
                        <Stat
                            icon={CheckCircle2}
                            label="فعال"
                            value={stats.activeDevices}
                        />
                        <Stat
                            icon={CalendarClock}
                            label="صف فعال"
                            value={stats.scheduledPlacements}
                        />
                        <Stat
                            icon={RadioTower}
                            label="آماده پخش"
                            value={stats.readyPlacements}
                        />
                        <Stat
                            icon={Activity}
                            label="رویداد امروز"
                            value={stats.eventsToday}
                        />
                        <Stat
                            icon={MonitorPlay}
                            label="نمایش"
                            value={stats.impressions}
                        />
                        <Stat
                            icon={RadioTower}
                            label="آنلاین"
                            value={stats.onlineDevices}
                        />
                        <Stat
                            icon={XCircle}
                            label="خطادار"
                            value={stats.errorDevices}
                        />
                    </div>
                </header>

                <section className="grid gap-3 md:grid-cols-3">
                    <Link
                        href="/admin/ads"
                        className="rounded-lg border border-sidebar-border/70 bg-background p-3 text-sm dark:border-sidebar-border"
                    >
                        <p className="font-semibold">تبلیغات آماده انتشار</p>
                        <p className="mt-2 leading-6 text-muted-foreground">
                            تبلیغ ابتدا باید تایید شود، سپس در این صفحه روی
                            نمایشگر زمان‌بندی شود.
                        </p>
                    </Link>
                    <Link
                        href="/admin/campaign-operations"
                        className="rounded-lg border border-sidebar-border/70 bg-background p-3 text-sm dark:border-sidebar-border"
                    >
                        <p className="font-semibold">هماهنگی با روز اجرا</p>
                        <p className="mt-2 leading-6 text-muted-foreground">
                            زمان پخش نمایشگر باید با نقشه عملیات کمپین و نقاط QR
                            هماهنگ باشد.
                        </p>
                    </Link>
                    <Link
                        href="/admin/support"
                        className="rounded-lg border border-sidebar-border/70 bg-background p-3 text-sm dark:border-sidebar-border"
                    >
                        <p className="font-semibold">عیب‌یابی نمایشگر</p>
                        <p className="mt-2 leading-6 text-muted-foreground">
                            آفلاین بودن، خطای پخش یا heartbeat نامنظم را از مسیر
                            پشتیبانی پیگیری کنید.
                        </p>
                    </Link>
                </section>

                {flash?.success ? (
                    <Alert>
                        <AlertDescription>{flash.success}</AlertDescription>
                    </Alert>
                ) : null}

                <section className="rounded-lg border border-sidebar-border/70 bg-background dark:border-sidebar-border">
                    <div className="border-b border-sidebar-border/70 px-4 py-3 dark:border-sidebar-border">
                        <h2 className="font-semibold">
                            موجودی و سلامت نمایشگرها
                        </h2>
                    </div>
                    <div className="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                        {displayDevices.length === 0 ? <EmptyState /> : null}
                        {displayDevices.map((device) => (
                            <article
                                key={device.id}
                                className="grid gap-3 px-4 py-3 text-sm lg:grid-cols-[1.3fr_1fr_1fr_1fr]"
                            >
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
                                <p className="text-xs text-muted-foreground">
                                    مکان: {device.venueName ?? '-'} · هاب:{' '}
                                    {device.hubName ?? '-'}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    صف فعال:{' '}
                                    {device.scheduledPlacementsCount.toLocaleString(
                                        'fa-IR',
                                    )}{' '}
                                    · رویداد:{' '}
                                    {device.eventsCount.toLocaleString('fa-IR')}{' '}
                                    · نمایش:{' '}
                                    {device.impressionsCount.toLocaleString(
                                        'fa-IR',
                                    )}
                                </p>
                                <div className="flex flex-wrap items-center gap-2">
                                    <span className="rounded-full bg-muted px-2.5 py-1 text-xs">
                                        {statusLabels[device.status] ??
                                            device.status}
                                    </span>
                                    <span className="text-xs text-muted-foreground">
                                        آخرین رویداد:{' '}
                                        {formatDate(device.lastEventAt)}
                                    </span>
                                    <span
                                        className={`rounded-full px-2.5 py-1 text-xs ${
                                            device.isOnline
                                                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                                                : 'bg-muted text-muted-foreground'
                                        }`}
                                    >
                                        {device.isOnline ? 'آنلاین' : 'آفلاین'}
                                    </span>
                                    <span className="text-xs text-muted-foreground">
                                        پخش:{' '}
                                        {device.playbackStatus
                                            ? (statusLabels[
                                                  device.playbackStatus
                                              ] ?? device.playbackStatus)
                                            : '-'}
                                    </span>
                                    <span className="text-xs text-muted-foreground">
                                        heartbeat:{' '}
                                        {formatDate(device.lastHeartbeatAt)}
                                    </span>
                                    <span className="text-xs text-muted-foreground">
                                        اسلات: {device.currentSlot ?? '-'}
                                    </span>
                                    {device.lastPlaybackError ? (
                                        <span className="basis-full text-xs text-destructive">
                                            خطا: {device.lastPlaybackError}
                                        </span>
                                    ) : null}
                                </div>
                            </article>
                        ))}
                    </div>
                </section>

                <section className="rounded-lg border border-sidebar-border/70 bg-background dark:border-sidebar-border">
                    <div className="border-b border-sidebar-border/70 px-4 py-3 dark:border-sidebar-border">
                        <h2 className="font-semibold">
                            تبلیغات تاییدشده آماده زمان‌بندی
                        </h2>
                    </div>
                    <div className="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                        {readyPlacements.length === 0 ? <EmptyState /> : null}
                        {readyPlacements.map((placement) => {
                            const compatibleDevices = displayDevices.filter(
                                (device) =>
                                    placement.compatibleDisplayIds.includes(
                                        device.id,
                                    ),
                            );

                            return (
                                <article
                                    key={placement.id}
                                    className="grid gap-3 px-4 py-3 text-sm"
                                >
                                    <div className="flex flex-col gap-2 lg:flex-row lg:items-start lg:justify-between">
                                        <div className="min-w-0">
                                            <p className="truncate font-medium">
                                                {placement.adTitle}
                                            </p>
                                            <p
                                                className="mt-1 truncate text-xs text-muted-foreground"
                                                dir="ltr"
                                            >
                                                {placement.adCode} ·{' '}
                                                {placement.placementType}
                                            </p>
                                            <p className="mt-1 text-xs text-muted-foreground">
                                                واحد/حامی مرتبط:{' '}
                                                {placement.partnerName ?? '-'} ·
                                                هاب: {placement.hubName ?? '-'}{' '}
                                                · جایگاه:{' '}
                                                {placementLabels[
                                                    placement.placementType
                                                ] ?? placement.placementType}
                                            </p>
                                        </div>
                                        <Form
                                            action={`/admin/display-operations/placements/${placement.id}/schedule`}
                                            method="post"
                                            options={{ preserveScroll: true }}
                                        >
                                            {({ processing }) => (
                                                <div className="grid gap-2 sm:grid-cols-[minmax(190px,1fr)_120px_150px_150px_auto]">
                                                    <select
                                                        name="display_device_id"
                                                        defaultValue={
                                                            compatibleDevices[0]
                                                                ?.id ?? ''
                                                        }
                                                        disabled={
                                                            processing ||
                                                            compatibleDevices.length ===
                                                                0
                                                        }
                                                        className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                                    >
                                                        {compatibleDevices.map(
                                                            (device) => (
                                                                <option
                                                                    key={
                                                                        device.id
                                                                    }
                                                                    value={
                                                                        device.id
                                                                    }
                                                                >
                                                                    {
                                                                        device.name
                                                                    }
                                                                </option>
                                                            ),
                                                        )}
                                                    </select>
                                                    <input
                                                        name="priority"
                                                        type="number"
                                                        min="1"
                                                        max="10"
                                                        defaultValue={
                                                            placement.priority
                                                        }
                                                        className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                                    />
                                                    <DateTimePickerField
                                                        name="starts_at"
                                                        defaultValue={toDateTimeLocal(
                                                            placement.startsAt,
                                                        )}
                                                        hint={null}
                                                        wrapperClassName="gap-0"
                                                    />
                                                    <DateTimePickerField
                                                        name="ends_at"
                                                        defaultValue={toDateTimeLocal(
                                                            placement.endsAt,
                                                        )}
                                                        hint={null}
                                                        wrapperClassName="gap-0"
                                                    />
                                                    <Button
                                                        size="sm"
                                                        type="submit"
                                                        disabled={
                                                            processing ||
                                                            compatibleDevices.length ===
                                                                0
                                                        }
                                                    >
                                                        <CalendarClock className="size-4" />
                                                        زمان‌بندی
                                                    </Button>
                                                </div>
                                            )}
                                        </Form>
                                    </div>
                                </article>
                            );
                        })}
                    </div>
                </section>

                <section className="rounded-lg border border-sidebar-border/70 bg-background dark:border-sidebar-border">
                    <div className="border-b border-sidebar-border/70 px-4 py-3 dark:border-sidebar-border">
                        <h2 className="font-semibold">صف فعال پخش نمایشگرها</h2>
                    </div>
                    <div className="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                        {scheduledPlacements.length === 0 ? (
                            <EmptyState />
                        ) : null}
                        {scheduledPlacements.map((placement) => (
                            <article
                                key={placement.id}
                                className="grid gap-3 px-4 py-3 text-sm lg:grid-cols-[1.4fr_1fr_1fr_auto]"
                            >
                                <div className="min-w-0">
                                    <p className="truncate font-medium">
                                        {placement.adTitle}
                                    </p>
                                    <p
                                        className="mt-1 truncate text-xs text-muted-foreground"
                                        dir="ltr"
                                    >
                                        {placement.adCode} ·{' '}
                                        {placement.placementType}
                                    </p>
                                    <p className="mt-1 text-xs text-muted-foreground">
                                        واحد/حامی مرتبط:{' '}
                                        {placement.partnerName ?? '-'} ·
                                        اولویت:{' '}
                                        {placement.priority.toLocaleString(
                                            'fa-IR',
                                        )}
                                    </p>
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    نمایشگر:{' '}
                                    {placement.displayDeviceName ?? '-'} · هاب:{' '}
                                    {placement.hubName ?? '-'}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    نمایش:{' '}
                                    {placement.impressionsCount.toLocaleString(
                                        'fa-IR',
                                    )}{' '}
                                    /{' '}
                                    {placement.impressionCap?.toLocaleString(
                                        'fa-IR',
                                    ) ?? 'نامحدود'}{' '}
                                    · کلیک:{' '}
                                    {placement.clicksCount.toLocaleString(
                                        'fa-IR',
                                    )}
                                    <br />
                                    بازه: {formatDate(
                                        placement.startsAt,
                                    )} تا {formatDate(placement.endsAt)}
                                </p>
                                <Form
                                    action={`/admin/display-operations/placements/${placement.id}/cancel`}
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
                                            لغو
                                        </Button>
                                    )}
                                </Form>
                            </article>
                        ))}
                    </div>
                </section>
            </div>
        </>
    );
}

AdminDisplayOperationsIndex.layout = {
    breadcrumbs: [
        {
            title: 'عملیات نمایشگرها',
            href: '/admin/display-operations',
        },
    ],
};
