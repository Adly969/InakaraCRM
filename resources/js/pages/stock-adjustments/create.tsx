import { Head, Link, useForm, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { index as indexAdjustmentsRoute, store as storeAdjustmentRoute, create as createAdjustmentRoute } from '@/routes/stock-adjustments';
import { ArrowLeft, Loader2, Plus, Trash2 } from 'lucide-react';
import type { Warehouse, InventoryItem } from '@/types';
import { useState } from 'react';

interface Props {
    warehouses: Warehouse[];
    inventoryItems: InventoryItem[];
    adjustmentTypes: Array<{ value: string; label: string }>;
}

interface AdjustmentLineItem {
    inventory_item_id: string;
    type: string;
    quantity_adjusted: string;
    unit_cost: string;
}

export default function StockAdjustmentCreate({ warehouses, inventoryItems, adjustmentTypes }: Props) {
    const [lines, setLines] = useState<AdjustmentLineItem[]>([]);

    const { data, setData, post, processing, errors } = useForm({
        warehouse_id: '',
        adjustment_date: nowFormatted(),
        notes: '',
        items: [] as AdjustmentLineItem[],
    });

    function nowFormatted() {
        const d = new Date();
        return d.toISOString().split('T')[0];
    }

    const handleWarehouseChange = (whId: string) => {
        setData('warehouse_id', whId);
        setLines([]);
        // Reload page to get products of this warehouse
        router.get(createAdjustmentRoute().url, { warehouse_id: whId }, { 
            preserveState: true,
            only: ['inventoryItems']
        });
    };

    const addLine = () => {
        setLines([
            ...lines,
            { inventory_item_id: '', type: 'addition', quantity_adjusted: '1', unit_cost: '0' }
        ]);
    };

    const removeLine = (idx: number) => {
        const newLines = [...lines];
        newLines.splice(idx, 1);
        setLines(newLines);
    };

    const updateLine = (idx: number, field: keyof AdjustmentLineItem, val: string) => {
        const newLines = [...lines];
        newLines[idx][field] = val;
        setLines(newLines);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        if (lines.length === 0) {
            alert('Please add at least one line item.');
            return;
        }

        // Validate that all lines have an inventory item selected
        const incomplete = lines.some(l => !l.inventory_item_id);
        if (incomplete) {
            alert('Please select an item for all lines.');
            return;
        }

        post(storeAdjustmentRoute().url, {
            data: {
                ...data,
                items: lines
            }
        });
    };

    return (
        <>
            <Head title="New Stock Adjustment" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 max-w-5xl mx-auto w-full">
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="icon" asChild>
                        <Link href={indexAdjustmentsRoute().url}>
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <div className="flex flex-col gap-1">
                        <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                            Create Stock Adjustment
                        </h1>
                        <p className="text-sm text-neutral-500 dark:text-neutral-400">
                            Create a stock balance adjustment document.
                        </p>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm">
                        <CardHeader>
                            <CardTitle className="text-lg">Adjustment Details</CardTitle>
                        </CardHeader>
                        <CardContent className="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div className="space-y-2">
                                <Label htmlFor="warehouse_id">Target Warehouse <span className="text-red-500">*</span></Label>
                                <Select
                                    value={data.warehouse_id}
                                    onValueChange={handleWarehouseChange}
                                >
                                    <SelectTrigger id="warehouse_id">
                                        <SelectValue placeholder="Select warehouse" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {warehouses.map((wh) => (
                                            <SelectItem key={wh.id} value={wh.id.toString()}>
                                                {wh.name} ({wh.code})
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {errors.warehouse_id && <p className="text-xs text-red-500 font-medium">{errors.warehouse_id}</p>}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="adjustment_date">Adjustment Date <span className="text-red-500">*</span></Label>
                                <Input
                                    id="adjustment_date"
                                    type="date"
                                    value={data.adjustment_date}
                                    onChange={(e) => setData('adjustment_date', e.target.value)}
                                    required
                                />
                                {errors.adjustment_date && <p className="text-xs text-red-500 font-medium">{errors.adjustment_date}</p>}
                            </div>

                            <div className="space-y-2 col-span-1 md:col-span-3">
                                <Label htmlFor="notes">Adjustment Reason / Notes <span className="text-red-500">*</span></Label>
                                <Input
                                    id="notes"
                                    value={data.notes}
                                    onChange={(e) => setData('notes', e.target.value)}
                                    placeholder="Explain the reason for this manual stock adjustment (e.g. damaged goods, annual audit variance)..."
                                    required
                                />
                                {errors.notes && <p className="text-xs text-red-500 font-medium">{errors.notes}</p>}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Line Items Card */}
                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm">
                        <CardHeader className="flex flex-row items-center justify-between">
                            <div>
                                <CardTitle className="text-lg">Line Items</CardTitle>
                                <CardDescription>Specify stock adjustments to apply to individual SKUs</CardDescription>
                            </div>
                            <Button 
                                type="button" 
                                size="sm" 
                                onClick={addLine} 
                                disabled={!data.warehouse_id}
                                className="bg-indigo-600 hover:bg-indigo-700 text-white"
                            >
                                <Plus className="mr-1.5 h-4 w-4" />
                                Add Row
                            </Button>
                        </CardHeader>
                        <CardContent className="p-0 border-t border-neutral-100 dark:border-neutral-850">
                            {lines.length === 0 ? (
                                <div className="text-center p-8 text-neutral-500 dark:text-neutral-400 text-sm font-medium">
                                    {!data.warehouse_id ? 'Please select warehouse first to add line items.' : 'No line items added yet. Click Add Row to start.'}
                                </div>
                            ) : (
                                <div className="overflow-x-auto">
                                    <table className="w-full text-sm">
                                        <thead className="bg-neutral-100/50 dark:bg-neutral-800/50 border-b border-neutral-200 dark:border-neutral-850">
                                            <tr>
                                                <th className="h-10 px-4 text-left font-semibold text-neutral-600 dark:text-neutral-350">Inventory SKU</th>
                                                <th className="h-10 px-4 text-left font-semibold text-neutral-600 dark:text-neutral-350">Type</th>
                                                <th className="h-10 px-4 text-right font-semibold text-neutral-600 dark:text-neutral-350">Qty Adjusted</th>
                                                <th className="h-10 px-4 text-right font-semibold text-neutral-600 dark:text-neutral-350">Unit Cost (HPP)</th>
                                                <th className="h-10 px-4 text-center font-semibold text-neutral-600 dark:text-neutral-350 w-16">Delete</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-neutral-200 dark:divide-neutral-800">
                                            {lines.map((line, idx) => (
                                                <tr key={idx} className="hover:bg-neutral-50/50">
                                                    <td className="p-3 w-80">
                                                        <select
                                                            value={line.inventory_item_id}
                                                            onChange={(e) => updateLine(idx, 'inventory_item_id', e.target.value)}
                                                            className="flex h-9 w-full rounded-md border border-neutral-200 bg-white px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-indigo-600 dark:border-neutral-800 dark:bg-neutral-950 dark:focus-visible:ring-indigo-400"
                                                            required
                                                        >
                                                            <option value="">-- Select SKU --</option>
                                                            {inventoryItems.map((item) => (
                                                                <option key={item.id} value={item.id.toString()}>
                                                                    {item.sku} - {item.name} ({Number(item.quantity_current).toLocaleString()} avail)
                                                                </option>
                                                            ))}
                                                        </select>
                                                    </td>
                                                    <td className="p-3 w-48">
                                                        <select
                                                            value={line.type}
                                                            onChange={(e) => updateLine(idx, 'type', e.target.value)}
                                                            className="flex h-9 w-full rounded-md border border-neutral-200 bg-white px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-indigo-600 dark:border-neutral-800 dark:bg-neutral-950 dark:focus-visible:ring-indigo-400"
                                                            required
                                                        >
                                                            {adjustmentTypes.map((t) => (
                                                                <option key={t.value} value={t.value}>
                                                                    {t.label}
                                                                </option>
                                                            ))}
                                                        </select>
                                                    </td>
                                                    <td className="p-3">
                                                        <Input
                                                            type="number"
                                                            min="0.01"
                                                            step="0.01"
                                                            value={line.quantity_adjusted}
                                                            onChange={(e) => updateLine(idx, 'quantity_adjusted', e.target.value)}
                                                            className="text-right"
                                                            required
                                                        />
                                                    </td>
                                                    <td className="p-3">
                                                        <Input
                                                            type="number"
                                                            min="0"
                                                            step="1"
                                                            value={line.unit_cost}
                                                            onChange={(e) => updateLine(idx, 'unit_cost', e.target.value)}
                                                            className="text-right"
                                                            required
                                                        />
                                                    </td>
                                                    <td className="p-3 text-center">
                                                        <Button 
                                                            type="button" 
                                                            variant="ghost" 
                                                            size="icon" 
                                                            onClick={() => removeLine(idx)}
                                                            className="text-red-500 hover:text-red-650 hover:bg-red-50"
                                                        >
                                                            <Trash2 className="h-4 w-4" />
                                                        </Button>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {/* Form Controls */}
                    <div className="flex items-center justify-end gap-3 pt-4 border-t border-neutral-200 dark:border-neutral-800">
                        <Button variant="outline" type="button" asChild>
                            <Link href={indexAdjustmentsRoute().url}>
                                Cancel
                            </Link>
                        </Button>
                        <Button 
                            type="submit" 
                            disabled={processing || lines.length === 0} 
                            className="bg-indigo-600 hover:bg-indigo-700 text-white min-w-[100px]"
                        >
                            {processing ? (
                                <>
                                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                    Creating...
                                </>
                            ) : (
                                'Submit Draft'
                            )}
                        </Button>
                    </div>
                </form>
            </div>
        </>
    );
}

StockAdjustmentCreate.layout = {
    breadcrumbs: [
        {
            title: 'Stock Adjustments',
            href: indexAdjustmentsRoute().url,
        },
        {
            title: 'Create',
            href: '',
        },
    ],
};
