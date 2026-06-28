import { Form, Head, Link, usePage } from '@inertiajs/react';
import {
    CalendarClock,
    Megaphone,
    Plus,
    QrCode,
    SquareActivity,
} from 'lucide-react';
import InputError from '@/components/input-error';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';


type RewardBasketTier = {
    level: string;
    items: string[];
};

type SelectedBlueprint = {
    code: string;
    title: string;
    missionGoal: string;
    evidenceType: string;
    userSteps: string[];
    navigationHint: string;
    points: { base: number; bonus: string };
    rewardIdeas: string[];
    stakeholders: string[];
    connectedSurfaces: string[];
    rewardBasket: RewardBasketTier[];
    nextBuildAction: string;
};

type RegistryEntity = {
    id: string;
    code: string;
    name: string;
};

type CampaignItem = {
    id: string;
    code: string;
    name: string;
    campaignType: string;
    blueprintCode: string | null;
    status: string;
    startAt: string | null;
    endAt: string | null;
    qrCodesCount: number;
    visitsCount: number;
    venue: RegistryEntity | null;
};

type Props = {
    campaigns: CampaignItem[];
    venueOptions: RegistryEntity[];
    selectedBlueprint: SelectedBlueprint | null;
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

function canMutate(role?: string) {
    return role === 'admin' || role === 'operator';
}

export default function CampaignRegistryIndex({
    campaigns,
    venueOptions,
    selectedBlueprint,
}: Props) {
    const { flash, auth } = usePage<SharedProps>().props;
    const activeCount = campaigns.filter(
        (campaign) => campaign.status === 'active',
    ).length;

    return (
        <>
            <Head title="مدیریت کمپین‌ها" />
            <div
                dir="rtl"
                className="flex h-full flex-1 flex-col gap-5 overflow-x-auto p-4"
            >
                <header className="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p className="text-sm text-muted-foreground">
                            هسته عملیاتی فاز ۱
                        </p>
                        <h1 className="mt-1 text-2xl font-semibold">
                            مدیریت کمپین‌ها
                        </h1>
                    </div>
                    <div className="grid grid-cols-3 gap-3 text-sm">
                        <div className="rounded-lg border border-border/80 bg-card/80 px-3 py-2 shadow-sm">
                            <p className="text-muted-foreground">کل کمپین‌ها</p>
                            <p className="mt-1 font-semibold">
                                {campaigns.length.toLocaleString('fa-IR')}
                            </p>
                        </div>
                        <div className="rounded-lg border border-border/80 bg-card/80 px-3 py-2 shadow-sm">
                            <p className="text-muted-foreground">فعال</p>
                            <p className="mt-1 font-semibold">
                                {activeCount.toLocaleString('fa-IR')}
                            </p>
                        </div>
                        <div className="rounded-lg border border-border/80 bg-card/80 px-3 py-2 shadow-sm">
                            <p className="text-muted-foreground">QR متصل</p>
                            <p className="mt-1 font-semibold">
                                {campaigns
                                    .reduce(
                                        (sum, campaign) =>
                                            sum + campaign.qrCodesCount,
                                        0,
                                    )
                                    .toLocaleString('fa-IR')}
                            </p>
                        </div>
                    </div>
                </header>


                {selectedBlueprint ? (
                    <section className="rounded-lg border border-primary/25 bg-primary/5 p-4 text-sm shadow-sm">
                        <div className="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                            <div>
                                <p className="text-xs text-muted-foreground">الگوی مرجع برای ساخت کمپین</p>
                                <h2 className="mt-1 text-lg font-semibold">{selectedBlueprint.title}</h2>
                                <p className="mt-1 text-muted-foreground">این الگو باید اول به کمپین تبدیل شود؛ بعد از ثبت کمپین، QR، اعضا، مأموریت، پاداش و نقشه عملیات باید تابع همین کمپین باشند.</p>
                            </div>
                            <span className="rounded-full bg-background px-3 py-1 text-xs" dir="ltr">{selectedBlueprint.code}</span>
                        </div>
                        <div className="mt-4 grid gap-3 lg:grid-cols-3">
                            <div className="rounded-lg bg-background/75 p-3"><p className="font-medium">هدف کمپین</p><p className="mt-1 text-muted-foreground">{selectedBlueprint.missionGoal}</p></div>
                            <div className="rounded-lg bg-background/75 p-3"><p className="font-medium">مسیر و ناوبری</p><p className="mt-1 text-muted-foreground">{selectedBlueprint.navigationHint}</p></div>
                            <div className="rounded-lg bg-background/75 p-3"><p className="font-medium">بخش‌های تابع کمپین</p><div className="mt-2 flex flex-wrap gap-2">{selectedBlueprint.connectedSurfaces.slice(0, 5).map((item) => <span key={item} className="rounded-full bg-muted px-2 py-1 text-xs">{item}</span>)}</div></div>
                        </div>
                    </section>
                ) : null}

                {flash?.success ? (
                    <Alert>
                        <AlertDescription>{flash.success}</AlertDescription>
                    </Alert>
                ) : null}

                {canMutate(auth.user.role) ? (
                    <section className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
                        <div className="mb-4 flex items-center gap-2">
                            <Plus className="size-4 text-muted-foreground" />
                            <h2 className="font-semibold">ثبت کمپین جدید</h2>
                        </div>
                        <Form
                            action="/admin/campaigns"
                            method="post"
                            options={{ preserveScroll: true }}
                            className="grid gap-4 md:grid-cols-6"
                        >
                            {({ processing, errors }) => (
                                <>
                                    {selectedBlueprint ? <input type="hidden" name="blueprint_code" value={selectedBlueprint.code} /> : null}
                                    <div className="grid gap-2 md:col-span-2">
                                        <Label htmlFor="venue_id">مکان</Label>
                                        <select
                                            id="venue_id"
                                            name="venue_id"
                                            required
                                            defaultValue={
                                                venueOptions[0]?.id ?? ''
                                            }
                                            className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                        >
                                            {venueOptions.map((venue) => (
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
                                        <Label htmlFor="name">نام کمپین</Label>
                                        <Input
                                            id="name"
                                            name="name"
                                            required
                                            placeholder="مثلا کمپین گنج تابستان"
                                        />
                                        <InputError message={errors.name} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="code">کد</Label>
                                        <Input
                                            id="code"
                                            name="code"
                                            required
                                            dir="ltr"
                                            placeholder="summer-treasure"
                                            defaultValue={selectedBlueprint?.code ? `${selectedBlueprint.code}-campaign` : ''}
                                        />
                                        <InputError message={errors.code} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="campaign_type">
                                            نوع
                                        </Label>
                                        <Input
                                            id="campaign_type"
                                            name="campaign_type"
                                            required
                                            dir="ltr"
                                            defaultValue={selectedBlueprint ? 'blueprint_campaign' : 'pilot_visit'}
                                        />
                                        <InputError
                                            message={errors.campaign_type}
                                        />
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
                                    <div className="grid gap-2">
                                        <Label htmlFor="start_at">شروع</Label>
                                        <Input
                                            id="start_at"
                                            type="datetime-local"
                                            name="start_at"
                                        />
                                        <InputError message={errors.start_at} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="end_at">پایان</Label>
                                        <Input
                                            id="end_at"
                                            type="datetime-local"
                                            name="end_at"
                                        />
                                        <InputError message={errors.end_at} />
                                    </div>
                                    <div className="flex items-end md:col-span-2">
                                        <Button
                                            disabled={processing}
                                            className="w-full"
                                        >
                                            <Plus className="size-4" />
                                            ثبت کمپین
                                        </Button>
                                    </div>
                                </>
                            )}
                        </Form>
                    </section>
                ) : null}

                <section className="exploria-panel">
                    <div className="grid min-w-[860px] grid-cols-[1.25fr_1fr_1fr_0.8fr_1fr_0.8fr] gap-3 border-b border-border/70 px-4 py-3 text-xs font-medium text-muted-foreground dark:border-sidebar-border">
                        <span>کمپین</span>
                        <span>مکان</span>
                        <span>نوع</span>
                        <span>QR</span>
                        <span>اعتبار</span>
                        <span>بازدید</span>
                    </div>

                    {campaigns.length === 0 ? (
                        <div className="p-8 text-center text-sm text-muted-foreground">
                            هنوز کمپینی ثبت نشده است.
                        </div>
                    ) : (
                        <div className="min-w-[860px] divide-y divide-border/70">
                            {campaigns.map((campaign) => (
                                <article
                                    key={campaign.id}
                                    className="grid grid-cols-[1.25fr_1fr_1fr_0.8fr_1fr_0.8fr] items-center gap-3 px-4 py-3 text-sm"
                                >
                                    <div className="min-w-0">
                                        <div className="flex items-center gap-2">
                                            <Megaphone className="size-4 shrink-0 text-muted-foreground" />
                                            <span className="truncate font-medium">
                                                {campaign.name}
                                            </span>
                                        </div>
                                        <div className="mt-2 flex items-center gap-2">
                                            <span
                                                className={`inline-flex rounded-full px-2.5 py-1 text-xs font-medium ${
                                                    statusClasses[
                                                        campaign.status
                                                    ] ?? statusClasses.inactive
                                                }`}
                                            >
                                                {statusLabels[
                                                    campaign.status
                                                ] ?? campaign.status}
                                            </span>
                                            <span
                                                className="truncate text-xs text-muted-foreground"
                                                dir="ltr"
                                            >
                                                {campaign.code}
                                            </span>
                                            {campaign.blueprintCode ? (
                                                <span className="rounded-full bg-primary/10 px-2 py-0.5 text-xs text-primary" dir="ltr">
                                                    {campaign.blueprintCode}
                                                </span>
                                            ) : null}
                                        </div>
                                        <div className="mt-3 flex flex-wrap gap-2 text-xs">
                                            {[
                                                ['QR', `/admin/qr-codes?campaign=${campaign.code}`],
                                                ['مأموریت و پاداش', `/admin/missions?campaign=${campaign.code}`],
                                                ['اعضای کمپین', `/admin/campaign-participants?campaign=${campaign.code}`],
                                                ['نقشه عملیات', `/admin/campaign-operations?campaign=${campaign.code}`],
                                            ].map(([label, href]) => (
                                                <Link
                                                    key={href}
                                                    href={href}
                                                    className="rounded-full border border-primary/20 bg-primary/5 px-2.5 py-1 text-primary transition hover:bg-primary/10"
                                                >
                                                    {label}
                                                </Link>
                                            ))}
                                        </div>
                                    </div>
                                    <div className="min-w-0">
                                        <p className="truncate">
                                            {campaign.venue?.name ??
                                                'بدون مکان'}
                                        </p>
                                        <p
                                            className="mt-1 truncate text-xs text-muted-foreground"
                                            dir="ltr"
                                        >
                                            {campaign.venue?.code ?? '-'}
                                        </p>
                                    </div>
                                    <span dir="ltr">
                                        {campaign.campaignType}
                                    </span>
                                    <div className="flex items-center gap-2">
                                        <QrCode className="size-4 text-muted-foreground" />
                                        <span>
                                            {campaign.qrCodesCount.toLocaleString(
                                                'fa-IR',
                                            )}
                                        </span>
                                    </div>
                                    <div className="min-w-0">
                                        <div className="flex items-center gap-2 text-xs">
                                            <CalendarClock className="size-4 shrink-0 text-muted-foreground" />
                                            <span className="truncate">
                                                {formatDate(campaign.startAt)}
                                            </span>
                                        </div>
                                        <p className="mt-1 truncate text-xs text-muted-foreground">
                                            تا {formatDate(campaign.endAt)}
                                        </p>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <SquareActivity className="size-4 text-muted-foreground" />
                                        <span>
                                            {campaign.visitsCount.toLocaleString(
                                                'fa-IR',
                                            )}
                                        </span>
                                    </div>
                                </article>
                            ))}
                        </div>
                    )}
                </section>
            </div>
        </>
    );
}

CampaignRegistryIndex.layout = {
    breadcrumbs: [
        {
            title: 'مدیریت کمپین‌ها',
            href: '/admin/campaigns',
        },
    ],
};
