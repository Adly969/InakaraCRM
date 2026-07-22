import { Link, usePage } from '@inertiajs/react';
import { CircleHelp, LogOut, ShieldCheck, User as UserIcon } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard, logout } from '@/routes';

export function AppSidebar() {
    const page = usePage();
    const auth = (page.props as any).auth;
    const user = auth?.user;

    return (
        <Sidebar collapsible="icon" variant="sidebar" className="bg-white dark:bg-slate-950 border-r border-slate-200/80 dark:border-slate-800/80 text-slate-900 dark:text-slate-100 shadow-xs">
            <SidebarHeader className="p-3 border-b border-slate-100 dark:border-slate-800/80">
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild className="hover:bg-slate-100/60 dark:hover:bg-slate-900 rounded-xl p-2 transition-colors">
                            <Link href={dashboard()} prefetch className="flex items-center justify-between w-full">
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent className="py-2">
                <NavMain />
            </SidebarContent>

            <SidebarFooter className="p-3 border-t border-slate-100 dark:border-slate-800/80 space-y-2">
                {/* User Profile Mini Card */}
                {user && (
                    <div className="flex items-center gap-2.5 p-2 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/60 dark:border-slate-800">
                        <div className="flex size-8 items-center justify-center rounded-lg bg-sky-600 text-white font-bold text-xs shrink-0 shadow-xs">
                            {user.name ? user.name.charAt(0).toUpperCase() : 'U'}
                        </div>
                        <div className="grid flex-1 text-left min-w-0">
                            <span className="truncate text-xs font-bold text-slate-900 dark:text-slate-100 leading-tight">
                                {user.name}
                            </span>
                            <span className="truncate text-[10px] text-slate-500 capitalize">
                                {user.role || 'Sales Enterprise'}
                            </span>
                        </div>
                    </div>
                )}

                <SidebarMenu className="space-y-1">
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            asChild
                            className="text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-100 dark:hover:bg-slate-900 rounded-xl transition-all"
                        >
                            <Link href="/support">
                                <CircleHelp className="h-4 w-4 text-sky-500" />
                                <span>Pusat Bantuan (Support)</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            asChild
                            className="text-xs font-semibold text-rose-600 hover:text-rose-700 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/30 rounded-xl transition-all"
                        >
                            <Link
                                href={logout()}
                                method="post"
                                as="button"
                                className="w-full text-left"
                            >
                                <LogOut className="h-4 w-4 text-rose-500" />
                                <span>Keluar Sistem</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarFooter>
        </Sidebar>
    );
}
