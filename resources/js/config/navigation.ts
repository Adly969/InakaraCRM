import { LayoutGrid, Settings } from 'lucide-react';
import { dashboard } from '@/routes';
import { edit as editProfile } from '@/routes/profile';
import type { NavItem } from '@/types';

export interface ExtendedNavItem extends NavItem {
    requiredPermission?: string;
}

export const mainNavItems: ExtendedNavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
        requiredPermission: 'view-dashboard',
    },
    {
        title: 'Settings',
        href: editProfile(),
        icon: Settings,
        requiredPermission: 'view-settings',
    },
];
