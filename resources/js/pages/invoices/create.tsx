import { Head, Link, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { Customer } from '@/types';
import { ArrowLeft, Save, Plus, Trash2 } from 'lucide-react';
import React, { useState, useEffect } from 'react';

interface SalesOrderItem {
    id: number;
    sku: string;
    description: string;
    quantity: number | string;
    unit: string;
    unit_price: number | string;
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

interface DeliveryOrderItem {
    id: number;
    sku: string;
    description: string;
    quantity_requested: number | string;
    unit: string;
    sales_order_item?: { unit_price: number | string };
}

interface DeliveryOrder {
    id: number;
    reference_no: string;
    customer_id: number;
    customer?: Customer;
    items?: DeliveryOrderItem[];
    sales_order_id: number;
    shipping_address_snapshot?: { address: string };
    billing_address_snapshot?: { address: string };
}

interface Props {
    salesOrders: SalesOrder[];
    deliveryOrders: DeliveryOrder[];
    customers: Customer[];
}

export default function CreateInvoice({ salesOrders, deliveryOrders, customers }: Props) {
    const [sourceType, setSourceType] = useState<'delivery_order' | 'sales_order'>('delivery_order');
    const [selectedDoc, setSelectedDoc] = useState<any>(null);

    const { data, setData, post, processing, errors } = useForm({
        sales_order_id: '',
        delivery_order_id: '',
        customer_id: '',
        invoice_date: new Date().toISOString().split('T')[0],
        due_date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0], // default 30 days
        payment_term_code: 'NET30',
        currency: 'IDR',
        exchange_rate: 1.000000,
        billing_address: '',
        shipping_address: '',
        notes: '',
        items: [] as Array<{
            sales_order_item_id: number | null;
            delivery_order_item_id: number | null;
            sku: string;
            description: string;
            quantity: number;
            unit: string;
            unit_price: number;
            discount_percentage: number;
            tax_percentage: number;
            sort_order: number;
        }>,
        adjustments: [] as Array<{
            type: string;
            description: string;
            amount: number;
            is_taxable: boolean;
        }>,
    });

    const [totals, setTotals] = useState({
        subtotal: 0,
        discount_amount: 0,
        tax_amount: 0,
        adjustment_amount: 0,
        total_amount: 0,
    });

    // Run local math calculation matching Laravel InvoiceCalculationService
    useEffect(() => {
        let subtotal = 0;
        let discount_amount = 0;
        let tax_amount = 0;

        data.items.forEach((item) => {
            const itemSub = item.quantity * item.unit_price;
            const itemDisc = Math.round(((itemSub * item.discount_percentage) / 100) * 100) / 100;
            const discounted = itemSub - itemDisc;
            const itemTax = Math.round(((discounted * item.tax_percentage) / 100) * 100) / 100;

            subtotal += itemSub;
            discount_amount += itemDisc;
            tax_amount += itemTax;
        });

        let adjustment_amount = 0;
        data.adjustments.forEach((adj) => {
            adjustment_amount += adj.amount;
        });

        const total_amount = Math.max(0, (subtotal - discount_amount) + tax_amount + adjustment_amount);

        setTotals({
            subtotal,
            discount_amount,
            tax_amount,
            adjustment_amount,
            total_amount,
        });
    }, [data.items, data.adjustments]);

    const handleSourceTypeChange = (type: 'delivery_order' | 'sales_order') => {
        setSourceType(type);
        setSelectedDoc(null);
        setData((prev) => ({
            ...prev,
            sales_order_id: '',
            delivery_order_id: '',
            customer_id: '',
            billing_address: '',
            shipping_address: '',
            items: [],
            adjustments: [],
        }));
    };

    const handleDocChange = (docId: string) => {
        if (!docId) {
            setSelectedDoc(null);
            setData((prev) => ({
                ...prev,
                sales_order_id: '',
                delivery_order_id: '',
                customer_id: '',
                billing_address: '',
                shipping_address: '',
                items: [],
            }));
            return;
        }

        if (sourceType === 'delivery_order') {
            const doDoc = deliveryOrders.find((d) => d.id === Number(docId)) || null;
            setSelectedDoc(doDoc);
            if (doDoc) {
                const customer = doDoc.customer || customers.find((c) => c.id === doDoc.customer_id);
                setData((prev) => ({
                    ...prev,
                    delivery_order_id: docId,
                    sales_order_id: String(doDoc.sales_order_id),
                    customer_id: String(doDoc.customer_id),
                    shipping_address: doDoc.shipping_address_snapshot?.address || customer?.shipping_address || '',
                    billing_address: doDoc.billing_address_snapshot?.address || customer?.billing_address || '',
                    items: (doDoc.items || []).map((item, idx) => ({
                        sales_order_item_id: null,
                        delivery_order_item_id: item.id,
                        sku: item.sku,
                        description: item.description,
                        quantity: Number(item.quantity_requested),
                        unit: item.unit || 'pcs',
                        unit_price: Number(item.sales_order_item?.unit_price || 0.00),
                        discount_percentage: 0,
                        tax_percentage: 11, // default VAT 11%
                        sort_order: idx,
                    })),
                }));
            }
        } else {
            const soDoc = salesOrders.find((s) => s.id === Number(docId)) || null;
            setSelectedDoc(soDoc);
            if (soDoc) {
                const customer = soDoc.customer || customers.find((c) => c.id === soDoc.customer_id);
                setData((prev) => ({
                    ...prev,
                    delivery_order_id: '',
                    sales_order_id: docId,
                    customer_id: String(soDoc.customer_id),
                    shipping_address: soDoc.shipping_address || customer?.shipping_address || '',
                    billing_address: soDoc.billing_address || customer?.billing_address || '',
                    items: (soDoc.items || []).map((item, idx) => ({
                        sales_order_item_id: item.id,
                        delivery_order_item_id: null,
                        sku: item.sku,
                        description: item.description,
                        quantity: Number(item.quantity),
                        unit: item.unit || 'pcs',
                        unit_price: Number(item.unit_price),
                        discount_percentage: 0,
                        tax_percentage: 11, // default VAT 11%
                        sort_order: idx,
                    })),
                }));
            }
        }
    };

    const handleItemChange = (idx: number, key: string, val: any) => {
        setData('items', data.items.map((item, i) => {
            if (i === idx) {
                return { ...item, [key]: val };
            }
            return item;
        }));
    };

    const addAdjustment = () => {
        setData('adjustments', [
            ...data.adjustments,
            { type: 'shipping_fee', description: 'Adjustment Fee', amount: 0, is_taxable: false }
        ]);
    };

    const removeAdjustment = (idx: number) => {
        setData('adjustments', data.adjustments.filter((_, i) => i !== idx));
    };

    const handleAdjustmentChange = (idx: number, key: string, val: any) => {
        setData('adjustments', data.adjustments.map((adj, i) => {
            if (i === idx) {
                return { ...adj, [key]: val };
            }
            return adj;
        }));
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/invoices');
    };

    const formatCurrency = (val: number) => {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
    };

    return (
        <>
            <Head title="Create Invoice" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 max-w-5xl mx-auto">
                <div className="flex items-center gap-4">
                    <Button asChild variant="outline" size="icon" className="h-8 w-8">
                        <Link href="/invoices">
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <div className="flex flex-col">
                        <h1 className="text-xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                            Create Invoice
                        </h1>
                        <p className="text-xs text-neutral-500">
                            Generate draft invoice billing document.
                        </p>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="flex flex-col gap-6">
                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm">
                        <CardHeader>
                            <CardTitle className="text-base font-semibold">1. Billing Reference Document</CardTitle>
                        </CardHeader>
                        <CardContent className="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div className="flex flex-col gap-2">
                                <label className="text-xs font-semibold text-neutral-500">Source Type</label>
                                <div className="flex gap-2">
                                    <Button
                                        type="button"
                                        variant={sourceType === 'delivery_order' ? 'default' : 'outline'}
                                        onClick={() => handleSourceTypeChange('delivery_order')}
                                        className="flex-1"
                                    >
                                        Delivery Order (DO)
                                    </Button>
                                    <Button
                                        type="button"
                                        variant={sourceType === 'sales_order' ? 'default' : 'outline'}
                                        onClick={() => handleSourceTypeChange('sales_order')}
                                        className="flex-1"
                                    >
                                        Sales Order (SO)
                                    </Button>
                                </div>
                            </div>

                            <div className="flex flex-col gap-2 md:col-span-2">
                                <label className="text-xs font-semibold text-neutral-500">
                                    Select {sourceType === 'delivery_order' ? 'Delivery Order' : 'Sales Order'}
                                </label>
                                <select
                                    value={sourceType === 'delivery_order' ? data.delivery_order_id : data.sales_order_id}
                                    onChange={(e) => handleDocChange(e.target.value)}
                                    className="rounded-md border border-neutral-200 bg-white px-3 py-2 text-sm text-neutral-900 focus:border-indigo-500 focus:outline-none dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-50 w-full"
                                >
                                    <option value="">-- Choose reference document --</option>
                                    {sourceType === 'delivery_order'
                                        ? deliveryOrders.map((d) => (
                                              <option key={d.id} value={d.id}>
                                                  {d.reference_no} ({d.customer?.name})
                                              </option>
                                          ))
                                        : salesOrders.map((s) => (
                                              <option key={s.id} value={s.id}>
                                                  {s.reference_no} ({s.customer?.name})
                                              </option>
                                          ))}
                                </select>
                                {errors.customer_id && <div className="text-xs text-red-500 font-semibold">{errors.customer_id}</div>}
                            </div>
                        </CardContent>
                    </Card>

                    {selectedDoc && (
                        <>
                            <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm">
                                <CardHeader>
                                    <CardTitle className="text-base font-semibold">2. Invoice Metadata & Settings</CardTitle>
                                </CardHeader>
                                <CardContent className="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div className="flex flex-col gap-1.5">
                                        <label className="text-xs font-medium">Invoice Date</label>
                                        <input
                                            type="date"
                                            value={data.invoice_date}
                                            onChange={(e) => setData('invoice_date', e.target.value)}
                                            className="rounded-md border border-neutral-200 px-3 py-2 text-sm dark:border-neutral-800 dark:bg-neutral-950"
                                        />
                                        {errors.invoice_date && <div className="text-xs text-red-500">{errors.invoice_date}</div>}
                                    </div>
                                    <div className="flex flex-col gap-1.5">
                                        <label className="text-xs font-medium">Due Date</label>
                                        <input
                                            type="date"
                                            value={data.due_date}
                                            onChange={(e) => setData('due_date', e.target.value)}
                                            className="rounded-md border border-neutral-200 px-3 py-2 text-sm dark:border-neutral-800 dark:bg-neutral-950"
                                        />
                                        {errors.due_date && <div className="text-xs text-red-500">{errors.due_date}</div>}
                                    </div>
                                    <div className="flex flex-col gap-1.5">
                                        <label className="text-xs font-medium">Payment Term</label>
                                        <select
                                            value={data.payment_term_code}
                                            onChange={(e) => setData('payment_term_code', e.target.value)}
                                            className="rounded-md border border-neutral-200 px-3 py-2 text-sm dark:border-neutral-800 dark:bg-neutral-950"
                                        >
                                            <option value="NET14">14 Days (NET14)</option>
                                            <option value="NET30">30 Days (NET30)</option>
                                            <option value="NET60">60 Days (NET60)</option>
                                            <option value="COD">Cash on Delivery (COD)</option>
                                        </select>
                                        {errors.payment_term_code && <div className="text-xs text-red-500">{errors.payment_term_code}</div>}
                                    </div>

                                    <div className="flex flex-col gap-1.5 md:col-span-1.5">
                                        <label className="text-xs font-medium">Billing Address Snapshot</label>
                                        <textarea
                                            value={data.billing_address}
                                            onChange={(e) => setData('billing_address', e.target.value)}
                                            rows={3}
                                            className="rounded-md border border-neutral-200 px-3 py-2 text-sm dark:border-neutral-800 dark:bg-neutral-950"
                                        />
                                        {errors.billing_address && <div className="text-xs text-red-500">{errors.billing_address}</div>}
                                    </div>

                                    <div className="flex flex-col gap-1.5 md:col-span-1.5">
                                        <label className="text-xs font-medium">Shipping Address Snapshot</label>
                                        <textarea
                                            value={data.shipping_address}
                                            onChange={(e) => setData('shipping_address', e.target.value)}
                                            rows={3}
                                            className="rounded-md border border-neutral-200 px-3 py-2 text-sm dark:border-neutral-800 dark:bg-neutral-950"
                                        />
                                        {errors.shipping_address && <div className="text-xs text-red-500">{errors.shipping_address}</div>}
                                    </div>
                                </CardContent>
                            </Card>

                            <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm overflow-hidden">
                                <CardHeader>
                                    <CardTitle className="text-base font-semibold">3. Line Items calculations</CardTitle>
                                </CardHeader>
                                <CardContent className="p-0">
                                    <div className="overflow-x-auto">
                                        <table className="w-full text-sm text-left">
                                            <thead className="bg-neutral-50 dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-800 text-xs font-semibold text-neutral-600 dark:text-neutral-400">
                                                <tr>
                                                    <th className="px-6 py-3">SKU</th>
                                                    <th className="px-6 py-3">Description</th>
                                                    <th className="px-6 py-3 w-[100px]">Qty</th>
                                                    <th className="px-6 py-3 w-[150px]">Unit Price</th>
                                                    <th className="px-6 py-3 w-[100px]">Disc (%)</th>
                                                    <th className="px-6 py-3 w-[100px]">Tax (%)</th>
                                                    <th className="px-6 py-3 text-right">Line Total</th>
                                                </tr>
                                            </thead>
                                            <tbody className="divide-y divide-neutral-200 dark:divide-neutral-800">
                                                {data.items.map((item, idx) => {
                                                    const lineSub = item.quantity * item.unit_price;
                                                    const disc = (lineSub * item.discount_percentage) / 100;
                                                    const tax = ((lineSub - disc) * item.tax_percentage) / 100;
                                                    const lineTotal = lineSub - disc + tax;

                                                    return (
                                                        <tr key={idx}>
                                                            <td className="px-6 py-4 font-semibold">{item.sku}</td>
                                                            <td className="px-6 py-4">{item.description}</td>
                                                            <td className="px-6 py-4">
                                                                <input
                                                                    type="number"
                                                                    value={item.quantity}
                                                                    onChange={(e) => handleItemChange(idx, 'quantity', Number(e.target.value))}
                                                                    className="w-full rounded border px-2 py-1 text-sm dark:bg-neutral-950"
                                                                />
                                                            </td>
                                                            <td className="px-6 py-4">
                                                                <input
                                                                    type="number"
                                                                    value={item.unit_price}
                                                                    onChange={(e) => handleItemChange(idx, 'unit_price', Number(e.target.value))}
                                                                    className="w-full rounded border px-2 py-1 text-sm dark:bg-neutral-950"
                                                                />
                                                            </td>
                                                            <td className="px-6 py-4">
                                                                <input
                                                                    type="number"
                                                                    value={item.discount_percentage}
                                                                    onChange={(e) => handleItemChange(idx, 'discount_percentage', Number(e.target.value))}
                                                                    className="w-full rounded border px-2 py-1 text-sm dark:bg-neutral-950"
                                                                />
                                                            </td>
                                                            <td className="px-6 py-4">
                                                                <input
                                                                    type="number"
                                                                    value={item.tax_percentage}
                                                                    onChange={(e) => handleItemChange(idx, 'tax_percentage', Number(e.target.value))}
                                                                    className="w-full rounded border px-2 py-1 text-sm dark:bg-neutral-950"
                                                                />
                                                            </td>
                                                            <td className="px-6 py-4 text-right font-semibold">
                                                                {formatCurrency(lineTotal)}
                                                            </td>
                                                        </tr>
                                                    );
                                                })}
                                            </tbody>
                                        </table>
                                    </div>
                                    {errors.items && <div className="text-xs text-red-500 font-semibold p-4">{errors.items}</div>}
                                </CardContent>
                            </Card>

                            <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm">
                                <CardHeader className="flex flex-row items-center justify-between">
                                    <CardTitle className="text-base font-semibold">4. Manual Adjustments</CardTitle>
                                    <Button type="button" onClick={addAdjustment} size="sm" variant="outline">
                                        <Plus className="mr-1 h-3 w-3" />
                                        Add Adjustment
                                    </Button>
                                </CardHeader>
                                <CardContent className="flex flex-col gap-4">
                                    {data.adjustments.map((adj, idx) => (
                                        <div key={idx} className="flex gap-4 items-end">
                                            <div className="flex-1">
                                                <label className="text-2xs font-semibold text-neutral-400">Type</label>
                                                <select
                                                    value={adj.type}
                                                    onChange={(e) => handleAdjustmentChange(idx, 'type', e.target.value)}
                                                    className="w-full rounded-md border border-neutral-200 px-3 py-1.5 text-sm dark:bg-neutral-950"
                                                >
                                                    <option value="shipping_fee">Shipping & Delivery Fee</option>
                                                    <option value="pallet_charge">Pallet Cost</option>
                                                    <option value="general_discount">General Adjustment Discount</option>
                                                </select>
                                            </div>
                                            <div className="flex-1">
                                                <label className="text-2xs font-semibold text-neutral-400">Description</label>
                                                <input
                                                    type="text"
                                                    value={adj.description}
                                                    onChange={(e) => handleAdjustmentChange(idx, 'description', e.target.value)}
                                                    className="w-full rounded border px-3 py-1 text-sm dark:bg-neutral-950"
                                                />
                                            </div>
                                            <div>
                                                <label className="text-2xs font-semibold text-neutral-400">Amount</label>
                                                <input
                                                    type="number"
                                                    value={adj.amount}
                                                    onChange={(e) => handleAdjustmentChange(idx, 'amount', Number(e.target.value))}
                                                    className="w-[120px] rounded border px-3 py-1 text-sm dark:bg-neutral-950"
                                                />
                                            </div>
                                            <Button type="button" onClick={() => removeAdjustment(idx)} variant="ghost" size="icon" className="text-red-500">
                                                <Trash2 className="h-4 w-4" />
                                            </Button>
                                        </div>
                                    ))}
                                    {data.adjustments.length === 0 && (
                                        <div className="text-xs text-neutral-400 italic text-center py-4">
                                            No billing adjustments added.
                                        </div>
                                    )}
                                </CardContent>
                            </Card>

                            <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm bg-neutral-50 dark:bg-neutral-900/20">
                                <CardContent className="p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                                    <div className="flex flex-col gap-1.5 max-w-md w-full">
                                        <label className="text-xs font-semibold text-neutral-500">Invoice Notes</label>
                                        <textarea
                                            value={data.notes}
                                            onChange={(e) => setData('notes', e.target.value)}
                                            rows={2}
                                            placeholder="Write additional billing/payment instructions..."
                                            className="w-full rounded border px-3 py-2 text-sm dark:bg-neutral-950"
                                        />
                                    </div>

                                    <div className="flex flex-col gap-2 min-w-[240px] text-right font-medium text-sm text-neutral-600 dark:text-neutral-400">
                                        <div className="flex justify-between">
                                            <span>Subtotal:</span>
                                            <span>{formatCurrency(totals.subtotal)}</span>
                                        </div>
                                        <div className="flex justify-between text-red-500">
                                            <span>Line Discounts:</span>
                                            <span>-{formatCurrency(totals.discount_amount)}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span>VAT 11%:</span>
                                            <span>{formatCurrency(totals.tax_amount)}</span>
                                        </div>
                                        {totals.adjustment_amount !== 0 && (
                                            <div className="flex justify-between text-indigo-500">
                                                <span>Adjustments:</span>
                                                <span>{formatCurrency(totals.adjustment_amount)}</span>
                                            </div>
                                        )}
                                        <div className="flex justify-between text-lg font-bold text-neutral-900 dark:text-neutral-50 border-t border-neutral-200 dark:border-neutral-800 pt-2">
                                            <span>Total Amount:</span>
                                            <span>{formatCurrency(totals.total_amount)}</span>
                                        </div>

                                        {errors.credit_limit && (
                                            <div className="text-xs text-red-500 font-bold mt-2 text-right">
                                                {errors.credit_limit}
                                            </div>
                                        )}

                                        <Button type="submit" disabled={processing} className="mt-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold w-full">
                                            <Save className="mr-2 h-4 w-4" />
                                            Save Invoice Draft
                                        </Button>
                                    </div>
                                </CardContent>
                            </Card>
                        </>
                    )}
                </form>
            </div>
        </>
    );
}
