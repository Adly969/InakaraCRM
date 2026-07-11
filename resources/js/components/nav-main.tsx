import { Link } from '@inertiajs/react';
import {
    SidebarGroup,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { usePermission } from '@/hooks/use-permission';
import type { ExtendedNavItem } from '@/config/navigation';

export function NavMain({ items = [] }: { items: ExtendedNavItem[] }) {
    const { isCurrentUrl } = useCurrentUrl();
    const { can } = usePermission();

    // Filter items based on permissions
    const visibleItems = items.filter(
        (item) => !item.requiredPermission || can(item.requiredPermission)
    );

    return (
        <SidebarGroup className="px-2 py-0">
            <SidebarMenu>
                {visibleItems.map((item) => {
                    const active = isCurrentUrl(item.href);
                    return (
                        <SidebarMenuItem key={item.title} className="relative">
                            {active && (
                                <div className="absolute left-[-8px] top-1.5 bottom-1.5 w-1 rounded-r bg-primary transition-all" />
                            )}
                            <SidebarMenuButton
                                asChild
                                isActive={active}
                                tooltip={{ children: item.title }}
                            >
                                <Link href={item.href} prefetch>
                                    {item.icon && <item.icon />}
                                    <span>{item.title}</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    );
                })}
            </SidebarMenu>
        </SidebarGroup>
    );
}
