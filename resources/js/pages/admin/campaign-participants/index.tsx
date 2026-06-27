import { Head } from '@inertiajs/react';
import { Building2, Link2, Megaphone, Network, Store } from 'lucide-react';

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

export default function CampaignParticipantsIndex({
    stats,
    participants,
    campaignGroups,
    hubGroups,
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
                            <div key={title} className="rounded-lg border border-sidebar-border/70 px-3 py-2 dark:border-sidebar-border">
                                <p className="text-muted-foreground">{title}</p>
                                <p className="mt-1 font-semibold">{formatNumber(Number(value))}</p>
                            </div>
                        ))}
                    </div>
                </header>

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

                <section className="rounded-lg border border-sidebar-border/70 bg-background dark:border-sidebar-border">
                    <div className="grid min-w-[1100px] grid-cols-[1.2fr_1fr_0.9fr_0.9fr_0.9fr_0.9fr_0.9fr] gap-3 border-b border-sidebar-border/70 px-4 py-3 text-xs font-medium text-muted-foreground dark:border-sidebar-border">
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
                        <div className="min-w-[1100px] divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
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