import { Head, Link, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { index as indexWarehousesRoute, store as storeWarehouseRoute } from '@/routes/warehouses';
import { ArrowLeft, Loader2 } from 'lucide-react';

interface Props {
    managers: Array<{ id: number; name: string }>;
}

export default function WarehouseCreate({ managers }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        code: '',
        name: '',
        type: 'central',
        is_default: false,
        status: 'active',
        address: '',
        manager_id: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(storeWarehouseRoute().url);
    };

    return (
        <>
            <Head title="Create Warehouse" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 max-w-4xl mx-auto w-full">
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="icon" asChild>
                        <Link href={indexWarehousesRoute().url}>
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <div className="flex flex-col gap-1">
                        <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                            Create Warehouse
                        </h1>
                        <p className="text-sm text-neutral-500 dark:text-neutral-400">
                            Add a new physical storage warehouse to start tracking inventory.
                        </p>
                    </div>
                </div>

                <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm">
                    <CardHeader>
                        <CardTitle className="text-lg">Warehouse Profile</CardTitle>
                        <CardDescription>Enter unique code and other identification metadata details.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div className="space-y-2">
                                    <Label htmlFor="code">Warehouse Code <span className="text-red-500">*</span></Label>
                                    <Input
                                        id="code"
                                        value={data.code}
                                        onChange={(e) => setData('code', e.target.value)}
                                        placeholder="e.g., WH-JKT-01"
                                        required
                                    />
                                    {errors.code && <p className="text-xs text-red-500 font-medium">{errors.code}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="name">Warehouse Name <span className="text-red-500">*</span></Label>
                                    <Input
                                        id="name"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        placeholder="e.g., Jakarta Central Distribution"
                                        required
                                    />
                                    {errors.name && <p className="text-xs text-red-500 font-medium">{errors.name}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="type">Warehouse Type <span className="text-red-500">*</span></Label>
                                    <Select
                                        value={data.type}
                                        onValueChange={(val) => setData('type', val)}
                                    >
                                        <SelectTrigger id="type">
                                            <SelectValue placeholder="Select type" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="central">Central Distribution Center</SelectItem>
                                            <SelectItem value="transit">Transit / Temporary</SelectItem>
                                            <SelectItem value="damaged">Damaged / QC Scrap</SelectItem>
                                            <SelectItem value="return">Customer Returns</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    {errors.type && <p className="text-xs text-red-500 font-medium">{errors.type}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="manager_id">Warehouse Manager</Label>
                                    <Select
                                        value={data.manager_id}
                                        onValueChange={(val) => setData('manager_id', val)}
                                    >
                                        <SelectTrigger id="manager_id">
                                            <SelectValue placeholder="Select warehouse manager" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {managers.map((m) => (
                                                <SelectItem key={m.id} value={m.id.toString()}>
                                                    {m.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.manager_id && <p className="text-xs text-red-500 font-medium">{errors.manager_id}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="status">Warehouse Status <span className="text-red-500">*</span></Label>
                                    <Select
                                        value={data.status}
                                        onValueChange={(val) => setData('status', val)}
                                    >
                                        <SelectTrigger id="status">
                                            <SelectValue placeholder="Select status" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="active">Active</SelectItem>
                                            <SelectItem value="inactive">Inactive</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    {errors.status && <p className="text-xs text-red-500 font-medium">{errors.status}</p>}
                                </div>

                                <div className="flex items-center space-x-2 pt-8">
                                    <Checkbox
                                        id="is_default"
                                        checked={data.is_default}
                                        onCheckedChange={(checked) => setData('is_default', checked === true)}
                                    />
                                    <div className="grid gap-1.5 leading-none">
                                        <Label htmlFor="is_default" className="text-sm font-semibold cursor-pointer">
                                            Set as Default Warehouse
                                        </Label>
                                        <p className="text-xs text-neutral-500 dark:text-neutral-400">
                                            This warehouse will be pre-selected on all transactions.
                                        </p>
                                    </div>
                                    {errors.is_default && <p className="text-xs text-red-500 font-medium">{errors.is_default}</p>}
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="address">Warehouse Address</Label>
                                <Input
                                    id="address"
                                    value={data.address}
                                    onChange={(e) => setData('address', e.target.value)}
                                    placeholder="Enter physical address street name, unit number, city, etc."
                                />
                                {errors.address && <p className="text-xs text-red-500 font-medium">{errors.address}</p>}
                            </div>

                            <div className="flex items-center justify-end gap-3 pt-4 border-t border-neutral-200 dark:border-neutral-800">
                                <Button variant="outline" type="button" asChild>
                                    <Link href={indexWarehousesRoute().url}>
                                        Cancel
                                    </Link>
                                </Button>
                                <Button type="submit" disabled={processing} className="bg-indigo-600 hover:bg-indigo-700 text-white min-w-[100px]">
                                    {processing ? (
                                        <>
                                            <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                            Saving...
                                        </>
                                    ) : (
                                        'Create'
                                    )}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

WarehouseCreate.layout = {
    breadcrumbs: [
        {
            title: 'Warehouses',
            href: indexWarehousesRoute().url,
        },
        {
            title: 'Create',
            href: '',
        },
    ],
};
