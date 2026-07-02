import { Form, Head, Link, usePage } from '@inertiajs/react';
import { BadgeDollarSign, FileCheck2, Gift, Megaphone, Plus, Send, Store, Trash2 } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';

type RegistryEntity = { id: string; code: string; name: string };
type CampaignOption = RegistryEntity & { status: string; venueName: string | null };
type PartnerOption = RegistryEntity & { partnerType: string; status: string; venueName: string | null };

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
    description: string | null;
};

type Props = {
    sponsor: Sponsor;
    stats: {
        proposals: number;
        pendingProposals: number;
        approvedProposals: number;
        revisionRequested: number;
    };
    proposals: SponsorProposal[];
    formOptions: {
        campaigns: CampaignOption[];
        partners: PartnerOption[];
    };
};

type SharedProps = { flash?: { success?: string } };

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

const objectiveLabels: Record<string, string> = {
    awareness: 'دیده‌شدن برند',
    footfall: 'افزایش مراجعه',
    lead_generation: 'جذب لید',
    sales: 'فروش',
    engagement: 'تعامل',
    social_impact: 'اثر فرهنگی/اجتماعی',
};

const itemTypeLabels: Record<string, string> = {
    reward: 'جایزه',
    discount: 'تخفیف/کد هدیه',
    product: 'محصول',
    sample: 'نمونه رایگان',
    media: 'رسانه/بنر',
    content: 'محتوا',
    cash_support: 'حمایت نقدی',
};

const defaultProposalItem = { item_type: 'reward' };

function fa(value: number) {
    return value.toLocaleString('fa-IR');
}

function money(value: number) {
    return value > 0 ? `${fa(value)} تومان` : '-';
}

function label(map: Record<string, string>, value: string) {
    return map[value] ?? value;
}

export default function SponsorDashboard({ sponsor, stats, proposals, formOptions }: Props) {
    const { flash } = usePage<SharedProps>().props;
    const [proposalItems, setProposalItems] = useState([defaultProposalItem]);

    const addProposalItem = () => {
        setProposalItems((items) => [...items, defaultProposalItem]);
    };

    const removeProposalItem = (index: number) => {
        setProposalItems((items) => items.filter((_, itemIndex) => itemIndex !== index));
    };

    return (
        <>
            <Head title="پنل اسپانسر" />
            <div dir="rtl" className="flex h-full flex-1 flex-col gap-5 overflow-x-auto p-4">
                <header className="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p className="text-sm text-muted-foreground">پنل خوداظهاری و پیشنهاد اسپانسری</p>
                        <h1 className="mt-1 text-2xl font-semibold">پنل اسپانسر</h1>
                        <p className="mt-2 text-sm text-muted-foreground">
                            {sponsor.name} {sponsor.venueName ? `- ${sponsor.venueName}` : ''}
                        </p>
                    </div>
                    <div className="grid grid-cols-2 gap-3 text-sm md:grid-cols-4">
                        {[
                            ['کل پیشنهادها', stats.proposals],
                            ['در انتظار بررسی', stats.pendingProposals],
                            ['تأیید شده', stats.approvedProposals],
                            ['نیازمند اصلاح', stats.revisionRequested],
                        ].map(([title, value]) => (
                            <div key={title} className="rounded-lg border border-border/80 bg-card/80 px-3 py-2 shadow-sm">
                                <p className="text-muted-foreground">{title}</p>
                                <p className="mt-1 font-semibold">{fa(Number(value))}</p>
                            </div>
                        ))}
                    </div>
                </header>

                {flash?.success ? (
                    <section className="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-100">
                        {flash.success}
                    </section>
                ) : null}

                <section className="grid gap-4 xl:grid-cols-[0.8fr_1.2fr]">
                    <div className="exploria-panel">
                        <div className="border-b border-border/70 px-4 py-3 dark:border-sidebar-border">
                            <div className="flex items-center gap-2">
                                <BadgeDollarSign className="size-4 text-muted-foreground" />
                                <h2 className="font-semibold">حساب اسپانسر</h2>
                            </div>
                        </div>
                        <div className="grid gap-3 p-4 text-sm">
                            <p><span className="text-muted-foreground">کد: </span><span dir="ltr">{sponsor.code}</span></p>
                            <p><span className="text-muted-foreground">وضعیت: </span>{label(statusLabels, sponsor.status)}</p>
                            <p><span className="text-muted-foreground">مسئول تماس: </span>{sponsor.contactName ?? '-'}</p>
                            <p><span className="text-muted-foreground">موبایل: </span><span dir="ltr">{sponsor.contactMobile ?? '-'}</span></p>
                            {sponsor.websiteUrl ? <Link className="text-primary underline" href={sponsor.websiteUrl}>وب‌سایت / دارایی برند</Link> : null}
                        </div>
                    </div>

                    <div className="exploria-panel">
                        <div className="border-b border-border/70 px-4 py-3 dark:border-sidebar-border">
                            <div className="flex items-center gap-2">
                                <Send className="size-4 text-muted-foreground" />
                                <h2 className="font-semibold">ارسال پیشنهاد اسپانسری</h2>
                            </div>
                            <p className="mt-1 text-sm text-muted-foreground">پیشنهاد شما برای ادمین ارسال می‌شود و بعد از بررسی می‌تواند به کمپین، جایزه، تبلیغ یا واحد عضو وصل شود.</p>
                        </div>
                        <Form action="/sponsor/proposals" method="post" options={{ preserveScroll: true }} className="grid gap-4 p-4 md:grid-cols-2">
                            {({ processing, errors }) => (
                                <>
                                    <div className="grid gap-1.5">
                                        <label htmlFor="title" className="text-xs font-medium">عنوان پیشنهاد</label>
                                        <input id="title" name="title" required className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                        <InputError message={errors.title} />
                                    </div>
                                    <div className="grid gap-1.5">
                                        <label htmlFor="proposal_type" className="text-xs font-medium">نوع پیشنهاد</label>
                                        <select id="proposal_type" name="proposal_type" defaultValue="campaign_sponsorship" className="h-9 rounded-md border border-input bg-background px-3 text-sm">
                                            <option value="campaign_sponsorship">حمایت کمپین</option>
                                            <option value="reward_offer">جایزه پیشنهادی</option>
                                            <option value="discount_offer">تخفیف/کد خرید</option>
                                            <option value="display_media">تبلیغ نمایشگر</option>
                                            <option value="family_challenge">چالش خانوادگی</option>
                                            <option value="scientific_cultural_content">محتوای علمی/فرهنگی</option>
                                            <option value="product_sampling">نمونه‌گیری محصول</option>
                                        </select>
                                    </div>
                                    <div className="grid gap-1.5">
                                        <label htmlFor="objective" className="text-xs font-medium">هدف</label>
                                        <select id="objective" name="objective" defaultValue="engagement" className="h-9 rounded-md border border-input bg-background px-3 text-sm">
                                            <option value="awareness">دیده‌شدن برند</option>
                                            <option value="footfall">افزایش مراجعه</option>
                                            <option value="lead_generation">جذب لید</option>
                                            <option value="sales">فروش</option>
                                            <option value="engagement">تعامل</option>
                                            <option value="social_impact">اثر فرهنگی/اجتماعی</option>
                                        </select>
                                    </div>
                                    <div className="grid gap-1.5">
                                        <label htmlFor="campaign_id" className="text-xs font-medium">کمپین مورد علاقه</label>
                                        <select id="campaign_id" name="campaign_id" defaultValue="" className="h-9 rounded-md border border-input bg-background px-3 text-sm">
                                            <option value="">ادمین انتخاب کند</option>
                                            {formOptions.campaigns.map((campaign) => <option key={campaign.id} value={campaign.id}>{campaign.name}</option>)}
                                        </select>
                                    </div>
                                    <div className="grid gap-1.5 md:col-span-2">
                                        <label htmlFor="partner_account_ids" className="text-xs font-medium">واحدهای اجرایی پیشنهادی</label>
                                        <select id="partner_account_ids" name="partner_account_ids[]" multiple className="min-h-28 rounded-md border border-input bg-background px-3 py-2 text-sm">
                                            {formOptions.partners.map((partner) => (
                                                <option key={partner.id} value={partner.id}>
                                                    {partner.name} {partner.venueName ? `- ${partner.venueName}` : ''}
                                                </option>
                                            ))}
                                        </select>
                                        <InputError message={errors.partner_account_ids} />
                                    </div>
                                    <div className="grid gap-1.5">
                                        <label htmlFor="proposed_budget_amount" className="text-xs font-medium">بودجه پیشنهادی</label>
                                        <input id="proposed_budget_amount" name="proposed_budget_amount" type="number" min="0" className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                    </div>
                                    <div className="grid gap-1.5">
                                        <label htmlFor="estimated_value_amount" className="text-xs font-medium">ارزش غیرنقدی/جایزه</label>
                                        <input id="estimated_value_amount" name="estimated_value_amount" type="number" min="0" className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                    </div>
                                    <div className="grid gap-1.5">
                                        <label htmlFor="asset_url" className="text-xs font-medium">لینک لوگو/بنر/دارایی</label>
                                        <input id="asset_url" name="asset_url" dir="ltr" className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                        <InputError message={errors.asset_url} />
                                    </div>
                                    <div className="grid gap-3 md:col-span-2">
                                        <div className="flex flex-wrap items-center justify-between gap-2">
                                            <label className="text-xs font-medium">جایزه‌ها، تخفیف‌ها و محصولات پیشنهادی</label>
                                            <Button type="button" variant="secondary" size="sm" onClick={addProposalItem}>
                                                <Plus className="size-4" />
                                                افزودن آیتم
                                            </Button>
                                        </div>
                                        <div className="grid gap-3">
                                            {proposalItems.map((item, index) => (
                                                <div key={index} className="grid gap-3 rounded-md border border-border/80 p-3 md:grid-cols-4">
                                                    <div className="grid gap-1.5">
                                                        <label htmlFor={`items_${index}_item_type`} className="text-xs font-medium">نوع</label>
                                                        <select
                                                            id={`items_${index}_item_type`}
                                                            name={`items[${index}][item_type]`}
                                                            defaultValue={item.item_type}
                                                            className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                                        >
                                                            <option value="reward">جایزه</option>
                                                            <option value="discount">تخفیف/کد هدیه</option>
                                                            <option value="product">محصول</option>
                                                            <option value="sample">نمونه رایگان</option>
                                                            <option value="media">رسانه/بنر</option>
                                                            <option value="content">محتوا</option>
                                                            <option value="cash_support">حمایت نقدی</option>
                                                        </select>
                                                    </div>
                                                    <div className="grid gap-1.5 md:col-span-3">
                                                        <label htmlFor={`items_${index}_title`} className="text-xs font-medium">عنوان آیتم</label>
                                                        <input
                                                            id={`items_${index}_title`}
                                                            name={`items[${index}][title]`}
                                                            required={index === 0}
                                                            className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                                        />
                                                    </div>
                                                    <div className="grid gap-1.5">
                                                        <label htmlFor={`items_${index}_quantity`} className="text-xs font-medium">تعداد</label>
                                                        <input
                                                            id={`items_${index}_quantity`}
                                                            name={`items[${index}][quantity]`}
                                                            type="number"
                                                            min="1"
                                                            className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                                        />
                                                    </div>
                                                    <div className="grid gap-1.5">
                                                        <label htmlFor={`items_${index}_estimated_unit_value_amount`} className="text-xs font-medium">ارزش هر واحد</label>
                                                        <input
                                                            id={`items_${index}_estimated_unit_value_amount`}
                                                            name={`items[${index}][estimated_unit_value_amount]`}
                                                            type="number"
                                                            min="0"
                                                            className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                                        />
                                                    </div>
                                                    <div className="grid gap-1.5 md:col-span-2">
                                                        <label htmlFor={`items_${index}_target_partner_account_ids`} className="text-xs font-medium">واحدهای هدف این آیتم</label>
                                                        <select
                                                            id={`items_${index}_target_partner_account_ids`}
                                                            name={`items[${index}][target_partner_account_ids][]`}
                                                            multiple
                                                            className="min-h-20 rounded-md border border-input bg-background px-3 py-2 text-sm"
                                                        >
                                                            {formOptions.partners.map((partner) => (
                                                                <option key={partner.id} value={partner.id}>{partner.name}</option>
                                                            ))}
                                                        </select>
                                                    </div>
                                                    <div className="grid gap-1.5 md:col-span-4">
                                                        <label htmlFor={`items_${index}_description`} className="text-xs font-medium">توضیح آیتم</label>
                                                        <textarea
                                                            id={`items_${index}_description`}
                                                            name={`items[${index}][description]`}
                                                            className="min-h-20 rounded-md border border-input bg-background px-3 py-2 text-sm"
                                                        />
                                                    </div>
                                                    {proposalItems.length > 1 ? (
                                                        <div className="md:col-span-4">
                                                            <Button type="button" variant="secondary" size="sm" onClick={() => removeProposalItem(index)}>
                                                                <Trash2 className="size-4" />
                                                                حذف آیتم
                                                            </Button>
                                                        </div>
                                                    ) : null}
                                                </div>
                                            ))}
                                        </div>
                                        <InputError message={errors.items} />
                                    </div>
                                    <div className="grid gap-1.5 md:col-span-2">
                                        <label htmlFor="target_audience" className="text-xs font-medium">مخاطب هدف</label>
                                        <textarea id="target_audience" name="target_audience" className="min-h-20 rounded-md border border-input bg-background px-3 py-2 text-sm" />
                                    </div>
                                    <div className="grid gap-1.5 md:col-span-2">
                                        <label htmlFor="notes" className="text-xs font-medium">توضیحات برای ادمین</label>
                                        <textarea id="notes" name="notes" className="min-h-24 rounded-md border border-input bg-background px-3 py-2 text-sm" />
                                    </div>
                                    <div className="md:col-span-2">
                                        <Button disabled={processing} className="w-full">
                                            <Send className="size-4" />
                                            ارسال برای بررسی ادمین
                                        </Button>
                                    </div>
                                </>
                            )}
                        </Form>
                    </div>
                </section>

                <section className="exploria-panel">
                    <div className="border-b border-border/70 px-4 py-3 dark:border-sidebar-border">
                        <h2 className="font-semibold">پیشنهادهای ارسال‌شده</h2>
                    </div>
                    <div className="min-w-[960px] divide-y divide-border/70">
                        {proposals.length === 0 ? (
                            <div className="p-6 text-sm text-muted-foreground">هنوز پیشنهادی ثبت نشده است.</div>
                        ) : proposals.map((proposal) => (
                            <article key={proposal.id} className="grid grid-cols-[1.2fr_0.9fr_0.9fr_0.9fr_0.8fr] items-center gap-3 px-4 py-3 text-sm">
                                <div className="min-w-0">
                                    <div className="flex items-center gap-2">
                                        <FileCheck2 className="size-4 text-muted-foreground" />
                                        <p className="truncate font-medium">{proposal.title}</p>
                                    </div>
                                    <p className="mt-1 truncate text-xs text-muted-foreground" dir="ltr">{proposal.code}</p>
                                </div>
                                <div className="flex items-center gap-2">
                                    <Megaphone className="size-4 text-muted-foreground" />
                                    <span>{label(proposalTypeLabels, proposal.proposalType)}</span>
                                </div>
                                <div className="flex items-center gap-2">
                                    <Store className="size-4 text-muted-foreground" />
                                    <span className="truncate">
                                        {proposal.partners?.length ? `${fa(proposal.partners.length)} واحد` : proposal.campaign?.name ?? proposal.preferredPartner?.name ?? 'انتخاب با ادمین'}
                                    </span>
                                </div>
                                <div className="flex items-center gap-2">
                                    <Gift className="size-4 text-muted-foreground" />
                                    <span>
                                        {proposal.items?.length ? `${fa(proposal.items.length)} آیتم` : money(proposal.proposedBudgetAmount || proposal.estimatedValueAmount)}
                                    </span>
                                </div>
                                <span className="rounded-full bg-muted px-2.5 py-1 text-center text-xs">{label(statusLabels, proposal.status)}</span>
                            </article>
                        ))}
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
