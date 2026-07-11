import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { FileQuestion } from 'lucide-react';

export default function NotFound() {
    return (
        <>
            <Head title="Page Not Found" />
            <div className="flex min-h-screen flex-col items-center justify-center bg-background px-4 text-center">
                <div className="flex h-16 w-16 items-center justify-center rounded-full bg-muted text-muted-foreground mb-6">
                    <FileQuestion className="h-10 w-10" />
                </div>
                <h1 className="text-4xl font-extrabold tracking-tight sm:text-5xl text-foreground mb-3">
                    404 - Page Not Found
                </h1>
                <p className="text-muted-foreground max-w-md mb-8 text-base">
                    The page you are looking for does not exist, has been removed, or has had its name changed.
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
