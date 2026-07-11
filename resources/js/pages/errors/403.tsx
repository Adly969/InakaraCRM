import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { ShieldAlert } from 'lucide-react';

export default function Forbidden() {
    return (
        <>
            <Head title="Access Denied" />
            <div className="flex min-h-screen flex-col items-center justify-center bg-background px-4 text-center">
                <div className="flex h-16 w-16 items-center justify-center rounded-full bg-destructive/10 text-destructive mb-6">
                    <ShieldAlert className="h-10 w-10" />
                </div>
                <h1 className="text-4xl font-extrabold tracking-tight sm:text-5xl text-foreground mb-3">
                    403 - Access Denied
                </h1>
                <p className="text-muted-foreground max-w-md mb-8 text-base">
                    You do not have permission to access this page or resource. Please contact your administrator if you believe this is an error.
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
