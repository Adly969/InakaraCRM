import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { usePermission } from '@/hooks/use-permission';
import { index as indexCustomersRoute, create as createCustomerRoute, show as showCustomerRoute, edit as editCustomerRoute, destroy as destroyCustomerRoute } from '@/routes/customers';
import type { Customer } from '@/types';
import { Plus, Eye, Pencil, Trash2 } from 'lucide-react';
import { router } from '@inertiajs/react';

interface Props {
    customers: {
        data: Customer[];
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        current_page: number;
        last_page: number;
        total: number;
    };
}

export default function CustomersIndex({ customers }: Props) {
    const { can } = usePermission();

    const handleDelete = (customer: Customer) => {
        if (confirm(`Are you sure you want to delete customer ${customer.name}?`)) {
            router.delete(destroyCustomerRoute(customer.id).url);
        }
    };

    return (
        <>
            <Head title="Customers" />
            <div className="flex h-full flex-1 flex-col gap-4 p-6">
                <div className="flex items-center justify-between">
                    <div className="flex flex-col gap-1">
                        <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                            Customers
                        </h1>
                        <p className="text-sm text-neutral-500 dark:text-neutral-400">
                            Manage customer profiles, details, and assignments.
                        </p>
                    </div>
                    {can('create-customers') && (
                        <Button asChild>
                            <Link href={createCustomerRoute()}>
                                <Plus className="mr-2 h-4 w-4" />
                                Create Customer
                            </Link>
                        </Button>
                    )}
                </div>

                <Card className="flex-1 overflow-hidden border border-neutral-200 dark:border-neutral-800 shadow-sm rounded-xl bg-white dark:bg-neutral-900">
                    <CardContent className="p-0">
                        <div className="relative w-full overflow-auto">
                            <table className="w-full caption-bottom text-sm border-collapse">
                                <thead className="border-b border-neutral-200 dark:border-neutral-800 bg-neutral-100/90 dark:bg-neutral-800/90">
                                    <tr className="hover:bg-transparent">
                                        <th className="py-3.5 px-4 text-left align-middle text-[11px] font-bold text-neutral-600 dark:text-neutral-300 uppercase tracking-wider border-r border-neutral-200/40 dark:border-neutral-700/40">No. Referensi</th>
                                        <th className="py-3.5 px-4 text-left align-middle text-[11px] font-bold text-neutral-600 dark:text-neutral-300 uppercase tracking-wider border-r border-neutral-200/40 dark:border-neutral-700/40">Nama Pelanggan</th>
                                        <th className="py-3.5 px-4 text-left align-middle text-[11px] font-bold text-neutral-600 dark:text-neutral-300 uppercase tracking-wider border-r border-neutral-200/40 dark:border-neutral-700/40">Perusahaan / Entitas</th>
                                        <th className="py-3.5 px-4 text-left align-middle text-[11px] font-bold text-neutral-600 dark:text-neutral-300 uppercase tracking-wider border-r border-neutral-200/40 dark:border-neutral-700/40">Kategori</th>
                                        <th className="py-3.5 px-4 text-left align-middle text-[11px] font-bold text-neutral-600 dark:text-neutral-300 uppercase tracking-wider border-r border-neutral-200/40 dark:border-neutral-700/40">Status</th>
                                        <th className="py-3.5 px-4 text-left align-middle text-[11px] font-bold text-neutral-600 dark:text-neutral-300 uppercase tracking-wider border-r border-neutral-200/40 dark:border-neutral-700/40">Account Manager</th>
                                        <th className="py-3.5 px-4 text-right align-middle text-[11px] font-bold text-neutral-600 dark:text-neutral-300 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-neutral-200/80 dark:divide-neutral-800/80">
                                    {customers.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={7} className="h-24 text-center text-neutral-500 dark:text-neutral-400">
                                                Tidak ada data pelanggan yang ditemukan.
                                            </td>
                                        </tr>
                                    ) : (
                                        customers.data.map((customer) => (
                                            <tr key={customer.id} className="hover:bg-neutral-50 dark:hover:bg-neutral-850/60 transition-colors group">
                                                <td className="py-3.5 px-4 align-middle">
                                                    <span className="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-bold bg-neutral-100 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200 border border-neutral-300/80 dark:border-neutral-700">
                                                        {customer.reference_no ?? `CUST-${customer.id}`}
                                                    </span>
                                                </td>
                                                <td className="py-3.5 px-4 align-middle">
                                                    <div className="flex items-center gap-3">
                                                        <div className="w-8 h-8 rounded-full bg-neutral-200 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200 flex items-center justify-center font-bold text-xs shrink-0 border border-neutral-300/60 dark:border-neutral-700">
                                                            {customer.name.substring(0, 2).toUpperCase()}
                                                        </div>
                                                        <div>
                                                            <div className="font-semibold text-neutral-900 dark:text-neutral-100 text-xs">{customer.name}</div>
                                                            <div className="text-[11px] text-neutral-500 dark:text-neutral-400">{customer.email || customer.phone || '-'}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="py-3.5 px-4 align-middle">
                                                    <span className="font-medium text-neutral-800 dark:text-neutral-200 text-xs">{customer.company_name ?? '-'}</span>
                                                </td>
                                                <td className="py-3.5 px-4 align-middle">
                                                     <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-neutral-100 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200 border border-neutral-200 dark:border-neutral-700">
                                                         <span className={`h-1.5 w-1.5 rounded-full ${
                                                             customer.type === 'company' || customer.type === 'organization' ? 'bg-sky-500' : 'bg-amber-500'
                                                         }`} />
                                                         <span className="capitalize">{customer.type}</span>
                                                     </span>
                                                </td>
                                                <td className="py-3.5 px-4 align-middle">
                                                     <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-neutral-100 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200 border border-neutral-200 dark:border-neutral-700">
                                                         <span className={`h-1.5 w-1.5 rounded-full ${
                                                             customer.status === 'active' ? 'bg-emerald-500' : 'bg-rose-500'
                                                         }`} />
                                                         <span className="capitalize">{customer.status}</span>
                                                     </span>
                                                </td>
                                                <td className="py-3.5 px-4 align-middle text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                                    {customer.assigned_to_user?.name ?? 'Adly Pratama'}
                                                </td>
                                                <td className="py-3.5 px-4 align-middle text-right">
                                                    <div className="flex items-center justify-end gap-1">
                                                        <Button variant="ghost" size="icon" asChild className="h-8 w-8 text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-neutral-100 hover:bg-neutral-200/60 dark:hover:bg-neutral-800">
                                                            <Link href={showCustomerRoute(customer.id)}>
                                                                 <Eye className="h-4 w-4" />
                                                            </Link>
                                                        </Button>
                                                        {can('edit-customers') && (
                                                            <Button variant="ghost" size="icon" asChild className="h-8 w-8 text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-neutral-100 hover:bg-neutral-200/60 dark:hover:bg-neutral-800">
                                                                <Link href={editCustomerRoute(customer.id)}>
                                                                    <Pencil className="h-4 w-4" />
                                                                </Link>
                                                            </Button>
                                                        )}
                                                        {can('delete-customers') && (
                                                            <Button variant="ghost" size="icon" onClick={() => handleDelete(customer)} className="h-8 w-8 text-rose-600 hover:text-rose-700 hover:bg-rose-100/60 dark:hover:bg-rose-950/40">
                                                                <Trash2 className="h-4 w-4" />
                                                            </Button>
                                                        )}
                                                    </div>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                        
                        {customers.links && customers.links.length > 3 && (
                            <div className="flex items-center justify-between border-t border-neutral-200 p-4 dark:border-neutral-800">
                                <div className="text-xs text-neutral-500">
                                    Showing page {customers.current_page} of {customers.last_page} ({customers.total} total customers)
                                </div>
                                <div className="flex items-center gap-1">
                                    {customers.links.map((link, idx) => {
                                        const cleanLabel = link.label
                                            .replace('&laquo;', '‹')
                                            .replace('&raquo;', '›')
                                            .replace('Previous', '‹')
                                            .replace('Next', '›');

                                        if (!link.url) {
                                            return (
                                                <Button key={idx} variant="outline" size="sm" disabled className="px-2 text-xs">
                                                    {cleanLabel}
                                                </Button>
                                            );
                                        }

                                        return (
                                            <Button key={idx} variant={link.active ? 'default' : 'outline'} size="sm" asChild className="px-2 text-xs">
                                                <Link href={link.url}>
                                                    {cleanLabel}
                                                </Link>
                                            </Button>
                                        );
                                    })}
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

CustomersIndex.layout = {
    breadcrumbs: [
        {
            title: 'Customers',
            href: indexCustomersRoute(),
        },
    ],
};
