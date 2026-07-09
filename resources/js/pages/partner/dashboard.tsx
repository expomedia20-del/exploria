import { Form, Head, Link, usePage } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';
import {
    CheckCircle2,
    Gift,
    Megaphone,
    Percent,
    ReceiptText,
    Store,
    TicketCheck,
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
    contactName: string | null;
    contactMobile: string | null;
    category: string | null;
    operatingNotes: string | null;
    displayVisibility: boolean;
};

type RewardDefinition = {
    id: string;
    code: string;
    name: string;
    rewardType: string;
    status: string;
    pointCost: number | null;
    stockQuantity: number | null;
    userRewardsCount: number;
    awardedCount: number;
    inventoryAllocated: number;
    inventoryReserved: number;
    inventoryRedeemed: number;
    inventoryRemaining: number;
    campaignName: string | null;
    approvalStatus: string;
    availabilityStatus: string;
    rewardTier: string | null;
    rewardOption: string | null;
    availableFrom: string | null;
    availableUntil: string | null;
    description: string | null;
    terms: string | null;
    reviewNotes: string | null;
    cycleStepIndex: number | null;
    cycleStepLabel: string | null;
};

type MissionPlanStep = {
    index: number;
    userStep: string;
    title: string;
    rewardTier: string;
    routeIntent: string;
};

type RewardDesignTier = {
    tierKey: string;
    level: string;
    suggestedOptionCount: number;
    options: string[];
};

type Redemption = {
    id: string;
    redemptionCode: string;
    status: string;
    redeemedAt: string | null;
    createdAt: string | null;
    visitorName: string | null;
    rewardName: string | null;
    rewardCode: string | null;
    rewardType: string | null;
    campaignName: string | null;
    campaignCode: string | null;
};

type PartnerAdRequest = {
    id: string;
    code: string;
    title: string;
    status: string;
    adType: string;
    creativeType: string | null;
    placementType: string | null;
    placementStatus: string | null;
    displayDeviceName: string | null;
    displayDeviceCode: string | null;
    hubName: string | null;
    startsAt: string | null;
    endsAt: string | null;
    impressionsCount: number;
    clicksCount: number;
};
type Props = {
    partner: Partner;
    stats: {
        rewardDefinitions: number;
        issuedRewards: number;
        pendingRedemptions: number;
        confirmedRedemptions: number;
        allocatedInventory: number;
        reservedInventory: number;
        redeemedInventory: number;
        remainingInventory: number;
        adRequests: number;
        pendingAds: number;
        scheduledAds: number;
    };
    rewardDefinitions: RewardDefinition[];
    redemptions: Redemption[];
    adRequests: PartnerAdRequest[];
    proposalContext: {
        campaign: {
            id: string;
            code: string;
            name: string;
            status: string;
        } | null;
        missionPlan: MissionPlanStep[];
        rewardTiers: RewardDesignTier[];
    };
};

type SharedProps = {
    flash?: {
        success?: string;
    };
};

const statusLabels: Record<string, string> = {
    active: 'فعال',
    awarded: 'صادر شده',
    pending: 'در انتظار',
    confirmed: 'تایید شده',
    redeemed: 'مصرف شده',
    draft: 'پیش نویس',
    inactive: 'غیرفعال',
    pending_review: 'در انتظار تایید',
    approved: 'تایید شده',
    rejected: 'رد شده',
    revision_requested: 'نیازمند اصلاح',
};

function formatDate(value: string | null) {
    if (!value) {
        return '-';
    }

    return new Intl.DateTimeFormat('fa-IR', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

function formatDateTimeLocal(value: string | null) {
    if (!value) {
        return '';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return '';
    }

    return date.toISOString().slice(0, 16);
}

function formatCount(value: number | null | undefined) {
    return (value ?? 0).toLocaleString('fa-IR');
}

function describeRewardOption(option: string, step: MissionPlanStep | null) {
    if (!option) {
        return 'اگر گزینه آزاد را انتخاب کنید، ادمین بعداً نوع دقیق جایزه یا ترکیب قابل اجرا را با شما نهایی می‌کند.';
    }

    const parts: string[] = [];
    if (option.includes('ارجاع') || option.includes('همراه')) {
        parts.push('یعنی پاداش وقتی معنی دارد که کاربر یک نفر دیگر را همراه کند یا دعوت کند');
    }
    if (option.includes('رأی') || option.includes('رای') || option.includes('نظر') || option.includes('پاسخ')) {
        parts.push('و کاربر در همان گام رأی، نظر یا پاسخ خود را ثبت کرده باشد');
    }
    if (option.includes('کوپن') || option.includes('تخفیف')) {
        parts.push('پس پیشنهاد شما می‌تواند کوپن، تخفیف یا مزیت خرید قابل مصرف باشد');
    }
    if (option.includes('امتیاز')) {
        parts.push('پس پیشنهاد می‌تواند ارزش افزوده‌ای باشد که بعد از انجام گام به امتیاز یا مزیت کاربر اضافه می‌شود');
    }
    if (option.includes('نشان')) {
        parts.push('پس پیشنهاد بیشتر نقش یادگاری، افتخار یا مزیت نمادین برای کاربر دارد');
    }
    if (option.includes('گنج')) {
        parts.push('پس پیشنهاد باید حس کشف یا جایزه پنهان داشته باشد و بعد از تکمیل شرط گام فعال شود');
    }

    if (parts.length > 0) {
        return parts.join('؛ ') + '.';
    }

    return step
        ? `یعنی پیشنهاد شما باید به اجرای گام «${step.userStep}» کمک کند و برای همین بخش از مسیر کاربر قابل استفاده باشد.`
        : 'یعنی پیشنهاد شما باید با همین گزینه پاداش و شرایط اجرایی کمپین همخوان باشد.';
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

export default function PartnerDashboard({
    partner,
    stats,
    rewardDefinitions,
    redemptions,
    adRequests,
    proposalContext,
}: Props) {
    const { flash } = usePage<SharedProps>().props;
    const profileReady = Boolean(partner.contactName && partner.contactMobile && partner.category);
    const pendingOffers = rewardDefinitions.filter((reward) => reward.approvalStatus === 'pending_review').length;
    const approvedOffers = rewardDefinitions.filter((reward) => reward.approvalStatus === 'approved').length;
    const firstStepIndex = proposalContext.missionPlan[0]?.index ?? '';
    const [selectedStepIndex, setSelectedStepIndex] = useState<string>(String(firstStepIndex));
    const selectedStep = useMemo(
        () => proposalContext.missionPlan.find((step) => String(step.index) === selectedStepIndex) ?? null,
        [proposalContext.missionPlan, selectedStepIndex],
    );
    const selectedTier = useMemo(
        () => proposalContext.rewardTiers.find((tier) => tier.tierKey === selectedStep?.rewardTier) ?? null,
        [proposalContext.rewardTiers, selectedStep?.rewardTier],
    );
    const [selectedRewardOption, setSelectedRewardOption] = useState<string>('');
    const selectedRewardOptionLabel = selectedRewardOption || selectedTier?.options[0] || '';
    const offerTitlePlaceholder = selectedRewardOptionLabel
        ? `${selectedRewardOptionLabel} - پیشنهاد ${partner.name}`
        : 'عنوان محصول، خدمت یا تخفیف پیشنهادی شما';
    const offerDescriptionPlaceholder = selectedRewardOptionLabel
        ? `توضیح دهید برای «${selectedRewardOptionLabel}» چه کالا، خدمت، تخفیف یا ظرفیت واقعی ارائه می‌کنید.`
        : 'توضیح دهید این پیشنهاد برای کدام مخاطب، در چه شرایطی و چگونه قابل استفاده است.';
    useEffect(() => {
        setSelectedRewardOption(selectedTier?.options[0] ?? '');
    }, [selectedTier]);
    const tierForStep = (step: MissionPlanStep) =>
        proposalContext.rewardTiers.find((tier) => tier.tierKey === step.rewardTier) ?? null;
    const actionSteps = [
        {
            title: 'تکمیل اطلاعات فروشگاه',
            description: 'نام مسئول، موبایل، دسته‌بندی و توضیح عملیاتی را ثبت کنید تا ادمین بداند پاداش چگونه تحویل می‌شود.',
            complete: profileReady,
            href: '#partner-profile',
            action: 'تکمیل اطلاعات',
        },
        {
            title: 'ثبت پیشنهاد پاداش یا تخفیف',
            description: proposalContext.campaign
                ? `برای ${proposalContext.campaign.name} سطح پاداش، نوع پیشنهاد، ظرفیت و شرایط مصرف را وارد کنید.`
                : 'فعلا کمپین در حال تنظیم برای این مکان پیدا نشده است.',
            complete: rewardDefinitions.length > 0,
            href: '#partner-offer-form',
            action: 'ثبت پیشنهاد',
        },
        {
            title: 'پیگیری تایید ادمین',
            description: pendingOffers > 0
                ? `${pendingOffers.toLocaleString('fa-IR')} پیشنهاد در انتظار بررسی ادمین است.`
                : approvedOffers > 0
                    ? 'پیشنهاد تایید شده دارید؛ موجودی و وضعیت فعال بودن را کنترل کنید.'
                    : 'بعد از ثبت پیشنهاد، وضعیت بررسی همین‌جا نمایش داده می‌شود.',
            complete: approvedOffers > 0,
            href: '#partner-rewards',
            action: 'دیدن وضعیت',
        },
        {
            title: 'تحویل و تایید مصرف پاداش',
            description: 'وقتی کاربر کد مصرف آورد، کد را در فرم تایید وارد کنید تا تحویل پاداش ثبت شود.',
            complete: stats.confirmedRedemptions > 0,
            href: '#reward-redemption-form',
            action: 'تایید مصرف',
        },
    ];

    return (
        <>
            <Head title="پنل فروشگاه" />
            <div
                dir="rtl"
                className="flex h-full min-w-0 flex-1 flex-col gap-5 overflow-x-hidden p-3 sm:p-4"
            >
                <header className="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div className="min-w-0">
                        <p className="text-sm text-muted-foreground">
                            پنل شریک تجاری
                        </p>
                        <h1 className="mt-1 text-2xl font-semibold leading-tight">
                            {partner.name}
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            {partner.venueName} · {partner.partnerType}
                        </p>
                    </div>
                    <div className="grid w-full grid-cols-2 gap-2 text-sm sm:grid-cols-3 md:w-auto xl:grid-cols-6">
                        <Stat
                            icon={Gift}
                            label="پاداش‌ها"
                            value={stats.rewardDefinitions}
                        />
                        <Stat
                            icon={TicketCheck}
                            label="صادر شده"
                            value={stats.issuedRewards}
                        />
                        <Stat
                            icon={ReceiptText}
                            label="در انتظار"
                            value={stats.pendingRedemptions}
                        />
                        <Stat
                            icon={CheckCircle2}
                            label="تایید شده"
                            value={stats.confirmedRedemptions}
                        />
                        <Stat
                            icon={ReceiptText}
                            label="رزرو موجودی"
                            value={stats.reservedInventory}
                        />
                        <Stat
                            icon={Store}
                            label="مانده تحویل"
                            value={stats.remainingInventory}
                        />
                    </div>
                </header>

                <section className="rounded-lg border border-primary/25 bg-primary/5 p-4 shadow-sm">
                    <div className="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                        <div>
                            <p className="text-xs text-muted-foreground">راهنمای مرحله ۴ برای فروشگاه و اسپانسر</p>
                            <h2 className="mt-1 font-semibold">برای مشارکت در کمپین این کارها را انجام دهید</h2>
                            <p className="mt-1 text-sm text-muted-foreground">
                                این صفحه برای ثبت پیشنهاد پاداش، پیگیری تایید ادمین، مدیریت موجودی و تایید مصرف پاداش کاربران است.
                            </p>
                        </div>
                        {proposalContext.campaign ? (
                            <span className="rounded-full bg-background px-3 py-1 text-xs" dir="ltr">{proposalContext.campaign.code}</span>
                        ) : null}
                    </div>
                    <div className="mt-4 flex flex-col gap-2 rounded-lg border border-primary/20 bg-background/80 p-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p className="text-sm font-medium">اقدام اصلی این مرحله: پیشنهاد پاداش یا تخفیف</p>
                            <p className="mt-1 text-xs text-muted-foreground">ثبت تبلیغ اقدام جداگانه است و بعد از مشخص شدن نقش پاداش می‌تواند انجام شود.</p>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            <Button asChild size="sm">
                                <a href="#partner-offer-form">
                                    <Gift className="size-4" />
                                    ثبت پیشنهاد/تخفیف
                                </a>
                            </Button>
                            <Button asChild size="sm" variant="outline">
                                <Link href="/partner/ads">
                                    <Megaphone className="size-4" />
                                    تبلیغات جداگانه
                                </Link>
                            </Button>
                        </div>
                    </div>
                    <div className="mt-4 grid gap-3 lg:grid-cols-4">
                        {actionSteps.map((step, index) => (
                            <a key={step.title} href={step.href} className="rounded-md border border-border/70 bg-background/80 p-3 text-sm transition hover:border-primary/50">
                                <div className="flex items-start justify-between gap-2">
                                    <p className="font-medium">{index + 1}. {step.title}</p>
                                    <span className={step.complete ? 'text-xs text-emerald-700 dark:text-emerald-300' : 'text-xs text-amber-700 dark:text-amber-300'}>
                                        {step.complete ? 'انجام شده' : 'نیازمند اقدام'}
                                    </span>
                                </div>
                                <p className="mt-2 min-h-12 text-xs text-muted-foreground">{step.description}</p>
                                <p className="mt-3 text-xs font-medium text-primary">{step.action}</p>
                            </a>
                        ))}
                    </div>
                </section>
                {flash?.success ? (
                    <Alert>
                        <AlertDescription>{flash.success}</AlertDescription>
                    </Alert>
                ) : null}

                <section id="partner-profile" className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
                    <div className="mb-4 flex items-center gap-2">
                        <Store className="size-4 text-muted-foreground" />
                        <h2 className="font-semibold">اطلاعات فروشگاه</h2>
                    </div>
                    <Form
                        action="/partner/profile"
                        method="patch"
                        options={{ preserveScroll: true }}
                        className="grid gap-4 md:grid-cols-2"
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="partner_name">
                                        نام فروشگاه
                                    </Label>
                                    <Input
                                        id="partner_name"
                                        name="name"
                                        required
                                        defaultValue={partner.name}
                                    />
                                    <InputError message={errors.name} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="partner_category">
                                        دسته‌بندی
                                    </Label>
                                    <Input
                                        id="partner_category"
                                        name="category"
                                        defaultValue={partner.category ?? ''}
                                        placeholder="مثلا کافه، پوشاک، خدمات"
                                    />
                                    <InputError message={errors.category} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="contact_name">
                                        نام مسئول
                                    </Label>
                                    <Input
                                        id="contact_name"
                                        name="contact_name"
                                        defaultValue={partner.contactName ?? ''}
                                    />
                                    <InputError message={errors.contact_name} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="contact_mobile">
                                        موبایل مسئول
                                    </Label>
                                    <Input
                                        id="contact_mobile"
                                        name="contact_mobile"
                                        dir="ltr"
                                        defaultValue={partner.contactMobile ?? ''}
                                    />
                                    <InputError message={errors.contact_mobile} />
                                </div>
                                <div className="grid gap-2 md:col-span-2">
                                    <Label htmlFor="operating_notes">
                                        توضیحات عملیاتی
                                    </Label>
                                    <textarea
                                        id="operating_notes"
                                        name="operating_notes"
                                        defaultValue={partner.operatingNotes ?? ''}
                                        className="min-h-20 rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                        placeholder="ساعت پاسخگویی، نکته‌های تحویل جایزه یا محدودیت‌های اجرایی"
                                    />
                                    <InputError message={errors.operating_notes} />
                                </div>
                                <div className="flex items-center gap-2 md:col-span-2">
                                    <input
                                        type="hidden"
                                        name="display_visibility"
                                        value="0"
                                    />
                                    <input
                                        id="display_visibility"
                                        name="display_visibility"
                                        type="checkbox"
                                        value="1"
                                        defaultChecked={partner.displayVisibility}
                                        className="size-4 rounded border border-input"
                                    />
                                    <Label htmlFor="display_visibility">
                                        نمایش فروشگاه در تجربه‌های کاربر و نمایشگرها
                                    </Label>
                                    <InputError message={errors.display_visibility} />
                                </div>
                                <div className="md:col-span-2">
                                    <Button disabled={processing}>
                                        <Store className="size-4" />
                                        ذخیره اطلاعات فروشگاه
                                    </Button>
                                </div>
                            </>
                        )}
                    </Form>
                </section>
                <section className="grid gap-4 lg:grid-cols-[1.2fr_0.8fr]">
                    <div
                        id="partner-offer-form"
                        className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border"
                    >
                        <div className="mb-4 flex items-center gap-2">
                            <Percent className="size-4 text-muted-foreground" />
                            <h2 className="font-semibold">
                                ثبت پیشنهاد/تخفیف جدید
                            </h2>
                        </div>
                        <div className="mb-4 rounded-md bg-muted/40 px-3 py-2 text-xs text-muted-foreground">
                            {proposalContext.campaign ? (
                                <>
                                    <p className="font-medium text-foreground">کمپین در حال تنظیم: {proposalContext.campaign.name}</p>
                                    <p className="mt-1">ابتدا گام چرخه کمپین را انتخاب کنید؛ سطح پاداش همان گام به‌صورت خودکار به پیشنهاد شما وصل می‌شود.</p>
                                </>
                            ) : (
                                <p>برای ثبت پیشنهاد پاداش، ابتدا باید یک کمپین در حال تنظیم برای مکان این فروشگاه وجود داشته باشد.</p>
                            )}
                        </div>
                        {proposalContext.campaign ? (
                            <div className="mb-4 rounded-md border border-sidebar-border/70 dark:border-sidebar-border">
                                <div className="border-b border-sidebar-border/70 px-3 py-2 dark:border-sidebar-border">
                                    <p className="text-sm font-medium">خلاصه چرخه و سطح‌های پاداش کمپین</p>
                                    <p className="mt-1 text-xs text-muted-foreground">
                                        هر ردیف یک گام از مسیر کاربر است. برای ثبت پیشنهاد، روی گام مناسب کلیک کنید یا همان گام را در فرم انتخاب کنید.
                                    </p>
                                </div>
                                <div className="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                                    {proposalContext.missionPlan.length === 0 ? (
                                        <p className="px-3 py-3 text-xs text-muted-foreground">
                                            چرخه مأموریت برای این کمپین هنوز آماده نشده است.
                                        </p>
                                    ) : (
                                        proposalContext.missionPlan.map((step) => {
                                            const tier = tierForStep(step);
                                            const isSelected = String(step.index) === selectedStepIndex;

                                            return (
                                                <button
                                                    key={step.index}
                                                    type="button"
                                                    onClick={() => setSelectedStepIndex(String(step.index))}
                                                    className={`grid w-full gap-2 px-3 py-3 text-right text-xs transition hover:bg-muted/40 md:grid-cols-[1.1fr_0.8fr_1.2fr] ${
                                                        isSelected ? 'bg-primary/10' : ''
                                                    }`}
                                                >
                                                    <span>
                                                        <span className="font-medium text-foreground">
                                                            گام {step.index.toLocaleString('fa-IR')}: {step.userStep}
                                                        </span>
                                                    </span>
                                                    <span>
                                                        <span className="block text-muted-foreground">سطح پاداش</span>
                                                        <span className="font-medium text-foreground">{tier?.level ?? step.rewardTier}</span>
                                                    </span>
                                                    <span>
                                                        <span className="block text-muted-foreground">گزینه‌های همین سطح</span>
                                                        <span className="line-clamp-2 text-foreground">
                                                            {(tier?.options ?? []).slice(0, 3).join('، ') || 'در انتظار تنظیم ادمین'}
                                                        </span>
                                                    </span>
                                                </button>
                                            );
                                        })
                                    )}
                                </div>
                            </div>
                        ) : null}
                        <Form
                            action="/partner/offers"
                            method="post"
                            options={{ preserveScroll: true }}
                            className="grid gap-4 md:grid-cols-2"
                            autoComplete="off"
                        >
                            {({ processing, errors }) => (
                                <>
                                    {proposalContext.campaign ? (
                                        <input type="hidden" name="campaign_id" value={proposalContext.campaign.id} />
                                    ) : null}
                                    <input type="hidden" name="cycle_step_label" value={selectedStep?.userStep ?? ''} />
                                    <input type="hidden" name="reward_tier" value={selectedStep?.rewardTier ?? ''} />
                                    <div className="grid gap-2">
                                        <Label htmlFor="cycle_step_index">
                                            گام چرخه کمپین
                                        </Label>
                                        <select
                                            id="cycle_step_index"
                                            name="cycle_step_index"
                                            required
                                            className="h-9 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                            value={selectedStepIndex}
                                            onChange={(event) => setSelectedStepIndex(event.target.value)}
                                        >
                                            <option value="">انتخاب گام</option>
                                            {proposalContext.missionPlan.map((step) => (
                                                <option key={step.index} value={step.index}>
                                                    گام {step.index.toLocaleString('fa-IR')} - {step.userStep}
                                                </option>
                                            ))}
                                        </select>
                                        <InputError message={errors.cycle_step_index} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="reward_tier_display">
                                            سطح پاداش همان گام
                                        </Label>
                                        <Input
                                            id="reward_tier_display"
                                            value={selectedTier?.level ?? 'پس از انتخاب گام مشخص می‌شود'}
                                            readOnly
                                            className="bg-muted/40"
                                        />
                                        <InputError message={errors.reward_tier} />
                                    </div>
                                    <div className="grid gap-2 md:col-span-2">
                                        <Label htmlFor="route_intent_display">
                                            ارتباط عملیاتی گام
                                        </Label>
                                        <Input
                                            id="route_intent_display"
                                            value={selectedStep?.routeIntent ?? 'گام چرخه را انتخاب کنید'}
                                            readOnly
                                            className="bg-muted/40"
                                        />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="reward_option">
                                            گزینه/ترکیب پیشنهادی
                                        </Label>
                                        <select
                                            id="reward_option"
                                            name="reward_option"
                                            className="h-9 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                            key={selectedStep?.rewardTier ?? 'no-tier'}
                                            value={selectedRewardOption}
                                            onChange={(event) => setSelectedRewardOption(event.target.value)}
                                        >
                                            <option value="">انتخاب آزاد توسط ادمین</option>
                                            {(selectedTier?.options ?? []).map((option) => (
                                                <option key={option} value={option}>
                                                    {option}
                                                </option>
                                            ))}
                                        </select>
                                        <p className="text-xs text-muted-foreground">
                                            این گزینه‌ها مربوط به {selectedStep ? `گام ${selectedStep.index.toLocaleString('fa-IR')}` : 'گام انتخاب‌شده'} و سطح {selectedTier?.level ?? '-'} هستند.
                                            {' '}
                                            {describeRewardOption(selectedRewardOptionLabel, selectedStep)}
                                        </p>
                                        <InputError message={errors.reward_option} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="offer_name">
                                            عنوان پیشنهادی که ارائه می‌کنید
                                        </Label>
                                        <Input
                                            id="offer_name"
                                            name="name"
                                            required
                                            autoComplete="off"
                                            placeholder={offerTitlePlaceholder}
                                        />
                                        <p className="text-xs text-muted-foreground">
                                            عنوان را براساس گزینه/ترکیب انتخاب‌شده بنویسید؛ ادمین همین عنوان را برای بررسی پیشنهاد می‌بیند.
                                        </p>
                                        <InputError message={errors.name} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="reward_type">
                                            نوع پیشنهاد شما
                                        </Label>
                                        <select
                                            id="reward_type"
                                            name="reward_type"
                                            required
                                            className="h-9 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                            defaultValue="discount"
                                        >
                                            <option value="discount">
                                                تخفیف
                                            </option>
                                            <option value="partner_coupon">
                                                کوپن فروشگاهی
                                            </option>
                                            <option value="gift">هدیه</option>
                                            <option value="service_credit">
                                                اعتبار خدمات
                                            </option>
                                            <option value="sponsor_reward">
                                                پاداش اسپانسری
                                            </option>
                                        </select>
                                        <p className="text-xs text-muted-foreground">
                                            این گزینه فقط جنس پیشنهاد را مشخص می‌کند؛ گام و سطح پاداش از بالا تعیین شده است.
                                        </p>
                                        <InputError
                                            message={errors.reward_type}
                                        />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="point_cost">
                                            هزینه امتیازی
                                        </Label>
                                        <Input
                                            id="point_cost"
                                            name="point_cost"
                                            type="number"
                                            min="0"
                                            autoComplete="off"
                                            placeholder="اختیاری"
                                        />
                                        <InputError
                                            message={errors.point_cost}
                                        />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="stock_quantity">
                                            ظرفیت/موجودی
                                        </Label>
                                        <Input
                                            id="stock_quantity"
                                            name="stock_quantity"
                                            type="number"
                                            min="1"
                                            autoComplete="off"
                                            placeholder="اختیاری"
                                        />
                                        <InputError
                                            message={errors.stock_quantity}
                                        />
                                    </div>
                                    <div className="grid gap-2 md:col-span-2">
                                        <Label htmlFor="description">
                                            توضیح کوتاه
                                        </Label>
                                        <textarea
                                            id="description"
                                            name="description"
                                            autoComplete="off"
                                            className="min-h-20 rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                            placeholder={offerDescriptionPlaceholder}
                                        />
                                        <InputError
                                            message={errors.description}
                                        />
                                    </div>
                                    <div className="grid gap-2 md:col-span-2">
                                        <Label htmlFor="terms">
                                            شرایط مصرف
                                        </Label>
                                        <textarea
                                            id="terms"
                                            name="terms"
                                            autoComplete="off"
                                            className="min-h-20 rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                            placeholder="مثلا فقط روزهای کاری، یک‌بار برای هر کاربر، غیرقابل تبدیل به وجه نقد"
                                        />
                                        <InputError message={errors.terms} />
                                    </div>
                                    <div className="md:col-span-2">
                                        <Button disabled={processing || !proposalContext.campaign}>
                                            <Gift className="size-4" />
                                            ارسال برای تایید
                                        </Button>
                                    </div>
                                </>
                            )}
                        </Form>
                    </div>

                    <div
                        id="reward-redemption-form"
                        className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border"
                    >
                        <div className="mb-4 flex items-center gap-2">
                            <TicketCheck className="size-4 text-muted-foreground" />
                            <h2 className="font-semibold">تایید مصرف پاداش</h2>
                        </div>
                        <Form
                            action="/partner/redemptions/confirm"
                            method="post"
                            options={{ preserveScroll: true }}
                            className="grid gap-4 md:grid-cols-[1fr_auto]"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <div className="grid gap-2">
                                        <Label htmlFor="redemption_code">
                                            کد مصرف مشتری
                                        </Label>
                                        <Input
                                            id="redemption_code"
                                            name="redemption_code"
                                            required
                                            dir="ltr"
                                            placeholder="مثلا X4K8P2M9Q1"
                                        />
                                        <InputError
                                            message={errors.redemption_code}
                                        />
                                    </div>
                                    <div className="flex items-end">
                                        <Button disabled={processing}>
                                            <CheckCircle2 className="size-4" />
                                            تایید مصرف
                                        </Button>
                                    </div>
                                </>
                            )}
                        </Form>
                    </div>
                </section>

                <section className="rounded-lg border border-sidebar-border/70 bg-background dark:border-sidebar-border">
                    <div className="border-b border-sidebar-border/70 px-4 py-3 dark:border-sidebar-border">
                        <div className="flex items-center gap-2">
                            <Megaphone className="size-4 text-muted-foreground" />
                            <h2 className="font-semibold">تبلیغات فروشگاه</h2>
                        </div>
                    </div>
                    <div className="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                        {adRequests.length === 0 ? (
                            <p className="p-4 text-sm text-muted-foreground">
                                هنوز درخواست تبلیغی برای این فروشگاه ثبت نشده
                                است.
                            </p>
                        ) : (
                            adRequests.map((adRequest) => (
                                <article
                                    key={adRequest.id}
                                    className="grid gap-2 px-4 py-3 text-sm lg:grid-cols-[1.2fr_1fr_1fr]"
                                >
                                    <div className="min-w-0">
                                        <p className="truncate font-medium">
                                            {adRequest.title}
                                        </p>
                                        <p
                                            className="mt-1 truncate text-xs text-muted-foreground"
                                            dir="ltr"
                                        >
                                            {adRequest.code} ·{' '}
                                            {adRequest.creativeType ?? '-'}
                                        </p>
                                    </div>
                                    <p className="text-xs text-muted-foreground">
                                        وضعیت:{' '}
                                        {statusLabels[adRequest.status] ??
                                            adRequest.status}{' '}
                                        · جایگاه:{' '}
                                        {adRequest.placementType ?? '-'} · پخش:{' '}
                                        {statusLabels[
                                            adRequest.placementStatus ?? ''
                                        ] ??
                                            adRequest.placementStatus ??
                                            '-'}
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        نمایشگر:{' '}
                                        {adRequest.displayDeviceName ?? '-'} ·
                                        نمایش:{' '}
                                        {adRequest.impressionsCount.toLocaleString(
                                            'fa-IR',
                                        )}{' '}
                                        · کلیک:{' '}
                                        {adRequest.clicksCount.toLocaleString(
                                            'fa-IR',
                                        )}
                                    </p>
                                </article>
                            ))
                        )}
                    </div>
                </section>
                <section className="grid gap-4 lg:grid-cols-2">
                    <div id="partner-rewards" className="rounded-lg border border-sidebar-border/70 bg-background dark:border-sidebar-border">
                        <div className="border-b border-sidebar-border/70 px-4 py-3 dark:border-sidebar-border">
                            <h2 className="font-semibold">پاداش‌های فروشگاه</h2>
                        </div>
                        <div className="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                            {rewardDefinitions.length === 0 ? (
                                <p className="p-4 text-sm text-muted-foreground">
                                    هنوز پاداشی برای این فروشگاه تعریف نشده است.
                                </p>
                            ) : (
                                rewardDefinitions.map((reward) => (
                                    <article
                                        key={reward.id}
                                        className="grid gap-2 px-4 py-3 text-sm"
                                    >
                                        <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                            <div className="min-w-0">
                                                <p className="truncate font-medium">
                                                    {reward.name}
                                                </p>
                                                <p
                                                    className="mt-1 truncate text-xs text-muted-foreground"
                                                    dir="ltr"
                                                >
                                                    {reward.code} ·{' '}
                                                    {reward.rewardType}
                                                </p>
                                            </div>
                                            <span className="w-fit shrink-0 text-xs text-muted-foreground">
                                                {statusLabels[
                                                    reward.approvalStatus
                                                ] ??
                                                    statusLabels[
                                                        reward.status
                                                    ] ??
                                                    reward.status}
                                            </span>
                                        </div>
                                        {reward.description ? (
                                            <p className="line-clamp-2 text-xs text-muted-foreground">
                                                {reward.description}
                                            </p>
                                        ) : null}
                                        <p className="text-xs text-muted-foreground">
                                            {reward.cycleStepIndex ? `گام ${Number(reward.cycleStepIndex).toLocaleString('fa-IR')}: ${reward.cycleStepLabel ?? '-'}` : 'گام چرخه: ثبت نشده'}
                                            {' '}· سطح: {reward.rewardTier ?? '-'}
                                            {reward.rewardOption ? ` · گزینه: ${reward.rewardOption}` : ''}
                                        </p>
                                        <p className="text-xs text-muted-foreground">
                                            صادر شده:{' '}
                                            {formatCount(reward.userRewardsCount)}{' '}
                                            · موجودی:{' '}
                                            {reward.stockQuantity?.toLocaleString(
                                                'fa-IR',
                                            ) ?? 'نامحدود'}
                                        </p>
                                        <p className="text-xs text-muted-foreground">
                                            تخصیص:{' '}
                                            {formatCount(reward.inventoryAllocated)}
                                            {' '}· رزرو:{' '}
                                            {formatCount(reward.inventoryReserved)}
                                            {' '}· مصرف:{' '}
                                            {formatCount(reward.inventoryRedeemed)}
                                            {' '}· مانده:{' '}
                                            {formatCount(reward.inventoryRemaining)}
                                        </p>
                                        <Form
                                            action={`/partner/offers/${reward.id}`}
                                            method="patch"
                                            options={{ preserveScroll: true }}
                                            className="mt-2 grid gap-3 rounded-md bg-muted/30 p-3 md:grid-cols-2"
                                        >
                                            {({ processing, errors }) => (
                                                <>
                                                    <div className="grid gap-2">
                                                        <Label htmlFor={`reward_stock_${reward.id}`}>
                                                            موجودی
                                                        </Label>
                                                        <Input
                                                            id={`reward_stock_${reward.id}`}
                                                            name="stock_quantity"
                                                            type="number"
                                                            min="0"
                                                            defaultValue={reward.stockQuantity ?? ''}
                                                        />
                                                        <InputError message={errors.stock_quantity} />
                                                    </div>
                                                    <div className="grid gap-2">
                                                        <Label htmlFor={`reward_point_cost_${reward.id}`}>
                                                            هزینه امتیازی
                                                        </Label>
                                                        <Input
                                                            id={`reward_point_cost_${reward.id}`}
                                                            name="point_cost"
                                                            type="number"
                                                            min="0"
                                                            defaultValue={reward.pointCost ?? ''}
                                                        />
                                                        <InputError message={errors.point_cost} />
                                                    </div>
                                                    <div className="grid gap-2">
                                                        <Label htmlFor={`reward_status_${reward.id}`}>
                                                            وضعیت ارائه
                                                        </Label>
                                                        <select
                                                            id={`reward_status_${reward.id}`}
                                                            name="availability_status"
                                                            defaultValue={reward.availabilityStatus}
                                                            className="h-9 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                                        >
                                                            <option value="active">فعال</option>
                                                            <option value="paused">متوقف</option>
                                                        </select>
                                                        <InputError message={errors.availability_status} />
                                                    </div>
                                                    <DateTimePickerField
                                                        id={`reward_from_${reward.id}`}
                                                        name="available_from"
                                                        label="شروع اعتبار"
                                                        defaultValue={formatDateTimeLocal(reward.availableFrom)}
                                                        error={errors.available_from}
                                                    />
                                                    <DateTimePickerField
                                                        id={`reward_until_${reward.id}`}
                                                        name="available_until"
                                                        label="پایان اعتبار"
                                                        defaultValue={formatDateTimeLocal(reward.availableUntil)}
                                                        error={errors.available_until}
                                                    />
                                                    <div className="flex items-end">
                                                        <Button size="sm" disabled={processing}>
                                                            ذخیره تنظیمات
                                                        </Button>
                                                    </div>
                                                    <div className="grid gap-2 md:col-span-2">
                                                        <Label htmlFor={`reward_description_${reward.id}`}>
                                                            توضیح پیشنهاد
                                                        </Label>
                                                        <textarea
                                                            id={`reward_description_${reward.id}`}
                                                            name="description"
                                                            defaultValue={reward.description ?? ''}
                                                            className="min-h-16 rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                                        />
                                                        <InputError message={errors.description} />
                                                    </div>
                                                    <div className="grid gap-2 md:col-span-2">
                                                        <Label htmlFor={`reward_terms_${reward.id}`}>
                                                            شرایط مصرف
                                                        </Label>
                                                        <textarea
                                                            id={`reward_terms_${reward.id}`}
                                                            name="terms"
                                                            defaultValue={reward.terms ?? ''}
                                                            className="min-h-16 rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                                        />
                                                        <InputError message={errors.terms} />
                                                    </div>
                                                </>
                                            )}
                                        </Form>
                                    </article>
                                ))
                            )}
                        </div>
                    </div>

                    <div className="rounded-lg border border-sidebar-border/70 bg-background dark:border-sidebar-border">
                        <div className="border-b border-sidebar-border/70 px-4 py-3 dark:border-sidebar-border">
                            <h2 className="font-semibold">مصرف پاداش‌ها</h2>
                        </div>
                        <div className="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                            {redemptions.length === 0 ? (
                                <p className="p-4 text-sm text-muted-foreground">
                                    هنوز کد مصرفی برای این فروشگاه ثبت نشده است.
                                </p>
                            ) : (
                                redemptions.map((redemption) => (
                                    <article
                                        key={redemption.id}
                                        className="grid gap-2 px-4 py-3 text-sm"
                                    >
                                        <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                            <div className="min-w-0">
                                                <p className="truncate font-medium">
                                                    {redemption.rewardName}
                                                </p>
                                                <p
                                                    className="mt-1 truncate text-xs text-muted-foreground"
                                                    dir="ltr"
                                                >
                                                    {redemption.redemptionCode}
                                                    {redemption.rewardCode
                                                        ? ` · ${redemption.rewardCode}`
                                                        : ''}
                                                </p>
                                            </div>
                                            <span className="w-fit shrink-0 rounded-full bg-muted px-2.5 py-1 text-xs">
                                                {statusLabels[
                                                    redemption.status
                                                ] ?? redemption.status}
                                            </span>
                                        </div>
                                        {redemption.status === 'pending' ? (
                                            <Form
                                                action="/partner/redemptions/confirm"
                                                method="post"
                                                options={{
                                                    preserveScroll: true,
                                                }}
                                                className="flex justify-end"
                                            >
                                                {({ processing }) => (
                                                    <>
                                                        <input
                                                            type="hidden"
                                                            name="redemption_code"
                                                            value={
                                                                redemption.redemptionCode
                                                            }
                                                        />
                                                        <Button
                                                            size="sm"
                                                            disabled={processing}
                                                        >
                                                            <CheckCircle2 className="size-4" />
                                                            تایید همین کد
                                                        </Button>
                                                    </>
                                                )}
                                            </Form>
                                        ) : null}
                                        <p className="text-xs text-muted-foreground">
                                            مشتری:{' '}
                                            {redemption.visitorName ?? '-'} ·{' '}
                                            کمپین:{' '}
                                            {redemption.campaignName ??
                                                redemption.campaignCode ??
                                                '-'}{' '}
                                            ·{' '}
                                            {formatDate(
                                                redemption.redeemedAt ??
                                                    redemption.createdAt,
                                            )}
                                        </p>
                                    </article>
                                ))
                            )}
                        </div>
                    </div>
                </section>
            </div>
        </>
    );
}

PartnerDashboard.layout = {
    breadcrumbs: [
        {
            title: 'پنل فروشگاه',
            href: '/partner/dashboard',
        },
    ],
};
