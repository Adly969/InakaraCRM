import React from 'react';
import { usePermission } from '@/hooks/use-permission';

type Props = {
    permission?: string;
    role?: string;
    fallback?: React.ReactNode;
    children: React.ReactNode;
};

export function PermissionGate({ permission, role, fallback = null, children }: Props) {
    const { can, hasRole } = usePermission();

    if (role && !hasRole(role)) {
        return <>{fallback}</>;
    }

    if (permission && !can(permission)) {
        return <>{fallback}</>;
    }

    return <>{children}</>;
}
