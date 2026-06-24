import { Head, Link } from '@inertiajs/react';
import {
    CalendarClock,
    ExternalLink,
    MapPin,
    QrCode as QrCodeIcon,
    RadioTower,
} from 'lucide-react';

type RegistryEntity = {
    id: string;
    code: string;
    name?: string;
    label?: string;
};

type QrRegistryItem = {
    id: string;
    code: string;
    label: string | null;
    status: string;
    destinationUrl: string;
    venue: RegistryEntity | null;
    touchpoint: RegistryEntity | null;
    campaign: RegistryEntity | null;
    validFrom: string | null;
    validUntil: string | null;
};

type Props = {
    qrCodes: QrRegistryItem[];
};

const statusLabels: Record<string, string> = {
    active: 'فعال',
    draft: 'پیش نویس',
    inactive: 'غیرفعال',
    placeholder: 'نمونه کنترل شده',
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

function entityLabel(entity: RegistryEntity | null, fallback: string) {
    if (!entity) {
        return fallback;
    }

    return entity.name ?? entity.label ?? entity.code;
}

export default function QrRegistryIndex({ qrCodes }: Props) {
    const activeCount = qrCodes.filter((qr) => qr.status === 'active').length;

    return (
        <>
            <Head title="مدیریت QR" />
            <div
                dir="rtl"
                className="flex h-full flex-1 flex-col gap-5 overflow-x-auto p-4"
            >
                <header className="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p className="text-sm text-muted-foreground">
                            رجیستری پایلوت
                        </p>
                        <h1 className="mt-1 text-2xl font-semibold">
                            مدیریت کدهای QR
                        </h1>
                    </div>
                    <div className="grid grid-cols-2 gap-3 text-sm sm:grid-cols-3">
                        <div className="rounded-lg border border-sidebar-border/70 px-3 py-2 dark:border-sidebar-border">
                            <p className="text-muted-foreground">کل کدها</p>
                            <p className="mt-1 font-semibold">
                                {qrCodes.length.toLocaleString('fa-IR')}
                            </p>
                        </div>
                        <div className="rounded-lg border border-sidebar-border/70 px-3 py-2 dark:border-sidebar-border">
                            <p className="text-muted-foreground">فعال</p>
                            <p className="mt-1 font-semibold">
                                {activeCount.toLocaleString('fa-IR')}
                            </p>
                        </div>
                        <div className="rounded-lg border border-sidebar-border/70 px-3 py-2 dark:border-sidebar-border">
                            <p className="text-muted-foreground">پایلوت</p>
                            <p className="mt-1 font-semibold">عباس آباد</p>
                        </div>
                    </div>
                </header>

                <section className="rounded-lg border border-sidebar-border/70 bg-background dark:border-sidebar-border">
                    <div className="grid min-w-[860px] grid-cols-[1.25fr_1fr_1fr_1.15fr_1fr_auto] gap-3 border-b border-sidebar-border/70 px-4 py-3 text-xs font-medium text-muted-foreground dark:border-sidebar-border">
                        <span>کد و وضعیت</span>
                        <span>مکان</span>
                        <span>نقطه تماس</span>
                        <span>کمپین</span>
                        <span>اعتبار</span>
                        <span className="text-left">عملیات</span>
                    </div>

                    {qrCodes.length === 0 ? (
                        <div className="p-8 text-center text-sm text-muted-foreground">
                            هنوز کد QR ثبت نشده است.
                        </div>
                    ) : (
                        <div className="min-w-[860px] divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                            {qrCodes.map((qr) => (
                                <article
                                    key={qr.id}
                                    className="grid grid-cols-[1.25fr_1fr_1fr_1.15fr_1fr_auto] items-center gap-3 px-4 py-3 text-sm"
                                >
                                    <div className="min-w-0">
                                        <div className="flex items-center gap-2">
                                            <QrCodeIcon className="size-4 shrink-0 text-muted-foreground" />
                                            <span
                                                className="truncate font-medium"
                                                dir="ltr"
                                            >
                                                {qr.code}
                                            </span>
                                        </div>
                                        <div className="mt-2 flex items-center gap-2">
                                            <span
                                                className={`inline-flex rounded-full px-2.5 py-1 text-xs font-medium ${
                                                    statusClasses[qr.status] ??
                                                    statusClasses.inactive
                                                }`}
                                            >
                                                {statusLabels[qr.status] ??
                                                    qr.status}
                                            </span>
                                            {qr.label ? (
                                                <span className="truncate text-xs text-muted-foreground">
                                                    {qr.label}
                                                </span>
                                            ) : null}
                                        </div>
                                    </div>

                                    <div className="min-w-0">
                                        <div className="flex items-center gap-2">
                                            <MapPin className="size-4 shrink-0 text-muted-foreground" />
                                            <span className="truncate">
                                                {entityLabel(
                                                    qr.venue,
                                                    'بدون مکان',
                                                )}
                                            </span>
                                        </div>
                                        <p
                                            className="mt-1 truncate text-xs text-muted-foreground"
                                            dir="ltr"
                                        >
                                            {qr.venue?.code ?? '-'}
                                        </p>
                                    </div>

                                    <div className="min-w-0">
                                        <div className="flex items-center gap-2">
                                            <RadioTower className="size-4 shrink-0 text-muted-foreground" />
                                            <span className="truncate">
                                                {entityLabel(
                                                    qr.touchpoint,
                                                    'بدون نقطه تماس',
                                                )}
                                            </span>
                                        </div>
                                        <p
                                            className="mt-1 truncate text-xs text-muted-foreground"
                                            dir="ltr"
                                        >
                                            {qr.touchpoint?.code ?? '-'}
                                        </p>
                                    </div>

                                    <div className="min-w-0">
                                        <p className="truncate">
                                            {entityLabel(
                                                qr.campaign,
                                                'بدون کمپین',
                                            )}
                                        </p>
                                        <p
                                            className="mt-1 truncate text-xs text-muted-foreground"
                                            dir="ltr"
                                        >
                                            {qr.campaign?.code ?? '-'}
                                        </p>
                                    </div>

                                    <div className="min-w-0">
                                        <div className="flex items-center gap-2 text-xs">
                                            <CalendarClock className="size-4 shrink-0 text-muted-foreground" />
                                            <span className="truncate">
                                                {formatDate(qr.validFrom)}
                                            </span>
                                        </div>
                                        <p className="mt-1 truncate text-xs text-muted-foreground">
                                            تا {formatDate(qr.validUntil)}
                                        </p>
                                    </div>

                                    <Link
                                        href={`/scan/${qr.code}`}
                                        className="inline-flex h-8 items-center justify-center gap-2 rounded-md border border-input bg-background px-3 text-xs font-medium hover:bg-accent hover:text-accent-foreground"
                                    >
                                        <ExternalLink className="size-4" />
                                        <span>باز کردن</span>
                                    </Link>
                                </article>
                            ))}
                        </div>
                    )}
                </section>
            </div>
        </>
    );
}

QrRegistryIndex.layout = {
    breadcrumbs: [
        {
            title: 'مدیریت QR',
            href: '/admin/qr-codes',
        },
    ],
};
