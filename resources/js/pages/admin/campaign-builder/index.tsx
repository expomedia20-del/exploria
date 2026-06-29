import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    BadgeCheck,
    Building2,
    CheckCircle2,
    CircleAlert,
    ClipboardCheck,
    Megaphone,
    QrCode,
    Route,
    Store,
    Trophy,
    UsersRound,
} from 'lucide-react';
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
    steps: BuilderStep[];
    roleTracks: RoleTrack[];
};

type MetricItem = [string, number, typeof Megaphone];

const stepIcons: Record<string, typeof Megaphone> = {
    setup: Megaphone,
    qr: QrCode,
    components: Trophy,
    partners: UsersRound,
    route: Route,
    review: ClipboardCheck,
};

function fa(value: number) {
    return value.toLocaleString('fa-IR');
}

function formatDate(value: string | null) {
    if (!value) return 'ثبت نشده';

    return new Intl.DateTimeFormat('fa-IR', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

function builderUrl(code: string) {
    return `/admin/campaign-builder?campaign=${code}`;
}

function StepStatus({ complete }: { complete: boolean }) {
    return complete ? (
        <span className="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">
            <CheckCircle2 className="size-3.5" />
            تکمیل شده
        </span>
    ) : (
        <span className="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs text-amber-800 dark:bg-amber-950 dark:text-amber-200">
            <CircleAlert className="size-3.5" />
            نیازمند اقدام
        </span>
    );
}

export default function CampaignBuilderIndex({
    campaigns,
    selectedCampaign,
    counts,
    steps,
    roleTracks,
}: Props) {
    const completedSteps = steps.filter((step) => step.complete).length;
    const progress = steps.length > 0 ? Math.round((completedSteps / steps.length) * 100) : 0;
    const selectedCode = selectedCampaign?.code ?? '';
    const metrics: MetricItem[] = [
        ['QR', counts.qrCodes, QrCode],
        ['مأموریت', counts.missions, Trophy],
        ['پاداش و گنج', counts.rewards + counts.treasures, BadgeCheck],
        ['عضو و شریک', counts.participants, Store],
    ];

    return (
        <>
            <Head title="کارگاه ساخت کمپین" />
            <div dir="rtl" className="flex h-full flex-1 flex-col gap-5 overflow-x-auto p-4">
                <header className="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p className="text-sm text-muted-foreground">مرکز واحد تکمیل کمپین بدون حذف منوهای تخصصی</p>
                        <h1 className="mt-1 text-2xl font-semibold">کارگاه ساخت کمپین</h1>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        <Button asChild variant="outline">
                            <Link href="/admin/campaigns">مدیریت کمپین‌ها</Link>
                        </Button>
                        {selectedCampaign ? (
                            <Button asChild>
                                <Link href={`/admin/campaign-operations?campaign=${selectedCampaign.code}`}>
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
                            <label htmlFor="campaign-builder-select" className="text-sm font-medium">
                                کمپین در حال ساخت
                            </label>
                            <select
                                id="campaign-builder-select"
                                value={selectedCode}
                                onChange={(event) => {
                                    if (event.target.value) {
                                        window.location.href = builderUrl(event.target.value);
                                    }
                                }}
                                className="mt-2 h-10 w-full rounded-md border border-input bg-background px-3 text-sm"
                            >
                                {campaigns.length === 0 ? (
                                    <option value="">کمپینی ثبت نشده است</option>
                                ) : null}
                                {campaigns.map((campaign) => (
                                    <option key={campaign.id} value={campaign.code}>
                                        {campaign.name}
                                    </option>
                                ))}
                            </select>

                            {selectedCampaign ? (
                                <div className="mt-4 grid gap-3 text-sm md:grid-cols-3">
                                    <div className="rounded-md bg-muted/45 p-3">
                                        <p className="text-xs text-muted-foreground">کد کمپین</p>
                                        <p className="mt-1 font-medium" dir="ltr">{selectedCampaign.code}</p>
                                    </div>
                                    <div className="rounded-md bg-muted/45 p-3">
                                        <p className="text-xs text-muted-foreground">مکان</p>
                                        <p className="mt-1 font-medium">{selectedCampaign.venue?.name ?? 'ثبت نشده'}</p>
                                    </div>
                                    <div className="rounded-md bg-muted/45 p-3">
                                        <p className="text-xs text-muted-foreground">اعتبار</p>
                                        <p className="mt-1 font-medium">{formatDate(selectedCampaign.startAt)} تا {formatDate(selectedCampaign.endAt)}</p>
                                    </div>
                                </div>
                            ) : (
                                <div className="mt-4 rounded-md bg-muted/45 p-4 text-sm text-muted-foreground">
                                    ابتدا از گنجینه یا صفحه مدیریت کمپین‌ها یک کمپین بسازید.
                                </div>
                            )}
                        </div>

                        <div className="rounded-lg border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                            <div className="flex items-center justify-between gap-3">
                                <div>
                                    <p className="text-sm text-muted-foreground">پیشرفت تکمیل</p>
                                    <p className="mt-1 text-2xl font-semibold">{fa(progress)}٪</p>
                                </div>
                                <BadgeCheck className="size-8 text-primary" />
                            </div>
                            <div className="mt-4 h-2 overflow-hidden rounded-full bg-muted">
                                <div className="h-full rounded-full bg-primary" style={{ width: `${progress}%` }} />
                            </div>
                            <p className="mt-3 text-xs text-muted-foreground">
                                {fa(completedSteps)} مرحله از {fa(steps.length)} مرحله تکمیل شده است.
                            </p>
                        </div>
                    </div>
                </section>

                <section className="grid gap-3 text-sm md:grid-cols-4">
                    {metrics.map(([label, value, MetricIcon]) => {
                        return (
                            <div key={String(label)} className="rounded-lg border border-border/80 bg-card/80 px-3 py-2 shadow-sm">
                                <div className="flex items-center gap-2 text-muted-foreground">
                                    <MetricIcon className="size-4" />
                                    <p>{label}</p>
                                </div>
                                <p className="mt-1 font-semibold">{fa(Number(value))}</p>
                            </div>
                        );
                    })}
                </section>

                <section className="exploria-panel">
                    <div className="border-b border-border/70 px-4 py-3 dark:border-sidebar-border">
                        <h2 className="font-semibold">مراحل تکمیل کمپین</h2>
                    </div>
                    <div className="grid gap-3 p-4 lg:grid-cols-2">
                        {steps.map((step, index) => {
                            const Icon = stepIcons[step.key] ?? ClipboardCheck;

                            return (
                                <article key={step.key} className="rounded-lg border border-border/80 bg-card/75 p-4 shadow-sm">
                                    <div className="flex items-start justify-between gap-3">
                                        <div className="flex items-start gap-3">
                                            <span className="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                                                <Icon className="size-4" />
                                            </span>
                                            <div>
                                                <p className="text-xs text-muted-foreground">مرحله {fa(index + 1)} · {step.owner}</p>
                                                <h3 className="mt-1 font-semibold">{step.title}</h3>
                                            </div>
                                        </div>
                                        <StepStatus complete={step.complete} />
                                    </div>
                                    <p className="mt-3 text-sm leading-6 text-muted-foreground">{step.description}</p>
                                    <Button asChild variant={step.complete ? 'outline' : 'default'} size="sm" className="mt-4">
                                        <Link href={step.href}>
                                            {step.complete ? 'بازبینی مرحله' : 'ادامه تکمیل'}
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
                                <article key={track.role} className="rounded-lg border border-border/80 bg-card/75 p-3 shadow-sm">
                                    <div className="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                                        <div>
                                            <h3 className="font-semibold">{track.role}</h3>
                                            <p className="mt-1 text-sm leading-6 text-muted-foreground">{track.responsibility}</p>
                                        </div>
                                        <span className="rounded-full bg-muted px-3 py-1 text-xs text-muted-foreground">{track.status}</span>
                                    </div>
                                    <Button asChild variant="outline" size="sm" className="mt-3">
                                        <Link href={track.href}>رفتن به پنل مرتبط</Link>
                                    </Button>
                                </article>
                            ))}
                        </div>
                    </div>

                    <div className="exploria-panel">
                        <div className="border-b border-border/70 px-4 py-3 dark:border-sidebar-border">
                            <h2 className="font-semibold">کنترل‌های فایل کمپین</h2>
                        </div>
                        <div className="grid gap-3 p-4 text-sm">
                            <Button asChild variant="outline">
                                <Link href={selectedCampaign ? `/admin/campaigns?campaign=${selectedCampaign.code}` : '/admin/campaigns'}>
                                    ویرایش اطلاعات پایه
                                </Link>
                            </Button>
                            <Button asChild variant="outline">
                                <Link href={selectedCampaign ? `/admin/campaign-builder?campaign=${selectedCampaign.code}` : '/admin/campaign-builder'}>
                                    ادامه تکمیل
                                </Link>
                            </Button>
                            <div className="rounded-lg border border-dashed border-amber-300 bg-amber-50 p-3 text-amber-900 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-100">
                                حذف یا آرشیو کمپین در این نسخه عمداً خودکار نشده است؛ چون عملیات پرریسک است و باید با تأیید رسمی و قواعد حفظ داده انجام شود.
                            </div>
                            <div className="rounded-lg bg-muted/45 p-3 text-muted-foreground">
                                منوهای مستقل مثل مکان‌ها، شرکا، QR و پنل فروشگاه حفظ شده‌اند. کارگاه فقط آنها را برای کمپین انتخاب‌شده کنار هم می‌آورد.
                            </div>
                        </div>
                    </div>
                </section>

                <section className="rounded-lg border border-sidebar-border/70 bg-background p-4 text-sm dark:border-sidebar-border">
                    <div className="flex items-center gap-2">
                        <Building2 className="size-4 text-muted-foreground" />
                        <h2 className="font-semibold">چک نهایی قبل از اجرا</h2>
                    </div>
                    <p className="mt-2 leading-6 text-muted-foreground">
                        کمپین زمانی آماده اجراست که QR، مأموریت، پاداش یا گنج، اعضای مسئول و مسیر عملیاتی برای همان کد کمپین قابل مشاهده باشند. اگر یکی از مراحل زرد است، همان مرحله را از دکمه «ادامه تکمیل» باز کنید.
                    </p>
                </section>
            </div>
        </>
    );
}

CampaignBuilderIndex.layout = {
    title: 'کارگاه ساخت کمپین',
    breadcrumbs: [
        {
            title: 'کارگاه ساخت کمپین',
            href: '/admin/campaign-builder',
        },
    ],
};
