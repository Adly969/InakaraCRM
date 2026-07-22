import { Head, Link } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { ArrowLeft, Calendar } from 'lucide-react';
import React from 'react';

interface AgingRow {
    customer_id: number;
    customer_name: string;
    current: number;
    bucket_1_30: number;
    bucket_31_60: number;
    bucket_61_90: number;
    bucket_over_90: number;
    total: number;
}

interface Props {
    agingSummary: AgingRow[];
}

export default function ReceivablesAging({ agingSummary }: Props) {
    const formatCurrency = (val: number | string) => {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(Number(val));
    };

    // Calculate columns totals
    const totals = agingSummary.reduce((acc, row) => ({
        current: acc.current + Number(row.current),
        bucket_1_30: acc.bucket_1_30 + Number(row.bucket_1_30),
        bucket_31_60: acc.bucket_31_60 + Number(row.bucket_31_60),
        bucket_61_90: acc.bucket_61_90 + Number(row.bucket_61_90),
        bucket_over_90: acc.bucket_over_90 + Number(row.bucket_over_90),
        total: acc.total + Number(row.total),
    }), { current: 0, bucket_1_30: 0, bucket_31_60: 0, bucket_61_90: 0, bucket_over_90: 0, total: 0 });

    return (
        <>
            <Head title="Receivables Aging Matrix" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Button asChild variant="ghost" size="icon" className="h-8 w-8 text-neutral-500 hover:text-neutral-900">
                            <Link href="/receivables">
                                <ArrowLeft className="h-4 w-4" />
                            </Link>
                        </Button>
                        <div>
                            <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                                Accounts Receivable Aging Matrix
                            </h1>
                            <p className="text-sm text-neutral-500 dark:text-neutral-400">
                                Breakdown of customer outstanding balances grouped by overdue aging intervals.
                            </p>
                        </div>
                    </div>
                </div>

                <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm overflow-hidden">
                    <CardHeader className="flex flex-row items-center gap-2">
                        <Calendar className="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                        <CardTitle className="text-lg">Aging Interval Summary</CardTitle>
                    </CardHeader>
                    <CardContent className="p-0">
                        {agingSummary.length === 0 ? (
                            <div className="p-6 text-center text-neutral-500">
                                No outstanding invoice data to populate matrix.
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full border-collapse text-left text-sm">
                                    <thead className="bg-neutral-50 dark:bg-neutral-900 text-xs font-semibold text-neutral-600 dark:text-neutral-400 border-b border-neutral-200 dark:border-neutral-800">
                                        <tr>
                                            <th className="px-6 py-3 min-w-[200px]">Customer Name</th>
                                            <th className="px-6 py-3 text-right">Current</th>
                                            <th className="px-6 py-3 text-right">1-30 Days</th>
                                            <th className="px-6 py-3 text-right">31-60 Days</th>
                                            <th className="px-6 py-3 text-right">61-90 Days</th>
                                            <th className="px-6 py-3 text-right">90+ Days</th>
                                            <th className="px-6 py-3 text-right font-bold text-indigo-900 dark:text-indigo-200">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-neutral-200 dark:divide-neutral-800 bg-white dark:bg-neutral-950">
                                        {agingSummary.map((row) => (
                                            <tr key={row.customer_id} className="hover:bg-neutral-50/50 dark:hover:bg-neutral-900/30">
                                                <td className="px-6 py-4 font-medium text-neutral-900 dark:text-neutral-100">
                                                    <Link href={`/receivables/customer/${row.customer_id}`} className="hover:underline text-indigo-600 dark:text-indigo-400">
                                                        {row.customer_name}
                                                    </Link>
                                                </td>
                                                <td className="px-6 py-4 text-right text-neutral-600 dark:text-neutral-400">{formatCurrency(row.current)}</td>
                                                <td className="px-6 py-4 text-right text-amber-600 dark:text-amber-400">{formatCurrency(row.bucket_1_30)}</td>
                                                <td className="px-6 py-4 text-right text-orange-600 dark:text-orange-400">{formatCurrency(row.bucket_31_60)}</td>
                                                <td className="px-6 py-4 text-right text-red-500 dark:text-red-400">{formatCurrency(row.bucket_61_90)}</td>
                                                <td className="px-6 py-4 text-right font-semibold text-red-600 dark:text-red-400">{formatCurrency(row.bucket_over_90)}</td>
                                                <td className="px-6 py-4 text-right font-bold text-indigo-600 dark:text-indigo-400">{formatCurrency(row.total)}</td>
                                            </tr>
                                        ))}
                                        {/* Total Summary Row */}
                                        <tr className="bg-neutral-50 dark:bg-neutral-900 font-bold border-t border-neutral-200 dark:border-neutral-800">
                                            <td className="px-6 py-4 text-neutral-900 dark:text-neutral-50">Total Receivables</td>
                                            <td className="px-6 py-4 text-right text-neutral-900 dark:text-neutral-50">{formatCurrency(totals.current)}</td>
                                            <td className="px-6 py-4 text-right text-amber-600 dark:text-amber-400">{formatCurrency(totals.bucket_1_30)}</td>
                                            <td className="px-6 py-4 text-right text-orange-600 dark:text-orange-400">{formatCurrency(totals.bucket_31_60)}</td>
                                            <td className="px-6 py-4 text-right text-red-500 dark:text-red-400">{formatCurrency(totals.bucket_61_90)}</td>
                                            <td className="px-6 py-4 text-right text-red-600 dark:text-red-400">{formatCurrency(totals.bucket_over_90)}</td>
                                            <td className="px-6 py-4 text-right text-indigo-600 dark:text-indigo-400">{formatCurrency(totals.total)}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
