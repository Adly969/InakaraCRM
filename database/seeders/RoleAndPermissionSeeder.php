<?php

namespace Database\Seeders;

use App\Enums\Permission as AppPermission;
use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        foreach (AppPermission::cases() as $permission) {
            Permission::firstOrCreate(['name' => $permission->value, 'guard_name' => 'web']);
        }

        // Create roles and assign permissions
        foreach (UserRole::cases() as $roleEnum) {
            $role = Role::firstOrCreate(['name' => $roleEnum->value, 'guard_name' => 'web']);

            if ($roleEnum === UserRole::Owner) {
                // Owner gets all permissions
                $role->syncPermissions(Permission::all());
            } elseif ($roleEnum === UserRole::Admin) {
                // Admin gets all foundation permissions
                $role->syncPermissions(Permission::all());
            } elseif ($roleEnum === UserRole::Manager) {
                // Manager gets view-dashboard, view-settings and lead/customer read/write
                $role->syncPermissions([
                    AppPermission::ViewDashboard->value,
                    AppPermission::ViewSettings->value,
                    AppPermission::ViewLeads->value,
                    AppPermission::CreateLeads->value,
                    AppPermission::EditLeads->value,
                    AppPermission::ViewCustomers->value,
                    AppPermission::CreateCustomers->value,
                    AppPermission::EditCustomers->value,
                    AppPermission::ViewQuotations->value,
                    AppPermission::CreateQuotations->value,
                    AppPermission::EditQuotations->value,
                    AppPermission::ViewSalesOrders->value,
                    AppPermission::CreateSalesOrders->value,
                    AppPermission::EditSalesOrders->value,
                    AppPermission::CancelSalesOrders->value,
                    AppPermission::ViewProductionOrders->value,
                    AppPermission::CreateProductionOrders->value,
                    AppPermission::EditProductionOrders->value,
                    AppPermission::CancelProductionOrders->value,
                    // Warehouse & Inventory permissions for Manager
                    AppPermission::ViewWarehouses->value,
                    AppPermission::CreateWarehouses->value,
                    AppPermission::EditWarehouses->value,
                    AppPermission::ViewInventory->value,
                    AppPermission::AdjustInventory->value,
                    AppPermission::ApproveInventoryAdjustment->value,
                    AppPermission::ViewGoodsReceipts->value,
                    AppPermission::CreateGoodsReceipts->value,
                    AppPermission::ApproveGoodsReceipts->value,
                    AppPermission::ViewGoodsIssues->value,
                    AppPermission::CreateGoodsIssues->value,
                    AppPermission::ApproveGoodsIssues->value,
                    AppPermission::ViewDeliveryOrders->value,
                    AppPermission::CreateDeliveryOrders->value,
                    AppPermission::ApproveDeliveryOrders->value,
                    AppPermission::ManageCarriers->value,
                    AppPermission::ManageDrivers->value,
                    AppPermission::ConfirmDeliveries->value,
                    AppPermission::CancelDeliveryOrders->value,
                    // Invoice permissions for Manager
                    AppPermission::ViewInvoices->value,
                    AppPermission::CreateInvoices->value,
                    AppPermission::EditInvoices->value,
                    AppPermission::ApproveInvoices->value,
                    AppPermission::IssueInvoices->value,
                    AppPermission::VoidInvoices->value,
                    // Payment and Receivable permissions for Manager
                    AppPermission::ViewPayments->value,
                    AppPermission::CreatePayments->value,
                    AppPermission::EditPayments->value,
                    AppPermission::SubmitPayments->value,
                    AppPermission::VerifyPayments->value,
                    AppPermission::ApprovePaymentsL1->value,
                    AppPermission::ApprovePaymentsL2->value,
                    AppPermission::ApprovePaymentsL3->value,
                    AppPermission::PostPayments->value,
                    AppPermission::CancelPayments->value,
                    AppPermission::ReversePayments->value,
                    AppPermission::ViewReceivables->value,
                    AppPermission::ViewOpportunities->value,
                    AppPermission::CreateOpportunities->value,
                    AppPermission::EditOpportunities->value,
                    AppPermission::DeleteOpportunities->value,
                ]);
            } elseif ($roleEnum === UserRole::Sales) {
                // Sales gets view-dashboard and lead/customer read/write
                $role->syncPermissions([
                    AppPermission::ViewDashboard->value,
                    AppPermission::ViewLeads->value,
                    AppPermission::CreateLeads->value,
                    AppPermission::EditLeads->value,
                    AppPermission::ViewCustomers->value,
                    AppPermission::CreateCustomers->value,
                    AppPermission::EditCustomers->value,
                    AppPermission::ViewQuotations->value,
                    AppPermission::CreateQuotations->value,
                    AppPermission::EditQuotations->value,
                    AppPermission::ViewSalesOrders->value,
                    AppPermission::CreateSalesOrders->value,
                    AppPermission::EditSalesOrders->value,
                    AppPermission::CancelSalesOrders->value,
                    AppPermission::ViewProductionOrders->value,
                    // Warehouse & Inventory permissions for Sales
                    AppPermission::ViewInventory->value,
                    AppPermission::ViewDeliveryOrders->value,
                    AppPermission::CreateDeliveryOrders->value,
                    AppPermission::ViewPayments->value,
                    AppPermission::ViewReceivables->value,
                    AppPermission::ViewOpportunities->value,
                    AppPermission::CreateOpportunities->value,
                    AppPermission::EditOpportunities->value,
                ]);
            } elseif ($roleEnum === UserRole::CustomerService) {
                // Customer Service gets view-dashboard and view-customers
                $role->syncPermissions([
                    AppPermission::ViewDashboard->value,
                    AppPermission::ViewCustomers->value,
                    AppPermission::ViewQuotations->value,
                    AppPermission::ViewSalesOrders->value,
                    AppPermission::ViewProductionOrders->value,
                    // Warehouse & Inventory permissions for Customer Service
                    AppPermission::ViewInventory->value,
                    AppPermission::ViewDeliveryOrders->value,
                    AppPermission::ConfirmDeliveries->value,
                ]);
            } elseif ($roleEnum === UserRole::Produksi) {
                // Produksi gets production order management
                $role->syncPermissions([
                    AppPermission::ViewDashboard->value,
                    AppPermission::ViewProductionOrders->value,
                    AppPermission::CreateProductionOrders->value,
                    AppPermission::EditProductionOrders->value,
                    AppPermission::CancelProductionOrders->value,
                ]);
            } elseif ($roleEnum === UserRole::Gudang) {
                // Gudang gets warehouse and inventory permissions
                $role->syncPermissions([
                    AppPermission::ViewDashboard->value,
                    AppPermission::ViewWarehouses->value,
                    AppPermission::ViewInventory->value,
                    AppPermission::AdjustInventory->value,
                    AppPermission::ViewGoodsReceipts->value,
                    AppPermission::CreateGoodsReceipts->value,
                    AppPermission::ViewGoodsIssues->value,
                    AppPermission::CreateGoodsIssues->value,
                    AppPermission::ViewDeliveryOrders->value,
                    AppPermission::ManageDrivers->value,
                    AppPermission::DispatchShipments->value,
                    AppPermission::ConfirmDeliveries->value,
                ]);
            } elseif ($roleEnum === UserRole::Finance) {
                // Finance gets billing, invoices, payments, and receivables
                $role->syncPermissions([
                    AppPermission::ViewDashboard->value,
                    AppPermission::ViewInvoices->value,
                    AppPermission::CreateInvoices->value,
                    AppPermission::EditInvoices->value,
                    AppPermission::ApproveInvoices->value,
                    AppPermission::IssueInvoices->value,
                    AppPermission::VoidInvoices->value,
                    AppPermission::ViewPayments->value,
                    AppPermission::CreatePayments->value,
                    AppPermission::EditPayments->value,
                    AppPermission::SubmitPayments->value,
                    AppPermission::VerifyPayments->value,
                    AppPermission::ApprovePaymentsL1->value,
                    AppPermission::ApprovePaymentsL2->value,
                    AppPermission::ApprovePaymentsL3->value,
                    AppPermission::PostPayments->value,
                    AppPermission::CancelPayments->value,
                    AppPermission::ReversePayments->value,
                    AppPermission::ViewReceivables->value,
                ]);
            } else {
                // All other roles get view-dashboard only
                $role->syncPermissions([
                    AppPermission::ViewDashboard->value,
                ]);
            }
        }
    }
}
