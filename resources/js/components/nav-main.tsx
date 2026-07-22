import { useState, useEffect } from 'react';
import { Link } from '@inertiajs/react';
import { ChevronDown, ChevronRight, Clock, Sparkles } from 'lucide-react';
import {
    SidebarGroup,
    SidebarMenu,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { navigationConfig, type NavGroup, type NavSubItem } from '@/config/navigation';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { usePermission } from '@/hooks/use-permission';

const STORAGE_KEY = 'inakara_sidebar_open_groups';

// Color themes per navigation group for rich visual identification
const groupThemeMap: Record<string, { iconColor: string; bgBadge: string; borderAccent: string }> = {
    'Dashboard': { iconColor: 'text-sky-500 dark:text-sky-400', bgBadge: 'bg-sky-500/10 text-sky-600 dark:text-sky-400', borderAccent: 'border-sky-500' },
    'CRM': { iconColor: 'text-blue-600 dark:text-blue-400', bgBadge: 'bg-blue-500/10 text-blue-600 dark:text-blue-400', borderAccent: 'border-blue-500' },
    'Sales': { iconColor: 'text-emerald-600 dark:text-emerald-400', bgBadge: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400', borderAccent: 'border-emerald-500' },
    'Production': { iconColor: 'text-amber-600 dark:text-amber-400', bgBadge: 'bg-amber-500/10 text-amber-600 dark:text-amber-400', borderAccent: 'border-amber-500' },
    'Warehouse': { iconColor: 'text-indigo-600 dark:text-indigo-400', bgBadge: 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400', borderAccent: 'border-indigo-500' },
    'Purchasing': { iconColor: 'text-purple-600 dark:text-purple-400', bgBadge: 'bg-purple-500/10 text-purple-600 dark:text-purple-400', borderAccent: 'border-purple-500' },
    'Finance': { iconColor: 'text-rose-600 dark:text-rose-400', bgBadge: 'bg-rose-500/10 text-rose-600 dark:text-rose-400', borderAccent: 'border-rose-500' },
    'Reports': { iconColor: 'text-teal-600 dark:text-teal-400', bgBadge: 'bg-teal-500/10 text-teal-600 dark:text-teal-400', borderAccent: 'border-teal-500' },
    'Master Data': { iconColor: 'text-cyan-600 dark:text-cyan-400', bgBadge: 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400', borderAccent: 'border-cyan-500' },
    'System': { iconColor: 'text-slate-600 dark:text-slate-400', bgBadge: 'bg-slate-500/10 text-slate-600 dark:text-slate-400', borderAccent: 'border-slate-500' },
};

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
        <div className="space-y-2.5 px-3 py-2">
            {navigationConfig.map((group: NavGroup) => {
                // Filter items based on permissions
                const visibleSubItems = group.items.filter(
                    item => !item.permission || can(item.permission)
                );

                if (visibleSubItems.length === 0) return null;

                const isOpen = openGroups[group.title] ?? false;
                const GroupIcon = group.icon;
                const theme = groupThemeMap[group.title] || {
                    iconColor: 'text-slate-500',
                    bgBadge: 'bg-slate-500/10 text-slate-600',
                    borderAccent: 'border-slate-500',
                };

                const hasActiveItem = visibleSubItems.some(i => isCurrentUrl(i.href));

                return (
                    <SidebarGroup key={group.title} className="p-0 select-none">
                        {/* Group Header Card Toggle */}
                        <button
                            type="button"
                            onClick={() => toggleGroup(group.title)}
                            className={`group flex w-full items-center justify-between px-3 py-2 text-xs font-bold transition-all rounded-xl border ${
                                hasActiveItem
                                    ? 'bg-slate-100/90 border-slate-200 dark:bg-slate-800/80 dark:border-slate-700/80 text-slate-900 dark:text-white shadow-xs'
                                    : 'border-transparent text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 dark:text-slate-400 dark:hover:text-slate-100 dark:hover:bg-slate-800/50'
                            }`}
                        >
                            <div className="flex items-center gap-2.5">
                                <div className={`p-1.5 rounded-lg bg-white dark:bg-slate-900 shadow-xs border border-slate-200/60 dark:border-slate-800 ${theme.iconColor}`}>
                                    <GroupIcon className="h-4 w-4" />
                                </div>
                                <span className="uppercase tracking-wider font-extrabold text-[11px]">
                                    {group.title}
                                </span>
                            </div>

                            <div className="flex items-center gap-1.5">
                                <span className={`px-1.5 py-0.5 text-[10px] font-bold rounded-md ${theme.bgBadge}`}>
                                    {visibleSubItems.length}
                                </span>
                                <div className={`transition-transform duration-200 ${isOpen ? 'rotate-180' : ''}`}>
                                    <ChevronDown className="h-3.5 w-3.5 text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300" />
                                </div>
                            </div>
                        </button>

                        {/* Collapsible Submenu Items with Guide Rail */}
                        {isOpen && (
                            <SidebarMenu className="mt-1.5 space-y-1 pl-3.5 ml-3.5 border-l-2 border-slate-200/80 dark:border-slate-800 transition-all">
                                {visibleSubItems.map((item: NavSubItem) => {
                                    const active = isCurrentUrl(item.href);
                                    const ItemIcon = item.icon;

                                    return (
                                        <SidebarMenuItem key={item.title} className="relative">
                                            <Link
                                                href={item.href}
                                                prefetch
                                                className={`group/item flex items-center justify-between w-full px-3 py-2 rounded-xl text-xs font-semibold transition-all ${
                                                    active
                                                        ? 'bg-gradient-to-r from-sky-600 to-blue-600 text-white font-bold shadow-md shadow-sky-500/20 translate-x-0.5'
                                                        : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:translate-x-0.5'
                                                }`}
                                            >
                                                <div className="flex items-center gap-2.5 min-w-0">
                                                    <ItemIcon
                                                        className={`h-4 w-4 shrink-0 transition-transform group-hover/item:scale-110 ${
                                                            active
                                                                ? 'text-white'
                                                                : 'text-slate-400 group-hover/item:text-sky-600 dark:group-hover/item:text-sky-400'
                                                        }`}
                                                    />
                                                    <span className="truncate">{item.title}</span>
                                                </div>

                                                {item.comingSoon && (
                                                    <span className="inline-flex items-center px-1.5 py-0.5 text-[9px] font-black rounded-md bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/20 ml-1.5 shrink-0">
                                                        <Clock className="h-2.5 w-2.5 mr-0.5" />
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
