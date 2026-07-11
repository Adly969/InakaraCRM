import { Head, Link } from '@inertiajs/react';
import { ShieldAlert } from 'lucide-react';
import { Button } from '@/components/ui/button';

export default function Forbidden() {
    return (
        <>
            <Head title="Access Denied" />
            <div className="flex min-h-screen flex-col items-center justify-center bg-background px-4 text-center">
                <div className="mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-destructive/10 text-destructive">
                    <ShieldAlert className="h-10 w-10" />
                </div>
                <h1 className="mb-3 text-4xl font-extrabold tracking-tight text-foreground sm:text-5xl">
                    403 - Access Denied
                </h1>
                <p className="mb-8 max-w-md text-base text-muted-foreground">
                    You do not have permission to access this page or resource.
                    Please contact your administrator if you believe this is an
                    error.
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
