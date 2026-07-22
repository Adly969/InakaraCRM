import { Head } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import { Check, Plus } from 'lucide-react';
import { edit as editAppearance } from '@/routes/appearance';
import { useAppearance } from '@/hooks/use-appearance';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { toast } from 'sonner';

export default function Appearance() {
    const { appearance, updateAppearance } = useAppearance();

    // Persist local custom states for Primary Color & Sidebar Style
    const [primaryColor, setPrimaryColor] = useState('blue');
    const [sidebarStyle, setSidebarStyle] = useState('standard');

    const applyPrimaryColor = (color: string) => {
        if (typeof document === 'undefined') return;
        const root = document.documentElement;
        const isDark = root.classList.contains('dark');
        
        let primary = isDark ? 'oklch(0.65 0.15 260)' : 'oklch(0.6 0.17 260)'; // Blue

        if (color === 'indigo') {
            primary = isDark ? 'oklch(0.55 0.2 280)' : 'oklch(0.5 0.2 280)';
        } else if (color === 'slate') {
            primary = isDark ? 'oklch(0.5 0.05 240)' : 'oklch(0.45 0.05 240)';
        } else if (color === 'emerald') {
            primary = isDark ? 'oklch(0.55 0.15 150)' : 'oklch(0.6 0.15 150)';
        } else if (color === 'custom') {
            primary = isDark ? 'oklch(0.55 0.25 20)' : 'oklch(0.6 0.25 20)'; // Red/Orange
        }

        root.style.setProperty('--primary', primary);
        root.style.setProperty('--sidebar-primary', primary);
    };

    useEffect(() => {
        const storedColor = localStorage.getItem('primary_color');
        if (storedColor) {
            setPrimaryColor(storedColor);
            applyPrimaryColor(storedColor);
        }
        const storedSidebar = localStorage.getItem('sidebar_style');
        if (storedSidebar) {
            setSidebarStyle(storedSidebar);
        }
    }, []);

    const handleColorChange = (color: string) => {
        setPrimaryColor(color);
        localStorage.setItem('primary_color', color);
        applyPrimaryColor(color);
        toast.success(`Primary color theme updated to ${color}.`);
    };

    const handleSidebarChange = (style: string) => {
        setSidebarStyle(style);
        localStorage.setItem('sidebar_style', style);
        window.dispatchEvent(new Event('sidebar-style-changed'));
        toast.success(`Sidebar style layout updated to ${style}.`);
    };

    const handleResetDefaults = () => {
        updateAppearance('system');
        setPrimaryColor('blue');
        localStorage.setItem('primary_color', 'blue');
        applyPrimaryColor('blue');
        setSidebarStyle('standard');
        localStorage.setItem('sidebar_style', 'standard');
        window.dispatchEvent(new Event('sidebar-style-changed'));
        toast.success('Appearance settings restored to default values.');
    };

    return (
        <>
            <Head title="Appearance settings" />

            <h1 className="sr-only">Appearance settings</h1>

            <div className="space-y-6">
                <div>
                    <h2 className="text-xl font-bold text-neutral-900 dark:text-neutral-100">Interface Appearance</h2>
                    <p className="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
                        Customize the look and feel of your CRM workspace.
                    </p>
                </div>

                {/* Theme Preference Card */}
                <Card className="border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 rounded-xl overflow-hidden shadow-sm p-6">
                    <h3 className="text-sm font-bold text-neutral-900 dark:text-neutral-100 mb-4">Theme Preference</h3>
                    
                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        {/* Light Option */}
                        <div
                            onClick={() => updateAppearance('light')}
                            className="space-y-3 cursor-pointer group"
                        >
                            <div className={`aspect-4/3 rounded-lg border-2 p-2 flex gap-1.5 transition-all ${
                                appearance === 'light'
                                    ? 'border-indigo-650 ring-2 ring-indigo-500/20 bg-neutral-50'
                                    : 'border-neutral-200 dark:border-neutral-800 bg-neutral-50/50 hover:border-neutral-300 dark:hover:border-neutral-700'
                            }`}>
                                <div className="w-1/4 rounded bg-white border border-neutral-200 flex flex-col gap-1 p-1">
                                    <div className="h-2 w-full rounded-sm bg-neutral-200" />
                                    <div className="h-1.5 w-3/4 rounded-sm bg-neutral-150" />
                                    <div className="h-1.5 w-1/2 rounded-sm bg-neutral-150" />
                                </div>
                                <div className="flex-1 rounded bg-white border border-neutral-200 p-1 flex flex-col gap-1.5">
                                    <div className="h-3 w-1/3 rounded-sm bg-neutral-200" />
                                    <div className="grid grid-cols-2 gap-1.5">
                                        <div className="h-10 rounded bg-neutral-50 border border-neutral-150" />
                                        <div className="h-10 rounded bg-neutral-50 border border-neutral-150" />
                                    </div>
                                </div>
                            </div>
                            <div className="flex items-center gap-2">
                                <input
                                    type="radio"
                                    name="theme"
                                    checked={appearance === 'light'}
                                    onChange={() => updateAppearance('light')}
                                    className="h-4 w-4 rounded-full border-neutral-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                                />
                                <Label className="text-sm font-semibold text-neutral-800 dark:text-neutral-200 cursor-pointer">
                                    Light
                                </Label>
                            </div>
                        </div>

                        {/* Dark Option */}
                        <div
                            onClick={() => updateAppearance('dark')}
                            className="space-y-3 cursor-pointer group"
                        >
                            <div className={`aspect-4/3 rounded-lg border-2 p-2 flex gap-1.5 transition-all ${
                                appearance === 'dark'
                                    ? 'border-indigo-650 ring-2 ring-indigo-500/20 bg-neutral-950'
                                    : 'border-neutral-200 dark:border-neutral-800 bg-neutral-900/50 hover:border-neutral-300 dark:hover:border-neutral-700'
                            }`}>
                                <div className="w-1/4 rounded bg-neutral-900 border border-neutral-850 flex flex-col gap-1 p-1">
                                    <div className="h-2 w-full rounded-sm bg-neutral-800" />
                                    <div className="h-1.5 w-3/4 rounded-sm bg-neutral-850" />
                                    <div className="h-1.5 w-1/2 rounded-sm bg-neutral-850" />
                                </div>
                                <div className="flex-1 rounded bg-neutral-900 border border-neutral-850 p-1 flex flex-col gap-1.5">
                                    <div className="h-3 w-1/3 rounded-sm bg-neutral-800" />
                                    <div className="grid grid-cols-2 gap-1.5">
                                        <div className="h-10 rounded bg-neutral-950 border border-neutral-850" />
                                        <div className="h-10 rounded bg-neutral-950 border border-neutral-850" />
                                    </div>
                                </div>
                            </div>
                            <div className="flex items-center gap-2">
                                <input
                                    type="radio"
                                    name="theme"
                                    checked={appearance === 'dark'}
                                    onChange={() => updateAppearance('dark')}
                                    className="h-4 w-4 rounded-full border-neutral-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                                />
                                <Label className="text-sm font-semibold text-neutral-800 dark:text-neutral-200 cursor-pointer">
                                    Dark
                                </Label>
                            </div>
                        </div>

                        {/* System Option */}
                        <div
                            onClick={() => updateAppearance('system')}
                            className="space-y-3 cursor-pointer group"
                        >
                            <div className={`aspect-4/3 rounded-lg border-2 p-2 flex gap-1.5 transition-all ${
                                appearance === 'system'
                                    ? 'border-indigo-650 ring-2 ring-indigo-500/20 bg-neutral-50/50 dark:bg-neutral-950/50'
                                    : 'border-neutral-200 dark:border-neutral-800 bg-neutral-100/30 hover:border-neutral-300 dark:hover:border-neutral-700'
                            }`}>
                                {/* Half light, half dark */}
                                <div className="w-full flex rounded overflow-hidden border border-neutral-200 dark:border-neutral-800">
                                    {/* Light side */}
                                    <div className="w-1/2 bg-white p-1.5 flex gap-1">
                                        <div className="w-1/3 rounded bg-neutral-50 border border-neutral-200 flex flex-col gap-0.5 p-0.5">
                                            <div className="h-1 w-full rounded-sm bg-neutral-200" />
                                            <div className="h-1 w-2/3 rounded-sm bg-neutral-150" />
                                        </div>
                                        <div className="flex-1 rounded bg-neutral-50 border border-neutral-200 p-0.5">
                                            <div className="h-8 rounded bg-white border border-neutral-150" />
                                        </div>
                                    </div>
                                    {/* Dark side */}
                                    <div className="w-1/2 bg-neutral-950 p-1.5 flex gap-1 border-l border-neutral-250 dark:border-neutral-850">
                                        <div className="w-1/3 rounded bg-neutral-900 border border-neutral-850 flex flex-col gap-0.5 p-0.5">
                                            <div className="h-1 w-full rounded-sm bg-neutral-800" />
                                            <div className="h-1 w-2/3 rounded-sm bg-neutral-850" />
                                        </div>
                                        <div className="flex-1 rounded bg-neutral-900 border border-neutral-850 p-0.5">
                                            <div className="h-8 rounded bg-neutral-950 border border-neutral-850" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div className="flex items-center gap-2">
                                <input
                                    type="radio"
                                    name="theme"
                                    checked={appearance === 'system'}
                                    onChange={() => updateAppearance('system')}
                                    className="h-4 w-4 rounded-full border-neutral-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                                />
                                <Label className="text-sm font-semibold text-neutral-800 dark:text-neutral-200 cursor-pointer">
                                    System Auto
                                </Label>
                            </div>
                        </div>
                    </div>
                </Card>

                {/* Primary Color & Sidebar Style in 2 columns */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {/* Primary Color Card */}
                    <Card className="border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 rounded-xl overflow-hidden shadow-sm p-6">
                        <h3 className="text-sm font-bold text-neutral-900 dark:text-neutral-100">Primary Color</h3>
                        <p className="text-xs text-neutral-500 mt-1 mb-4">Accent color used for active states and buttons.</p>

                        <div className="flex flex-wrap items-center gap-4">
                            {/* Blue */}
                            <button
                                type="button"
                                onClick={() => handleColorChange('blue')}
                                className="w-10 h-10 rounded-full bg-[#0052cc] flex items-center justify-center text-white cursor-pointer relative"
                            >
                                {primaryColor === 'blue' && <Check className="h-5 w-5 stroke-3" />}
                            </button>

                            {/* Indigo */}
                            <button
                                type="button"
                                onClick={() => handleColorChange('indigo')}
                                className="w-10 h-10 rounded-full bg-[#4f46e5] flex items-center justify-center text-white cursor-pointer relative"
                            >
                                {primaryColor === 'indigo' && <Check className="h-5 w-5 stroke-3" />}
                            </button>

                            {/* Slate */}
                            <button
                                type="button"
                                onClick={() => handleColorChange('slate')}
                                className="w-10 h-10 rounded-full bg-[#475569] flex items-center justify-center text-white cursor-pointer relative"
                            >
                                {primaryColor === 'slate' && <Check className="h-5 w-5 stroke-3" />}
                            </button>

                            {/* Emerald */}
                            <button
                                type="button"
                                onClick={() => handleColorChange('emerald')}
                                className="w-10 h-10 rounded-full bg-[#059669] flex items-center justify-center text-white cursor-pointer relative"
                            >
                                {primaryColor === 'emerald' && <Check className="h-5 w-5 stroke-3" />}
                            </button>

                            {/* Custom */}
                            <button
                                type="button"
                                onClick={() => handleColorChange('custom')}
                                className="w-10 h-10 rounded-full border-2 border-dashed border-neutral-300 hover:border-neutral-400 flex items-center justify-center text-neutral-500 cursor-pointer relative"
                            >
                                {primaryColor === 'custom' ? (
                                    <Check className="h-5 w-5 text-neutral-700 stroke-3" />
                                ) : (
                                    <Plus className="h-5 w-5" />
                                )}
                            </button>
                        </div>
                    </Card>

                    {/* Sidebar Style Card */}
                    <Card className="border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 rounded-xl overflow-hidden shadow-sm p-6">
                        <h3 className="text-sm font-bold text-neutral-900 dark:text-neutral-100">Sidebar Style</h3>
                        <p className="text-xs text-neutral-500 mt-1 mb-4">Choose your preferred navigation layout.</p>

                        <div className="grid grid-cols-2 gap-4">
                            {/* Standard */}
                            <div
                                onClick={() => handleSidebarChange('standard')}
                                className="space-y-3 cursor-pointer group"
                            >
                                <div className={`aspect-4/3 rounded-lg border-2 p-1.5 flex gap-1.5 bg-neutral-50/50 dark:bg-neutral-950/20 transition-all ${
                                    sidebarStyle === 'standard'
                                        ? 'border-indigo-650 ring-2 ring-indigo-500/20'
                                        : 'border-neutral-200 dark:border-neutral-800 hover:border-neutral-350 dark:hover:border-neutral-700'
                                }`}>
                                    <div className="w-1/3 rounded bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800" />
                                    <div className="flex-1 rounded bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800" />
                                </div>
                                <div className="flex items-center gap-2">
                                    <input
                                        type="radio"
                                        name="sidebar"
                                        checked={sidebarStyle === 'standard'}
                                        onChange={() => handleSidebarChange('standard')}
                                        className="h-4 w-4 rounded-full border-neutral-350 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                                    />
                                    <Label className="text-xs font-semibold text-neutral-800 dark:text-neutral-200 cursor-pointer">
                                        Standard
                                    </Label>
                                </div>
                            </div>

                            {/* Compact Icons */}
                            <div
                                onClick={() => handleSidebarChange('compact')}
                                className="space-y-3 cursor-pointer group"
                            >
                                <div className={`aspect-4/3 rounded-lg border-2 p-1.5 flex gap-1.5 bg-neutral-50/50 dark:bg-neutral-950/20 transition-all ${
                                    sidebarStyle === 'compact'
                                        ? 'border-indigo-650 ring-2 ring-indigo-500/20'
                                        : 'border-neutral-200 dark:border-neutral-800 hover:border-neutral-350 dark:hover:border-neutral-700'
                                }`}>
                                    <div className="w-1/8 rounded bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 shrink-0" />
                                    <div className="flex-1 rounded bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800" />
                                </div>
                                <div className="flex items-center gap-2">
                                    <input
                                        type="radio"
                                        name="sidebar"
                                        checked={sidebarStyle === 'compact'}
                                        onChange={() => handleSidebarChange('compact')}
                                        className="h-4 w-4 rounded-full border-neutral-350 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                                    />
                                    <Label className="text-xs font-semibold text-neutral-800 dark:text-neutral-200 cursor-pointer">
                                        Compact Icons
                                    </Label>
                                </div>
                            </div>
                        </div>
                    </Card>
                </div>

                {/* Reset to Defaults Footer */}
                <div className="flex justify-end pt-6 border-t border-neutral-100 dark:border-neutral-800 mt-6">
                    <Button
                        type="button"
                        variant="outline"
                        onClick={handleResetDefaults}
                        className="px-6 border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-850 hover:bg-neutral-50 dark:hover:bg-neutral-800 text-sm font-semibold text-neutral-700 dark:text-neutral-300"
                    >
                        Reset to Defaults
                    </Button>
                </div>
            </div>
        </>
    );
}

Appearance.layout = {
    breadcrumbs: [
        {
            title: 'Appearance settings',
            href: editAppearance(),
        },
    ],
};
