import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import type { Customer, Invoice } from '@/types';
import type { Payment } from '@/types/payment';
import { ArrowLeft, MessageSquare, Phone, Plus, Calendar, Receipt } from 'lucide-react';
import React, { useState } from 'react';

interface Props {
    customer: Customer;
    invoices: Invoice[];
    payments: Payment[];
    aging: {
        current: number;
        bucket_1_30: number;
        bucket_31_60: number;
        bucket_61_90: number;
        bucket_over_90: number;
        total: number;
    };
}

export default function CustomerReceivables({ customer, invoices, payments, aging }: Props) {
    const { auth } = usePage().props as any;

    const { data, setData, post, processing, errors, reset } = useForm({
        customer_id: customer.id,
        invoice_id: '',
        activity_type: 'phone_call',
        status: 'completed',
        promise_amount: '',
        promise_date: '',
        notes: '',
        next_follow_up_date: '',
        assigned_to: auth.user.id,
    });

    const formatCurrency = (val: number | string) => {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(Number(val));
    };

    const handleLogActivity = (e: React.FormEvent) => {
        e.preventDefault();
        post('/collection-activities', {
            onSuccess: () => {
                reset('notes', 'promise_amount', 'promise_date', 'next_follow_up_date', 'invoice_id');
                alert('Collection activity logged successfully.');
            }
        });
    };

    return (
        <>
            <Head title={`Customer Receivables - ${customer.name}`} />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                <div className="flex items-center gap-4">
                    <Button asChild variant="ghost" size="icon" className="h-8 w-8 text-neutral-500 hover:text-neutral-900">
                        <Link href="/receivables">
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                            Receivables Breakdown: {customer.name}
                        </h1>
                        <p className="text-sm text-neutral-500 dark:text-neutral-400">
                            Detailed invoice tracking, collection history, and communications ledger.
                        </p>
                    </div>
                </div>

                {/* Aging visual buckets */}
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    <div className="bg-neutral-50 dark:bg-neutral-900 p-4 rounded border text-center">
                        <div className="text-xs text-neutral-400 font-semibold uppercase">Current</div>
                        <div className="text-lg font-bold text-neutral-800 dark:text-neutral-200">{formatCurrency(aging.current)}</div>
                    </div>
                    <div className="bg-amber-50 dark:bg-amber-950/20 p-4 rounded border border-amber-200 text-center">
                        <div className="text-xs text-amber-500 font-semibold uppercase">1-30 Days</div>
                        <div className="text-lg font-bold text-amber-700 dark:text-amber-400">{formatCurrency(aging.bucket_1_30)}</div>
                    </div>
                    <div className="bg-orange-50 dark:bg-orange-950/20 p-4 rounded border border-orange-200 text-center">
                        <div className="text-xs text-orange-500 font-semibold uppercase">31-60 Days</div>
                        <div className="text-lg font-bold text-orange-700 dark:text-orange-400">{formatCurrency(aging.bucket_31_60)}</div>
                    </div>
                    <div className="bg-red-50 dark:bg-red-950/20 p-4 rounded border border-red-200 text-center">
                        <div className="text-xs text-red-500 font-semibold uppercase">61-90 Days</div>
                        <div className="text-lg font-bold text-red-700 dark:text-red-400">{formatCurrency(aging.bucket_61_90)}</div>
                    </div>
                    <div className="bg-red-100 dark:bg-red-900/30 p-4 rounded border border-red-300 text-center">
                        <div className="text-xs text-red-600 font-semibold uppercase">90+ Days</div>
                        <div className="text-lg font-bold text-red-800 dark:text-red-300">{formatCurrency(aging.bucket_over_90)}</div>
                    </div>
                </div>

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div className="space-y-6 lg:col-span-2">
                        {/* Outstanding Invoices */}
                        <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm overflow-hidden">
                            <CardHeader>
                                <CardTitle className="text-lg">Outstanding Invoices</CardTitle>
                            </CardHeader>
                            <CardContent className="p-0">
                                {invoices.length === 0 ? (
                                    <div className="p-6 text-center text-neutral-500">
                                        No outstanding invoices for this customer.
                                    </div>
                                ) : (
                                    <table className="w-full border-collapse text-left text-sm">
                                        <thead className="bg-neutral-50 dark:bg-neutral-900 text-xs font-semibold text-neutral-600 dark:text-neutral-400 border-b border-neutral-200 dark:border-neutral-800">
                                            <tr>
                                                <th className="px-6 py-3">Invoice Ref</th>
                                                <th className="px-6 py-3">Due Date</th>
                                                <th className="px-6 py-3">Total Amount</th>
                                                <th className="px-6 py-3 text-right">Outstanding Balance</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-neutral-200 dark:divide-neutral-800 bg-white dark:bg-neutral-950">
                                            {invoices.map((inv) => (
                                                <tr key={inv.id} className="hover:bg-neutral-50/50 dark:hover:bg-neutral-900/30">
                                                    <td className="px-6 py-4 font-medium text-neutral-900 dark:text-neutral-100">
                                                        <Link href={`/invoices/${inv.id}`} className="hover:underline text-indigo-600 dark:text-indigo-400">
                                                            {inv.reference_no}
                                                        </Link>
                                                    </td>
                                                    <td className="px-6 py-4">{inv.due_date}</td>
                                                    <td className="px-6 py-4">{formatCurrency(inv.total_amount)}</td>
                                                    <td className="px-6 py-4 text-right font-bold text-red-600 dark:text-red-400">
                                                        {formatCurrency(inv.outstanding_balance)}
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                )}
                            </CardContent>
                        </Card>

                        {/* Recent Payments Received */}
                        <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm overflow-hidden">
                            <CardHeader>
                                <CardTitle className="text-lg">Recent Received Payments</CardTitle>
                            </CardHeader>
                            <CardContent className="p-0">
                                {payments.length === 0 ? (
                                    <div className="p-6 text-center text-neutral-500">
                                        No payments received from this customer.
                                    </div>
                                ) : (
                                    <table className="w-full border-collapse text-left text-sm">
                                        <thead className="bg-neutral-50 dark:bg-neutral-900 text-xs font-semibold text-neutral-600 dark:text-neutral-400 border-b border-neutral-200 dark:border-neutral-800">
                                            <tr>
                                                <th className="px-6 py-3">Payment Ref</th>
                                                <th className="px-6 py-3">Date</th>
                                                <th className="px-6 py-3">Amount</th>
                                                <th className="px-6 py-3">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-neutral-200 dark:divide-neutral-800 bg-white dark:bg-neutral-950">
                                            {payments.map((pay) => (
                                                <tr key={pay.id}>
                                                    <td className="px-6 py-4 font-medium text-neutral-900 dark:text-neutral-100">
                                                        <Link href={`/payments/${pay.id}`} className="hover:underline text-indigo-600 dark:text-indigo-400">
                                                            {pay.reference_no || 'Draft'}
                                                        </Link>
                                                    </td>
                                                    <td className="px-6 py-4">{pay.payment_date}</td>
                                                    <td className="px-6 py-4">{formatCurrency(pay.amount)}</td>
                                                    <td className="px-6 py-4">{pay.status.toUpperCase()}</td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                )}
                            </CardContent>
                        </Card>
                    </div>

                    {/* Sidebar logs collection activity form */}
                    <div className="space-y-6">
                        <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm">
                            <CardHeader>
                                <CardTitle className="text-lg">Log CRM Collection Activity</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <form onSubmit={handleLogActivity} className="space-y-4">
                                    <div className="space-y-1">
                                        <label className="text-xs font-semibold text-neutral-600 dark:text-neutral-400">
                                            Target Invoice
                                        </label>
                                        <select
                                            value={data.invoice_id}
                                            onChange={(e) => setData('invoice_id', e.target.value)}
                                            className="w-full rounded border p-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                        >
                                            <option value="">General (No Invoice Selected)</option>
                                            {invoices.map((inv) => (
                                                <option key={inv.id} value={inv.id}>
                                                    {inv.reference_no} ({formatCurrency(inv.outstanding_balance)})
                                                </option>
                                            ))}
                                        </select>
                                    </div>

                                    <div className="space-y-1">
                                        <label className="text-xs font-semibold text-neutral-600 dark:text-neutral-400">
                                            Activity Type
                                        </label>
                                        <select
                                            value={data.activity_type}
                                            onChange={(e) => setData('activity_type', e.target.value)}
                                            className="w-full rounded border p-2 text-sm focus:outline-none"
                                            required
                                        >
                                            <option value="phone_call">Phone Call</option>
                                            <option value="whatsapp_message">WhatsApp Message</option>
                                            <option value="email_reminder">Email Reminder</option>
                                            <option value="site_visit">Site Visit</option>
                                            <option value="demand_letter">Demand Letter</option>
                                        </select>
                                    </div>

                                    <div className="space-y-1">
                                        <label className="text-xs font-semibold text-neutral-600 dark:text-neutral-400">
                                            Status
                                        </label>
                                        <select
                                            value={data.status}
                                            onChange={(e) => setData('status', e.target.value)}
                                            className="w-full rounded border p-2 text-sm focus:outline-none"
                                            required
                                        >
                                            <option value="completed">Completed / Contacted</option>
                                            <option value="pending">No Answer / Pending</option>
                                            <option value="broken">Broken Promise</option>
                                        </select>
                                    </div>

                                    <div className="space-y-1">
                                        <label className="text-xs font-semibold text-neutral-600 dark:text-neutral-400">
                                            Promise Amount (Optional)
                                        </label>
                                        <input
                                            type="number"
                                            value={data.promise_amount}
                                            onChange={(e) => setData('promise_amount', e.target.value)}
                                            className="w-full rounded border p-2 text-sm"
                                            placeholder="0.00"
                                        />
                                    </div>

                                    <div className="space-y-1">
                                        <label className="text-xs font-semibold text-neutral-600 dark:text-neutral-400">
                                            Promise Payment Date (Optional)
                                        </label>
                                        <input
                                            type="date"
                                            value={data.promise_date}
                                            onChange={(e) => setData('promise_date', e.target.value)}
                                            className="w-full rounded border p-2 text-sm"
                                        />
                                    </div>

                                    <div className="space-y-1">
                                        <label className="text-xs font-semibold text-neutral-600 dark:text-neutral-400">
                                            Notes / Conversation Log
                                        </label>
                                        <textarea
                                            value={data.notes}
                                            onChange={(e) => setData('notes', e.target.value)}
                                            className="w-full rounded border p-2 text-sm"
                                            rows={3}
                                            placeholder="Write summary of customer response..."
                                        />
                                    </div>

                                    <div className="space-y-1">
                                        <label className="text-xs font-semibold text-neutral-600 dark:text-neutral-400">
                                            Next Follow Up Date
                                        </label>
                                        <input
                                            type="date"
                                            value={data.next_follow_up_date}
                                            onChange={(e) => setData('next_follow_up_date', e.target.value)}
                                            className="w-full rounded border p-2 text-sm"
                                        />
                                    </div>

                                    <Button
                                        type="submit"
                                        className="w-full bg-indigo-600 hover:bg-indigo-700 text-white mt-2"
                                        disabled={processing}
                                    >
                                        Log CRM Activity
                                    </Button>
                                </form>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </>
    );
}
