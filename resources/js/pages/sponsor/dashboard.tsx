import { Form, Head, Link, usePage } from '@inertiajs/react';
import {
    BadgeDollarSign,
    CalendarClock,
    FileCheck2,
    Gift,
    Megaphone,
    MonitorPlay,
    Pencil,
    Plus,
    RadioTower,
    Send,
    Store,
    Trash2,
} from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';

type RegistryEntity = { id: string; code: string; name: string };
type CampaignOption = RegistryEntity & {
    status: string;
    venueName: string | null;
};
type PartnerOption = RegistryEntity & {
    partnerType: string;
    status: string;
    venueName: string | null;
};
type HubOption = RegistryEntity;

type Sponsor = RegistryEntity & {
    sponsorType: string;
    status: string;
    contactName: string | null;
    contactMobile: string | null;
    websiteUrl: string | null;
    venueName: string | null;
};

type SponsorProposal = {
    id: string;
    code: string;
    title: string;
    proposalType: string;
    objective: string;
    status: string;
    proposedBudgetAmount: number;
    estimatedValueAmount: number;
    rewardOffer: string | null;
    discountOffer: string | null;
    assetUrl: string | null;
    targetAudience: string | null;
    notes: string | null;
    reviewNotes: string | null;
    campaign: (RegistryEntity & { status: string }) | null;
    preferredPartner: PartnerOption | null;
    partners: PartnerOption[];
    items: SponsorProposalItem[];
};

type SponsorProposalItem = {
    id: string;
    itemType: string;
    title: string;
    quantity: number;
    estimatedUnitValueAmount: number;
    targetPartnerAccountIds: string[];
    partnerAllocations: Array<{ partner_account_id: string; quantity: number }>;
    description: string | null;
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
    placementTypes: string[];
    creativeType: string | null;
    assetUrl: string | null;
};

type Props = {
    sponsor: Sponsor;
    stats: {
        proposals: number;
        pendingProposals: number;
        approvedProposals: number;
        revisionRequested: number;
        adRequests: number;
        pendingAds: number;
        approvedAds: number;
    };
    proposals: SponsorProposal[];
    adRequests: AdRequest[];
    formOptions: {
        campaigns: CampaignOption[];
        partners: PartnerOption[];
        adHubs: HubOption[];
    };
};

type SharedProps = { flash?: { success?: string } };
type FormErrorBag = Record<string, string | undefined>;

const statusLabels: Record<string, string> = {
    active: 'فعال',
    draft: 'پیش‌نویس',
    pending_review: 'در انتظار بررسی',
    approved: 'تأیید شده',
    rejected: 'رد شده',
    revision_requested: 'نیازمند اصلاح',
};

const proposalTypeLabels: Record<string, string> = {
    campaign_sponsorship: 'حمایت کمپین',
    reward_offer: 'جایزه پیشنهادی',
    discount_offer: 'تخفیف/کد خرید',
    display_media: 'تبلیغ نمایشگر',
    family_challenge: 'چالش خانوادگی',
    scientific_cultural_content: 'محتوای علمی/فرهنگی',
    product_sampling: 'نمونه‌گیری محصول',
};

const placementLabels: Record<string, string> = {
    public_feed: 'ویترین عمومی فروشگاه‌ها',
    fixed_display: 'نمایشگر ثابت',
    mobile_display: 'نمایشگر سیار',
    qr_landing: 'صفحه QR',
    reward_page: 'صفحه پاداش',
    map_route: 'نقشه و مسیر بازی',
    post_mission: 'پس از ماموریت',
};

const onlinePlacementOptions = [
    ['public_feed', placementLabels.public_feed],
    ['qr_landing', placementLabels.qr_landing],
    ['reward_page', placementLabels.reward_page],
    ['map_route', placementLabels.map_route],
    ['post_mission', placementLabels.post_mission],
] as const;

type ProposalItemForm = {
    item_type: string;
    title?: string;
    quantity?: number | null;
    estimated_unit_value_amount?: number | null;
    target_partner_account_ids?: string[];
    partner_allocations?: Array<{
        partner_account_id: string;
        quantity: number;
    }>;
    description?: string | null;
};

function newDefaultProposalItem(): ProposalItemForm {
    return {
        item_type: 'reward',
        target_partner_account_ids: [],
        partner_allocations: [],
    };
}

function fa(value: number) {
    return value.toLocaleString('fa-IR');
}

function money(value: number) {
    return value > 0 ? `${fa(value)} تومان` : '-';
}

function placementText(placementTypes: string[], fallback: string | null) {
    const types = placementTypes.length > 0 ? placementTypes : [fallback ?? ''];

    return types
        .filter(Boolean)
        .map((type) => placementLabels[type] ?? type)
        .join('، ');
}

function formatDate(value: string | null) {
    if (!value) {
        return 'بدون محدودیت';
    }

    return new Intl.DateTimeFormat('fa-IR', {
        dateStyle: 'medium',
    }).format(new Date(value));
}

function label(map: Record<string, string>, value: string) {
    return map[value] ?? value;
}

function errorAt(errors: FormErrorBag, field: string) {
    return errors[field];
}

function itemError(errors: FormErrorBag, index: number) {
    return (
        errors[`items.${index}.quantity`] ??
        errors[`items.${index}.target_partner_account_ids`] ??
        errors[`items.${index}.partner_allocations`] ??
        errors[`items.${index}.title`] ??
        errors.items
    );
}

function ProposalSummary({
    proposal,
    onEdit,
}: {
    proposal: SponsorProposal;
    onEdit: (proposal: SponsorProposal) => void;
}) {
    return (
        <article className="grid gap-3 px-4 py-3 text-sm">
            <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <div className="min-w-0">
                    <div className="flex min-w-0 items-center gap-2">
                        <FileCheck2 className="size-4 shrink-0 text-muted-foreground" />
                        <p className="truncate font-medium">{proposal.title}</p>
                    </div>
                    {proposal.reviewNotes ? (
                        <p className="mt-1 line-clamp-2 text-xs text-orange-700 dark:text-orange-300">
                            یادداشت اصلاح: {proposal.reviewNotes}
                        </p>
                    ) : null}
                </div>
                <span className="w-fit shrink-0 rounded-full bg-muted px-2.5 py-1 text-xs">
                    {label(statusLabels, proposal.status)}
                </span>
            </div>

            <dl className="grid gap-2 text-xs text-muted-foreground sm:grid-cols-3">
                <div className="flex items-center gap-2">
                    <Megaphone className="size-4 shrink-0" />
                    <span>
                        {label(proposalTypeLabels, proposal.proposalType)}
                    </span>
                </div>
                <div className="flex items-center gap-2">
                    <Store className="size-4 shrink-0" />
                    <span className="truncate">
                        {proposal.partners?.length
                            ? `${fa(proposal.partners.length)} واحد`
                            : (proposal.campaign?.name ??
                              proposal.preferredPartner?.name ??
                              'انتخاب با ادمین')}
                    </span>
                </div>
                <div className="flex items-center gap-2">
                    <Gift className="size-4 shrink-0" />
                    <span>
                        {proposal.items?.length
                            ? `${fa(proposal.items.length)} آیتم`
                            : money(
                                  proposal.proposedBudgetAmount ||
                                      proposal.estimatedValueAmount,
                              )}
                    </span>
                </div>
            </dl>

            {proposal.status === 'revision_requested' ? (
                <div>
                    <Button
                        type="button"
                        variant="secondary"
                        size="sm"
                        onClick={() => onEdit(proposal)}
                    >
                        <Pencil className="size-4" />
                        اصلاح
                    </Button>
                </div>
            ) : null}
        </article>
    );
}

export default function SponsorDashboard({
    sponsor,
    stats,
    proposals,
    adRequests,
    formOptions,
}: Props) {
    const { flash } = usePage<SharedProps>().props;
    const [proposalItems, setProposalItems] = useState([
        newDefaultProposalItem(),
    ]);
    const [editingProposal, setEditingProposal] =
        useState<SponsorProposal | null>(null);

    const addProposalItem = () => {
        setProposalItems((items) => [...items, newDefaultProposalItem()]);
    };

    const removeProposalItem = (index: number) => {
        setProposalItems((items) =>
            items.filter((_, itemIndex) => itemIndex !== index),
        );
    };

    const startEditProposal = (proposal: SponsorProposal) => {
        setEditingProposal(proposal);
        setProposalItems(
            proposal.items.length > 0
                ? proposal.items.map((item) => ({
                      item_type: item.itemType,
                      title: item.title,
                      quantity: item.quantity || null,
                      estimated_unit_value_amount:
                          item.estimatedUnitValueAmount || null,
                      target_partner_account_ids:
                          item.targetPartnerAccountIds ?? [],
                      partner_allocations: item.partnerAllocations ?? [],
                      description: item.description,
                  }))
                : [newDefaultProposalItem()],
        );
        document
            .getElementById('sponsor-proposal-form')
            ?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    const cancelEditProposal = () => {
        setEditingProposal(null);
        setProposalItems([newDefaultProposalItem()]);
    };

    const allocationQuantity = (item: ProposalItemForm, partnerId: string) =>
        item.partner_allocations?.find(
            (allocation) => allocation.partner_account_id === partnerId,
        )?.quantity ?? '';

    return (
        <>
            <Head title="پنل اسپانسر" />
            <div
                dir="rtl"
                className="flex h-full min-w-0 flex-1 flex-col gap-5 overflow-x-hidden p-3 sm:p-4"
            >
                <header className="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div className="min-w-0">
                        <p className="text-sm text-muted-foreground">
                            پنل خوداظهاری و پیشنهاد اسپانسری
                        </p>
                        <h1 className="mt-1 text-2xl leading-tight font-semibold">
                            پنل اسپانسر
                        </h1>
                        <p className="mt-2 text-sm text-muted-foreground">
                            {sponsor.name}{' '}
                            {sponsor.venueName ? `- ${sponsor.venueName}` : ''}
                        </p>
                    </div>
                    <div className="grid w-full grid-cols-2 gap-2 text-sm md:w-auto xl:grid-cols-4">
                        {[
                            ['کل پیشنهادها', stats.proposals],
                            ['در انتظار بررسی', stats.pendingProposals],
                            ['تأیید شده', stats.approvedProposals],
                            ['نیازمند اصلاح', stats.revisionRequested],
                        ].map(([title, value]) => (
                            <div
                                key={title}
                                className="min-w-0 rounded-lg border border-border/80 bg-card/80 px-3 py-2 shadow-sm"
                            >
                                <p className="text-xs leading-5 text-muted-foreground">
                                    {title}
                                </p>
                                <p className="mt-1 font-semibold">
                                    {fa(Number(value))}
                                </p>
                            </div>
                        ))}
                    </div>
                </header>

                {flash?.success ? (
                    <section className="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-100">
                        {flash.success}
                    </section>
                ) : null}

                <section className="rounded-lg border border-primary/25 bg-primary/5 px-4 py-3 text-sm text-muted-foreground">
                    پنل اسپانسر برای ثبت و اصلاح پیشنهاد حمایت، بسته پاداش،
                    تبلیغ برند و تخصیص پیشنهادی به واحدهای اجراست. تایید نهایی
                    کمپین، قیمت‌گذاری عملیاتی، زمان‌بندی نمایش و تحویل پاداش در
                    پنل‌های ادمین، هاب و فروشگاه انجام می‌شود.
                </section>

                <section className="grid min-w-0 gap-4 xl:grid-cols-[minmax(17rem,0.8fr)_minmax(0,1.2fr)]">
                    <div className="exploria-panel">
                        <div className="border-b border-border/70 px-4 py-3 dark:border-sidebar-border">
                            <div className="flex items-center gap-2">
                                <BadgeDollarSign className="size-4 text-muted-foreground" />
                                <h2 className="font-semibold">حساب اسپانسر</h2>
                            </div>
                        </div>
                        <div className="grid gap-3 p-4 text-sm">
                            <p>
                                <span className="text-muted-foreground">
                                    وضعیت:{' '}
                                </span>
                                {label(statusLabels, sponsor.status)}
                            </p>
                            <p>
                                <span className="text-muted-foreground">
                                    مسئول تماس:{' '}
                                </span>
                                {sponsor.contactName ?? '-'}
                            </p>
                            <p>
                                <span className="text-muted-foreground">
                                    موبایل:{' '}
                                </span>
                                <span dir="ltr">
                                    {sponsor.contactMobile ?? '-'}
                                </span>
                            </p>
                            {sponsor.websiteUrl ? (
                                <Link
                                    className="text-primary underline"
                                    href={sponsor.websiteUrl}
                                >
                                    وب‌سایت / دارایی برند
                                </Link>
                            ) : null}
                        </div>
                    </div>

                    <div id="sponsor-proposal-form" className="exploria-panel">
                        <div className="border-b border-border/70 px-4 py-3 dark:border-sidebar-border">
                            <div className="flex flex-wrap items-center justify-between gap-3">
                                <div className="flex items-center gap-2">
                                    <Send className="size-4 text-muted-foreground" />
                                    <h2 className="font-semibold">
                                        {editingProposal
                                            ? 'اصلاح پیشنهاد اسپانسری'
                                            : 'ارسال پیشنهاد اسپانسری'}
                                    </h2>
                                </div>
                                {editingProposal ? (
                                    <Button
                                        type="button"
                                        variant="secondary"
                                        size="sm"
                                        onClick={cancelEditProposal}
                                    >
                                        انصراف از اصلاح
                                    </Button>
                                ) : null}
                            </div>
                            <p className="mt-1 text-sm text-muted-foreground">
                                {editingProposal
                                    ? 'پیشنهاد برگشتی را اصلاح کنید و برای بررسی دوباره ادمین بفرستید.'
                                    : 'پیشنهاد شما برای ادمین ارسال می‌شود و بعد از بررسی می‌تواند به کمپین، جایزه، تبلیغ یا واحد عضو وصل شود.'}
                            </p>
                        </div>
                        <Form
                            key={editingProposal?.id ?? 'new-proposal'}
                            action={
                                editingProposal
                                    ? `/sponsor/proposals/${editingProposal.id}`
                                    : '/sponsor/proposals'
                            }
                            method={editingProposal ? 'patch' : 'post'}
                            options={{ preserveScroll: true }}
                            className="grid min-w-0 gap-4 p-4 md:grid-cols-2"
                        >
                            {({ processing, errors }) => (
                                <>
                                    {Object.keys(errors).length > 0 ? (
                                        <div className="rounded-md border border-destructive/30 bg-destructive/10 px-3 py-2 text-sm text-destructive md:col-span-2">
                                            <p className="font-medium">
                                                فرم ارسال نشد؛ موارد مشخص‌شده را
                                                اصلاح کنید.
                                            </p>
                                            {Object.values(errors)[0] ? (
                                                <p className="mt-1 text-xs">
                                                    {Object.values(errors)[0]}
                                                </p>
                                            ) : null}
                                        </div>
                                    ) : null}
                                    <div className="rounded-md bg-muted/40 px-3 py-2 md:col-span-2">
                                        <h3 className="text-sm font-semibold">
                                            اطلاعات کل بسته پیشنهادی
                                        </h3>
                                    </div>
                                    <div className="grid gap-1.5">
                                        <label
                                            htmlFor="title"
                                            className="text-xs font-medium"
                                        >
                                            عنوان پیشنهاد
                                        </label>
                                        <input
                                            id="title"
                                            name="title"
                                            defaultValue={
                                                editingProposal?.title ?? ''
                                            }
                                            required
                                            className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                        />
                                        <InputError message={errors.title} />
                                    </div>
                                    <div className="grid gap-1.5">
                                        <label
                                            htmlFor="proposal_type"
                                            className="text-xs font-medium"
                                        >
                                            نوع پیشنهاد
                                        </label>
                                        <select
                                            id="proposal_type"
                                            name="proposal_type"
                                            defaultValue={
                                                editingProposal?.proposalType ??
                                                'campaign_sponsorship'
                                            }
                                            className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                        >
                                            <option value="campaign_sponsorship">
                                                حمایت کمپین
                                            </option>
                                            <option value="reward_offer">
                                                جایزه پیشنهادی
                                            </option>
                                            <option value="discount_offer">
                                                تخفیف/کد خرید
                                            </option>
                                            <option value="display_media">
                                                تبلیغ نمایشگر
                                            </option>
                                            <option value="family_challenge">
                                                چالش خانوادگی
                                            </option>
                                            <option value="scientific_cultural_content">
                                                محتوای علمی/فرهنگی
                                            </option>
                                            <option value="product_sampling">
                                                نمونه‌گیری محصول
                                            </option>
                                        </select>
                                        <InputError
                                            message={errors.proposal_type}
                                        />
                                    </div>
                                    <div className="grid gap-1.5">
                                        <label
                                            htmlFor="objective"
                                            className="text-xs font-medium"
                                        >
                                            هدف
                                        </label>
                                        <select
                                            id="objective"
                                            name="objective"
                                            defaultValue={
                                                editingProposal?.objective ??
                                                'engagement'
                                            }
                                            className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                        >
                                            <option value="awareness">
                                                دیده‌شدن برند
                                            </option>
                                            <option value="footfall">
                                                افزایش مراجعه
                                            </option>
                                            <option value="lead_generation">
                                                جذب لید
                                            </option>
                                            <option value="sales">فروش</option>
                                            <option value="engagement">
                                                تعامل
                                            </option>
                                            <option value="social_impact">
                                                اثر فرهنگی/اجتماعی
                                            </option>
                                        </select>
                                        <InputError
                                            message={errors.objective}
                                        />
                                    </div>
                                    <div className="grid gap-1.5">
                                        <label
                                            htmlFor="campaign_id"
                                            className="text-xs font-medium"
                                        >
                                            کمپین مورد علاقه
                                        </label>
                                        <select
                                            id="campaign_id"
                                            name="campaign_id"
                                            defaultValue={
                                                editingProposal?.campaign?.id ??
                                                ''
                                            }
                                            className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                        >
                                            <option value="">
                                                ادمین انتخاب کند
                                            </option>
                                            {formOptions.campaigns.map(
                                                (campaign) => (
                                                    <option
                                                        key={campaign.id}
                                                        value={campaign.id}
                                                    >
                                                        {campaign.name}
                                                    </option>
                                                ),
                                            )}
                                        </select>
                                        <InputError
                                            message={errors.campaign_id}
                                        />
                                    </div>
                                    <div className="grid gap-1.5 md:col-span-2">
                                        <label
                                            htmlFor="partner_account_ids"
                                            className="text-xs font-medium"
                                        >
                                            واحدهای اجرایی پیشنهادی
                                        </label>
                                        <select
                                            id="partner_account_ids"
                                            name="partner_account_ids[]"
                                            multiple
                                            defaultValue={
                                                editingProposal?.partners.map(
                                                    (partner) => partner.id,
                                                ) ?? []
                                            }
                                            className="min-h-28 rounded-md border border-input bg-background px-3 py-2 text-sm"
                                        >
                                            {formOptions.partners.map(
                                                (partner) => (
                                                    <option
                                                        key={partner.id}
                                                        value={partner.id}
                                                    >
                                                        {partner.name}{' '}
                                                        {partner.venueName
                                                            ? `- ${partner.venueName}`
                                                            : ''}
                                                    </option>
                                                ),
                                            )}
                                        </select>
                                        <InputError
                                            message={errors.partner_account_ids}
                                        />
                                    </div>
                                    <div className="grid gap-1.5">
                                        <label
                                            htmlFor="proposed_budget_amount"
                                            className="text-xs font-medium"
                                        >
                                            بودجه پیشنهادی
                                        </label>
                                        <input
                                            id="proposed_budget_amount"
                                            name="proposed_budget_amount"
                                            type="number"
                                            min="0"
                                            defaultValue={
                                                editingProposal?.proposedBudgetAmount ||
                                                ''
                                            }
                                            className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                        />
                                        <InputError
                                            message={
                                                errors.proposed_budget_amount
                                            }
                                        />
                                    </div>
                                    <div className="grid gap-1.5">
                                        <label
                                            htmlFor="estimated_value_amount"
                                            className="text-xs font-medium"
                                        >
                                            ارزش غیرنقدی/جایزه
                                        </label>
                                        <input
                                            id="estimated_value_amount"
                                            name="estimated_value_amount"
                                            type="number"
                                            min="0"
                                            defaultValue={
                                                editingProposal?.estimatedValueAmount ||
                                                ''
                                            }
                                            className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                        />
                                        <InputError
                                            message={
                                                errors.estimated_value_amount
                                            }
                                        />
                                    </div>
                                    <div className="grid gap-1.5">
                                        <label
                                            htmlFor="asset_url"
                                            className="text-xs font-medium"
                                        >
                                            لینک لوگو/بنر/دارایی
                                        </label>
                                        <input
                                            id="asset_url"
                                            name="asset_url"
                                            dir="ltr"
                                            defaultValue={
                                                editingProposal?.assetUrl ?? ''
                                            }
                                            className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                        />
                                        <InputError
                                            message={errors.asset_url}
                                        />
                                    </div>
                                    <div className="grid gap-3 md:col-span-2">
                                        <div className="flex flex-wrap items-center justify-between gap-2 rounded-md bg-muted/40 px-3 py-2">
                                            <h3 className="text-sm font-semibold">
                                                جایزه‌ها، تخفیف‌ها و محصولات
                                                پیشنهادی
                                            </h3>
                                            <Button
                                                type="button"
                                                variant="secondary"
                                                size="sm"
                                                onClick={addProposalItem}
                                            >
                                                <Plus className="size-4" />
                                                افزودن آیتم
                                            </Button>
                                        </div>
                                        <div className="grid gap-3">
                                            {proposalItems.map(
                                                (item, index) => (
                                                    <div
                                                        key={index}
                                                        className="grid min-w-0 gap-3 rounded-md border border-border/80 p-3 sm:grid-cols-2 xl:grid-cols-4"
                                                    >
                                                        <div className="flex items-center justify-between gap-2 md:col-span-4">
                                                            <h4 className="text-sm font-semibold">
                                                                آیتم{' '}
                                                                {fa(index + 1)}
                                                            </h4>
                                                            {itemError(
                                                                errors,
                                                                index,
                                                            ) ? (
                                                                <span className="rounded-full bg-destructive/10 px-2.5 py-1 text-xs text-destructive">
                                                                    نیازمند
                                                                    اصلاح
                                                                </span>
                                                            ) : null}
                                                        </div>
                                                        {itemError(
                                                            errors,
                                                            index,
                                                        ) ? (
                                                            <div className="md:col-span-4">
                                                                <InputError
                                                                    message={itemError(
                                                                        errors,
                                                                        index,
                                                                    )}
                                                                />
                                                            </div>
                                                        ) : null}
                                                        <div className="grid gap-1.5">
                                                            <label
                                                                htmlFor={`items_${index}_item_type`}
                                                                className="text-xs font-medium"
                                                            >
                                                                نوع
                                                            </label>
                                                            <select
                                                                id={`items_${index}_item_type`}
                                                                name={`items[${index}][item_type]`}
                                                                defaultValue={
                                                                    item.item_type
                                                                }
                                                                className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                                            >
                                                                <option value="reward">
                                                                    جایزه
                                                                </option>
                                                                <option value="discount">
                                                                    تخفیف/کد
                                                                    هدیه
                                                                </option>
                                                                <option value="product">
                                                                    محصول
                                                                </option>
                                                                <option value="sample">
                                                                    نمونه رایگان
                                                                </option>
                                                                <option value="media">
                                                                    رسانه/بنر
                                                                </option>
                                                                <option value="content">
                                                                    محتوا
                                                                </option>
                                                                <option value="cash_support">
                                                                    حمایت نقدی
                                                                </option>
                                                            </select>
                                                            <InputError
                                                                message={errorAt(
                                                                    errors,
                                                                    `items.${index}.item_type`,
                                                                )}
                                                            />
                                                        </div>
                                                        <div className="grid gap-1.5 sm:col-span-2 xl:col-span-3">
                                                            <label
                                                                htmlFor={`items_${index}_title`}
                                                                className="text-xs font-medium"
                                                            >
                                                                عنوان آیتم
                                                            </label>
                                                            <input
                                                                id={`items_${index}_title`}
                                                                name={`items[${index}][title]`}
                                                                defaultValue={
                                                                    item.title ??
                                                                    ''
                                                                }
                                                                required={
                                                                    index === 0
                                                                }
                                                                className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                                            />
                                                            <InputError
                                                                message={errorAt(
                                                                    errors,
                                                                    `items.${index}.title`,
                                                                )}
                                                            />
                                                        </div>
                                                        <div className="grid gap-1.5">
                                                            <label
                                                                htmlFor={`items_${index}_quantity`}
                                                                className="text-xs font-medium"
                                                            >
                                                                تعداد کل آیتم
                                                            </label>
                                                            <input
                                                                id={`items_${index}_quantity`}
                                                                name={`items[${index}][quantity]`}
                                                                type="number"
                                                                min="1"
                                                                defaultValue={
                                                                    item.quantity ??
                                                                    ''
                                                                }
                                                                className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                                            />
                                                            <InputError
                                                                message={errorAt(
                                                                    errors,
                                                                    `items.${index}.quantity`,
                                                                )}
                                                            />
                                                        </div>
                                                        <div className="grid gap-1.5">
                                                            <label
                                                                htmlFor={`items_${index}_estimated_unit_value_amount`}
                                                                className="text-xs font-medium"
                                                            >
                                                                ارزش هر واحد
                                                            </label>
                                                            <input
                                                                id={`items_${index}_estimated_unit_value_amount`}
                                                                name={`items[${index}][estimated_unit_value_amount]`}
                                                                type="number"
                                                                min="0"
                                                                defaultValue={
                                                                    item.estimated_unit_value_amount ??
                                                                    ''
                                                                }
                                                                className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                                            />
                                                            <InputError
                                                                message={errorAt(
                                                                    errors,
                                                                    `items.${index}.estimated_unit_value_amount`,
                                                                )}
                                                            />
                                                        </div>
                                                        <div className="grid gap-1.5 sm:col-span-2">
                                                            <label
                                                                htmlFor={`items_${index}_target_partner_account_ids`}
                                                                className="text-xs font-medium"
                                                            >
                                                                واحدهای هدف این
                                                                آیتم
                                                            </label>
                                                            <select
                                                                id={`items_${index}_target_partner_account_ids`}
                                                                name={`items[${index}][target_partner_account_ids][]`}
                                                                multiple
                                                                defaultValue={
                                                                    item.target_partner_account_ids ??
                                                                    []
                                                                }
                                                                className="min-h-20 rounded-md border border-input bg-background px-3 py-2 text-sm"
                                                            >
                                                                {formOptions.partners.map(
                                                                    (
                                                                        partner,
                                                                    ) => (
                                                                        <option
                                                                            key={
                                                                                partner.id
                                                                            }
                                                                            value={
                                                                                partner.id
                                                                            }
                                                                        >
                                                                            {
                                                                                partner.name
                                                                            }
                                                                        </option>
                                                                    ),
                                                                )}
                                                            </select>
                                                            <InputError
                                                                message={errorAt(
                                                                    errors,
                                                                    `items.${index}.target_partner_account_ids`,
                                                                )}
                                                            />
                                                        </div>
                                                        <div className="grid gap-2 sm:col-span-2 xl:col-span-4">
                                                            <p className="text-xs font-medium">
                                                                سهم هر واحد از
                                                                این آیتم
                                                            </p>
                                                            <div className="grid gap-2 lg:grid-cols-2">
                                                                {formOptions.partners.map(
                                                                    (
                                                                        partner,
                                                                        partnerIndex,
                                                                    ) => (
                                                                        <div
                                                                            key={
                                                                                partner.id
                                                                            }
                                                                            className="grid grid-cols-1 gap-2 rounded-md border border-border/70 px-3 py-2 sm:grid-cols-[minmax(0,1fr)_7rem] sm:items-center"
                                                                        >
                                                                            <span className="truncate text-xs text-muted-foreground">
                                                                                {
                                                                                    partner.name
                                                                                }
                                                                            </span>
                                                                            <input
                                                                                type="hidden"
                                                                                name={`items[${index}][partner_allocations][${partnerIndex}][partner_account_id]`}
                                                                                value={
                                                                                    partner.id
                                                                                }
                                                                            />
                                                                            <input
                                                                                name={`items[${index}][partner_allocations][${partnerIndex}][quantity]`}
                                                                                type="number"
                                                                                min="1"
                                                                                placeholder="تعداد"
                                                                                defaultValue={allocationQuantity(
                                                                                    item,
                                                                                    partner.id,
                                                                                )}
                                                                                className="h-8 rounded-md border border-input bg-background px-2 text-sm"
                                                                            />
                                                                        </div>
                                                                    ),
                                                                )}
                                                            </div>
                                                            <InputError
                                                                message={errorAt(
                                                                    errors,
                                                                    `items.${index}.partner_allocations`,
                                                                )}
                                                            />
                                                        </div>
                                                        <div className="grid gap-1.5 sm:col-span-2 xl:col-span-4">
                                                            <label
                                                                htmlFor={`items_${index}_description`}
                                                                className="text-xs font-medium"
                                                            >
                                                                توضیح آیتم
                                                            </label>
                                                            <textarea
                                                                id={`items_${index}_description`}
                                                                name={`items[${index}][description]`}
                                                                defaultValue={
                                                                    item.description ??
                                                                    ''
                                                                }
                                                                className="min-h-20 rounded-md border border-input bg-background px-3 py-2 text-sm"
                                                            />
                                                        </div>
                                                        {proposalItems.length >
                                                        1 ? (
                                                            <div className="sm:col-span-2 xl:col-span-4">
                                                                <Button
                                                                    type="button"
                                                                    variant="secondary"
                                                                    size="sm"
                                                                    onClick={() =>
                                                                        removeProposalItem(
                                                                            index,
                                                                        )
                                                                    }
                                                                >
                                                                    <Trash2 className="size-4" />
                                                                    حذف آیتم
                                                                </Button>
                                                            </div>
                                                        ) : null}
                                                    </div>
                                                ),
                                            )}
                                        </div>
                                        <InputError message={errors.items} />
                                    </div>
                                    <div className="grid gap-1.5 md:col-span-2">
                                        <label
                                            htmlFor="target_audience"
                                            className="text-xs font-medium"
                                        >
                                            مخاطب هدف
                                        </label>
                                        <textarea
                                            id="target_audience"
                                            name="target_audience"
                                            defaultValue={
                                                editingProposal?.targetAudience ??
                                                ''
                                            }
                                            className="min-h-20 rounded-md border border-input bg-background px-3 py-2 text-sm"
                                        />
                                    </div>
                                    <div className="grid gap-1.5 md:col-span-2">
                                        <label
                                            htmlFor="notes"
                                            className="text-xs font-medium"
                                        >
                                            توضیحات برای ادمین
                                        </label>
                                        <textarea
                                            id="notes"
                                            name="notes"
                                            defaultValue={
                                                editingProposal?.notes ?? ''
                                            }
                                            className="min-h-24 rounded-md border border-input bg-background px-3 py-2 text-sm"
                                        />
                                    </div>
                                    <div className="md:col-span-2">
                                        <Button
                                            disabled={processing}
                                            className="w-full"
                                        >
                                            <Send className="size-4" />
                                            {editingProposal
                                                ? 'ارسال مجدد اصلاحات برای بررسی ادمین'
                                                : 'ارسال برای بررسی ادمین'}
                                        </Button>
                                    </div>
                                </>
                            )}
                        </Form>
                    </div>
                </section>

                <section className="exploria-panel overflow-hidden">
                    <div className="border-b border-border/70 px-4 py-3 dark:border-sidebar-border">
                        <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div className="flex items-center gap-2">
                                <MonitorPlay className="size-4 text-muted-foreground" />
                                <h2 className="font-semibold">
                                    درخواست تبلیغ اسپانسری
                                </h2>
                            </div>
                            <div className="grid grid-cols-3 gap-2 text-xs">
                                <span className="rounded-md bg-muted px-2.5 py-1">
                                    کل: {fa(stats.adRequests)}
                                </span>
                                <span className="rounded-md bg-muted px-2.5 py-1">
                                    منتظر: {fa(stats.pendingAds)}
                                </span>
                                <span className="rounded-md bg-muted px-2.5 py-1">
                                    تایید: {fa(stats.approvedAds)}
                                </span>
                            </div>
                        </div>
                    </div>
                    <Form
                        action="/sponsor/ads"
                        method="post"
                        options={{ preserveScroll: true }}
                        className="grid min-w-0 gap-4 p-4 md:grid-cols-2"
                    >
                        {({ processing, errors }) => (
                            <>
                                {Object.keys(errors).length > 0 ? (
                                    <div className="rounded-md border border-destructive/30 bg-destructive/10 px-3 py-2 text-sm text-destructive md:col-span-2">
                                        <p className="font-medium">
                                            فرم تبلیغ ارسال نشد؛ موارد مشخص‌شده
                                            را اصلاح کنید.
                                        </p>
                                        {Object.values(errors)[0] ? (
                                            <p className="mt-1 text-xs">
                                                {Object.values(errors)[0]}
                                            </p>
                                        ) : null}
                                    </div>
                                ) : null}
                                <div className="grid gap-1.5">
                                    <label
                                        htmlFor="ad_title"
                                        className="text-xs font-medium"
                                    >
                                        عنوان تبلیغ
                                    </label>
                                    <input
                                        id="ad_title"
                                        name="title"
                                        required
                                        className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                        placeholder="مثلا حمایت از مسیر گنج خانوادگی"
                                    />
                                    <InputError message={errors.title} />
                                </div>
                                <div className="grid gap-1.5">
                                    <label
                                        htmlFor="ad_hub_id"
                                        className="text-xs font-medium"
                                    >
                                        محدوده اجرا
                                    </label>
                                    <select
                                        id="ad_hub_id"
                                        name="hub_id"
                                        className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                        defaultValue=""
                                    >
                                        <option value="">
                                            کل محدوده اسپانسر
                                        </option>
                                        {(formOptions.adHubs ?? []).map(
                                            (hub) => (
                                                <option
                                                    key={hub.id}
                                                    value={hub.id}
                                                >
                                                    {hub.name}
                                                </option>
                                            ),
                                        )}
                                    </select>
                                    <InputError message={errors.hub_id} />
                                </div>
                                <div className="grid gap-1.5">
                                    <label
                                        htmlFor="ad_type"
                                        className="text-xs font-medium"
                                    >
                                        نوع تبلیغ
                                    </label>
                                    <select
                                        id="ad_type"
                                        name="ad_type"
                                        required
                                        className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                        defaultValue="standalone"
                                    >
                                        <option value="standalone">
                                            تبلیغ مستقل اسپانسر
                                        </option>
                                        <option value="display_takeover">
                                            جایگاه ویژه نمایشگر
                                        </option>
                                        <option value="reward_moment">
                                            پیام کنار پاداش
                                        </option>
                                    </select>
                                    <InputError message={errors.ad_type} />
                                </div>
                                <div className="grid gap-1.5">
                                    <label
                                        htmlFor="placement_type"
                                        className="text-xs font-medium"
                                    >
                                        جایگاه اصلی
                                    </label>
                                    <select
                                        id="placement_type"
                                        name="placement_type"
                                        required
                                        className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                        defaultValue="fixed_display"
                                    >
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
                                        <option value="reward_page">
                                            صفحه پاداش
                                        </option>
                                        <option value="map_route">
                                            نقشه و مسیر بازی
                                        </option>
                                        <option value="post_mission">
                                            پس از ماموریت
                                        </option>
                                    </select>
                                    <InputError
                                        message={errors.placement_type}
                                    />
                                </div>
                                <div className="grid gap-2 rounded-md border border-border/70 p-3 md:col-span-2">
                                    <p className="text-sm font-medium">
                                        کانال‌های آنلاین مکمل
                                    </p>
                                    <p className="text-xs leading-6 text-muted-foreground">
                                        ویترین عمومی بدون امتیاز است؛ جایگاه‌های
                                        مسیر برای محتوای ویژه و امتیازآور همان
                                        مرحله‌اند. پنج انتشار نخست ویترین در
                                        پایلوت رایگان معرفی شده‌اند.
                                    </p>
                                    <div className="grid gap-2 sm:grid-cols-2 lg:grid-cols-5">
                                        {onlinePlacementOptions.map(
                                            ([value, title]) => (
                                                <label
                                                    key={value}
                                                    className="flex min-h-10 items-center gap-2 rounded-md border border-input px-3 text-sm"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        name="online_placements[]"
                                                        value={value}
                                                        className="size-4"
                                                    />
                                                    <span>{title}</span>
                                                </label>
                                            ),
                                        )}
                                    </div>
                                    <InputError
                                        message={errors.online_placements}
                                    />
                                </div>
                                <div className="grid gap-1.5">
                                    <label
                                        htmlFor="creative_type"
                                        className="text-xs font-medium"
                                    >
                                        نوع محتوا
                                    </label>
                                    <select
                                        id="creative_type"
                                        name="creative_type"
                                        required
                                        className="h-9 rounded-md border border-input bg-background px-3 text-sm"
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
                                <div className="grid gap-1.5">
                                    <label
                                        htmlFor="asset_url"
                                        className="text-xs font-medium"
                                    >
                                        لینک فایل محتوا
                                    </label>
                                    <input
                                        id="asset_url"
                                        name="asset_url"
                                        type="url"
                                        dir="ltr"
                                        className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                        placeholder="https://example.com/sponsor-ad.jpg"
                                    />
                                    <InputError message={errors.asset_url} />
                                </div>
                                <div className="grid gap-1.5">
                                    <label
                                        htmlFor="starts_at"
                                        className="text-xs font-medium"
                                    >
                                        شروع نمایش
                                    </label>
                                    <input
                                        id="starts_at"
                                        name="starts_at"
                                        type="datetime-local"
                                        className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                    />
                                    <InputError message={errors.starts_at} />
                                </div>
                                <div className="grid gap-1.5">
                                    <label
                                        htmlFor="ends_at"
                                        className="text-xs font-medium"
                                    >
                                        پایان نمایش
                                    </label>
                                    <input
                                        id="ends_at"
                                        name="ends_at"
                                        type="datetime-local"
                                        className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                    />
                                    <InputError message={errors.ends_at} />
                                </div>
                                <div className="grid gap-1.5">
                                    <label
                                        htmlFor="budget_amount"
                                        className="text-xs font-medium"
                                    >
                                        بودجه پیشنهادی
                                    </label>
                                    <input
                                        id="budget_amount"
                                        name="budget_amount"
                                        type="number"
                                        min="0"
                                        className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                    />
                                    <InputError
                                        message={errors.budget_amount}
                                    />
                                </div>
                                <div className="grid gap-1.5">
                                    <label
                                        htmlFor="impression_cap"
                                        className="text-xs font-medium"
                                    >
                                        سقف نمایش
                                    </label>
                                    <input
                                        id="impression_cap"
                                        name="impression_cap"
                                        type="number"
                                        min="1"
                                        className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                    />
                                    <InputError
                                        message={errors.impression_cap}
                                    />
                                </div>
                                <div className="grid gap-1.5 md:col-span-2">
                                    <label
                                        htmlFor="body_copy"
                                        className="text-xs font-medium"
                                    >
                                        متن تبلیغ
                                    </label>
                                    <textarea
                                        id="body_copy"
                                        name="body_copy"
                                        className="min-h-24 rounded-md border border-input bg-background px-3 py-2 text-sm"
                                        placeholder="پیام برند، پیشنهاد اسپانسری یا متن فراخوان"
                                    />
                                    <InputError message={errors.body_copy} />
                                </div>
                                <div className="grid gap-1.5">
                                    <label
                                        htmlFor="cta_text"
                                        className="text-xs font-medium"
                                    >
                                        متن دکمه
                                    </label>
                                    <input
                                        id="cta_text"
                                        name="cta_text"
                                        className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                        placeholder="مثلا مشاهده پیشنهاد"
                                    />
                                    <InputError message={errors.cta_text} />
                                </div>
                                <div className="grid gap-1.5">
                                    <label
                                        htmlFor="target_url"
                                        className="text-xs font-medium"
                                    >
                                        لینک مقصد
                                    </label>
                                    <input
                                        id="target_url"
                                        name="target_url"
                                        type="url"
                                        dir="ltr"
                                        className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                        placeholder="https://example.com"
                                    />
                                    <InputError message={errors.target_url} />
                                </div>
                                <div className="md:col-span-2">
                                    <Button
                                        disabled={processing}
                                        className="w-full sm:w-auto"
                                    >
                                        <RadioTower className="size-4" />
                                        ارسال درخواست تبلیغ
                                    </Button>
                                </div>
                            </>
                        )}
                    </Form>
                </section>

                <section className="exploria-panel overflow-hidden">
                    <div className="border-b border-border/70 px-4 py-3 dark:border-sidebar-border">
                        <div className="flex items-center gap-2">
                            <CalendarClock className="size-4 text-muted-foreground" />
                            <h2 className="font-semibold">
                                تبلیغات اسپانسری ثبت‌شده
                            </h2>
                        </div>
                    </div>
                    <div className="divide-y divide-border/70">
                        {adRequests.length === 0 ? (
                            <div className="p-6 text-sm text-muted-foreground">
                                هنوز درخواست تبلیغی ثبت نشده است.
                            </div>
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
                                                {adRequest.creativeType ?? '-'}
                                            </p>
                                        </div>
                                        <span className="w-fit shrink-0 rounded-full bg-muted px-2.5 py-1 text-xs">
                                            {label(
                                                statusLabels,
                                                adRequest.status,
                                            )}
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
                                        · محدوده: {adRequest.hubName ?? '-'} ·
                                        بازه: {formatDate(adRequest.startsAt)}{' '}
                                        تا {formatDate(adRequest.endsAt)} ·
                                        نمایش: {fa(adRequest.impressionsCount)}
                                    </p>
                                </article>
                            ))
                        )}
                    </div>
                </section>

                <section className="exploria-panel overflow-hidden">
                    <div className="border-b border-border/70 px-4 py-3 dark:border-sidebar-border">
                        <h2 className="font-semibold">پیشنهادهای ارسال‌شده</h2>
                    </div>
                    <div className="divide-y divide-border/70 lg:hidden">
                        {proposals.length === 0 ? (
                            <div className="p-6 text-sm text-muted-foreground">
                                هنوز پیشنهادی ثبت نشده است.
                            </div>
                        ) : (
                            proposals.map((proposal) => (
                                <ProposalSummary
                                    key={proposal.id}
                                    proposal={proposal}
                                    onEdit={startEditProposal}
                                />
                            ))
                        )}
                    </div>
                    <div className="hidden overflow-x-auto lg:block">
                        <div className="min-w-[960px] divide-y divide-border/70">
                            {proposals.length === 0 ? (
                                <div className="p-6 text-sm text-muted-foreground">
                                    هنوز پیشنهادی ثبت نشده است.
                                </div>
                            ) : (
                                proposals.map((proposal) => (
                                    <article
                                        key={proposal.id}
                                        className="grid grid-cols-[1.2fr_0.9fr_0.9fr_0.9fr_0.8fr_0.9fr] items-center gap-3 px-4 py-3 text-sm"
                                    >
                                        <div className="min-w-0">
                                            <div className="flex items-center gap-2">
                                                <FileCheck2 className="size-4 text-muted-foreground" />
                                                <p className="truncate font-medium">
                                                    {proposal.title}
                                                </p>
                                            </div>
                                            {proposal.reviewNotes ? (
                                                <p className="mt-1 line-clamp-2 text-xs text-orange-700 dark:text-orange-300">
                                                    یادداشت اصلاح:{' '}
                                                    {proposal.reviewNotes}
                                                </p>
                                            ) : null}
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <Megaphone className="size-4 text-muted-foreground" />
                                            <span>
                                                {label(
                                                    proposalTypeLabels,
                                                    proposal.proposalType,
                                                )}
                                            </span>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <Store className="size-4 text-muted-foreground" />
                                            <span className="truncate">
                                                {proposal.partners?.length
                                                    ? `${fa(proposal.partners.length)} واحد`
                                                    : (proposal.campaign
                                                          ?.name ??
                                                      proposal.preferredPartner
                                                          ?.name ??
                                                      'انتخاب با ادمین')}
                                            </span>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <Gift className="size-4 text-muted-foreground" />
                                            <span>
                                                {proposal.items?.length
                                                    ? `${fa(proposal.items.length)} آیتم`
                                                    : money(
                                                          proposal.proposedBudgetAmount ||
                                                              proposal.estimatedValueAmount,
                                                      )}
                                            </span>
                                        </div>
                                        <span className="rounded-full bg-muted px-2.5 py-1 text-center text-xs">
                                            {label(
                                                statusLabels,
                                                proposal.status,
                                            )}
                                        </span>
                                        <div className="flex justify-end">
                                            {proposal.status ===
                                            'revision_requested' ? (
                                                <Button
                                                    type="button"
                                                    variant="secondary"
                                                    size="sm"
                                                    onClick={() =>
                                                        startEditProposal(
                                                            proposal,
                                                        )
                                                    }
                                                >
                                                    <Pencil className="size-4" />
                                                    اصلاح
                                                </Button>
                                            ) : (
                                                <span className="text-xs text-muted-foreground">
                                                    -
                                                </span>
                                            )}
                                        </div>
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

SponsorDashboard.layout = {
    breadcrumbs: [
        {
            title: 'پنل اسپانسر',
            href: '/sponsor/dashboard',
        },
    ],
};
