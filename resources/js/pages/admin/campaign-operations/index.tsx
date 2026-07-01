import { Form, Head, Link, usePage } from '@inertiajs/react';
import { useState } from 'react';
import {
    Gift,
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
import { CampaignContextNav } from '@/components/campaign-context-nav';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetFooter,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';

type SelectedCampaign = {
    id: string;
    code: string;
    name: string;
    campaignType: string;
    blueprintCode: string | null;
    status: string;
    venue: { id: string; code: string; name: string } | null;
};

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
    cycleStep?: { index: number | null; label: string | null };
    rewardType?: string;
    approvalStatus?: string;
    availabilityStatus?: string;
    source?: string | null;
    rewardTier?: string | null;
    rewardOption?: string | null;
    treasureType?: string;
    adType?: string;
    deviceType?: string;
    hub?: Hub | null;
    partner?: Partner | null;
};

type JourneySection = { title: string; items: JourneyItem[] | Participant[] };

type AlignmentReview = {
    status: 'ready' | 'needs_attention' | 'unchecked';
    expectedSteps: number;
    completedSteps: number;
    issues: {
        level: 'error' | 'warning';
        code: string;
        title: string;
        action: string;
    }[];
};

type OperationalReview = {
    status: 'ready' | 'needs_attention';
    checks: {
        key: string;
        title: string;
        description: string;
        complete: boolean;
        count: number;
        severity: 'error' | 'warning';
    }[];
    issues: {
        level: 'error' | 'warning';
        code: string;
        title: string;
        action: string;
    }[];
};

type CampaignBlueprint = {
    id: string;
    code: string;
    name: string;
    campaignType: string;
    status: string;
    routeReviewedAt: string | null;
    routeReviewNotes: string | null;
    alignment: AlignmentReview;
    operationalReview: OperationalReview;
    venue: Entity | null;
    stats: {
        participants: number;
        readyParticipants: number;
        internalSponsors: number;
        externalSponsors: number;
        missions: number;
        rewards: number;
        approvedRewards: number;
        pendingRewards: number;
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


type RewardBasketTier = {
    level: string;
    items: string[];
};

type MissionPlanStep = {
    index: number;
    userStep: string;
    recommendedTemplateCode: string;
    title: string;
    suggestedCodeSuffix: string;
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
    missionPlan: MissionPlanStep[];
    nextBuildAction: string;
};

type OperationGuide = { steps: string[]; navigation: string[] };

type OperationSelection = {
    item?: JourneyItem | Participant;
    title?: string;
    rows?: string[][];
    guide?: OperationGuide;
    sectionTitle: string;
    href: string;
    campaign: CampaignBlueprint;
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
    selectedBlueprint: SelectedBlueprint | null;
    selectedCampaign: SelectedCampaign | null;
};

type SharedProps = {
    flash?: { success?: string };
    auth: { user: { role?: string } };
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
    incentives: Gift,
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

function itemHref(item: JourneyItem | Participant) {
    if ('participationRole' in item) {
        return '/admin/campaign-participants';
    }

    const journeyItem = item as JourneyItem;

    if (journeyItem.type === 'qr') return '/admin/qr-codes';
    if (journeyItem.type === 'mission' || journeyItem.type === 'reward' || journeyItem.type === 'treasure') return '/admin/missions';
    if (journeyItem.type === 'ad') return '/admin/ads';
    if (journeyItem.type === 'display') return '/admin/display-operations';

    return '/admin/campaign-operations';
}
function itemKind(item: JourneyItem | Participant) {
    if ('participationRole' in item) return label(typeLabels, item.participantType);

    const journeyItem = item as JourneyItem;
    if (journeyItem.type === 'qr') return 'نقطه شروع QR';
    if (journeyItem.type === 'mission') return 'ماموریت';
    if (journeyItem.type === 'reward') return 'پاداش';
    if (journeyItem.type === 'treasure') return 'گنج';
    if (journeyItem.type === 'ad') return 'تبلیغ';
    if (journeyItem.type === 'display') return 'نمایشگر';

    return journeyItem.type;
}

function detailRows(item: JourneyItem | Participant, campaign: CampaignBlueprint) {
    const baseRows = [
        ['کمپین', campaign.name],
        ['مکان', campaign.venue?.name ?? 'ثبت نشده'],
        ['نوع آیتم', itemKind(item)],
        ['وضعیت', 'status' in item && item.status ? item.status : 'ثبت نشده'],
    ];

    if ('participationRole' in item) {
        return [
            ...baseRows,
            ['عضو', item.partner?.name ?? 'عضو بدون شریک'],
            ['نقش در کمپین', label(roleLabels, item.participationRole)],
            ['هاب', item.hub?.name ?? 'بدون هاب'],
            ['وضعیت آماده سازی', item.onboardingStatus],
            ['اتصال ها', `${fa(item.connections.rewards)} پاداش، ${fa(item.connections.ads)} تبلیغ، ${fa(item.connections.missions)} ماموریت`],
        ];
    }

    const journeyItem = item as JourneyItem;

    return [
        ...baseRows,
        ['کد', journeyItem.code ?? 'ثبت نشده'],
        ['گام چرخه کاربر', journeyItem.cycleStep?.label ?? 'ثبت نشده'],
        ['هاب', journeyItem.hub?.name ?? 'بدون هاب'],
        ['شریک', journeyItem.partner?.name ?? 'بدون شریک'],
        ['جزئیات', itemMeta(item)],
    ];
}
function participantNames(participants: Participant[]) {
    const names = participants.map((participant) => participant.partner?.name).filter(Boolean);

    return names.length === 0 ? 'ثبت نشده' : names.join('، ');
}

function hubDetailRows(group: HubGroup, campaign: CampaignBlueprint) {
    return [
        ['کمپین', campaign.name],
        ['مکان', campaign.venue?.name ?? 'ثبت نشده'],
        ['هاب', group.hub?.name ?? 'بدون هاب / خارجی'],
        ['تعداد اعضا', `${fa(group.participantsCount)} عضو`],
        ['تعداد اسپانسر', `${fa(group.sponsorsCount)} اسپانسر`],
        ['نقش ها', group.roles.length === 0 ? 'ثبت نشده' : group.roles.map((role) => label(roleLabels, role)).join('، ')],
        ['اعضا', participantNames(group.participants)],
    ];
}

function sponsorDetailRows(type: 'internal' | 'external', sponsors: Participant[], campaign: CampaignBlueprint) {
    return [
        ['کمپین', campaign.name],
        ['مکان', campaign.venue?.name ?? 'ثبت نشده'],
        ['نوع اسپانسر', type === 'internal' ? 'داخلی' : 'خارجی'],
        ['تعداد', `${fa(sponsors.length)} اسپانسر`],
        ['اعضا', participantNames(sponsors)],
    ];
}
function itemOperationalGuide(item: JourneyItem | Participant, campaign: CampaignBlueprint): OperationGuide {
    const venue = campaign.venue?.name ?? 'مکان پروژه';
    const hub = 'hub' in item ? item.hub?.name : null;
    const partner = 'partner' in item ? item.partner?.name : null;

    if ('participationRole' in item) {
        return {
            steps: [
                `نقش ${label(roleLabels, item.participationRole)} را برای ${item.partner?.name ?? 'عضو کمپین'} فعال کنید.`,
                'تعهدات، پاداش ها، کدهای QR و تبلیغات مرتبط با این عضو را در صفحه اعضای کمپین کنترل کنید.',
                'وضعیت آماده سازی را تا زمان تحویل تجربه به کاربر نهایی پیگیری کنید.',
            ],
            navigation: [
                item.hub?.name ? `این عضو در محدوده ${item.hub.name} دیده می شود.` : 'این عضو به هاب مشخصی وصل نشده و باید جایگاه مکانی آن تعیین شود.',
                'برای استفاده میدانی، نقطه شروع کاربر و نزدیک ترین QR یا مأموریت مرتبط با این عضو را در نقشه کمپین مشخص کنید.',
            ],
        };
    }

    const journeyItem = item as JourneyItem;

    if (journeyItem.type === 'qr') {
        return {
            steps: [
                'کاربر کمپین را با اسکن این QR آغاز می کند و ورود او به مسیر ثبت می شود.',
                'بعد از اسکن، سیستم باید مأموریت بعدی یا اولین نقطه بازدید را به کاربر نشان دهد.',
                'در اجرا، سلامت کد، محل نصب و پیام بعد از اسکن باید قبل از شروع کمپین تست شود.',
            ],
            navigation: [
                `QR باید در ورودی قابل مشاهده ${venue} یا نزدیک نقطه شروع مسیر نصب شود.`,
                'اگر چند ورودی وجود دارد، برای هر ورودی یک QR مستقل و قابل گزارش تعریف کنید.',
            ],
        };
    }

    if (journeyItem.type === 'mission') {
        return {
            steps: [
                `کاربر مأموریت «${itemTitle(item)}» را در محل تعیین شده انجام می دهد.`,
                'راهنمای مأموریت باید کوتاه، قابل فهم و قابل انجام در همان نقطه باشد؛ مثل عکس، پاسخ، مشاهده یا ثبت خاطره.',
                'بعد از ارسال یا تأیید مأموریت، امتیاز کاربر و وضعیت مرحله بعد به روز می شود.',
            ],
            navigation: [
                hub ? `کاربر باید به محدوده ${hub} هدایت شود.` : 'برای این مأموریت هنوز هاب یا نقطه مکانی مشخص نشده است.',
                'در نسخه کاربری، این آیتم باید با راهنمای نزدیک ترین مسیر، نشانه محیطی و فاصله تقریبی همراه شود.',
            ],
        };
    }

    if (journeyItem.type === 'reward' || journeyItem.type === 'treasure') {
        return {
            steps: [
                `کاربر پس از تکمیل شرط لازم، مشوق «${itemTitle(item)}» را باز می کند.`,
                'نوع دریافت باید روشن باشد: کوپن، قرعه کشی، نشان، جایزه حضوری یا هدیه شریک تجاری.',
                'تحویل یا مصرف پاداش باید با کد، اسکن یا تأیید مسئول همان نقطه ثبت شود.',
            ],
            navigation: [
                partner ? `محل دریافت یا مصرف این مشوق به ${partner} وصل است.` : 'برای این مشوق هنوز شریک تحویل دهنده مشخص نشده است.',
                'اگر مشوق حضوری است، مسیر کاربر باید تا نقطه تحویل پاداش یا گنج ادامه پیدا کند.',
            ],
        };
    }

    if (journeyItem.type === 'ad') {
        return {
            steps: [
                'پیام تبلیغاتی باید با کمپین، مکان و مرحله حضور کاربر هماهنگ شود.',
                'قبل از انتشار، زمان نمایش، مخاطب هدف، جایگاه نمایش و تأیید محتوایی کنترل شود.',
                'بعد از اجرا، بازدید، کلیک یا اسکن مرتبط با تبلیغ برای گزارش اسپانسر ثبت شود.',
            ],
            navigation: [
                hub ? `این تبلیغ در محدوده ${hub} معنا پیدا می کند.` : 'برای تبلیغ هنوز محدوده مکانی مشخص نشده است.',
                'اگر تبلیغ مسیر کاربر را تغییر می دهد، مقصد بعدی باید در راهنمای کاربر مشخص باشد.',
            ],
        };
    }

    if (journeyItem.type === 'display') {
        return {
            steps: [
                'نمایشگر باید محتوای درست کمپین را در زمان و مکان تعیین شده پخش کند.',
                'قبل از اجرا، وضعیت اتصال، برنامه پخش و نسخه محتوا کنترل شود.',
                'در پایان هر بازه، گزارش پخش و رخدادهای خطا برای تیم عملیات ثبت شود.',
            ],
            navigation: [
                hub ? `نمایشگر به محدوده ${hub} وابسته است.` : 'برای نمایشگر هنوز هاب یا نقطه نصب مشخص نشده است.',
                'در نقشه اجرایی، نمایشگرهای ثابت و سیار باید از مسیر مأموریت و نقاط تجمع جدا و قابل تشخیص باشند.',
            ],
        };
    }

    return {
        steps: ['این آیتم باید در صفحه مدیریت تکمیل و به یک نقش عملیاتی مشخص وصل شود.'],
        navigation: ['اگر آیتم وابسته به مکان است، هاب، شریک و نقطه اجرای آن باید مشخص شود.'],
    };
}

function hubOperationalGuide(group: HubGroup, campaign: CampaignBlueprint): OperationGuide {
    const hubName = group.hub?.name ?? 'بدون هاب / خارجی';

    return {
        steps: [
            `اعضای ${hubName} را از نظر نقش، آمادگی اجرا و ارتباط با مأموریت ها کنترل کنید.`,
            'برای هر عضو مشخص کنید آیا نقطه شروع، مأموریت، مشوق، تبلیغ یا تحویل پاداش دارد یا نه.',
            'اگر تعداد اعضا زیاد شد، آنها را به مسیرهای کوچک تر یا سناریوهای جداگانه تقسیم کنید.',
        ],
        navigation: [
            group.hub ? `این گروه در نقشه مکانی باید زیر محدوده ${hubName} نمایش داده شود.` : 'این گروه باید قبل از اجرا به یک محدوده مکانی یا مسیر خارجی وصل شود.',
            `در ${campaign.name} مسیر کاربر باید نشان دهد از کدام QR وارد این هاب می شود و بعد به کدام نقطه می رود.`,
        ],
    };
}

function sponsorOperationalGuide(type: 'internal' | 'external', sponsors: Participant[], campaign: CampaignBlueprint): OperationGuide {
    const label = type === 'internal' ? 'داخلی' : 'خارجی';

    return {
        steps: [
            `اسپانسرهای ${label} را از نظر نوع حمایت، تعهد محتوایی و خروجی قابل گزارش ثبت کنید.`,
            'برای هر اسپانسر مشخص کنید حمایت او پاداش، کوپن، تبلیغ، جایزه، محتوا یا مسیر ویژه است.',
            'قبل از اجرا، دسترسی گزارش گیری و سطح دیده شدن اسپانسر برای نقش های مجاز تعیین شود.',
        ],
        navigation: [
            type === 'internal'
                ? 'اسپانسر داخلی معمولاً باید به هاب یا واحد اجرایی همان مکان وصل شود.'
                : 'اسپانسر خارجی ممکن است خارج از مکان پروژه باشد و فقط در سطح ادمین مرکزی یا منطقه ای دیده شود.',
            `در ${campaign.name} باید مشخص باشد پیام اسپانسر در کدام مرحله از مسیر کاربر دیده می شود.`,
            sponsors.length === 0 ? 'هنوز اسپانسری ثبت نشده؛ بعد از ثبت، نقطه اثرگذاری آن در مسیر کمپین مشخص می شود.' : 'برای هر اسپانسر ثبت شده، نقطه تماس با کاربر یا گزارش تبلیغاتی را مشخص کنید.',
        ],
    };
}
function JourneyColumn({
    id,
    section,
    onSelect,
}: {
    id: keyof CampaignBlueprint['journey'];
    section: JourneySection;
    onSelect: (item: JourneyItem | Participant) => void;
}) {
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
                        <button
                            key={item.id}
                            type="button"
                            onClick={() => onSelect(item)}
                            className="block w-full rounded-md border border-sidebar-border/60 px-2 py-2 text-right text-xs transition hover:border-primary/40 hover:bg-muted/60 dark:border-sidebar-border"
                        >
                            <p className="line-clamp-1 font-medium">{itemTitle(item)}</p>
                            <p className="mt-1 line-clamp-1 text-muted-foreground">{itemMeta(item)}</p>
                        </button>
                    ))
                )}
            </div>
        </div>
    );
}


function OperationDetailsSheet({
    selection,
    onOpenChange,
}: {
    selection: OperationSelection | null;
    onOpenChange: (open: boolean) => void;
}) {
    const rows = selection ? (selection.rows ?? (selection.item ? detailRows(selection.item, selection.campaign) : [])) : [];
    const guide = selection?.guide;

    return (
        <Sheet open={selection !== null} onOpenChange={onOpenChange}>
            <SheetContent side="left" className="w-full overflow-y-auto sm:max-w-md" dir="rtl">
                {selection ? (
                    <>
                        <SheetHeader>
                            <SheetTitle>{selection.title ?? (selection.item ? itemTitle(selection.item) : selection.sectionTitle)}</SheetTitle>
                            <SheetDescription>
                                {selection.sectionTitle} در {selection.campaign.name}
                            </SheetDescription>
                        </SheetHeader>

                        <div className="space-y-3 px-4">
                            <div className="rounded-lg border border-border/80 bg-card/75 p-3 shadow-sm">
                                <p className="text-xs text-muted-foreground">مسیر مدیریتی</p>
                                <p className="mt-1 text-sm font-medium">{selection.href}</p>
                            </div>

                            <div className="divide-y divide-sidebar-border/70 rounded-lg border border-sidebar-border/70 dark:divide-sidebar-border dark:border-sidebar-border">
                                {rows.map(([title, value]) => (
                                    <div key={title} className="grid grid-cols-[0.85fr_1.15fr] gap-3 px-3 py-2 text-sm">
                                        <span className="text-muted-foreground">{title}</span>
                                        <span className="font-medium">{value}</span>
                                    </div>
                                ))}
                            </div>

                            {guide ? (
                                <div className="space-y-3">
                                    <section className="rounded-lg border border-border/80 bg-card/75 p-3 shadow-sm">
                                        <h3 className="text-sm font-semibold">راهنمای اجرا</h3>
                                        <ol className="mt-3 space-y-2 text-sm text-muted-foreground">
                                            {guide.steps.map((step, index) => (
                                                <li key={step} className="flex gap-2">
                                                    <span className="font-semibold text-foreground">{fa(index + 1)}</span>
                                                    <span>{step}</span>
                                                </li>
                                            ))}
                                        </ol>
                                    </section>

                                    <section className="rounded-lg border border-border/80 bg-card/75 p-3 shadow-sm">
                                        <h3 className="text-sm font-semibold">راهنمای مسیر و ناوبری</h3>
                                        <div className="mt-3 space-y-2 text-sm text-muted-foreground">
                                            {guide.navigation.map((item) => (
                                                <p key={item}>{item}</p>
                                            ))}
                                        </div>
                                    </section>
                                </div>
                            ) : null}
                        </div>

                        <SheetFooter>
                            <Button asChild>
                                <Link href={selection.href}>رفتن به صفحه مدیریت</Link>
                            </Button>
                        </SheetFooter>
                    </>
                ) : null}
            </SheetContent>
        </Sheet>
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

export default function CampaignOperationsIndex({ stats, campaigns, selectedBlueprint, selectedCampaign }: Props) {
    const [selectedOperation, setSelectedOperation] = useState<OperationSelection | null>(null);
    const { flash, auth } = usePage<SharedProps>().props;
    const canMutate = auth.user.role === 'admin' || auth.user.role === 'operator';
    const activeCampaign = selectedCampaign ? campaigns.find((campaign) => campaign.id === selectedCampaign.id) : null;
    const activeAlignmentErrors = activeCampaign?.alignment.issues.filter((issue) => issue.level === 'error') ?? [];
    const activeOperationalErrors = activeCampaign?.operationalReview.issues.filter((issue) => issue.level === 'error') ?? [];
    const routeBlockers = activeAlignmentErrors.length + activeOperationalErrors.length;

    return (
        <>
            <Head title="نقشه عملیات کمپین" />
            <OperationDetailsSheet selection={selectedOperation} onOpenChange={(open) => !open && setSelectedOperation(null)} />
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
                            <div key={title} className="rounded-lg border border-border/80 bg-card/80 px-3 py-2 shadow-sm">
                                <p className="text-muted-foreground">{title}</p>
                                <p className="mt-1 font-semibold">{fa(Number(value))}</p>
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

                {selectedCampaign ? <CampaignContextNav campaign={selectedCampaign} /> : null}

                {selectedBlueprint ? (
                    <section className="rounded-lg border border-primary/25 bg-primary/5 p-4 text-sm shadow-sm">
                        <div className="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                            <div>
                                <p className="text-xs text-muted-foreground">الگوی آماده برای اقدام</p>
                                <h2 className="mt-1 text-lg font-semibold">{selectedBlueprint.title}</h2>
                                <p className="mt-1 text-muted-foreground">داده الگو آمده تا مسیر، QR، نقطه شروع و محل تحویل پاداش به نقشه عملیات وصل شود.</p>
                            </div>
                            <span className="rounded-full bg-background px-3 py-1 text-xs" dir="ltr">{selectedBlueprint.code}</span>
                        </div>
                        <div className="mt-4 grid gap-3 lg:grid-cols-3">
                            <div className="rounded-lg bg-background/75 p-3"><p className="font-medium">ناوبری و مسیر</p><p className="mt-1 text-muted-foreground">{selectedBlueprint.navigationHint}</p></div>
                            <div className="rounded-lg bg-background/75 p-3"><p className="font-medium">چرخه و مأموریت‌های متناظر</p><ol className="mt-2 space-y-1 text-muted-foreground">{(selectedBlueprint.missionPlan ?? []).slice(0, 4).map((step) => <li key={step.userStep}>{step.index.toLocaleString('fa-IR')}. {step.userStep} · {step.operationLink}</li>)}</ol></div>
                            <div className="rounded-lg bg-background/75 p-3"><p className="font-medium">صفحات و بخش‌های مرتبط</p><div className="mt-2 flex flex-wrap gap-2">{selectedBlueprint.connectedSurfaces.slice(0, 5).map((item) => <span key={item} className="rounded-full bg-muted px-2 py-1 text-xs">{item}</span>)}</div></div>
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
                                <Link href={blueprintFlowUrl('/admin/missions', selectedBlueprint.code, 'components', selectedCampaign?.code)}>تکمیل اجزای کمپین</Link>
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

                {selectedCampaign && activeCampaign ? (
                    <section className="exploria-panel">
                        <div className="border-b border-border/70 px-4 py-3 dark:border-sidebar-border">
                            <h2 className="font-semibold">تایید مرحله ۵: مسیر عملیاتی کمپین</h2>
                            <p className="mt-1 text-sm text-muted-foreground">بعد از بررسی اتصال QR، مأموریت، مشوق، اعضا و نمایشگرها، مسیر همین کمپین را تایید کنید.</p>
                        </div>
                        <div className="grid gap-4 p-4 lg:grid-cols-[1fr_0.8fr]">
                            <div className="grid gap-3 text-sm sm:grid-cols-5">
                                {[
                                    ['QR', activeCampaign.stats.qrCodes],
                                    ['مأموریت', activeCampaign.stats.missions],
                                    ['مشوق', activeCampaign.stats.rewards + activeCampaign.stats.treasures],
                                    ['عضو آماده', activeCampaign.stats.readyParticipants],
                                    ['نمایش/تبلیغ', activeCampaign.stats.adRequests + activeCampaign.stats.displayDevices],
                                ].map(([labelText, value]) => (
                                    <div key={String(labelText)} className="rounded-lg border border-border/80 bg-card/75 p-3 shadow-sm">
                                        <p className="text-xs text-muted-foreground">{labelText}</p>
                                        <p className="mt-1 font-semibold">{fa(Number(value))}</p>
                                    </div>
                                ))}
                            </div>
                            <div className="rounded-lg border border-border/80 bg-card/75 p-3 shadow-sm">
                                <p className="text-sm font-semibold">{activeCampaign.routeReviewedAt ? 'مسیر تایید شده است' : 'مسیر هنوز تایید نشده است'}</p>
                                <p className="mt-1 text-xs text-muted-foreground">
                                    کنترل مسیر: {activeCampaign.operationalReview.status === 'ready' ? 'آماده تایید عملیاتی' : `${routeBlockers.toLocaleString('fa-IR')} مانع اصلی قبل از تایید`}
                                </p>
                                {activeCampaign.routeReviewedAt ? (
                                    <p className="mt-1 text-xs text-muted-foreground">
                                        زمان تایید: {new Intl.DateTimeFormat('fa-IR', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(activeCampaign.routeReviewedAt))}
                                    </p>
                                ) : null}
                                <div className="mt-3 grid gap-2">
                                    {activeCampaign.operationalReview.checks.map((check) => (
                                        <div key={check.key} className="flex items-start justify-between gap-3 rounded-md bg-muted/40 px-2 py-1.5 text-xs">
                                            <div>
                                                <p className="font-medium">{check.title}</p>
                                                <p className="mt-1 text-muted-foreground">{check.description}</p>
                                            </div>
                                            <span className={check.complete ? 'text-emerald-700 dark:text-emerald-300' : check.severity === 'warning' ? 'text-amber-700 dark:text-amber-300' : 'text-destructive'}>
                                                {check.complete ? 'کامل' : check.severity === 'warning' ? 'هشدار' : 'ناقص'} · {fa(check.count)}
                                            </span>
                                        </div>
                                    ))}
                                </div>
                                {activeCampaign.operationalReview.issues.length > 0 ? (
                                    <div className="mt-3 grid gap-2 rounded-md bg-muted/40 p-2 text-xs">
                                        {activeCampaign.operationalReview.issues.slice(0, 5).map((issue) => (
                                            <div key={`${issue.code}-${issue.title}`} className="rounded-md bg-background px-2 py-1.5">
                                                <p className="font-medium">{issue.title}</p>
                                                <p className="mt-1 text-muted-foreground">{issue.action}</p>
                                            </div>
                                        ))}
                                    </div>
                                ) : null}
                                {canMutate ? (
                                    <Form action="/admin/campaign-operations/review" method="post" options={{ preserveScroll: true }} className="mt-3 grid gap-3">
                                        {({ processing }) => (
                                            <>
                                                <input type="hidden" name="campaign_id" value={selectedCampaign.id} />
                                                <textarea
                                                    name="route_notes"
                                                    defaultValue={activeCampaign.routeReviewNotes ?? ''}
                                                    placeholder="یادداشت کوتاه بازبینی مسیر"
                                                    className="min-h-20 rounded-md border border-input bg-background px-3 py-2 text-sm"
                                                />
                                                <Button disabled={processing || routeBlockers > 0}>
                                                    <Route className="size-4" />
                                                    {activeCampaign.routeReviewedAt ? 'ذخیره ویرایش بازبینی مسیر' : 'تایید مسیر عملیاتی'}
                                                </Button>
                                            </>
                                        )}
                                    </Form>
                                ) : null}
                                {canMutate && activeCampaign.routeReviewedAt ? (
                                    <Form
                                        action="/admin/campaign-operations/review"
                                        method="delete"
                                        options={{ preserveScroll: true }}
                                        className="mt-2"
                                        onSubmit={(event) => {
                                            if (!window.confirm('بازبینی مسیر عملیات حذف شود؟')) {
                                                event.preventDefault();
                                            }
                                        }}
                                    >
                                        {({ processing }) => (
                                            <>
                                                <input type="hidden" name="campaign_id" value={selectedCampaign.id} />
                                                <Button type="submit" variant="outline" disabled={processing} className="w-full">
                                                    حذف بازبینی مسیر
                                                </Button>
                                            </>
                                        )}
                                    </Form>
                                ) : null}
                            </div>
                        </div>
                    </section>
                ) : null}

                {campaigns.length === 0 ? (
                    <section className="rounded-lg border border-sidebar-border/70 bg-background p-8 text-center text-sm text-muted-foreground dark:border-sidebar-border">
                        هنوز کمپینی برای این سطح دسترسی دیده نمی‌شود.
                    </section>
                ) : (
                    campaigns.map((campaign) => (
                        <section key={campaign.id} className="exploria-panel">
                            <div className="flex flex-col gap-3 border-b border-border/70 p-4 dark:border-sidebar-border lg:flex-row lg:items-start lg:justify-between">
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
                                        <JourneyColumn
                                            id="entry"
                                            section={campaign.journey.entry}
                                            onSelect={(item) =>
                                                setSelectedOperation({
                                                    item,
                                                    sectionTitle: campaign.journey.entry.title,
                                                    href: itemHref(item),
                                                    guide: itemOperationalGuide(item, campaign),
                                                    campaign,
                                                })
                                            }
                                        />
                                        <JourneyColumn
                                            id="missions"
                                            section={campaign.journey.missions}
                                            onSelect={(item) =>
                                                setSelectedOperation({
                                                    item,
                                                    sectionTitle: campaign.journey.missions.title,
                                                    href: itemHref(item),
                                                    guide: itemOperationalGuide(item, campaign),
                                                    campaign,
                                                })
                                            }
                                        />
                                        <JourneyColumn
                                            id="incentives"
                                            section={campaign.journey.incentives}
                                            onSelect={(item) =>
                                                setSelectedOperation({
                                                    item,
                                                    sectionTitle: campaign.journey.incentives.title,
                                                    href: itemHref(item),
                                                    guide: itemOperationalGuide(item, campaign),
                                                    campaign,
                                                })
                                            }
                                        />
                                        <JourneyColumn
                                            id="commercial"
                                            section={campaign.journey.commercial}
                                            onSelect={(item) =>
                                                setSelectedOperation({
                                                    item,
                                                    sectionTitle: campaign.journey.commercial.title,
                                                    href: itemHref(item),
                                                    guide: itemOperationalGuide(item, campaign),
                                                    campaign,
                                                })
                                            }
                                        />
                                        <JourneyColumn
                                            id="media"
                                            section={campaign.journey.media}
                                            onSelect={(item) =>
                                                setSelectedOperation({
                                                    item,
                                                    sectionTitle: campaign.journey.media.title,
                                                    href: itemHref(item),
                                                    guide: itemOperationalGuide(item, campaign),
                                                    campaign,
                                                })
                                            }
                                        />
                                    </div>
                                </div>

                                <aside className="space-y-4">
                                    <div className="rounded-lg border border-border/80 bg-card/75 p-3 shadow-sm">
                                        <div className="mb-3 flex items-center gap-2">
                                            <Network className="size-4 text-muted-foreground" />
                                            <h3 className="text-sm font-semibold">اعضا به تفکیک هاب</h3>
                                        </div>
                                        <div className="space-y-3">
                                            {campaign.participantsByHub.map((group) => (
                                                <button
                                                    key={group.hub?.id ?? 'external'}
                                                    type="button"
                                                    onClick={() =>
                                                        setSelectedOperation({
                                                            title: group.hub?.name ?? 'بدون هاب / خارجی',
                                                            rows: hubDetailRows(group, campaign),
                                                            guide: hubOperationalGuide(group, campaign),
                                                            sectionTitle: 'اعضا به تفکیک هاب',
                                                            href: '/admin/campaign-participants',
                                                            campaign,
                                                        })
                                                    }
                                                    className="block w-full rounded-md px-2 py-2 text-right text-sm transition hover:bg-muted/60"
                                                >
                                                    <div className="flex items-center justify-between gap-2">
                                                        <span className="font-medium">{group.hub?.name ?? 'بدون هاب / خارجی'}</span>
                                                        <span className="text-xs text-muted-foreground">{fa(group.participantsCount)} عضو</span>
                                                    </div>
                                                    <p className="mt-1 line-clamp-1 text-xs text-muted-foreground">
                                                        {group.roles.map((role) => label(roleLabels, role)).join('، ')}
                                                    </p>
                                                </button>
                                            ))}
                                        </div>
                                    </div>

                                    <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                                        <button
                                            type="button"
                                            onClick={() =>
                                                setSelectedOperation({
                                                    title: 'اسپانسر داخلی',
                                                    rows: sponsorDetailRows('internal', campaign.sponsors.internal, campaign),
                                                    guide: sponsorOperationalGuide('internal', campaign.sponsors.internal, campaign),
                                                    sectionTitle: 'اسپانسرهای کمپین',
                                                    href: '/admin/campaign-participants',
                                                    campaign,
                                                })
                                            }
                                            className="block w-full rounded-lg border border-sidebar-border/70 p-3 text-right transition hover:bg-muted/60 dark:border-sidebar-border"
                                        >
                                            <div className="mb-2 flex items-center gap-2">
                                                <Building2 className="size-4 text-muted-foreground" />
                                                <h3 className="text-sm font-semibold">اسپانسر داخلی</h3>
                                            </div>
                                            <p className="text-xs text-muted-foreground">{participantNames(campaign.sponsors.internal)}</p>
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() =>
                                                setSelectedOperation({
                                                    title: 'اسپانسر خارجی',
                                                    rows: sponsorDetailRows('external', campaign.sponsors.external, campaign),
                                                    guide: sponsorOperationalGuide('external', campaign.sponsors.external, campaign),
                                                    sectionTitle: 'اسپانسرهای کمپین',
                                                    href: '/admin/campaign-participants',
                                                    campaign,
                                                })
                                            }
                                            className="block w-full rounded-lg border border-sidebar-border/70 p-3 text-right transition hover:bg-muted/60 dark:border-sidebar-border"
                                        >
                                            <div className="mb-2 flex items-center gap-2">
                                                <Gem className="size-4 text-muted-foreground" />
                                                <h3 className="text-sm font-semibold">اسپانسر خارجی</h3>
                                            </div>
                                            <p className="text-xs text-muted-foreground">{participantNames(campaign.sponsors.external)}</p>
                                        </button>
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
