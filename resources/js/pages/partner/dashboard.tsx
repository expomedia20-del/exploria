import { Head, usePage } from '@inertiajs/react';
import { Building2, MapPin } from 'lucide-react';
import { useState } from 'react';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { PartnerDashboardNavigation } from '@/components/partner/partner-dashboard-navigation';
import { PartnerDashboardOverview } from '@/components/partner/partner-dashboard-overview';
import type { PartnerActionStep } from '@/components/partner/partner-dashboard-overview';
import { PartnerOfferWizard } from '@/components/partner/partner-offer-wizard';
import { PartnerProfilePanel } from '@/components/partner/partner-profile-panel';
import { PartnerRedemptionsPanel } from '@/components/partner/partner-redemptions-panel';
import { PartnerRewardsPanel } from '@/components/partner/partner-rewards-panel';
import type {
    PartnerDashboardProps,
    PartnerDashboardSection,
} from '@/types/partner-dashboard';

type SharedProps = {
    flash?: {
        success?: string;
    };
};

const dashboardSections: PartnerDashboardSection[] = [
    'overview',
    'profile',
    'offers',
    'rewards',
    'redemptions',
];

function initialSection(): PartnerDashboardSection {
    if (typeof window === 'undefined') {
        return 'overview';
    }

    const hash = window.location.hash.replace('#', '');

    return dashboardSections.includes(hash as PartnerDashboardSection)
        ? (hash as PartnerDashboardSection)
        : 'overview';
}

export default function PartnerDashboard({
    partner,
    stats,
    rewardDefinitions,
    redemptions,
    adRequests,
    proposalContext,
}: PartnerDashboardProps) {
    const { flash } = usePage<SharedProps>().props;
    const [activeSection, setActiveSection] =
        useState<PartnerDashboardSection>(initialSection);
    const profileReady = Boolean(
        partner.contactName && partner.contactMobile && partner.category,
    );
    const pendingOffers = rewardDefinitions.filter(
        (reward) => reward.approvalStatus === 'pending_review',
    ).length;
    const approvedOffers = rewardDefinitions.filter(
        (reward) => reward.approvalStatus === 'approved',
    ).length;
    const actionSteps: PartnerActionStep[] = [
        {
            title: 'تکمیل اطلاعات فروشگاه',
            description:
                'نام مسئول، موبایل و راهنمای تحویل را کامل کنید تا اجرای پیشنهاد برای ادمین و کارکنان روشن باشد.',
            complete: profileReady,
            section: 'profile',
            action: 'تکمیل اطلاعات',
        },
        {
            title: 'ثبت پیشنهاد برای کمپین',
            description: proposalContext.campaign
                ? `یک پاداش یا تخفیف متناسب با «${proposalContext.campaign.name}» پیشنهاد دهید.`
                : 'فعلاً کمپین قابل انتخابی برای این مکان وجود ندارد.',
            complete: rewardDefinitions.length > 0,
            section: 'offers',
            action: 'ساخت پیشنهاد',
        },
        {
            title: 'پیگیری تأیید و تنظیم موجودی',
            description:
                pendingOffers > 0
                    ? `${pendingOffers.toLocaleString('fa-IR')} پیشنهاد منتظر بررسی ادمین است.`
                    : approvedOffers > 0
                      ? 'پیشنهاد تأییدشده دارید؛ موجودی و زمان ارائه را کنترل کنید.'
                      : 'نتیجه بررسی ادمین پس از ارسال پیشنهاد در این بخش دیده می‌شود.',
            complete: approvedOffers > 0,
            section: approvedOffers > 0 ? 'rewards' : 'offers',
            action: approvedOffers > 0 ? 'مدیریت موجودی' : 'دیدن وضعیت پیشنهاد',
        },
        {
            title: 'تحویل پاداش به مشتری',
            description:
                stats.pendingRedemptions > 0
                    ? `${stats.pendingRedemptions.toLocaleString('fa-IR')} کد مشتری منتظر تحویل است.`
                    : 'وقتی مشتری کد مصرف آورد، تحویل پاداش و نتیجه خرید را ثبت کنید.',
            complete: stats.confirmedRedemptions > 0,
            section: 'redemptions',
            action: 'ورود کد مصرف',
        },
    ];

    const navigateTo = (section: PartnerDashboardSection) => {
        setActiveSection(section);

        if (typeof window !== 'undefined') {
            window.history.replaceState(null, '', `#${section}`);
            window.requestAnimationFrame(() =>
                window.scrollTo({ top: 0, behavior: 'smooth' }),
            );
        }
    };

    return (
        <>
            <Head title="پنل فروشگاه" />
            <div
                dir="rtl"
                className="flex h-full min-w-0 flex-1 flex-col gap-4 overflow-x-hidden p-3 sm:p-4"
            >
                <header className="flex flex-col gap-3 rounded-xl border border-sidebar-border/70 bg-background p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div className="min-w-0">
                        <p className="text-xs text-muted-foreground">
                            پنل فروشگاه / واحد تجاری
                        </p>
                        <h1 className="mt-1 truncate text-2xl leading-tight font-semibold">
                            {partner.name}
                        </h1>
                        <div className="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-muted-foreground">
                            <span className="flex items-center gap-1">
                                <MapPin className="size-3.5" />
                                {partner.venueName ?? 'مکان ثبت نشده'}
                            </span>
                            <span className="flex items-center gap-1">
                                <Building2 className="size-3.5" />
                                {partner.category ?? 'دسته‌بندی تکمیل نشده'}
                            </span>
                        </div>
                    </div>
                    <div className="rounded-lg bg-muted/40 px-3 py-2 text-xs text-muted-foreground">
                        کد فروشگاه:{' '}
                        <span className="font-mono text-foreground" dir="ltr">
                            {partner.code}
                        </span>
                    </div>
                </header>

                <PartnerDashboardNavigation
                    activeSection={activeSection}
                    pendingOffers={pendingOffers}
                    pendingRedemptions={stats.pendingRedemptions}
                    onSelect={navigateTo}
                />

                {flash?.success ? (
                    <Alert>
                        <AlertDescription>{flash.success}</AlertDescription>
                    </Alert>
                ) : null}

                <div aria-live="polite">
                    {activeSection === 'overview' ? (
                        <PartnerDashboardOverview
                            partner={partner}
                            stats={stats}
                            actionSteps={actionSteps}
                            adRequests={adRequests}
                            onNavigate={navigateTo}
                        />
                    ) : null}

                    {activeSection === 'profile' ? (
                        <PartnerProfilePanel
                            partner={partner}
                            profileReady={profileReady}
                        />
                    ) : null}

                    {activeSection === 'offers' ? (
                        <PartnerOfferWizard
                            partner={partner}
                            proposalContext={proposalContext}
                            rewardDefinitions={rewardDefinitions}
                        />
                    ) : null}

                    {activeSection === 'rewards' ? (
                        <PartnerRewardsPanel
                            rewardDefinitions={rewardDefinitions}
                            onNavigate={navigateTo}
                        />
                    ) : null}

                    {activeSection === 'redemptions' ? (
                        <PartnerRedemptionsPanel
                            redemptions={redemptions}
                            stats={stats}
                        />
                    ) : null}
                </div>
            </div>
        </>
    );
}

PartnerDashboard.layout = {
    breadcrumbs: [
        {
            title: 'پنل فروشگاه',
            href: '/partner/dashboard',
        },
    ],
};
