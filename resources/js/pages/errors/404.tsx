import { Head, Link } from '@inertiajs/react';
import { FileQuestion } from 'lucide-react';
import { Button } from '@/components/ui/button';

export default function NotFound() {
    return (
        <>
            <Head title="Page Not Found" />
            <div className="flex min-h-screen flex-col items-center justify-center bg-background px-4 text-center">
                <div className="mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-muted text-muted-foreground">
                    <FileQuestion className="h-10 w-10" />
                </div>
                <h1 className="mb-3 text-4xl font-extrabold tracking-tight text-foreground sm:text-5xl">
                    404 - Page Not Found
                </h1>
                <p className="mb-8 max-w-md text-base text-muted-foreground">
                    The page you are looking for does not exist, has been
                    removed, or has had its name changed.
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
