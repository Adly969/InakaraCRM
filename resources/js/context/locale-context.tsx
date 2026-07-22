import React, { createContext, useContext, useEffect, useState } from 'react';
import { Locale, translations } from '@/lib/i18n';

interface LocaleContextType {
    locale: Locale;
    setLocale: (locale: Locale) => void;
    t: (key: keyof typeof translations['id'], fallback?: string) => string;
}

const LocaleContext = createContext<LocaleContextType | undefined>(undefined);

export const LocaleProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
    const [locale, setLocaleState] = useState<Locale>('id');

    useEffect(() => {
        const savedLocale = localStorage.getItem('inakara_crm_locale') as Locale;
        if (savedLocale && (savedLocale === 'id' || savedLocale === 'en')) {
            setLocaleState(savedLocale);
        }
    }, []);

    const setLocale = (newLocale: Locale) => {
        setLocaleState(newLocale);
        localStorage.setItem('inakara_crm_locale', newLocale);
    };

    const t = (key: keyof typeof translations['id'], fallback?: string): string => {
        const dict = translations[locale] || translations['id'];
        return dict[key] || fallback || key;
    };

    return (
        <LocaleContext.Provider value={{ locale, setLocale, t }}>
            {children}
        </LocaleContext.Provider>
    );
};

export const useLocale = (): LocaleContextType => {
    const context = useContext(LocaleContext);
    if (!context) {
        // Fallback if not wrapped in provider
        return {
            locale: 'id',
            setLocale: () => {},
            t: (key, fallback) => translations['id'][key] || fallback || key,
        };
    }
    return context;
};
