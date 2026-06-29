import { Form, Head, Link, usePage } from '@inertiajs/react';
import {
    BadgeCheck,
    BookOpenCheck,
    CalendarClock,
    CheckCircle2,
    Gift,
    MapPin,
    Sparkles,
    Trophy,
    XCircle,
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import InputError from '@/components/input-error';

type SelectedCampaign = {
    id: string;
    code: string;
    name: string;
    campaignType: string;
    blueprintCode: string | null;
    status: string;
    venue: { id: string; code: string; name: string } | null;
};

type RegistryEntity = {
    id: string;
    code: string;
    name?: string;
    label?: string;
};

type MissionItem = {
    id: string;
    code: string;
    title: string | null;
    status: string;
    missionType: string | null;
    triggerType: string | null;
    points: number;
    startsAt: string | null;
    endsAt: string | null;
    unlockRule: Record<string, unknown> | null;
    progressCount: number;
    campaign: RegistryEntity | null;
    venue: RegistryEntity | null;
    hub: RegistryEntity | null;
    touchpoint: RegistryEntity | null;
    treasure: (RegistryEntity & { treasureType: string }) | null;
};

type RewardItem = {
    id: string;
    code: string;
    name: string;
    rewardType: string;
    status: string;
    approvalStatus: string;
    rewardTier: string | null;
    description: string | null;
    terms: string | null;
    reviewNotes: string | null;
    availabilityStatus: string;
    availableFrom: string | null;
    availableUntil: string | null;
    submittedAt: string | null;
    reviewedAt: string | null;
    pointCost: number | null;
    stockQuantity: number | null;
    awardedCount: number;
    campaign: RegistryEntity | null;
    venue: RegistryEntity | null;
    partner: (RegistryEntity & { partnerType: string }) | null;
};


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

type TreasureItem = {
    id: string;
    code: string;
    name: string;
    treasureType: string;
    status: string;
    revealRule: Record<string, unknown> | null;
    campaign: RegistryEntity | null;
    venue: RegistryEntity | null;
    missionCode: string | null;
};

type Props = {
    stats: {
        missions: number;
        activeMissions: number;
        totalPoints: number;
        rewards: number;
        pendingRewards: number;
        approvedRewards: number;
        rejectedRewards: number;
        treasures: number;
    };
    missions: MissionItem[];
    rewards: RewardItem[];
    treasures: TreasureItem[];
    selectedBlueprint: SelectedBlueprint | null;
    selectedCampaign: SelectedCampaign | null;
    formOptions: FormOptions;
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

type FormEntity = {
    id: string;
    code: string;
    name?: string;
    title?: string;
    label?: string;
};

type MissionTemplateOption = {
    id: string;
    code: string;
    title: string;
    missionType: string;
    triggerType: string;
    points: number;
};

type PartnerOption = FormEntity & {
    partnerType: string;
};

type FormOptions = {
    missionTemplates: MissionTemplateOption[];
    hubs: FormEntity[];
    touchpoints: (FormEntity & { hubId: string })[];
    partners: PartnerOption[];
};

const statusLabels: Record<string, string> = {
    active: 'فعال',
    draft: 'پیش نویس',
    inactive: 'غیرفعال',
    placeholder: 'نمونه کنترل شده',
    pending_review: 'در انتظار تایید',
    approved: 'تایید شده',
    rejected: 'رد شده',
};

const statusClasses: Record<string, string> = {
    active: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200',
    draft: 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200',
    inactive: 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200',
    placeholder: 'bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-200',
};

const rewardTierLabels: Record<string, string> = {
    bronze: 'برنزی',
    silver: 'نقره‌ای',
    gold: 'طلایی',
    diamond: 'الماسی',
    custom: 'سفارشی',
};

const rewardTierOptions = [
    { value: 'bronze', label: 'برنزی' },
    { value: 'silver', label: 'نقره‌ای' },
    { value: 'gold', label: 'طلایی' },
    { value: 'diamond', label: 'الماسی' },
    { value: 'custom', label: 'سفارشی' },
];

function formatDate(value: string | null) {
    if (!value) {
        return 'بدون محدودیت';
    }

    return new Intl.DateTimeFormat('fa-IR', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

function Stat({
    label,
    value,
    icon: Icon,
}: {
    label: string;
    value: number;
    icon: typeof Trophy;
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

function blueprintFlowUrl(path: string, blueprintCode: string, action: string, campaignCode?: string) {
    const params = new URLSearchParams({
        blueprint: blueprintCode,
        blueprint_action: action,
    });

    if (campaignCode) {
        params.set('campaign', campaignCode);
    }

    return `${path}?${params.toString()}`;
}

export default function MissionRewardRegistryIndex({
    stats,
    missions,
    rewards,
    treasures,
    selectedBlueprint,
    selectedCampaign,
    formOptions,
}: Props) {
    const { flash, auth } = usePage<SharedProps>().props;
    const canMutate = auth.user.role === 'admin' || auth.user.role === 'operator';

    return (
        <>
            <Head title="ماموریت و پاداش" />
            <div
                dir="rtl"
                className="flex h-full flex-1 flex-col gap-5 overflow-x-auto p-4"
            >
                <header className="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p className="text-sm text-muted-foreground">
                            اسکلت واقعی Sprint 1.4
                        </p>
                        <h1 className="mt-1 text-2xl font-semibold">
                            ماموریت، گنج، امتیاز و پاداش
                        </h1>
                    </div>
                    <Button asChild variant="outline">
                        <Link href="/admin/mission-blueprints">
                            <BookOpenCheck className="size-4" />
                            گنجینه ماموریت‌ها
                        </Link>
                    </Button>
                    <div className="grid grid-cols-2 gap-3 text-sm sm:grid-cols-3 xl:grid-cols-6">
                        <Stat
                            icon={Trophy}
                            label="ماموریت"
                            value={stats.missions}
                        />
                        <Stat
                            icon={BadgeCheck}
                            label="فعال"
                            value={stats.activeMissions}
                        />
                        <Stat
                            icon={Sparkles}
                            label="امتیاز"
                            value={stats.totalPoints}
                        />
                        <Stat icon={Gift} label="پاداش" value={stats.rewards} />
                        <Stat
                            icon={CalendarClock}
                            label="در انتظار"
                            value={stats.pendingRewards}
                        />
                        <Stat
                            icon={MapPin}
                            label="گنج"
                            value={stats.treasures}
                        />
                    </div>
                </header>

                {selectedCampaign ? (
                    <section className="rounded-lg border border-primary/25 bg-primary/5 p-4 text-sm shadow-sm">
                        <div className="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                            <div>
                                <p className="text-xs text-muted-foreground">زمینه کمپین فعال</p>
                                <h2 className="mt-1 font-semibold">{selectedCampaign.name}</h2>
                                <p className="mt-1 text-muted-foreground">داده‌های این صفحه فقط برای همین کمپین فیلتر شده‌اند؛ اگر از منوی اصلی وارد شوید، نمای کلی نمایش داده می‌شود.</p>
                            </div>
                            <div className="flex flex-wrap gap-2">
                                <span className="rounded-full bg-background px-3 py-1 text-xs" dir="ltr">{selectedCampaign.code}</span>
                                {selectedCampaign.blueprintCode ? <span className="rounded-full bg-background px-3 py-1 text-xs" dir="ltr">{selectedCampaign.blueprintCode}</span> : null}
                            </div>
                        </div>
                    </section>
                ) : null}

                {selectedBlueprint ? (
                    <section className="rounded-lg border border-primary/25 bg-primary/5 p-4 text-sm shadow-sm">
                        <div className="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                            <div>
                                <p className="text-xs text-muted-foreground">الگوی آماده برای اقدام</p>
                                <h2 className="mt-1 text-lg font-semibold">{selectedBlueprint.title}</h2>
                                <p className="mt-1 text-muted-foreground">داده الگو به این صفحه آمده تا بر اساس آن مأموریت، امتیاز، مدرک و پاداش واقعی تعریف شود.</p>
                            </div>
                            <span className="rounded-full bg-background px-3 py-1 text-xs" dir="ltr">{selectedBlueprint.code}</span>
                        </div>
                        <div className="mt-4 grid gap-3 lg:grid-cols-3">
                            <div className="rounded-lg bg-background/75 p-3"><p className="font-medium">هدف</p><p className="mt-1 text-muted-foreground">{selectedBlueprint.missionGoal}</p></div>
                            <div className="rounded-lg bg-background/75 p-3"><p className="font-medium">مدرک انجام</p><p className="mt-1 text-muted-foreground">{selectedBlueprint.evidenceType}</p></div>
                            <div className="rounded-lg bg-background/75 p-3"><p className="font-medium">امتیاز پیشنهادی</p><p className="mt-1 text-muted-foreground">{selectedBlueprint.points.base.toLocaleString('fa-IR')} + {selectedBlueprint.points.bonus}</p></div>
                        </div>
                        <div className="mt-4 grid gap-3 lg:grid-cols-2">
                            <div className="rounded-lg bg-background/75 p-3"><p className="font-medium">مراحل کاربر</p><ol className="mt-2 space-y-1 text-muted-foreground">{selectedBlueprint.userSteps.map((step, index) => <li key={step}>{(index + 1).toLocaleString('fa-IR')}. {step}</li>)}</ol></div>
                            <div className="rounded-lg bg-background/75 p-3"><p className="font-medium">سطوح پاداش</p><div className="mt-2 flex flex-wrap gap-2">{selectedBlueprint.rewardBasket.slice(0, 4).map((tier) => <span key={tier.level} className="rounded-full bg-muted px-2 py-1 text-xs">{tier.level}: {tier.items.slice(0, 2).join(' / ')}</span>)}</div></div>
                        </div>
                        <p className="mt-3 rounded-lg bg-background/75 p-3 text-muted-foreground"><span className="font-medium text-foreground">اقدام بعدی: </span>{selectedBlueprint.nextBuildAction}</p>
                        <div className="mt-4 flex flex-wrap gap-2">
                            <Button asChild variant="outline" size="sm">
                                {selectedCampaign ? (
                                    <Link href={`/admin/campaign-builder?campaign=${selectedCampaign.code}`}>بازگشت به کارگاه همین کمپین</Link>
                                ) : (
                                    <Link href={blueprintFlowUrl('/admin/campaigns', selectedBlueprint.code, 'build')}>ساخت کمپین مرجع</Link>
                                )}
                            </Button>
                            <Button asChild variant="outline" size="sm">
                                <Link href={blueprintFlowUrl('/admin/campaign-operations', selectedBlueprint.code, 'route', selectedCampaign?.code)}>رفتن به مسیر کمپین</Link>
                            </Button>
                            <Button asChild variant="outline" size="sm">
                                <Link href={blueprintFlowUrl('/admin/campaign-participants', selectedBlueprint.code, 'participants', selectedCampaign?.code)}>مالک پاداش و اعضا</Link>
                            </Button>
                            <Button asChild variant="ghost" size="sm">
                                <Link href="/admin/mission-blueprints">بازگشت به گنجینه</Link>
                            </Button>
                        </div>
                    </section>
                ) : null}

                {flash?.success ? (
                    <section className="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-100">
                        {flash.success}
                    </section>
                ) : null}

                {selectedCampaign && canMutate ? (
                    <section className="exploria-panel">
                        <div className="border-b border-border/70 px-4 py-3 dark:border-sidebar-border">
                            <h2 className="font-semibold">ثبت اجزای مرحله ۳ برای همین کمپین</h2>
                            <p className="mt-1 text-sm text-muted-foreground">
                                مأموریت از قالب‌های آماده انتخاب می‌شود؛ پاداش و گنج هم به همین کد کمپین وصل می‌شوند.
                            </p>
                        </div>
                        <div className="grid gap-4 p-4 xl:grid-cols-3">
                            <Form action="/admin/missions" method="post" options={{ preserveScroll: true }} className="grid gap-3 rounded-lg border border-border/80 bg-card/75 p-3 shadow-sm" autoComplete="off">
                                {({ processing, errors }) => (
                                    <>
                                        <input type="hidden" name="campaign_id" value={selectedCampaign.id} />
                                        <div>
                                            <h3 className="font-semibold">مأموریت</h3>
                                            <p className="mt-1 text-xs text-muted-foreground">قالب مأموریت را انتخاب و نمونه اجرایی آن را بسازید.</p>
                                        </div>
                                        <div className="grid gap-1.5">
                                            <label htmlFor="mission_template_id" className="text-xs font-medium">قالب مأموریت</label>
                                            <select id="mission_template_id" name="mission_template_id" required className="h-9 rounded-md border border-input bg-background px-3 text-sm">
                                                {formOptions.missionTemplates.map((template) => (
                                                    <option key={template.id} value={template.id}>
                                                        {template.title} - {template.points.toLocaleString('fa-IR')} امتیاز
                                                    </option>
                                                ))}
                                            </select>
                                            <InputError message={errors.mission_template_id} />
                                        </div>
                                        <div className="grid gap-1.5">
                                            <label htmlFor="mission_code" className="text-xs font-medium">کد مأموریت</label>
                                            <input id="mission_code" name="code" required dir="ltr" autoComplete="off" placeholder="first-scan-mission" className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                            <InputError message={errors.code} />
                                        </div>
                                        <div className="grid gap-1.5">
                                            <label htmlFor="title_override" className="text-xs font-medium">عنوان نمایشی</label>
                                            <input id="title_override" name="title_override" autoComplete="off" placeholder="مثلا اسکن ورودی خانواده" className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                            <InputError message={errors.title_override} />
                                        </div>
                                        <div className="grid gap-2 sm:grid-cols-2">
                                            <div className="grid gap-1.5">
                                                <label htmlFor="mission_status" className="text-xs font-medium">وضعیت</label>
                                                <select id="mission_status" name="status" defaultValue="draft" className="h-9 rounded-md border border-input bg-background px-3 text-sm">
                                                    <option value="draft">پیش‌نویس</option>
                                                    <option value="active">فعال</option>
                                                    <option value="inactive">غیرفعال</option>
                                                </select>
                                            </div>
                                            <div className="grid gap-1.5">
                                                <label htmlFor="unlock_min_points" className="text-xs font-medium">حداقل امتیاز باز شدن</label>
                                                <input id="unlock_min_points" name="unlock_min_points" type="number" min="0" className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                            </div>
                                        </div>
                                        <div className="grid gap-2 sm:grid-cols-2">
                                            <div className="grid gap-1.5">
                                                <label htmlFor="hub_id" className="text-xs font-medium">هاب</label>
                                                <select id="hub_id" name="hub_id" className="h-9 rounded-md border border-input bg-background px-3 text-sm">
                                                    <option value="">بدون هاب</option>
                                                    {formOptions.hubs.map((hub) => <option key={hub.id} value={hub.id}>{hub.name}</option>)}
                                                </select>
                                                <InputError message={errors.hub_id} />
                                            </div>
                                            <div className="grid gap-1.5">
                                                <label htmlFor="touchpoint_id" className="text-xs font-medium">نقطه تماس</label>
                                                <select id="touchpoint_id" name="touchpoint_id" className="h-9 rounded-md border border-input bg-background px-3 text-sm">
                                                    <option value="">بدون نقطه تماس</option>
                                                    {formOptions.touchpoints.map((touchpoint) => <option key={touchpoint.id} value={touchpoint.id}>{touchpoint.label}</option>)}
                                                </select>
                                                <InputError message={errors.touchpoint_id} />
                                            </div>
                                        </div>
                                        <Button disabled={processing}>
                                            <Trophy className="size-4" />
                                            ثبت مأموریت
                                        </Button>
                                    </>
                                )}
                            </Form>

                            <Form action="/admin/rewards" method="post" options={{ preserveScroll: true }} className="grid gap-3 rounded-lg border border-border/80 bg-card/75 p-3 shadow-sm" autoComplete="off">
                                {({ processing, errors }) => (
                                    <>
                                        <input type="hidden" name="campaign_id" value={selectedCampaign.id} />
                                        <div>
                                            <h3 className="font-semibold">پاداش</h3>
                                            <p className="mt-1 text-xs text-muted-foreground">پاداش قابل دریافت یا هزینه امتیازی را برای کمپین بسازید.</p>
                                        </div>
                                        <div className="grid gap-1.5">
                                            <label htmlFor="reward_name" className="text-xs font-medium">نام پاداش</label>
                                            <input id="reward_name" name="name" required autoComplete="off" placeholder="مثلا کوپن نوشیدنی" className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                            <InputError message={errors.name} />
                                        </div>
                                        <div className="grid gap-2 sm:grid-cols-2">
                                            <div className="grid gap-1.5">
                                                <label htmlFor="reward_code" className="text-xs font-medium">کد</label>
                                                <input id="reward_code" name="code" required dir="ltr" autoComplete="off" placeholder="drink-coupon" className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                                <InputError message={errors.code} />
                                            </div>
                                            <div className="grid gap-1.5">
                                                <label htmlFor="reward_type" className="text-xs font-medium">نوع</label>
                                                <input id="reward_type" name="reward_type" required dir="ltr" autoComplete="off" defaultValue="partner_coupon" className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                                <InputError message={errors.reward_type} />
                                            </div>
                                        </div>
                                        <div className="grid gap-2 sm:grid-cols-2">
                                            <div className="grid gap-1.5">
                                                <label htmlFor="point_cost" className="text-xs font-medium">هزینه امتیازی</label>
                                                <input id="point_cost" name="point_cost" type="number" min="0" className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                            </div>
                                            <div className="grid gap-1.5">
                                                <label htmlFor="stock_quantity" className="text-xs font-medium">موجودی</label>
                                                <input id="stock_quantity" name="stock_quantity" type="number" min="0" className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                            </div>
                                        </div>
                                        <div className="grid gap-1.5">
                                            <label htmlFor="reward_tier" className="text-xs font-medium">سطح پاداش</label>
                                            <select id="reward_tier" name="reward_tier" defaultValue="bronze" className="h-9 rounded-md border border-input bg-background px-3 text-sm">
                                                <option value="">بدون سطح</option>
                                                {rewardTierOptions.map((tier) => (
                                                    <option key={tier.value} value={tier.value}>
                                                        {tier.label}
                                                    </option>
                                                ))}
                                            </select>
                                            <InputError message={errors.reward_tier} />
                                        </div>
                                        {selectedBlueprint?.rewardBasket?.length ? (
                                            <div className="rounded-md bg-muted/40 px-3 py-2 text-xs text-muted-foreground">
                                                <p className="font-medium text-foreground">سطوح پیشنهادی همین الگو</p>
                                                <div className="mt-2 flex flex-wrap gap-1.5">
                                                    {selectedBlueprint.rewardBasket.slice(0, 4).map((tier) => (
                                                        <span key={tier.level} className="rounded-full bg-background px-2 py-1">
                                                            {tier.level}: {tier.items.slice(0, 2).join(' / ')}
                                                        </span>
                                                    ))}
                                                </div>
                                            </div>
                                        ) : null}
                                        <div className="grid gap-2 sm:grid-cols-2">
                                            <div className="grid gap-1.5">
                                                <label htmlFor="partner_account_id" className="text-xs font-medium">مالک پاداش</label>
                                                <select id="partner_account_id" name="partner_account_id" className="h-9 rounded-md border border-input bg-background px-3 text-sm">
                                                    <option value="">پلتفرم / ادمین</option>
                                                    {formOptions.partners.map((partner) => <option key={partner.id} value={partner.id}>{partner.name}</option>)}
                                                </select>
                                                <InputError message={errors.partner_account_id} />
                                            </div>
                                            <div className="grid gap-1.5">
                                                <label htmlFor="reward_status" className="text-xs font-medium">وضعیت</label>
                                                <select id="reward_status" name="status" defaultValue="draft" className="h-9 rounded-md border border-input bg-background px-3 text-sm">
                                                    <option value="draft">پیش‌نویس</option>
                                                    <option value="active">فعال</option>
                                                    <option value="inactive">غیرفعال</option>
                                                </select>
                                            </div>
                                        </div>
                                        <textarea name="description" autoComplete="off" placeholder="توضیح کوتاه پاداش" className="min-h-16 rounded-md border border-input bg-background px-3 py-2 text-sm" />
                                        <textarea name="terms" autoComplete="off" placeholder="شرایط استفاده" className="min-h-16 rounded-md border border-input bg-background px-3 py-2 text-sm" />
                                        <Button disabled={processing}>
                                            <Gift className="size-4" />
                                            ثبت پاداش
                                        </Button>
                                    </>
                                )}
                            </Form>

                            <Form action="/admin/treasures" method="post" options={{ preserveScroll: true }} className="grid gap-3 rounded-lg border border-border/80 bg-card/75 p-3 shadow-sm" autoComplete="off">
                                {({ processing, errors }) => (
                                    <>
                                        <input type="hidden" name="campaign_id" value={selectedCampaign.id} />
                                        <div>
                                            <h3 className="font-semibold">گنج</h3>
                                            <p className="mt-1 text-xs text-muted-foreground">گنج نهایی یا مرحله‌ای را به کمپین و در صورت نیاز به مأموریت وصل کنید.</p>
                                        </div>
                                        <div className="grid gap-1.5">
                                            <label htmlFor="treasure_name" className="text-xs font-medium">نام گنج</label>
                                            <input id="treasure_name" name="name" required autoComplete="off" placeholder="مثلا گنج مسیر خانوادگی" className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                            <InputError message={errors.name} />
                                        </div>
                                        <div className="grid gap-2 sm:grid-cols-2">
                                            <div className="grid gap-1.5">
                                                <label htmlFor="treasure_code" className="text-xs font-medium">کد</label>
                                                <input id="treasure_code" name="code" required dir="ltr" autoComplete="off" placeholder="family-route-treasure" className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                                <InputError message={errors.code} />
                                            </div>
                                            <div className="grid gap-1.5">
                                                <label htmlFor="treasure_type" className="text-xs font-medium">نوع</label>
                                                <input id="treasure_type" name="treasure_type" required dir="ltr" autoComplete="off" defaultValue="final_treasure" className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                                <InputError message={errors.treasure_type} />
                                            </div>
                                        </div>
                                        <div className="grid gap-2 sm:grid-cols-2">
                                            <div className="grid gap-1.5">
                                                <label htmlFor="mission_instance_id" className="text-xs font-medium">اتصال به مأموریت</label>
                                                <select id="mission_instance_id" name="mission_instance_id" className="h-9 rounded-md border border-input bg-background px-3 text-sm">
                                                    <option value="">بدون اتصال مستقیم</option>
                                                    {missions.map((mission) => <option key={mission.id} value={mission.id}>{mission.title ?? mission.code}</option>)}
                                                </select>
                                                <InputError message={errors.mission_instance_id} />
                                            </div>
                                            <div className="grid gap-1.5">
                                                <label htmlFor="required_completed_missions" className="text-xs font-medium">تعداد مأموریت لازم</label>
                                                <input id="required_completed_missions" name="required_completed_missions" type="number" min="0" className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                            </div>
                                        </div>
                                        <div className="grid gap-1.5">
                                            <label htmlFor="treasure_status" className="text-xs font-medium">وضعیت</label>
                                            <select id="treasure_status" name="status" defaultValue="draft" className="h-9 rounded-md border border-input bg-background px-3 text-sm">
                                                <option value="draft">پیش‌نویس</option>
                                                <option value="active">فعال</option>
                                                <option value="inactive">غیرفعال</option>
                                            </select>
                                        </div>
                                        <Button disabled={processing}>
                                            <MapPin className="size-4" />
                                            ثبت گنج
                                        </Button>
                                    </>
                                )}
                            </Form>
                        </div>
                    </section>
                ) : selectedCampaign ? null : (
                    <section className="rounded-lg border border-dashed border-amber-300 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-100">
                        برای ثبت مأموریت، پاداش و گنج، ابتدا از کارگاه یا فهرست کمپین‌ها یک کمپین مشخص را انتخاب کنید.
                    </section>
                )}

                <section className="rounded-lg border border-sidebar-border/70 bg-background dark:border-sidebar-border">
                    <div className="grid min-w-[980px] grid-cols-[1.3fr_0.9fr_0.9fr_0.9fr_0.75fr_1fr_0.8fr] gap-3 border-b border-sidebar-border/70 px-4 py-3 text-xs font-medium text-muted-foreground dark:border-sidebar-border">
                        <span>ماموریت</span>
                        <span>کمپین</span>
                        <span>مکان/هاب</span>
                        <span>نوع/تریگر</span>
                        <span>امتیاز</span>
                        <span>اعتبار</span>
                        <span>گنج</span>
                    </div>

                    {missions.length === 0 ? (
                        <div className="p-8 text-center text-sm text-muted-foreground">
                            هنوز ماموریتی ثبت نشده است.
                        </div>
                    ) : (
                        <div className="min-w-[980px] divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                            {missions.map((mission) => (
                                <article
                                    key={mission.id}
                                    className="grid grid-cols-[1.3fr_0.9fr_0.9fr_0.9fr_0.75fr_1fr_0.8fr] items-center gap-3 px-4 py-3 text-sm"
                                >
                                    <div className="min-w-0">
                                        <div className="flex items-center gap-2">
                                            <Trophy className="size-4 shrink-0 text-muted-foreground" />
                                            <span className="truncate font-medium">
                                                {mission.title}
                                            </span>
                                        </div>
                                        <div className="mt-2 flex items-center gap-2">
                                            <span
                                                className={`inline-flex rounded-full px-2.5 py-1 text-xs font-medium ${
                                                    statusClasses[
                                                        mission.status
                                                    ] ?? statusClasses.inactive
                                                }`}
                                            >
                                                {statusLabels[mission.status] ??
                                                    mission.status}
                                            </span>
                                            <span
                                                className="truncate text-xs text-muted-foreground"
                                                dir="ltr"
                                            >
                                                {mission.code}
                                            </span>
                                        </div>
                                    </div>

                                    <div className="min-w-0">
                                        <p className="truncate">
                                            {mission.campaign?.name ?? '-'}
                                        </p>
                                        <p
                                            className="mt-1 truncate text-xs text-muted-foreground"
                                            dir="ltr"
                                        >
                                            {mission.campaign?.code ?? '-'}
                                        </p>
                                    </div>

                                    <div className="min-w-0">
                                        <p className="truncate">
                                            {mission.venue?.name ?? '-'}
                                        </p>
                                        <p className="mt-1 truncate text-xs text-muted-foreground">
                                            {mission.hub?.name ??
                                                mission.touchpoint?.label ??
                                                '-'}
                                        </p>
                                    </div>

                                    <div className="min-w-0 text-xs">
                                        <p dir="ltr">{mission.missionType}</p>
                                        <p
                                            className="mt-1 text-muted-foreground"
                                            dir="ltr"
                                        >
                                            {mission.triggerType}
                                        </p>
                                    </div>

                                    <div>
                                        <p className="font-semibold">
                                            {mission.points.toLocaleString(
                                                'fa-IR',
                                            )}
                                        </p>
                                        <p className="mt-1 text-xs text-muted-foreground">
                                            انجام:{' '}
                                            {mission.progressCount.toLocaleString(
                                                'fa-IR',
                                            )}
                                        </p>
                                    </div>

                                    <div className="min-w-0">
                                        <div className="flex items-center gap-2 text-xs">
                                            <CalendarClock className="size-4 shrink-0 text-muted-foreground" />
                                            <span className="truncate">
                                                {formatDate(mission.startsAt)}
                                            </span>
                                        </div>
                                        <p className="mt-1 truncate text-xs text-muted-foreground">
                                            تا {formatDate(mission.endsAt)}
                                        </p>
                                    </div>

                                    <div className="min-w-0">
                                        <p className="truncate">
                                            {mission.treasure?.name ?? '-'}
                                        </p>
                                        <p
                                            className="mt-1 truncate text-xs text-muted-foreground"
                                            dir="ltr"
                                        >
                                            {mission.treasure?.treasureType ??
                                                '-'}
                                        </p>
                                    </div>
                                </article>
                            ))}
                        </div>
                    )}
                </section>

                <section className="grid gap-4 lg:grid-cols-2">
                    <div className="rounded-lg border border-sidebar-border/70 bg-background dark:border-sidebar-border">
                        <div className="border-b border-sidebar-border/70 px-4 py-3 dark:border-sidebar-border">
                            <h2 className="font-semibold">تعریف پاداش‌ها</h2>
                        </div>
                        <div className="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                            {rewards.map((reward) => (
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
                                            {reward.rewardTier ? (
                                                <span className="mt-2 inline-flex rounded-full bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary">
                                                    سطح {rewardTierLabels[reward.rewardTier] ?? reward.rewardTier}
                                                </span>
                                            ) : null}
                                        </div>
                                        <div className="flex shrink-0 items-center gap-2">
                                            <span
                                                className={`rounded-full px-2.5 py-1 text-xs font-medium ${
                                                    statusClasses[
                                                        reward.status
                                                    ] ?? statusClasses.inactive
                                                }`}
                                            >
                                                {statusLabels[
                                                    reward.approvalStatus
                                                ] ??
                                                    statusLabels[
                                                        reward.status
                                                    ] ??
                                                    reward.status}
                                            </span>
                                            <span className="text-xs text-muted-foreground">
                                                {reward.pointCost
                                                    ? `${reward.pointCost.toLocaleString('fa-IR')} امتیاز`
                                                    : 'بدون هزینه'}
                                            </span>
                                        </div>
                                    </div>
                                    {reward.description ? (
                                        <p className="line-clamp-2 text-xs text-muted-foreground">
                                            {reward.description}
                                        </p>
                                    ) : null}
                                    {reward.terms ? (
                                        <p className="line-clamp-2 text-xs text-muted-foreground">
                                            شرایط: {reward.terms}
                                        </p>
                                    ) : null}
                                    <p className="text-xs text-muted-foreground">
                                        شریک: {reward.partner?.name ?? 'پلتفرم'}{' '}
                                        · موجودی:{' '}
                                        {reward.stockQuantity?.toLocaleString(
                                            'fa-IR',
                                        ) ?? 'نامحدود'}{' '}
                                        · وضعیت ارائه:{' '}
                                        {statusLabels[reward.availabilityStatus] ??
                                            reward.availabilityStatus}{' '}
                                        · ثبت: {formatDate(reward.submittedAt)}
                                    </p>
                                    {reward.reviewNotes ? (
                                        <p className="rounded-md bg-muted/40 px-3 py-2 text-xs text-muted-foreground">
                                            یادداشت بازبینی: {reward.reviewNotes}
                                        </p>
                                    ) : null}
                                    {reward.approvalStatus ===
                                    'pending_review' ? (
                                        <div className="grid gap-3 rounded-md bg-muted/30 p-3 pt-3">
                                            <Form
                                                action={`/admin/rewards/${reward.id}/approve`}
                                                method="post"
                                                options={{
                                                    preserveScroll: true,
                                                }}
                                                className="grid gap-2 md:grid-cols-[1fr_auto]"
                                            >
                                                {({ processing }) => (
                                                    <>
                                                        <textarea
                                                            name="notes"
                                                            className="min-h-16 rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                                            placeholder="یادداشت تایید برای تیم عملیات"
                                                        />
                                                        <div className="flex items-end">
                                                            <Button
                                                                size="sm"
                                                                disabled={processing}
                                                            >
                                                                <CheckCircle2 className="size-4" />
                                                                تایید
                                                            </Button>
                                                        </div>
                                                    </>
                                                )}
                                            </Form>
                                            <Form
                                                action={`/admin/rewards/${reward.id}/reject`}
                                                method="post"
                                                options={{
                                                    preserveScroll: true,
                                                }}
                                                className="grid gap-2 md:grid-cols-[1fr_auto]"
                                            >
                                                {({ processing }) => (
                                                    <>
                                                        <textarea
                                                            name="notes"
                                                            className="min-h-16 rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                                            placeholder="دلیل رد یا اصلاح موردنیاز"
                                                        />
                                                        <div className="flex items-end">
                                                            <Button
                                                                size="sm"
                                                                variant="outline"
                                                                disabled={processing}
                                                            >
                                                                <XCircle className="size-4" />
                                                                رد
                                                            </Button>
                                                        </div>
                                                    </>
                                                )}
                                            </Form>
                                        </div>
                                    ) : null}
                                </article>
                            ))}
                        </div>
                    </div>

                    <div className="rounded-lg border border-sidebar-border/70 bg-background dark:border-sidebar-border">
                        <div className="border-b border-sidebar-border/70 px-4 py-3 dark:border-sidebar-border">
                            <h2 className="font-semibold">گنج‌ها</h2>
                        </div>
                        <div className="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                            {treasures.map((treasure) => (
                                <article
                                    key={treasure.id}
                                    className="grid gap-2 px-4 py-3 text-sm"
                                >
                                    <div className="flex items-center justify-between gap-3">
                                        <div className="min-w-0">
                                            <p className="truncate font-medium">
                                                {treasure.name}
                                            </p>
                                            <p
                                                className="mt-1 truncate text-xs text-muted-foreground"
                                                dir="ltr"
                                            >
                                                {treasure.code} ·{' '}
                                                {treasure.treasureType}
                                            </p>
                                        </div>
                                        <span
                                            className={`shrink-0 rounded-full px-2.5 py-1 text-xs font-medium ${
                                                statusClasses[
                                                    treasure.status
                                                ] ?? statusClasses.inactive
                                            }`}
                                        >
                                            {statusLabels[treasure.status] ??
                                                treasure.status}
                                        </span>
                                    </div>
                                    <p className="text-xs text-muted-foreground">
                                        ماموریت:{' '}
                                        <span dir="ltr">
                                            {treasure.missionCode ?? '-'}
                                        </span>
                                    </p>
                                </article>
                            ))}
                        </div>
                    </div>
                </section>
            </div>
        </>
    );
}

MissionRewardRegistryIndex.layout = {
    breadcrumbs: [
        {
            title: 'ماموریت و پاداش',
            href: '/admin/missions',
        },
    ],
};
