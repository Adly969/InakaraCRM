import { useState, useEffect } from 'react';
import { Link } from '@inertiajs/react';
import { ChevronDown, ChevronRight, Clock } from 'lucide-react';
import {
    SidebarGroup,
    SidebarMenu,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { navigationConfig, type NavGroup, type NavSubItem } from '@/config/navigation';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { usePermission } from '@/hooks/use-permission';

const STORAGE_KEY = 'inakara_sidebar_open_groups';

export function NavMain() {
    const { isCurrentUrl } = useCurrentUrl();
    const { can } = usePermission();

    // Persist open groups in localStorage
    const [openGroups, setOpenGroups] = useState<Record<string, boolean>>(() => {
        if (typeof window !== 'undefined') {
            try {
                const saved = localStorage.getItem(STORAGE_KEY);
                if (saved) {
                    return JSON.parse(saved);
                }
            } catch (e) {}
        }
        // Default: Open Dashboard and CRM groups
        const initial: Record<string, boolean> = {};
        navigationConfig.forEach(g => {
            initial[g.title] = g.title === 'Dashboard' || g.title === 'CRM';
        });
        return initial;
    });

    // Auto expand group containing current URL
    useEffect(() => {
        navigationConfig.forEach(group => {
            const hasActiveChild = group.items.some(item => isCurrentUrl(item.href));
            if (hasActiveChild && !openGroups[group.title]) {
                setOpenGroups(prev => {
                    const next = { ...prev, [group.title]: true };
                    try {
                        localStorage.setItem(STORAGE_KEY, JSON.stringify(next));
                    } catch (e) {}
                    return next;
                });
            }
        });
    }, [isCurrentUrl]);

    const toggleGroup = (title: string) => {
        setOpenGroups(prev => {
            const next = { ...prev, [title]: !prev[title] };
            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(next));
            } catch (e) {}
            return next;
        });
    };

    return (
        <div className="space-y-1 px-2 py-1">
            {navigationConfig.map((group: NavGroup) => {
                // Filter items based on permissions
                const visibleSubItems = group.items.filter(
                    item => !item.permission || can(item.permission)
                );

                if (visibleSubItems.length === 0) return null;

                const isOpen = openGroups[group.title] ?? false;
                const GroupIcon = group.icon;

                return (
                    <SidebarGroup key={group.title} className="p-0 select-none">
                        {/* Group Header Toggle Button */}
                        <button
                            type="button"
                            onClick={() => toggleGroup(group.title)}
                            className="group flex w-full items-center justify-between px-2.5 py-1.5 text-[11px] font-bold uppercase tracking-wider text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-neutral-100 transition-colors rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-800/60"
                        >
                            <div className="flex items-center gap-2">
                                <GroupIcon className="h-3.5 w-3.5 text-neutral-400 group-hover:text-neutral-700 dark:group-hover:text-neutral-200 transition-colors" />
                                <span>{group.title}</span>
                            </div>
                            {isOpen ? (
                                <ChevronDown className="h-3.5 w-3.5 text-neutral-400" />
                            ) : (
                                <ChevronRight className="h-3.5 w-3.5 text-neutral-400" />
                            )}
                        </button>

                        {/* Submenu Items */}
                        {isOpen && (
                            <SidebarMenu className="mt-0.5 space-y-0.5 pl-2 ml-2 border-l border-neutral-200 dark:border-neutral-800">
                                {visibleSubItems.map((item: NavSubItem) => {
                                    const active = isCurrentUrl(item.href);
                                    const ItemIcon = item.icon;

                                    return (
                                        <SidebarMenuItem key={item.title}>
                                            <Link
                                                href={item.href}
                                                prefetch
                                                className={`flex items-center justify-between w-full px-2.5 py-1.5 rounded-lg text-xs font-medium transition-colors ${
                                                    active
                                                        ? 'bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-400 font-semibold'
                                                        : 'text-neutral-600 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-neutral-800/50'
                                                }`}
                                            >
                                                <div className="flex items-center gap-2 min-w-0">
                                                    <ItemIcon className={`h-3.5 w-3.5 shrink-0 ${active ? 'text-sky-600 dark:text-sky-400' : 'text-neutral-400'}`} />
                                                    <span className="truncate">{item.title}</span>
                                                </div>

                                                {item.comingSoon && (
                                                    <span className="inline-flex items-center px-1.5 py-0.5 text-[9px] font-semibold rounded bg-neutral-100 text-neutral-500 dark:bg-neutral-800 dark:text-neutral-400 ml-1 shrink-0">
                                                        {item.plannedSprint || 'Soon'}
                                                    </span>
                                                )}
                                            </Link>
                                        </SidebarMenuItem>
                                    );
                                })}
                            </SidebarMenu>
                        )}
                    </SidebarGroup>
                );
            })}
        </div>
    );
}
