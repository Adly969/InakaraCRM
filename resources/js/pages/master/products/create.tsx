import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import { PackageSearch, ArrowLeft, Save } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface Category {
    id: number;
    name: string;
}

interface Brand {
    id: number;
    name: string;
}

interface Unit {
    id: number;
    code: string;
    name: string;
}

interface Props {
    categories: Category[];
    brands: Brand[];
    units: Unit[];
}

export default function ProductCreate({ categories, brands, units }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Master Data', href: '#' },
        { title: 'Products', href: '/master/products' },
        { title: 'New Product SKU', href: '/master/products/create' },
    ];

    const { data, setData, post, processing, errors } = useForm({
        name: '',
        sku: '',
        barcode: '',
        product_type: 'finished_goods',
        category_id: '',
        brand_id: '',
        primary_uom_id: '',
        safety_stock: '0',
        reorder_point: '0',
        lead_time_days: '0',
        abc_classification: 'C',
        is_batch_tracked: false,
        is_serial_tracked: false,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/master/products');
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create New Product SKU" />

            <div className="flex flex-col space-y-6 p-6 max-w-4xl">
                <div className="flex items-center gap-3">
                    <Link href="/master/products">
                        <Button variant="outline" size="icon" className="h-9 w-9">
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-white flex items-center gap-2">
                            <PackageSearch className="h-6 w-6 text-sky-600" />
                            Create New Product SKU
                        </h1>
                        <p className="text-sm text-neutral-500">Add a new furniture item to the enterprise product master catalog.</p>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900 space-y-4">
                        <h2 className="text-lg font-bold text-neutral-900 dark:text-white">Basic Identification</h2>

                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label className="block text-xs font-semibold uppercase text-neutral-500 mb-1">Product Name *</label>
                                <input
                                    type="text"
                                    required
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    placeholder="e.g. Teak Dining Chair Natural Finish"
                                    className="w-full px-3 py-2 text-sm rounded-lg border border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-950 focus:ring-2 focus:ring-sky-500"
                                />
                                {errors.name && <span className="text-xs text-rose-500">{errors.name}</span>}
                            </div>

                            <div>
                                <label className="block text-xs font-semibold uppercase text-neutral-500 mb-1">SKU (Auto-generated if empty)</label>
                                <input
                                    type="text"
                                    value={data.sku}
                                    onChange={(e) => setData('sku', e.target.value)}
                                    placeholder="e.g. CHR-TEAK-001"
                                    className="w-full px-3 py-2 text-sm font-mono rounded-lg border border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-950 focus:ring-2 focus:ring-sky-500"
                                />
                            </div>
                        </div>

                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <label className="block text-xs font-semibold uppercase text-neutral-500 mb-1">Barcode (EAN-13 / QR)</label>
                                <input
                                    type="text"
                                    value={data.barcode}
                                    onChange={(e) => setData('barcode', e.target.value)}
                                    placeholder="e.g. 8991234567890"
                                    className="w-full px-3 py-2 text-sm font-mono rounded-lg border border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-950 focus:ring-2 focus:ring-sky-500"
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-semibold uppercase text-neutral-500 mb-1">Product Type *</label>
                                <select
                                    value={data.product_type}
                                    onChange={(e) => setData('product_type', e.target.value)}
                                    className="w-full px-3 py-2 text-sm rounded-lg border border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-950"
                                >
                                    <option value="finished_goods">Finished Goods</option>
                                    <option value="raw_material">Raw Material</option>
                                    <option value="semi_finished">Semi-Finished Goods</option>
                                    <option value="service">Service</option>
                                    <option value="bundle">Bundle</option>
                                </select>
                            </div>

                            <div>
                                <label className="block text-xs font-semibold uppercase text-neutral-500 mb-1">Primary Unit (UOM)</label>
                                <select
                                    value={data.primary_uom_id}
                                    onChange={(e) => setData('primary_uom_id', e.target.value)}
                                    className="w-full px-3 py-2 text-sm rounded-lg border border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-950"
                                >
                                    <option value="">Select UOM...</option>
                                    {units.map((u) => (
                                        <option key={u.id} value={u.id}>{u.name} ({u.code})</option>
                                    ))}
                                </select>
                            </div>
                        </div>
                    </div>

                    <div className="flex justify-end gap-3">
                        <Link href="/master/products">
                            <Button variant="outline" type="button">Cancel</Button>
                        </Link>
                        <Button type="submit" disabled={processing} className="bg-sky-600 hover:bg-sky-700 text-white gap-2">
                            <Save className="h-4 w-4" />
                            Save Product SKU
                        </Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
