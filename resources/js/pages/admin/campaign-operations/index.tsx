import { Head } from '@inertiajs/react';
import {
    BadgeGift,
    Building2,
    ChevronLeft,
    Gem,
    Megaphone,
    MonitorPlay,
    Network,
    QrCode,
    Route,
    Sparkles,
    Store,
    Trophy,
} from 'lucide-react';

type Entity = { id: string; code: string; name: string };
type Hub = Entity & { hubType?: string };
type Partner = Entity & { partnerType: string };

type Participant = {
    id: string;
    participantType: string;
    participationRole: string;
    status: string;
    onboardingStatus: string;
    hub: Hub | null;
    partner: Partner | null;
    connections: { rewards: number; ads: number; qrCodes: number; missions: number };
};

type HubGroup = {
    hub: Hub | null;
    participantsCount: number;
    sponsorsCount: number;
    roles: string[];
    participants: Participant[];
};

type JourneyItem = {
    id: string;
    type: string;
    code?: string;
    label?: string | null;
    title?: string;
    name?: string;
    status?: string;
    points?: number;
    rewardType?: string;
    treasureType?: string;
    adType?: string;
    deviceType?: string;
    hub?: Hub | null;
    partner?: Partner | null;
};

type JourneySection = { title: string; items: JourneyItem[] | Participant[] };

type CampaignBlueprint = {
    id: string;
    code: string;
    name: string;
    campaignType: string;
    status: string;
    venue: Entity | null;
    stats: {
        participants: number;
        internalSponsors: number;
        externalSponsors: number;
        missions: number;
        rewards: number;
        treasures: number;
        qrCodes: number;
        adRequests: number;
        displayDevices: number;
    };
    participantsByHub: HubGroup[];
    sponsors: { internal: Participant[]; external: Participant[] };
    journey: {
        entry: JourneySection;
        missions: JourneySection;
        incentives: JourneySection;
        commercial: JourneySection;
        media: JourneySection;
    };
};

type Props = {
    stats: {
        campaigns: number;
        participants: number;
        internalSponsors: number;
        externalSponsors: number;
        missions: number;
        incentives: number;
        entryPoints: number;
        adRequests: number;
        displayDevices: number;
    };
    campaigns: CampaignBlueprint[];
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

const sectionIcons = {
    entry: QrCode,
    missions: Trophy,
    incentives: BadgeGift,
    commercial: Store,
    media: MonitorPlay,
};

function fa(value: number) {
    return value.toLocaleString('fa-IR');
}

function label(map: Record<string, string>, value?: string) {
    return value ? (map[value] ?? value) : 'ثبت نشده';
}

function itemTitle(item: JourneyItem | Participant) {
    if ('partner' in item && 'participationRole' in item) {
        return item.partner?.name ?? 'عضو بدون شریک';
    }

    const journeyItem = item as JourneyItem;
    return journeyItem.title ?? journeyItem.name ?? journeyItem.label ?? journeyItem.code ?? 'آیتم عملیاتی';
}

function itemMeta(item: JourneyItem | Participant) {
    if ('participationRole' in item) {
        return label(roleLabels, item.participationRole);
    }

    const journeyItem = item as JourneyItem;
    if (journeyItem.type === 'mission') return `${journeyItem.points ?? 0} امتیاز`;
    if (journeyItem.rewardType) return journeyItem.rewardType;
    if (journeyItem.treasureType) return journeyItem.treasureType;
    if (journeyItem.adType) return journeyItem.adType;
    if (journeyItem.deviceType) return journeyItem.deviceType;
    return journeyItem.status ?? journeyItem.type;
}

function JourneyColumn({ id, section }: { id: keyof CampaignBlueprint['journey']; section: JourneySection }) {
    const Icon = sectionIcons[id];

    return (
        <div className="min-w-[220px] rounded-lg border border-sidebar-border/70 bg-background p-3 dark:border-sidebar-border">
            <div className="mb-3 flex items-center gap-2">
                <Icon className="size-4 text-muted-foreground" />
                <h3 className="text-sm font-semibold">{section.title}</h3>
            </div>
            <div className="space-y-2">
                {section.items.length === 0 ? (
                    <p className="text-xs text-muted-foreground">هنوز آیتمی ثبت نشده است.</p>
                ) : (
                    section.items.slice(0, 6).map((item) => (
                        <div key={item.id} className="rounded-md border border-sidebar-border/60 px-2 py-2 text-xs dark:border-sidebar-border">
                            <p className="line-clamp-1 font-medium">{itemTitle(item)}</p>
                            <p className="mt-1 line-clamp-1 text-muted-foreground">{itemMeta(item)}</p>
                        </div>
                    ))
                )}
            </div>
        </div>
    );
}

export default function CampaignOperationsIndex({ stats, campaigns }: Props) {
    return (
        <>
            <Head title="نقشه عملیات کمپین" />
            <div dir="rtl" className="flex h-full flex-1 flex-col gap-5 overflow-x-auto p-4">
                <header className="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p className="text-sm text-muted-foreground">طرح یکپارچه بازی، بازدید و تبلیغات</p>
                        <h1 className="mt-1 text-2xl font-semibold">نقشه عملیات کمپین</h1>
                    </div>
                    <div className="grid grid-cols-2 gap-3 text-sm sm:grid-cols-5">
                        {[
                            ['کمپین', stats.campaigns],
                            ['عضو', stats.participants],
                            ['اسپانسر داخلی', stats.internalSponsors],
                            ['اسپانسر خارجی', stats.externalSponsors],
                            ['مشوق', stats.incentives],
                        ].map(([title, value]) => (
                            <div key={title} className="rounded-lg border border-sidebar-border/70 px-3 py-2 dark:border-sidebar-border">
                                <p className="text-muted-foreground">{title}</p>
                                <p className="mt-1 font-semibold">{fa(Number(value))}</p>
                            </div>
                        ))}
                    </div>
                </header>

                {campaigns.length === 0 ? (
                    <section className="rounded-lg border border-sidebar-border/70 bg-background p-8 text-center text-sm text-muted-foreground dark:border-sidebar-border">
                        هنوز کمپینی برای این سطح دسترسی دیده نمی‌شود.
                    </section>
                ) : (
                    campaigns.map((campaign) => (
                        <section key={campaign.id} className="rounded-lg border border-sidebar-border/70 bg-background dark:border-sidebar-border">
                            <div className="flex flex-col gap-3 border-b border-sidebar-border/70 p-4 dark:border-sidebar-border lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <div className="flex items-center gap-2">
                                        <Route className="size-5 text-muted-foreground" />
                                        <h2 className="text-lg font-semibold">{campaign.name}</h2>
                                    </div>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        {campaign.venue?.name} <ChevronLeft className="inline size-3" /> {campaign.code}
                                    </p>
                                </div>
                                <div className="grid grid-cols-3 gap-2 text-xs sm:grid-cols-6">
                                    {[
                                        ['عضو', campaign.stats.participants],
                                        ['ماموریت', campaign.stats.missions],
                                        ['QR', campaign.stats.qrCodes],
                                        ['پاداش', campaign.stats.rewards],
                                        ['گنج', campaign.stats.treasures],
                                        ['نمایشگر', campaign.stats.displayDevices],
                                    ].map(([title, value]) => (
                                        <div key={title} className="rounded-md bg-muted px-2 py-2">
                                            <p className="text-muted-foreground">{title}</p>
                                            <p className="mt-1 font-semibold">{fa(Number(value))}</p>
                                        </div>
                                    ))}
                                </div>
                            </div>

                            <div className="grid gap-4 p-4 xl:grid-cols-[1fr_0.72fr]">
                                <div>
                                    <div className="mb-3 flex items-center gap-2">
                                        <Sparkles className="size-4 text-muted-foreground" />
                                        <h3 className="font-semibold">چرخه کاربر در کمپین</h3>
                                    </div>
                                    <div className="flex gap-3 overflow-x-auto pb-1">
                                        <JourneyColumn id="entry" section={campaign.journey.entry} />
                                        <JourneyColumn id="missions" section={campaign.journey.missions} />
                                        <JourneyColumn id="incentives" section={campaign.journey.incentives} />
                                        <JourneyColumn id="commercial" section={campaign.journey.commercial} />
                                        <JourneyColumn id="media" section={campaign.journey.media} />
                                    </div>
                                </div>

                                <aside className="space-y-4">
                                    <div className="rounded-lg border border-sidebar-border/70 p-3 dark:border-sidebar-border">
                                        <div className="mb-3 flex items-center gap-2">
                                            <Network className="size-4 text-muted-foreground" />
                                            <h3 className="text-sm font-semibold">اعضا به تفکیک هاب</h3>
                                        </div>
                                        <div className="space-y-3">
                                            {campaign.participantsByHub.map((group) => (
                                                <div key={group.hub?.id ?? 'external'} className="text-sm">
                                                    <div className="flex items-center justify-between gap-2">
                                                        <span className="font-medium">{group.hub?.name ?? 'بدون هاب / خارجی'}</span>
                                                        <span className="text-xs text-muted-foreground">{fa(group.participantsCount)} عضو</span>
                                                    </div>
                                                    <p className="mt-1 line-clamp-1 text-xs text-muted-foreground">
                                                        {group.roles.map((role) => label(roleLabels, role)).join('، ')}
                                                    </p>
                                                </div>
                                            ))}
                                        </div>
                                    </div>

                                    <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                                        <div className="rounded-lg border border-sidebar-border/70 p-3 dark:border-sidebar-border">
                                            <div className="mb-2 flex items-center gap-2">
                                                <Building2 className="size-4 text-muted-foreground" />
                                                <h3 className="text-sm font-semibold">اسپانسر داخلی</h3>
                                            </div>
                                            <p className="text-xs text-muted-foreground">{campaign.sponsors.internal.length === 0 ? 'ثبت نشده' : campaign.sponsors.internal.map((item) => item.partner?.name).join('، ')}</p>
                                        </div>
                                        <div className="rounded-lg border border-sidebar-border/70 p-3 dark:border-sidebar-border">
                                            <div className="mb-2 flex items-center gap-2">
                                                <Gem className="size-4 text-muted-foreground" />
                                                <h3 className="text-sm font-semibold">اسپانسر خارجی</h3>
                                            </div>
                                            <p className="text-xs text-muted-foreground">{campaign.sponsors.external.length === 0 ? 'ثبت نشده' : campaign.sponsors.external.map((item) => item.partner?.name).join('، ')}</p>
                                        </div>
                                    </div>
                                </aside>
                            </div>
                        </section>
                    ))
                )}
            </div>
        </>
    );
}

CampaignOperationsIndex.layout = {
    title: 'نقشه عملیات',
    breadcrumbs: [
        {
            title: 'نقشه عملیات',
            href: '/admin/campaign-operations',
        },
    ],
};