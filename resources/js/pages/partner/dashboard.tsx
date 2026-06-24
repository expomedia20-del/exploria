import { Form, Head, usePage } from '@inertiajs/react';
import {
    CheckCircle2,
    Gift,
    Percent,
    ReceiptText,
    TicketCheck,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
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
    campaignName: string | null;
    approvalStatus: string;
    description: string | null;
};

type Redemption = {
    id: string;
    redemptionCode: string;
    status: string;
    redeemedAt: string | null;
    createdAt: string | null;
    visitorName: string | null;
    rewardName: string | null;
    rewardType: string | null;
};

type Props = {
    partner: Partner;
    stats: {
        rewardDefinitions: number;
        issuedRewards: number;
        pendingRedemptions: number;
        confirmedRedemptions: number;
    };
    rewardDefinitions: RewardDefinition[];
    redemptions: Redemption[];
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

export default function PartnerDashboard({
    partner,
    stats,
    rewardDefinitions,
    redemptions,
}: Props) {
    const { flash } = usePage<SharedProps>().props;

    return (
        <>
            <Head title="پنل فروشگاه" />
            <div
                dir="rtl"
                className="flex h-full flex-1 flex-col gap-5 overflow-x-auto p-4"
            >
                <header className="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p className="text-sm text-muted-foreground">
                            پنل شریک تجاری
                        </p>
                        <h1 className="mt-1 text-2xl font-semibold">
                            {partner.name}
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            {partner.venueName} · {partner.partnerType}
                        </p>
                    </div>
                    <div className="grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
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
                    </div>
                </header>

                {flash?.success ? (
                    <Alert>
                        <AlertDescription>{flash.success}</AlertDescription>
                    </Alert>
                ) : null}

                <section className="grid gap-4 lg:grid-cols-[1.2fr_0.8fr]">
                    <div className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
                        <div className="mb-4 flex items-center gap-2">
                            <Percent className="size-4 text-muted-foreground" />
                            <h2 className="font-semibold">
                                ثبت پیشنهاد/تخفیف جدید
                            </h2>
                        </div>
                        <Form
                            action="/partner/offers"
                            method="post"
                            options={{ preserveScroll: true }}
                            className="grid gap-4 md:grid-cols-2"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <div className="grid gap-2">
                                        <Label htmlFor="name">
                                            عنوان پیشنهاد
                                        </Label>
                                        <Input
                                            id="name"
                                            name="name"
                                            required
                                            placeholder="مثلا ۲۰٪ تخفیف نوشیدنی خانواده"
                                        />
                                        <InputError message={errors.name} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="reward_type">
                                            نوع پیشنهاد
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
                                            className="min-h-20 rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                            placeholder="این پیشنهاد کجا و برای چه مخاطبی قابل استفاده است؟"
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
                                            className="min-h-20 rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                            placeholder="مثلا فقط روزهای کاری، یک‌بار برای هر کاربر، غیرقابل تبدیل به وجه نقد"
                                        />
                                        <InputError message={errors.terms} />
                                    </div>
                                    <div className="md:col-span-2">
                                        <Button disabled={processing}>
                                            <Gift className="size-4" />
                                            ارسال برای تایید
                                        </Button>
                                    </div>
                                </>
                            )}
                        </Form>
                    </div>

                    <div className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
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

                <section className="grid gap-4 lg:grid-cols-2">
                    <div className="rounded-lg border border-sidebar-border/70 bg-background dark:border-sidebar-border">
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
                                        <div className="flex items-center justify-between gap-3">
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
                                            <span className="shrink-0 text-xs text-muted-foreground">
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
                                            صادر شده:{' '}
                                            {reward.userRewardsCount.toLocaleString(
                                                'fa-IR',
                                            )}{' '}
                                            · موجودی:{' '}
                                            {reward.stockQuantity?.toLocaleString(
                                                'fa-IR',
                                            ) ?? 'نامحدود'}
                                        </p>
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
                                        <div className="flex items-center justify-between gap-3">
                                            <div className="min-w-0">
                                                <p className="truncate font-medium">
                                                    {redemption.rewardName}
                                                </p>
                                                <p
                                                    className="mt-1 truncate text-xs text-muted-foreground"
                                                    dir="ltr"
                                                >
                                                    {redemption.redemptionCode}
                                                </p>
                                            </div>
                                            <span className="shrink-0 rounded-full bg-muted px-2.5 py-1 text-xs">
                                                {statusLabels[
                                                    redemption.status
                                                ] ?? redemption.status}
                                            </span>
                                        </div>
                                        <p className="text-xs text-muted-foreground">
                                            مشتری:{' '}
                                            {redemption.visitorName ?? '-'} ·{' '}
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
