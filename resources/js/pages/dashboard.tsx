import { Head, usePage } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard } from '@/routes';
import type { Auth } from '@/types';

export default function Dashboard() {
    const { props } = usePage();
    const auth = props.auth as Auth;
    const user = auth.user;

    const roleLabels =
        user.roles.map((r) => r.toUpperCase()).join(', ') || 'VIEWER';

    return (
        <>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 p-6">
                <Card className="max-w-2xl border-sidebar-border/70 shadow-none">
                    <CardHeader>
                        <div className="flex items-center gap-3">
                            <CardTitle className="text-2xl font-bold tracking-tight">
                                Welcome back, {user.name}
                            </CardTitle>
                            <Badge
                                variant="secondary"
                                className="border-none bg-primary/10 px-2.5 py-0.5 text-xs font-semibold text-primary"
                            >
                                {roleLabels}
                            </Badge>
                        </div>
                        <CardDescription className="mt-1 text-muted-foreground">
                            Current session account: {user.email}
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <p className="text-sm text-muted-foreground">
                            You have successfully authenticated with the INAKARA
                            CRM system. Navigation and modules will become
                            available as individual features are developed in
                            future sprints.
                        </p>
                        <div className="border-t border-sidebar-border/50 pt-2">
                            <span className="text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                Assigned Permissions
                            </span>
                            <div className="mt-2 flex flex-wrap gap-1.5">
                                {user.permissions.length > 0 ? (
                                    user.permissions.map((perm) => (
                                        <Badge
                                            key={perm}
                                            variant="outline"
                                            className="border-neutral-200 text-xs text-neutral-600"
                                        >
                                            {perm}
                                        </Badge>
                                    ))
                                ) : (
                                    <span className="text-xs text-muted-foreground italic">
                                        None
                                    </span>
                                )}
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
