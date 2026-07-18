import { Form, Head, Link, usePage } from '@inertiajs/react';
import {
    BadgeCheck,
    CalendarClock,
    CheckCircle2,
    Megaphone,
    MonitorPlay,
    RadioTower,
    XCircle,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';

type AdRequest = {
    id: string;
    code: string;
    title: string;
    bodyCopy: string | null;
    ctaText: string | null;
    targetUrl: string | null;
    advertiserType: string;
    adType: string;
    status: string;
    startsAt: string | null;
    endsAt: string | null;
    budgetAmount: number | null;
    impressionCap: number | null;
    impressionsCount: number;
    venueName: string | null;
    partnerName: string | null;
    partnerType: string | null;
    hubName: string | null;
    placementType: string | null;
    placementStatus: string | null;
    creativeType: string | null;
    assetUrl: string | null;
};

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
};

type Props = {
    stats: {
        requests: number;
        pending: number;
        approved: number;
        rejected: number;
        devices: number;
    };
    adRequests: AdRequest[];
    displayDevices: DisplayDevice[];
};

type SharedProps = {
    flash?: {
        success?: string;
    };
};

const statusLabels: Record<string, string> = {
    pending_review: 'در انتظار تایید',
    approved: 'تایید شده',
    rejected: 'رد شده',
    scheduled: 'زمان‌بندی شده',
    active: 'فعال',
    inactive: 'غیرفعال',
};

const placementLabels: Record<string, string> = {
    fixed_display: 'نمایشگر ثابت',
    mobile_display: 'نمایشگر سیار',
    qr_landing: 'صفحه QR',
    reward_page: 'صفحه پاداش',
    map_route: 'نقشه و مسیر',
    post_mission: 'بعد از ماموریت',
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
        return 'بدون محدودیت';
    }

    return new Intl.DateTimeFormat('fa-IR', {
        dateStyle: 'medium',
    }).format(new Date(value));
}

export default function AdminAdsIndex({
    stats,
    adRequests,
    displayDevices,
}: Props) {
    const { flash } = usePage<SharedProps>().props;

    return (
        <>
            <Head title="تبلیغات مستقل" />
            <div
                dir="rtl"
                className="flex h-full flex-1 flex-col gap-5 overflow-x-auto p-4"
            >
                <header className="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p className="text-sm text-muted-foreground">
                            Sprint 1.6
                        </p>
                        <h1 className="mt-1 text-2xl font-semibold">
                            تبلیغات مستقل و نمایشگرها
                        </h1>
                    </div>
                    <div className="grid grid-cols-2 gap-3 text-sm sm:grid-cols-5">
                        <Stat
                            icon={Megaphone}
                            label="درخواست"
                            value={stats.requests}
                        />
                        <Stat
                            icon={CalendarClock}
                            label="منتظر"
                            value={stats.pending}
                        />
                        <Stat
                            icon={BadgeCheck}
                            label="تایید"
                            value={stats.approved}
                        />
                        <Stat
                            icon={RadioTower}
                            label="رد شده"
                            value={stats.rejected}
                        />
                        <Stat
                            icon={MonitorPlay}
                            label="نمایشگر"
                            value={stats.devices}
                        />
                    </div>
                </header>

                <section className="grid gap-3 md:grid-cols-3">
                    <Link
                        href="/admin/display-operations"
                        className="rounded-lg border border-sidebar-border/70 bg-background p-3 text-sm dark:border-sidebar-border"
                    >
                        <p className="font-semibold">
                            ارسال تبلیغ تاییدشده به نمایشگر
                        </p>
                        <p className="mt-2 leading-6 text-muted-foreground">
                            بعد از تایید، زمان‌بندی و انتخاب نمایشگر از عملیات
                            نمایشگرها انجام می‌شود.
                        </p>
                    </Link>
                    <Link
                        href="/admin/partners"
                        className="rounded-lg border border-sidebar-border/70 bg-background p-3 text-sm dark:border-sidebar-border"
                    >
                        <p className="font-semibold">بررسی مالک تبلیغ</p>
                        <p className="mt-2 leading-6 text-muted-foreground">
                            تبلیغ مستقل باید به فروشگاه، اسپانسر یا برند مشخص
                            وصل باشد.
                        </p>
                    </Link>
                    <Link
                        href="/admin/support"
                        className="rounded-lg border border-sidebar-border/70 bg-background p-3 text-sm dark:border-sidebar-border"
                    >
                        <p className="font-semibold">راهنمای خطای تبلیغ</p>
                        <p className="mt-2 leading-6 text-muted-foreground">
                            اگر تبلیغ تایید می‌شود ولی پخش نمی‌شود، مسیر
                            پشتیبانی را بررسی کنید.
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
                        <h2 className="font-semibold">صف تایید تبلیغات</h2>
                    </div>
                    <div className="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                        {adRequests.length === 0 ? (
                            <p className="p-4 text-sm text-muted-foreground">
                                هنوز درخواست تبلیغی ثبت نشده است.
                            </p>
                        ) : (
                            adRequests.map((adRequest) => (
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
                                    {adRequest.bodyCopy ? (
                                        <p className="line-clamp-2 text-xs text-muted-foreground">
                                            {adRequest.bodyCopy}
                                        </p>
                                    ) : null}
                                    <p className="text-xs text-muted-foreground">
                                        شریک: {adRequest.partnerName ?? '-'} ·
                                        مکان: {adRequest.venueName ?? '-'} ·
                                        هاب: {adRequest.hubName ?? '-'} ·
                                        جایگاه:{' '}
                                        {placementLabels[
                                            adRequest.placementType ?? ''
                                        ] ??
                                            adRequest.placementType ??
                                            '-'}
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        بازه: {formatDate(adRequest.startsAt)}{' '}
                                        تا {formatDate(adRequest.endsAt)} ·
                                        بودجه:{' '}
                                        {adRequest.budgetAmount?.toLocaleString(
                                            'fa-IR',
                                        ) ?? 'ثبت نشده'}{' '}
                                        · سقف نمایش:{' '}
                                        {adRequest.impressionCap?.toLocaleString(
                                            'fa-IR',
                                        ) ?? 'نامحدود'}
                                    </p>
                                    {adRequest.status === 'pending_review' ? (
                                        <div className="flex flex-wrap gap-2 pt-1">
                                            <Form
                                                action={`/admin/ads/${adRequest.id}/approve`}
                                                method="post"
                                                options={{
                                                    preserveScroll: true,
                                                }}
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
                                                options={{
                                                    preserveScroll: true,
                                                }}
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
                            ))
                        )}
                    </div>
                </section>

                <section className="rounded-lg border border-sidebar-border/70 bg-background dark:border-sidebar-border">
                    <div className="border-b border-sidebar-border/70 px-4 py-3 dark:border-sidebar-border">
                        <h2 className="font-semibold">
                            موجودی نمایشگر ثابت و سیار
                        </h2>
                    </div>
                    <div className="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                        {displayDevices.map((device) => (
                            <article
                                key={device.id}
                                className="grid gap-2 px-4 py-3 text-sm md:grid-cols-[1fr_1fr_1fr_auto]"
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
                                    {device.venueName} · {device.hubName ?? '-'}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    فرمت‌ها: {device.formats.join(', ')}
                                </p>
                                <span className="w-fit rounded-full bg-muted px-2.5 py-1 text-xs">
                                    {statusLabels[device.status] ??
                                        device.status}
                                </span>
                            </article>
                        ))}
                    </div>
                </section>
            </div>
        </>
    );
}

AdminAdsIndex.layout = {
    breadcrumbs: [
        {
            title: 'تبلیغات مستقل',
            href: '/admin/ads',
        },
    ],
};
