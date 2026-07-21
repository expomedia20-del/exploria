import { Link, usePage } from '@inertiajs/react';
import {
    BadgeDollarSign,
    BookOpen,
    BriefcaseBusiness,
    Building2,
    BotMessageSquare,
    ClipboardCheck,
    LayoutGrid,
    MapPinned,
    Megaphone,
    MonitorPlay,
    Network,
    PlayCircle,
    QrCode,
    Route,
    ScrollText,
    ShieldCheck,
    ShoppingBag,
    Store,
    Trophy,
    UserCog,
    UsersRound,
    WalletCards,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
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
    | 'regional_admin'
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
        roles: ['admin', 'regional_admin', 'operator', 'viewer'],
    },
    {
        title: 'ارزیابی مکان',
        href: '/admin/venues',
        icon: MapPinned,
        group: 'تیم داخلی اکسپلوریا',
        roles: ['admin', 'regional_admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'پنل عملیات داخلی',
        href: '/admin/internal-operations',
        icon: ClipboardCheck,
        group: 'تیم داخلی اکسپلوریا',
        roles: ['admin', 'regional_admin', 'operator', 'viewer'],
    },
    {
        title: 'چرخه دمو اکوپارک',
        href: '/admin/demo-cycle',
        icon: Route,
        group: 'تیم داخلی اکسپلوریا',
        roles: ['admin', 'regional_admin', 'operator', 'viewer'],
    },
    {
        title: 'تجاری‌سازی و فروش',
        href: '/admin/commercialization',
        icon: BriefcaseBusiness,
        group: 'تیم داخلی اکسپلوریا',
        roles: ['admin', 'regional_admin', 'operator', 'viewer'],
    },
    {
        title: 'گنجینه الگوها',
        href: '/admin/mission-blueprints',
        icon: BookOpen,
        group: 'تیم داخلی اکسپلوریا',
        roles: ['admin'],
    },
    {
        title: 'ثبت و انتخاب کمپین',
        href: '/admin/campaigns',
        icon: Megaphone,
        group: 'تیم داخلی اکسپلوریا',
        roles: ['admin', 'regional_admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'کارگاه ساخت کمپین',
        href: '/admin/campaign-builder',
        icon: ClipboardCheck,
        group: 'تیم داخلی اکسپلوریا',
        roles: ['admin', 'regional_admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'ماموریت، گنج و پاداش',
        href: '/admin/missions',
        icon: Trophy,
        group: 'تیم داخلی اکسپلوریا',
        roles: ['admin', 'regional_admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'مدیریت QR و ورود',
        href: '/admin/qr-codes',
        icon: QrCode,
        group: 'تیم داخلی اکسپلوریا',
        roles: ['admin', 'regional_admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'پایش رویدادهای اسکن',
        href: '/admin/events/scan-log',
        icon: ScrollText,
        group: 'تیم داخلی اکسپلوریا',
        roles: ['admin', 'regional_admin', 'operator', 'viewer'],
    },
    {
        title: 'نقشه عملیات کمپین',
        href: '/admin/campaign-operations',
        icon: Route,
        group: 'تیم داخلی اکسپلوریا',
        roles: ['admin', 'regional_admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'مدیریت شرکا',
        href: '/admin/partners',
        icon: Store,
        group: 'واحدهای تجاری و اسپانسرها',
        roles: ['admin', 'regional_admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'عملیات تبلیغات و نمایشگرها',
        href: '/admin/display-operations',
        icon: MonitorPlay,
        group: 'تیم داخلی اکسپلوریا',
        roles: ['admin', 'regional_admin', 'operator'],
    },
    {
        title: 'پنل مدیر اجرایی مکان',
        href: '/venue/dashboard',
        icon: Building2,
        group: 'مدیریت مکان و زون‌ها',
        roles: ['admin', 'operator', 'viewer'],
    },
    {
        title: 'پنل مدیر رواق تجاری',
        href: '/ravaq/dashboard',
        icon: Network,
        group: 'مدیریت مکان و زون‌ها',
        roles: ['admin', 'hub_manager'],
    },
    {
        title: 'اعضا، فروشگاه‌ها و شرکا',
        href: '/admin/campaign-participants',
        icon: UsersRound,
        group: 'واحدهای تجاری و اسپانسرها',
        roles: ['admin', 'regional_admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'اسپانسرها و درآمد',
        href: '/admin/sponsors',
        icon: BadgeDollarSign,
        group: 'واحدهای تجاری و اسپانسرها',
        roles: ['admin', 'regional_admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'تبلیغات مستقل',
        href: '/admin/ads',
        icon: Megaphone,
        group: 'واحدهای تجاری و اسپانسرها',
        roles: ['admin', 'regional_admin', 'operator', 'viewer', 'hub_manager'],
    },
    {
        title: 'پنل فروشگاه / شریک',
        href: '/partner/dashboard',
        icon: ShoppingBag,
        group: 'واحدهای تجاری و اسپانسرها',
        roles: ['admin', 'shop_partner', 'sponsor'],
    },
    {
        title: 'پنل اسپانسر',
        href: '/sponsor/dashboard',
        icon: BadgeDollarSign,
        group: 'واحدهای تجاری و اسپانسرها',
        roles: ['admin', 'sponsor'],
    },
    {
        title: '۱. نقشه نقش‌ها و اختیارها',
        href: '/admin/role-operations',
        icon: UserCog,
        group: 'مدیریت نقش‌ها و دسترسی',
        roles: ['admin', 'regional_admin', 'operator', 'viewer'],
    },
    {
        title: '۲. مدیریت حساب‌های کاربری',
        href: '/admin/users',
        icon: UsersRound,
        group: 'مدیریت نقش‌ها و دسترسی',
        roles: ['admin', 'regional_admin', 'operator', 'viewer'],
    },
    {
        title: '۳. ساخت و تخصیص دسترسی',
        href: '/admin/access-scopes',
        icon: ShieldCheck,
        group: 'مدیریت نقش‌ها و دسترسی',
        roles: ['admin', 'regional_admin', 'operator', 'viewer'],
    },
    {
        title: '۴. راهنمای نقش و کاربر',
        href: '/admin/users/guide',
        icon: BookOpen,
        group: 'مدیریت نقش‌ها و دسترسی',
        roles: ['admin', 'regional_admin', 'operator', 'viewer'],
    },
    {
        title: 'پنل مشارکت‌کننده',
        href: '/participant/dashboard',
        icon: PlayCircle,
        group: 'پنل کاربر',
        roles: ['admin', 'visitor'],
    },
    {
        title: 'پشتیبانی و چت‌بات',
        href: '/admin/support',
        icon: BotMessageSquare,
        group: 'پشتیبانی',
        roles: [
            'admin',
            'regional_admin',
            'operator',
            'viewer',
            'hub_manager',
            'shop_partner',
            'sponsor',
        ],
    },
    {
        title: 'اقتصاد و کیف پول‌ها',
        href: '/admin/finance-wallets',
        icon: WalletCards,
        group: 'تیم داخلی اکسپلوریا',
        roles: ['admin', 'regional_admin', 'operator', 'viewer'],
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
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
