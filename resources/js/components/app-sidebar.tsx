import { Link, usePage } from '@inertiajs/react';
import {
    BookOpen,
    ClipboardCheck,
    FolderGit2,
    LayoutGrid,
    MapPinned,
    Megaphone,
    MonitorPlay,
    Network,
    QrCode,
    Route,
    ShieldCheck,
    ShoppingBag,
    Store,
    Trophy,
    UserCog,
    UsersRound,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

type UserRole =
    | 'admin'
    | 'operator'
    | 'viewer'
    | 'visitor'
    | 'shop_partner'
    | 'hub_manager'
    | 'sponsor';

type RoleAwareNavItem = NavItem & {
    roles?: UserRole[];
};

type SharedProps = {
    auth?: {
        user?: {
            role?: UserRole;
        };
    };
};

const mainNavItems: RoleAwareNavItem[] = [
    {
        title: 'داشبورد',
        href: dashboard(),
        icon: LayoutGrid,
        group: 'نمای کلی',
    },
    {
        title: 'شناخت‌نامه مکان',
        href: '/admin/venues',
        icon: MapPinned,
        group: '۱. طراحی و شروع کمپین',
        roles: ['admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'گنجینه الگوها',
        href: '/admin/mission-blueprints',
        icon: BookOpen,
        group: '۱. طراحی و شروع کمپین',
        roles: ['admin'],
    },
    {
        title: 'ساخت کمپین',
        href: '/admin/campaign-builder',
        icon: ClipboardCheck,
        group: '۱. طراحی و شروع کمپین',
        roles: ['admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'کمپین‌ها',
        href: '/admin/campaigns',
        icon: Megaphone,
        group: '۱. طراحی و شروع کمپین',
        roles: ['admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'مأموریت‌ها و پاداش‌ها',
        href: '/admin/missions',
        icon: Trophy,
        group: '۲. تکمیل و اجرای کمپین',
        roles: ['admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'اعضا و شرکای کمپین',
        href: '/admin/campaign-participants',
        icon: UsersRound,
        group: '۲. تکمیل و اجرای کمپین',
        roles: ['admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'نقشه عملیات کمپین',
        href: '/admin/campaign-operations',
        icon: Route,
        group: '۲. تکمیل و اجرای کمپین',
        roles: ['admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'مدیریت QR',
        href: '/admin/qr-codes',
        icon: QrCode,
        group: '۳. زیرساخت عملیات',
        roles: ['admin', 'operator', 'viewer'],
    },
    {
        title: 'مدیریت شرکا',
        href: '/admin/partners',
        icon: Store,
        group: '۳. زیرساخت عملیات',
        roles: ['admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'تبلیغات مستقل',
        href: '/admin/ads',
        icon: Megaphone,
        group: '۳. زیرساخت عملیات',
        roles: ['admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'عملیات نمایشگرها',
        href: '/admin/display-operations',
        icon: MonitorPlay,
        group: '۳. زیرساخت عملیات',
        roles: ['admin', 'operator'],
    },
    {
        title: 'پنل فروشگاه / اسپانسر',
        href: '/partner/dashboard',
        icon: ShoppingBag,
        group: '۴. پنل نقش‌ها',
        roles: ['admin', 'shop_partner', 'sponsor'],
    },
    {
        title: 'پنل مدیر هاب',
        href: '/hub/dashboard',
        icon: Network,
        group: '۴. پنل نقش‌ها',
        roles: ['admin', 'hub_manager'],
    },
    {
        title: 'نقش‌ها و عملیات',
        href: '/admin/role-operations',
        icon: UserCog,
        group: '۵. دسترسی و کنترل',
        roles: ['admin', 'operator', 'viewer'],
    },
    {
        title: 'تخصیص دسترسی',
        href: '/admin/access-scopes',
        icon: ShieldCheck,
        group: '۵. دسترسی و کنترل',
        roles: ['admin', 'operator', 'viewer'],
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'GitHub پروژه',
        href: 'https://github.com/expomedia20-del/exploria',
        icon: FolderGit2,
    },
];

function isVisibleForRole(item: RoleAwareNavItem, role?: UserRole) {
    return !item.roles || (role !== undefined && item.roles.includes(role));
}

export function AppSidebar() {
    const { auth } = usePage<SharedProps>().props;
    const role = auth?.user?.role;
    const visibleNavItems = mainNavItems.filter((item) =>
        isVisibleForRole(item, role),
    );

    return (
        <Sidebar collapsible="icon" variant="inset" dir="rtl">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={visibleNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
