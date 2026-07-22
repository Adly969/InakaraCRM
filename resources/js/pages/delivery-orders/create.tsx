import { Head, Link, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { Warehouse, Customer } from '@/types';
import { ArrowLeft, Save } from 'lucide-react';
import React, { useState } from 'react';

interface SalesOrderItem {
    id: number;
    sku: string;
    description: string;
    quantity: number | string;
    unit: string;
}

interface SalesOrder {
    id: number;
    reference_no: string;
    customer_id: number;
    customer?: Customer;
    items?: SalesOrderItem[];
    shipping_address?: string;
    billing_address?: string;
}

interface Props {
    salesOrders: SalesOrder[];
    warehouses: Warehouse[];
    customers: Customer[];
}

export default function CreateDeliveryOrder({ salesOrders, warehouses, customers }: Props) {
    const [selectedSo, setSelectedSo] = useState<SalesOrder | null>(null);

    const { data, setData, post, processing, errors } = useForm({
        sales_order_id: '',
        warehouse_id: '',
        customer_id: '',
        shipping_address: '',
        billing_address: '',
        notes: '',
        items: [] as Array<{
            sales_order_item_id: number;
            sku: string;
            description: string;
            quantity_requested: number;
            unit: string;
            sort_order: number;
        }>,
    });

    const handleSoChange = (soId: string) => {
        const so = salesOrders.find((s) => s.id === Number(soId)) || null;
        setSelectedSo(so);

        if (so) {
            const customer = so.customer || customers.find((c) => c.id === so.customer_id);
            setData((prev) => ({
                ...prev,
                sales_order_id: soId,
                customer_id: String(so.customer_id),
                shipping_address: so.shipping_address || customer?.shipping_address || '',
                billing_address: so.billing_address || customer?.billing_address || '',
                items: (so.items || []).map((item, idx) => ({
                    sales_order_item_id: item.id,
                    sku: item.sku || `ITEM-${item.id}`,
                    description: item.description,
                    quantity_requested: Number(item.quantity),
                    unit: item.unit || 'pcs',
                    sort_order: idx,
                })),
            }));
        } else {
            setData((prev) => ({
                ...prev,
                sales_order_id: '',
                customer_id: '',
                shipping_address: '',
                billing_address: '',
                items: [],
            }));
        }
    };

    const handleQtyChange = (idx: number, val: number) => {
        setData('items', data.items.map((item, i) => {
            if (i === idx) {
                return { ...item, quantity_requested: val };
            }
            return item;
        }));
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/delivery-orders');
    };

    return (
        <>
            <Head title="Create Delivery Order" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                <div className="flex items-center gap-4">
                    <Button asChild variant="outline" size="icon">
                        <Link href="/delivery-orders">
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <div className="flex flex-col gap-1">
                        <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                            Create Delivery Order
                        </h1>
                        <p className="text-sm text-neutral-500 dark:text-neutral-400">
                            Prepare split or full dispatch schedules from a confirmed sales order.
                        </p>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="flex flex-col gap-6">
                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm">
                        <CardHeader>
                            <CardTitle className="text-base font-semibold">General Information</CardTitle>
                        </CardHeader>
                        <CardContent className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label className="block text-xs font-semibold uppercase tracking-wider text-neutral-500 mb-1">
                                    Source Sales Order
                                </label>
                                <select
                                    value={data.sales_order_id}
                                    onChange={(e) => handleSoChange(e.target.value)}
                                    className="flex h-9 w-full rounded-md border border-neutral-200 bg-white px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-indigo-600 dark:border-neutral-800 dark:bg-neutral-950"
                                >
                                    <option value="">Select Sales Order</option>
                                    {salesOrders.map((so) => (
                                        <option key={so.id} value={so.id}>
                                            {so.reference_no} ({so.customer?.name})
                                        </option>
                                    ))}
                                </select>
                                {errors.sales_order_id && (
                                    <span className="text-xs text-red-500 mt-1 block">{errors.sales_order_id}</span>
                                )}
                            </div>

                            <div>
                                <label className="block text-xs font-semibold uppercase tracking-wider text-neutral-500 mb-1">
                                    Dispatch Warehouse
                                </label>
                                <select
                                    value={data.warehouse_id}
                                    onChange={(e) => setData('warehouse_id', e.target.value)}
                                    className="flex h-9 w-full rounded-md border border-neutral-200 bg-white px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-indigo-600 dark:border-neutral-800 dark:bg-neutral-950"
                                >
                                    <option value="">Select Warehouse</option>
                                    {warehouses.map((wh) => (
                                        <option key={wh.id} value={wh.id}>
                                            {wh.name}
                                        </option>
                                    ))}
                                </select>
                                {errors.warehouse_id && (
                                    <span className="text-xs text-red-500 mt-1 block">{errors.warehouse_id}</span>
                                )}
                            </div>

                            <div>
                                <label className="block text-xs font-semibold uppercase tracking-wider text-neutral-500 mb-1">
                                    Customer
                                </label>
                                <input
                                    type="text"
                                    disabled
                                    value={selectedSo?.customer?.name || ''}
                                    className="flex h-9 w-full rounded-md border border-neutral-200 bg-neutral-100 px-3 py-1 text-sm shadow-sm dark:border-neutral-800 dark:bg-neutral-900 text-neutral-500 cursor-not-allowed"
                                    placeholder="Customer automatically linked"
                                />
                            </div>

                            <div className="md:col-span-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-semibold uppercase tracking-wider text-neutral-500 mb-1">
                                        Shipping Address
                                    </label>
                                    <textarea
                                        value={data.shipping_address}
                                        onChange={(e) => setData('shipping_address', e.target.value)}
                                        rows={3}
                                        className="flex w-full rounded-md border border-neutral-200 bg-white px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-indigo-600 dark:border-neutral-800 dark:bg-neutral-950"
                                        placeholder="Enter customer shipping address..."
                                    />
                                    {errors.shipping_address && (
                                        <span className="text-xs text-red-500 mt-1 block">{errors.shipping_address}</span>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold uppercase tracking-wider text-neutral-500 mb-1">
                                        Billing Address
                                    </label>
                                    <textarea
                                        value={data.billing_address}
                                        onChange={(e) => setData('billing_address', e.target.value)}
                                        rows={3}
                                        className="flex w-full rounded-md border border-neutral-200 bg-white px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-indigo-600 dark:border-neutral-800 dark:bg-neutral-950"
                                        placeholder="Enter customer billing address..."
                                    />
                                    {errors.billing_address && (
                                        <span className="text-xs text-red-500 mt-1 block">{errors.billing_address}</span>
                                    )}
                                </div>
                            </div>

                            <div className="md:col-span-3">
                                <label className="block text-xs font-semibold uppercase tracking-wider text-neutral-500 mb-1">
                                    Notes
                                </label>
                                <input
                                    type="text"
                                    value={data.notes}
                                    onChange={(e) => setData('notes', e.target.value)}
                                    className="flex h-9 w-full rounded-md border border-neutral-200 bg-white px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-indigo-600 dark:border-neutral-800 dark:bg-neutral-950"
                                    placeholder="Enter additional dispatch remarks..."
                                />
                            </div>
                        </CardContent>
                    </Card>

                    {selectedSo && (
                        <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm">
                            <CardHeader>
                                <CardTitle className="text-base font-semibold">Line Items Outstanding Balance</CardTitle>
                            </CardHeader>
                            <CardContent className="p-0">
                                <table className="w-full text-sm">
                                    <thead className="bg-neutral-100/50 dark:bg-neutral-800/50 border-b border-neutral-200 dark:border-neutral-800">
                                        <tr>
                                            <th className="p-3 text-left">SKU</th>
                                            <th className="p-3 text-left">Description</th>
                                            <th className="p-3 text-center">Ordered Qty</th>
                                            <th className="p-3 text-center w-36">Delivery Qty</th>
                                            <th className="p-3 text-left">Unit</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-neutral-200 dark:divide-neutral-800">
                                        {data.items.map((item, idx) => {
                                            const originalItem = selectedSo.items?.find((i) => i.id === item.sales_order_item_id);
                                            return (
                                                <tr key={idx}>
                                                    <td className="p-3 font-medium text-neutral-900 dark:text-neutral-100">{item.sku}</td>
                                                    <td className="p-3 text-neutral-700 dark:text-neutral-300">{item.description}</td>
                                                    <td className="p-3 text-center font-semibold">{originalItem?.quantity}</td>
                                                    <td className="p-3">
                                                        <input
                                                            type="number"
                                                            step="0.01"
                                                            min="0.01"
                                                            max={originalItem?.quantity}
                                                            value={item.quantity_requested}
                                                            onChange={(e) => handleQtyChange(idx, Number(e.target.value))}
                                                            className="flex h-8 w-full rounded-md border border-neutral-200 bg-white px-2 py-1 text-sm text-center shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-indigo-600 dark:border-neutral-800 dark:bg-neutral-950"
                                                        />
                                                    </td>
                                                    <td className="p-3 text-neutral-500">{item.unit}</td>
                                                </tr>
                                            );
                                        })}
                                    </tbody>
                                </table>
                            </CardContent>
                        </Card>
                    )}

                    <div className="flex justify-end gap-3">
                        <Button asChild variant="outline">
                            <Link href="/delivery-orders">Cancel</Link>
                        </Button>
                        <Button type="submit" disabled={processing} className="bg-indigo-600 hover:bg-indigo-700 text-white">
                            <Save className="mr-2 h-4 w-4" />
                            Save as Draft
                        </Button>
                    </div>
                </form>
            </div>
        </>
    );
}
