import { Link } from '@inertiajs/react';
import { ChevronDown } from 'lucide-react';
import {
    SidebarGroup,
    SidebarGroupContent,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
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
            {Object.entries(groups).map(([group, groupItems], index) => {
                const hasActiveItem = groupItems.some((item) =>
                    isCurrentUrl(item.href),
                );

                return (
                    <Collapsible
                        key={group}
                        defaultOpen={index === 0 || hasActiveItem}
                        className="group/collapsible"
                    >
                        <SidebarGroup className="px-2 py-0">
                            <SidebarGroupLabel asChild>
                                <CollapsibleTrigger className="flex w-full items-center justify-between gap-2">
                                    <span className="truncate">{group}</span>
                                    <ChevronDown className="size-4 transition-transform group-data-[state=open]/collapsible:rotate-180" />
                                </CollapsibleTrigger>
                            </SidebarGroupLabel>
                            <CollapsibleContent>
                                <SidebarGroupContent>
                                    <SidebarMenu>
                                        {groupItems.map((item) => (
                                            <SidebarMenuItem key={item.title}>
                                                <SidebarMenuButton
                                                    asChild
                                                    isActive={isCurrentUrl(
                                                        item.href,
                                                    )}
                                                    tooltip={{
                                                        children: item.title,
                                                    }}
                                                >
                                                    <Link
                                                        href={item.href}
                                                        prefetch
                                                    >
                                                        {item.icon && (
                                                            <item.icon />
                                                        )}
                                                        <span>
                                                            {item.title}
                                                        </span>
                                                    </Link>
                                                </SidebarMenuButton>
                                            </SidebarMenuItem>
                                        ))}
                                    </SidebarMenu>
                                </SidebarGroupContent>
                            </CollapsibleContent>
                        </SidebarGroup>
                    </Collapsible>
                );
            })}
        </>
    );
}
