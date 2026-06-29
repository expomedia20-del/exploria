import { Head, Link } from '@inertiajs/react';
import { Building2, Link2, Megaphone, Network, Store } from 'lucide-react';
import { Button } from '@/components/ui/button';

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
    name: string;
};

type HubEntity = RegistryEntity & {
    hubType: string;
};

type CampaignEntity = RegistryEntity & {
    status: string;
};

type PartnerEntity = RegistryEntity & {
    partnerType: string;
    status: string;
    contactName: string | null;
    contactMobile: string | null;
};

type ParticipantConnections = {
    rewards: number;
    ads: number;
    qrCodes: number;
    missions: number;
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

type CampaignParticipant = {
    id: string;
    participantType: string;
    participationRole: string;
    status: string;
    onboardingStatus: string;
    joinedAt: string | null;
    campaign: CampaignEntity | null;
    venue: RegistryEntity | null;
    hub: HubEntity | null;
    partner: PartnerEntity | null;
    connections: ParticipantConnections;
};

type CampaignGroup = {
    campaign: CampaignEntity | null;
    participantsCount: number;
    activeCount: number;
    hubCount: number;
};

type HubGroup = {
    hub: HubEntity | null;
    participantsCount: number;
    activeCount: number;
    roles: string[];
};

type Props = {
    stats: {
        participants: number;
        activeParticipants: number;
        invitedParticipants: number;
        readyParticipants: number;
        hubs: number;
        campaigns: number;
    };
    participants: CampaignParticipant[];
    campaignGroups: CampaignGroup[];
    hubGroups: HubGroup[];
    selectedBlueprint: SelectedBlueprint | null;
    selectedCampaign: SelectedCampaign | null;
};

const statusLabels: Record<string, string> = {
    active: 'فعال',
    draft: 'پیش نویس',
    inactive: 'غیرفعال',
    placeholder: 'نمونه کنترل شده',
};

const onboardingLabels: Record<string, string> = {
    invited: 'دعوت شده',
    ready: 'آماده اجرا',
    pending_review: 'در انتظار تایید',
    paused: 'متوقف',
};

const roleLabels: Record<string, string> = {
    reward_redemption: 'تحویل پاداش',
    commercial_activation: 'فعال سازی تجاری',
    route_sponsor: 'اسپانسر مسیر',
    content_partner: 'محتوا و تجربه',
    display_partner: 'نمایشگر و تبلیغات',
};

const typeLabels: Record<string, string> = {
    member_shop: 'واحد عضو',
    sponsor: 'اسپانسر',
    external_brand: 'برند بیرونی',
    hub_subunit: 'زیرمجموعه هاب',
};

function label(map: Record<string, string>, value: string) {
    return map[value] ?? value;
}

function formatNumber(value: number) {
    return value.toLocaleString('fa-IR');
}

function connectionTotal(connections: ParticipantConnections) {
    return connections.rewards + connections.ads + connections.qrCodes + connections.missions;
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

export default function CampaignParticipantsIndex({
    stats,
    participants,
    campaignGroups,
    hubGroups,
    selectedBlueprint,
    selectedCampaign,
}: Props) {

    return (
        <>
            <Head title="اعضای کمپین" />
            <div dir="rtl" className="flex h-full flex-1 flex-col gap-5 overflow-x-auto p-4">
                <header className="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p className="text-sm text-muted-foreground">رجیستری عملیاتی کمپین</p>
                        <h1 className="mt-1 text-2xl font-semibold">اعضای مشارکت کننده کمپین</h1>
                    </div>
                    <div className="grid grid-cols-2 gap-3 text-sm sm:grid-cols-5">
                        {[
                            ['کل اعضا', stats.participants],
                            ['فعال', stats.activeParticipants],
                            ['آماده', stats.readyParticipants],
                            ['هاب', stats.hubs],
                            ['کمپین', stats.campaigns],
                        ].map(([title, value]) => (
                            <div key={title} className="rounded-lg border border-border/80 bg-card/80 px-3 py-2 shadow-sm">
                                <p className="text-muted-foreground">{title}</p>
                                <p className="mt-1 font-semibold">{formatNumber(Number(value))}</p>
                            </div>
                        ))}
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
                                <p className="mt-1 text-muted-foreground">داده الگو آمده تا مالک پاداش، هاب، فروشگاه یا اسپانسر مرتبط با آن مشخص شود.</p>
                            </div>
                            <span className="rounded-full bg-background px-3 py-1 text-xs" dir="ltr">{selectedBlueprint.code}</span>
                        </div>
                        <div className="mt-4 grid gap-3 lg:grid-cols-3">
                            <div className="rounded-lg bg-background/75 p-3"><p className="font-medium">ذی‌نفعان</p><div className="mt-2 flex flex-wrap gap-2">{selectedBlueprint.stakeholders.map((item) => <span key={item} className="rounded-full bg-muted px-2 py-1 text-xs">{item}</span>)}</div></div>
                            <div className="rounded-lg bg-background/75 p-3"><p className="font-medium">سطوح پاداش</p><div className="mt-2 flex flex-wrap gap-2">{selectedBlueprint.rewardBasket.map((tier) => <span key={tier.level} className="rounded-full bg-muted px-2 py-1 text-xs">{tier.level}</span>)}</div></div>
                            <div className="rounded-lg bg-background/75 p-3"><p className="font-medium">صفحات و بخش‌های مرتبط</p><div className="mt-2 flex flex-wrap gap-2">{selectedBlueprint.connectedSurfaces.slice(0, 5).map((item) => <span key={item} className="rounded-full bg-muted px-2 py-1 text-xs">{item}</span>)}</div></div>
                        </div>
                        <p className="mt-3 rounded-lg bg-background/75 p-3 text-muted-foreground"><span className="font-medium text-foreground">اقدام بعدی: </span>{selectedBlueprint.nextBuildAction}</p>
                        <div className="mt-4 flex flex-wrap gap-2">
                            <Button asChild variant="outline" size="sm">
                                <Link href={blueprintFlowUrl('/admin/campaigns', selectedBlueprint.code, 'build', selectedCampaign?.code)}>ساخت کمپین مرجع</Link>
                            </Button>
                            <Button asChild variant="outline" size="sm">
                                <Link href={blueprintFlowUrl('/admin/missions', selectedBlueprint.code, 'components', selectedCampaign?.code)}>تکمیل اجزای کمپین</Link>
                            </Button>
                            <Button asChild variant="outline" size="sm">
                                <Link href={blueprintFlowUrl('/admin/campaign-operations', selectedBlueprint.code, 'route', selectedCampaign?.code)}>رفتن به مسیر کمپین</Link>
                            </Button>
                            <Button asChild variant="ghost" size="sm">
                                <Link href="/admin/mission-blueprints">بازگشت به گنجینه</Link>
                            </Button>
                        </div>
                    </section>
                ) : null}

                <section className="grid gap-4 lg:grid-cols-2">
                    <div className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
                        <div className="mb-3 flex items-center gap-2">
                            <Megaphone className="size-4 text-muted-foreground" />
                            <h2 className="font-semibold">خلاصه بر اساس کمپین</h2>
                        </div>
                        <div className="space-y-3">
                            {campaignGroups.map((group) => (
                                <div key={group.campaign?.id ?? 'none'} className="grid grid-cols-[1fr_auto_auto] items-center gap-3 text-sm">
                                    <div className="min-w-0">
                                        <p className="truncate font-medium">{group.campaign?.name ?? 'بدون کمپین'}</p>
                                        <p className="truncate text-xs text-muted-foreground" dir="ltr">{group.campaign?.code}</p>
                                    </div>
                                    <span>{formatNumber(group.participantsCount)} عضو</span>
                                    <span>{formatNumber(group.hubCount)} هاب</span>
                                </div>
                            ))}
                        </div>
                    </div>

                    <div className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
                        <div className="mb-3 flex items-center gap-2">
                            <Network className="size-4 text-muted-foreground" />
                            <h2 className="font-semibold">خلاصه بر اساس هاب</h2>
                        </div>
                        <div className="space-y-3">
                            {hubGroups.map((group) => (
                                <div key={group.hub?.id ?? 'none'} className="grid grid-cols-[1fr_auto_auto] items-center gap-3 text-sm">
                                    <div className="min-w-0">
                                        <p className="truncate font-medium">{group.hub?.name ?? 'بدون هاب'}</p>
                                        <p className="truncate text-xs text-muted-foreground" dir="ltr">{group.hub?.code}</p>
                                    </div>
                                    <span>{formatNumber(group.participantsCount)} عضو</span>
                                    <span>{group.roles.map((role) => label(roleLabels, role)).join('، ')}</span>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                <section className="exploria-panel">
                    <div className="grid min-w-[1100px] grid-cols-[1.2fr_1fr_0.9fr_0.9fr_0.9fr_0.9fr_0.9fr] gap-3 border-b border-border/70 px-4 py-3 text-xs font-medium text-muted-foreground dark:border-sidebar-border">
                        <span>عضو</span>
                        <span>کمپین</span>
                        <span>هاب</span>
                        <span>نقش</span>
                        <span>وضعیت عضویت</span>
                        <span>اتصال ها</span>
                        <span>تماس</span>
                    </div>

                    {participants.length === 0 ? (
                        <div className="p-8 text-center text-sm text-muted-foreground">هنوز عضوی برای کمپین های قابل مشاهده ثبت نشده است.</div>
                    ) : (
                        <div className="min-w-[1100px] divide-y divide-border/70">
                            {participants.map((participant) => (
                                <article key={participant.id} className="grid grid-cols-[1.2fr_1fr_0.9fr_0.9fr_0.9fr_0.9fr_0.9fr] items-center gap-3 px-4 py-3 text-sm">
                                    <div className="min-w-0">
                                        <div className="flex items-center gap-2">
                                            <Store className="size-4 shrink-0 text-muted-foreground" />
                                            <span className="truncate font-medium">{participant.partner?.name ?? 'عضو بدون شریک'}</span>
                                        </div>
                                        <p className="mt-1 truncate text-xs text-muted-foreground">
                                            {label(typeLabels, participant.participantType)}
                                        </p>
                                    </div>

                                    <div className="min-w-0">
                                        <p className="truncate font-medium">{participant.campaign?.name}</p>
                                        <p className="mt-1 truncate text-xs text-muted-foreground" dir="ltr">{participant.campaign?.code}</p>
                                    </div>

                                    <div className="min-w-0">
                                        <div className="flex items-center gap-2">
                                            <Building2 className="size-4 shrink-0 text-muted-foreground" />
                                            <span className="truncate">{participant.hub?.name ?? participant.venue?.name}</span>
                                        </div>
                                    </div>

                                    <span>{label(roleLabels, participant.participationRole)}</span>

                                    <div className="space-y-1">
                                        <span className="inline-flex rounded-md bg-emerald-100 px-2 py-1 text-xs text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">
                                            {label(statusLabels, participant.status)}
                                        </span>
                                        <p className="text-xs text-muted-foreground">{label(onboardingLabels, participant.onboardingStatus)}</p>
                                    </div>

                                    <div className="flex items-center gap-2">
                                        <Link2 className="size-4 shrink-0 text-muted-foreground" />
                                        <span>{formatNumber(connectionTotal(participant.connections))}</span>
                                        <span className="text-xs text-muted-foreground">مورد</span>
                                    </div>

                                    <div className="min-w-0 text-xs">
                                        <p className="truncate">{participant.partner?.contactName ?? 'بدون مسئول'}</p>
                                        <p className="mt-1 truncate text-muted-foreground" dir="ltr">{participant.partner?.contactMobile}</p>
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

CampaignParticipantsIndex.layout = {
    title: 'اعضای کمپین',
    breadcrumbs: [
        {
            title: 'اعضای کمپین',
            href: '/admin/campaign-participants',
        },
    ],
};
