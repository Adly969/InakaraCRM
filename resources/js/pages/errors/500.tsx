import { Head, Link } from '@inertiajs/react';
import { ServerCrash } from 'lucide-react';
import { Button } from '@/components/ui/button';

export default function ServerError() {
    return (
        <>
            <Head title="Server Error" />
            <div className="flex min-h-screen flex-col items-center justify-center bg-background px-4 text-center">
                <div className="mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-destructive/10 text-destructive">
                    <ServerCrash className="h-10 w-10" />
                </div>
                <h1 className="mb-3 text-4xl font-extrabold tracking-tight text-foreground sm:text-5xl">
                    500 - Server Error
                </h1>
                <p className="mb-8 max-w-md text-base text-muted-foreground">
                    Oops! Something went wrong on our server. We have logged
                    this error and are working on fixing it.
                </p>
                <div className="flex flex-col justify-center gap-4 sm:flex-row">
                    <Button asChild variant="default">
                        <Link href="/dashboard">Back to Dashboard</Link>
                    </Button>
                </div>
            </div>
        </>
    );
}
