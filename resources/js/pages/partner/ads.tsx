import { Form, Head, usePage } from '@inertiajs/react';
import {
    BadgeCheck,
    CalendarClock,
    Megaphone,
    MonitorPlay,
    RadioTower,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { useState } from 'react';
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
    clickCap: number | null;
    impressionsCount: number;
    hubName: string | null;
    placementType: string | null;
    placementTypes: string[];
    creativeType: string | null;
    assetUrl: string | null;
    rewardedPoints: number | null;
    requiredSeconds: number | null;
    gameStageIndex: number | null;
    latestReview: {
        action: string;
        notes: string | null;
        reviewerName: string | null;
        createdAt: string | null;
    } | null;
};

type Props = {
    partner: Partner;
    stats: {
        requests: number;
        pending: number;
        approved: number;
        rejected: number;
        needsRevision: number;
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
    revision_requested: 'نیازمند اصلاح',
    paused: 'متوقف شده',
    archived: 'بایگانی شده',
    scheduled: 'زمان‌بندی شده',
};

const placementLabels: Record<string, string> = {
    public_feed: 'ویترین عمومی فروشگاه‌ها',
    fixed_display: 'نمایشگر ثابت',
    mobile_display: 'نمایشگر سیار',
    qr_landing: 'صفحه QR',
    reward_page: 'صفحه پاداش',
    map_route: 'نقشه و مسیر',
    post_mission: 'بعد از ماموریت',
};

const onlinePlacementOptions = [
    ['public_feed', placementLabels.public_feed],
    ['qr_landing', placementLabels.qr_landing],
    ['reward_page', placementLabels.reward_page],
    ['map_route', placementLabels.map_route],
    ['post_mission', placementLabels.post_mission],
] as const;

const creativeLabels: Record<string, string> = {
    image: 'تصویر ثابت',
    video: 'ویدئو نمایشگر محیطی',
    text_card: 'کارت متنی',
    display_banner: 'بنر نمایشگر',
};

const partnerTypeLabels: Record<string, string> = {
    member_shop: 'فروشگاه یا واحد تجاری عضو',
    food_reward_point: 'کافه یا واحد غذایی مستقل',
    sponsor: 'اسپانسر',
};

function placementText(placementTypes: string[], fallback: string | null) {
    const types = placementTypes.length > 0 ? placementTypes : [fallback ?? ''];

    return types
        .filter(Boolean)
        .map((type) => placementLabels[type] ?? type)
        .join('، ');
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
    const [adType, setAdType] = useState('standalone');
    const [placementType, setPlacementType] = useState('fixed_display');
    const [onlinePlacements, setOnlinePlacements] = useState<string[]>([]);
    const isRewardedPopup = adType === 'rewarded_content';
    const requiresStaticImage =
        isRewardedPopup ||
        placementType === 'public_feed' ||
        onlinePlacements.includes('public_feed');

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
                            تبلیغات فروشگاه
                        </p>
                        <h1 className="mt-1 text-2xl leading-tight font-semibold">
                            {partner.name}
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            {partner.venueName} ·{' '}
                            {partnerTypeLabels[partner.partnerType] ??
                                'واحد تجاری عضو'}
                        </p>
                    </div>
                    <div className="grid w-full grid-cols-2 gap-2 text-sm md:w-auto xl:grid-cols-5">
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
                            icon={CalendarClock}
                            label="نیازمند اصلاح"
                            value={stats.needsRevision}
                        />
                    </div>
                </header>

                {flash?.success ? (
                    <Alert>
                        <AlertDescription>{flash.success}</AlertDescription>
                    </Alert>
                ) : null}

                <Alert>
                    <AlertDescription>
                        این صفحه فقط برای تبلیغ مستقل همان فروشگاه یا واحد تجاری
                        است. درخواست‌های اسپانسری، حمایت از مسیر و قراردادهای
                        برند از پنل اسپانسر ثبت و پیگیری می‌شوند.
                    </AlertDescription>
                </Alert>

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
                                        <option value="">
                                            کل مکان فروشگاه/واحد تجاری
                                        </option>
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
                                        value={adType}
                                        onChange={(event) => {
                                            const value = event.target.value;

                                            setAdType(value);

                                            if (value === 'rewarded_content') {
                                                setPlacementType('map_route');
                                                setOnlinePlacements(
                                                    (placements) =>
                                                        placements.filter(
                                                            (placement) =>
                                                                placement !==
                                                                'public_feed',
                                                        ),
                                                );
                                            }
                                        }}
                                    >
                                        <option value="standalone">
                                            تبلیغ مستقل فروشگاه
                                        </option>
                                        <option value="display_takeover">
                                            جایگاه نمایش فروشگاه
                                        </option>
                                        <option value="reward_moment">
                                            پیام کنار پاداش فروشگاه
                                        </option>
                                        <option value="rewarded_content">
                                            پاپ‌آپ امتیازآور داخل بازی
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
                                        value={placementType}
                                        onChange={(event) =>
                                            setPlacementType(event.target.value)
                                        }
                                    >
                                        {!isRewardedPopup ? (
                                            <>
                                                <option value="fixed_display">
                                                    نمایشگر ثابت
                                                </option>
                                                <option value="mobile_display">
                                                    نمایشگر سیار
                                                </option>
                                                <option value="public_feed">
                                                    ویترین عمومی فروشگاه‌ها
                                                </option>
                                                <option value="qr_landing">
                                                    صفحه QR
                                                </option>
                                            </>
                                        ) : null}
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
                                <div className="grid gap-2 rounded-md border border-sidebar-border/70 p-3 md:col-span-2 dark:border-sidebar-border">
                                    <div>
                                        <p className="text-sm font-medium">
                                            کانال‌های آنلاین مکمل
                                        </p>
                                        <p className="mt-1 text-xs leading-6 text-muted-foreground">
                                            ویترین عمومی، غیرامتیازی و مستقل از
                                            بازی است. جایگاه‌های مسیر فقط برای
                                            محتوای ویژه همان مراحل استفاده
                                            می‌شوند. پنج انتشار نخست ویترین در
                                            پایلوت رایگان معرفی شده‌اند.
                                        </p>
                                    </div>
                                    <div className="grid gap-2 sm:grid-cols-2 lg:grid-cols-5">
                                        {onlinePlacementOptions
                                            .filter(
                                                ([value]) =>
                                                    !isRewardedPopup ||
                                                    value !== 'public_feed',
                                            )
                                            .map(([value, label]) => (
                                                <label
                                                    key={value}
                                                    className="flex min-h-10 items-center gap-2 rounded-md border border-input px-3 text-sm"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        name="online_placements[]"
                                                        value={value}
                                                        checked={onlinePlacements.includes(
                                                            value,
                                                        )}
                                                        onChange={(event) =>
                                                            setOnlinePlacements(
                                                                (placements) =>
                                                                    event.target
                                                                        .checked
                                                                        ? [
                                                                              ...placements,
                                                                              value,
                                                                          ]
                                                                        : placements.filter(
                                                                              (
                                                                                  placement,
                                                                              ) =>
                                                                                  placement !==
                                                                                  value,
                                                                          ),
                                                            )
                                                        }
                                                        className="size-4"
                                                    />
                                                    <span>{label}</span>
                                                </label>
                                            ))}
                                    </div>
                                    <InputError
                                        message={errors.online_placements}
                                    />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="creative_type">
                                        نوع محتوا
                                    </Label>
                                    {requiresStaticImage ? (
                                        <>
                                            <input
                                                type="hidden"
                                                name="creative_type"
                                                value="image"
                                            />
                                            <div className="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm leading-6 text-emerald-950">
                                                تصویر ثابت ۱۶:۹ — ویدئو در{' '}
                                                {isRewardedPopup
                                                    ? 'پاپ‌آپ بازی'
                                                    : 'ویترین عمومی'}{' '}
                                                نمایش داده نمی‌شود.
                                            </div>
                                        </>
                                    ) : (
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
                                    )}
                                    <InputError
                                        message={errors.creative_type}
                                    />
                                </div>
                                <div className="grid gap-2 md:col-span-2">
                                    <Label htmlFor="asset_file">
                                        بارگذاری تصویر تبلیغ
                                    </Label>
                                    <Input
                                        id="asset_file"
                                        name="asset_file"
                                        type="file"
                                        accept="image/jpeg,image/webp"
                                        className="cursor-pointer"
                                    />
                                    <p className="text-xs leading-6 text-muted-foreground">
                                        JPEG یا WebP، نسبت ۱۶:۹، حداقل ۸۰۰×۴۵۰ و
                                        حداکثر ۲۵۰ کیلوبایت؛ اندازه پیشنهادی
                                        ۱۲۰۰×۶۷۵ پیکسل است.
                                    </p>
                                    <InputError message={errors.asset_file} />
                                </div>
                                <div className="grid gap-2 md:col-span-2">
                                    <Label htmlFor="asset_url">
                                        یا لینک مستقیم فایل/تصویر
                                    </Label>
                                    <Input
                                        id="asset_url"
                                        name="asset_url"
                                        type="url"
                                        dir="ltr"
                                        placeholder="https://example.com/ad.jpg"
                                    />
                                    {requiresStaticImage ? (
                                        <p className="text-xs leading-6 text-muted-foreground">
                                            برای پاپ‌آپ یا ویترین، یکی از دو روش
                                            «بارگذاری تصویر» یا «لینک مستقیم»
                                            الزامی است.
                                        </p>
                                    ) : null}
                                    <InputError message={errors.asset_url} />
                                </div>
                                {isRewardedPopup ? (
                                    <div className="grid gap-3 rounded-md border border-amber-200 bg-amber-50 p-3 sm:grid-cols-3 md:col-span-2">
                                        <div className="grid gap-2">
                                            <Label htmlFor="rewarded_points">
                                                امتیاز کاربر
                                            </Label>
                                            <Input
                                                id="rewarded_points"
                                                name="rewarded_points"
                                                type="number"
                                                min="1"
                                                max="100"
                                                defaultValue="30"
                                                required
                                            />
                                            <InputError
                                                message={errors.rewarded_points}
                                            />
                                        </div>
                                        <div className="grid gap-2">
                                            <Label htmlFor="required_seconds">
                                                زمان مشاهده (ثانیه)
                                            </Label>
                                            <Input
                                                id="required_seconds"
                                                name="required_seconds"
                                                type="number"
                                                min="8"
                                                max="15"
                                                defaultValue="10"
                                                required
                                            />
                                            <InputError
                                                message={
                                                    errors.required_seconds
                                                }
                                            />
                                        </div>
                                        <div className="grid gap-2">
                                            <Label htmlFor="game_stage_index">
                                                مرحله نمایش
                                            </Label>
                                            <select
                                                id="game_stage_index"
                                                name="game_stage_index"
                                                required
                                                defaultValue="3"
                                                className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                            >
                                                {[2, 3, 4, 5, 6, 7, 8, 9].map(
                                                    (stage) => (
                                                        <option
                                                            key={stage}
                                                            value={stage}
                                                        >
                                                            مرحله{' '}
                                                            {stage.toLocaleString(
                                                                'fa-IR',
                                                            )}
                                                        </option>
                                                    ),
                                                )}
                                            </select>
                                            <InputError
                                                message={
                                                    errors.game_stage_index
                                                }
                                            />
                                        </div>
                                    </div>
                                ) : null}
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
                                        required={isRewardedPopup}
                                        className="min-h-24 rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                        placeholder="پیام تبلیغاتی یا توضیح پیشنهاد فروشگاه"
                                    />
                                    <InputError message={errors.body_copy} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="cta_text">
                                        متن دکمه اقدام
                                    </Label>
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
                                                {creativeLabels[
                                                    adRequest.creativeType ?? ''
                                                ] ??
                                                    adRequest.creativeType ??
                                                    'بدون محتوا'}
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
                                        {placementText(
                                            adRequest.placementTypes,
                                            adRequest.placementType,
                                        ) || '-'}{' '}
                                        · هاب: {adRequest.hubName ?? '-'} ·
                                        بازه: {formatDate(adRequest.startsAt)}{' '}
                                        تا {formatDate(adRequest.endsAt)}
                                    </p>
                                    {adRequest.assetUrl ? (
                                        <img
                                            src={adRequest.assetUrl}
                                            alt={adRequest.title}
                                            className="aspect-video w-full max-w-md rounded-md border object-cover"
                                        />
                                    ) : null}
                                    {adRequest.latestReview?.notes ? (
                                        <Alert
                                            variant={
                                                [
                                                    'rejected',
                                                    'revision_requested',
                                                ].includes(adRequest.status)
                                                    ? 'destructive'
                                                    : 'default'
                                            }
                                        >
                                            <AlertDescription>
                                                توضیح آخرین بررسی:{' '}
                                                {adRequest.latestReview.notes}
                                            </AlertDescription>
                                        </Alert>
                                    ) : null}
                                    {[
                                        'rejected',
                                        'revision_requested',
                                    ].includes(adRequest.status) ? (
                                        <Form
                                            action={`/partner/ads/${adRequest.id}`}
                                            method="patch"
                                            encType="multipart/form-data"
                                            className="grid gap-3 rounded-lg border border-amber-200 bg-amber-50/60 p-3 md:grid-cols-2"
                                            options={{ preserveScroll: true }}
                                        >
                                            {({ processing, errors }) => (
                                                <>
                                                    <input
                                                        type="hidden"
                                                        name="ad_type"
                                                        value={adRequest.adType}
                                                    />
                                                    <input
                                                        type="hidden"
                                                        name="creative_type"
                                                        value={
                                                            adRequest.creativeType ??
                                                            'image'
                                                        }
                                                    />
                                                    <input
                                                        type="hidden"
                                                        name="placement_type"
                                                        value={
                                                            adRequest
                                                                .placementTypes[0] ??
                                                            adRequest.placementType ??
                                                            'public_feed'
                                                        }
                                                    />
                                                    {adRequest.placementTypes
                                                        .slice(1)
                                                        .map((placement) => (
                                                            <input
                                                                key={placement}
                                                                type="hidden"
                                                                name="online_placements[]"
                                                                value={
                                                                    placement
                                                                }
                                                            />
                                                        ))}
                                                    {adRequest.rewardedPoints ? (
                                                        <input
                                                            type="hidden"
                                                            name="rewarded_points"
                                                            value={
                                                                adRequest.rewardedPoints
                                                            }
                                                        />
                                                    ) : null}
                                                    {adRequest.requiredSeconds ? (
                                                        <input
                                                            type="hidden"
                                                            name="required_seconds"
                                                            value={
                                                                adRequest.requiredSeconds
                                                            }
                                                        />
                                                    ) : null}
                                                    {adRequest.gameStageIndex ? (
                                                        <input
                                                            type="hidden"
                                                            name="game_stage_index"
                                                            value={
                                                                adRequest.gameStageIndex
                                                            }
                                                        />
                                                    ) : null}
                                                    {adRequest.startsAt ? (
                                                        <input
                                                            type="hidden"
                                                            name="starts_at"
                                                            value={
                                                                adRequest.startsAt
                                                            }
                                                        />
                                                    ) : null}
                                                    {adRequest.endsAt ? (
                                                        <input
                                                            type="hidden"
                                                            name="ends_at"
                                                            value={
                                                                adRequest.endsAt
                                                            }
                                                        />
                                                    ) : null}
                                                    {adRequest.budgetAmount !==
                                                    null ? (
                                                        <input
                                                            type="hidden"
                                                            name="budget_amount"
                                                            value={
                                                                adRequest.budgetAmount
                                                            }
                                                        />
                                                    ) : null}
                                                    {adRequest.impressionCap !==
                                                    null ? (
                                                        <input
                                                            type="hidden"
                                                            name="impression_cap"
                                                            value={
                                                                adRequest.impressionCap
                                                            }
                                                        />
                                                    ) : null}
                                                    {adRequest.clickCap !==
                                                    null ? (
                                                        <input
                                                            type="hidden"
                                                            name="click_cap"
                                                            value={
                                                                adRequest.clickCap
                                                            }
                                                        />
                                                    ) : null}
                                                    <div className="grid gap-2">
                                                        <Label
                                                            htmlFor={`revision-title-${adRequest.id}`}
                                                        >
                                                            عنوان اصلاح‌شده
                                                        </Label>
                                                        <Input
                                                            id={`revision-title-${adRequest.id}`}
                                                            name="title"
                                                            defaultValue={
                                                                adRequest.title
                                                            }
                                                            required
                                                        />
                                                        <InputError
                                                            message={
                                                                errors.title
                                                            }
                                                        />
                                                    </div>
                                                    <div className="grid gap-2">
                                                        <Label
                                                            htmlFor={`revision-asset-${adRequest.id}`}
                                                        >
                                                            تصویر جایگزین
                                                            (اختیاری)
                                                        </Label>
                                                        <Input
                                                            id={`revision-asset-${adRequest.id}`}
                                                            name="asset_file"
                                                            type="file"
                                                            accept=".jpg,.jpeg,.webp,image/jpeg,image/webp"
                                                        />
                                                        <InputError
                                                            message={
                                                                errors.asset_file
                                                            }
                                                        />
                                                    </div>
                                                    <div className="grid gap-2 md:col-span-2">
                                                        <Label
                                                            htmlFor={`revision-body-${adRequest.id}`}
                                                        >
                                                            متن اصلاح‌شده
                                                        </Label>
                                                        <textarea
                                                            id={`revision-body-${adRequest.id}`}
                                                            name="body_copy"
                                                            defaultValue={
                                                                adRequest.bodyCopy ??
                                                                ''
                                                            }
                                                            required={
                                                                adRequest.adType ===
                                                                'rewarded_content'
                                                            }
                                                            className="min-h-24 rounded-md border bg-background px-3 py-2 text-sm"
                                                        />
                                                        <InputError
                                                            message={
                                                                errors.body_copy
                                                            }
                                                        />
                                                    </div>
                                                    <input
                                                        type="hidden"
                                                        name="cta_text"
                                                        value={
                                                            adRequest.ctaText ??
                                                            ''
                                                        }
                                                    />
                                                    <input
                                                        type="hidden"
                                                        name="target_url"
                                                        value={
                                                            adRequest.targetUrl ??
                                                            ''
                                                        }
                                                    />
                                                    <Button
                                                        disabled={processing}
                                                        className="md:col-span-2 md:w-fit"
                                                    >
                                                        ارسال نسخه اصلاح‌شده
                                                    </Button>
                                                </>
                                            )}
                                        </Form>
                                    ) : null}
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
