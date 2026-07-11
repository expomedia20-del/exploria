import { Link, usePage } from '@inertiajs/react';
import {
    BadgeDollarSign,
    BookOpen,
    BriefcaseBusiness,
    Building2,
    BotMessageSquare,
    ClipboardCheck,
    FolderGit2,
    LayoutGrid,
    MapPinned,
    Megaphone,
    MonitorPlay,
    Network,
    PlayCircle,
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
        group: '۰. نمای کلی',
        roles: ['admin', 'operator', 'viewer'],
    },
    {
        title: 'ارزیابی مکان',
        href: '/admin/venues',
        icon: MapPinned,
        group: '۱. تیم داخلی اکسپلوریا',
        roles: ['admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'پنل عملیات داخلی',
        href: '/admin/internal-operations',
        icon: ClipboardCheck,
        group: '۱. تیم داخلی اکسپلوریا',
        roles: ['admin', 'operator', 'viewer'],
    },
    {
        title: 'چرخه دمو اکوپارک',
        href: '/admin/demo-cycle',
        icon: Route,
        group: '۱. تیم داخلی اکسپلوریا',
        roles: ['admin', 'operator', 'viewer'],
    },
    {
        title: 'تجاری‌سازی و فروش',
        href: '/admin/commercialization',
        icon: BriefcaseBusiness,
        group: '۱. تیم داخلی اکسپلوریا',
        roles: ['admin', 'operator', 'viewer'],
    },
    {
        title: 'گنجینه الگوها',
        href: '/admin/mission-blueprints',
        icon: BookOpen,
        group: '۱. تیم داخلی اکسپلوریا',
        roles: ['admin'],
    },
    {
        title: 'ثبت و انتخاب کمپین',
        href: '/admin/campaigns',
        icon: Megaphone,
        group: '۱. تیم داخلی اکسپلوریا',
        roles: ['admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'کارگاه ساخت کمپین',
        href: '/admin/campaign-builder',
        icon: ClipboardCheck,
        group: '۱. تیم داخلی اکسپلوریا',
        roles: ['admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'ماموریت، گنج و پاداش',
        href: '/admin/missions',
        icon: Trophy,
        group: '۱. تیم داخلی اکسپلوریا',
        roles: ['admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'مدیریت QR و ورود',
        href: '/admin/qr-codes',
        icon: QrCode,
        group: '۱. تیم داخلی اکسپلوریا',
        roles: ['admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'نقشه عملیات کمپین',
        href: '/admin/campaign-operations',
        icon: Route,
        group: '۱. تیم داخلی اکسپلوریا',
        roles: ['admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'مدیریت شرکا',
        href: '/admin/partners',
        icon: Store,
        group: '۳. واحدهای تجاری و اسپانسرها',
        roles: ['admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'عملیات تبلیغات و نمایشگرها',
        href: '/admin/display-operations',
        icon: MonitorPlay,
        group: '۱. تیم داخلی اکسپلوریا',
        roles: ['admin', 'operator'],
    },
    {
        title: 'پنل مدیر اجرایی مکان',
        href: '/venue/dashboard',
        icon: Building2,
        group: '۲. مدیریت مکان و زون‌ها',
        roles: ['admin', 'operator', 'viewer'],
    },
    {
        title: 'پنل مدیر رواق تجاری',
        href: '/ravaq/dashboard',
        icon: Network,
        group: '۲. مدیریت مکان و زون‌ها',
        roles: ['admin', 'hub_manager'],
    },
    {
        title: 'اعضا، فروشگاه‌ها و شرکا',
        href: '/admin/campaign-participants',
        icon: UsersRound,
        group: '۳. واحدهای تجاری و اسپانسرها',
        roles: ['admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'اسپانسرها و درآمد',
        href: '/admin/sponsors',
        icon: BadgeDollarSign,
        group: '۳. واحدهای تجاری و اسپانسرها',
        roles: ['admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'تبلیغات مستقل',
        href: '/admin/ads',
        icon: Megaphone,
        group: '۳. واحدهای تجاری و اسپانسرها',
        roles: ['admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'پنل فروشگاه / شریک',
        href: '/partner/dashboard',
        icon: ShoppingBag,
        group: '۳. واحدهای تجاری و اسپانسرها',
        roles: ['admin', 'shop_partner', 'sponsor'],
    },
    {
        title: 'پنل اسپانسر',
        href: '/sponsor/dashboard',
        icon: BadgeDollarSign,
        group: '۳. واحدهای تجاری و اسپانسرها',
        roles: ['admin', 'sponsor'],
    },
    {
        title: 'ساختار نقش‌ها و حدود اختیار',
        href: '/admin/role-operations',
        icon: UserCog,
        group: '۴. دسترسی و کنترل',
        roles: ['admin', 'operator', 'viewer'],
    },
    {
        title: 'مدیریت کاربران',
        href: '/admin/users',
        icon: UsersRound,
        group: '۴. دسترسی و کنترل',
        roles: ['admin', 'operator', 'viewer'],
    },
    {
        title: 'راهنمای مدیریت کاربران',
        href: '/admin/users/guide',
        icon: BookOpen,
        group: '۴. دسترسی و کنترل',
        roles: ['admin', 'operator', 'viewer'],
    },
    {
        title: 'تخصیص دسترسی',
        href: '/admin/access-scopes',
        icon: ShieldCheck,
        group: '۴. دسترسی و کنترل',
        roles: ['admin', 'operator', 'viewer'],
    },
    {
        title: 'پنل مشارکت‌کننده',
        href: '/participant/dashboard',
        icon: PlayCircle,
        group: '۵. پنل کاربر',
        roles: ['admin', 'visitor'],
    },
    {
        title: 'پشتیبانی و چت‌بات',
        href: '/admin/support',
        icon: BotMessageSquare,
        group: '۶. پشتیبانی',
        roles: [
            'admin',
            'operator',
            'viewer',
            'hub_manager',
            'shop_partner',
            'sponsor',
        ],
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

function homeHrefForRole(role?: UserRole) {
    if (role === 'visitor') {
        return '/participant/dashboard';
    }

    if (role === 'shop_partner') {
        return '/partner/dashboard';
    }

    if (role === 'sponsor') {
        return '/sponsor/dashboard';
    }

    if (role === 'hub_manager') {
        return '/ravaq/dashboard';
    }

    return dashboard();
}

export function AppSidebar() {
    const { auth } = usePage<SharedProps>().props;
    const role = auth?.user?.role;
    const visibleNavItems = mainNavItems.filter((item) =>
        isVisibleForRole(item, role),
    );

    return (
        <Sidebar side="right" collapsible="icon" variant="inset" dir="rtl">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={homeHrefForRole(role)} prefetch>
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
