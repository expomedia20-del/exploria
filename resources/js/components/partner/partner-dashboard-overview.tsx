import { Link } from '@inertiajs/react';
import {
    ArrowLeft,
    BadgeCheck,
    BarChart3,
    CheckCircle2,
    CircleAlert,
    Eye,
    Gift,
    Megaphone,
    PackageCheck,
    ReceiptText,
    ShoppingCart,
    Sparkles,
    Store,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { partnerStatusLabels } from '@/components/partner/partner-dashboard-utils';
import type {
    Partner,
    PartnerAdRequest,
    PartnerDashboardSection,
    PartnerDashboardStats,
} from '@/types/partner-dashboard';

export type PartnerActionStep = {
    title: string;
    description: string;
    complete: boolean;
    section: PartnerDashboardSection;
    action: string;
};

function OverviewStat({
    icon: Icon,
    label,
    value,
    hint,
    emphasis = false,
}: {
    icon: LucideIcon;
    label: string;
    value: number;
    hint: string;
    emphasis?: boolean;
}) {
    return (
        <div
            className={`rounded-xl border p-4 ${
                emphasis
                    ? 'border-primary/30 bg-primary/5'
                    : 'border-sidebar-border/70 bg-background'
            }`}
        >
            <div className="flex items-center justify-between gap-3">
                <p className="text-sm text-muted-foreground">{label}</p>
                <span className="rounded-lg bg-muted p-2">
                    <Icon className="size-4" />
                </span>
            </div>
            <p className="mt-3 text-2xl font-semibold">
                {value.toLocaleString('fa-IR')}
            </p>
            <p className="mt-1 text-xs text-muted-foreground">{hint}</p>
        </div>
    );
}

export function PartnerDashboardOverview({
    partner,
    stats,
    actionSteps,
    adRequests,
    onNavigate,
}: {
    partner: Partner;
    stats: PartnerDashboardStats;
    actionSteps: PartnerActionStep[];
    adRequests: PartnerAdRequest[];
    onNavigate: (section: PartnerDashboardSection) => void;
}) {
    const nextAction =
        actionSteps.find((step) => !step.complete) ??
        actionSteps[actionSteps.length - 1];
    const completedSteps = actionSteps.filter((step) => step.complete).length;
    const lastAd = adRequests[0] ?? null;

    return (
        <div className="grid gap-4">
            <section className="overflow-hidden rounded-2xl border border-primary/25 bg-gradient-to-l from-primary/10 via-background to-background shadow-sm">
                <div className="grid gap-5 p-5 lg:grid-cols-[1.35fr_0.65fr] lg:items-center">
                    <div>
                        <div className="flex flex-wrap items-center gap-2">
                            <span className="rounded-full bg-primary/10 px-3 py-1 text-xs font-medium text-primary">
                                مرکز کنترل فروشگاه
                            </span>
                            <span
                                className={`rounded-full px-3 py-1 text-xs ${
                                    partner.displayVisibility
                                        ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200'
                                        : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200'
                                }`}
                            >
                                {partner.displayVisibility
                                    ? 'نمایش فروشگاه فعال است'
                                    : 'نمایش فروشگاه غیرفعال است'}
                            </span>
                        </div>
                        <p className="mt-4 text-sm text-muted-foreground">
                            امروز چه کاری مهم‌تر است؟
                        </p>
                        <h2 className="mt-1 text-xl font-semibold">
                            {nextAction?.title ?? 'وضعیت فروشگاه را مرور کنید'}
                        </h2>
                        <p className="mt-2 max-w-2xl text-sm leading-7 text-muted-foreground">
                            {nextAction?.description ??
                                'همه گام‌های اصلی آماده‌اند؛ وضعیت پیشنهادها، موجودی و تحویل‌های مشتریان را کنترل کنید.'}
                        </p>
                        {nextAction ? (
                            <Button
                                className="mt-4"
                                onClick={() => onNavigate(nextAction.section)}
                            >
                                {nextAction.action}
                                <ArrowLeft className="size-4" />
                            </Button>
                        ) : null}
                    </div>
                    <div className="rounded-xl border border-primary/20 bg-background/80 p-4">
                        <div className="flex items-center justify-between gap-3">
                            <div>
                                <p className="text-sm font-medium">
                                    آمادگی فروشگاه
                                </p>
                                <p className="mt-1 text-xs text-muted-foreground">
                                    {completedSteps.toLocaleString('fa-IR')} از{' '}
                                    {actionSteps.length.toLocaleString('fa-IR')}{' '}
                                    کار اصلی انجام شده
                                </p>
                            </div>
                            <Sparkles className="size-5 text-primary" />
                        </div>
                        <div className="mt-4 h-2 overflow-hidden rounded-full bg-muted">
                            <div
                                className="h-full rounded-full bg-primary transition-all"
                                style={{
                                    width: `${Math.round(
                                        (completedSteps /
                                            Math.max(actionSteps.length, 1)) *
                                            100,
                                    )}%`,
                                }}
                            />
                        </div>
                        <p className="mt-4 text-xs leading-6 text-muted-foreground">
                            این شاخص فقط مسیر راه‌اندازی را نشان می‌دهد؛
                            سفارش‌ها و تحویل‌های روزانه در کارت‌های پایین قابل
                            پیگیری هستند.
                        </p>
                    </div>
                </div>
            </section>

            <section aria-label="شاخص‌های اصلی فروشگاه">
                <div className="grid grid-cols-2 gap-3 lg:grid-cols-4">
                    <OverviewStat
                        icon={BadgeCheck}
                        label="پیشنهاد فعال"
                        value={stats.rewardDefinitions}
                        hint="همه پیشنهادهای ثبت‌شده"
                    />
                    <OverviewStat
                        icon={ReceiptText}
                        label="منتظر تحویل"
                        value={stats.pendingRedemptions}
                        hint="کدهای مشتری که اقدام می‌خواهند"
                        emphasis={stats.pendingRedemptions > 0}
                    />
                    <OverviewStat
                        icon={PackageCheck}
                        label="مانده قابل تحویل"
                        value={stats.remainingInventory}
                        hint="موجودی فعلی پاداش‌ها"
                    />
                    <OverviewStat
                        icon={ShoppingCart}
                        label="خرید منتسب"
                        value={stats.confirmedPurchases}
                        hint="خریدهای ثبت‌شده از کمپین"
                    />
                </div>
            </section>

            <section className="grid gap-4 xl:grid-cols-[1.2fr_0.8fr]">
                <div className="rounded-xl border border-sidebar-border/70 bg-background p-4">
                    <div className="flex items-start justify-between gap-3">
                        <div>
                            <p className="text-xs text-muted-foreground">
                                راهنمای شروع
                            </p>
                            <h2 className="mt-1 font-semibold">
                                مسیر کار مدیر فروشگاه
                            </h2>
                        </div>
                        <span className="rounded-full bg-muted px-3 py-1 text-xs">
                            {completedSteps.toLocaleString('fa-IR')} از{' '}
                            {actionSteps.length.toLocaleString('fa-IR')}
                        </span>
                    </div>
                    <div className="mt-4 grid gap-2">
                        {actionSteps.map((step, index) => (
                            <button
                                key={step.title}
                                type="button"
                                onClick={() => onNavigate(step.section)}
                                className="flex w-full items-start gap-3 rounded-lg border border-transparent p-3 text-right transition hover:border-primary/20 hover:bg-muted/40"
                            >
                                <span
                                    className={`mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full text-xs font-semibold ${
                                        step.complete
                                            ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200'
                                            : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200'
                                    }`}
                                >
                                    {step.complete ? (
                                        <CheckCircle2 className="size-4" />
                                    ) : (
                                        (index + 1).toLocaleString('fa-IR')
                                    )}
                                </span>
                                <span className="min-w-0 flex-1">
                                    <span className="flex flex-wrap items-center gap-2">
                                        <span className="font-medium">
                                            {step.title}
                                        </span>
                                        <span className="text-[11px] text-muted-foreground">
                                            {step.complete
                                                ? 'انجام شده'
                                                : 'نیازمند اقدام'}
                                        </span>
                                    </span>
                                    <span className="mt-1 block text-xs leading-6 text-muted-foreground">
                                        {step.description}
                                    </span>
                                </span>
                                <ArrowLeft className="mt-2 size-4 shrink-0 text-muted-foreground" />
                            </button>
                        ))}
                    </div>
                </div>

                <div className="grid gap-4">
                    <div className="rounded-xl border border-sidebar-border/70 bg-background p-4">
                        <div className="flex items-center justify-between gap-3">
                            <div className="flex items-center gap-2">
                                {stats.pendingRedemptions > 0 ? (
                                    <CircleAlert className="size-4 text-amber-600" />
                                ) : (
                                    <Gift className="size-4 text-primary" />
                                )}
                                <h2 className="font-semibold">
                                    تحویل پاداش مشتری
                                </h2>
                            </div>
                            <span className="text-xs text-muted-foreground">
                                {stats.pendingRedemptions.toLocaleString(
                                    'fa-IR',
                                )}{' '}
                                در انتظار
                            </span>
                        </div>
                        <p className="mt-3 text-sm leading-7 text-muted-foreground">
                            وقتی مشتری کد مصرف را ارائه کرد، تحویل پاداش و نتیجه
                            خرید را در یک فرم کوتاه ثبت کنید.
                        </p>
                        <Button
                            className="mt-4 w-full"
                            variant={
                                stats.pendingRedemptions > 0
                                    ? 'default'
                                    : 'outline'
                            }
                            onClick={() => onNavigate('redemptions')}
                        >
                            <ReceiptText className="size-4" />
                            ورود کد مصرف
                        </Button>
                    </div>

                    <div className="rounded-xl border border-sidebar-border/70 bg-background p-4">
                        <div className="flex items-center gap-2">
                            <Megaphone className="size-4 text-primary" />
                            <h2 className="font-semibold">تبلیغات فروشگاه</h2>
                        </div>
                        {lastAd ? (
                            <div className="mt-3 rounded-lg bg-muted/40 p-3">
                                <p className="truncate text-sm font-medium">
                                    {lastAd.title}
                                </p>
                                <p className="mt-1 text-xs text-muted-foreground">
                                    {partnerStatusLabels[lastAd.status] ??
                                        lastAd.status}{' '}
                                    ·{' '}
                                    {lastAd.impressionsCount.toLocaleString(
                                        'fa-IR',
                                    )}{' '}
                                    نمایش
                                </p>
                            </div>
                        ) : (
                            <p className="mt-3 text-sm leading-7 text-muted-foreground">
                                هنوز تبلیغی ثبت نشده است. متن، تصویر و زمان‌بندی
                                تبلیغ در صفحه تخصصی تبلیغات مدیریت می‌شود.
                            </p>
                        )}
                        <Button
                            asChild
                            variant="outline"
                            className="mt-4 w-full"
                        >
                            <Link href="/partner/ads">
                                <Eye className="size-4" />
                                مدیریت تبلیغات
                            </Link>
                        </Button>
                    </div>
                </div>
            </section>

            <section className="rounded-xl border border-sidebar-border/70 bg-muted/20 p-4">
                <div className="flex items-center gap-2">
                    <BarChart3 className="size-4 text-primary" />
                    <h2 className="font-semibold">خلاصه نتیجه کمپین</h2>
                </div>
                <div className="mt-4 grid grid-cols-2 gap-3 text-sm md:grid-cols-4">
                    <div>
                        <p className="text-xs text-muted-foreground">
                            مراجعه منتسب
                        </p>
                        <p className="mt-1 font-semibold">
                            {stats.attributedVisits.toLocaleString('fa-IR')}
                        </p>
                    </div>
                    <div>
                        <p className="text-xs text-muted-foreground">
                            تحویل ثبت‌شده
                        </p>
                        <p className="mt-1 font-semibold">
                            {stats.confirmedRedemptions.toLocaleString('fa-IR')}
                        </p>
                    </div>
                    <div>
                        <p className="text-xs text-muted-foreground">
                            فروش منتسب
                        </p>
                        <p className="mt-1 font-semibold">
                            {stats.attributedSalesIrr.toLocaleString('fa-IR')}{' '}
                            ریال
                        </p>
                    </div>
                    <div>
                        <p className="text-xs text-muted-foreground">
                            درخواست تبلیغ
                        </p>
                        <p className="mt-1 font-semibold">
                            {stats.adRequests.toLocaleString('fa-IR')}
                        </p>
                    </div>
                </div>
                <div className="mt-4 flex flex-wrap gap-2 border-t pt-4 text-xs text-muted-foreground">
                    <Store className="size-4" />
                    <span>
                        فروشگاه: {partner.name} · مکان:{' '}
                        {partner.venueName ?? 'ثبت نشده'}
                    </span>
                </div>
            </section>
        </div>
    );
}
