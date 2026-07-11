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
    }
}
