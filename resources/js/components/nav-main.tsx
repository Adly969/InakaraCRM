import { useState, useEffect } from 'react';
import { Link } from '@inertiajs/react';
import { ChevronDown, ChevronRight, Clock } from 'lucide-react';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
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
            } catch (e) {
                // Ignore parse errors
            }
        }
        // Default: Open all groups initially
        const initial: Record<string, boolean> = {};
        navigationConfig.forEach(g => {
            initial[g.title] = true;
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
        <div className="space-y-3 px-2 py-1">
            {navigationConfig.map((group: NavGroup) => {
                // Filter items based on permissions
                const visibleSubItems = group.items.filter(
                    item => !item.permission || can(item.permission)
                );

                if (visibleSubItems.length === 0) return null;

                const isOpen = openGroups[group.title] ?? true;
                const GroupIcon = group.icon;

                return (
                    <SidebarGroup key={group.title} className="p-0">
                        {/* Group Header Toggle Button */}
                        <button
                            type="button"
                            onClick={() => toggleGroup(group.title)}
                            className="group flex w-full items-center justify-between px-2.5 py-1.5 text-xs font-extrabold uppercase tracking-wider text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-neutral-100 transition-colors rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800/60"
                        >
                            <div className="flex items-center gap-2">
                                <GroupIcon className="h-3.5 w-3.5 text-neutral-400 group-hover:text-neutral-700 dark:group-hover:text-neutral-200 transition-colors" />
                                <span>{group.title}</span>
                            </div>
                            <div className="flex items-center">
                                {isOpen ? (
                                    <ChevronDown className="h-3.5 w-3.5 text-neutral-400" />
                                ) : (
                                    <ChevronRight className="h-3.5 w-3.5 text-neutral-400" />
                                )}
                            </div>
                        </button>

                        {/* Collapsible Submenu Items */}
                        {isOpen && (
                            <SidebarMenu className="mt-1 space-y-0.5 pl-1.5 border-l border-neutral-200/60 dark:border-neutral-800 ml-3">
                                {visibleSubItems.map((item: NavSubItem) => {
                                    const active = isCurrentUrl(item.href);
                                    const ItemIcon = item.icon;

                                    return (
                                        <SidebarMenuItem key={item.title} className="relative">
                                            {active && (
                                                <div className="absolute top-1.5 bottom-1.5 left-[-7px] w-1 rounded-r bg-emerald-500 dark:bg-emerald-400 transition-all" />
                                            )}
                                            <SidebarMenuButton
                                                asChild
                                                isActive={active}
                                                tooltip={{ children: item.title }}
                                                className={`text-xs font-semibold py-1.5 rounded-xl transition-all ${
                                                    active 
                                                        ? 'bg-neutral-900 text-white dark:bg-neutral-100 dark:text-neutral-950 font-bold shadow-xs' 
                                                        : 'text-neutral-600 hover:text-neutral-900 dark:text-neutral-300 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-neutral-800/60'
                                                }`}
                                            >
                                                <Link href={item.href} prefetch className="flex items-center justify-between w-full">
                                                    <div className="flex items-center gap-2.5">
                                                        <ItemIcon className={`h-4 w-4 shrink-0 ${active ? 'text-emerald-400 dark:text-emerald-600' : 'text-neutral-400'}`} />
                                                        <span className="truncate">{item.title}</span>
                                                    </div>
                                                    {item.comingSoon && (
                                                        <span className="inline-flex items-center px-1.5 py-0.5 text-[9px] font-black rounded-md bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/20 ml-1">
                                                            <Clock className="h-2.5 w-2.5 mr-0.5" />
                                                            {item.plannedSprint || 'Soon'}
                                                        </span>
                                                    )}
                                                </Link>
                                            </SidebarMenuButton>
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
