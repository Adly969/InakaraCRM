import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { usePermission } from '@/hooks/use-permission';
import { index as indexCustomersRoute, edit as editCustomerRoute } from '@/routes/customers';
import type { Customer } from '@/types';
import { ArrowLeft, Pencil } from 'lucide-react';

interface Props {
    customer: Customer;
}

export default function CustomerShow({ customer }: Props) {
    const { can } = usePermission();

    // Helper to format date
    const formatDate = (dateString?: string) => {
        if (!dateString) return '-';
        return new Date(dateString).toLocaleString('en-US', {
            dateStyle: 'medium',
            timeStyle: 'short',
        });
    };

    return (
        <>
            <Head title={`Customer Details - ${customer.name}`} />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 max-w-4xl mx-auto">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <Button variant="ghost" size="icon" asChild>
                            <Link href={indexCustomersRoute()}>
                                <ArrowLeft className="h-4 w-4" />
                            </Link>
                        </Button>
                        <div className="flex flex-col gap-0.5">
                            <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                                {customer.name}
                            </h1>
                            <p className="text-xs text-neutral-500">
                                {customer.reference_no ?? 'Reference Pending'}
                            </p>
                        </div>
                    </div>
                    {can('edit-customers') && (
                        <Button asChild variant="outline">
                            <Link href={editCustomerRoute(customer.id)}>
                                <Pencil className="mr-2 h-4 w-4" />
                                Edit Customer
                            </Link>
                        </Button>
                    )}
                </div>

                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Card className="md:col-span-2 border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-900/50">
                        <CardHeader className="border-b border-neutral-200 dark:border-neutral-800 pb-4">
                            <CardTitle className="text-base font-semibold">Customer Information</CardTitle>
                        </CardHeader>
                        <CardContent className="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div className="flex flex-col gap-1">
                                <span className="text-xs text-neutral-500">Company Name</span>
                                <span className="text-sm font-medium">{customer.company_name ?? '-'}</span>
                            </div>
                            <div className="flex flex-col gap-1">
                                <span className="text-xs text-neutral-500">Email Address</span>
                                <span className="text-sm font-medium">{customer.email ?? '-'}</span>
                            </div>
                            <div className="flex flex-col gap-1">
                                <span className="text-xs text-neutral-500">Phone Number</span>
                                <span className="text-sm font-medium">{customer.phone ?? '-'}</span>
                            </div>
                            <div className="flex flex-col gap-1">
                                <span className="text-xs text-neutral-500">Customer Type</span>
                                <span className="text-sm font-medium capitalize">{customer.type}</span>
                            </div>
                            <div className="flex flex-col gap-1">
                                <span className="text-xs text-neutral-500">Current Status</span>
                                <div>
                                    <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset ${
                                        customer.status === 'active' ? 'bg-green-50 text-green-700 ring-green-600/20 dark:bg-green-900/30 dark:text-green-400' :
                                        customer.status === 'inactive' ? 'bg-neutral-50 text-neutral-600 ring-neutral-500/20 dark:bg-neutral-800 dark:text-neutral-400' :
                                        'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-900/30 dark:text-red-400'
                                    }`}>
                                        {customer.status}
                                    </span>
                                </div>
                            </div>
                            <div className="flex flex-col gap-1">
                                <span className="text-xs text-neutral-500">Assigned To</span>
                                <span className="text-sm font-medium">
                                    {customer.assigned_to_user?.name ?? '-'}
                                </span>
                            </div>

                            {customer.notes && (
                                <div className="sm:col-span-2 flex flex-col gap-1 p-3 rounded-lg bg-neutral-100/50 dark:bg-neutral-800/20 border border-neutral-200/30">
                                    <span className="text-xs font-semibold text-neutral-500">Notes / Context</span>
                                    <span className="text-sm text-neutral-900 dark:text-neutral-200 whitespace-pre-wrap">{customer.notes}</span>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    <div className="flex flex-col gap-6">
                        <Card className="border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-900/50">
                            <CardHeader className="border-b border-neutral-200 dark:border-neutral-800 pb-4">
                                <CardTitle className="text-base font-semibold">Audit Log</CardTitle>
                            </CardHeader>
                            <CardContent className="p-4 flex flex-col gap-3 text-xs text-neutral-600 dark:text-neutral-400">
                                <div className="flex flex-col gap-0.5">
                                    <span className="font-semibold">Created By</span>
                                    <span>{customer.creator?.name ?? 'System'}</span>
                                </div>
                                <div className="flex flex-col gap-0.5">
                                    <span className="font-semibold">Created At</span>
                                    <span>{formatDate(customer.created_at)}</span>
                                </div>
                                <div className="flex flex-col gap-0.5">
                                    <span className="font-semibold">Last Updated By</span>
                                    <span>{customer.updater?.name ?? 'System'}</span>
                                </div>
                                <div className="flex flex-col gap-0.5">
                                    <span className="font-semibold">Last Updated At</span>
                                    <span>{formatDate(customer.updated_at)}</span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </>
    );
}

CustomerShow.layout = {
    breadcrumbs: [
        {
            title: 'Customers',
            href: indexCustomersRoute(),
        },
        {
            title: 'Details',
        },
    ],
};
