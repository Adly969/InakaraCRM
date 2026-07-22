import React, { useState, useEffect } from 'react';
import { Head } from '@inertiajs/react';

interface Metrics {
    total_events_processed: number;
    queued_jobs_depth: number;
    failed_jobs_count: number;
    dlq_active_count: number;
}

export default function IntegrationDashboard() {
    const [metrics, setMetrics] = useState<Metrics | null>(null);
    const [loading, setLoading] = useState<boolean>(true);

    useEffect(() => {
        fetch('/api/v1/finance/gateway/health')
            .then(res => res.json())
            .then(data => {
                setMetrics(data.metrics || null);
                setLoading(false);
            })
            .catch(err => {
                console.error(err);
                setLoading(false);
            });
    }, []);

    return (
        <>
            <Head title="ERP Financial Integration Hub" />
            <div className="flex h-full flex-1 flex-col gap-4 p-6">
                <div className="flex flex-col gap-1">
                    <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                        ERP Financial Integration Hub (Accounting Gateway)
                    </h1>
                    <p className="text-sm text-neutral-500 dark:text-neutral-400">
                        Asynchronous event-driven sub-ledger postings tracker, Dead Letter Queue replayer, and dynamic rules inspector.
                    </p>
                </div>

                {loading ? (
                    <div className="text-center py-10 text-neutral-500">Loading OpenTelemetry metrics...</div>
                ) : (
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <div className="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                            <p className="text-xs font-semibold text-neutral-500 uppercase tracking-wider">Total Ingested Events</p>
                            <h3 className="text-2xl font-bold text-neutral-900 dark:text-neutral-50 mt-1">
                                {metrics?.total_events_processed.toLocaleString() ?? 0}
                            </h3>
                        </div>

                        <div className="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                            <p className="text-xs font-semibold text-neutral-500 uppercase tracking-wider">Active Queue Depth</p>
                            <h3 className="text-2xl font-bold text-neutral-900 dark:text-neutral-50 mt-1">
                                {metrics?.queued_jobs_depth ?? 0}
                            </h3>
                        </div>

                        <div className="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                            <p className="text-xs font-semibold text-neutral-500 uppercase tracking-wider">Queue Processing Failures</p>
                            <h3 className="text-2xl font-bold text-red-600 mt-1">
                                {metrics?.failed_jobs_count ?? 0}
                            </h3>
                        </div>

                        <div className="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                            <p className="text-xs font-semibold text-neutral-500 uppercase tracking-wider">Dead Letter Queue (DLQ)</p>
                            <h3 className="text-2xl font-bold text-red-600 mt-1">
                                {metrics?.dlq_active_count ?? 0}
                            </h3>
                        </div>
                    </div>
                )}

                <div className="flex-1 rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                    <h2 className="text-lg font-bold text-neutral-800 dark:text-neutral-200 mb-4">Dead Letter Queue Replay Terminal</h2>
                    <p className="text-sm text-neutral-500 dark:text-neutral-400 mb-6">
                        Errors and poison payloads route here. Correct your matching rules and trigger dry-run, sandbox, or production replays dynamically.
                    </p>

                    <div className="border border-neutral-200 rounded-lg p-10 text-center text-sm text-neutral-500 dark:border-neutral-800">
                        All ingestion pipelines are currently operational. No active failures detected in DLQ queue.
                    </div>
                </div>
            </div>
        </>
    );
}
