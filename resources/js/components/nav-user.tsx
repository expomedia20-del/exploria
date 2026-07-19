import { Link, router, usePage } from '@inertiajs/react';
import { LogOut, Settings } from 'lucide-react';
import { UserInfo } from '@/components/user-info';
import { useMobileNavigation } from '@/hooks/use-mobile-navigation';
import { logout } from '@/routes';
import { edit } from '@/routes/profile';
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';

const actionClassName =
    'flex min-w-0 flex-1 cursor-pointer items-center justify-center gap-1.5 rounded-md px-2 py-2 text-xs font-medium text-sidebar-foreground/80 transition-colors hover:bg-sidebar-accent hover:text-sidebar-accent-foreground focus-visible:ring-2 focus-visible:ring-sidebar-ring focus-visible:outline-none';

export function NavUser() {
    const { auth } = usePage().props;
    const cleanup = useMobileNavigation();

    if (!auth.user) {
        return null;
    }

    const handleLogout = () => {
        cleanup();
        router.post(logout().url);
    };

    return (
        <SidebarMenu>
            <SidebarMenuItem className="rounded-lg border border-sidebar-border/70 bg-sidebar-accent/30">
                <SidebarMenuButton
                    size="lg"
                    className="text-sidebar-accent-foreground hover:bg-transparent"
                >
                    <UserInfo user={auth.user} />
                </SidebarMenuButton>

                <div className="flex gap-1 border-t border-sidebar-border/70 p-1.5 group-data-[collapsible=icon]:hidden">
                    <Link
                        href={edit()}
                        prefetch
                        onClick={cleanup}
                        className={actionClassName}
                    >
                        <Settings className="size-3.5" />
                        <span>تنظیمات</span>
                    </Link>
                    <button
                        type="button"
                        onClick={handleLogout}
                        data-test="logout-button"
                        className={actionClassName}
                    >
                        <LogOut className="size-3.5" />
                        <span>خروج</span>
                    </button>
                </div>
            </SidebarMenuItem>
        </SidebarMenu>
    );
}
