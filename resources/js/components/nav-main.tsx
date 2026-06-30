import { Link } from '@inertiajs/react';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/hooks/use-current-url';
import type { NavItem } from '@/types';

export function NavMain({ items = [] }: { items: NavItem[] }) {
    const { isCurrentUrl } = useCurrentUrl();
    const groups = items.reduce<Record<string, NavItem[]>>((carry, item) => {
        const group = item.group ?? 'عملیات';

        return {
            ...carry,
            [group]: [...(carry[group] ?? []), item],
        };
    }, {});

    return (
        <>
            {Object.entries(groups).map(([group, groupItems]) => (
                <SidebarGroup key={group} className="px-2 py-0">
                    <SidebarGroupLabel>{group}</SidebarGroupLabel>
                    <SidebarMenu>
                        {groupItems.map((item) => (
                            <SidebarMenuItem key={item.title}>
                                <SidebarMenuButton
                                    asChild
                                    isActive={isCurrentUrl(item.href)}
                                    tooltip={{ children: item.title }}
                                >
                                    <Link href={item.href} prefetch>
                                        {item.icon && <item.icon />}
                                        <span>{item.title}</span>
                                    </Link>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                        ))}
                    </SidebarMenu>
                </SidebarGroup>
            ))}
        </>
    );
}
