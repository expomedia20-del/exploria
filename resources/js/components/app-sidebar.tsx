import { Link } from '@inertiajs/react';
import {
    BookOpen,
    FolderGit2,
    LayoutGrid,
    MapPinned,
    Megaphone,
    MonitorPlay,
    Network,
    QrCode,
    ShoppingBag,
    Store,
    Trophy,
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

const mainNavItems: NavItem[] = [
    {
        title: 'داشبورد',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'مدیریت QR',
        href: '/admin/qr-codes',
        icon: QrCode,
    },
    {
        title: 'کمپین‌ها',
        href: '/admin/campaigns',
        icon: Megaphone,
    },
    {
        title: 'ماموریت و پاداش',
        href: '/admin/missions',
        icon: Trophy,
    },
    {
        title: 'مدیریت مکان‌ها',
        href: '/admin/venues',
        icon: MapPinned,
    },
    {
        title: 'مدیریت شرکا',
        href: '/admin/partners',
        icon: Store,
    },
    {
        title: 'تبلیغات مستقل',
        href: '/admin/ads',
        icon: Megaphone,
    },
    {
        title: 'عملیات نمایشگرها',
        href: '/admin/display-operations',
        icon: MonitorPlay,
    },
    {
        title: 'پنل فروشگاه',
        href: '/partner/dashboard',
        icon: ShoppingBag,
    },
    {
        title: 'پنل مدیر رواق',
        href: '/hub/dashboard',
        icon: Network,
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

export function AppSidebar() {
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
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
