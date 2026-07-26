import type { LucideIcon } from 'lucide-react';

export type RoleDashboardNavigationItem<Section extends string> = {
    key: Section;
    label: string;
    description: string;
    icon: LucideIcon;
    badge?: number;
};

export function RoleDashboardNavigation<Section extends string>({
    activeSection,
    items,
    onSelect,
}: {
    activeSection: Section;
    items: RoleDashboardNavigationItem<Section>[];
    onSelect: (section: Section) => void;
}) {
    return (
        <nav
            aria-label="بخش‌های پنل مدیریتی"
            className="grid grid-cols-2 gap-2 sm:grid-cols-3 xl:grid-cols-5"
        >
            {items.map((item) => {
                const Icon = item.icon;
                const isActive = activeSection === item.key;

                return (
                    <button
                        key={item.key}
                        type="button"
                        aria-current={isActive ? 'page' : undefined}
                        onClick={() => onSelect(item.key)}
                        className={`min-w-0 rounded-xl border p-3 text-right transition-colors ${
                            isActive
                                ? 'border-primary bg-primary text-primary-foreground'
                                : 'border-sidebar-border/70 bg-background hover:border-primary/50 hover:bg-muted/40 dark:border-sidebar-border'
                        }`}
                    >
                        <span className="flex items-center justify-between gap-2">
                            <span className="flex min-w-0 items-center gap-2 font-medium">
                                <Icon className="size-4 shrink-0" />
                                <span className="truncate">{item.label}</span>
                            </span>
                            {item.badge ? (
                                <span
                                    className={`shrink-0 rounded-full px-2 py-0.5 text-xs ${
                                        isActive
                                            ? 'bg-primary-foreground/15'
                                            : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200'
                                    }`}
                                >
                                    {item.badge.toLocaleString('fa-IR')}
                                </span>
                            ) : null}
                        </span>
                        <span
                            className={`mt-1 hidden text-xs leading-5 sm:block ${
                                isActive
                                    ? 'text-primary-foreground/80'
                                    : 'text-muted-foreground'
                            }`}
                        >
                            {item.description}
                        </span>
                    </button>
                );
            })}
        </nav>
    );
}
