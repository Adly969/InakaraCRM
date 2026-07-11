import { Head, Link } from '@inertiajs/react';
import { Clock } from 'lucide-react';
import { Button } from '@/components/ui/button';

export default function PageExpired() {
    const handleRefresh = () => {
        window.location.reload();
    };

    return (
        <>
            <Head title="Session Expired" />
            <div className="flex min-h-screen flex-col items-center justify-center bg-background px-4 text-center">
                <div className="mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-orange-100 text-orange-600 dark:bg-orange-950 dark:text-orange-400">
                    <Clock className="h-10 w-10" />
                </div>
                <h1 className="mb-3 text-4xl font-extrabold tracking-tight text-foreground sm:text-5xl">
                    419 - Session Expired
                </h1>
                <p className="mb-8 max-w-md text-base text-muted-foreground">
                    Your session has expired or is inactive. This is usually
                    because you were inactive for a while or refreshed/reloaded.
                </p>
                <div className="flex flex-col justify-center gap-4 sm:flex-row">
                    <Button onClick={handleRefresh} variant="default">
                        Refresh Page
                    </Button>
                    <Button asChild variant="outline">
                        <Link href="/login">Go to Login</Link>
                    </Button>
                </div>
            </div>
        </>
    );
}
