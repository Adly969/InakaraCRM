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
                // Manager gets view-dashboard and view-settings
                $role->syncPermissions([
                    AppPermission::ViewDashboard->value,
                    AppPermission::ViewSettings->value,
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
