import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import { PackageSearch, ArrowLeft, Layers, MapPin, FileText, ShieldCheck } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface Props {
    product: any;
}

export default function ProductShow({ product }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Master Data', href: '#' },
        { title: 'Products', href: '/master/products' },
        { title: product.sku, href: `/master/products/${product.id}` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Product ${product.sku}`} />

            <div className="flex flex-col space-y-6 p-6">
                <div className="flex items-center gap-3">
                    <Link href="/master/products">
                        <Button variant="outline" size="icon" className="h-9 w-9">
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-white flex items-center gap-2">
                            <PackageSearch className="h-6 w-6 text-sky-600" />
                            {product.name} ({product.sku})
                        </h1>
                        <p className="text-sm text-neutral-500">Barcode: {product.barcode || 'N/A'} | Classification: Class {product.abc_classification}</p>
                    </div>
                </div>

                {/* Stock Balances Across Warehouses */}
                <div className="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                    <h2 className="text-lg font-bold text-neutral-900 dark:text-white mb-4">Stock Balances Across Bins</h2>

                    {product.balances?.length === 0 ? (
                        <div className="py-6 text-center text-sm text-neutral-500">
                            No physical stock balances recorded for this item yet.
                        </div>
                    ) : (
                        <table className="w-full text-left text-sm text-neutral-600 dark:text-neutral-300">
                            <thead className="bg-neutral-50 dark:bg-neutral-950 text-xs uppercase text-neutral-500">
                                <tr>
                                    <th className="px-4 py-3">Warehouse</th>
                                    <th className="px-4 py-3">On Hand</th>
                                    <th className="px-4 py-3">Reserved</th>
                                    <th className="px-4 py-3">Available</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-neutral-200 dark:divide-neutral-800">
                                {product.balances?.map((b: any) => (
                                    <tr key={b.id}>
                                        <td className="px-4 py-3 font-semibold text-neutral-900 dark:text-white">{b.warehouse?.name}</td>
                                        <td className="px-4 py-3 font-mono">{b.quantity_on_hand}</td>
                                        <td className="px-4 py-3 font-mono text-amber-600">{b.quantity_reserved}</td>
                                        <td className="px-4 py-3 font-mono font-bold text-emerald-600">{b.quantity_available}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
