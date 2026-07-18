import { Form, Head, Link, usePage } from '@inertiajs/react';
import type { Megaphone } from 'lucide-react';
import {
    ArrowLeft,
    BadgeCheck,
    Building2,
    CheckCircle2,
    CircleAlert,
    ClipboardCheck,
    Gift,
    QrCode,
    Route,
    Store,
    Trophy,
    UsersRound,
} from 'lucide-react';
import { CampaignContextNav } from '@/components/campaign-context-nav';
import { Button } from '@/components/ui/button';

type CampaignSummary = {
    id: string;
    code: string;
    name: string;
    campaignType: string;
    blueprintCode: string | null;
    status: string;
    startAt: string | null;
    endAt: string | null;
    venue: { id: string; code: string; name: string } | null;
};

type Counts = {
    qrCodes: number;
    missions: number;
    rewards: number;
    approvedRewards: number;
    pendingRewards: number;
    partnerRewardOffers: number;
    treasures: number;
    participants: number;
    readyParticipants: number;
    ads: number;
    displayDevices: number;
};

type BuilderStep = {
    key: string;
    title: string;
    owner: string;
    complete: boolean;
    status: 'complete' | 'needs_action';
    description: string;
    href: string;
};

type RoleTrack = {
    role: string;
    responsibility: string;
    status: string;
    href: string;
};

type Props = {
    campaigns: CampaignSummary[];
    selectedCampaign: CampaignSummary | null;
    counts: Counts;
    readiness: Readiness;
    steps: BuilderStep[];
    roleTracks: RoleTrack[];
};

type ReadinessCheck = {
    key: string;
    label: string;
    detail: string;
    complete: boolean;
    severity: 'blocker' | 'warning';
    actionHref: string;
    actionLabel: string;
};

type Readiness = {
    checks: ReadinessCheck[];
    canActivate: boolean;
    routeReviewedAt: string | null;
    status: 'ready' | 'ready_with_warnings' | 'needs_action';
    blockersCount: number;
    warningsCount: number;
    summary: string;
};

type SharedProps = {
    flash?: { success?: string };
    errors?: Record<string, string>;
    auth: { user: { role?: string } };
};

type MetricItem = [string, number, typeof Megaphone];

type WorkflowAction = {
    title: string;
    description: string;
    href: string;
    icon: typeof Megaphone;
    tone: 'primary' | 'review' | 'neutral';
};

function fa(value: number) {
    return value.toLocaleString('fa-IR');
}

function formatDate(value: string | null) {
    if (!value) {
        return 'ثبت نشده';
    }

    return new Intl.DateTimeFormat('fa-IR', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

function builderUrl(code: string) {
    return `/admin/campaign-builder?campaign=${code}`;
}

export default function CampaignBuilderIndex({
    campaigns,
    selectedCampaign,
    counts,
    readiness,
    steps,
    roleTracks,
}: Props) {
    const { flash, errors, auth } = usePage<SharedProps>().props;
    const completedSteps = steps.filter((step) => step.complete).length;
    const progress =
        steps.length > 0
            ? Math.round((completedSteps / steps.length) * 100)
            : 0;
    const selectedCode = selectedCampaign?.code ?? '';
    const contextHref = (path: string, action?: string) => {
        const params = new URLSearchParams();

        if (selectedCampaign?.code) {
            params.set('campaign', selectedCampaign.code);
        }

        if (selectedCampaign?.blueprintCode) {
            params.set('blueprint', selectedCampaign.blueprintCode);
        }

        if (action) {
            params.set('blueprint_action', action);
        }

        const query = params.toString();

        return `${path}${query ? `?${query}` : ''}`;
    };
    const metrics: MetricItem[] = [
        ['QR', counts.qrCodes, QrCode],
        ['مأموریت', counts.missions, Trophy],
        ['پاداش تاییدشده', counts.approvedRewards, BadgeCheck],
        ['پیشنهاد معلق', counts.pendingRewards, CircleAlert],
        ['عضو و شریک', counts.participants, Store],
    ];

    const workflowActions: WorkflowAction[] = [
        {
            title: 'ثبت QR ورود',
            description: 'نقطه شروع کاربر را به همین کمپین وصل کنید.',
            href: contextHref('/admin/qr-codes'),
            icon: QrCode,
            tone: counts.qrCodes > 0 ? 'neutral' : 'primary',
        },
        {
            title: 'تعریف مأموریت‌ها و پاداش‌ها',
            description: 'مرحله ۳ را برای مأموریت، گنج و پاداش تکمیل کنید.',
            href: contextHref('/admin/missions', 'components'),
            icon: Trophy,
            tone:
                counts.missions > 0 &&
                (counts.approvedRewards > 0 || counts.treasures > 0)
                    ? 'neutral'
                    : 'primary',
        },
        {
            title: 'ارسال فرم پیشنهاد فروشگاه',
            description:
                'فروشگاه یا اسپانسر برای سطوح پاداش همین کمپین پیشنهاد ثبت کند.',
            href: selectedCampaign
                ? `/partner/dashboard?campaign=${selectedCampaign.code}`
                : '/partner/dashboard',
            icon: Store,
            tone: counts.partnerRewardOffers > 0 ? 'neutral' : 'primary',
        },
        {
            title: 'بررسی پاداش‌های معلق',
            description:
                'پیشنهادهای فروشگاه را تایید، رد یا برای اصلاح برگردانید.',
            href: contextHref('/admin/missions', 'reward_review'),
            icon: Gift,
            tone: counts.pendingRewards > 0 ? 'review' : 'neutral',
        },
        {
            title: 'آماده‌سازی اعضا و شرکا',
            description:
                'فروشگاه‌ها، اسپانسرها و نقش‌های اجرایی را آماده کنید.',
            href: contextHref('/admin/campaign-participants', 'participants'),
            icon: UsersRound,
            tone: counts.readyParticipants > 0 ? 'neutral' : 'primary',
        },
        {
            title: 'تایید نقشه عملیات کمپین',
            description:
                'ارتباط QR، مأموریت، مکان، فروشگاه و تبلیغات را نهایی کنید.',
            href: contextHref('/admin/campaign-operations', 'route'),
            icon: Route,
            tone: readiness.routeReviewedAt ? 'neutral' : 'primary',
        },
    ];

    const canMutate =
        auth.user.role === 'admin' || auth.user.role === 'operator';
    const readinessStatusLabel =
        readiness.status === 'ready'
            ? 'آماده فعال‌سازی'
            : readiness.status === 'ready_with_warnings'
              ? 'آماده با هشدار'
              : 'نیازمند تکمیل';
    const readinessStatusClass =
        readiness.status === 'ready'
            ? 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-100'
            : readiness.status === 'ready_with_warnings'
              ? 'border-amber-300 bg-amber-50 text-amber-900 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-100'
              : 'border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-900/60 dark:bg-rose-950/30 dark:text-rose-100';

    return (
        <>
            <Head title="ساخت کمپین" />
            <div
                dir="rtl"
                className="flex h-full flex-1 flex-col gap-5 overflow-x-auto p-4"
            >
                <header className="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p className="text-sm text-muted-foreground">
                            مرکز واحد تکمیل کمپین بدون حذف منوهای تخصصی
                        </p>
                        <h1 className="mt-1 text-2xl font-semibold">
                            ساخت کمپین
                        </h1>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        <Button asChild variant="outline">
                            <Link href="/admin/campaigns">کمپین‌ها</Link>
                        </Button>
                        {selectedCampaign ? (
                            <Button asChild>
                                <Link
                                    href={`/admin/campaign-operations?campaign=${selectedCampaign.code}`}
                                >
                                    نقشه عملیات
                                    <ArrowLeft className="size-4" />
                                </Link>
                            </Button>
                        ) : null}
                    </div>
                </header>

                <section className="rounded-lg border border-border/80 bg-card/80 p-4 shadow-sm">
                    <div className="grid gap-4 lg:grid-cols-[1fr_0.9fr]">
                        <div>
                            <label
                                htmlFor="campaign-builder-select"
                                className="text-sm font-medium"
                            >
                                کمپین در حال ساخت
                            </label>
                            <select
                                id="campaign-builder-select"
                                value={selectedCode}
                                onChange={(event) => {
                                    if (event.target.value) {
                                        window.location.href = builderUrl(
                                            event.target.value,
                                        );
                                    }
                                }}
                                className="mt-2 h-10 w-full rounded-md border border-input bg-background px-3 text-sm"
                            >
                                {campaigns.length === 0 ? (
                                    <option value="">
                                        کمپینی ثبت نشده است
                                    </option>
                                ) : null}
                                {campaigns.map((campaign) => (
                                    <option
                                        key={campaign.id}
                                        value={campaign.code}
                                    >
                                        {campaign.name}
                                    </option>
                                ))}
                            </select>

                            {selectedCampaign ? (
                                <div className="mt-4 grid gap-3 text-sm md:grid-cols-3">
                                    <div className="rounded-md bg-muted/45 p-3">
                                        <p className="text-xs text-muted-foreground">
                                            کد کمپین
                                        </p>
                                        <p
                                            className="mt-1 font-medium"
                                            dir="ltr"
                                        >
                                            {selectedCampaign.code}
                                        </p>
                                    </div>
                                    <div className="rounded-md bg-muted/45 p-3">
                                        <p className="text-xs text-muted-foreground">
                                            مکان
                                        </p>
                                        <p className="mt-1 font-medium">
                                            {selectedCampaign.venue?.name ??
                                                'ثبت نشده'}
                                        </p>
                                    </div>
                                    <div className="rounded-md bg-muted/45 p-3">
                                        <p className="text-xs text-muted-foreground">
                                            اعتبار
                                        </p>
                                        <p className="mt-1 font-medium">
                                            {formatDate(
                                                selectedCampaign.startAt,
                                            )}{' '}
                                            تا{' '}
                                            {formatDate(selectedCampaign.endAt)}
                                        </p>
                                    </div>
                                </div>
                            ) : (
                                <div className="mt-4 rounded-md bg-muted/45 p-4 text-sm text-muted-foreground">
                                    ابتدا از گنجینه الگوها یا صفحه کمپین‌ها یک
                                    کمپین بسازید.
                                </div>
                            )}
                        </div>

                        <div className="rounded-lg border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                            <div className="flex items-center justify-between gap-3">
                                <div>
                                    <p className="text-sm text-muted-foreground">
                                        پیشرفت تکمیل
                                    </p>
                                    <p className="mt-1 text-2xl font-semibold">
                                        {fa(progress)}٪
                                    </p>
                                </div>
                                <BadgeCheck className="size-8 text-primary" />
                            </div>
                            <div className="mt-4 h-2 overflow-hidden rounded-full bg-muted">
                                <div
                                    className="h-full rounded-full bg-primary"
                                    style={{ width: `${progress}%` }}
                                />
                            </div>
                            <p className="mt-3 text-xs text-muted-foreground">
                                {fa(completedSteps)} مرحله از {fa(steps.length)}{' '}
                                مرحله تکمیل شده است.
                            </p>
                        </div>
                    </div>
                </section>

                {selectedCampaign ? (
                    <CampaignContextNav campaign={selectedCampaign} />
                ) : null}

                <section className="grid gap-3 text-sm md:grid-cols-3 xl:grid-cols-5">
                    {metrics.map(([label, value, MetricIcon]) => {
                        return (
                            <div
                                key={String(label)}
                                className="rounded-lg border border-border/80 bg-card/80 px-3 py-2 shadow-sm"
                            >
                                <div className="flex items-center gap-2 text-muted-foreground">
                                    <MetricIcon className="size-4" />
                                    <p>{label}</p>
                                </div>
                                <p className="mt-1 font-semibold">
                                    {fa(Number(value))}
                                </p>
                            </div>
                        );
                    })}
                </section>

                <section className="exploria-panel">
                    <div className="flex flex-col gap-1 border-b border-border/70 px-4 py-3 dark:border-sidebar-border">
                        <h2 className="font-semibold">
                            اقدام‌های قابل انجام همین کمپین
                        </h2>
                        <p className="text-sm text-muted-foreground">
                            برای تکمیل هر مرحله، از همینجا مستقیم به صفحه
                            عملیاتی مرتبط بروید.
                        </p>
                    </div>
                    <div className="grid gap-3 p-4 md:grid-cols-2 xl:grid-cols-3">
                        {workflowActions.map((action) => {
                            const ActionIcon = action.icon;
                            const isPrimary = action.tone === 'primary';
                            const isReview = action.tone === 'review';

                            return (
                                <article
                                    key={action.title}
                                    className={[
                                        'rounded-lg border p-4 shadow-sm',
                                        isPrimary
                                            ? 'border-primary/35 bg-primary/5'
                                            : '',
                                        isReview
                                            ? 'border-amber-300 bg-amber-50 dark:border-amber-900/60 dark:bg-amber-950/30'
                                            : '',
                                        !isPrimary && !isReview
                                            ? 'border-border/80 bg-card/75'
                                            : '',
                                    ].join(' ')}
                                >
                                    <div className="flex items-start gap-3">
                                        <span className="flex size-9 shrink-0 items-center justify-center rounded-full bg-background text-primary shadow-sm">
                                            <ActionIcon className="size-4" />
                                        </span>
                                        <div className="min-w-0">
                                            <h3 className="font-semibold">
                                                {action.title}
                                            </h3>
                                            <p className="mt-1 text-sm leading-6 text-muted-foreground">
                                                {action.description}
                                            </p>
                                        </div>
                                    </div>
                                    <Button
                                        asChild
                                        variant={
                                            isPrimary || isReview
                                                ? 'default'
                                                : 'outline'
                                        }
                                        size="sm"
                                        className="mt-4"
                                    >
                                        <Link href={action.href}>
                                            رفتن به این اقدام
                                            <ArrowLeft className="size-4" />
                                        </Link>
                                    </Button>
                                </article>
                            );
                        })}
                    </div>
                </section>

                <section className="grid gap-4 xl:grid-cols-[1fr_0.9fr]">
                    <div className="exploria-panel">
                        <div className="border-b border-border/70 px-4 py-3 dark:border-sidebar-border">
                            <h2 className="font-semibold">تقسیم مسئولیت‌ها</h2>
                        </div>
                        <div className="grid gap-3 p-4">
                            {roleTracks.map((track) => (
                                <article
                                    key={track.role}
                                    className="rounded-lg border border-border/80 bg-card/75 p-3 shadow-sm"
                                >
                                    <div className="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                                        <div>
                                            <h3 className="font-semibold">
                                                {track.role}
                                            </h3>
                                            <p className="mt-1 text-sm leading-6 text-muted-foreground">
                                                {track.responsibility}
                                            </p>
                                        </div>
                                        <span className="rounded-full bg-muted px-3 py-1 text-xs text-muted-foreground">
                                            {track.status}
                                        </span>
                                    </div>
                                    <Button
                                        asChild
                                        variant="outline"
                                        size="sm"
                                        className="mt-3"
                                    >
                                        <Link href={track.href}>
                                            رفتن به پنل مرتبط
                                        </Link>
                                    </Button>
                                </article>
                            ))}
                        </div>
                    </div>

                    <div className="exploria-panel">
                        <div className="border-b border-border/70 px-4 py-3 dark:border-sidebar-border">
                            <h2 className="font-semibold">
                                کنترل‌های فایل کمپین
                            </h2>
                        </div>
                        <div className="grid gap-3 p-4 text-sm">
                            <Button asChild variant="outline">
                                <Link
                                    href={
                                        selectedCampaign
                                            ? `/admin/campaigns?campaign=${selectedCampaign.code}`
                                            : '/admin/campaigns'
                                    }
                                >
                                    ویرایش اطلاعات پایه
                                </Link>
                            </Button>
                            <Button asChild variant="outline">
                                <Link
                                    href={
                                        selectedCampaign
                                            ? `/admin/campaign-builder?campaign=${selectedCampaign.code}`
                                            : '/admin/campaign-builder'
                                    }
                                >
                                    ادامه تکمیل
                                </Link>
                            </Button>
                            <div className="rounded-lg border border-dashed border-amber-300 bg-amber-50 p-3 text-amber-900 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-100">
                                حذف یا آرشیو کمپین در این نسخه عمداً خودکار نشده
                                است؛ چون عملیات پرریسک است و باید با تأیید رسمی
                                و قواعد حفظ داده انجام شود.
                            </div>
                            <div className="rounded-lg bg-muted/45 p-3 text-muted-foreground">
                                منوهای مستقل مثل مکان‌ها، شرکا، QR و پنل فروشگاه
                                حفظ شده‌اند. ساخت کمپین فقط آنها را برای کمپین
                                انتخاب‌شده کنار هم می‌آورد.
                            </div>
                        </div>
                    </div>
                </section>

                {flash?.success ? (
                    <section className="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-100">
                        {flash.success}
                    </section>
                ) : null}
                {errors?.campaign ? (
                    <section className="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-900/60 dark:bg-rose-950/30 dark:text-rose-100">
                        {errors.campaign}
                    </section>
                ) : null}

                <section className="rounded-lg border border-sidebar-border/70 bg-background p-4 text-sm dark:border-sidebar-border">
                    <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                        <div className="flex items-center gap-2">
                            <Building2 className="size-4 text-muted-foreground" />
                            <div>
                                <h2 className="font-semibold">
                                    چک نهایی و فعال‌سازی مرحله ۶
                                </h2>
                                <p className="mt-1 text-xs text-muted-foreground">
                                    {readiness.summary}
                                </p>
                            </div>
                        </div>
                        <span
                            className={`w-fit rounded-full border px-3 py-1 text-xs font-medium ${readinessStatusClass}`}
                        >
                            {readinessStatusLabel}
                        </span>
                    </div>
                    <div className="mt-4 grid gap-3 lg:grid-cols-[1fr_260px]">
                        <div className="grid gap-3 md:grid-cols-2">
                            {readiness.checks.map((check) => {
                                const needsAction = !check.complete;
                                const isWarning = check.severity === 'warning';

                                return (
                                    <article
                                        key={check.key}
                                        className={[
                                            'rounded-lg border p-3 shadow-sm',
                                            check.complete
                                                ? 'border-emerald-200 bg-emerald-50/70 dark:border-emerald-900/60 dark:bg-emerald-950/20'
                                                : '',
                                            needsAction && isWarning
                                                ? 'border-amber-300 bg-amber-50 dark:border-amber-900/60 dark:bg-amber-950/30'
                                                : '',
                                            needsAction && !isWarning
                                                ? 'border-rose-200 bg-rose-50 dark:border-rose-900/60 dark:bg-rose-950/30'
                                                : '',
                                        ].join(' ')}
                                    >
                                        <div className="flex items-start gap-2">
                                            {check.complete ? (
                                                <CheckCircle2 className="mt-0.5 size-4 shrink-0 text-emerald-600" />
                                            ) : (
                                                <CircleAlert
                                                    className={
                                                        isWarning
                                                            ? 'mt-0.5 size-4 shrink-0 text-amber-600'
                                                            : 'mt-0.5 size-4 shrink-0 text-rose-600'
                                                    }
                                                />
                                            )}
                                            <div className="min-w-0">
                                                <h3 className="font-medium">
                                                    {check.label}
                                                </h3>
                                                <p className="mt-1 text-xs leading-5 text-muted-foreground">
                                                    {check.detail}
                                                </p>
                                                {!check.complete ? (
                                                    <Button
                                                        asChild
                                                        variant={
                                                            isWarning
                                                                ? 'outline'
                                                                : 'default'
                                                        }
                                                        size="sm"
                                                        className="mt-3"
                                                    >
                                                        <Link
                                                            href={
                                                                check.actionHref
                                                            }
                                                        >
                                                            {check.actionLabel}
                                                            <ArrowLeft className="size-4" />
                                                        </Link>
                                                    </Button>
                                                ) : null}
                                            </div>
                                        </div>
                                    </article>
                                );
                            })}
                        </div>
                        <div className="flex min-w-[220px] flex-col justify-between gap-3 rounded-lg border border-border/80 bg-card/75 p-3 shadow-sm">
                            <div>
                                <p className="text-sm font-semibold">
                                    {readinessStatusLabel}
                                </p>
                                <p className="mt-1 text-xs text-muted-foreground">
                                    وضعیت فعلی:{' '}
                                    {selectedCampaign?.status === 'active'
                                        ? 'فعال'
                                        : 'پیش از اجرا'}
                                </p>
                                <div className="mt-3 grid grid-cols-2 gap-2 text-xs">
                                    <div className="rounded-md bg-muted/50 p-2">
                                        <p className="text-muted-foreground">
                                            موارد ضروری
                                        </p>
                                        <p className="mt-1 font-semibold">
                                            {fa(readiness.blockersCount)}{' '}
                                            باقی‌مانده
                                        </p>
                                    </div>
                                    <div className="rounded-md bg-muted/50 p-2">
                                        <p className="text-muted-foreground">
                                            هشدارها
                                        </p>
                                        <p className="mt-1 font-semibold">
                                            {fa(readiness.warningsCount)}{' '}
                                            باقی‌مانده
                                        </p>
                                    </div>
                                </div>
                            </div>
                            {selectedCampaign && canMutate ? (
                                <Form
                                    action={`/admin/campaign-builder/${selectedCampaign.code}/activate`}
                                    method="post"
                                    options={{ preserveScroll: true }}
                                >
                                    {({ processing }) => (
                                        <Button
                                            disabled={
                                                processing ||
                                                !readiness.canActivate ||
                                                selectedCampaign.status ===
                                                    'active'
                                            }
                                            className="w-full"
                                        >
                                            <ClipboardCheck className="size-4" />
                                            فعال‌سازی کمپین
                                        </Button>
                                    )}
                                </Form>
                            ) : null}
                        </div>
                    </div>
                </section>
            </div>
        </>
    );
}

CampaignBuilderIndex.layout = {
    title: 'ساخت کمپین',
    breadcrumbs: [
        {
            title: 'ساخت کمپین',
            href: '/admin/campaign-builder',
        },
    ],
};
