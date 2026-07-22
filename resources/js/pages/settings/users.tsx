import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import InputError from '@/components/input-error';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';

type UserRecord = {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    role: string;
    is_active: boolean;
};

type RoleOption = {
    value: string;
    label: string;
};

type Props = {
    users: {
        data: UserRecord[];
        links: any[];
        current_page: number;
        last_page: number;
    };
    roles: RoleOption[];
};

export default function UsersSettings({ users, roles }: Props) {
    const [isDialogOpen, setIsDialogOpen] = useState(false);
    const [editingUser, setEditingUser] = useState<UserRecord | null>(null);

    const { data, setData, post, put, delete: destroy, processing, errors, reset, clearErrors } = useForm({
        name: '',
        email: '',
        phone: '',
        password: '',
        role: 'sales',
        is_active: true,
    });

    const openCreateDialog = () => {
        setEditingUser(null);
        reset();
        clearErrors();
        setIsDialogOpen(true);
    };

    const openEditDialog = (user: UserRecord) => {
        setEditingUser(user);
        setData({
            name: user.name,
            email: user.email,
            phone: user.phone || '',
            password: '',
            role: user.role,
            is_active: user.is_active,
        });
        clearErrors();
        setIsDialogOpen(true);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (editingUser) {
            put(`/settings/users/${editingUser.id}`, {
                onSuccess: () => {
                    setIsDialogOpen(false);
                    reset();
                },
            });
        } else {
            post('/settings/users', {
                onSuccess: () => {
                    setIsDialogOpen(false);
                    reset();
                },
            });
        }
    };

    const handleDelete = (user: UserRecord) => {
        if (confirm(`Are you sure you want to deactivate or remove ${user.name}?`)) {
            destroy(`/settings/users/${user.id}`);
        }
    };

    return (
        <>
            <Head title="Users settings" />

            <h1 className="sr-only">Users settings</h1>

            <div className="space-y-6">
                <div className="flex items-center justify-between gap-4 border-b border-neutral-200 dark:border-neutral-800 pb-4">
                    <div>
                        <h2 className="text-lg font-semibold text-neutral-900 dark:text-neutral-100">User Management</h2>
                        <p className="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400">
                            Manage user accounts and permission roles for your tenant.
                        </p>
                    </div>
                    <Button onClick={openCreateDialog} size="sm">
                        Add User
                    </Button>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {users.data.map((user) => (
                        <Card key={user.id} className="overflow-hidden border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 shadow-sm rounded-xl">
                            <CardContent className="p-6 space-y-4">
                                {/* Top Header Section */}
                                <div className="flex items-center justify-between gap-4">
                                    <div className="flex items-center gap-3">
                                        <div className="w-12 h-12 rounded-full bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center font-bold text-neutral-700 dark:text-neutral-300 text-lg shrink-0">
                                            {user.name.charAt(0).toUpperCase()}
                                        </div>
                                        <div className="min-w-0">
                                            <h3 className="font-bold text-neutral-900 dark:text-neutral-100 text-base leading-snug">{user.name}</h3>
                                            <span className="text-xs text-neutral-500 dark:text-neutral-400 block mt-0.5">{user.email}</span>
                                        </div>
                                    </div>
                                    <span className={`inline-flex items-center rounded px-2.5 py-0.5 text-xs font-bold shrink-0 ${
                                        user.is_active
                                            ? 'bg-[#e9f2ff] text-[#0066cc] dark:bg-blue-950/40 dark:text-blue-400'
                                            : 'bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400'
                                    }`}>
                                        {user.is_active ? 'ACTIVE' : 'INACTIVE'}
                                    </span>
                                </div>

                                {/* Body Information Fields */}
                                <div className="space-y-4 pt-2">
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm text-neutral-500 dark:text-neutral-400">Role:</span>
                                        <span className="inline-flex items-center rounded bg-neutral-100 dark:bg-neutral-800 px-3 py-1 text-xs font-bold uppercase text-neutral-800 dark:text-neutral-200">
                                            {user.role}
                                        </span>
                                    </div>
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm text-neutral-500 dark:text-neutral-400">Phone:</span>
                                        <span className="text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                            {user.phone || '-'}
                                        </span>
                                    </div>
                                </div>

                                {/* Horizontal Divider */}
                                <div className="border-t border-neutral-100 dark:border-neutral-800 pt-4" />

                                {/* Centered Actions */}
                                <div className="flex items-center justify-center gap-8">
                                    <Button
                                        variant="outline"
                                        onClick={() => openEditDialog(user)}
                                        className="px-6 border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-700 text-sm font-medium text-neutral-700 dark:text-neutral-355 h-9 rounded"
                                    >
                                        Edit
                                    </Button>
                                    <button
                                        type="button"
                                        onClick={() => handleDelete(user)}
                                        className="text-[#d93838] hover:text-red-700 font-semibold text-sm transition-colors"
                                    >
                                        Deactivate
                                    </button>
                                </div>
                            </CardContent>
                        </Card>
                    ))}

                    {users.data.length === 0 && (
                        <div className="col-span-full text-center py-12 border border-dashed rounded-lg text-neutral-500 dark:text-neutral-400 bg-white dark:bg-neutral-900/50">
                            No users found in this tenant organization.
                        </div>
                    )}
                </div>
            </div>

            <Dialog open={isDialogOpen} onOpenChange={setIsDialogOpen}>
                <DialogContent className="sm:max-w-[425px] border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-950 shadow-lg">
                    <DialogHeader>
                        <DialogTitle className="text-lg font-bold text-neutral-900 dark:text-neutral-100">
                            {editingUser ? 'Edit User details' : 'Add new User'}
                        </DialogTitle>
                        <DialogDescription className="text-sm text-neutral-500 dark:text-neutral-400">
                            {editingUser
                                ? 'Update details and permission roles for this user account.'
                                : 'Create a new user account mapped to your organization tenant.'}
                        </DialogDescription>
                    </DialogHeader>

                    <form onSubmit={handleSubmit} className="space-y-4 py-4">
                        <div className="grid gap-2">
                            <Label htmlFor="name" className="text-neutral-700 dark:text-neutral-300 font-medium">Full Name</Label>
                            <Input
                                id="name"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                placeholder="E.g., John Doe"
                                className="w-full"
                                required
                            />
                            <InputError message={errors.name} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="email" className="text-neutral-700 dark:text-neutral-300 font-medium">Email Address</Label>
                            <Input
                                id="email"
                                type="email"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                placeholder="john.doe@company.com"
                                className="w-full"
                                required
                            />
                            <InputError message={errors.email} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="phone" className="text-neutral-700 dark:text-neutral-300 font-medium">Phone Number (Optional)</Label>
                            <Input
                                id="phone"
                                value={data.phone}
                                onChange={(e) => setData('phone', e.target.value)}
                                placeholder="+628123456789"
                                className="w-full"
                            />
                            <InputError message={errors.phone} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="password" className="text-neutral-700 dark:text-neutral-300 font-medium">
                                {editingUser ? 'New Password (Leave blank to keep current)' : 'Password'}
                            </Label>
                            <Input
                                id="password"
                                type="password"
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                placeholder="••••••••"
                                className="w-full"
                                required={!editingUser}
                            />
                            <InputError message={errors.password} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="role" className="text-neutral-700 dark:text-neutral-300 font-medium">Assign Role</Label>
                            <select
                                id="role"
                                value={data.role}
                                onChange={(e) => setData('role', e.target.value)}
                                className="w-full h-10 px-3 rounded-md border border-neutral-250 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-neutral-100"
                                required
                            >
                                {roles.map((role) => (
                                    <option key={role.value} value={role.value}>
                                        {role.label}
                                    </option>
                                ))}
                            </select>
                            <InputError message={errors.role} />
                        </div>

                        {editingUser && (
                            <div className="flex items-center space-x-2 pt-2">
                                <input
                                    type="checkbox"
                                    id="is_active"
                                    checked={data.is_active}
                                    onChange={(e) => setData('is_active', e.target.checked)}
                                    className="h-4 w-4 rounded border-neutral-350 text-indigo-600 focus:ring-indigo-500"
                                />
                                <Label htmlFor="is_active" className="text-neutral-700 dark:text-neutral-300 font-medium cursor-pointer">
                                    User is Active
                                </Label>
                            </div>
                        )}

                        <DialogFooter className="pt-6 gap-2">
                            <Button type="button" variant="outline" onClick={() => setIsDialogOpen(false)}>
                                Cancel
                            </Button>
                            <Button type="submit" disabled={processing} className="bg-indigo-600 hover:bg-indigo-700 text-white">
                                {editingUser ? 'Save changes' : 'Create account'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </>
    );
}
