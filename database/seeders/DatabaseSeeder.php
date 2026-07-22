<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions
        $this->call(RoleAndPermissionSeeder::class);
        $this->call(CrmPipelineSeeder::class);

        // Create default Owner user
        $owner = User::factory()->create([
            'name' => 'System Owner',
            'email' => 'owner@inakara.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $owner->assignRole(UserRole::Owner->value);

        // Create default Admin user
        $admin = User::factory()->create([
            'name' => 'System Administrator',
            'email' => 'admin@inakara.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $admin->assignRole(UserRole::Admin->value);

        // Create default Manager user
        $manager = User::factory()->create([
            'name' => 'System Manager',
            'email' => 'manager@inakara.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $manager->assignRole(UserRole::Manager->value);

        // Create default Sales user
        $sales = User::factory()->create([
            'name' => 'System Sales Representative',
            'email' => 'sales@inakara.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $sales->assignRole(UserRole::Sales->value);

        // Create default Finance user
        $finance = User::factory()->create([
            'name' => 'System Finance Officer',
            'email' => 'finance@inakara.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $finance->assignRole(UserRole::Finance->value);

        // Create default Warehouse user
        $gudang = User::factory()->create([
            'name' => 'System Warehouse Operator',
            'email' => 'gudang@inakara.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $gudang->assignRole(UserRole::Gudang->value);

        // Create default Production user
        $produksi = User::factory()->create([
            'name' => 'System Production Planner',
            'email' => 'produksi@inakara.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $produksi->assignRole(UserRole::Produksi->value);

        // Create default Customer Service user
        $cs = User::factory()->create([
            'name' => 'System Support Agent',
            'email' => 'cs@inakara.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $cs->assignRole(UserRole::CustomerService->value);

        // Seed rich dummy records for WMS, Customers, Credit limits, Invoices, Payments, and Leads
        $this->call(CrmDummyDataSeeder::class);
    }
}
