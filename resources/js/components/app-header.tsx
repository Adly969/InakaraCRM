import { Link, usePage } from '@inertiajs/react';
import { Bell, Clock } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { Breadcrumbs } from '@/components/breadcrumbs';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { UserMenuContent } from '@/components/user-menu-content';
import { useInitials } from '@/hooks/use-initials';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

export function AppHeader({ breadcrumbs = [] }: Props) {
    const page = usePage();
    const { auth } = page.props;
    const getInitials = useInitials();

    return (
        <>
            <div className="sticky top-0 z-20 border-b border-sidebar-border/80 bg-background/95 backdrop-blur-md">
                <div className="mx-auto flex h-16 items-center px-4 md:max-w-7xl">
                    <Link
                        href={dashboard()}
                        prefetch
                        className="mr-6 flex items-center space-x-2"
                    >
                        <AppLogo />
                    </Link>

                    {/* Breadcrumbs shown in topbar */}
                    <div className="hidden md:block">
                        <Breadcrumbs breadcrumbs={breadcrumbs} />
                    </div>

                    <div className="ml-auto flex items-center space-x-4">
                        {/* History / Clock Icon */}
                        <Button
                            variant="ghost"
                            size="icon"
                            className="h-9 w-9 text-muted-foreground hover:text-foreground"
                            aria-label="History"
                        >
                            <Clock className="h-5 w-5" />
                        </Button>

                        {/* Notification Bell */}
                        <Button
                            variant="ghost"
                            size="icon"
                            className="h-9 w-9 text-muted-foreground hover:text-foreground"
                            aria-label="Notifications"
                        >
                            <Bell className="h-5 w-5" />
                        </Button>

                        {/* Profile Dropdown */}
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button
                                    variant="ghost"
                                    className="size-10 rounded-full p-1"
                                >
                                    <Avatar className="size-8 overflow-hidden rounded-full">
                                        <AvatarImage
                                            src={auth.user?.avatar ?? undefined}
                                            alt={auth.user?.name}
                                        />
                                        <AvatarFallback className="rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                            {getInitials(auth.user?.name ?? '')}
                                        </AvatarFallback>
                                    </Avatar>
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent className="w-56" align="end">
                                {auth.user && (
                                    <UserMenuContent user={auth.user} />
                                )}
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>
            </div>
        </>
    );
}
