export type Locale = 'id' | 'en';

export const translations = {
    id: {
        // Navigation Titles
        nav_dashboard: 'Dashboard Utama',
        nav_leads: 'Prospek Sales (Leads)',
        nav_customers: 'Pelanggan (Customers)',
        nav_quotations: 'Surat Penawaran (Quotations)',
        nav_sales_orders: 'Pesanan Penjualan (SO)',
        nav_production_orders: 'Produksi Manufaktur',
        nav_warehouses: 'Gudang & Lokasi',
        nav_inventory: 'Stok Inventaris',
        nav_goods_receipts: 'Penerimaan Barang (GR)',
        nav_goods_issues: 'Pengeluaran Barang (GI)',
        nav_stock_adjustments: 'Penyesuaian Stok',
        nav_delivery_orders: 'Surat Jalan (DO)',
        nav_shipments: 'Pengiriman & Logistik',
        nav_invoices: 'Faktur Tagihan (Invoice)',
        nav_payments: 'Pencatatan Pembayaran',
        nav_receivables: 'Penagihan Piutang',
        nav_settings: 'Pengaturan Sistem',
        nav_support: 'Pusat Bantuan & Support',
        nav_sign_out: 'Keluar Akun',

        // Header & Actions
        search_placeholder: 'Cari di CRM... (Tekan Ctrl+K)',
        language_switcher: 'Bahasa Indonesia (ID)',
        notifications_title: 'Notifikasi Sistem',
        history_title: 'Riwayat Aktivitas Terakhir',
        create_new: 'Buat Baru',
        filter_data: 'Filter Data',
        export_pdf: 'Unduh PDF',
        total_value: 'Total Nilai',
        status_active: 'Aktif',
        status_pending: 'Menunggu Persetujuan',

        // Feature Page Subtitles
        leads_subtitle: 'Kelola calon pembeli furnitur hotel/villa dan alur kualifikasi sales.',
        quotations_subtitle: 'Kelola proposal harga furnitur, estimasi nilai proyek, dan revisi penawaran.',
        sales_orders_subtitle: 'Kelola pesanan penjualan resmi yang disetujui, alokasi stok, dan status produksi.',
        receivables_subtitle: 'Pantau piutang berjalan pelanggan, kepatuhan termin pembayaran (TOP), dan klasifikasi umur piutang.',
    },
    en: {
        // Navigation Titles
        nav_dashboard: 'Dashboard Overview',
        nav_leads: 'Sales Leads',
        nav_customers: 'Customers Directory',
        nav_quotations: 'Quotations & Proposals',
        nav_sales_orders: 'Sales Orders (SO)',
        nav_production_orders: 'Production Orders',
        nav_warehouses: 'Warehouses & Topography',
        nav_inventory: 'Inventory Stock',
        nav_goods_receipts: 'Goods Receipts (GR)',
        nav_goods_issues: 'Goods Issues (GI)',
        nav_stock_adjustments: 'Stock Adjustments',
        nav_delivery_orders: 'Delivery Orders (DO)',
        nav_shipments: 'Shipments & Logistics',
        nav_invoices: 'Invoices & Billing',
        nav_payments: 'Payments Recording',
        nav_receivables: 'Accounts Receivable',
        nav_settings: 'System Settings',
        nav_support: 'Support Helpdesk',
        nav_sign_out: 'Sign Out',

        // Header & Actions
        search_placeholder: 'Search CRM... (Press Ctrl+K)',
        language_switcher: 'English (EN)',
        notifications_title: 'System Notifications',
        history_title: 'Recent Activity History',
        create_new: 'Create New',
        filter_data: 'Filter Data',
        export_pdf: 'Export PDF',
        total_value: 'Total Value',
        status_active: 'Active',
        status_pending: 'Pending Approval',

        // Feature Page Subtitles
        leads_subtitle: 'Manage customer leads, hospitality furniture buyers, and sales qualification pipeline.',
        quotations_subtitle: 'Manage furniture pricing proposals, project valuations, and quote revisions.',
        sales_orders_subtitle: 'Manage confirmed commercial commitments, stock allocations, and production status.',
        receivables_subtitle: 'Monitor customer outstanding balances, credit terms compliance, and aging categories.',
    },
};
