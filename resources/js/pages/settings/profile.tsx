import { Head, useForm, usePage } from '@inertiajs/react';
import { useRef, useState } from 'react';
import { Mail, Upload } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent } from '@/components/ui/card';
import InputError from '@/components/input-error';
import { edit } from '@/routes/profile';
import type { Auth } from '@/types';

type PageProps = {
    auth: Auth;
};

export default function Profile() {
    const { auth } = usePage<PageProps>().props;
    const fileInputRef = useRef<HTMLInputElement>(null);

    const { data, setData, post, processing, errors, reset } = useForm({
        _method: 'PATCH',
        name: auth.user.name,
        email: auth.user.email,
        phone: auth.user.phone || '',
        title: auth.user.title || '',
        bio: auth.user.bio || '',
        avatar: null as File | null,
        remove_avatar: false,
    });

    const [previewUrl, setPreviewUrl] = useState<string | null>(auth.user.avatar || null);

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            setData('avatar', file);
            setData('remove_avatar', false);
            const reader = new FileReader();
            reader.onloadend = () => {
                setPreviewUrl(reader.result as string);
            };
            reader.readAsDataURL(file);
        }
    };

    const triggerFileSelect = () => {
        fileInputRef.current?.click();
    };

    const handleRemovePhoto = () => {
        setData('avatar', null);
        setData('remove_avatar', true);
        setPreviewUrl(null);
        if (fileInputRef.current) {
            fileInputRef.current.value = '';
        }
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/settings/profile', {
            preserveScroll: true,
        });
    };

    return (
        <>
            <Head title="Profile settings" />

            <h1 className="sr-only">Profile settings</h1>

            <div className="space-y-6">
                <div>
                    <h2 className="text-xl font-bold text-neutral-900 dark:text-neutral-100">Personal Information</h2>
                    <p className="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
                        Update your profile photo and personal details here. This information will be displayed across the CRM.
                    </p>
                </div>

                <form onSubmit={handleSubmit}>
                    <Card className="border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 rounded-xl overflow-hidden shadow-sm">
                        <CardContent className="p-0 divide-y divide-neutral-200 dark:divide-neutral-800">
                            {/* Profile Picture Section */}
                            <div className="p-6 grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                                <div className="md:col-span-4 space-y-1">
                                    <h3 className="text-sm font-semibold text-neutral-800 dark:text-neutral-200">Profile Picture</h3>
                                    <p className="text-xs text-neutral-500 dark:text-neutral-450 leading-relaxed">
                                        Recommended size: 400×400px.<br />Max file size: 5MB.
                                    </p>
                                </div>
                                <div className="md:col-span-8 flex items-center gap-4">
                                    {previewUrl ? (
                                        <img
                                            src={previewUrl}
                                            alt={auth.user.name}
                                            className="w-16 h-16 rounded-full object-cover border border-neutral-200 dark:border-neutral-800"
                                        />
                                    ) : (
                                        <div className="w-16 h-16 rounded-full bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center font-bold text-neutral-700 dark:text-neutral-350 text-xl border border-neutral-200 dark:border-neutral-800">
                                            {auth.user.name.charAt(0).toUpperCase()}
                                        </div>
                                    )}

                                    <input
                                        type="file"
                                        ref={fileInputRef}
                                        className="hidden"
                                        accept="image/*"
                                        onChange={handleFileChange}
                                    />

                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={triggerFileSelect}
                                        className="flex items-center gap-2 text-neutral-700 dark:text-neutral-300 border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-700 h-9 px-4 text-xs font-semibold"
                                    >
                                        <Upload className="h-4.5 w-4.5" />
                                        Change photo
                                    </Button>

                                    <button
                                        type="button"
                                        onClick={handleRemovePhoto}
                                        className="text-[#d93838] hover:text-red-700 font-semibold text-xs ml-2"
                                    >
                                        Remove
                                    </button>
                                </div>
                                <div className="md:col-span-12">
                                    <InputError message={errors.avatar} />
                                </div>
                            </div>

                            {/* Full Name */}
                            <div className="p-6 grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                                <Label htmlFor="name" className="md:col-span-4 text-sm font-semibold text-neutral-800 dark:text-neutral-200">
                                    Full Name
                                </Label>
                                <div className="md:col-span-8 w-full">
                                    <Input
                                        id="name"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        className="w-full max-w-xl"
                                        required
                                        placeholder="Full name"
                                    />
                                    <InputError message={errors.name} />
                                </div>
                            </div>

                            {/* Email Address */}
                            <div className="p-6 grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                                <Label htmlFor="email" className="md:col-span-4 text-sm font-semibold text-neutral-800 dark:text-neutral-200">
                                    Email Address
                                </Label>
                                <div className="md:col-span-8 w-full">
                                    <div className="relative w-full max-w-xl">
                                        <Mail className="absolute left-3 top-3 h-4 w-4 text-neutral-400 dark:text-neutral-500" />
                                        <Input
                                            id="email"
                                            type="email"
                                            value={data.email}
                                            onChange={(e) => setData('email', e.target.value)}
                                            className="w-full pl-10"
                                            required
                                            placeholder="Email address"
                                        />
                                    </div>
                                    <InputError message={errors.email} />
                                </div>
                            </div>

                            {/* Phone Number */}
                            <div className="p-6 grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                                <Label htmlFor="phone" className="md:col-span-4 text-sm font-semibold text-neutral-800 dark:text-neutral-200">
                                    Phone Number
                                </Label>
                                <div className="md:col-span-8 w-full">
                                    <Input
                                        id="phone"
                                        value={data.phone}
                                        onChange={(e) => setData('phone', e.target.value)}
                                        className="w-full max-w-xl"
                                        placeholder="+1 (555) 000-0000"
                                    />
                                    <InputError message={errors.phone} />
                                </div>
                            </div>

                            {/* Role / Title */}
                            <div className="p-6 grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                                <Label htmlFor="title" className="md:col-span-4 text-sm font-semibold text-neutral-800 dark:text-neutral-200">
                                    Role / Title
                                </Label>
                                <div className="md:col-span-8 w-full">
                                    <Input
                                        id="title"
                                        value={data.title}
                                        onChange={(e) => setData('title', e.target.value)}
                                        className="w-full max-w-xl"
                                        placeholder="E.g., Administrator"
                                    />
                                    <InputError message={errors.title} />
                                </div>
                            </div>

                            {/* Bio */}
                            <div className="p-6 grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
                                <div className="md:col-span-4 space-y-1">
                                    <Label htmlFor="bio" className="text-sm font-semibold text-neutral-800 dark:text-neutral-200">
                                        Bio
                                    </Label>
                                    <p className="text-xs text-neutral-550 dark:text-neutral-450 leading-relaxed">
                                        Brief description for your internal profile.
                                    </p>
                                </div>
                                <div className="md:col-span-8 w-full">
                                    <textarea
                                        id="bio"
                                        value={data.bio}
                                        onChange={(e) => setData('bio', e.target.value.slice(0, 500))}
                                        rows={4}
                                        className="flex min-h-[100px] w-full max-w-xl rounded-md border border-neutral-300 dark:border-neutral-800 bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-neutral-100"
                                        placeholder="Write a few sentences about yourself..."
                                    />
                                    <div className="text-right text-xs text-neutral-500 dark:text-neutral-400 mt-1 max-w-xl">
                                        {data.bio.length} / 500 characters
                                    </div>
                                    <InputError message={errors.bio} />
                                </div>
                            </div>

                            {/* Form Footer */}
                            <div className="p-6 flex items-center justify-end gap-4 bg-neutral-50/50 dark:bg-neutral-900/10">
                                <button
                                    type="button"
                                    onClick={() => reset()}
                                    className="text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-250 text-sm font-medium"
                                >
                                    Cancel
                                </button>
                                <Button
                                    type="submit"
                                    disabled={processing}
                                    className="bg-[#0052cc] hover:bg-[#0747a6] text-white px-5 py-2 font-semibold text-sm rounded-md"
                                >
                                    Save Changes
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </form>
            </div>
        </>
    );
}

Profile.layout = {
    breadcrumbs: [
        {
            title: 'Profile settings',
            href: edit(),
        },
    ],
};
