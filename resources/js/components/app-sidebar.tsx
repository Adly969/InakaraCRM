import { Link } from '@inertiajs/react';
import { CircleHelp, LogOut } from 'lucide-react';
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
    return (
        <Sidebar collapsible="icon" variant="sidebar" className="bg-white dark:bg-neutral-900 border-r border-neutral-200 dark:border-neutral-800 text-neutral-900 dark:text-neutral-100">
            <SidebarHeader className="p-3 border-b border-neutral-100 dark:border-neutral-800">
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild className="hover:bg-neutral-100 dark:hover:bg-neutral-800 rounded-lg p-2 transition-colors">
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

            <SidebarFooter className="p-3 border-t border-neutral-100 dark:border-neutral-800">
                <SidebarMenu className="space-y-0.5">
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            asChild
                            className="text-xs font-medium text-neutral-600 hover:text-neutral-900 hover:bg-neutral-100 dark:text-neutral-400 dark:hover:text-neutral-100 dark:hover:bg-neutral-800 rounded-lg transition-colors"
                        >
                            <Link href="/support">
                                <CircleHelp className="h-4 w-4 text-neutral-500" />
                                <span>Support</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            asChild
                            className="text-xs font-medium text-neutral-600 hover:text-neutral-900 hover:bg-neutral-100 dark:text-neutral-400 dark:hover:text-neutral-100 dark:hover:bg-neutral-800 rounded-lg transition-colors"
                        >
                            <Link
                                href={logout()}
                                method="post"
                                as="button"
                                className="w-full text-left"
                            >
                                <LogOut className="h-4 w-4 text-neutral-500" />
                                <span>Sign Out</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarFooter>
        </Sidebar>
    );
}
