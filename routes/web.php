<?php

use App\Http\Controllers\Api\v1\CustomerApiController;
use App\Http\Controllers\Api\v1\LeadApiController;
use App\Http\Controllers\CollectionActivityController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DeliveryConfirmationController;
use App\Http\Controllers\DeliveryOrderController;
use App\Http\Controllers\FinancialEventController;
use App\Http\Controllers\GoodsIssueController;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InvoiceApprovalController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadWorkflowController;
use App\Http\Controllers\OpportunityController;
use App\Http\Controllers\PaymentAttachmentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentWorkflowController;
use App\Http\Controllers\ProductionOrderController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\ReceivableController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\TenantOnboardingController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WmsWarehouseController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::inertia('/', 'welcome')->name('home');

Route::get('register', [TenantOnboardingController::class, 'show'])->name('register');
Route::post('register', [TenantOnboardingController::class, 'store']);
Route::get('subscription-inactive', function () {
    return Inertia::render('errors/subscription-inactive');
})->name('subscription.inactive');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', \App\Http\Controllers\DashboardController::class)->name('dashboard');

    Route::put('leads/{lead}/status', [LeadController::class, 'changeStatus'])->name('leads.status.update');
    Route::post('leads/{lead}/qualify', [LeadWorkflowController::class, 'qualify'])->name('leads.qualify');
    Route::get('leads/{lead}/convert', [LeadWorkflowController::class, 'showConvertForm'])->name('leads.convert.form');
    Route::post('leads/{lead}/convert', [LeadWorkflowController::class, 'convert'])->name('leads.convert');
    Route::resource('leads', LeadController::class);

    Route::post('opportunities/bulk-assign', [OpportunityController::class, 'bulkAssign'])->name('opportunities.bulk-assign');
    Route::post('opportunities/bulk-stage', [OpportunityController::class, 'bulkStage'])->name('opportunities.bulk-stage');
    Route::post('opportunities/bulk-delete', [OpportunityController::class, 'bulkDelete'])->name('opportunities.bulk-delete');
    Route::post('opportunities/{opportunity}/win', [OpportunityController::class, 'win'])->name('opportunities.win');
    Route::post('opportunities/{opportunity}/lose', [OpportunityController::class, 'lose'])->name('opportunities.lose');
    Route::post('opportunities/{opportunity}/stage', [OpportunityController::class, 'changeStage'])->name('opportunities.stage.update');
    Route::resource('opportunities', OpportunityController::class);
    Route::resource('customers', CustomerController::class);
    Route::post('quotations/{quotation}/convert', [SalesOrderController::class, 'convertFromQuotation'])->name('quotations.convert');
    Route::resource('quotations', QuotationController::class);
    Route::post('sales-orders/{sales_order}/release-credit', [SalesOrderController::class, 'releaseCreditHold'])->name('sales-orders.release-credit');
    Route::resource('sales-orders', SalesOrderController::class);
    Route::post('sales-orders/{salesOrder}/production', [ProductionOrderController::class, 'convertFromSalesOrder'])->name('sales-orders.create-production');
    Route::resource('production-orders', ProductionOrderController::class);

    // Warehouse & Inventory Module Routes
    Route::resource('warehouses', WarehouseController::class);
    Route::post('inventory/{item}/rebuild', [InventoryController::class, 'rebuild'])->name('inventory.rebuild');
    Route::resource('inventory', InventoryController::class)->only(['index', 'show']);
    Route::post('goods-receipts/{goods_receipt}/receive', [GoodsReceiptController::class, 'receive'])->name('goods-receipts.receive');
    Route::resource('goods-receipts', GoodsReceiptController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('goods-issues/{goods_issue}/issue', [GoodsIssueController::class, 'issue'])->name('goods-issues.issue');
    Route::resource('goods-issues', GoodsIssueController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('stock-adjustments/{stock_adjustment}/approve', [StockAdjustmentController::class, 'approve'])->name('stock-adjustments.approve');
    Route::post('stock-adjustments/{stock_adjustment}/reject', [StockAdjustmentController::class, 'reject'])->name('stock-adjustments.reject');
    Route::resource('stock-adjustments', StockAdjustmentController::class)->only(['index', 'create', 'store', 'show']);

    // Delivery Order & Shipment Module Routes
    Route::post('delivery-orders/{delivery_order}/approve', [DeliveryOrderController::class, 'approve'])->name('delivery-orders.approve');
    Route::post('delivery-orders/{delivery_order}/cancel', [DeliveryOrderController::class, 'cancel'])->name('delivery-orders.cancel');
    Route::resource('delivery-orders', DeliveryOrderController::class)->only(['index', 'create', 'store', 'show']);

    Route::get('shipments', [ShipmentController::class, 'index'])->name('shipments.index');
    Route::post('delivery-orders/{delivery_order}/shipments', [ShipmentController::class, 'store'])->name('shipments.store');
    Route::post('shipments/{shipment}/dispatch', [ShipmentController::class, 'dispatchShipment'])->name('shipments.dispatch');

    Route::post('shipments/{shipment}/confirm', [DeliveryConfirmationController::class, 'confirm'])->name('shipments.confirm');
    Route::post('shipments/{shipment}/fail', [DeliveryConfirmationController::class, 'fail'])->name('shipments.fail');
    Route::post('shipments/{shipment}/return', [DeliveryConfirmationController::class, 'returnShipment'])->name('shipments.return');

    // Invoice & Billing Module Routes
    Route::post('invoices/{invoice}/approve', [InvoiceApprovalController::class, 'approve'])->name('invoices.approve');
    Route::post('invoices/{invoice}/issue', [InvoiceApprovalController::class, 'issue'])->name('invoices.issue');
    Route::post('invoices/{invoice}/void', [InvoiceApprovalController::class, 'void'])->name('invoices.void');
    Route::resource('invoices', InvoiceController::class)->only(['index', 'create', 'store', 'show']);

    // Accounts Receivable & Payment Management Routes
    Route::post('payments/{payment}/submit', [PaymentWorkflowController::class, 'submit'])->name('payments.submit');
    Route::post('payments/{payment}/verify', [PaymentWorkflowController::class, 'verify'])->name('payments.verify');
    Route::post('payments/{payment}/approve', [PaymentWorkflowController::class, 'approve'])->name('payments.approve');
    Route::post('payments/{payment}/post', [PaymentWorkflowController::class, 'post'])->name('payments.post');
    Route::post('payments/{payment}/cancel', [PaymentWorkflowController::class, 'cancel'])->name('payments.cancel');
    Route::post('payments/{payment}/reverse', [PaymentWorkflowController::class, 'reverse'])->name('payments.reverse');
    Route::resource('payments', PaymentController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('payments/{payment}/attachments', [PaymentAttachmentController::class, 'store'])->name('payments.attachments.store');

    Route::get('receivables', [ReceivableController::class, 'index'])->name('receivables.index');
    Route::get('receivables/aging', [ReceivableController::class, 'aging'])->name('receivables.aging');
    Route::get('receivables/customer/{customer}', [ReceivableController::class, 'customer'])->name('receivables.customer');
    Route::post('collection-activities', [CollectionActivityController::class, 'store'])->name('collection.store');

    // General Ledger Core routes
    Route::post('finance/journals/bulk-approve', [JournalController::class, 'bulkApprove'])->name('finance.journals.bulk-approve');
    Route::post('finance/journals/bulk-reverse', [JournalController::class, 'bulkReverse'])->name('finance.journals.bulk-reverse');
    Route::post('finance/journals/{journal}/post', [JournalController::class, 'post'])->name('finance.journals.post');
    Route::post('finance/journals/{journal}/reverse', [JournalController::class, 'reverse'])->name('finance.journals.reverse');
    Route::post('finance/journals/{journal}/revision', [JournalController::class, 'revision'])->name('finance.journals.revision');
    Route::get('finance/ledger/trial-balance', [JournalController::class, 'trialBalance'])->name('finance.ledger.trial-balance');
    Route::resource('finance/journals', JournalController::class)->names('finance.journals');

    // Accounting Gateway Integration routes
    Route::post('api/v1/finance/gateway/events', [FinancialEventController::class, 'ingestEvent'])->name('finance.gateway.events');
    Route::post('api/v1/finance/gateway/simulate', [FinancialEventController::class, 'simulate'])->name('finance.gateway.simulate');
    Route::post('api/v1/finance/gateway/failures/{id}/replay', [FinancialEventController::class, 'replay'])->name('finance.gateway.replay');
    Route::get('api/v1/finance/gateway/health', [FinancialEventController::class, 'health'])->name('finance.gateway.health');
    Route::inertia('finance/integration', 'finance/integration')->name('finance.integration');

    Route::get('wms/dashboard', [WmsWarehouseController::class, 'index'])->name('wms.dashboard');

    // CRM API Routes
    Route::get('api/v1/leads', [LeadApiController::class, 'index'])->name('api.leads.index');
    Route::post('api/v1/leads', [LeadApiController::class, 'store'])->name('api.leads.store');
    Route::post('api/v1/leads/{id}/convert', [LeadApiController::class, 'convert'])->name('api.leads.convert');
    Route::get('api/v1/customers', [CustomerApiController::class, 'index'])->name('api.customers.index');
    Route::get('api/v1/customers/{id}', [CustomerApiController::class, 'show'])->name('api.customers.show');
    Route::post('api/v1/customers', [CustomerApiController::class, 'store'])->name('api.customers.store');
    Route::put('api/v1/customers/{id}', [CustomerApiController::class, 'update'])->name('api.customers.update');
    Route::delete('api/v1/customers/{id}', [CustomerApiController::class, 'destroy'])->name('api.customers.destroy');
    Route::post('api/v1/customers/merge', [CustomerApiController::class, 'merge'])->name('api.customers.merge');
    Route::post('api/v1/customers/{id}/assign', [CustomerApiController::class, 'assign'])->name('api.customers.assign');
    Route::get('api/v1/customers/{id}/timeline', [CustomerApiController::class, 'timeline'])->name('api.customers.timeline');
    Route::post('api/v1/customers/{id}/activities', [CustomerApiController::class, 'logActivity'])->name('api.customers.activities.store');
    Route::inertia('support', 'support/index')->name('support');

    // Reserved Navigation Placeholder Routes (Sprint 21-40)
    $placeholders = [
        // CRM Group
        ['uri' => 'crm/activities', 'name' => 'crm.activities', 'title' => 'Activities', 'desc' => 'Record sales calls, meetings, and follow-up activities.', 'sprint' => 'Sprint 22', 'group' => 'CRM'],
        ['uri' => 'crm/calendar', 'name' => 'crm.calendar', 'title' => 'Calendar', 'desc' => 'Interactive sales calendar and appointment scheduler.', 'sprint' => 'Sprint 22', 'group' => 'CRM'],
        ['uri' => 'crm/tasks', 'name' => 'crm.tasks', 'title' => 'Tasks', 'desc' => 'Manage individual and team sales tasks.', 'sprint' => 'Sprint 22', 'group' => 'CRM'],
        ['uri' => 'crm/documents', 'name' => 'crm.documents', 'title' => 'Documents', 'desc' => 'Centralized customer document repository.', 'sprint' => 'Sprint 23', 'group' => 'CRM'],

        // Production Group
        ['uri' => 'production/work-orders', 'name' => 'production.work-orders', 'title' => 'Work Orders', 'desc' => 'Factory floor work order execution tickets.', 'sprint' => 'Sprint 26', 'group' => 'Production'],
        ['uri' => 'production/bom', 'name' => 'production.bom', 'title' => 'Bill Of Materials', 'desc' => 'Configure furniture raw material recipes.', 'sprint' => 'Sprint 26', 'group' => 'Production'],
        ['uri' => 'production/routing', 'name' => 'production.routing', 'title' => 'Routing', 'desc' => 'Configure machinery station routing sequences.', 'sprint' => 'Sprint 27', 'group' => 'Production'],
        ['uri' => 'production/work-centers', 'name' => 'production.work-centers', 'title' => 'Work Centers', 'desc' => 'Monitor factory machine stations and capacity.', 'sprint' => 'Sprint 27', 'group' => 'Production'],
        ['uri' => 'production/qc', 'name' => 'production.qc', 'title' => 'Quality Control', 'desc' => 'Quality assurance and product inspection sheets.', 'sprint' => 'Sprint 28', 'group' => 'Production'],

        // Warehouse Group
        ['uri' => 'warehouse/locations', 'name' => 'warehouse.locations', 'title' => 'Storage Locations', 'desc' => 'Map physical warehouse plants, racks, and bins.', 'sprint' => 'Sprint 29', 'group' => 'Warehouse'],
        ['uri' => 'warehouse/stock-transfer', 'name' => 'warehouse.stock-transfer', 'title' => 'Stock Transfer', 'desc' => 'Inter-warehouse inventory stock movement.', 'sprint' => 'Sprint 30', 'group' => 'Warehouse'],
        ['uri' => 'warehouse/stock-opname', 'name' => 'warehouse.stock-opname', 'title' => 'Stock Opname', 'desc' => 'Physical inventory audit and variance reconciler.', 'sprint' => 'Sprint 31', 'group' => 'Warehouse'],

        // Purchasing Group
        ['uri' => 'purchasing/requests', 'name' => 'purchasing.requests', 'title' => 'Purchase Requests', 'desc' => 'Internal material purchase requisitions.', 'sprint' => 'Sprint 32', 'group' => 'Purchasing'],
        ['uri' => 'purchasing/orders', 'name' => 'purchasing.orders', 'title' => 'Purchase Orders', 'desc' => 'Issue procurement contracts to suppliers.', 'sprint' => 'Sprint 32', 'group' => 'Purchasing'],
        ['uri' => 'purchasing/suppliers', 'name' => 'purchasing.suppliers', 'title' => 'Suppliers', 'desc' => 'Vendor directory and supplier master data.', 'sprint' => 'Sprint 33', 'group' => 'Purchasing'],
        ['uri' => 'purchasing/receipts', 'name' => 'purchasing.receipts', 'title' => 'Supplier Receipts', 'desc' => 'Log supplier bills and accounts payable notes.', 'sprint' => 'Sprint 33', 'group' => 'Purchasing'],

        // Finance Group
        ['uri' => 'finance/payables', 'name' => 'finance.payables', 'title' => 'Payables', 'desc' => 'Vendor accounts payable aging and payment schedules.', 'sprint' => 'Sprint 34', 'group' => 'Finance'],
        ['uri' => 'finance/cash-bank', 'name' => 'finance.cash-bank', 'title' => 'Cash & Bank', 'desc' => 'Manage bank accounts and cash flow balances.', 'sprint' => 'Sprint 35', 'group' => 'Finance'],
        ['uri' => 'finance/reconciliation', 'name' => 'finance.reconciliation', 'title' => 'Reconciliation', 'desc' => 'Bank statement matching and reconciliation.', 'sprint' => 'Sprint 35', 'group' => 'Finance'],

        // Reports Group
        ['uri' => 'reports/analytics', 'name' => 'reports.analytics', 'title' => 'Analytics Dashboard', 'desc' => 'Executive BI dashboards and cross-module metrics.', 'sprint' => 'Sprint 36', 'group' => 'Reports'],
        ['uri' => 'reports/sales', 'name' => 'reports.sales', 'title' => 'Sales Report', 'desc' => 'Comprehensive commercial sales performance report.', 'sprint' => 'Sprint 36', 'group' => 'Reports'],
        ['uri' => 'reports/customers', 'name' => 'reports.customers', 'title' => 'Customer Report', 'desc' => 'Customer lifetime value and acquisition analytics.', 'sprint' => 'Sprint 37', 'group' => 'Reports'],
        ['uri' => 'reports/production', 'name' => 'reports.production', 'title' => 'Production Report', 'desc' => 'OEE efficiency and factory output analytics.', 'sprint' => 'Sprint 37', 'group' => 'Reports'],
        ['uri' => 'reports/inventory', 'name' => 'reports.inventory', 'title' => 'Inventory Report', 'desc' => 'Inventory turnover ratio and dead stock analysis.', 'sprint' => 'Sprint 38', 'group' => 'Reports'],
        ['uri' => 'reports/finance', 'name' => 'reports.finance', 'title' => 'Finance Report', 'desc' => 'Trial balance, GL ledger summary, and tax reports.', 'sprint' => 'Sprint 38', 'group' => 'Reports'],
        ['uri' => 'reports/pnl', 'name' => 'reports.pnl', 'title' => 'Profit & Loss', 'desc' => 'Income statement and corporate P&L breakdown.', 'sprint' => 'Sprint 38', 'group' => 'Reports'],
        ['uri' => 'reports/export', 'name' => 'reports.export', 'title' => 'Export Data', 'desc' => 'Bulk CSV / Excel data exporter for ERP data.', 'sprint' => 'Sprint 38', 'group' => 'Reports'],

        // Master Data Group
        ['uri' => 'master/products', 'name' => 'master.products', 'title' => 'Products', 'desc' => 'Central product master SKU catalog.', 'sprint' => 'Sprint 28', 'group' => 'Master Data'],
        ['uri' => 'master/categories', 'name' => 'master.categories', 'title' => 'Categories', 'desc' => 'Product category taxonomy and hierarchy.', 'sprint' => 'Sprint 28', 'group' => 'Master Data'],
        ['uri' => 'master/brands', 'name' => 'master.brands', 'title' => 'Brands', 'desc' => 'Manage furniture product brand lines.', 'sprint' => 'Sprint 28', 'group' => 'Master Data'],
        ['uri' => 'master/materials', 'name' => 'master.materials', 'title' => 'Materials', 'desc' => 'Raw material grade and veneer catalog.', 'sprint' => 'Sprint 28', 'group' => 'Master Data'],
        ['uri' => 'master/taxes', 'name' => 'master.taxes', 'title' => 'Taxes', 'desc' => 'Configure PPN, PPh, and tax rate codes.', 'sprint' => 'Sprint 34', 'group' => 'Master Data'],
        ['uri' => 'master/units', 'name' => 'master.units', 'title' => 'Units', 'desc' => 'Units of measurement (Pcs, Set, Meter, Kg).', 'sprint' => 'Sprint 28', 'group' => 'Master Data'],
        ['uri' => 'master/currencies', 'name' => 'master.currencies', 'title' => 'Currencies', 'desc' => 'Multi-currency exchange rate tables.', 'sprint' => 'Sprint 34', 'group' => 'Master Data'],

        // System Group
        ['uri' => 'system/roles', 'name' => 'system.roles', 'title' => 'Roles', 'desc' => 'Configure RBAC security roles.', 'sprint' => 'Sprint 39', 'group' => 'System'],
        ['uri' => 'system/permissions', 'name' => 'system.permissions', 'title' => 'Permissions', 'desc' => 'Granular policy permission definitions.', 'sprint' => 'Sprint 39', 'group' => 'System'],
        ['uri' => 'system/tenants', 'name' => 'system.tenants', 'title' => 'Tenants', 'desc' => 'Multi-tenant SaaS organization management.', 'sprint' => 'Sprint 40', 'group' => 'System'],
        ['uri' => 'system/companies', 'name' => 'system.companies', 'title' => 'Companies', 'desc' => 'Multi-company legal entity setup.', 'sprint' => 'Sprint 38', 'group' => 'System'],
        ['uri' => 'system/branches', 'name' => 'system.branches', 'title' => 'Branches', 'desc' => 'Branch plant and store office setup.', 'sprint' => 'Sprint 38', 'group' => 'System'],
        ['uri' => 'system/notifications', 'name' => 'system.notifications', 'title' => 'Notifications', 'desc' => 'System alert triggers and notification preferences.', 'sprint' => 'Sprint 39', 'group' => 'System'],
        ['uri' => 'system/audit-logs', 'name' => 'system.audit-logs', 'title' => 'Audit Logs', 'desc' => 'Enterprise event audit trail and transactional outbox.', 'sprint' => 'Sprint 39', 'group' => 'System'],
    ];

    foreach ($placeholders as $ph) {
        Route::get($ph['uri'], function () use ($ph) {
            return Inertia::render('placeholder', [
                'title' => $ph['title'],
                'description' => $ph['desc'],
                'plannedSprint' => $ph['sprint'],
                'status' => 'Coming Soon',
                'moduleGroup' => $ph['group'],
            ]);
        })->name($ph['name']);
    }
});

require __DIR__.'/settings.php';
