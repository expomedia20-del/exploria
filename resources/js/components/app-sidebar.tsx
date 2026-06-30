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
        title: 'کارگاه ساخت کمپین',
        href: '/admin/campaign-builder',
        icon: ClipboardCheck,
        group: 'مسیر ساخت کمپین',
        roles: ['admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'گنجینه الگوها',
        href: '/admin/mission-blueprints',
        icon: BookOpen,
        group: 'مسیر ساخت کمپین',
        roles: ['admin'],
    },
    {
        title: 'کمپین‌ها',
        href: '/admin/campaigns',
        icon: Megaphone,
        group: 'مسیر ساخت کمپین',
        roles: ['admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'ماموریت و پاداش',
        href: '/admin/missions',
        icon: Trophy,
        group: 'مسیر ساخت کمپین',
        roles: ['admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'اعضا و شرکای کمپین',
        href: '/admin/campaign-participants',
        icon: UsersRound,
        group: 'مسیر ساخت کمپین',
        roles: ['admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'نقشه عملیات کمپین',
        href: '/admin/campaign-operations',
        icon: Route,
        group: 'مسیر ساخت کمپین',
        roles: ['admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'مدیریت QR',
        href: '/admin/qr-codes',
        icon: QrCode,
        group: 'ثبت‌های عملیاتی',
        roles: ['admin', 'operator', 'viewer'],
    },
    {
        title: 'مدیریت مکان‌ها',
        href: '/admin/venues',
        icon: MapPinned,
        group: 'ثبت‌های عملیاتی',
        roles: ['admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'مدیریت شرکا',
        href: '/admin/partners',
        icon: Store,
        group: 'ثبت‌های عملیاتی',
        roles: ['admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'تبلیغات مستقل',
        href: '/admin/ads',
        icon: Megaphone,
        group: 'ثبت‌های عملیاتی',
        roles: ['admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'عملیات نمایشگرها',
        href: '/admin/display-operations',
        icon: MonitorPlay,
        group: 'ثبت‌های عملیاتی',
        roles: ['admin', 'operator'],
    },
    {
        title: 'پنل فروشگاه',
        href: '/partner/dashboard',
        icon: ShoppingBag,
        group: 'پنل نقش‌ها',
        roles: ['admin', 'shop_partner', 'sponsor'],
    },
    {
        title: 'پنل مدیر هاب',
        href: '/hub/dashboard',
        icon: Network,
        group: 'پنل نقش‌ها',
        roles: ['admin', 'hub_manager'],
    },
    {
        title: 'نقش‌ها و عملیات',
        href: '/admin/role-operations',
        icon: UserCog,
        group: 'دسترسی و کنترل',
        roles: ['admin', 'operator', 'viewer'],
    },
    {
        title: 'تخصیص دسترسی',
        href: '/admin/access-scopes',
        icon: ShieldCheck,
        group: 'دسترسی و کنترل',
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
