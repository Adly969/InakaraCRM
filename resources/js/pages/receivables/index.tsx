import { Head, Link } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Receipt, Calendar, ArrowRight, TrendingUp } from 'lucide-react';
import React from 'react';

interface SummaryRow {
    customer_id: number;
    customer_name: string;
    total_outstanding: number | string;
    open_invoices_count: number;
    oldest_due_date: string | null;
}

interface Props {
    summary: SummaryRow[];
    metrics: {
        total_outstanding: number;
        total_overdue: number;
        today_collection: number;
        weekly_collection: number;
        monthly_collection: number;
        collection_rate: number;
        dso: number;
        top_debtors: Array<{ id: number; name: string; outstanding: number }>;
        forecast: {
            next_7_days: number;
            next_14_days: number;
            next_30_days: number;
        };
    };
}

export default function ReceivablesIndex({ summary, metrics }: Props) {
    const formatCurrency = (val: number | string) => {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(Number(val));
    };

    return (
        <>
            <Head title="Penagihan & Piutang (Receivables)" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div className="flex flex-col gap-1">
                        <div className="flex items-center gap-2">
                            <Receipt className="h-6 w-6 text-sky-600 dark:text-sky-400" />
                            <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                                Penagihan & Piutang Usaha (Receivables)
                            </h1>
                        </div>
                        <p className="text-sm text-neutral-500 dark:text-neutral-400">
                            Pantau piutang berjalan pelanggan, kepatuhan termin pembayaran (TOP), dan klasifikasi umur piutang.
                        </p>
                    </div>

                    <Button asChild variant="outline" className="border-neutral-300 dark:border-neutral-700 text-neutral-800 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-800">
                        <Link href="/receivables/aging">
                            <Calendar className="mr-2 h-4 w-4" />
                            Matriks Umur Piutang (Aging Matrix)
                        </Link>
                    </Button>
                </div>

                {/* KPI Metrics block */}
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">
                                Total Outstanding
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-neutral-900 dark:text-neutral-50">
                                {formatCurrency(metrics.total_outstanding)}
                            </div>
                            <p className="text-xs text-red-600 dark:text-red-400 mt-1">
                                Overdue: {formatCurrency(metrics.total_overdue)}
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">
                                Monthly Collection
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-neutral-900 dark:text-neutral-50">
                                {formatCurrency(metrics.monthly_collection)}
                            </div>
                            <p className="text-xs text-neutral-500 mt-1">
                                Today: {formatCurrency(metrics.today_collection)}
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">
                                Days Sales Outstanding (DSO)
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                                {metrics.dso} Days
                            </div>
                            <p className="text-xs text-neutral-500 mt-1">
                                Avg collections turn timeline
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">
                                Collection Rate
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                                {metrics.collection_rate}%
                            </div>
                            <p className="text-xs text-neutral-500 mt-1 flex items-center gap-1">
                                <TrendingUp className="h-3 w-3" /> Target: 90% collections
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Main lists */}
                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    {/* Invoices List */}
                    <div className="lg:col-span-2 space-y-6">
                        <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm overflow-hidden">
                            <CardHeader>
                                <CardTitle className="text-lg">Receivables by Customer</CardTitle>
                            </CardHeader>
                            <CardContent className="p-0">
                                {summary.length === 0 ? (
                                    <div className="p-6 text-center text-neutral-500">
                                        No customer outstanding balances.
                                    </div>
                                ) : (
                                    <div className="overflow-x-auto">
                                        <table className="w-full border-collapse text-left text-sm">
                                            <thead className="bg-neutral-50 dark:bg-neutral-900 text-xs font-semibold text-neutral-600 dark:text-neutral-400 border-b border-neutral-200 dark:border-neutral-800">
                                                <tr>
                                                    <th className="px-6 py-3">Customer Name</th>
                                                    <th className="px-6 py-3">Open Invoices</th>
                                                    <th className="px-6 py-3">Oldest Invoice Date</th>
                                                    <th className="px-6 py-3">Total Outstanding</th>
                                                    <th className="px-6 py-3 text-right">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody className="divide-y divide-neutral-200 dark:divide-neutral-800 bg-white dark:bg-neutral-950">
                                                {summary.map((row) => (
                                                    <tr key={row.customer_id} className="hover:bg-neutral-50/50 dark:hover:bg-neutral-900/30">
                                                        <td className="px-6 py-4 font-medium text-neutral-900 dark:text-neutral-100">
                                                            {row.customer_name}
                                                        </td>
                                                        <td className="px-6 py-4">{row.open_invoices_count}</td>
                                                        <td className="px-6 py-4 text-neutral-500">{row.oldest_due_date || 'N/A'}</td>
                                                        <td className="px-6 py-4 font-semibold text-indigo-600 dark:text-indigo-400">
                                                            {formatCurrency(row.total_outstanding)}
                                                        </td>
                                                        <td className="px-6 py-4 text-right">
                                                            <Button asChild variant="ghost" size="icon" className="h-8 w-8 text-neutral-500 hover:text-indigo-600">
                                                                <Link href={`/receivables/customer/${row.customer_id}`}>
                                                                    <ArrowRight className="h-4 w-4" />
                                                                </Link>
                                                            </Button>
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>

                    {/* Cash collection forecast */}
                    <div className="space-y-6">
                        <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm">
                            <CardHeader>
                                <CardTitle className="text-lg">Cash Collection Forecast</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <p className="text-xs text-neutral-500">Predicted collections timeline based on due dates.</p>
                                <div className="space-y-3">
                                    <div className="flex justify-between items-center text-sm border-b pb-2">
                                        <span className="text-neutral-500">Next 7 Days:</span>
                                        <span className="font-semibold text-neutral-900 dark:text-neutral-100">{formatCurrency(metrics.forecast.next_7_days)}</span>
                                    </div>
                                    <div className="flex justify-between items-center text-sm border-b pb-2">
                                        <span className="text-neutral-500">Next 14 Days:</span>
                                        <span className="font-semibold text-neutral-900 dark:text-neutral-100">{formatCurrency(metrics.forecast.next_14_days)}</span>
                                    </div>
                                    <div className="flex justify-between items-center text-sm">
                                        <span className="text-neutral-500">Next 30 Days:</span>
                                        <span className="font-semibold text-indigo-600 dark:text-indigo-400">{formatCurrency(metrics.forecast.next_30_days)}</span>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </>
    );
}
