import { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import { PackageSearch, Plus, Search, Filter, Tag, FolderTree } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface Category {
    id: number;
    name: string;
}

interface Product {
    id: number;
    sku: string;
    barcode: string | null;
    name: string;
    product_type: string;
    category: Category | null;
    primaryUom: { id: number; code: string } | null;
    safety_stock: number;
    reorder_point: number;
    abc_classification: string;
}

interface Props {
    products: {
        data: Product[];
        links: any[];
    };
    categories: Category[];
    filters: {
        search?: string;
        category_id?: string;
    };
}

export default function ProductIndex({ products, categories, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Master Data', href: '#' },
        { title: 'Products', href: '/master/products' },
    ];

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get('/master/products', { search }, { preserveState: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Product Master Catalog" />

            <div className="flex flex-col space-y-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-white flex items-center gap-2">
                            <PackageSearch className="h-6 w-6 text-sky-600" />
                            Product Master Catalog
                        </h1>
                        <p className="text-sm text-neutral-500">Manage central furniture SKUs, barcodes, and product specifications.</p>
                    </div>

                    <Link href="/master/products/create">
                        <Button className="bg-sky-600 hover:bg-sky-700 text-white font-medium gap-2">
                            <Plus className="h-4 w-4" />
                            New Product SKU
                        </Button>
                    </Link>
                </div>

                {/* Filters Header */}
                <div className="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-900">
                    <form onSubmit={handleSearch} className="flex flex-col sm:flex-row gap-3">
                        <div className="relative flex-1">
                            <Search className="absolute left-3 top-2.5 h-4 w-4 text-neutral-400" />
                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Search by SKU, Product Name, or Barcode..."
                                className="w-full pl-9 pr-4 py-2 text-sm rounded-lg border border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-950 focus:outline-none focus:ring-2 focus:ring-sky-500"
                            />
                        </div>
                        <Button type="submit" variant="secondary">Search</Button>
                    </form>
                </div>

                {/* Product Data Table */}
                <div className="rounded-xl border border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-900 overflow-hidden shadow-xs">
                    <table className="w-full text-left text-sm text-neutral-600 dark:text-neutral-300">
                        <thead className="bg-neutral-50 dark:bg-neutral-950 border-b border-neutral-200 dark:border-neutral-800 text-xs font-semibold uppercase text-neutral-500">
                            <tr>
                                <th className="px-4 py-3">SKU / Barcode</th>
                                <th className="px-4 py-3">Product Name</th>
                                <th className="px-4 py-3">Category</th>
                                <th className="px-4 py-3">Type</th>
                                <th className="px-4 py-3">UOM</th>
                                <th className="px-4 py-3 text-center">ABC</th>
                                <th className="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-neutral-200 dark:divide-neutral-800">
                            {products.data.length === 0 ? (
                                <tr>
                                    <td colSpan={7} className="px-4 py-8 text-center text-neutral-400">
                                        No products found in catalog.
                                    </td>
                                </tr>
                            ) : (
                                products.data.map((product) => (
                                    <tr key={product.id} className="hover:bg-neutral-50/50 dark:hover:bg-neutral-900/50">
                                        <td className="px-4 py-3 font-mono font-semibold text-neutral-900 dark:text-white">
                                            {product.sku}
                                            {product.barcode && <span className="block text-[10px] text-neutral-400">{product.barcode}</span>}
                                        </td>
                                        <td className="px-4 py-3 font-medium text-neutral-900 dark:text-white">
                                            <Link href={`/master/products/${product.id}`} className="hover:text-sky-600">
                                                {product.name}
                                            </Link>
                                        </td>
                                        <td className="px-4 py-3">
                                            {product.category ? (
                                                <span className="inline-flex items-center gap-1 text-xs text-neutral-600 dark:text-neutral-300">
                                                    <FolderTree className="h-3 w-3 text-neutral-400" />
                                                    {product.category.name}
                                                </span>
                                            ) : '-'}
                                        </td>
                                        <td className="px-4 py-3 capitalize text-xs">
                                            {product.product_type.replace('_', ' ')}
                                        </td>
                                        <td className="px-4 py-3 font-mono text-xs">
                                            {product.primaryUom ? product.primaryUom.code : '-'}
                                        </td>
                                        <td className="px-4 py-3 text-center">
                                            <span className="px-2 py-0.5 text-xs font-bold rounded bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                                                Class {product.abc_classification}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            <Link href={`/master/products/${product.id}`}>
                                                <Button variant="ghost" size="sm">View Details</Button>
                                            </Link>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </AppLayout>
    );
}
