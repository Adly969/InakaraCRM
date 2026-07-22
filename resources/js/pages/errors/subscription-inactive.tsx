import React from 'react';
import { Head, Link } from '@inertiajs/react';

export default function SubscriptionInactive() {
    return (
        <div className="min-h-screen flex items-center justify-center bg-zinc-50 dark:bg-zinc-950 p-6">
            <Head title="Subscription Inactive" />
            <div className="w-full max-w-md p-8 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-xl text-center space-y-6">
                <div className="w-16 h-16 bg-red-100 dark:bg-red-950/50 text-red-600 dark:text-red-400 rounded-full flex items-center justify-center mx-auto">
                    <svg className="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                
                <div className="space-y-2">
                    <h1 className="text-2xl font-bold text-zinc-900 dark:text-zinc-50">
                        Subscription Suspended
                    </h1>
                    <p className="text-sm text-zinc-500 dark:text-zinc-400">
                        Your workspace access has been temporarily blocked because your subscription trial has expired or requires renewal.
                    </p>
                </div>

                <div className="pt-4 border-t border-zinc-100 dark:border-zinc-850 flex flex-col space-y-2">
                    <a
                        href="mailto:billing@inakara.com"
                        className="w-full py-2.5 px-4 bg-zinc-900 dark:bg-zinc-50 text-white dark:text-zinc-950 hover:bg-zinc-850 dark:hover:bg-zinc-100 font-semibold rounded-lg shadow transition-colors text-sm text-center"
                    >
                        Contact Billing Support
                    </a>
                    <Link
                        href="/"
                        className="w-full py-2.5 px-4 bg-transparent border border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-50 font-semibold rounded-lg transition-colors text-sm text-center"
                    >
                        Return Home
                    </Link>
                </div>
            </div>
        </div>
    );
}
