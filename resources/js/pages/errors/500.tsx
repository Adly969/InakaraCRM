import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { ServerCrash } from 'lucide-react';

export default function ServerError() {
    return (
        <>
            <Head title="Server Error" />
            <div className="flex min-h-screen flex-col items-center justify-center bg-background px-4 text-center">
                <div className="flex h-16 w-16 items-center justify-center rounded-full bg-destructive/10 text-destructive mb-6">
                    <ServerCrash className="h-10 w-10" />
                </div>
                <h1 className="text-4xl font-extrabold tracking-tight sm:text-5xl text-foreground mb-3">
                    500 - Server Error
                </h1>
                <p className="text-muted-foreground max-w-md mb-8 text-base">
                    Oops! Something went wrong on our server. We have logged this error and are working on fixing it.
                </p>
                <div className="flex flex-col sm:flex-row gap-4 justify-center">
                    <Button asChild variant="default">
                        <Link href="/dashboard">
                            Back to Dashboard
                        </Link>
                    </Button>
                </div>
            </div>
        </>
    );
}
