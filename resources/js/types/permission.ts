export const UserRole = {
    Owner: 'owner',
    Manager: 'manager',
    Sales: 'sales',
    Admin: 'admin',
    Finance: 'finance',
    Gudang: 'gudang',
    Produksi: 'produksi',
    CustomerService: 'customer-service',
    Viewer: 'viewer',
} as const;

export type UserRoleType = (typeof UserRole)[keyof typeof UserRole];

export const Permission = {
    ViewDashboard: 'view-dashboard',
    ViewUsers: 'view-users',
    CreateUsers: 'create-users',
    EditUsers: 'edit-users',
    DeleteUsers: 'delete-users',
    ViewSettings: 'view-settings',
    ManageSettings: 'manage-settings',
    ViewLeads: 'view-leads',
    CreateLeads: 'create-leads',
    EditLeads: 'edit-leads',
    DeleteLeads: 'delete-leads',
    ViewCustomers: 'view-customers',
    CreateCustomers: 'create-customers',
    EditCustomers: 'edit-customers',
    DeleteCustomers: 'delete-customers',
} as const;

export type PermissionType = (typeof Permission)[keyof typeof Permission];
