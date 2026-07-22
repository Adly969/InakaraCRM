import { Head, Link, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { Customer, Invoice } from '@/types';
import { ArrowLeft, Save } from 'lucide-react';
import React, { useState, useEffect } from 'react';

interface Props {
    customers: Customer[];
    invoices: Invoice[];
}

export default function CreatePayment({ customers, invoices }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        customer_id: '',
        payment_date: new Date().toISOString().split('T')[0],
        payment_method: 'bank_transfer',
        amount: '',
        currency: 'IDR',
        exchange_rate: '1.000000',
        bank_name: '',
        bank_account_no: '',
        cheque_no: '',
        transaction_ref: '',
        notes: '',
        allocations: [] as Array<{ invoice_id: number; amount: string; notes: string }>,
    });

    const [filteredInvoices, setFilteredInvoices] = useState<Invoice[]>([]);
    const [selectedInvoices, setSelectedInvoices] = useState<Record<number, boolean>>({});
    const [allocationAmounts, setAllocationAmounts] = useState<Record<number, string>>({});

    // Filter invoices when customer changes
    useEffect(() => {
        if (data.customer_id) {
            const customerInvoices = invoices.filter(
                (inv) => inv.customer_id === Number(data.customer_id)
            );
            setFilteredInvoices(customerInvoices);
            setSelectedInvoices({});
            setAllocationAmounts({});
        } else {
            setFilteredInvoices([]);
        }
    }, [data.customer_id, invoices]);

    // Handle allocation calculation and forms synchronizations
    useEffect(() => {
        const allocations = Object.keys(selectedInvoices)
            .filter((id) => selectedInvoices[Number(id)])
            .map((id) => ({
                invoice_id: Number(id),
                amount: allocationAmounts[Number(id)] || '0.00',
                notes: 'Allocated during creation',
            }));
        setData('allocations', allocations);
    }, [selectedInvoices, allocationAmounts]);

    const handleCheckboxChange = (invoiceId: number, outstandingBalance: number) => {
        const isChecked = !selectedInvoices[invoiceId];
        setSelectedInvoices((prev) => ({ ...prev, [invoiceId]: isChecked }));

        if (isChecked) {
            // Auto fill with the outstanding balance or remaining payment amount
            const currentAllocated = Object.keys(allocationAmounts)
                .filter((id) => Number(id) !== invoiceId && selectedInvoices[Number(id)])
                .reduce((sum, id) => sum + Number(allocationAmounts[Number(id)] || 0), 0);

            const paymentLimit = Number(data.amount || 0);
            const remaining = Math.max(0, paymentLimit - currentAllocated);
            const autoFill = Math.min(outstandingBalance, remaining);

            setAllocationAmounts((prev) => ({
                ...prev,
                [invoiceId]: autoFill.toFixed(2),
            }));
        } else {
            setAllocationAmounts((prev) => {
                const copy = { ...prev };
                delete copy[invoiceId];
                return copy;
            });
        }
    };

    const handleAllocationAmountChange = (invoiceId: number, value: string) => {
        setAllocationAmounts((prev) => ({ ...prev, [invoiceId]: value }));
    };

    const totalAllocated = Object.keys(selectedInvoices)
        .filter((id) => selectedInvoices[Number(id)])
        .reduce((sum, id) => sum + Number(allocationAmounts[Number(id)] || 0), 0);

    const unallocatedAmount = Math.max(0, Number(data.amount || 0) - totalAllocated);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/payments');
    };

    return (
        <>
            <Head title="Record Payment" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Button asChild variant="ghost" size="icon" className="h-8 w-8 text-neutral-500 hover:text-neutral-900">
                            <Link href="/payments">
                                <ArrowLeft className="h-4 w-4" />
                            </Link>
                        </Button>
                        <div>
                            <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                                Record Incoming Payment
                            </h1>
                            <p className="text-sm text-neutral-500 dark:text-neutral-400">
                                Record customer cash or bank receipts and allocate them to open invoices.
                            </p>
                        </div>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div className="space-y-6 lg:col-span-2">
                        {/* Core details */}
                        <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm">
                            <CardHeader>
                                <CardTitle className="text-lg">Payment Details</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div className="space-y-1">
                                        <label className="text-xs font-semibold text-neutral-600 dark:text-neutral-400">
                                            Customer
                                        </label>
                                        <select
                                            value={data.customer_id}
                                            onChange={(e) => setData('customer_id', e.target.value)}
                                            className="w-full rounded-md border border-neutral-200 bg-white px-3 py-2 text-sm text-neutral-900 focus:border-indigo-500 focus:outline-none dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-50"
                                            required
                                        >
                                            <option value="">Select Customer...</option>
                                            {customers.map((cust) => (
                                                <option key={cust.id} value={cust.id}>
                                                    {cust.name}
                                                </option>
                                            ))}
                                        </select>
                                        {errors.customer_id && (
                                            <p className="text-xs text-red-600">{errors.customer_id}</p>
                                        )}
                                    </div>

                                    <div className="space-y-1">
                                        <label className="text-xs font-semibold text-neutral-600 dark:text-neutral-400">
                                            Payment Date
                                        </label>
                                        <input
                                            type="date"
                                            value={data.payment_date}
                                            onChange={(e) => setData('payment_date', e.target.value)}
                                            className="w-full rounded-md border border-neutral-200 bg-white px-3 py-2 text-sm text-neutral-900 focus:border-indigo-500 focus:outline-none dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-50"
                                            required
                                        />
                                        {errors.payment_date && (
                                            <p className="text-xs text-red-600">{errors.payment_date}</p>
                                        )}
                                    </div>

                                    <div className="space-y-1">
                                        <label className="text-xs font-semibold text-neutral-600 dark:text-neutral-400">
                                            Payment Method
                                        </label>
                                        <select
                                            value={data.payment_method}
                                            onChange={(e) => setData('payment_method', e.target.value)}
                                            className="w-full rounded-md border border-neutral-200 bg-white px-3 py-2 text-sm text-neutral-900 focus:border-indigo-500 focus:outline-none dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-50"
                                            required
                                        >
                                            <option value="cash">Cash</option>
                                            <option value="bank_transfer">Bank Transfer</option>
                                            <option value="cheque">Cheque</option>
                                            <option value="giro">Giro</option>
                                            <option value="virtual_account">Virtual Account</option>
                                            <option value="qris">QRIS</option>
                                            <option value="credit_card">Credit Card</option>
                                            <option value="other">Other</option>
                                        </select>
                                        {errors.payment_method && (
                                            <p className="text-xs text-red-600">{errors.payment_method}</p>
                                        )}
                                    </div>

                                    <div className="space-y-1">
                                        <label className="text-xs font-semibold text-neutral-600 dark:text-neutral-400">
                                            Payment Amount
                                        </label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            placeholder="Enter payment amount..."
                                            value={data.amount}
                                            onChange={(e) => setData('amount', e.target.value)}
                                            className="w-full rounded-md border border-neutral-200 bg-white px-3 py-2 text-sm text-neutral-900 focus:border-indigo-500 focus:outline-none dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-50"
                                            required
                                        />
                                        {errors.amount && (
                                            <p className="text-xs text-red-600">{errors.amount}</p>
                                        )}
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div className="space-y-1">
                                        <label className="text-xs font-semibold text-neutral-600 dark:text-neutral-400">
                                            Currency
                                        </label>
                                        <input
                                            type="text"
                                            value={data.currency}
                                            onChange={(e) => setData('currency', e.target.value)}
                                            className="w-full rounded-md border border-neutral-200 bg-neutral-100 px-3 py-2 text-sm text-neutral-500 focus:outline-none dark:border-neutral-800 dark:bg-neutral-900"
                                            readOnly
                                        />
                                    </div>
                                    <div className="space-y-1">
                                        <label className="text-xs font-semibold text-neutral-600 dark:text-neutral-400">
                                            Exchange Rate
                                        </label>
                                        <input
                                            type="number"
                                            step="0.000001"
                                            value={data.exchange_rate}
                                            onChange={(e) => setData('exchange_rate', e.target.value)}
                                            className="w-full rounded-md border border-neutral-200 bg-neutral-100 px-3 py-2 text-sm text-neutral-500 focus:outline-none dark:border-neutral-800 dark:bg-neutral-900"
                                            readOnly
                                        />
                                    </div>
                                </div>

                                {['bank_transfer', 'cheque', 'giro', 'virtual_account'].includes(data.payment_method) && (
                                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 pt-2 border-t border-neutral-100 dark:border-neutral-900">
                                        <div className="space-y-1">
                                            <label className="text-xs font-semibold text-neutral-600 dark:text-neutral-400">
                                                Bank Name
                                            </label>
                                            <input
                                                type="text"
                                                placeholder="e.g. BCA, Mandiri"
                                                value={data.bank_name}
                                                onChange={(e) => setData('bank_name', e.target.value)}
                                                className="w-full rounded-md border border-neutral-200 bg-white px-3 py-2 text-sm text-neutral-900 focus:border-indigo-500 focus:outline-none dark:border-neutral-800 dark:bg-neutral-950"
                                            />
                                        </div>

                                        <div className="space-y-1">
                                            <label className="text-xs font-semibold text-neutral-600 dark:text-neutral-400">
                                                Bank Account No
                                            </label>
                                            <input
                                                type="text"
                                                placeholder="Enter bank account no..."
                                                value={data.bank_account_no}
                                                onChange={(e) => setData('bank_account_no', e.target.value)}
                                                className="w-full rounded-md border border-neutral-200 bg-white px-3 py-2 text-sm text-neutral-900 focus:border-indigo-500 focus:outline-none dark:border-neutral-800 dark:bg-neutral-950"
                                            />
                                        </div>
                                    </div>
                                )}

                                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div className="space-y-1">
                                        <label className="text-xs font-semibold text-neutral-600 dark:text-neutral-400">
                                            Transaction Ref / Cheque No
                                            {data.payment_method === 'cheque' ? ' *' : ''}
                                        </label>
                                        <input
                                            type="text"
                                            placeholder="Enter external transaction reference..."
                                            value={data.payment_method === 'cheque' ? data.cheque_no : data.transaction_ref}
                                            onChange={(e) => {
                                                if (data.payment_method === 'cheque') {
                                                    setData('cheque_no', e.target.value);
                                                } else {
                                                    setData('transaction_ref', e.target.value);
                                                }
                                            }}
                                            className="w-full rounded-md border border-neutral-200 bg-white px-3 py-2 text-sm text-neutral-900 focus:border-indigo-500 focus:outline-none dark:border-neutral-800 dark:bg-neutral-950"
                                            required={data.payment_method === 'cheque'}
                                        />
                                    </div>

                                    <div className="space-y-1">
                                        <label className="text-xs font-semibold text-neutral-600 dark:text-neutral-400">
                                            Notes
                                        </label>
                                        <input
                                            type="text"
                                            placeholder="Remarks..."
                                            value={data.notes}
                                            onChange={(e) => setData('notes', e.target.value)}
                                            className="w-full rounded-md border border-neutral-200 bg-white px-3 py-2 text-sm text-neutral-900 focus:border-indigo-500 focus:outline-none dark:border-neutral-800 dark:bg-neutral-950"
                                        />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Allocations block */}
                        <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm">
                            <CardHeader>
                                <CardTitle className="text-lg">Outstanding Invoices Allocation</CardTitle>
                            </CardHeader>
                            <CardContent className="p-0">
                                {!data.customer_id ? (
                                    <div className="p-6 text-center text-neutral-500">
                                        Please select a customer to load open invoices.
                                    </div>
                                ) : filteredInvoices.length === 0 ? (
                                    <div className="p-6 text-center text-neutral-500">
                                        No open invoices found with outstanding balance for this customer.
                                    </div>
                                ) : (
                                    <div className="overflow-x-auto">
                                        <table className="w-full border-collapse text-left text-sm">
                                            <thead className="bg-neutral-50 dark:bg-neutral-900 text-xs font-semibold text-neutral-600 dark:text-neutral-400 border-b border-neutral-200 dark:border-neutral-800">
                                                <tr>
                                                    <th className="px-6 py-3 w-12">Select</th>
                                                    <th className="px-6 py-3">Reference No</th>
                                                    <th className="px-6 py-3">Due Date</th>
                                                    <th className="px-6 py-3">Total Amount</th>
                                                    <th className="px-6 py-3">Outstanding</th>
                                                    <th className="px-6 py-3 text-right">Allocation Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody className="divide-y divide-neutral-200 dark:divide-neutral-800 bg-white dark:bg-neutral-950">
                                                {filteredInvoices.map((inv) => {
                                                    const isChecked = !!selectedInvoices[inv.id];
                                                    const balance = Number(inv.outstanding_balance);

                                                    return (
                                                        <tr key={inv.id} className="hover:bg-neutral-50/50 dark:hover:bg-neutral-900/30">
                                                            <td className="px-6 py-4">
                                                                <input
                                                                    type="checkbox"
                                                                    checked={isChecked}
                                                                    onChange={() => handleCheckboxChange(inv.id, balance)}
                                                                    className="h-4 w-4 rounded border-neutral-300 text-indigo-600 focus:ring-indigo-500"
                                                                />
                                                            </td>
                                                            <td className="px-6 py-4 font-medium text-neutral-900 dark:text-neutral-100">
                                                                {inv.reference_no}
                                                            </td>
                                                            <td className="px-6 py-4">{inv.due_date}</td>
                                                            <td className="px-6 py-4">
                                                                {new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(Number(inv.total_amount))}
                                                            </td>
                                                            <td className="px-6 py-4 text-red-600 dark:text-red-400">
                                                                {new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(balance)}
                                                            </td>
                                                            <td className="px-6 py-4 text-right">
                                                                <input
                                                                    type="number"
                                                                    step="0.01"
                                                                    disabled={!isChecked}
                                                                    value={allocationAmounts[inv.id] || ''}
                                                                    onChange={(e) => handleAllocationAmountChange(inv.id, e.target.value)}
                                                                    className="rounded-md border border-neutral-200 px-3 py-1 text-right text-sm text-neutral-900 focus:border-indigo-500 focus:outline-none disabled:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-50 dark:disabled:bg-neutral-900"
                                                                    placeholder="0.00"
                                                                />
                                                            </td>
                                                        </tr>
                                                    );
                                                })}
                                            </tbody>
                                        </table>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>

                    {/* Summary Sidebar */}
                    <div className="space-y-6">
                        <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm sticky top-6">
                            <CardHeader>
                                <CardTitle className="text-lg">Allocation Summary</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="flex justify-between text-sm">
                                    <span className="text-neutral-500">Total Payment Amount:</span>
                                    <span className="font-semibold text-neutral-900 dark:text-neutral-50">
                                        {new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(Number(data.amount || 0))}
                                    </span>
                                </div>
                                <div className="flex justify-between text-sm border-b border-neutral-100 dark:border-neutral-900 pb-2">
                                    <span className="text-neutral-500">Total Allocated:</span>
                                    <span className="font-semibold text-indigo-600 dark:text-indigo-400">
                                        {new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(totalAllocated)}
                                    </span>
                                </div>
                                <div className="flex justify-between text-sm pt-2">
                                    <span className="text-neutral-500 font-medium">Unallocated (Prepayment Credit):</span>
                                    <span className="font-semibold text-amber-600 dark:text-amber-400">
                                        {new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(unallocatedAmount)}
                                    </span>
                                </div>

                                <Button
                                    type="submit"
                                    className="w-full bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm mt-4"
                                    disabled={processing || !data.customer_id}
                                >
                                    <Save className="mr-2 h-4 w-4" />
                                    {processing ? 'Saving...' : 'Save Payment Draft'}
                                </Button>
                            </CardContent>
                        </Card>
                    </div>
                </form>
            </div>
        </>
    );
}
