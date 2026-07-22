import { useForm, Head, usePage } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { index as indexSalesOrdersRoute } from '@/routes/sales-orders';
import { Plus, Trash2, ShieldCheck, ShoppingBag, User, FileText, Calculator, Sparkles, ArrowLeft } from 'lucide-react';
import type { SalesOrder } from '@/types';

interface DropdownOption {
    id: number;
    name: string;
    company_name?: string | null;
}

interface PageProps extends Record<string, any> {
    salesOrder: SalesOrder;
    customers: DropdownOption[];
    users: DropdownOption[];
}

interface FormItem {
    id?: number;
    description: string;
    quantity: number;
    unit: string;
    unit_price: number;
}

export default function SalesOrderEdit() {
    const { salesOrder, customers, users } = usePage<PageProps>().props;

    const { data, setData, put, processing, errors } = useForm({
        customer_id: (salesOrder.customer_id ?? '') as string | number,
        quotation_id: (salesOrder.quotation_id ?? '') as string | number,
        subject: salesOrder.subject,
        delivery_terms: salesOrder.delivery_terms ?? '',
        notes: salesOrder.notes ?? '',
        currency: salesOrder.currency,
        tax_rate: Number(salesOrder.tax_rate ?? 11),
        assigned_to: (salesOrder.assigned_to ?? '') as string | number,
        items: (salesOrder.items ?? []).map(item => ({
            id: item.id,
            description: item.description,
            quantity: Number(item.quantity),
            unit: item.unit,
            unit_price: Number(item.unit_price)
        })) as FormItem[]
    });

    const subtotal = (data.items || []).reduce((acc: number, item: FormItem) => acc + (Number(item.quantity || 0) * Number(item.unit_price || 0)), 0);
    const taxRate = Number(data.tax_rate ?? 11);
    const taxAmount = subtotal * (taxRate / 100);
    const totalAmount = subtotal + taxAmount;

    const handleAddItem = () => {
        setData('items', [
            ...data.items,
            { description: '', quantity: 1, unit: 'pcs', unit_price: 0 }
        ]);
    };

    const handleRemoveItem = (index: number) => {
        if (data.items.length === 1) return;
        setData('items', data.items.filter((_: FormItem, i: number) => i !== index));
    };

    const handleItemChange = (index: number, field: keyof FormItem, value: any) => {
        const updatedItems = [...data.items];
        updatedItems[index] = {
            ...updatedItems[index],
            [field]: value
        };
        setData('items', updatedItems);
    };

    const handleAddPresetProduct = (presetDescription: string, presetPrice: number) => {
        setData('items', [
            ...data.items,
            { description: presetDescription, quantity: 1, unit: 'set', unit_price: presetPrice }
        ]);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/sales-orders/${salesOrder.id}`);
    };

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: data.currency || 'IDR', maximumFractionDigits: 0 }).format(amount);
    };

    return (
        <>
            <Head title={`Edit Sales Order - ${salesOrder.reference_no ?? salesOrder.subject}`} />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                {/* Top Header Studio - Exactly matching Create page */}
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-neutral-200 dark:border-neutral-800 pb-5">
                    <div className="flex items-center gap-3">
                        <Button variant="outline" size="icon" asChild className="h-9 w-9 border-neutral-300 dark:border-neutral-700 rounded-xl">
                            <Link href={indexSalesOrdersRoute()}>
                                <ArrowLeft className="h-4 w-4 text-neutral-600 dark:text-neutral-300" />
                            </Link>
                        </Button>
                        <div>
                            <div className="flex items-center gap-2">
                                <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                                    Edit Pesanan Penjualan
                                </h1>
                                <span className="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                    {salesOrder.reference_no ?? 'SO DRAFT'}
                                </span>
                            </div>
                            <p className="text-sm text-neutral-500 dark:text-neutral-400">
                                Kelola pesanan penjualan resmi, alokasi item spesifikasi furnitur, dan kalkulasi PPN.
                            </p>
                        </div>
                    </div>

                    <div className="flex items-center gap-2">
                        <Button variant="outline" asChild className="border-neutral-300 dark:border-neutral-700 font-semibold rounded-xl">
                            <Link href={indexSalesOrdersRoute()}>Batal</Link>
                        </Button>
                        <Button onClick={handleSubmit} disabled={processing} className="bg-neutral-900 hover:bg-neutral-800 text-white dark:bg-neutral-100 dark:text-neutral-900 dark:hover:bg-neutral-200 font-bold rounded-xl">
                            <ShieldCheck className="mr-2 h-4 w-4 text-emerald-500" />
                            Simpan Perubahan Order
                        </Button>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="grid grid-cols-1 gap-6 lg:grid-cols-12">
                    {/* LEFT COLUMN: Main Form Cards (8 Cols) */}
                    <div className="lg:col-span-8 flex flex-col gap-6">
                        {/* CARD 1: Customer & Order Details */}
                        <Card className="border border-neutral-200 dark:border-neutral-800 shadow-sm rounded-2xl bg-white dark:bg-neutral-900 overflow-hidden">
                            <CardHeader className="border-b border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/60 dark:bg-neutral-850/60 py-4 px-6">
                                <div className="flex items-center gap-2.5">
                                    <div className="w-8 h-8 rounded-xl bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 flex items-center justify-center border border-sky-200/60 dark:border-sky-800/60">
                                        <User className="h-4 w-4" />
                                    </div>
                                    <CardTitle className="text-base font-bold text-neutral-900 dark:text-neutral-100">
                                        Informasi Pelanggan & Spesifikasi Kontrak
                                    </CardTitle>
                                </div>
                            </CardHeader>
                            <CardContent className="p-6 space-y-5">
                                <div className="grid gap-5 sm:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="customer_id" className="text-sm font-semibold text-neutral-800 dark:text-neutral-200">
                                            Pilih Pelanggan / Klien <span className="text-rose-500">*</span>
                                        </Label>
                                        <select
                                            id="customer_id"
                                            name="customer_id"
                                            value={data.customer_id ?? ''}
                                            onChange={(e) => setData('customer_id', e.target.value)}
                                            required
                                            className="w-full rounded-xl border border-neutral-300/80 dark:border-neutral-750 bg-neutral-50/50 dark:bg-neutral-800 pl-3.5 pr-10 py-2.5 text-sm font-medium text-neutral-900 dark:text-neutral-100 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236b7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-size-[1.25rem_1.25rem] bg-no-repeat bg-position-[right_0.85rem_center] focus:outline-none focus:ring-2 focus:ring-neutral-400 cursor-pointer shadow-xs"
                                        >
                                            <option value="">-- Pilih Pelanggan --</option>
                                            {customers.map((c) => (
                                                <option key={c.id} value={c.id}>
                                                    {c.name} {c.company_name ? `(${c.company_name})` : ''}
                                                </option>
                                            ))}
                                        </select>
                                        <InputError message={errors.customer_id} />
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="subject" className="text-sm font-semibold text-neutral-800 dark:text-neutral-200">
                                            Subjek / Judul Pesanan <span className="text-rose-500">*</span>
                                        </Label>
                                        <Input
                                            id="subject"
                                            name="subject"
                                            value={data.subject}
                                            onChange={(e) => setData('subject', e.target.value)}
                                            required
                                            placeholder="Contoh: Pesanan Furnitur Jati Villa Beachfront"
                                            className="text-sm font-medium h-10 rounded-xl border-neutral-300/80 dark:border-neutral-750 bg-neutral-50/50 dark:bg-neutral-800"
                                        />
                                        <InputError message={errors.subject} />
                                    </div>
                                </div>

                                <div className="grid gap-5 sm:grid-cols-3">
                                    <div className="space-y-2">
                                        <Label htmlFor="currency" className="text-sm font-semibold text-neutral-800 dark:text-neutral-200">Mata Uang</Label>
                                        <select
                                            id="currency"
                                            name="currency"
                                            value={data.currency}
                                            onChange={(e) => setData('currency', e.target.value)}
                                            className="w-full rounded-xl border border-neutral-300/80 dark:border-neutral-750 bg-neutral-50/50 dark:bg-neutral-800 pl-3.5 pr-10 py-2.5 text-sm font-medium text-neutral-900 dark:text-neutral-100 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236b7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-size-[1.25rem_1.25rem] bg-no-repeat bg-position-[right_0.85rem_center] focus:outline-none focus:ring-2 focus:ring-neutral-400 cursor-pointer shadow-xs"
                                        >
                                            <option value="IDR">Rupiah Indonesia (IDR)</option>
                                            <option value="USD">US Dollar (USD)</option>
                                        </select>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="assigned_to" className="text-sm font-semibold text-neutral-800 dark:text-neutral-200">Sales Representative / PIC</Label>
                                        <select
                                            id="assigned_to"
                                            name="assigned_to"
                                            value={data.assigned_to ?? ''}
                                            onChange={(e) => setData('assigned_to', e.target.value)}
                                            className="w-full rounded-xl border border-neutral-300/80 dark:border-neutral-750 bg-neutral-50/50 dark:bg-neutral-800 pl-3.5 pr-10 py-2.5 text-sm font-medium text-neutral-900 dark:text-neutral-100 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236b7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-size-[1.25rem_1.25rem] bg-no-repeat bg-position-[right_0.85rem_center] focus:outline-none focus:ring-2 focus:ring-neutral-400 cursor-pointer shadow-xs"
                                        >
                                            <option value="">Belum Ditugaskan</option>
                                            {users.map((u) => (
                                                <option key={u.id} value={u.id}>
                                                    {u.name}
                                                </option>
                                            ))}
                                        </select>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="delivery_terms" className="text-sm font-semibold text-neutral-800 dark:text-neutral-200">Syarat Pengiriman (TOP)</Label>
                                        <Input
                                            id="delivery_terms"
                                            name="delivery_terms"
                                            value={data.delivery_terms}
                                            onChange={(e) => setData('delivery_terms', e.target.value)}
                                            placeholder="Contoh: FOB Pelabuhan Denpasar"
                                            className="text-sm font-medium h-10 rounded-xl border-neutral-300/80 dark:border-neutral-750 bg-neutral-50/50 dark:bg-neutral-800"
                                        />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* CARD 2: Line Items Table & Quick Preset Buttons */}
                        <Card className="border border-neutral-200 dark:border-neutral-800 shadow-sm rounded-2xl bg-white dark:bg-neutral-900 overflow-hidden">
                            <CardHeader className="border-b border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/60 dark:bg-neutral-850/60 py-4 px-6 flex flex-row items-center justify-between">
                                <div className="flex items-center gap-2.5">
                                    <div className="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center border border-emerald-200/60 dark:border-emerald-800/60">
                                        <ShoppingBag className="h-4 w-4" />
                                    </div>
                                    <CardTitle className="text-base font-bold text-neutral-900 dark:text-neutral-100">
                                        Daftar Item & Spesifikasi Produk Furnitur
                                    </CardTitle>
                                </div>
                                <Button type="button" variant="outline" size="sm" onClick={handleAddItem} className="h-9 px-3.5 border-neutral-300 dark:border-neutral-700 text-xs font-semibold rounded-xl">
                                    <Plus className="h-4 w-4 mr-1 text-emerald-600" /> Tambah Baris
                                </Button>
                            </CardHeader>
                            <CardContent className="p-6 space-y-5">
                                {/* Quick Product Preset Bar */}
                                <div className="p-4 rounded-xl bg-neutral-100/70 dark:bg-neutral-800/50 border border-neutral-200/60 dark:border-neutral-750">
                                    <div className="flex items-center gap-2 text-xs font-bold text-neutral-800 dark:text-neutral-200 mb-2.5">
                                        <Sparkles className="h-4 w-4 text-amber-500" />
                                        <span>Tambah Cepat Produk Populer:</span>
                                    </div>
                                    <div className="flex flex-wrap items-center gap-2.5">
                                        <button
                                            type="button"
                                            onClick={() => handleAddPresetProduct('Rangka Tempat Tidur Jati King (Grand Hyatt Hotel Series)', 18500000)}
                                            className="px-3.5 py-1.5 rounded-full text-xs font-semibold bg-white dark:bg-neutral-800 border border-neutral-300/80 dark:border-neutral-700 text-neutral-800 dark:text-neutral-200 hover:border-neutral-400 hover:shadow-xs transition-all"
                                        >
                                            + Tempat Tidur Jati King (Rp 18,5Jt)
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => handleAddPresetProduct('Sunbed Outdoor Rotan Sintetis (Aman Villa Series)', 6200000)}
                                            className="px-3.5 py-1.5 rounded-full text-xs font-semibold bg-white dark:bg-neutral-800 border border-neutral-300/80 dark:border-neutral-700 text-neutral-800 dark:text-neutral-200 hover:border-neutral-400 hover:shadow-xs transition-all"
                                        >
                                            + Sunbed Rotan Outdoor (Rp 6,2Jt)
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => handleAddPresetProduct('Meja Kopi Atasan Marmer Carrara Italia', 8800000)}
                                            className="px-3.5 py-1.5 rounded-full text-xs font-semibold bg-white dark:bg-neutral-800 border border-neutral-300/80 dark:border-neutral-700 text-neutral-800 dark:text-neutral-200 hover:border-neutral-400 hover:shadow-xs transition-all"
                                        >
                                            + Meja Kopi Marmer (Rp 8,8Jt)
                                        </button>
                                    </div>
                                </div>

                                {/* Items Table */}
                                <div className="border border-neutral-200 dark:border-neutral-800 rounded-xl overflow-hidden shadow-xs">
                                    <table className="w-full text-sm">
                                        <thead className="bg-neutral-100/90 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 font-bold uppercase text-[11px] tracking-wider border-b border-neutral-200 dark:border-neutral-800">
                                            <tr>
                                                <th className="py-3 px-4 text-left w-5/12 border-r border-neutral-200/50 dark:border-neutral-750">Deskripsi / Spesifikasi Item</th>
                                                <th className="py-3 px-4 text-center w-2/12 border-r border-neutral-200/50 dark:border-neutral-750">Jumlah & Satuan</th>
                                                <th className="py-3 px-4 text-right w-2/12 border-r border-neutral-200/50 dark:border-neutral-750">Harga Satuan (IDR)</th>
                                                <th className="py-3 px-4 text-right w-2/12 border-r border-neutral-200/50 dark:border-neutral-750">Subtotal (IDR)</th>
                                                <th className="py-3 px-4 text-center w-1/12">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-neutral-200 dark:divide-neutral-800">
                                            {data.items.map((item: FormItem, idx: number) => {
                                                const itemSubtotal = Number(item.quantity || 0) * Number(item.unit_price || 0);
                                                return (
                                                    <tr key={idx} className="hover:bg-neutral-50/80 dark:hover:bg-neutral-850/40">
                                                        <td className="p-3 align-top">
                                                            <Input
                                                                placeholder="Deskripsi furnitur, kayu, finishing..."
                                                                value={item.description}
                                                                onChange={(e) => handleItemChange(idx, 'description', e.target.value)}
                                                                required
                                                                className="text-sm font-medium h-10 rounded-xl border-neutral-300/80 dark:border-neutral-750 bg-white dark:bg-neutral-900"
                                                            />
                                                        </td>
                                                        <td className="p-3 align-top">
                                                            <div className="flex items-center gap-1.5">
                                                                <Input
                                                                    type="number"
                                                                    placeholder="Qty"
                                                                    value={item.quantity}
                                                                    onChange={(e) => handleItemChange(idx, 'quantity', Number(e.target.value))}
                                                                    required
                                                                    min="0.01"
                                                                    step="any"
                                                                    className="text-sm font-semibold text-center h-10 rounded-xl border-neutral-300/80 dark:border-neutral-750 bg-white dark:bg-neutral-900 w-20"
                                                                />
                                                                <Input
                                                                    placeholder="Satuan"
                                                                    value={item.unit}
                                                                    onChange={(e) => handleItemChange(idx, 'unit', e.target.value)}
                                                                    required
                                                                    className="text-sm text-center h-10 rounded-xl border-neutral-300/80 dark:border-neutral-750 bg-white dark:bg-neutral-900 w-16"
                                                                />
                                                            </div>
                                                        </td>
                                                        <td className="p-3 align-top">
                                                            <Input
                                                                type="number"
                                                                placeholder="Harga Satuan"
                                                                value={item.unit_price}
                                                                onChange={(e) => handleItemChange(idx, 'unit_price', Number(e.target.value))}
                                                                required
                                                                min="0"
                                                                className="text-sm font-mono font-medium h-10 text-right rounded-xl border-neutral-300/80 dark:border-neutral-750 bg-white dark:bg-neutral-900"
                                                            />
                                                        </td>
                                                        <td className="p-3 align-middle text-right font-mono font-bold text-sm text-neutral-900 dark:text-neutral-100">
                                                            {formatCurrency(itemSubtotal)}
                                                        </td>
                                                        <td className="p-3 align-middle text-center">
                                                            <Button
                                                                type="button"
                                                                variant="ghost"
                                                                size="icon"
                                                                disabled={data.items.length === 1}
                                                                onClick={() => handleRemoveItem(idx)}
                                                                className="h-8 w-8 rounded-lg text-rose-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30"
                                                            >
                                                                <Trash2 className="h-4 w-4" />
                                                            </Button>
                                                        </td>
                                                    </tr>
                                                );
                                            })}
                                        </tbody>
                                    </table>
                                </div>
                            </CardContent>
                        </Card>

                        {/* CARD 3: Contract Notes & Exclusions */}
                        <Card className="border border-neutral-200 dark:border-neutral-800 shadow-sm rounded-2xl bg-white dark:bg-neutral-900 overflow-hidden">
                            <CardHeader className="border-b border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/60 dark:bg-neutral-850/60 py-3.5 px-6">
                                <div className="flex items-center gap-2.5">
                                    <FileText className="h-4 w-4 text-neutral-500" />
                                    <CardTitle className="text-sm font-bold text-neutral-800 dark:text-neutral-200">
                                        Catatan Kontrak, Garansi & Ketentuan Pengiriman
                                    </CardTitle>
                                </div>
                            </CardHeader>
                            <CardContent className="p-5">
                                <textarea
                                    id="notes"
                                    name="notes"
                                    rows={3}
                                    value={data.notes}
                                    onChange={(e) => setData('notes', e.target.value)}
                                    placeholder="Tambahkan catatan khusus, garansi kayu jati, jadwal instalasi..."
                                    className="w-full rounded-xl border border-neutral-300/80 dark:border-neutral-750 bg-neutral-50/50 dark:bg-neutral-800 p-3.5 text-sm text-neutral-800 dark:text-neutral-200 focus:outline-none focus:ring-2 focus:ring-neutral-400"
                                />
                            </CardContent>
                        </Card>
                    </div>

                    {/* RIGHT COLUMN: Sticky Calculation Summary Widget (4 Cols) */}
                    <div className="lg:col-span-4 space-y-6">
                        <Card className="border border-neutral-200 dark:border-neutral-800 shadow-sm rounded-2xl bg-white dark:bg-neutral-900 sticky top-6 overflow-hidden">
                            <CardHeader className="border-b border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/60 dark:bg-neutral-850/60 py-4 px-6">
                                <div className="flex items-center gap-2.5">
                                    <div className="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center border border-blue-200/60 dark:border-blue-800/60">
                                        <Calculator className="h-4 w-4" />
                                    </div>
                                    <CardTitle className="text-base font-bold text-neutral-900 dark:text-neutral-100">
                                        Kalkulasi Ringkasan Nilai Order
                                    </CardTitle>
                                </div>
                            </CardHeader>
                            <CardContent className="p-6 space-y-5">
                                <div className="space-y-3 text-sm">
                                    <div className="flex justify-between text-neutral-600 dark:text-neutral-400">
                                        <span>Subtotal Item:</span>
                                        <span className="font-mono font-semibold text-neutral-900 dark:text-neutral-100">{formatCurrency(subtotal)}</span>
                                    </div>
                                    <div className="flex justify-between text-neutral-600 dark:text-neutral-400">
                                        <span>Pajak PPN ({data.tax_rate}%):</span>
                                        <span className="font-mono font-semibold text-neutral-900 dark:text-neutral-100">{formatCurrency(taxAmount)}</span>
                                    </div>
                                    <div className="pt-4 border-t border-neutral-200 dark:border-neutral-800 flex justify-between items-baseline">
                                        <span className="font-bold text-neutral-900 dark:text-neutral-100 text-base">Total Nilai Order:</span>
                                        <span className="font-mono font-extrabold text-2xl text-emerald-600 dark:text-emerald-400">{formatCurrency(totalAmount)}</span>
                                    </div>
                                </div>

                                <div className="pt-5 border-t border-neutral-200 dark:border-neutral-800 space-y-2.5">
                                    <Button
                                        type="submit"
                                        disabled={processing}
                                        className="w-full bg-neutral-900 hover:bg-neutral-800 text-white dark:bg-neutral-100 dark:text-neutral-900 dark:hover:bg-neutral-200 font-bold py-3 text-sm rounded-xl shadow-sm"
                                    >
                                        <ShieldCheck className="mr-2 h-4 w-4 text-emerald-500" />
                                        Simpan Perubahan Sales Order
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        asChild
                                        className="w-full border-neutral-300 dark:border-neutral-700 text-sm font-semibold rounded-xl"
                                    >
                                        <Link href={indexSalesOrdersRoute()}>Batal</Link>
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </form>
            </div>
        </>
    );
}

SalesOrderEdit.layout = {
    breadcrumbs: [
        {
            title: 'Sales Orders',
            href: indexSalesOrdersRoute(),
        },
        {
            title: 'Edit',
        },
    ],
};
