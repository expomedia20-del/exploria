import { Form, Head, Link, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import {
    BadgeCheck,
    BookOpenCheck,
    CalendarClock,
    CheckCircle2,
    Gift,
    MapPin,
    Pencil,
    Sparkles,
    Trash2,
    Trophy,
    XCircle,
} from 'lucide-react';
import { CampaignContextNav } from '@/components/campaign-context-nav';
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
    missionTemplate: { id: string; code: string; title: string } | null;
    missionType: string | null;
    triggerType: string | null;
    points: number;
    startsAt: string | null;
    endsAt: string | null;
    unlockRule: Record<string, unknown> | null;
    visitorInstruction: string | null;
    completionEvidence: string | null;
    successMessage: string | null;
    cycleStep: { index: number | null; label: string | null };
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
    source: string | null;
    rewardOption: string | null;
    cycleStep: { index: number | null; label: string | null };
    description: string | null;
    terms: string | null;
    reviewNotes: string | null;
    availabilityStatus: string;
    availableFrom: string | null;
    availableUntil: string | null;
    fulfillmentWindow: string | null;
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

type RewardDesignTier = RewardBasketTier & {
    tierKey: string;
    suggestedOptionCount: number;
    options: string[];
};

type RewardDesign = {
    tiers: RewardDesignTier[];
    hiddenTreasures: {
        code: string;
        title: string;
        rule: string;
    }[];
};

type MissionPlanStep = {
    index: number;
    userStep: string;
    recommendedTemplateCode: string;
    title: string;
    suggestedCodeSuffix: string;
    suggestedUnlockMinPoints: number;
    rewardTier: string;
    routeIntent: string;
    operationLink: string;
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
    rewardDesign: RewardDesign;
    missionPlan: MissionPlanStep[];
    nextBuildAction: string;
};

type AlignmentReview = {
    status: 'ready' | 'needs_attention' | 'unchecked';
    expectedSteps: number;
    completedSteps: number;
    treasureSteps?: number[];
    issues: {
        level: 'error' | 'warning';
        code: string;
        title: string;
        action: string;
    }[];
};

type TreasureItem = {
    id: string;
    code: string;
    name: string;
    treasureType: string;
    status: string;
    revealRule: Record<string, unknown> | null;
    treasureTier: string | null;
    revealMode: string | null;
    revealDescription: string | null;
    discoveryHint: string | null;
    source: string | null;
    cycleStep: { index: number | null; label: string | null };
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
    alignment: AlignmentReview | null;
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
    description: string | null;
    missionType: string;
    triggerType: string;
    points: number;
    recommended: boolean;
    recommendationReason: string | null;
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
    revision_requested: 'نیازمند اصلاح',
};

const statusClasses: Record<string, string> = {
    active: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200',
    draft: 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200',
    inactive: 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200',
    placeholder: 'bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-200',
    revision_requested: 'bg-orange-100 text-orange-800 dark:bg-orange-950 dark:text-orange-200',
};

const rewardTierLabels: Record<string, string> = {
    bronze: 'برنزی',
    silver: 'نقره‌ای',
    gold: 'طلایی',
    diamond: 'الماسی',
    custom: 'سفارشی',
};

const rewardSourceLabels: Record<string, string> = {
    admin_campaign_components: 'تعریف مستقیم ادمین',
    partner_offer_submission: 'پیشنهاد واحد تجاری',
    sponsor_proposal_activation: 'از پیشنهاد اسپانسر',
};

const sourceClasses: Record<string, string> = {
    admin_campaign_components: 'bg-slate-100 text-slate-700 dark:bg-slate-900 dark:text-slate-200',
    partner_offer_submission: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200',
    sponsor_proposal_activation: 'bg-fuchsia-100 text-fuchsia-800 ring-1 ring-fuchsia-200 dark:bg-fuchsia-950 dark:text-fuchsia-200 dark:ring-fuchsia-900',
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

function formatDateTimeLocal(value: string | null) {
    if (!value) {
        return '';
    }

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return '';
    }

    const offsetDate = new Date(date.getTime() - date.getTimezoneOffset() * 60000);

    return offsetDate.toISOString().slice(0, 16);
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

function missionCodeSuggestion(campaignCode: string, suffix: string, missions: MissionItem[]) {
    const base = `${campaignCode}-${suffix}`
        .toLowerCase()
        .replace(/[^a-z0-9-]+/g, '-')
        .replace(/-{2,}/g, '-')
        .replace(/^-|-$/g, '')
        .slice(0, 86);
    const existingCount = missions.filter((mission) => mission.code === base || mission.code.startsWith(`${base}-`)).length;

    return existingCount > 0 ? `${base}-${existingCount + 1}`.slice(0, 96) : base;
}

function rewardCodeSuggestion(campaignCode: string, step: MissionPlanStep | null, tierKey: string, rewards: RewardItem[]) {
    const suffix = step ? `step-${step.index}-${tierKey}-reward` : `${tierKey}-reward`;
    const base = `${campaignCode}-${suffix}`
        .toLowerCase()
        .replace(/[^a-z0-9-]+/g, '-')
        .replace(/-{2,}/g, '-')
        .replace(/^-|-$/g, '')
        .slice(0, 86);
    const existingCount = rewards.filter((reward) => reward.code === base || reward.code.startsWith(`${base}-`)).length;

    return existingCount > 0 ? `${base}-${existingCount + 1}`.slice(0, 96) : base;
}

function treasureCodeSuggestion(campaignCode: string, step: MissionPlanStep | null, tierKey: string, treasures: TreasureItem[]) {
    const suffix = step ? `step-${step.index}-${tierKey}-treasure` : `${tierKey}-treasure`;
    const base = `${campaignCode}-${suffix}`
        .toLowerCase()
        .replace(/[^a-z0-9-]+/g, '-')
        .replace(/-{2,}/g, '-')
        .replace(/^-|-$/g, '')
        .slice(0, 86);
    const existingCount = treasures.filter((treasure) => treasure.code === base || treasure.code.startsWith(`${base}-`)).length;

    return existingCount > 0 ? `${base}-${existingCount + 1}`.slice(0, 96) : base;
}

export default function MissionRewardRegistryIndex({
    stats,
    missions,
    rewards,
    treasures,
    alignment,
    selectedBlueprint,
    selectedCampaign,
    formOptions,
}: Props) {
    const { flash, auth } = usePage<SharedProps>().props;
    const canMutate = auth.user.role === 'admin' || auth.user.role === 'operator';
    const missionPlan = selectedBlueprint?.missionPlan ?? [];
    const firstMissionPlan = missionPlan[0] ?? null;
    const firstMissionTemplateId = formOptions.missionTemplates.find((template) => template.code === firstMissionPlan?.recommendedTemplateCode)?.id ?? formOptions.missionTemplates[0]?.id ?? '';
    const [selectedMissionTemplateId, setSelectedMissionTemplateId] = useState(firstMissionTemplateId);
    const [selectedMissionPlanIndex, setSelectedMissionPlanIndex] = useState(0);
    const [selectedHubId, setSelectedHubId] = useState(formOptions.hubs[0]?.id ?? '');
    const [editingMission, setEditingMission] = useState<MissionItem | null>(null);
    const [editingReward, setEditingReward] = useState<RewardItem | null>(null);
    const [editingTreasure, setEditingTreasure] = useState<TreasureItem | null>(null);
    const selectedMissionPlan = missionPlan[selectedMissionPlanIndex] ?? null;
    const selectedMissionTemplate = formOptions.missionTemplates.find((template) => template.id === selectedMissionTemplateId) ?? formOptions.missionTemplates[0] ?? null;
    const missionPlanTemplate = formOptions.missionTemplates.find((template) => template.code === selectedMissionPlan?.recommendedTemplateCode) ?? selectedMissionTemplate;
    const filteredTouchpoints = selectedHubId
        ? formOptions.touchpoints.filter((touchpoint) => touchpoint.hubId === selectedHubId)
        : formOptions.touchpoints;
    const rewardDesignTiers = selectedBlueprint?.rewardDesign?.tiers ?? [];
    const suggestedRewardTier = selectedMissionPlan?.rewardTier ?? rewardDesignTiers[0]?.tierKey ?? 'bronze';
    const [selectedRewardTier, setSelectedRewardTier] = useState(suggestedRewardTier);
    const [selectedTreasureTier, setSelectedTreasureTier] = useState(suggestedRewardTier);
    const [selectedRewardOptionText, setSelectedRewardOptionText] = useState('');
    const selectedRewardDesignTier = rewardDesignTiers.find((tier) => tier.tierKey === selectedRewardTier) ?? rewardDesignTiers[0] ?? null;
    const selectedTreasureDesignTier = rewardDesignTiers.find((tier) => tier.tierKey === selectedTreasureTier) ?? rewardDesignTiers[0] ?? null;
    const selectedRewardOption = selectedRewardOptionText || selectedRewardDesignTier?.options[0] || '';
    const suggestedMissionSuffix = selectedMissionPlan?.suggestedCodeSuffix ?? selectedMissionTemplate?.code ?? 'mission';
    const suggestedMissionCode = useMemo(
        () => missionCodeSuggestion(selectedCampaign?.code ?? 'campaign', suggestedMissionSuffix, missions),
        [missions, selectedCampaign?.code, suggestedMissionSuffix],
    );
    const suggestedRewardCode = useMemo(
        () => rewardCodeSuggestion(selectedCampaign?.code ?? 'campaign', selectedMissionPlan, selectedRewardTier, rewards),
        [rewards, selectedCampaign?.code, selectedMissionPlan, selectedRewardTier],
    );
    const suggestedTreasureCode = useMemo(
        () => treasureCodeSuggestion(selectedCampaign?.code ?? 'campaign', selectedMissionPlan, selectedTreasureTier, treasures),
        [treasures, selectedCampaign?.code, selectedMissionPlan, selectedTreasureTier],
    );
    const missionForSelectedStep = selectedMissionPlan
        ? missions.find((mission) => Number(mission.cycleStep.index) === selectedMissionPlan.index) ?? null
        : null;
    const alignmentErrors = alignment?.issues.filter((issue) => issue.level === 'error') ?? [];
    const partnerRewardOffers = useMemo(
        () =>
            [...rewards]
                .filter((reward) => reward.source === 'partner_offer_submission')
                .sort((a, b) => {
                    const stepA = Number(a.cycleStep.index ?? 999);
                    const stepB = Number(b.cycleStep.index ?? 999);

                    if (stepA !== stepB) {
                        return stepA - stepB;
                    }

                    return (a.rewardTier ?? '').localeCompare(b.rewardTier ?? '');
                }),
        [rewards],
    );
    const pendingPartnerOffers = partnerRewardOffers.filter((reward) => reward.approvalStatus === 'pending_review');
    const approvedPartnerOffers = partnerRewardOffers.filter((reward) => reward.approvalStatus === 'approved');
    const revisionPartnerOffers = partnerRewardOffers.filter((reward) => reward.approvalStatus === 'revision_requested');
    const directRewardDefinitions = rewards.filter((reward) => reward.source !== 'partner_offer_submission');

    function selectMissionPlanStep(step: MissionPlanStep, index: number) {
        setSelectedMissionPlanIndex(index);
        setSelectedRewardTier(step.rewardTier);
        setSelectedTreasureTier(step.rewardTier);
        setSelectedRewardOptionText('');
        setEditingMission(null);
        setEditingReward(null);
        setEditingTreasure(null);

        const matchingTemplate = formOptions.missionTemplates.find((template) => template.code === step.recommendedTemplateCode);
        if (matchingTemplate) {
            setSelectedMissionTemplateId(matchingTemplate.id);
        }
    }

    function scrollToStageThreeForms() {
        document.getElementById('stage-three-forms')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function startEditMission(mission: MissionItem) {
        setEditingMission(mission);

        const stepIndex = mission.cycleStep?.index ? mission.cycleStep.index - 1 : -1;
        if (stepIndex >= 0 && missionPlan[stepIndex]) {
            setSelectedMissionPlanIndex(stepIndex);
        }

        const matchingTemplate = formOptions.missionTemplates.find((template) => template.id === mission.missionTemplate?.id || template.code === mission.missionTemplate?.code);
        if (matchingTemplate) {
            setSelectedMissionTemplateId(matchingTemplate.id);
        }

        setSelectedHubId(mission.hub?.id ?? '');
        scrollToStageThreeForms();
    }

    function startEditReward(reward: RewardItem) {
        setEditingReward(reward);

        const stepIndex = reward.cycleStep?.index ? reward.cycleStep.index - 1 : -1;
        if (stepIndex >= 0 && missionPlan[stepIndex]) {
            setSelectedMissionPlanIndex(stepIndex);
        }

        if (reward.rewardTier) {
            setSelectedRewardTier(reward.rewardTier);
        }

        setSelectedRewardOptionText(reward.rewardOption ?? '');
        scrollToStageThreeForms();
    }

    function startEditTreasure(treasure: TreasureItem) {
        setEditingTreasure(treasure);

        const stepIndex = treasure.cycleStep?.index ? treasure.cycleStep.index - 1 : -1;
        if (stepIndex >= 0 && missionPlan[stepIndex]) {
            setSelectedMissionPlanIndex(stepIndex);
        }

        if (treasure.treasureTier) {
            setSelectedTreasureTier(treasure.treasureTier);
        }

        scrollToStageThreeForms();
    }

    return (
        <>
            <Head title="مأموریت‌ها و پاداش‌ها" />
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
                            مأموریت‌ها، گنج، امتیاز و پاداش
                        </h1>
                    </div>
                    <Button asChild variant="outline">
                        <Link href="/admin/mission-blueprints">
                            <BookOpenCheck className="size-4" />
                            گنجینه الگوها
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

                {selectedCampaign ? (
                    <CampaignContextNav campaign={selectedCampaign} className="border-primary/35 bg-primary/5" />
                ) : (
                    <section className="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 shadow-sm dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-100">
                        <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <p className="font-semibold">کمپین مشخصی برای ادامه کار انتخاب نشده است.</p>
                                <p className="mt-1 text-xs opacity-85">
                                    برای دیدن نوار «ادامه کار همین کمپین»، از ساخت کمپین یا لیست کمپین‌ها وارد همین صفحه شوید.
                                </p>
                            </div>
                            <div className="flex flex-wrap gap-2">
                                <Button asChild size="sm">
                                    <Link href="/admin/campaign-builder">رفتن به ساخت کمپین</Link>
                                </Button>
                                <Button asChild variant="outline" size="sm">
                                    <Link href="/admin/campaigns">انتخاب کمپین</Link>
                                </Button>
                                <Button asChild variant="ghost" size="sm">
                                    <Link href="/admin/mission-blueprints">
                                        <BookOpenCheck className="size-4" />
                                        گنجینه الگوها
                                    </Link>
                                </Button>
                            </div>
                        </div>
                    </section>
                )}

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
                            <div className="rounded-lg bg-background/75 p-3"><p className="font-medium">چرخه و قالب مأموریت</p><ol className="mt-2 space-y-1 text-muted-foreground">{(selectedBlueprint.missionPlan ?? []).map((step) => <li key={step.userStep}>{step.index.toLocaleString('fa-IR')}. {step.userStep} · {step.recommendedTemplateCode}</li>)}</ol></div>
                            <div className="rounded-lg bg-background/75 p-3">
                                <p className="font-medium">سطوح چندگزینه‌ای پاداش</p>
                                <div className="mt-2 grid gap-2">
                                    {(selectedBlueprint.rewardDesign?.tiers ?? []).map((tier) => (
                                        <div key={tier.tierKey} className="rounded-md border border-border/70 bg-background px-3 py-2">
                                            <div className="flex items-center justify-between gap-2">
                                                <p className="text-xs font-medium">{tier.level}</p>
                                                <span className="text-[11px] text-muted-foreground">
                                                    {tier.suggestedOptionCount.toLocaleString('fa-IR')} گزینه پیشنهادی
                                                </span>
                                            </div>
                                            <div className="mt-2 flex flex-wrap gap-1.5">
                                                {tier.options.map((option) => (
                                                    <span key={option} className="rounded-full bg-muted px-2 py-1 text-[11px]">
                                                        {option}
                                                    </span>
                                                ))}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
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

                {alignment ? (
                    <section className={`rounded-lg border p-4 text-sm shadow-sm ${alignment.status === 'ready' ? 'border-emerald-200 bg-emerald-50 text-emerald-900 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-100' : 'border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-100'}`}>
                        <div className="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                            <div>
                                <p className="text-xs opacity-80">کنترل سازگاری الگو و مرحله ۳</p>
                                <h2 className="mt-1 font-semibold">
                                    {alignment.status === 'ready' ? 'چرخه، مأموریت‌ها و پاداش‌ها با الگوی کمپین همخوان است.' : 'چند مورد برای همخوانی چرخه، مأموریت‌ها و پاداش‌ها باقی مانده است.'}
                                </h2>
                                <p className="mt-1 text-xs opacity-80">
                                    {alignment.completedSteps.toLocaleString('fa-IR')} از {alignment.expectedSteps.toLocaleString('fa-IR')} گام چرخه مأموریت‌ها و پاداش‌ها ثبت‌شده دارد.
                                </p>
                            </div>
                            <span className="rounded-full bg-background/80 px-3 py-1 text-xs text-foreground">
                                {alignment.status === 'ready' ? 'آماده نقشه عملیات' : `${alignmentErrors.length.toLocaleString('fa-IR')} نقص اصلی`}
                            </span>
                        </div>
                        {alignment.issues.length > 0 ? (
                            <div className="mt-3 grid gap-2 md:grid-cols-2">
                                {alignment.issues.map((issue) => (
                                    <div key={`${issue.code}-${issue.title}`} className="rounded-md bg-background/80 px-3 py-2 text-foreground">
                                        <p className="font-medium">{issue.title}</p>
                                        <p className="mt-1 text-xs text-muted-foreground">{issue.action}</p>
                                    </div>
                                ))}
                            </div>
                        ) : null}
                    </section>
                ) : null}

                {flash?.success ? (
                    <section className="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-100">
                        {flash.success}
                    </section>
                ) : null}

                {selectedCampaign && canMutate ? (
                    <section id="stage-three-forms" className="exploria-panel">
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
                                        {editingTreasure ? <input type="hidden" name="treasure_id" value={editingTreasure.id} /> : null}
                                        {selectedMissionPlan ? (
                                            <>
                                                <input type="hidden" name="cycle_step_index" value={selectedMissionPlan.index} />
                                                <input type="hidden" name="cycle_step_label" value={selectedMissionPlan.userStep} />
                                            </>
                                        ) : null}
                                        <div>
                                            <h3 className="font-semibold">مأموریت</h3>
                                            <p className="mt-1 text-xs text-muted-foreground">قالب مأموریت را انتخاب و نمونه اجرایی آن را بسازید.</p>
                                        </div>
                                        {missionPlan.length > 0 ? (
                                            <div className="grid gap-2">
                                                <p className="text-xs font-medium">چرخه کاربر همین کمپین</p>
                                                <div className="grid gap-2">
                                                    {missionPlan.map((step, index) => (
                                                        <button
                                                            key={`${step.index}-${step.userStep}`}
                                                            type="button"
                                                            onClick={() => selectMissionPlanStep(step, index)}
                                                            className={`rounded-md border px-3 py-2 text-right text-xs transition ${
                                                                index === selectedMissionPlanIndex
                                                                    ? 'border-primary bg-primary/10 text-primary'
                                                                    : 'border-border bg-background text-muted-foreground hover:border-primary/50'
                                                            }`}
                                                        >
                                                            <span className="font-medium">گام {step.index.toLocaleString('fa-IR')}: {step.userStep}</span>
                                                            <span className="mt-1 block">{step.routeIntent}</span>
                                                        </button>
                                                    ))}
                                                </div>
                                            </div>
                                        ) : null}
                                        <div className="grid gap-1.5">
                                            <label htmlFor="mission_template_id" className="text-xs font-medium">قالب مأموریت همین گام</label>
                                            {missionPlanTemplate ? (
                                                <>
                                                    <input type="hidden" name="mission_template_id" value={missionPlanTemplate.id} />
                                                    <div className="rounded-md border border-primary/20 bg-primary/5 px-3 py-2 text-sm">
                                                        <p className="font-medium">{missionPlanTemplate.title}</p>
                                                        <p className="mt-1 text-xs text-muted-foreground" dir="ltr">{missionPlanTemplate.code} · {missionPlanTemplate.points.toLocaleString('fa-IR')} امتیاز</p>
                                                    </div>
                                                </>
                                            ) : (
                                                <select
                                                    id="mission_template_id"
                                                    name="mission_template_id"
                                                    required
                                                    value={selectedMissionTemplate?.id ?? ''}
                                                    onChange={(event) => setSelectedMissionTemplateId(event.target.value)}
                                                    className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                                >
                                                    {formOptions.missionTemplates.map((template) => (
                                                        <option key={template.id} value={template.id}>
                                                            {template.title} - {template.points.toLocaleString('fa-IR')} امتیاز
                                                        </option>
                                                    ))}
                                                </select>
                                            )}
                                            <InputError message={errors.mission_template_id} />
                                        </div>
                                        {missionPlanTemplate ? (
                                            <div className="rounded-md bg-muted/40 px-3 py-2 text-xs text-muted-foreground">
                                                <div className="flex flex-wrap items-center gap-2">
                                                    {missionPlanTemplate.recommended ? (
                                                        <span className="rounded-full bg-primary/10 px-2 py-1 font-medium text-primary">پیشنهادی برای این کمپین</span>
                                                    ) : null}
                                                    <span dir="ltr">{missionPlanTemplate.missionType}</span>
                                                    <span dir="ltr">{missionPlanTemplate.triggerType}</span>
                                                </div>
                                                {missionPlanTemplate.recommendationReason ? (
                                                    <p className="mt-2">{missionPlanTemplate.recommendationReason}</p>
                                                ) : null}
                                                {missionPlanTemplate.description ? (
                                                    <p className="mt-1">{missionPlanTemplate.description}</p>
                                                ) : null}
                                                {selectedMissionPlan ? (
                                                    <p className="mt-2 rounded-md bg-background px-2 py-1">
                                                        اتصال نقشه: {selectedMissionPlan.operationLink} · {selectedMissionPlan.routeIntent}
                                                    </p>
                                                ) : null}
                                            </div>
                                        ) : null}
                                        <div className="grid gap-1.5">
                                            <label htmlFor="mission_code" className="text-xs font-medium">کد مأموریت</label>
                                            <input key={`mission-code-${editingMission?.id ?? suggestedMissionCode}`} id="mission_code" name="code" required dir="ltr" autoComplete="off" defaultValue={editingMission?.code ?? suggestedMissionCode} className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                            <InputError message={errors.code} />
                                        </div>
                                        <div className="grid gap-1.5">
                                            <label htmlFor="title_override" className="text-xs font-medium">عنوان نمایشی</label>
                                            <input key={`mission-title-${editingMission?.id ?? selectedMissionPlan?.title ?? 'title'}`} id="title_override" name="title_override" autoComplete="off" defaultValue={editingMission?.title ?? selectedMissionPlan?.title ?? ''} placeholder="مثلا اسکن ورودی خانواده" className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                            <InputError message={errors.title_override} />
                                        </div>
                                        <div className="grid gap-1.5">
                                            <label htmlFor="visitor_instruction" className="text-xs font-medium">راهنمای نمایش به کاربر</label>
                                            <textarea key={`mission-instruction-${editingMission?.id ?? selectedMissionPlan?.index ?? 'new'}`} id="visitor_instruction" name="visitor_instruction" autoComplete="off" defaultValue={editingMission?.visitorInstruction ?? (selectedMissionPlan ? `${selectedMissionPlan.userStep} را انجام دهید و راهنمای مسیر را دنبال کنید.` : '')} placeholder="متنی که کاربر برای انجام این مأموریت می‌بیند" className="min-h-16 rounded-md border border-input bg-background px-3 py-2 text-sm" />
                                            <InputError message={errors.visitor_instruction} />
                                        </div>
                                        <div className="grid gap-2 sm:grid-cols-2">
                                            <div className="grid gap-1.5">
                                                <label htmlFor="completion_evidence" className="text-xs font-medium">مدرک انجام</label>
                                                <input key={`mission-evidence-${editingMission?.id ?? missionPlanTemplate?.triggerType ?? 'new'}`} id="completion_evidence" name="completion_evidence" autoComplete="off" defaultValue={editingMission?.completionEvidence ?? missionPlanTemplate?.triggerType ?? ''} placeholder="مثلا اسکن QR، عکس، پاسخ کوتاه، تایید فروشگاه" className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                                <InputError message={errors.completion_evidence} />
                                            </div>
                                            <div className="grid gap-1.5">
                                                <label htmlFor="success_message" className="text-xs font-medium">پیام موفقیت/قدم بعدی</label>
                                                <input key={`mission-success-${editingMission?.id ?? selectedMissionPlan?.index ?? 'new'}`} id="success_message" name="success_message" autoComplete="off" defaultValue={editingMission?.successMessage ?? (selectedMissionPlan ? `گام ${selectedMissionPlan.index.toLocaleString('fa-IR')} کامل شد؛ به مرحله بعد بروید.` : '')} placeholder="بعد از تکمیل مأموریت چه پیامی دیده شود؟" className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                                <InputError message={errors.success_message} />
                                            </div>
                                        </div>
                                        <div className="grid gap-2 sm:grid-cols-2">
                                            <div className="grid gap-1.5">
                                                <label htmlFor="mission_status" className="text-xs font-medium">وضعیت</label>
                                                <select key={`mission-status-${editingMission?.id ?? 'new'}`} id="mission_status" name="status" defaultValue={editingMission?.status ?? 'draft'} className="h-9 rounded-md border border-input bg-background px-3 text-sm">
                                                    <option value="draft">پیش‌نویس</option>
                                                    <option value="active">فعال</option>
                                                    <option value="inactive">غیرفعال</option>
                                                </select>
                                            </div>
                                            <div className="grid gap-1.5">
                                                <label htmlFor="unlock_min_points" className="text-xs font-medium">حداقل امتیاز باز شدن</label>
                                                <input key={`unlock-${editingMission?.id ?? selectedMissionPlan?.index ?? 'none'}`} id="unlock_min_points" name="unlock_min_points" type="number" min="0" defaultValue={Number(editingMission?.unlockRule?.min_points ?? selectedMissionPlan?.suggestedUnlockMinPoints ?? 0)} className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                            </div>
                                        </div>
                                        <div className="grid gap-2 sm:grid-cols-2">
                                            <div className="grid gap-1.5">
                                                <label htmlFor="starts_at" className="text-xs font-medium">شروع اعتبار مأموریت</label>
                                                <input key={`starts-${editingMission?.id ?? 'new'}`} id="starts_at" name="starts_at" type="datetime-local" defaultValue={formatDateTimeLocal(editingMission?.startsAt ?? null)} className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                                <InputError message={errors.starts_at} />
                                            </div>
                                            <div className="grid gap-1.5">
                                                <label htmlFor="ends_at" className="text-xs font-medium">پایان اعتبار مأموریت</label>
                                                <input key={`ends-${editingMission?.id ?? 'new'}`} id="ends_at" name="ends_at" type="datetime-local" defaultValue={formatDateTimeLocal(editingMission?.endsAt ?? null)} className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                                <InputError message={errors.ends_at} />
                                            </div>
                                        </div>
                                        <div className="grid gap-2 sm:grid-cols-2">
                                            <div className="grid gap-1.5">
                                                <label htmlFor="hub_id" className="text-xs font-medium">هاب</label>
                                                <select id="hub_id" name="hub_id" value={selectedHubId} onChange={(event) => setSelectedHubId(event.target.value)} className="h-9 rounded-md border border-input bg-background px-3 text-sm">
                                                    <option value="">بدون هاب</option>
                                                    {formOptions.hubs.map((hub) => <option key={hub.id} value={hub.id}>{hub.name}</option>)}
                                                </select>
                                                <InputError message={errors.hub_id} />
                                            </div>
                                            <div className="grid gap-1.5">
                                                <label htmlFor="touchpoint_id" className="text-xs font-medium">نقطه تماس</label>
                                                <select key={`touchpoint-${editingMission?.id ?? selectedHubId}`} id="touchpoint_id" name="touchpoint_id" defaultValue={editingMission?.touchpoint?.id ?? ''} className="h-9 rounded-md border border-input bg-background px-3 text-sm">
                                                    <option value="">بدون نقطه تماس</option>
                                                    {filteredTouchpoints.map((touchpoint) => <option key={touchpoint.id} value={touchpoint.id}>{touchpoint.label}</option>)}
                                                </select>
                                                <InputError message={errors.touchpoint_id} />
                                            </div>
                                        </div>
                                        {formOptions.hubs.length === 0 || formOptions.touchpoints.length === 0 ? (
                                            <p className="rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-100">
                                                برای این مکان هنوز هاب یا نقطه تماس کافی تعریف نشده است؛ تکمیل مکان و نقاط تماس باعث می‌شود مأموریت در نقشه عملیات دقیق‌تر بنشیند.
                                            </p>
                                        ) : null}
                                        <p className="rounded-md bg-primary/5 px-3 py-2 text-xs text-muted-foreground">
                                            در نقشه عملیات، همین مأموریت زیر بخش «مأموریت‌ها» و در صورت انتخاب هاب/نقطه تماس، روی مسیر همان نقطه دیده می‌شود.
                                        </p>
                                        <Button disabled={processing}>
                                            <Trophy className="size-4" />
                                            {editingMission ? 'ذخیره ویرایش مأموریت' : 'ثبت مأموریت'}
                                        </Button>
                                    </>
                                )}
                            </Form>

                            <Form action="/admin/rewards" method="post" options={{ preserveScroll: true }} className="grid gap-3 rounded-lg border border-border/80 bg-card/75 p-3 shadow-sm" autoComplete="off">
                                {({ processing, errors }) => (
                                    <>
                                        <input type="hidden" name="campaign_id" value={selectedCampaign.id} />
                                        {selectedMissionPlan ? (
                                            <>
                                                <input type="hidden" name="cycle_step_index" value={selectedMissionPlan.index} />
                                                <input type="hidden" name="cycle_step_label" value={selectedMissionPlan.userStep} />
                                            </>
                                        ) : null}
                                        <input type="hidden" name="reward_option" value={selectedRewardOption} />
                                        <div>
                                            <h3 className="font-semibold">پاداش</h3>
                                            <p className="mt-1 text-xs text-muted-foreground">پاداش قابل دریافت یا هزینه امتیازی را برای کمپین بسازید.</p>
                                        </div>
                                        <div className="grid gap-1.5">
                                            <label htmlFor="reward_name" className="text-xs font-medium">نام پاداش</label>
                                            <input key={`reward-name-${editingReward?.id ?? selectedRewardOption}`} id="reward_name" name="name" required autoComplete="off" defaultValue={editingReward?.name ?? selectedRewardOption} placeholder="مثلا کوپن نوشیدنی" className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                            <InputError message={errors.name} />
                                        </div>
                                        <div className="grid gap-2 sm:grid-cols-2">
                                            <div className="grid gap-1.5">
                                                <label htmlFor="reward_code" className="text-xs font-medium">کد</label>
                                                <input key={`reward-code-${editingReward?.id ?? suggestedRewardCode}`} id="reward_code" name="code" required dir="ltr" autoComplete="off" defaultValue={editingReward?.code ?? suggestedRewardCode} className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                                <InputError message={errors.code} />
                                            </div>
                                            <div className="grid gap-1.5">
                                                <label htmlFor="reward_type" className="text-xs font-medium">نوع</label>
                                                <input key={`reward-type-${editingReward?.id ?? 'new'}`} id="reward_type" name="reward_type" required dir="ltr" autoComplete="off" defaultValue={editingReward?.rewardType ?? 'partner_coupon'} className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                                <InputError message={errors.reward_type} />
                                            </div>
                                        </div>
                                        <div className="grid gap-2 sm:grid-cols-2">
                                            <div className="grid gap-1.5">
                                                <label htmlFor="point_cost" className="text-xs font-medium">هزینه امتیازی</label>
                                                <input key={`reward-points-${editingReward?.id ?? selectedMissionPlan?.index ?? 'none'}`} id="point_cost" name="point_cost" type="number" min="0" defaultValue={editingReward?.pointCost ?? selectedMissionPlan?.suggestedUnlockMinPoints ?? 0} className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                            </div>
                                            <div className="grid gap-1.5">
                                                <label htmlFor="stock_quantity" className="text-xs font-medium">موجودی</label>
                                                <input key={`reward-stock-${editingReward?.id ?? 'new'}`} id="stock_quantity" name="stock_quantity" type="number" min="0" defaultValue={editingReward?.stockQuantity ?? ''} className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                            </div>
                                        </div>
                                        <div className="grid gap-1.5">
                                            <label htmlFor="reward_tier" className="text-xs font-medium">سطح پاداش</label>
                                            <select id="reward_tier" name="reward_tier" value={selectedRewardTier} onChange={(event) => { setSelectedRewardTier(event.target.value); setSelectedRewardOptionText(''); }} className="h-9 rounded-md border border-input bg-background px-3 text-sm">
                                                <option value="">بدون سطح</option>
                                                {(rewardDesignTiers.length > 0 ? rewardDesignTiers : rewardTierOptions).map((tier) => (
                                                    <option key={'tierKey' in tier ? tier.tierKey : tier.value} value={'tierKey' in tier ? tier.tierKey : tier.value}>
                                                        {'tierKey' in tier ? tier.level : tier.label}
                                                    </option>
                                                ))}
                                                <option value="custom">سفارشی</option>
                                            </select>
                                            <InputError message={errors.reward_tier} />
                                        </div>
                                        {selectedRewardDesignTier ? (
                                            <div className="rounded-md bg-muted/40 px-3 py-2 text-xs text-muted-foreground">
                                                <p className="font-medium text-foreground">گزینه‌های ترکیبی سطح {selectedRewardDesignTier.level}</p>
                                                <div className="mt-2 grid gap-1.5">
                                                    {selectedRewardDesignTier.options.map((option) => (
                                                        <button
                                                            key={option}
                                                            type="button"
                                                            onClick={() => setSelectedRewardOptionText(option)}
                                                            className={`rounded-md border px-3 py-2 text-right transition ${selectedRewardOption === option ? 'border-primary bg-primary/10 text-primary' : 'border-border bg-background hover:border-primary/50'}`}
                                                        >
                                                            {option}
                                                        </button>
                                                    ))}
                                                </div>
                                            </div>
                                        ) : null}
                                        {selectedBlueprint?.rewardDesign?.hiddenTreasures?.length ? (
                                            <div className="rounded-md border border-dashed border-primary/30 bg-primary/5 px-3 py-2 text-xs text-muted-foreground">
                                                <p className="font-medium text-foreground">گنج پنهان برای انگیزش ادامه مسیر</p>
                                                <div className="mt-2 grid gap-1.5">
                                                    {selectedBlueprint.rewardDesign.hiddenTreasures.map((treasure) => (
                                                        <p key={treasure.code}>{treasure.title}: {treasure.rule}</p>
                                                    ))}
                                                </div>
                                            </div>
                                        ) : null}
                                        <div className="grid gap-2 sm:grid-cols-2">
                                            <div className="grid gap-1.5">
                                                <label htmlFor="partner_account_id" className="text-xs font-medium">مالک پاداش</label>
                                                <select key={`reward-partner-${editingReward?.id ?? 'new'}`} id="partner_account_id" name="partner_account_id" defaultValue={editingReward?.partner?.id ?? ''} className="h-9 rounded-md border border-input bg-background px-3 text-sm">
                                                    <option value="">پلتفرم / ادمین</option>
                                                    {formOptions.partners.map((partner) => <option key={partner.id} value={partner.id}>{partner.name}</option>)}
                                                </select>
                                                <InputError message={errors.partner_account_id} />
                                            </div>
                                            <div className="grid gap-1.5">
                                                <label htmlFor="reward_status" className="text-xs font-medium">وضعیت</label>
                                                <select key={`reward-status-${editingReward?.id ?? 'new'}`} id="reward_status" name="status" defaultValue={editingReward?.status ?? 'draft'} className="h-9 rounded-md border border-input bg-background px-3 text-sm">
                                                    <option value="draft">پیش‌نویس</option>
                                                    <option value="active">فعال</option>
                                                    <option value="inactive">غیرفعال</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div className="grid gap-2 sm:grid-cols-2">
                                            <div className="grid gap-1.5">
                                                <label htmlFor="available_from" className="text-xs font-medium">شروع اعتبار پاداش</label>
                                                <input key={`reward-from-${editingReward?.id ?? 'new'}`} id="available_from" name="available_from" type="datetime-local" defaultValue={formatDateTimeLocal(editingReward?.availableFrom ?? null)} className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                                <InputError message={errors.available_from} />
                                            </div>
                                            <div className="grid gap-1.5">
                                                <label htmlFor="available_until" className="text-xs font-medium">پایان اعتبار پاداش</label>
                                                <input key={`reward-until-${editingReward?.id ?? 'new'}`} id="available_until" name="available_until" type="datetime-local" defaultValue={formatDateTimeLocal(editingReward?.availableUntil ?? null)} className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                                <InputError message={errors.available_until} />
                                            </div>
                                        </div>
                                        <div className="grid gap-1.5">
                                            <label htmlFor="fulfillment_window" className="text-xs font-medium">زمان/روش تحویل پاداش</label>
                                            <input key={`reward-window-${editingReward?.id ?? 'new'}`} id="fulfillment_window" name="fulfillment_window" autoComplete="off" defaultValue={editingReward?.fulfillmentWindow ?? ''} placeholder="مثلا همان روز در فروشگاه مالک پاداش یا تا ۴۸ ساعت پس از تایید" className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                            <InputError message={errors.fulfillment_window} />
                                        </div>
                                        <textarea key={`reward-desc-${editingReward?.id ?? 'new'}`} name="description" autoComplete="off" defaultValue={editingReward?.description ?? ''} placeholder="توضیح کوتاه پاداش" className="min-h-16 rounded-md border border-input bg-background px-3 py-2 text-sm" />
                                        <textarea key={`reward-terms-${editingReward?.id ?? 'new'}`} name="terms" autoComplete="off" defaultValue={editingReward?.terms ?? ''} placeholder="شرایط استفاده" className="min-h-16 rounded-md border border-input bg-background px-3 py-2 text-sm" />
                                        <Button disabled={processing}>
                                            <Gift className="size-4" />
                                            {editingReward ? 'ذخیره ویرایش پاداش' : 'ثبت پاداش'}
                                        </Button>
                                    </>
                                )}
                            </Form>

                            <Form action="/admin/treasures" method="post" options={{ preserveScroll: true }} className="grid gap-3 rounded-lg border border-border/80 bg-card/75 p-3 shadow-sm" autoComplete="off">
                                {({ processing, errors }) => (
                                    <>
                                        <input type="hidden" name="campaign_id" value={selectedCampaign.id} />
                                        {selectedMissionPlan ? (
                                            <>
                                                <input type="hidden" name="cycle_step_index" value={selectedMissionPlan.index} />
                                                <input type="hidden" name="cycle_step_label" value={selectedMissionPlan.userStep} />
                                            </>
                                        ) : null}
                                        {missionForSelectedStep ? <input type="hidden" name="mission_instance_id" value={missionForSelectedStep.id} /> : null}
                                        <div>
                                            <div className="flex flex-wrap items-center justify-between gap-2">
                                                <h3 className="font-semibold">{editingTreasure ? 'ویرایش گنج' : 'گنج'}</h3>
                                                {editingTreasure ? (
                                                    <Button type="button" variant="ghost" size="sm" onClick={() => setEditingTreasure(null)}>
                                                        لغو ویرایش
                                                    </Button>
                                                ) : null}
                                            </div>
                                            <p className="mt-1 text-xs text-muted-foreground">گنج مرحله‌ای، پنهان یا نهایی را به چرخه کاربر و لحظه کشف وصل کنید.</p>
                                        </div>
                                        <div className="grid gap-1.5">
                                            <label htmlFor="treasure_name" className="text-xs font-medium">نام گنج</label>
                                            <input key={`treasure-name-${editingTreasure?.id ?? selectedMissionPlan?.index ?? 'none'}-${selectedTreasureTier}`} id="treasure_name" name="name" required autoComplete="off" defaultValue={editingTreasure?.name ?? (selectedMissionPlan ? `گنج ${selectedMissionPlan.userStep}` : '')} placeholder="مثلا گنج مسیر خانوادگی" className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                            <InputError message={errors.name} />
                                        </div>
                                        <div className="grid gap-2 sm:grid-cols-2">
                                            <div className="grid gap-1.5">
                                                <label htmlFor="treasure_code" className="text-xs font-medium">کد</label>
                                                <input key={`treasure-code-${editingTreasure?.id ?? suggestedTreasureCode}`} id="treasure_code" name="code" required dir="ltr" autoComplete="off" defaultValue={editingTreasure?.code ?? suggestedTreasureCode} className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                                <InputError message={errors.code} />
                                            </div>
                                            <div className="grid gap-1.5">
                                                <label htmlFor="treasure_type" className="text-xs font-medium">نوع</label>
                                                <select key={`treasure-type-${editingTreasure?.id ?? 'new'}`} id="treasure_type" name="treasure_type" defaultValue={editingTreasure?.treasureType ?? (selectedMissionPlan && selectedMissionPlan.index < missionPlan.length ? 'stage_treasure' : 'final_treasure')} className="h-9 rounded-md border border-input bg-background px-3 text-sm">
                                                    <option value="stage_treasure">گنج مرحله‌ای</option>
                                                    <option value="hidden_treasure">گنج پنهان</option>
                                                    <option value="final_treasure">گنج نهایی</option>
                                                    <option value="sponsor_treasure">گنج اسپانسری</option>
                                                    <option value="family_treasure">گنج خانوادگی</option>
                                                </select>
                                                <InputError message={errors.treasure_type} />
                                            </div>
                                        </div>
                                        <div className="grid gap-2 sm:grid-cols-2">
                                            <div className="grid gap-1.5">
                                                <label htmlFor="treasure_tier" className="text-xs font-medium">سطح گنج</label>
                                                <select id="treasure_tier" name="treasure_tier" value={selectedTreasureTier} onChange={(event) => setSelectedTreasureTier(event.target.value)} className="h-9 rounded-md border border-input bg-background px-3 text-sm">
                                                    {(rewardDesignTiers.length > 0 ? rewardDesignTiers : rewardTierOptions).map((tier) => (
                                                        <option key={'tierKey' in tier ? tier.tierKey : tier.value} value={'tierKey' in tier ? tier.tierKey : tier.value}>
                                                            {'tierKey' in tier ? tier.level : tier.label}
                                                        </option>
                                                    ))}
                                                </select>
                                                <InputError message={errors.treasure_tier} />
                                            </div>
                                            <div className="grid gap-1.5">
                                                <label className="text-xs font-medium">اتصال به مأموریت همین گام</label>
                                                <div className="flex h-9 items-center rounded-md border border-input bg-muted/30 px-3 text-sm text-muted-foreground">
                                                    {missionForSelectedStep ? missionForSelectedStep.title ?? missionForSelectedStep.code : 'ابتدا مأموریت همین گام را ثبت کنید'}
                                                </div>
                                                <InputError message={errors.mission_instance_id} />
                                            </div>
                                        </div>
                                        {selectedTreasureDesignTier ? (
                                            <div className="rounded-md bg-muted/40 px-3 py-2 text-xs text-muted-foreground">
                                                <p className="font-medium text-foreground">ایده‌های سطح {selectedTreasureDesignTier.level}</p>
                                                <div className="mt-2 flex flex-wrap gap-1.5">
                                                    {selectedTreasureDesignTier.options.slice(0, 4).map((option) => (
                                                        <span key={option} className="rounded-full bg-background px-2 py-1">{option}</span>
                                                    ))}
                                                </div>
                                            </div>
                                        ) : null}
                                        <div className="grid gap-2 sm:grid-cols-2">
                                            <div className="grid gap-1.5">
                                                <label htmlFor="required_completed_missions" className="text-xs font-medium">تعداد مأموریت لازم</label>
                                                <input key={`treasure-required-missions-${editingTreasure?.id ?? 'new'}`} id="required_completed_missions" name="required_completed_missions" type="number" min="0" defaultValue={Number(editingTreasure?.revealRule?.required_completed_missions ?? selectedMissionPlan?.index ?? 1)} className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                            </div>
                                            <div className="grid gap-1.5">
                                                <label htmlFor="required_min_points" className="text-xs font-medium">حداقل امتیاز کشف</label>
                                                <input key={`treasure-required-points-${editingTreasure?.id ?? 'new'}`} id="required_min_points" name="required_min_points" type="number" min="0" defaultValue={Number(editingTreasure?.revealRule?.required_min_points ?? selectedMissionPlan?.suggestedUnlockMinPoints ?? 0)} className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                            </div>
                                        </div>
                                        <div className="grid gap-1.5">
                                            <label htmlFor="reveal_mode" className="text-xs font-medium">روش آشکار شدن</label>
                                            <select key={`treasure-reveal-${editingTreasure?.id ?? 'new'}`} id="reveal_mode" name="reveal_mode" defaultValue={editingTreasure?.revealMode ?? 'after_step_completion'} className="h-9 rounded-md border border-input bg-background px-3 text-sm">
                                                <option value="after_step_completion">بعد از تکمیل گام</option>
                                                <option value="hidden_qr">کشف QR پنهان</option>
                                                <option value="admin_release">آزادسازی توسط ادمین/مجری</option>
                                                <option value="partner_confirmation">تایید فروشگاه/شریک</option>
                                                <option value="random_draw">قرعه‌کشی</option>
                                            </select>
                                        </div>
                                        <textarea key={`treasure-desc-${editingTreasure?.id ?? 'new'}`} name="reveal_description" autoComplete="off" defaultValue={editingTreasure?.revealDescription ?? ''} placeholder="تجربه کشف برای کاربر؛ مثلا پیام، لحظه باز شدن گنج یا جایزه قابل مشاهده" className="min-h-16 rounded-md border border-input bg-background px-3 py-2 text-sm" />
                                        <textarea key={`treasure-hint-${editingTreasure?.id ?? 'new'}`} name="discovery_hint" autoComplete="off" defaultValue={editingTreasure?.discoveryHint ?? ''} placeholder="راهنمای کشف؛ مثلا کجا باید دقت کند، بعد از کدام نشانه یا در کدام نقطه مسیر" className="min-h-16 rounded-md border border-input bg-background px-3 py-2 text-sm" />
                                        <div className="grid gap-1.5">
                                            <label htmlFor="treasure_status" className="text-xs font-medium">وضعیت</label>
                                            <select key={`treasure-status-${editingTreasure?.id ?? 'new'}`} id="treasure_status" name="status" defaultValue={editingTreasure?.status ?? 'draft'} className="h-9 rounded-md border border-input bg-background px-3 text-sm">
                                                <option value="draft">پیش‌نویس</option>
                                                <option value="active">فعال</option>
                                                <option value="inactive">غیرفعال</option>
                                            </select>
                                        </div>
                                        <Button disabled={processing}>
                                            <MapPin className="size-4" />
                                            {editingTreasure ? 'ذخیره ویرایش گنج' : 'ثبت گنج چرخه'}
                                        </Button>
                                    </>
                                )}
                            </Form>
                        </div>
                    </section>
                ) : selectedCampaign ? null : (
                    <section className="rounded-lg border border-dashed border-amber-300 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-100">
                        برای ثبت مأموریت، پاداش و گنج، ابتدا از ساخت کمپین یا فهرست کمپین‌ها یک کمپین مشخص را انتخاب کنید.
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
                                        {mission.cycleStep?.label ? (
                                            <p className="mt-1 truncate text-xs text-muted-foreground">
                                                گام چرخه: {mission.cycleStep.index?.toLocaleString('fa-IR') ?? '-'} · {mission.cycleStep.label}
                                            </p>
                                        ) : null}
                                        {mission.visitorInstruction ? (
                                            <p className="mt-1 line-clamp-2 text-xs text-muted-foreground">
                                                راهنمای کاربر: {mission.visitorInstruction}
                                            </p>
                                        ) : null}
                                        {mission.completionEvidence ? (
                                            <p className="mt-1 text-xs text-muted-foreground">
                                                مدرک انجام: {mission.completionEvidence}
                                            </p>
                                        ) : null}
                                        {canMutate ? (
                                            <div className="mt-2 flex items-center gap-1.5">
                                                <Button type="button" variant="ghost" size="sm" className="h-8 px-2" onClick={() => startEditMission(mission)} title="ویرایش مأموریت">
                                                    <Pencil className="size-4" />
                                                </Button>
                                                <Form
                                                    action={`/admin/missions/${mission.id}`}
                                                    method="delete"
                                                    options={{ preserveScroll: true }}
                                                    onSubmit={(event) => {
                                                        if (!window.confirm('این مأموریت از لیست مرحله ۳ حذف شود؟')) {
                                                            event.preventDefault();
                                                        }
                                                    }}
                                                >
                                                    {({ processing }) => (
                                                        <Button type="submit" variant="ghost" size="sm" className="h-8 px-2 text-destructive hover:text-destructive" disabled={processing} title="حذف مأموریت">
                                                            <Trash2 className="size-4" />
                                                        </Button>
                                                    )}
                                                </Form>
                                            </div>
                                        ) : null}
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

                <section className="rounded-lg border border-sidebar-border/70 bg-background dark:border-sidebar-border">
                    <div className="flex flex-col gap-3 border-b border-sidebar-border/70 px-4 py-3 dark:border-sidebar-border lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p className="text-xs text-muted-foreground">مرحله ۴: مشارکت فروشگاه و اسپانسر</p>
                            <h2 className="mt-1 font-semibold">میز بازبینی پیشنهادهای مشارکت</h2>
                            <p className="mt-1 text-sm text-muted-foreground">
                                هر پیشنهاد بر اساس گام چرخه، سطح پاداش و گزینه پیشنهادی بررسی می‌شود تا ادمین بداند کدام جایزه برای کدام بخش کمپین قابل اجراست.
                            </p>
                        </div>
                        <div className="grid grid-cols-3 gap-2 text-xs sm:min-w-[360px]">
                            <div className="rounded-md bg-muted/45 px-3 py-2">
                                <p className="text-muted-foreground">در انتظار</p>
                                <p className="mt-1 font-semibold">{pendingPartnerOffers.length.toLocaleString('fa-IR')}</p>
                            </div>
                            <div className="rounded-md bg-muted/45 px-3 py-2">
                                <p className="text-muted-foreground">تایید شده</p>
                                <p className="mt-1 font-semibold">{approvedPartnerOffers.length.toLocaleString('fa-IR')}</p>
                            </div>
                            <div className="rounded-md bg-muted/45 px-3 py-2">
                                <p className="text-muted-foreground">نیازمند اصلاح</p>
                                <p className="mt-1 font-semibold">{revisionPartnerOffers.length.toLocaleString('fa-IR')}</p>
                            </div>
                        </div>
                    </div>

                    {partnerRewardOffers.length === 0 ? (
                        <div className="p-6 text-sm text-muted-foreground">
                            هنوز پیشنهادی از فروشگاه یا اسپانسر برای این کمپین ثبت نشده است. از نوار همین کمپین وارد بخش «اعضا و مشارکت‌کنندگان» یا پنل فروشگاه/اسپانسر شوید و پیشنهاد مرحله ۴ را ثبت کنید.
                        </div>
                    ) : (
                        <div className="grid gap-3 p-4 xl:grid-cols-2">
                            {partnerRewardOffers.map((reward) => (
                                <article key={`partner-review-${reward.id}`} className="rounded-lg border border-border/80 bg-card/75 p-4 text-sm shadow-sm">
                                    <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                        <div className="min-w-0">
                                            <div className="flex flex-wrap items-center gap-2">
                                                <span className="font-semibold">{reward.name}</span>
                                                <span className={`rounded-full px-2.5 py-1 text-xs font-medium ${statusClasses[reward.approvalStatus] ?? statusClasses[reward.status] ?? statusClasses.inactive}`}>
                                                    {statusLabels[reward.approvalStatus] ?? statusLabels[reward.status] ?? reward.status}
                                                </span>
                                            </div>
                                            <p className="mt-1 text-xs text-muted-foreground" dir="ltr">{reward.code}</p>
                                        </div>
                                        <div className="flex flex-wrap gap-2 text-xs">
                                            <span className="rounded-full bg-muted px-2.5 py-1">
                                                گام {reward.cycleStep.index?.toLocaleString('fa-IR') ?? '-'}: {reward.cycleStep.label ?? 'بدون گام'}
                                            </span>
                                            <span className="rounded-full bg-primary/10 px-2.5 py-1 text-primary">
                                                سطح {reward.rewardTier ? rewardTierLabels[reward.rewardTier] ?? reward.rewardTier : 'نامشخص'}
                                            </span>
                                        </div>
                                    </div>

                                    <div className="mt-3 grid gap-2 rounded-md bg-muted/35 p-3 text-xs text-muted-foreground md:grid-cols-2">
                                        <p><span className="font-medium text-foreground">فروشگاه/اسپانسر: </span>{reward.partner?.name ?? 'نامشخص'}</p>
                                        <p><span className="font-medium text-foreground">گزینه/ترکیب: </span>{reward.rewardOption ?? 'ثبت نشده'}</p>
                                        <p><span className="font-medium text-foreground">موجودی: </span>{reward.stockQuantity?.toLocaleString('fa-IR') ?? 'نامحدود'}</p>
                                        <p><span className="font-medium text-foreground">اعتبار: </span>{formatDate(reward.availableFrom)} تا {formatDate(reward.availableUntil)}</p>
                                    </div>

                                    {reward.description ? (
                                        <p className="mt-3 text-sm leading-6 text-muted-foreground">{reward.description}</p>
                                    ) : null}
                                    {reward.terms ? (
                                        <p className="mt-2 rounded-md bg-muted/30 px-3 py-2 text-xs text-muted-foreground">شرایط اجرا: {reward.terms}</p>
                                    ) : null}
                                    {reward.reviewNotes ? (
                                        <p className="mt-2 rounded-md bg-amber-50 px-3 py-2 text-xs text-amber-900 dark:bg-amber-950/30 dark:text-amber-100">یادداشت بازبینی: {reward.reviewNotes}</p>
                                    ) : null}

                                    {canMutate && reward.approvalStatus === 'pending_review' ? (
                                        <div className="mt-4 grid gap-3">
                                            <Form action={`/admin/rewards/${reward.id}/approve`} method="post" options={{ preserveScroll: true }} className="grid gap-2 md:grid-cols-[1fr_auto]">
                                                {({ processing }) => (
                                                    <>
                                                        <textarea name="notes" className="min-h-14 rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="یادداشت تایید برای اجرای این پیشنهاد" />
                                                        <Button size="sm" disabled={processing} className="self-end">
                                                            <CheckCircle2 className="size-4" />
                                                            تایید برای اجرا
                                                        </Button>
                                                    </>
                                                )}
                                            </Form>
                                            <div className="grid gap-3 md:grid-cols-2">
                                                <Form action={`/admin/rewards/${reward.id}/revision`} method="post" options={{ preserveScroll: true }} className="grid gap-2">
                                                    {({ processing }) => (
                                                        <>
                                                            <textarea name="notes" className="min-h-14 rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="چه چیزی باید توسط فروشگاه/اسپانسر اصلاح شود؟" />
                                                            <Button size="sm" variant="outline" disabled={processing}>
                                                                <Pencil className="size-4" />
                                                                درخواست اصلاح
                                                            </Button>
                                                        </>
                                                    )}
                                                </Form>
                                                <Form action={`/admin/rewards/${reward.id}/reject`} method="post" options={{ preserveScroll: true }} className="grid gap-2">
                                                    {({ processing }) => (
                                                        <>
                                                            <textarea name="notes" className="min-h-14 rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="دلیل رد پیشنهاد" />
                                                            <Button size="sm" variant="outline" disabled={processing}>
                                                                <XCircle className="size-4" />
                                                                رد پیشنهاد
                                                            </Button>
                                                        </>
                                                    )}
                                                </Form>
                                            </div>
                                        </div>
                                    ) : null}
                                </article>
                            ))}
                        </div>
                    )}
                </section>

                <section className="grid gap-4 lg:grid-cols-2">
                    <div className="rounded-lg border border-sidebar-border/70 bg-background dark:border-sidebar-border">
                        <div className="border-b border-sidebar-border/70 px-4 py-3 dark:border-sidebar-border">
                            <h2 className="font-semibold">پاداش‌های داخلی و نهایی کمپین</h2>
                            <p className="mt-1 text-xs text-muted-foreground">
                                پیشنهادهای فروشگاه/اسپانسر در میز بازبینی مرحله ۴ مدیریت می‌شوند؛ این بخش برای پاداش‌هایی است که ادمین مستقیم تعریف یا نهایی می‌کند.
                            </p>
                        </div>
                        <div className="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                            {directRewardDefinitions.length === 0 ? (
                                <div className="p-6 text-sm text-muted-foreground">
                                    هنوز پاداش داخلی یا نهایی جدا از پیشنهادهای مشارکت ثبت نشده است.
                                </div>
                            ) : directRewardDefinitions.map((reward) => (
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
                                            {reward.source ? (
                                                <span className={`mt-2 inline-flex rounded-full px-2.5 py-1 text-xs font-medium ${sourceClasses[reward.source] ?? 'bg-muted text-muted-foreground'}`}>
                                                    {rewardSourceLabels[reward.source] ?? reward.source}
                                                </span>
                                            ) : null}
                                            {reward.cycleStep?.label ? (
                                                <p className="mt-1 truncate text-xs text-muted-foreground">
                                                    گام چرخه: {reward.cycleStep.index?.toLocaleString('fa-IR') ?? '-'} · {reward.cycleStep.label}
                                                </p>
                                            ) : null}
                                            {canMutate ? (
                                                <div className="mt-2 flex items-center gap-1.5">
                                                    <Button type="button" variant="ghost" size="sm" className="h-8 px-2" onClick={() => startEditReward(reward)} title="ویرایش پاداش">
                                                        <Pencil className="size-4" />
                                                    </Button>
                                                    <Form
                                                        action={`/admin/rewards/${reward.id}`}
                                                        method="delete"
                                                        options={{ preserveScroll: true }}
                                                        onSubmit={(event) => {
                                                            if (!window.confirm('این پاداش از لیست مرحله ۳ حذف شود؟')) {
                                                                event.preventDefault();
                                                            }
                                                        }}
                                                    >
                                                        {({ processing }) => (
                                                            <Button type="submit" variant="ghost" size="sm" className="h-8 px-2 text-destructive hover:text-destructive" disabled={processing} title="حذف پاداش">
                                                                <Trash2 className="size-4" />
                                                            </Button>
                                                        )}
                                                    </Form>
                                                </div>
                                            ) : null}
                                        </div>
                                        <div className="flex shrink-0 items-center gap-2">
                                            <span
                                                className={`rounded-full px-2.5 py-1 text-xs font-medium ${
                                                    statusClasses[
                                                        reward.approvalStatus
                                                    ] ??
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
                                    {reward.rewardOption ? (
                                        <p className="line-clamp-2 text-xs text-muted-foreground">
                                            گزینه انتخابی: {reward.rewardOption}
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
                                    <p className="text-xs text-muted-foreground">
                                        اعتبار: {formatDate(reward.availableFrom)} تا {formatDate(reward.availableUntil)}
                                        {reward.fulfillmentWindow ? ` · تحویل: ${reward.fulfillmentWindow}` : ''}
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
                                                action={`/admin/rewards/${reward.id}/revision`}
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
                                                            placeholder="چه چیزی باید توسط فروشگاه/اسپانسر اصلاح یا شفاف‌تر شود؟"
                                                        />
                                                        <div className="flex items-end">
                                                            <Button
                                                                size="sm"
                                                                variant="outline"
                                                                disabled={processing}
                                                            >
                                                                <Pencil className="size-4" />
                                                                درخواست اصلاح
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
                            <p className="mt-1 text-xs text-muted-foreground">
                                گنج با پاداش فرق دارد: گنج لحظه کشف، پیام، قفل‌گشایی یا جایزه پنهان مسیر است و باید به ماموریت و گام چرخه وصل بماند.
                            </p>
                        </div>
                        <div className="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                            {treasures.length === 0 ? (
                                <div className="p-6 text-sm text-muted-foreground">
                                    هنوز گنجی برای چرخه این کمپین ثبت نشده است. از فرم گنج مرحله ۳، گنج را به ماموریت همان گام وصل کنید.
                                </div>
                            ) : treasures.map((treasure) => (
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
                                            {treasure.source ? (
                                                <span className={`mt-2 inline-flex rounded-full px-2.5 py-1 text-xs font-medium ${sourceClasses[treasure.source] ?? 'bg-muted text-muted-foreground'}`}>
                                                    {rewardSourceLabels[treasure.source] ?? treasure.source}
                                                </span>
                                            ) : null}
                                            {canMutate ? (
                                                <div className="mt-2 flex items-center gap-1.5">
                                                    <Button type="button" variant="ghost" size="sm" className="h-8 px-2" onClick={() => startEditTreasure(treasure)} title="ویرایش گنج">
                                                        <Pencil className="size-4" />
                                                    </Button>
                                                    <Form
                                                        action={`/admin/treasures/${treasure.id}`}
                                                        method="delete"
                                                        options={{ preserveScroll: true }}
                                                        onSubmit={(event) => {
                                                            if (!window.confirm('این گنج از لیست مرحله ۳ حذف شود؟')) {
                                                                event.preventDefault();
                                                            }
                                                        }}
                                                    >
                                                        {({ processing }) => (
                                                            <Button type="submit" variant="ghost" size="sm" className="h-8 px-2 text-destructive hover:text-destructive" disabled={processing} title="حذف گنج">
                                                                <Trash2 className="size-4" />
                                                            </Button>
                                                        )}
                                                    </Form>
                                                </div>
                                            ) : null}
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
                                    <div className="grid gap-2 text-xs text-muted-foreground sm:grid-cols-3">
                                        <p>
                                            گام:{' '}
                                            <span className="font-medium text-foreground">
                                                {treasure.cycleStep.label ?? '-'}
                                            </span>
                                        </p>
                                        <p>
                                            سطح:{' '}
                                            <span className="font-medium text-foreground">
                                                {treasure.treasureTier ? rewardTierLabels[treasure.treasureTier] ?? treasure.treasureTier : '-'}
                                            </span>
                                        </p>
                                        <p>
                                            روش کشف:{' '}
                                            <span className="font-medium text-foreground">
                                                {treasure.revealMode ?? '-'}
                                            </span>
                                        </p>
                                    </div>
                                    {treasure.revealDescription || treasure.discoveryHint ? (
                                        <div className="rounded-md bg-muted/40 px-3 py-2 text-xs text-muted-foreground">
                                            {treasure.revealDescription ? <p>{treasure.revealDescription}</p> : null}
                                            {treasure.discoveryHint ? <p className="mt-1">راهنما: {treasure.discoveryHint}</p> : null}
                                        </div>
                                    ) : null}
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
    title: 'مأموریت‌ها و پاداش‌ها',
            href: '/admin/missions',
        },
    ],
};
