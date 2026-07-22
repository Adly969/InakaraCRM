<?php

namespace App\Enums;

enum Permission: string
{
    case ViewDashboard = 'view-dashboard';
    case ViewUsers = 'view-users';
    case CreateUsers = 'create-users';
    case EditUsers = 'edit-users';
    case DeleteUsers = 'delete-users';
    case ViewSettings = 'view-settings';
    case ManageSettings = 'manage-settings';
    case ViewLeads = 'view-leads';
    case CreateLeads = 'create-leads';
    case EditLeads = 'edit-leads';
    case DeleteLeads = 'delete-leads';
    case ViewCustomers = 'view-customers';
    case CreateCustomers = 'create-customers';
    case EditCustomers = 'edit-customers';
    case DeleteCustomers = 'delete-customers';
    case ViewQuotations = 'view-quotations';
    case CreateQuotations = 'create-quotations';
    case EditQuotations = 'edit-quotations';
    case DeleteQuotations = 'delete-quotations';
    case ViewSalesOrders = 'view-sales-orders';
    case CreateSalesOrders = 'create-sales-orders';
    case EditSalesOrders = 'edit-sales-orders';
    case DeleteSalesOrders = 'delete-sales-orders';
    case CancelSalesOrders = 'cancel-sales-orders';
    case ViewProductionOrders = 'view-production-orders';
    case CreateProductionOrders = 'create-production-orders';
    case EditProductionOrders = 'edit-production-orders';
    case DeleteProductionOrders = 'delete-production-orders';
    case CancelProductionOrders = 'cancel-production-orders';
    case ViewWarehouses = 'view-warehouses';
    case CreateWarehouses = 'create-warehouses';
    case EditWarehouses = 'edit-warehouses';
    case DeleteWarehouses = 'delete-warehouses';
    case ViewInventory = 'view-inventory';
    case AdjustInventory = 'adjust-inventory';
    case ApproveInventoryAdjustment = 'approve-inventory-adjustment';
    case ViewGoodsReceipts = 'view-goods-receipts';
    case CreateGoodsReceipts = 'create-goods-receipts';
    case ApproveGoodsReceipts = 'approve-goods-receipts';
    case ViewGoodsIssues = 'view-goods-issues';
    case CreateGoodsIssues = 'create-goods-issues';
    case ApproveGoodsIssues = 'approve-goods-issues';
    case ViewDeliveryOrders = 'view-delivery-orders';
    case CreateDeliveryOrders = 'create-delivery-orders';
    case ApproveDeliveryOrders = 'approve-delivery-orders';
    case ManageCarriers = 'manage-carriers';
    case ManageDrivers = 'manage-drivers';
    case DispatchShipments = 'dispatch-shipments';
    case ConfirmDeliveries = 'confirm-deliveries';
    case CancelDeliveryOrders = 'cancel-delivery-orders';
    case ViewInvoices = 'view-invoices';
    case CreateInvoices = 'create-invoices';
    case EditInvoices = 'edit-invoices';
    case ApproveInvoices = 'approve-invoices';
    case IssueInvoices = 'issue-invoices';
    case VoidInvoices = 'void-invoices';
    case ViewPayments = 'view-payments';
    case CreatePayments = 'create-payments';
    case EditPayments = 'edit-payments';
    case SubmitPayments = 'submit-payments';
    case VerifyPayments = 'verify-payments';
    case ApprovePaymentsL1 = 'approve-payments-l1';
    case ApprovePaymentsL2 = 'approve-payments-l2';
    case ApprovePaymentsL3 = 'approve-payments-l3';
    case PostPayments = 'post-payments';
    case CancelPayments = 'cancel-payments';
    case ReversePayments = 'reverse-payments';
    case ViewReceivables = 'view-receivables';
    case ViewOpportunities = 'view-opportunities';
    case CreateOpportunities = 'create-opportunities';
    case EditOpportunities = 'edit-opportunities';
    case DeleteOpportunities = 'delete-opportunities';

    /**
     * Get the human-readable display name.
     */
    public function label(): string
    {
        return match ($this) {
            self::ViewDashboard => 'View Dashboard',
            self::ViewUsers => 'View Users',
            self::CreateUsers => 'Create Users',
            self::EditUsers => 'Edit Users',
            self::DeleteUsers => 'Delete Users',
            self::ViewSettings => 'View Settings',
            self::ManageSettings => 'Manage Settings',
            self::ViewLeads => 'View Leads',
            self::CreateLeads => 'Create Leads',
            self::EditLeads => 'Edit Leads',
            self::DeleteLeads => 'Delete Leads',
            self::ViewCustomers => 'View Customers',
            self::CreateCustomers => 'Create Customers',
            self::EditCustomers => 'Edit Customers',
            self::DeleteCustomers => 'Delete Customers',
            self::ViewQuotations => 'View Quotations',
            self::CreateQuotations => 'Create Quotations',
            self::EditQuotations => 'Edit Quotations',
            self::DeleteQuotations => 'Delete Quotations',
            self::ViewSalesOrders => 'View Sales Orders',
            self::CreateSalesOrders => 'Create Sales Orders',
            self::EditSalesOrders => 'Edit Sales Orders',
            self::DeleteSalesOrders => 'Delete Sales Orders',
            self::CancelSalesOrders => 'Cancel Sales Orders',
            self::ViewProductionOrders => 'View Production Orders',
            self::CreateProductionOrders => 'Create Production Orders',
            self::EditProductionOrders => 'Edit Production Orders',
            self::DeleteProductionOrders => 'Delete Production Orders',
            self::CancelProductionOrders => 'Cancel Production Orders',
            self::ViewWarehouses => 'View Warehouses',
            self::CreateWarehouses => 'Create Warehouses',
            self::EditWarehouses => 'Edit Warehouses',
            self::DeleteWarehouses => 'Delete Warehouses',
            self::ViewInventory => 'View Inventory',
            self::AdjustInventory => 'Adjust Inventory',
            self::ApproveInventoryAdjustment => 'Approve Inventory Adjustment',
            self::ViewGoodsReceipts => 'View Goods Receipts',
            self::CreateGoodsReceipts => 'Create Goods Receipts',
            self::ApproveGoodsReceipts => 'Approve Goods Receipts',
            self::ViewGoodsIssues => 'View Goods Issues',
            self::CreateGoodsIssues => 'Create Goods Issues',
            self::ApproveGoodsIssues => 'Approve Goods Issues',
            self::ViewDeliveryOrders => 'View Delivery Orders',
            self::CreateDeliveryOrders => 'Create Delivery Orders',
            self::ApproveDeliveryOrders => 'Approve Delivery Orders',
            self::ManageCarriers => 'Manage Carriers',
            self::ManageDrivers => 'Manage Drivers',
            self::DispatchShipments => 'Dispatch Shipments',
            self::ConfirmDeliveries => 'Confirm Deliveries',
            self::CancelDeliveryOrders => 'Cancel Delivery Orders',
            self::ViewInvoices => 'View Invoices',
            self::CreateInvoices => 'Create Invoices',
            self::EditInvoices => 'Edit Invoices',
            self::ApproveInvoices => 'Approve Invoices',
            self::IssueInvoices => 'Issue Invoices',
            self::VoidInvoices => 'Void Invoices',
            self::ViewPayments => 'View Payments',
            self::CreatePayments => 'Create Payments',
            self::EditPayments => 'Edit Payments',
            self::SubmitPayments => 'Submit Payments',
            self::VerifyPayments => 'Verify Payments',
            self::ApprovePaymentsL1 => 'Approve Payments L1 (Supervisor)',
            self::ApprovePaymentsL2 => 'Approve Payments L2 (Manager)',
            self::ApprovePaymentsL3 => 'Approve Payments L3 (Director)',
            self::PostPayments => 'Post Payments',
            self::CancelPayments => 'Cancel Payments',
            self::ReversePayments => 'Reverse Payments',
            self::ViewReceivables => 'View Receivables',
            self::ViewOpportunities => 'View Opportunities',
            self::CreateOpportunities => 'Create Opportunities',
            self::EditOpportunities => 'Edit Opportunities',
            self::DeleteOpportunities => 'Delete Opportunities',
        };
    }
}
