import { useForm, Head, usePage } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index as indexProductionOrdersRoute } from '@/routes/production-orders';
import { Plus, Trash2 } from 'lucide-react';
import type { ProductionOrder } from '@/types';

interface DropdownOption {
    id: number;
    name: string;
    company_name?: string | null;
}

interface PageProps extends Record<string, any> {
    productionOrder: ProductionOrder;
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

export default function ProductionOrderEdit() {
    const { productionOrder, customers, users } = usePage<PageProps>().props;

    const { data, setData, put, processing, errors } = useForm({
        customer_id: productionOrder.customer_id,
        subject: productionOrder.subject,
        priority: productionOrder.priority,
        target_completion_date: productionOrder.target_completion_date ?? '',
        actual_completion_date: productionOrder.actual_completion_date ?? '',
        started_at: productionOrder.started_at ?? '',
        completed_at: productionOrder.completed_at ?? '',
        estimated_hours: productionOrder.estimated_hours ?? '',
        actual_hours: productionOrder.actual_hours ?? '',
        production_notes: productionOrder.production_notes ?? '',
        currency: productionOrder.currency,
        tax_rate: Number(productionOrder.tax_rate ?? 11),
        assigned_to: productionOrder.assigned_to ?? '',
        _updated_at: productionOrder.updated_at,
        items: (productionOrder.items ?? []).map(item => ({
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

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/production-orders/${productionOrder.id}`);
    };

    return (
        <>
            <Head title={`Edit Production Order - ${productionOrder.reference_no ?? productionOrder.subject}`} />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 max-w-2xl mx-auto">
                <div className="flex flex-col gap-1">
                    <Heading
                        title={`Edit Production Order - ${productionOrder.reference_no ?? 'Pending'}`}
                        description="Modify production order details and manufacturing specifications."
                    />
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* Customer Selection */}
                    <div className="grid grid-cols-1 gap-4 bg-neutral-50 dark:bg-neutral-900/50 p-4 rounded-lg border border-neutral-200 dark:border-neutral-800">
                        <div className="flex flex-col gap-1.5">
                            <Label htmlFor="customer_id">Select Customer</Label>
                            <select
                                id="customer_id"
                                name="customer_id"
                                value={data.customer_id ?? ''}
                                onChange={(e) => setData('customer_id', Number(e.target.value))}
                                required
                                className="border-input flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] mt-1 dark:bg-neutral-950"
                            >
                                <option value="">-- Choose Customer --</option>
                                {customers.map((c) => (
                                    <option key={c.id} value={c.id}>
                                        {c.name} {c.company_name ? `(${c.company_name})` : ''}
                                    </option>
                                ))}
                            </select>
                            <InputError message={errors.customer_id} />
                        </div>
                    </div>

                    {/* General Fields */}
                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="grid gap-1">
                            <Label htmlFor="subject">Subject</Label>
                            <Input
                                id="subject"
                                name="subject"
                                value={data.subject}
                                onChange={(e) => setData('subject', e.target.value)}
                                required
                                className="mt-1"
                            />
                            <InputError message={errors.subject} />
                        </div>

                        <div className="grid gap-1">
                            <Label htmlFor="priority">Priority</Label>
                            <select
                                id="priority"
                                name="priority"
                                value={data.priority}
                                onChange={(e) => setData('priority', e.target.value as any)}
                                required
                                className="border-input flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] mt-1 dark:bg-neutral-950"
                            >
                                <option value="low">Low</option>
                                <option value="normal">Normal</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                            <InputError message={errors.priority} />
                        </div>

                        <div className="grid gap-1">
                            <Label htmlFor="target_completion_date">Target Completion Date</Label>
                            <Input
                                type="date"
                                id="target_completion_date"
                                name="target_completion_date"
                                value={data.target_completion_date}
                                onChange={(e) => setData('target_completion_date', e.target.value)}
                                className="mt-1"
                            />
                            <InputError message={errors.target_completion_date} />
                        </div>

                        <div className="grid gap-1">
                            <Label htmlFor="estimated_hours">Estimated Hours</Label>
                            <Input
                                type="number"
                                step="0.1"
                                id="estimated_hours"
                                name="estimated_hours"
                                value={data.estimated_hours}
                                onChange={(e) => setData('estimated_hours', e.target.value)}
                                className="mt-1"
                            />
                            <InputError message={errors.estimated_hours} />
                        </div>

                        <div className="grid gap-1">
                            <Label htmlFor="currency">Currency</Label>
                            <select
                                id="currency"
                                name="currency"
                                value={data.currency}
                                onChange={(e) => setData('currency', e.target.value)}
                                required
                                className="border-input flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] mt-1 dark:bg-neutral-950"
                            >
                                <option value="IDR">IDR</option>
                                <option value="USD">USD</option>
                            </select>
                            <InputError message={errors.currency} />
                        </div>

                        <div className="grid gap-1">
                            <Label htmlFor="tax_rate">Tax Rate (%)</Label>
                            <Input
                                type="number"
                                step="0.01"
                                id="tax_rate"
                                name="tax_rate"
                                value={data.tax_rate}
                                onChange={(e) => setData('tax_rate', Number(e.target.value))}
                                required
                                className="mt-1"
                            />
                            <InputError message={errors.tax_rate} />
                        </div>

                        <div className="grid gap-1 sm:col-span-2">
                            <Label htmlFor="assigned_to">Assigned Craftsman</Label>
                            <select
                                id="assigned_to"
                                name="assigned_to"
                                value={data.assigned_to ?? ''}
                                onChange={(e) => setData('assigned_to', e.target.value)}
                                className="border-input flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] mt-1 dark:bg-neutral-950"
                            >
                                <option value="">Unassigned</option>
                                {users.map((u) => (
                                    <option key={u.id} value={u.id}>
                                        {u.name}
                                    </option>
                                ))}
                            </select>
                            <InputError message={errors.assigned_to} />
                        </div>
                    </div>

                    <div className="grid gap-1">
                        <Label htmlFor="production_notes">Production Notes</Label>
                        <textarea
                            id="production_notes"
                            name="production_notes"
                            rows={3}
                            value={data.production_notes}
                            onChange={(e) => setData('production_notes', e.target.value)}
                            className="border-input flex w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] mt-1 dark:bg-neutral-950"
                        />
                        <InputError message={errors.production_notes} />
                    </div>

                    {/* Items Section */}
                    <div className="space-y-4">
                        <div className="flex items-center justify-between border-b border-neutral-200 dark:border-neutral-800 pb-2">
                            <h3 className="text-sm font-semibold text-neutral-950 dark:text-neutral-50">Production Line Items</h3>
                            <Button type="button" variant="outline" size="sm" onClick={handleAddItem}>
                                <Plus className="mr-1 h-3.5 w-3.5" />
                                Add Item
                            </Button>
                        </div>
                        <InputError message={errors.items} />

                        <div className="space-y-3">
                            {data.items.map((item, index) => (
                                <div key={index} className="flex flex-col sm:flex-row gap-3 items-start p-3 bg-neutral-50 dark:bg-neutral-900/30 rounded-lg border border-neutral-200 dark:border-neutral-800">
                                    <div className="flex-1 w-full grid gap-2">
                                        <Label className="text-xs text-neutral-500">Description</Label>
                                        <Input
                                            value={item.description}
                                            onChange={(e) => handleItemChange(index, 'description', e.target.value)}
                                            required
                                        />
                                        <InputError message={errors[`items.${index}.description` as any]} />
                                    </div>
                                    <div className="w-full sm:w-20 grid gap-2">
                                        <Label className="text-xs text-neutral-500">Qty</Label>
                                        <Input
                                            type="number"
                                            value={item.quantity}
                                            onChange={(e) => handleItemChange(index, 'quantity', Number(e.target.value))}
                                            required
                                            min="0.01"
                                            step="0.01"
                                        />
                                        <InputError message={errors[`items.${index}.quantity` as any]} />
                                    </div>
                                    <div className="w-full sm:w-20 grid gap-2">
                                        <Label className="text-xs text-neutral-500">Unit</Label>
                                        <Input
                                            value={item.unit}
                                            onChange={(e) => handleItemChange(index, 'unit', e.target.value)}
                                            required
                                        />
                                        <InputError message={errors[`items.${index}.unit` as any]} />
                                    </div>
                                    <div className="w-full sm:w-28 grid gap-2">
                                        <Label className="text-xs text-neutral-500">Unit Price</Label>
                                        <Input
                                            type="number"
                                            value={item.unit_price}
                                            onChange={(e) => handleItemChange(index, 'unit_price', Number(e.target.value))}
                                            required
                                            min="0"
                                        />
                                        <InputError message={errors[`items.${index}.unit_price` as any]} />
                                    </div>
                                    {data.items.length > 1 && (
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon"
                                            onClick={() => handleRemoveItem(index)}
                                            className="text-red-500 hover:text-red-600 self-end sm:mt-6 shrink-0"
                                        >
                                            <Trash2 className="h-4 w-4" />
                                        </Button>
                                    )}
                                </div>
                            ))}
                        </div>
                    </div>

                    {/* Totals Preview Block */}
                    <div className="flex flex-col items-end gap-2 border-t border-neutral-200 dark:border-neutral-800 pt-6">
                        <div className="flex justify-between w-64 text-sm text-neutral-600 dark:text-neutral-400">
                            <span>Subtotal:</span>
                            <span className="font-mono">{data.currency} {subtotal.toLocaleString()}</span>
                        </div>
                        <div className="flex justify-between w-64 text-sm text-neutral-600 dark:text-neutral-400">
                            <span>Tax ({taxRate}%):</span>
                            <span className="font-mono">{data.currency} {taxAmount.toLocaleString()}</span>
                        </div>
                        <div className="flex justify-between w-64 text-base font-bold text-neutral-900 dark:text-neutral-50 pt-2 border-t border-neutral-200 dark:border-neutral-800">
                            <span>Total (Preview):</span>
                            <span className="font-mono">{data.currency} {totalAmount.toLocaleString()}</span>
                        </div>
                    </div>

                    {/* Form actions */}
                    <div className="flex items-center justify-end gap-3 pt-4">
                        <Button asChild variant="outline">
                            <Link href={`/production-orders/${productionOrder.id}`}>Cancel</Link>
                        </Button>
                        <Button type="submit" disabled={processing}>
                            Update Production Order
                        </Button>
                    </div>
                </form>
            </div>
        </>
    );
}

ProductionOrderEdit.layout = {
    breadcrumbs: [
        {
            title: 'Production Orders',
            href: indexProductionOrdersRoute(),
        },
        {
            title: 'Edit',
        },
    ],
};
