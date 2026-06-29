import { Form, Head, Link, usePage } from '@inertiajs/react';
import {
    CalendarClock,
    ExternalLink,
    MapPin,
    Plus,
    QrCode as QrCodeIcon,
    RadioTower,
} from 'lucide-react';
import { DateTimePickerField } from '@/components/date-time-picker-field';
import InputError from '@/components/input-error';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

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
    maxScansPerUserPerWindow: number;
    duplicateWindowSeconds: number;
};

type SelectedCampaign = RegistryEntity & {
    campaignType: string;
    blueprintCode: string | null;
    status: string;
    venue: RegistryEntity | null;
};

type OptionItem = RegistryEntity & {
    venueId?: string | null;
    venueName?: string | null;
    hubName?: string | null;
};

type Props = {
    qrCodes: QrRegistryItem[];
    formOptions: {
        venues: RegistryEntity[];
        campaigns: OptionItem[];
        touchpoints: OptionItem[];
    };
    selectedCampaign: SelectedCampaign | null;
};

type SharedProps = {
    flash?: {
        success?: string;
    };
    auth: {
        user: {
            role?: string;
        };
    };
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

function canMutate(role?: string) {
    return role === 'admin' || role === 'operator';
}

export default function QrRegistryIndex({ qrCodes, formOptions, selectedCampaign }: Props) {
    const { flash, auth } = usePage<SharedProps>().props;
    const activeCount = qrCodes.filter((qr) => qr.status === 'active').length;
    const selectedCampaignOption = formOptions.campaigns.find((campaign) => campaign.id === selectedCampaign?.id);
    const selectedVenueId = selectedCampaign?.venue?.id ?? selectedCampaignOption?.venueId ?? formOptions.venues[0]?.id ?? '';
    const selectedCampaignId = selectedCampaign?.id ?? formOptions.campaigns[0]?.id ?? '';
    const campaignContextUrl = selectedCampaign ? `?campaign=${selectedCampaign.code}` : '';

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

                {flash?.success ? (
                    <Alert>
                        <AlertDescription>{flash.success}</AlertDescription>
                    </Alert>
                ) : null}

                {selectedCampaign ? (
                    <section className="rounded-lg border border-primary/25 bg-primary/5 p-4 text-sm shadow-sm">
                        <div className="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                            <div>
                                <p className="text-xs text-muted-foreground">زمینه کمپین فعال</p>
                                <h2 className="mt-1 font-semibold">{selectedCampaign.name}</h2>
                                <p className="mt-1 text-muted-foreground">کدهای QR این صفحه به عنوان نقاط شروع، اسکن یا تماس همین کمپین ثبت و فیلتر می‌شوند.</p>
                            </div>
                            <div className="flex flex-wrap gap-2">
                                <span className="rounded-full bg-background px-3 py-1 text-xs" dir="ltr">{selectedCampaign.code}</span>
                                {selectedCampaign.blueprintCode ? <span className="rounded-full bg-background px-3 py-1 text-xs" dir="ltr">{selectedCampaign.blueprintCode}</span> : null}
                            </div>
                        </div>
                    </section>
                ) : null}

                {canMutate(auth.user.role) ? (
                    <section className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
                        <div className="mb-4 flex items-center gap-2">
                            <Plus className="size-4 text-muted-foreground" />
                            <h2 className="font-semibold">ثبت QR جدید</h2>
                        </div>
                        <Form
                            action={`/admin/qr-codes${campaignContextUrl}`}
                            method="post"
                            options={{ preserveScroll: true }}
                            className="grid gap-4 md:grid-cols-6"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <div className="grid gap-2 md:col-span-2">
                                        <Label htmlFor="venue_id">مکان</Label>
                                        <select
                                            id="venue_id"
                                            name="venue_id"
                                            required
                                            defaultValue={selectedVenueId}
                                            className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                        >
                                            {formOptions.venues.map((venue) => (
                                                <option
                                                    key={venue.id}
                                                    value={venue.id}
                                                >
                                                    {venue.name}
                                                </option>
                                            ))}
                                        </select>
                                        <InputError message={errors.venue_id} />
                                    </div>
                                    <div className="grid gap-2 md:col-span-2">
                                        <Label htmlFor="campaign_id">
                                            کمپین
                                        </Label>
                                        <select
                                            id="campaign_id"
                                            name="campaign_id"
                                            required
                                            defaultValue={selectedCampaignId}
                                            className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                        >
                                            {formOptions.campaigns.map(
                                                (campaign) => (
                                                    <option
                                                        key={campaign.id}
                                                        value={campaign.id}
                                                    >
                                                        {campaign.name} ·{' '}
                                                        {campaign.venueName}
                                                    </option>
                                                ),
                                            )}
                                        </select>
                                        <InputError
                                            message={errors.campaign_id}
                                        />
                                    </div>
                                    <div className="grid gap-2 md:col-span-2">
                                        <Label htmlFor="touchpoint_id">
                                            نقطه تماس
                                        </Label>
                                        <select
                                            id="touchpoint_id"
                                            name="touchpoint_id"
                                            required
                                            defaultValue={
                                                formOptions.touchpoints[0]
                                                    ?.id ?? ''
                                            }
                                            className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                        >
                                            {formOptions.touchpoints.map(
                                                (touchpoint) => (
                                                    <option
                                                        key={touchpoint.id}
                                                        value={touchpoint.id}
                                                    >
                                                        {touchpoint.label ??
                                                            touchpoint.name}{' '}
                                                        · {touchpoint.hubName}
                                                    </option>
                                                ),
                                            )}
                                        </select>
                                        <InputError
                                            message={errors.touchpoint_id}
                                        />
                                    </div>
                                    <div className="grid gap-2 md:col-span-2">
                                        <Label htmlFor="label">عنوان</Label>
                                        <Input
                                            id="label"
                                            name="label"
                                            placeholder="مثلا QR ورودی رواق"
                                        />
                                        <InputError message={errors.label} />
                                    </div>
                                    <div className="grid gap-2 md:col-span-2">
                                        <Label htmlFor="code">کد QR</Label>
                                        <Input
                                            id="code"
                                            name="code"
                                            required
                                            dir="ltr"
                                            placeholder="ep1405-ravaq-01"
                                        />
                                        <InputError message={errors.code} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="status">وضعیت</Label>
                                        <select
                                            id="status"
                                            name="status"
                                            required
                                            defaultValue="draft"
                                            className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                        >
                                            <option value="draft">
                                                پیش نویس
                                            </option>
                                            <option value="active">فعال</option>
                                            <option value="inactive">
                                                غیرفعال
                                            </option>
                                        </select>
                                        <InputError message={errors.status} />
                                    </div>
                                    <DateTimePickerField
                                        id="valid_from"
                                        name="valid_from"
                                        label="شروع"
                                        error={errors.valid_from}
                                    />
                                    <DateTimePickerField
                                        id="valid_until"
                                        name="valid_until"
                                        label="پایان"
                                        error={errors.valid_until}
                                    />
                                    <div className="grid gap-2">
                                        <Label htmlFor="max_scans_per_user_per_window">
                                            سقف تکرار
                                        </Label>
                                        <Input
                                            id="max_scans_per_user_per_window"
                                            type="number"
                                            name="max_scans_per_user_per_window"
                                            min={1}
                                            max={1000}
                                            required
                                            defaultValue={1}
                                        />
                                        <InputError
                                            message={
                                                errors.max_scans_per_user_per_window
                                            }
                                        />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="duplicate_window_seconds">
                                            بازه ضدتکرار
                                        </Label>
                                        <Input
                                            id="duplicate_window_seconds"
                                            type="number"
                                            name="duplicate_window_seconds"
                                            min={30}
                                            max={86400}
                                            required
                                            defaultValue={300}
                                        />
                                        <InputError
                                            message={
                                                errors.duplicate_window_seconds
                                            }
                                        />
                                    </div>
                                    <div className="flex items-end">
                                        <Button
                                            disabled={processing}
                                            className="w-full"
                                        >
                                            <Plus className="size-4" />
                                            ثبت QR
                                        </Button>
                                    </div>
                                </>
                            )}
                        </Form>
                    </section>
                ) : null}

                <section className="rounded-lg border border-sidebar-border/70 bg-background dark:border-sidebar-border">
                    <div className="grid min-w-[980px] grid-cols-[1.25fr_1fr_1fr_1.15fr_1fr_0.8fr_auto] gap-3 border-b border-sidebar-border/70 px-4 py-3 text-xs font-medium text-muted-foreground dark:border-sidebar-border">
                        <span>کد و وضعیت</span>
                        <span>مکان</span>
                        <span>نقطه تماس</span>
                        <span>کمپین</span>
                        <span>اعتبار</span>
                        <span>ضدتکرار</span>
                        <span className="text-left">عملیات</span>
                    </div>

                    {qrCodes.length === 0 ? (
                        <div className="p-8 text-center text-sm text-muted-foreground">
                            هنوز کد QR ثبت نشده است.
                        </div>
                    ) : (
                        <div className="min-w-[980px] divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                            {qrCodes.map((qr) => (
                                <article
                                    key={qr.id}
                                    className="grid grid-cols-[1.25fr_1fr_1fr_1.15fr_1fr_0.8fr_auto] items-center gap-3 px-4 py-3 text-sm"
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

                                    <div className="min-w-0 text-xs">
                                        <p>
                                            {qr.maxScansPerUserPerWindow.toLocaleString(
                                                'fa-IR',
                                            )}{' '}
                                            اسکن
                                        </p>
                                        <p className="mt-1 text-muted-foreground">
                                            {qr.duplicateWindowSeconds.toLocaleString(
                                                'fa-IR',
                                            )}{' '}
                                            ثانیه
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
