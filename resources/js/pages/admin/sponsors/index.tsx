import { Form, Head, usePage } from '@inertiajs/react';
import { BadgeDollarSign, Building2, Handshake, Target } from 'lucide-react';
import { CampaignContextNav } from '@/components/campaign-context-nav';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';

type RegistryEntity = { id: string; code: string; name: string };

type SelectedCampaign = RegistryEntity & {
    campaignType: string;
    blueprintCode: string | null;
    designSource?: string | null;
    designVenueCode?: string | null;
    status: string;
    venue: RegistryEntity | null;
};

type SponsorAccount = RegistryEntity & {
    sponsorType: string;
    status: string;
    contactName: string | null;
    contactMobile: string | null;
    websiteUrl: string | null;
    notes: string | null;
    venue: RegistryEntity | null;
};

type CampaignOption = RegistryEntity & { status: string; venueName: string | null };
type SponsorOption = RegistryEntity & { sponsorType: string; status: string };

type CampaignSponsorship = {
    id: string;
    sponsorshipGoal: string;
    packageType: string;
    status: string;
    budgetAmount: number;
    contractValue: number;
    notes: string | null;
    campaign: CampaignOption | null;
    sponsor: SponsorOption | null;
};

type Props = {
    stats: {
        sponsors: number;
        activeSponsors: number;
        sponsorships: number;
        activeSponsorships: number;
        plannedBudget: number;
        contractValue: number;
    };
    sponsors: SponsorAccount[];
    sponsorships: CampaignSponsorship[];
    selectedCampaign: SelectedCampaign | null;
    formOptions: {
        campaigns: CampaignOption[];
        sponsors: SponsorOption[];
        venues: RegistryEntity[];
    };
};

type SharedProps = {
    flash?: { success?: string };
    auth: { user: { role?: string } };
};

const statusLabels: Record<string, string> = {
    active: 'فعال',
    draft: 'پیش‌نویس',
    inactive: 'غیرفعال',
};

const sponsorTypeLabels: Record<string, string> = {
    brand: 'برند تجاری',
    cultural: 'فرهنگی',
    scientific: 'علمی',
    retail: 'خرده‌فروشی',
    institutional: 'سازمانی',
};

const goalLabels: Record<string, string> = {
    awareness: 'آگاهی از برند',
    footfall: 'افزایش مراجعه',
    lead_generation: 'لید و ثبت علاقه',
    sales: 'فروش و مصرف کد',
    engagement: 'تعامل و مشارکت',
};

const packageLabels: Record<string, string> = {
    pilot_activation: 'فعال‌سازی پایلوت',
    display_media: 'تبلیغ نمایشگر',
    treasure_sponsor: 'اسپانسر گنج',
    family_team_challenge: 'چالش خانوادگی/تیمی',
    scientific_cultural_challenge: 'چالش علمی/فرهنگی',
};

function fa(value: number) {
    return value.toLocaleString('fa-IR');
}

function money(value: number) {
    return value > 0 ? `${fa(value)} تومان` : 'ثبت نشده';
}

function label(map: Record<string, string>, value: string) {
    return map[value] ?? value;
}

export default function SponsorActivationIndex({ stats, sponsors, sponsorships, selectedCampaign, formOptions }: Props) {
    const { flash, auth } = usePage<SharedProps>().props;
    const canMutate = auth.user.role === 'admin' || auth.user.role === 'operator';

    const statCards = [
        ['اسپانسر', fa(stats.sponsors)],
        ['اسپانسر فعال', fa(stats.activeSponsors)],
        ['حمایت کمپین', fa(stats.sponsorships)],
        ['حمایت فعال', fa(stats.activeSponsorships)],
        ['بودجه برنامه‌ریزی', money(stats.plannedBudget)],
        ['ارزش قرارداد', money(stats.contractValue)],
    ];

    return (
        <>
            <Head title="اسپانسر و درآمد" />
            <div dir="rtl" className="flex h-full flex-1 flex-col gap-5 overflow-x-auto p-4">
                <header className="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p className="text-sm text-muted-foreground">لایه تجاری کمپین‌ها</p>
                        <h1 className="mt-1 text-2xl font-semibold">اسپانسر و درآمد</h1>
                    </div>
                    <div className="grid grid-cols-2 gap-3 text-sm lg:grid-cols-6">
                        {statCards.map(([title, value]) => (
                            <div key={title} className="rounded-lg border border-border/80 bg-card/80 px-3 py-2 shadow-sm">
                                <p className="text-muted-foreground">{title}</p>
                                <p className="mt-1 font-semibold">{value}</p>
                            </div>
                        ))}
                    </div>
                </header>

                {selectedCampaign ? <CampaignContextNav campaign={selectedCampaign} /> : null}

                {flash?.success ? (
                    <section className="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-100">
                        {flash.success}
                    </section>
                ) : null}

                {canMutate ? (
                    <section className="grid gap-4 xl:grid-cols-2">
                        <div className="exploria-panel">
                            <div className="border-b border-border/70 px-4 py-3 dark:border-sidebar-border">
                                <div className="flex items-center gap-2">
                                    <Building2 className="size-4 text-muted-foreground" />
                                    <h2 className="font-semibold">ثبت حساب اسپانسر</h2>
                                </div>
                                <p className="mt-1 text-sm text-muted-foreground">برند، سازمان، واحد علمی یا فرهنگی را برای اتصال به کمپین ثبت کنید.</p>
                            </div>
                            <Form action="/admin/sponsors" method="post" options={{ preserveScroll: true }} className="grid gap-4 p-4 md:grid-cols-2">
                                {({ processing, errors }) => (
                                    <>
                                        <div className="grid gap-1.5">
                                            <label htmlFor="sponsor_name" className="text-xs font-medium">نام اسپانسر</label>
                                            <input id="sponsor_name" name="name" required className="h-9 rounded-md border border-input bg-background px-3 text-sm" placeholder="مثلا برند حامی خانواده" />
                                            <InputError message={errors.name} />
                                        </div>
                                        <div className="grid gap-1.5">
                                            <label htmlFor="sponsor_code" className="text-xs font-medium">کد</label>
                                            <input id="sponsor_code" name="code" required dir="ltr" className="h-9 rounded-md border border-input bg-background px-3 text-sm" placeholder="family-brand-sponsor" />
                                            <InputError message={errors.code} />
                                        </div>
                                        <div className="grid gap-1.5">
                                            <label htmlFor="sponsor_type" className="text-xs font-medium">نوع</label>
                                            <select id="sponsor_type" name="sponsor_type" defaultValue="brand" className="h-9 rounded-md border border-input bg-background px-3 text-sm">
                                                <option value="brand">برند تجاری</option>
                                                <option value="cultural">فرهنگی</option>
                                                <option value="scientific">علمی</option>
                                                <option value="retail">خرده‌فروشی</option>
                                                <option value="institutional">سازمانی</option>
                                            </select>
                                        </div>
                                        <div className="grid gap-1.5">
                                            <label htmlFor="sponsor_status" className="text-xs font-medium">وضعیت</label>
                                            <select id="sponsor_status" name="status" defaultValue="draft" className="h-9 rounded-md border border-input bg-background px-3 text-sm">
                                                <option value="draft">پیش‌نویس</option>
                                                <option value="active">فعال</option>
                                                <option value="inactive">غیرفعال</option>
                                            </select>
                                        </div>
                                        <div className="grid gap-1.5">
                                            <label htmlFor="venue_id" className="text-xs font-medium">مکان مرتبط</label>
                                            <select id="venue_id" name="venue_id" defaultValue="" className="h-9 rounded-md border border-input bg-background px-3 text-sm">
                                                <option value="">سراسری / بدون مکان خاص</option>
                                                {formOptions.venues.map((venue) => <option key={venue.id} value={venue.id}>{venue.name}</option>)}
                                            </select>
                                        </div>
                                        <div className="grid gap-1.5">
                                            <label htmlFor="contact_name" className="text-xs font-medium">مسئول تماس</label>
                                            <input id="contact_name" name="contact_name" className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                        </div>
                                        <div className="grid gap-1.5">
                                            <label htmlFor="contact_mobile" className="text-xs font-medium">موبایل تماس</label>
                                            <input id="contact_mobile" name="contact_mobile" dir="ltr" className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                        </div>
                                        <div className="grid gap-1.5">
                                            <label htmlFor="website_url" className="text-xs font-medium">وب‌سایت</label>
                                            <input id="website_url" name="website_url" dir="ltr" className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                        </div>
                                        <div className="grid gap-1.5 md:col-span-2">
                                            <label htmlFor="notes" className="text-xs font-medium">یادداشت تجاری</label>
                                            <textarea id="notes" name="notes" className="min-h-20 rounded-md border border-input bg-background px-3 py-2 text-sm" />
                                        </div>
                                        <div className="md:col-span-2">
                                            <Button disabled={processing} className="w-full">
                                                <Handshake className="size-4" />
                                                ثبت اسپانسر
                                            </Button>
                                        </div>
                                    </>
                                )}
                            </Form>
                        </div>

                        <div className="exploria-panel">
                            <div className="border-b border-border/70 px-4 py-3 dark:border-sidebar-border">
                                <div className="flex items-center gap-2">
                                    <Target className="size-4 text-muted-foreground" />
                                    <h2 className="font-semibold">اتصال اسپانسر به کمپین</h2>
                                </div>
                                <p className="mt-1 text-sm text-muted-foreground">هدف، بسته و بودجه حمایت را برای یک کمپین مشخص ثبت کنید.</p>
                            </div>
                            <Form action="/admin/campaign-sponsorships" method="post" options={{ preserveScroll: true }} className="grid gap-4 p-4 md:grid-cols-2">
                                {({ processing, errors }) => (
                                    <>
                                        <div className="grid gap-1.5">
                                            <label htmlFor="campaign_id" className="text-xs font-medium">کمپین</label>
                                            <select id="campaign_id" name="campaign_id" required defaultValue={selectedCampaign?.id ?? ''} className="h-9 rounded-md border border-input bg-background px-3 text-sm">
                                                <option value="">انتخاب کمپین</option>
                                                {formOptions.campaigns.map((campaign) => <option key={campaign.id} value={campaign.id}>{campaign.name}</option>)}
                                            </select>
                                            <InputError message={errors.campaign_id} />
                                        </div>
                                        <div className="grid gap-1.5">
                                            <label htmlFor="sponsor_account_id" className="text-xs font-medium">اسپانسر</label>
                                            <select id="sponsor_account_id" name="sponsor_account_id" required className="h-9 rounded-md border border-input bg-background px-3 text-sm">
                                                <option value="">انتخاب اسپانسر</option>
                                                {formOptions.sponsors.map((sponsor) => <option key={sponsor.id} value={sponsor.id}>{sponsor.name}</option>)}
                                            </select>
                                            <InputError message={errors.sponsor_account_id} />
                                        </div>
                                        <div className="grid gap-1.5">
                                            <label htmlFor="sponsorship_goal" className="text-xs font-medium">هدف حمایت</label>
                                            <select id="sponsorship_goal" name="sponsorship_goal" defaultValue="engagement" className="h-9 rounded-md border border-input bg-background px-3 text-sm">
                                                <option value="awareness">آگاهی از برند</option>
                                                <option value="footfall">افزایش مراجعه</option>
                                                <option value="lead_generation">لید و ثبت علاقه</option>
                                                <option value="sales">فروش و مصرف کد</option>
                                                <option value="engagement">تعامل و مشارکت</option>
                                            </select>
                                        </div>
                                        <div className="grid gap-1.5">
                                            <label htmlFor="package_type" className="text-xs font-medium">بسته</label>
                                            <select id="package_type" name="package_type" defaultValue="pilot_activation" className="h-9 rounded-md border border-input bg-background px-3 text-sm">
                                                <option value="pilot_activation">فعال‌سازی پایلوت</option>
                                                <option value="display_media">تبلیغ نمایشگر</option>
                                                <option value="treasure_sponsor">اسپانسر گنج</option>
                                                <option value="family_team_challenge">چالش خانوادگی/تیمی</option>
                                                <option value="scientific_cultural_challenge">چالش علمی/فرهنگی</option>
                                            </select>
                                        </div>
                                        <div className="grid gap-1.5">
                                            <label htmlFor="sponsorship_status" className="text-xs font-medium">وضعیت</label>
                                            <select id="sponsorship_status" name="status" defaultValue="draft" className="h-9 rounded-md border border-input bg-background px-3 text-sm">
                                                <option value="draft">پیش‌نویس</option>
                                                <option value="active">فعال</option>
                                                <option value="inactive">غیرفعال</option>
                                            </select>
                                        </div>
                                        <div className="grid gap-1.5">
                                            <label htmlFor="budget_amount" className="text-xs font-medium">بودجه برنامه‌ریزی</label>
                                            <input id="budget_amount" name="budget_amount" type="number" min="0" className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                        </div>
                                        <div className="grid gap-1.5">
                                            <label htmlFor="contract_value" className="text-xs font-medium">ارزش قرارداد</label>
                                            <input id="contract_value" name="contract_value" type="number" min="0" className="h-9 rounded-md border border-input bg-background px-3 text-sm" />
                                        </div>
                                        <div className="grid gap-1.5 md:col-span-2">
                                            <label htmlFor="sponsorship_notes" className="text-xs font-medium">یادداشت حمایت</label>
                                            <textarea id="sponsorship_notes" name="notes" className="min-h-20 rounded-md border border-input bg-background px-3 py-2 text-sm" />
                                        </div>
                                        <div className="md:col-span-2">
                                            <Button disabled={processing} className="w-full">
                                                <BadgeDollarSign className="size-4" />
                                                اتصال اسپانسر به کمپین
                                            </Button>
                                        </div>
                                    </>
                                )}
                            </Form>
                        </div>
                    </section>
                ) : null}

                <section className="grid gap-4 xl:grid-cols-[0.8fr_1.2fr]">
                    <div className="exploria-panel">
                        <div className="border-b border-border/70 px-4 py-3 dark:border-sidebar-border">
                            <h2 className="font-semibold">حساب‌های اسپانسر</h2>
                        </div>
                        <div className="divide-y divide-border/70">
                            {sponsors.length === 0 ? (
                                <div className="p-6 text-sm text-muted-foreground">هنوز اسپانسری ثبت نشده است.</div>
                            ) : sponsors.map((sponsor) => (
                                <article key={sponsor.id} className="grid gap-2 px-4 py-3 text-sm">
                                    <div className="flex items-start justify-between gap-3">
                                        <div className="min-w-0">
                                            <p className="truncate font-medium">{sponsor.name}</p>
                                            <p className="mt-1 truncate text-xs text-muted-foreground" dir="ltr">{sponsor.code}</p>
                                        </div>
                                        <span className="rounded-full bg-muted px-2.5 py-1 text-xs">{label(statusLabels, sponsor.status)}</span>
                                    </div>
                                    <div className="flex flex-wrap gap-2 text-xs text-muted-foreground">
                                        <span>{label(sponsorTypeLabels, sponsor.sponsorType)}</span>
                                        <span>{sponsor.venue?.name ?? 'سراسری'}</span>
                                        {sponsor.contactName ? <span>{sponsor.contactName}</span> : null}
                                    </div>
                                </article>
                            ))}
                        </div>
                    </div>

                    <div className="exploria-panel">
                        <div className="border-b border-border/70 px-4 py-3 dark:border-sidebar-border">
                            <h2 className="font-semibold">حمایت‌های متصل به کمپین</h2>
                        </div>
                        <div className="min-w-[820px] divide-y divide-border/70">
                            {sponsorships.length === 0 ? (
                                <div className="p-6 text-sm text-muted-foreground">هنوز حمایتی به کمپین وصل نشده است.</div>
                            ) : sponsorships.map((sponsorship) => (
                                <article key={sponsorship.id} className="grid grid-cols-[1.2fr_1fr_1fr_0.8fr_0.8fr] items-center gap-3 px-4 py-3 text-sm">
                                    <div className="min-w-0">
                                        <p className="truncate font-medium">{sponsorship.sponsor?.name ?? 'اسپانسر نامشخص'}</p>
                                        <p className="mt-1 truncate text-xs text-muted-foreground">{sponsorship.campaign?.name ?? 'کمپین نامشخص'}</p>
                                    </div>
                                    <span>{label(goalLabels, sponsorship.sponsorshipGoal)}</span>
                                    <span>{label(packageLabels, sponsorship.packageType)}</span>
                                    <div>
                                        <p>{money(sponsorship.budgetAmount)}</p>
                                        <p className="mt-1 text-xs text-muted-foreground">قرارداد: {money(sponsorship.contractValue)}</p>
                                    </div>
                                    <span className="rounded-full bg-muted px-2.5 py-1 text-center text-xs">{label(statusLabels, sponsorship.status)}</span>
                                </article>
                            ))}
                        </div>
                    </div>
                </section>
            </div>
        </>
    );
}
