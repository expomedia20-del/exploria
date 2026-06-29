import { Link, usePage } from '@inertiajs/react';
import {
    BookOpen,
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
    },
    {
        title: 'مدیریت QR',
        href: '/admin/qr-codes',
        icon: QrCode,
        roles: ['admin', 'operator', 'viewer'],
    },
    {
        title: 'کمپین‌ها',
        href: '/admin/campaigns',
        icon: Megaphone,
        roles: ['admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'نقشه عملیات',
        href: '/admin/campaign-operations',
        icon: Route,
        roles: ['admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'اعضای کمپین',
        href: '/admin/campaign-participants',
        icon: UsersRound,
        roles: ['admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'ماموریت و پاداش',
        href: '/admin/missions',
        icon: Trophy,
        roles: ['admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'گنجینه ماموریت‌ها',
        href: '/admin/mission-blueprints',
        icon: BookOpen,
        roles: ['admin'],
    },
    {
        title: 'مدیریت مکان‌ها',
        href: '/admin/venues',
        icon: MapPinned,
        roles: ['admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'مدیریت شرکا',
        href: '/admin/partners',
        icon: Store,
        roles: ['admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'تبلیغات مستقل',
        href: '/admin/ads',
        icon: Megaphone,
        roles: ['admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'عملیات نمایشگرها',
        href: '/admin/display-operations',
        icon: MonitorPlay,
        roles: ['admin', 'operator'],
    },
    {
        title: 'نقش‌ها و عملیات',
        href: '/admin/role-operations',
        icon: UserCog,
        roles: ['admin', 'operator', 'viewer'],
    },
    {
        title: 'تخصیص دسترسی',
        href: '/admin/access-scopes',
        icon: ShieldCheck,
        roles: ['admin', 'operator', 'viewer'],
    },
    {
        title: 'پنل فروشگاه',
        href: '/partner/dashboard',
        icon: ShoppingBag,
        roles: ['admin', 'shop_partner', 'sponsor'],
    },
    {
        title: 'پنل مدیر رواق',
        href: '/hub/dashboard',
        icon: Network,
        roles: ['admin', 'hub_manager'],
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/react-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#react',
        icon: BookOpen,
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
        <Sidebar collapsible="icon" variant="inset">
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
