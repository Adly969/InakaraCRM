import { Head, Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { usePermission } from '@/hooks/use-permission';
import type { Invoice, Customer } from '@/types';
import { Eye, Plus, FileText, Search } from 'lucide-react';
import React from 'react';

interface Props {
    invoices: {
        data: Invoice[];
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        current_page: number;
        last_page: number;
        total: number;
    };
    filters: {
        search?: string;
        status?: string;
        customer_id?: string;
    };
    customers: Customer[];
}

export default function InvoicesIndex({ invoices, filters, customers }: Props) {
    const { can } = usePermission();

    const handleSearch = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        const formData = new FormData(e.currentTarget);
        const search = formData.get('search') as string;
        const status = formData.get('status') as string;
        const customer_id = formData.get('customer_id') as string;

        router.get('/invoices', { search, status, customer_id }, { preserveState: true });
    };

    const getStatusBadgeClass = (status: string) => {
        switch (status) {
            case 'draft':
                return 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400';
            case 'approved':
                return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
            case 'issued':
                return 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400';
            case 'overdue':
                return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
            case 'void':
                return 'bg-neutral-100 text-neutral-800 dark:bg-neutral-900/30 dark:text-neutral-400';
            case 'cancelled':
                return 'bg-neutral-100 text-neutral-800 dark:bg-neutral-900/30 dark:text-neutral-400';
            default:
                return 'bg-neutral-100 text-neutral-800 dark:bg-neutral-900/30 dark:text-neutral-400';
        }
    };

    const getStatusLabel = (status: string) => {
        return status.replace(/_/g, ' ').replace(/\b\w/g, (char) => char.toUpperCase());
    };

    const formatCurrency = (val: number | string) => {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(Number(val));
    };

    return (
        <>
            <Head title="Invoices & Billing" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <div className="flex flex-col gap-1">
                        <div className="flex items-center gap-2">
                            <FileText className="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                            <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                                Invoices & Billing
                            </h1>
                        </div>
                        <p className="text-sm text-neutral-500 dark:text-neutral-400">
                            Create billing documents from confirmed delivery orders, track collections, and void invoices.
                        </p>
                    </div>
                    {can('create-invoices') && (
                        <Button asChild className="bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm">
                            <Link href="/invoices/create">
                                <Plus className="mr-2 h-4 w-4" />
                                Create Invoice
                            </Link>
                        </Button>
                    )}
                </div>

                <div className="flex items-center justify-between gap-4">
                    <form onSubmit={handleSearch} className="flex w-full flex-wrap items-center gap-3">
                        <div className="relative flex-1 min-w-[200px] max-w-xs">
                            <input
                                type="text"
                                name="search"
                                defaultValue={filters.search}
                                placeholder="Search Invoice reference..."
                                className="w-full rounded-md border border-neutral-200 bg-white px-3 py-2 pl-9 text-sm text-neutral-900 placeholder-neutral-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-50"
                            />
                            <Search className="absolute left-3 top-2.5 h-4 w-4 text-neutral-400" />
                        </div>

                        <select
                            name="status"
                            defaultValue={filters.status}
                            className="rounded-md border border-neutral-200 bg-white px-3 py-2 text-sm text-neutral-900 focus:border-indigo-500 focus:outline-none dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-50"
                        >
                            <option value="">All Statuses</option>
                            <option value="draft">Draft</option>
                            <option value="approved">Approved</option>
                            <option value="issued">Issued</option>
                            <option value="overdue">Overdue</option>
                            <option value="void">Void</option>
                        </select>

                        <select
                            name="customer_id"
                            defaultValue={filters.customer_id}
                            className="rounded-md border border-neutral-200 bg-white px-3 py-2 text-sm text-neutral-900 focus:border-indigo-500 focus:outline-none dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-50"
                        >
                            <option value="">All Customers</option>
                            {customers.map((cust) => (
                                <option key={cust.id} value={cust.id}>
                                    {cust.name}
                                </option>
                            ))}
                        </select>

                        <Button type="submit" size="sm" variant="secondary">
                            Apply Filters
                        </Button>
                    </form>
                </div>

                <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm overflow-hidden">
                    <CardContent className="p-0">
                        <div className="overflow-x-auto">
                            <table className="w-full border-collapse text-left text-sm">
                                <thead className="bg-neutral-50 dark:bg-neutral-900 text-xs font-semibold text-neutral-600 dark:text-neutral-400 border-b border-neutral-200 dark:border-neutral-800">
                                    <tr>
                                        <th className="px-6 py-3">Reference No</th>
                                        <th className="px-6 py-3">Customer</th>
                                        <th className="px-6 py-3">Invoice Date</th>
                                        <th className="px-6 py-3">Due Date</th>
                                        <th className="px-6 py-3">Total Amount</th>
                                        <th className="px-6 py-3">Outstanding Balance</th>
                                        <th className="px-6 py-3">Status</th>
                                        <th className="px-6 py-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-neutral-200 dark:divide-neutral-800 bg-white dark:bg-neutral-950">
                                    {invoices.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={8} className="px-6 py-10 text-center text-neutral-500">
                                                No invoices found.
                                            </td>
                                        </tr>
                                    ) : (
                                        invoices.data.map((inv) => (
                                            <tr key={inv.id} className="hover:bg-neutral-50/50 dark:hover:bg-neutral-900/30">
                                                <td className="px-6 py-4 font-medium text-neutral-900 dark:text-neutral-100">
                                                    {inv.reference_no || <span className="text-neutral-400 italic">Draft</span>}
                                                </td>
                                                <td className="px-6 py-4">{inv.customer?.name}</td>
                                                <td className="px-6 py-4">{inv.invoice_date}</td>
                                                <td className="px-6 py-4">{inv.due_date}</td>
                                                <td className="px-6 py-4">{formatCurrency(inv.total_amount)}</td>
                                                <td className="px-6 py-4">{formatCurrency(inv.outstanding_balance)}</td>
                                                <td className="px-6 py-4">
                                                    <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-medium bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 border border-neutral-200/60 dark:border-neutral-700/60">
                                                        <span className={`h-1.5 w-1.5 rounded-full ${
                                                            inv.status === 'paid' ? 'bg-emerald-500' :
                                                            inv.status === 'issued' || inv.status === 'approved' ? 'bg-sky-500' :
                                                            inv.status === 'draft' ? 'bg-amber-500' :
                                                            'bg-rose-500'
                                                        }`} />
                                                        <span className="capitalize">{getStatusLabel(inv.status)}</span>
                                                    </span>
                                                </td>
                                                <td className="px-6 py-4 text-right">
                                                    <Button asChild variant="ghost" size="icon" className="h-8 w-8 text-neutral-500 hover:text-indigo-600">
                                                        <Link href={`/invoices/${inv.id}`}>
                                                            <Eye className="h-4 w-4" />
                                                        </Link>
                                                    </Button>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>

                {invoices.links && invoices.links.length > 3 && (
                    <div className="flex justify-center gap-1">
                        {invoices.links.map((link, idx) => (
                            <Button
                                key={idx}
                                asChild
                                size="sm"
                                variant={link.active ? 'default' : 'outline'}
                                disabled={!link.url}
                                className={!link.url ? 'opacity-50 pointer-events-none' : ''}
                            >
                                <Link
                                    href={link.url || '#'}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            </Button>
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}
