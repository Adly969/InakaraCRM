import { usePage } from '@inertiajs/react';
import type { Auth } from '@/types';

export function usePermission() {
    const { props } = usePage();
    const auth = props.auth as Auth | undefined;
    const user = auth?.user;

    const can = (permission: string): boolean => {
        if (!user) {
            return false;
        }
        
        // Owner role bypasses all checks on the frontend too
        if (user.roles.includes('owner')) {
            return true;
        }

        return user.permissions.includes(permission);
    };

    const hasRole = (role: string): boolean => {
        if (!user) {
            return false;
        }
        return user.roles.includes(role);
    };

    return {
        can,
        hasRole,
        user,
    };
}
