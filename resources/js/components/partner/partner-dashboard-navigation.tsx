import {
    BadgePercent,
    CircleUserRound,
    LayoutDashboard,
    PackageCheck,
    ScanLine,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import type { PartnerDashboardSection } from '@/types/partner-dashboard';

type NavigationItem = {
    id: PartnerDashboardSection;
    label: string;
    description: string;
    icon: LucideIcon;
    badge?: number;
};

export function PartnerDashboardNavigation({
    activeSection,
    pendingOffers,
    pendingRedemptions,
    onSelect,
}: {
    activeSection: PartnerDashboardSection;
    pendingOffers: number;
    pendingRedemptions: number;
    onSelect: (section: PartnerDashboardSection) => void;
}) {
    const items: NavigationItem[] = [
        {
            id: 'overview',
            label: 'نمای کلی',
            description: 'وضعیت و اقدام بعدی',
            icon: LayoutDashboard,
        },
        {
            id: 'profile',
            label: 'اطلاعات فروشگاه',
            description: 'مشخصات و نحوه تحویل',
            icon: CircleUserRound,
        },
        {
            id: 'offers',
            label: 'پیشنهاد به کمپین',
            description: 'ساخت و پیگیری پیشنهاد',
            icon: BadgePercent,
            badge: pendingOffers,
        },
        {
            id: 'rewards',
            label: 'پاداش و موجودی',
            description: 'تنظیم پیشنهادهای تأییدشده',
            icon: PackageCheck,
        },
        {
            id: 'redemptions',
            label: 'تحویل به مشتری',
            description: 'ثبت کد و سوابق مصرف',
            icon: ScanLine,
            badge: pendingRedemptions,
        },
    ];

    return (
        <nav
            aria-label="بخش‌های پنل فروشگاه"
            className="sticky top-0 z-10 -mx-3 overflow-x-auto border-y border-sidebar-border/70 bg-background/95 px-3 py-2 backdrop-blur sm:-mx-4 sm:px-4 lg:static lg:mx-0 lg:overflow-visible lg:rounded-xl lg:border lg:p-2"
        >
            <div className="flex min-w-max gap-2 lg:grid lg:min-w-0 lg:grid-cols-5">
                {items.map((item) => {
                    const Icon = item.icon;
                    const isActive = activeSection === item.id;

                    return (
                        <button
                            key={item.id}
                            type="button"
                            aria-current={isActive ? 'page' : undefined}
                            onClick={() => onSelect(item.id)}
                            className={`flex min-w-36 items-center gap-2 rounded-lg border px-3 py-2.5 text-right transition lg:min-w-0 ${
                                isActive
                                    ? 'border-primary bg-primary text-primary-foreground shadow-sm'
                                    : 'border-transparent bg-muted/35 hover:border-primary/25 hover:bg-muted/70'
                            }`}
                        >
                            <Icon className="size-4 shrink-0" />
                            <span className="min-w-0 flex-1">
                                <span className="block text-sm font-medium">
                                    {item.label}
                                </span>
                                <span
                                    className={`hidden truncate text-[11px] lg:block ${
                                        isActive
                                            ? 'text-primary-foreground/75'
                                            : 'text-muted-foreground'
                                    }`}
                                >
                                    {item.description}
                                </span>
                            </span>
                            {item.badge ? (
                                <span
                                    className={`rounded-full px-2 py-0.5 text-xs font-semibold ${
                                        isActive
                                            ? 'bg-background/20'
                                            : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200'
                                    }`}
                                >
                                    {item.badge.toLocaleString('fa-IR')}
                                </span>
                            ) : null}
                        </button>
                    );
                })}
            </div>
        </nav>
    );
}
