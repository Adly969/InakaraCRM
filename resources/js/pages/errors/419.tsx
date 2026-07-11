import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Clock } from 'lucide-react';

export default function PageExpired() {
    const handleRefresh = () => {
        window.location.reload();
    };

    return (
        <>
            <Head title="Session Expired" />
            <div className="flex min-h-screen flex-col items-center justify-center bg-background px-4 text-center">
                <div className="flex h-16 w-16 items-center justify-center rounded-full bg-orange-100 text-orange-600 dark:bg-orange-950 dark:text-orange-400 mb-6">
                    <Clock className="h-10 w-10" />
                </div>
                <h1 className="text-4xl font-extrabold tracking-tight sm:text-5xl text-foreground mb-3">
                    419 - Session Expired
                </h1>
                <p className="text-muted-foreground max-w-md mb-8 text-base">
                    Your session has expired or is inactive. This is usually because you were inactive for a while or refreshed/reloaded.
                </p>
                <div className="flex flex-col sm:flex-row gap-4 justify-center">
                    <Button onClick={handleRefresh} variant="default">
                        Refresh Page
                    </Button>
                    <Button asChild variant="outline">
                        <Link href="/login">
                            Go to Login
                        </Link>
                    </Button>
                </div>
            </div>
        </>
    );
}
