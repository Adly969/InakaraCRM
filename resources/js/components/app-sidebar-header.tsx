import React, { useState, useEffect, useRef } from 'react';
import { usePage, router } from '@inertiajs/react';
import { Bell, Clock, Search, Sparkles, User, FileText, Settings, Shield, ShieldAlert, CheckCircle, Info, ChevronRight, Globe, Check } from 'lucide-react';
import { Breadcrumbs } from '@/components/breadcrumbs';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuLabel,
} from '@/components/ui/dropdown-menu';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { UserMenuContent } from '@/components/user-menu-content';
import { useInitials } from '@/hooks/use-initials';
import { useLocale } from '@/context/locale-context';
import { toast } from 'sonner';
import type { BreadcrumbItem as BreadcrumbItemType, Auth } from '@/types';

type PageProps = {
    auth: Auth;
};

interface NotificationItem {
    id: number;
    title: string;
    desc: string;
    time: string;
    type: 'error' | 'warning' | 'success' | 'info';
    read: boolean;
}

interface HistoryItem {
    id: number;
    action: string;
    target: string;
    time: string;
    url: string;
}

// Clean Vector Flag SVGs (Prevents Windows emoji letter fallback issue)
const IndonesiaFlag = () => (
    <svg className="w-4 h-3 rounded-xs shadow-xs object-cover shrink-0 border border-neutral-200/40" viewBox="0 0 640 480">
        <g fillRule="evenodd" strokeWidth="1pt">
            <path fill="#e70011" d="M0 0h640v240H0z"/>
            <path fill="#ffffff" d="M0 240h640v240H0z"/>
        </g>
    </svg>
);

const UKFlag = () => (
    <svg className="w-4 h-3 rounded-xs shadow-xs object-cover shrink-0 border border-neutral-200/40" viewBox="0 0 640 480">
        <path fill="#012169" d="M0 0h640v480H0z"/>
        <path fill="#fff" d="m75 0 245 180L565 0h75v55L400 240l240 185v55h-75L320 300 75 480H0v-55l240-185L0 55V0h75z"/>
        <path fill="#c8102e" d="m424 281 216 164v35h-46L378 316l46-35zm141-281 75 55v5L424 220l-46-35L565 0zM0 440l216-164 46 35L46 480H0v-40zM0 0h46l216 164-46 35L0 35V0z"/>
        <path fill="#fff" d="M240 0h160v480H240zM0 160h640v160H0z"/>
        <path fill="#c8102e" d="M267 0h106v480H267zM0 187h640v106H0z"/>
    </svg>
);

export function AppSidebarHeader({
    breadcrumbs = [],
}: {
    breadcrumbs?: BreadcrumbItemType[];
}) {
    const page = usePage<PageProps>();
    const { auth } = page.props;
    const getInitials = useInitials();
    const { locale, setLocale } = useLocale();
    
    // Command Palette state
    const [isSearchOpen, setIsSearchOpen] = useState(false);
    const [searchQuery, setSearchQuery] = useState('');

    // Notification State with active dummy data
    const [notifications, setNotifications] = useState<NotificationItem[]>([
        {
            id: 1,
            title: 'Peringatan Stok Kritis',
            desc: 'Gudang Jepara menipis untuk Meja Kopi Marmer Carrara (sisa 3 unit).',
            time: '10 menit lalu',
            type: 'error',
            read: false,
        },
        {
            id: 2,
            title: 'Faktur Jatuh Tempo',
            desc: 'Faktur INV/2026/003 untuk CV Seminyak Luxury Villa telah jatuh tempo 45 hari (Rp 120M).',
            time: '1 jam lalu',
            type: 'warning',
            read: false,
        },
        {
            id: 3,
            title: 'Penggabungan Klien Berhasil',
            desc: 'Pelanggan Mulia Resort Bali dan PT Mulia Resort & Villa telah digabungkan.',
            time: '2 jam lalu',
            type: 'success',
            read: true,
        },
        {
            id: 4,
            title: 'Sinkronisasi Sistem',
            desc: 'Accounting gateway berhasil terhubung dengan buku besar.',
            time: '1 hari lalu',
            type: 'info',
            read: true,
        },
    ]);

    // History logs State
    const [historyLogs] = useState<HistoryItem[]>([
        {
            id: 1,
            action: 'Prospek Baru Dibuat',
            target: 'Hendra Setiawan (Proyek Villa St. Regis Bali)',
            time: '5 menit lalu',
            url: '/leads',
        },
        {
            id: 2,
            action: 'Pembayaran Disetujui',
            target: 'PAY-2026-001 (Rp 150.000.000 - Mulia Resort)',
            time: '20 menit lalu',
            url: '/payments',
        },
        {
            id: 3,
            action: 'Stok Diperbarui',
            target: 'Gudang Jepara (Tempat Tidur Jati King)',
            time: '1 jam lalu',
            url: '/inventory',
        },
        {
            id: 4,
            action: 'Pengaturan Tema Diubah',
            target: 'Warna utama mode gelap',
            time: '3 jam lalu',
            url: '/settings/appearance',
        },
    ]);

    // Search Index Data for filtering
    const searchItems = [
        { title: 'Hendra Setiawan', category: 'Prospek (Leads)', desc: 'Proyek Perluasan St. Regis Bali Resort', url: '/leads' },
        { title: 'PT Mulia Resort & Villa Nusa Dua', category: 'Pelanggan (Customers)', desc: 'Klien resort bintang 5 Bali', url: '/customers' },
        { title: 'CV Seminyak Luxury Villa', category: 'Pelanggan (Customers)', desc: 'Pengembang villa mewah Seminyak', url: '/customers' },
        { title: 'INV/2026/001', category: 'Faktur (Invoices)', desc: 'Faktur Suite Villa Beachfront Mulia Resort (Rp 120M)', url: '/receivables' },
        { title: 'INV/2026/003', category: 'Faktur (Invoices)', desc: 'Faktur Jatuh Tempo - Seminyak Villa Dev (Rp 120M)', url: '/receivables' },
        { title: 'Kelola Pengguna', category: 'Pengaturan (Settings)', desc: 'Atur akun penyewa & hak akses peran', url: '/settings/users' },
        { title: 'Tema & Tampilan', category: 'Pengaturan (Settings)', desc: 'Sesuaikan mode gelap & warna', url: '/settings/appearance' },
        { title: 'Keamanan Akses', category: 'Pengaturan (Settings)', desc: 'Pengaturan kata sandi & 2FA', url: '/settings/security' },
        { title: 'Pusat Bantuan & Support', category: 'Bantuan (Support)', desc: 'Tiket bantuan & panduan sistem', url: '/support' },
    ];

    // Filtered search list
    const filteredSearch = searchItems.filter(item =>
        item.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
        item.category.toLowerCase().includes(searchQuery.toLowerCase()) ||
        item.desc.toLowerCase().includes(searchQuery.toLowerCase())
    );

    // Keyboard shortcut handler for Ctrl+K / Cmd+K
    useEffect(() => {
        const handleKeyDown = (e: KeyboardEvent) => {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                setIsSearchOpen(true);
            }
        };
        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, []);

    const handleNavigate = (url: string) => {
        setIsSearchOpen(false);
        setSearchQuery('');
        router.visit(url);
    };

    const handleMarkAllRead = () => {
        setNotifications(prev => prev.map(n => ({ ...n, read: true })));
        toast.success('All notifications marked as read.');
    };

    const toggleNotificationRead = (id: number) => {
        setNotifications(prev => prev.map(n => n.id === id ? { ...n, read: !n.read } : n));
    };

    const unreadCount = notifications.filter(n => !n.read).length;

    return (
        <>
            <header className="sticky top-0 z-20 bg-background/95 backdrop-blur-md flex h-16 shrink-0 items-center justify-between border-b border-sidebar-border/50 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4">
                <div className="flex items-center gap-2">
                    <SidebarTrigger className="-ml-1" />
                    <Breadcrumbs breadcrumbs={breadcrumbs} />
                </div>

                <div className="flex items-center gap-4">
                    {/* Interactive Global Search Input */}
                    <div 
                        onClick={() => setIsSearchOpen(true)}
                        className="relative w-48 lg:w-64 hidden sm:block cursor-pointer"
                    >
                        <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-neutral-400 dark:text-neutral-500" />
                        <div className="w-full h-9 pl-9 pr-10 rounded-full bg-neutral-50 hover:bg-neutral-100/80 dark:bg-neutral-900/60 dark:hover:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 text-xs text-neutral-400 dark:text-neutral-550 flex items-center transition-all select-none">
                            Search CRM...
                        </div>
                        <kbd className="absolute right-2.5 top-2.5 hidden lg:inline-flex h-4 select-none items-center gap-1 rounded border border-neutral-200 dark:border-neutral-800 bg-neutral-100 dark:bg-neutral-900 px-1.5 font-mono text-[9px] font-medium text-neutral-400 dark:text-neutral-500">
                            <span>⌘</span>K
                        </kbd>
                    </div>

                    <div className="flex items-center space-x-2 md:space-x-3">
                        {/* 🌐 Language Switcher Dropdown (ID | EN) */}
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    className="h-8 px-2.5 gap-2 text-xs font-semibold text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 border border-neutral-200/80 dark:border-neutral-800 rounded-lg cursor-pointer"
                                    aria-label="Switch Language"
                                >
                                    {locale === 'id' ? <IndonesiaFlag /> : <UKFlag />}
                                    <span>{locale === 'id' ? 'ID' : 'EN'}</span>
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" className="w-48">
                                <DropdownMenuLabel className="text-[11px] font-bold text-neutral-500 uppercase tracking-wider">Pilih Bahasa / Language</DropdownMenuLabel>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem
                                    onClick={() => {
                                        setLocale('id');
                                        toast.success('Bahasa diubah ke Bahasa Indonesia');
                                    }}
                                    className="flex items-center justify-between cursor-pointer text-xs"
                                >
                                    <span className="flex items-center gap-2">
                                        <IndonesiaFlag />
                                        <span>Bahasa Indonesia (ID)</span>
                                    </span>
                                    {locale === 'id' && <Check className="h-4 w-4 text-emerald-600" />}
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    onClick={() => {
                                        setLocale('en');
                                        toast.success('Language changed to English');
                                    }}
                                    className="flex items-center justify-between cursor-pointer text-xs"
                                >
                                    <span className="flex items-center gap-2">
                                        <UKFlag />
                                        <span>English (EN)</span>
                                    </span>
                                    {locale === 'en' && <Check className="h-4 w-4 text-emerald-600" />}
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>

                        {/* Interactive History Dropdown Menu */}
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    className="h-9 w-9 text-muted-foreground hover:text-foreground shrink-0 cursor-pointer"
                                    aria-label="Recent History"
                                >
                                    <Clock className="h-5 w-5" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent className="w-80" align="end">
                                <div className="p-3 border-b border-neutral-100 dark:border-neutral-800">
                                    <h4 className="text-xs font-bold text-neutral-800 dark:text-neutral-200 uppercase tracking-wider">Recent Activity History</h4>
                                    <p className="text-[10px] text-neutral-500 mt-0.5">Quickly jump back to your recent changes.</p>
                                </div>
                                <div className="max-h-64 overflow-y-auto">
                                    {historyLogs.map(item => (
                                        <DropdownMenuItem 
                                            key={item.id}
                                            onClick={() => handleNavigate(item.url)}
                                            className="p-3 cursor-pointer hover:bg-neutral-50 dark:hover:bg-neutral-850 flex flex-col items-start gap-1 focus:bg-neutral-50 dark:focus:bg-neutral-850"
                                        >
                                            <div className="flex justify-between w-full">
                                                <span className="text-xs font-bold text-neutral-900 dark:text-neutral-100">{item.action}</span>
                                                <span className="text-[9px] text-neutral-400">{item.time}</span>
                                            </div>
                                            <span className="text-[10px] text-neutral-550 dark:text-neutral-400 truncate w-full">{item.target}</span>
                                        </DropdownMenuItem>
                                    ))}
                                </div>
                            </DropdownMenuContent>
                        </DropdownMenu>

                        {/* Interactive Notification Bell Dropdown */}
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    className="h-9 w-9 text-muted-foreground hover:text-foreground shrink-0 relative cursor-pointer"
                                    aria-label="System Notifications"
                                >
                                    <Bell className="h-5 w-5" />
                                    {unreadCount > 0 && (
                                        <span className="absolute top-2 right-2 w-2.5 h-2.5 rounded-full bg-indigo-600 dark:bg-indigo-400 ring-2 ring-white dark:ring-neutral-900 animate-pulse" />
                                    )}
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent className="w-80" align="end">
                                <div className="p-3 border-b border-neutral-100 dark:border-neutral-800 flex justify-between items-center">
                                    <div>
                                        <h4 className="text-xs font-bold text-neutral-800 dark:text-neutral-200 uppercase tracking-wider">System Alerts</h4>
                                        <p className="text-[10px] text-neutral-500 mt-0.5">{unreadCount} unread notification alerts</p>
                                    </div>
                                    {unreadCount > 0 && (
                                        <button 
                                            onClick={handleMarkAllRead}
                                            className="text-[10px] font-bold text-indigo-650 hover:underline"
                                        >
                                            Mark all read
                                        </button>
                                    )}
                                </div>
                                <div className="max-h-72 overflow-y-auto">
                                    {notifications.map(n => (
                                        <div 
                                            key={n.id}
                                            onClick={() => toggleNotificationRead(n.id)}
                                            className={`p-3 border-b border-neutral-50 dark:border-neutral-850 flex gap-3 cursor-pointer transition-colors ${
                                                n.read ? 'opacity-60 bg-transparent' : 'bg-neutral-50/50 dark:bg-neutral-900/30'
                                            }`}
                                        >
                                            {n.type === 'error' && <ShieldAlert className="h-5 w-5 text-red-500 shrink-0" />}
                                            {n.type === 'warning' && <ShieldAlert className="h-5 w-5 text-amber-500 shrink-0" />}
                                            {n.type === 'success' && <CheckCircle className="h-5 w-5 text-emerald-500 shrink-0" />}
                                            {n.type === 'info' && <Info className="h-5 w-5 text-blue-550 shrink-0" />}
                                            
                                            <div className="flex-1 space-y-0.5">
                                                <div className="flex justify-between items-center">
                                                    <span className="text-xs font-bold text-neutral-900 dark:text-neutral-100">{n.title}</span>
                                                    <span className="text-[9px] text-neutral-400 shrink-0">{n.time}</span>
                                                </div>
                                                <p className="text-[10px] text-neutral-500 dark:text-neutral-400 leading-relaxed">{n.desc}</p>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </DropdownMenuContent>
                        </DropdownMenu>

                        <div className="w-px h-6 bg-neutral-250 dark:bg-neutral-800 hidden sm:block" />

                        {/* Profile Dropdown */}
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button
                                    variant="ghost"
                                    className="size-10 rounded-full p-1 shrink-0 cursor-pointer"
                                >
                                    <Avatar className="size-8 overflow-hidden rounded-full">
                                        <AvatarImage
                                            src={auth.user?.avatar ?? undefined}
                                            alt={auth.user?.name}
                                        />
                                        <AvatarFallback className="rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white font-bold">
                                            {getInitials(auth.user?.name ?? '')}
                                        </AvatarFallback>
                                    </Avatar>
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent className="w-56" align="end">
                                {auth.user && (
                                    <UserMenuContent user={auth.user} />
                                )}
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>
            </header>

            {/* Premium Command Palette Search Dialog (triggered by Ctrl+K or search bar click) */}
            <Dialog open={isSearchOpen} onOpenChange={setIsSearchOpen}>
                <DialogContent className="max-w-xl p-0 overflow-hidden border border-neutral-200 dark:border-neutral-850 shadow-2xl bg-white dark:bg-neutral-900 rounded-xl">
                    <DialogHeader className="p-4 border-b border-neutral-150 dark:border-neutral-850 flex flex-row items-center gap-3">
                        <Search className="h-5 w-5 text-neutral-400 shrink-0 mt-2" />
                        <input
                            type="text"
                            placeholder="Type to search leads, customers, settings..."
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                            className="w-full bg-transparent border-0 outline-none text-sm text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 dark:placeholder-neutral-500 focus:outline-none focus:ring-0 pt-2"
                            autoFocus
                        />
                    </DialogHeader>
                    <div className="p-2 max-h-96 overflow-y-auto">
                        <div className="px-3 py-1.5 text-[10px] font-bold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider flex items-center gap-1">
                            <Sparkles className="h-3.5 w-3.5 text-indigo-500" />
                            {searchQuery ? 'Matching Search Results' : 'Suggested Navigation & Shortcuts'}
                        </div>
                        
                        <div className="space-y-0.5 mt-1">
                            {filteredSearch.length > 0 ? (
                                filteredSearch.map((item, idx) => (
                                    <div
                                        key={idx}
                                        onClick={() => handleNavigate(item.url)}
                                        className="px-3 py-2 rounded-lg cursor-pointer hover:bg-neutral-50 dark:hover:bg-neutral-850/80 flex items-center justify-between transition-colors group"
                                    >
                                        <div className="flex items-center gap-3">
                                            <div className="w-8 h-8 rounded-lg bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center shrink-0">
                                                {item.category.includes('Leads') && <User className="h-4 w-4 text-sky-600 dark:text-sky-400" />}
                                                {item.category.includes('Customers') && <User className="h-4 w-4 text-emerald-600 dark:text-emerald-400" />}
                                                {item.category.includes('Invoices') && <FileText className="h-4 w-4 text-amber-600 dark:text-amber-400" />}
                                                {item.category.includes('Settings') && <Settings className="h-4 w-4 text-neutral-600 dark:text-neutral-400" />}
                                                {item.category.includes('Support') && <Sparkles className="h-4 w-4 text-sky-600 dark:text-sky-400" />}
                                            </div>
                                            <div>
                                                <div className="text-xs font-bold text-neutral-800 dark:text-neutral-200 flex items-center gap-1.5">
                                                    {item.title}
                                                    <span className="text-[9px] font-medium px-1.5 py-0.5 rounded bg-neutral-100 dark:bg-neutral-800 text-neutral-500 dark:text-neutral-450 border border-neutral-200/40 dark:border-neutral-750">
                                                        {item.category}
                                                    </span>
                                                </div>
                                                <p className="text-[10px] text-neutral-400 mt-0.5">{item.desc}</p>
                                            </div>
                                        </div>
                                        <ChevronRight className="h-4 w-4 text-neutral-350 opacity-0 group-hover:opacity-100 transition-opacity" />
                                    </div>
                                ))
                            ) : (
                                <div className="p-8 text-center text-xs text-neutral-450 dark:text-neutral-500">
                                    No records found matching "{searchQuery}".
                                </div>
                            )}
                        </div>
                    </div>
                    <div className="bg-neutral-50 dark:bg-neutral-950/40 px-4 py-2.5 border-t border-neutral-150 dark:border-neutral-850 flex items-center justify-between text-[9px] text-neutral-400 dark:text-neutral-500">
                        <div className="flex gap-2">
                            <span>Use <kbd className="bg-neutral-200 dark:bg-neutral-850 px-1 py-0.5 rounded font-mono">↑↓</kbd> to navigate</span>
                            <span><kbd className="bg-neutral-200 dark:bg-neutral-850 px-1 py-0.5 rounded font-mono">Enter</kbd> to select</span>
                        </div>
                        <span>Press <kbd className="bg-neutral-200 dark:bg-neutral-850 px-1 py-0.5 rounded font-mono">Esc</kbd> to close</span>
                    </div>
                </DialogContent>
            </Dialog>
        </>
    );
}
