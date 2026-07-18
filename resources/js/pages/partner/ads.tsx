import { Form, Head, usePage } from '@inertiajs/react';
import {
    BadgeCheck,
    CalendarClock,
    Megaphone,
    MonitorPlay,
    RadioTower,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { DateTimePickerField } from '@/components/date-time-picker-field';
import InputError from '@/components/input-error';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Partner = {
    id: string;
    code: string;
    name: string;
    partnerType: string;
    venueName: string | null;
};

type HubOption = {
    id: string;
    code: string;
    name: string;
};

type AdRequest = {
    id: string;
    code: string;
    title: string;
    bodyCopy: string | null;
    ctaText: string | null;
    targetUrl: string | null;
    adType: string;
    status: string;
    startsAt: string | null;
    endsAt: string | null;
    budgetAmount: number | null;
    impressionCap: number | null;
    impressionsCount: number;
    hubName: string | null;
    placementType: string | null;
    creativeType: string | null;
    assetUrl: string | null;
};

type Props = {
    partner: Partner;
    stats: {
        requests: number;
        pending: number;
        approved: number;
        rejected: number;
    };
    hubOptions: HubOption[];
    adRequests: AdRequest[];
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
        <div className="min-w-0 rounded-lg border border-sidebar-border/70 bg-background px-3 py-2 dark:border-sidebar-border">
            <div className="flex min-w-0 items-center gap-2 text-muted-foreground">
                <Icon className="size-4 shrink-0" />
                <p className="min-w-0 text-xs leading-5">{label}</p>
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

export default function PartnerAds({
    partner,
    stats,
    hubOptions,
    adRequests,
}: Props) {
    const { flash } = usePage<SharedProps>().props;

    return (
        <>
            <Head title="تبلیغات فروشگاه" />
            <div
                dir="rtl"
                className="flex h-full min-w-0 flex-1 flex-col gap-5 overflow-x-hidden p-3 sm:p-4"
            >
                <header className="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div className="min-w-0">
                        <p className="text-sm text-muted-foreground">
                            تبلیغات مستقل
                        </p>
                        <h1 className="mt-1 text-2xl leading-tight font-semibold">
                            {partner.name}
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            {partner.venueName} · {partner.partnerType}
                        </p>
                    </div>
                    <div className="grid w-full grid-cols-2 gap-2 text-sm md:w-auto xl:grid-cols-4">
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
                    </div>
                </header>

                {flash?.success ? (
                    <Alert>
                        <AlertDescription>{flash.success}</AlertDescription>
                    </Alert>
                ) : null}

                <section className="overflow-hidden rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
                    <div className="mb-4 flex items-center gap-2">
                        <Megaphone className="size-4 text-muted-foreground" />
                        <h2 className="font-semibold">
                            ثبت درخواست تبلیغ مستقل
                        </h2>
                    </div>
                    <Form
                        action="/partner/ads"
                        method="post"
                        options={{ preserveScroll: true }}
                        className="grid min-w-0 gap-4 md:grid-cols-2"
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="title">عنوان تبلیغ</Label>
                                    <Input
                                        id="title"
                                        name="title"
                                        required
                                        placeholder="مثلا نوشیدنی خانوادگی مسیر اکوپارک"
                                    />
                                    <InputError message={errors.title} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="hub_id">هاب هدف</Label>
                                    <select
                                        id="hub_id"
                                        name="hub_id"
                                        className="h-9 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                        defaultValue=""
                                    >
                                        <option value="">کل مکان شریک</option>
                                        {hubOptions.map((hub) => (
                                            <option key={hub.id} value={hub.id}>
                                                {hub.name}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError message={errors.hub_id} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="ad_type">نوع تبلیغ</Label>
                                    <select
                                        id="ad_type"
                                        name="ad_type"
                                        required
                                        className="h-9 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                        defaultValue="standalone"
                                    >
                                        <option value="standalone">
                                            مستقل
                                        </option>
                                        <option value="sponsor_message">
                                            پیام اسپانسری
                                        </option>
                                        <option value="display_takeover">
                                            تصاحب جایگاه نمایش
                                        </option>
                                        <option value="route_sponsor">
                                            اسپانسر مسیر
                                        </option>
                                        <option value="reward_moment">
                                            لحظه پاداش
                                        </option>
                                    </select>
                                    <InputError message={errors.ad_type} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="placement_type">
                                        جایگاه نمایش
                                    </Label>
                                    <select
                                        id="placement_type"
                                        name="placement_type"
                                        required
                                        className="h-9 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                        defaultValue="fixed_display"
                                    >
                                        <option value="fixed_display">
                                            نمایشگر ثابت
                                        </option>
                                        <option value="mobile_display">
                                            نمایشگر سیار
                                        </option>
                                        <option value="qr_landing">
                                            صفحه QR
                                        </option>
                                        <option value="reward_page">
                                            صفحه پاداش
                                        </option>
                                        <option value="map_route">
                                            نقشه و مسیر
                                        </option>
                                        <option value="post_mission">
                                            بعد از ماموریت
                                        </option>
                                    </select>
                                    <InputError
                                        message={errors.placement_type}
                                    />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="creative_type">
                                        نوع محتوا
                                    </Label>
                                    <select
                                        id="creative_type"
                                        name="creative_type"
                                        required
                                        className="h-9 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                        defaultValue="image"
                                    >
                                        <option value="image">تصویر</option>
                                        <option value="video">ویدئو</option>
                                        <option value="text_card">
                                            کارت متنی
                                        </option>
                                        <option value="display_banner">
                                            بنر نمایشگر
                                        </option>
                                    </select>
                                    <InputError
                                        message={errors.creative_type}
                                    />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="asset_url">
                                        لینک فایل/نمونه محتوا
                                    </Label>
                                    <Input
                                        id="asset_url"
                                        name="asset_url"
                                        type="url"
                                        dir="ltr"
                                        placeholder="https://example.com/ad.jpg"
                                    />
                                    <InputError message={errors.asset_url} />
                                </div>
                                <DateTimePickerField
                                    id="starts_at"
                                    name="starts_at"
                                    label="شروع نمایش"
                                    error={errors.starts_at}
                                />
                                <DateTimePickerField
                                    id="ends_at"
                                    name="ends_at"
                                    label="پایان نمایش"
                                    error={errors.ends_at}
                                />
                                <div className="grid gap-2">
                                    <Label htmlFor="budget_amount">
                                        بودجه پیشنهادی
                                    </Label>
                                    <Input
                                        id="budget_amount"
                                        name="budget_amount"
                                        type="number"
                                        min="0"
                                        placeholder="اختیاری"
                                    />
                                    <InputError
                                        message={errors.budget_amount}
                                    />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="impression_cap">
                                        سقف نمایش
                                    </Label>
                                    <Input
                                        id="impression_cap"
                                        name="impression_cap"
                                        type="number"
                                        min="1"
                                        placeholder="اختیاری"
                                    />
                                    <InputError
                                        message={errors.impression_cap}
                                    />
                                </div>
                                <div className="grid gap-2 md:col-span-2">
                                    <Label htmlFor="body_copy">متن تبلیغ</Label>
                                    <textarea
                                        id="body_copy"
                                        name="body_copy"
                                        className="min-h-24 rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                        placeholder="پیام تبلیغاتی، توضیح پیشنهاد یا متن اسپانسری"
                                    />
                                    <InputError message={errors.body_copy} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="cta_text">متن CTA</Label>
                                    <Input
                                        id="cta_text"
                                        name="cta_text"
                                        placeholder="مثلا مشاهده پیشنهاد"
                                    />
                                    <InputError message={errors.cta_text} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="target_url">
                                        لینک مقصد
                                    </Label>
                                    <Input
                                        id="target_url"
                                        name="target_url"
                                        type="url"
                                        dir="ltr"
                                        placeholder="https://example.com"
                                    />
                                    <InputError message={errors.target_url} />
                                </div>
                                <div className="md:col-span-2">
                                    <Button disabled={processing}>
                                        <MonitorPlay className="size-4" />
                                        ارسال برای تایید
                                    </Button>
                                </div>
                            </>
                        )}
                    </Form>
                </section>

                <section className="rounded-lg border border-sidebar-border/70 bg-background dark:border-sidebar-border">
                    <div className="border-b border-sidebar-border/70 px-4 py-3 dark:border-sidebar-border">
                        <h2 className="font-semibold">درخواست‌های ثبت‌شده</h2>
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
                                    <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
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
                                        <span className="w-fit shrink-0 rounded-full bg-muted px-2.5 py-1 text-xs">
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
                                        جایگاه:{' '}
                                        {placementLabels[
                                            adRequest.placementType ?? ''
                                        ] ??
                                            adRequest.placementType ??
                                            '-'}{' '}
                                        · هاب: {adRequest.hubName ?? '-'} ·
                                        بازه: {formatDate(adRequest.startsAt)}{' '}
                                        تا {formatDate(adRequest.endsAt)}
                                    </p>
                                </article>
                            ))
                        )}
                    </div>
                </section>
            </div>
        </>
    );
}

PartnerAds.layout = {
    breadcrumbs: [
        {
            title: 'تبلیغات فروشگاه',
            href: '/partner/ads',
        },
    ],
};
