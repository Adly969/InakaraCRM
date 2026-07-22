import { useSyncExternalStore } from 'react';

export type ResolvedAppearance = 'light' | 'dark';
export type Appearance = ResolvedAppearance | 'system';

export type UseAppearanceReturn = {
    readonly appearance: Appearance;
    readonly resolvedAppearance: ResolvedAppearance;
    readonly updateAppearance: (mode: Appearance) => void;
};

const listeners = new Set<() => void>();
let currentAppearance: Appearance = 'system';

const prefersDark = (): boolean => {
    if (typeof window === 'undefined') {
        return false;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches;
};

const setCookie = (name: string, value: string, days = 365): void => {
    if (typeof document === 'undefined') {
        return;
    }

    const maxAge = days * 24 * 60 * 60;
    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};

const getStoredAppearance = (): Appearance => {
    if (typeof window === 'undefined') {
        return 'system';
    }

    return (localStorage.getItem('appearance') as Appearance) || 'system';
};

const isDarkMode = (appearance: Appearance): boolean => {
    return appearance === 'dark' || (appearance === 'system' && prefersDark());
};

const applyPrimaryColor = (color: string | null): void => {
    if (typeof document === 'undefined') {
        return;
    }

    const root = document.documentElement;
    const isDark = root.classList.contains('dark');

    // Color definitions corresponding to Blue, Indigo, Slate, Emerald, Custom
    let primary = isDark ? 'oklch(0.65 0.15 260)' : 'oklch(0.6 0.17 260)'; // Blue

    if (color === 'indigo') {
        primary = isDark ? 'oklch(0.55 0.2 280)' : 'oklch(0.5 0.2 280)';
    } else if (color === 'slate') {
        primary = isDark ? 'oklch(0.5 0.05 240)' : 'oklch(0.45 0.05 240)';
    } else if (color === 'emerald') {
        primary = isDark ? 'oklch(0.55 0.15 150)' : 'oklch(0.6 0.15 150)';
    } else if (color === 'custom') {
        primary = isDark ? 'oklch(0.55 0.25 20)' : 'oklch(0.6 0.25 20)'; // Warm Red/Orange
    }

    root.style.setProperty('--primary', primary);
    root.style.setProperty('--sidebar-primary', primary);
};

const applyTheme = (appearance: Appearance): void => {
    if (typeof document === 'undefined') {
        return;
    }

    const isDark = isDarkMode(appearance);

    document.documentElement.classList.toggle('dark', isDark);
    document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';

    // Apply primary color variable updates on theme changes
    const storedColor = localStorage.getItem('primary_color');
    applyPrimaryColor(storedColor);
};

const subscribe = (callback: () => void) => {
    listeners.add(callback);

    return () => listeners.delete(callback);
};

const notify = (): void => listeners.forEach((listener) => listener());

const mediaQuery = (): MediaQueryList | null => {
    if (typeof window === 'undefined') {
        return null;
    }

    return window.matchMedia('(prefers-color-scheme: dark)');
};

const handleSystemThemeChange = (): void => applyTheme(currentAppearance);

export function initializeTheme(): void {
    if (typeof window === 'undefined') {
        return;
    }

    if (!localStorage.getItem('appearance')) {
        localStorage.setItem('appearance', 'system');
        setCookie('appearance', 'system');
    }

    currentAppearance = getStoredAppearance();
    applyTheme(currentAppearance);

    // Set up system theme change listener
    mediaQuery()?.addEventListener('change', handleSystemThemeChange);
}

export function useAppearance(): UseAppearanceReturn {
    const appearance: Appearance = useSyncExternalStore(
        subscribe,
        () => currentAppearance,
        () => 'system',
    );

    const resolvedAppearance: ResolvedAppearance = isDarkMode(appearance)
        ? 'dark'
        : 'light';

    const updateAppearance = (mode: Appearance): void => {
        currentAppearance = mode;

        // Store in localStorage for client-side persistence...
        localStorage.setItem('appearance', mode);

        // Store in cookie for SSR...
        setCookie('appearance', mode);

        applyTheme(mode);
        notify();
    };

    return { appearance, resolvedAppearance, updateAppearance } as const;
}
