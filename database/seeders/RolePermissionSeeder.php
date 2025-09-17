<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions untuk POS system
        $permissions = [
            // Dashboard
            'view_dashboard',

            // Master Data - Products
            'view_products',
            'create_products',
            'edit_products',
            'delete_products',
            'export_products',
            'import_products',

            // Master Data - Categories
            'view_categories',
            'create_categories',
            'edit_categories',
            'delete_categories',

            // Master Data - Divisions
            'view_divisions',
            'create_divisions',
            'edit_divisions',
            'delete_divisions',

            // Master Data - Racks
            'view_racks',
            'create_racks',
            'edit_racks',
            'delete_racks',

            // Master Data - Suppliers
            'view_suppliers',
            'create_suppliers',
            'edit_suppliers',
            'delete_suppliers',

            // Master Data - Members
            'view_members',
            'create_members',
            'edit_members',
            'delete_members',

            // Master Data - Salesmen
            'view_salesmen',
            'create_salesmen',
            'edit_salesmen',
            'delete_salesmen',

            // Transactions
            'view_sales',
            'create_sales',
            'edit_sales',
            'delete_sales',
            'view_purchases',
            'create_purchases',
            'edit_purchases',
            'delete_purchases',

            // Reports
            'view_reports',
            'export_reports',

            // Shifts
            'view_shifts',
            'open_shifts',
            'close_shifts',

            // User Management
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'manage_roles',

            // System Settings
            'view_settings',
            'edit_settings',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create roles dengan permissions

        // 1. Super Admin - Full access
        $superAdmin = Role::findOrCreate($superAdmin = 'Super Admin');
        $superAdmin->givePermissionTo(Permission::all());

        // 2. Admin - Hampir full access (tidak bisa manage users)
        $admin = Role::findOrCreate($admin = 'Admin');
        $adminPermissions = [
            'view_dashboard',
            // Master Data - All
            'view_products',
            'create_products',
            'edit_products',
            'delete_products',
            'export_products',
            'import_products',
            'view_categories',
            'create_categories',
            'edit_categories',
            'delete_categories',
            'view_divisions',
            'create_divisions',
            'edit_divisions',
            'delete_divisions',
            'view_racks',
            'create_racks',
            'edit_racks',
            'delete_racks',
            'view_suppliers',
            'create_suppliers',
            'edit_suppliers',
            'delete_suppliers',
            'view_members',
            'create_members',
            'edit_members',
            'delete_members',
            'view_salesmen',
            'create_salesmen',
            'edit_salesmen',
            'delete_salesmen',
            // Transactions - All
            'view_sales',
            'create_sales',
            'edit_sales',
            'delete_sales',
            'view_purchases',
            'create_purchases',
            'edit_purchases',
            'delete_purchases',
            // Reports
            'view_reports',
            'export_reports',
            // Shifts
            'view_shifts',
            'open_shifts',
            'close_shifts',
            // Settings
            'view_settings',
            'edit_settings',
        ];
        $admin->givePermissionTo($adminPermissions);

        // 3. Manager - Bisa lihat semua, edit terbatas
        $manager = Role::findOrCreate($manager = 'Manager');
        $managerPermissions = [
            'view_dashboard',
            // Master Data - View & Edit only
            'view_products',
            'edit_products',
            'export_products',
            'view_categories',
            'edit_categories',
            'view_divisions',
            'edit_divisions',
            'view_racks',
            'edit_racks',
            'view_suppliers',
            'edit_suppliers',
            'view_members',
            'edit_members',
            'view_salesmen',
            'edit_salesmen',
            // Transactions
            'view_sales',
            'create_sales',
            'edit_sales',
            'view_purchases',
            'create_purchases',
            'edit_purchases',
            // Reports
            'view_reports',
            'export_reports',
            // Shifts
            'view_shifts',
            'open_shifts',
            'close_shifts',
        ];
        $manager->givePermissionTo($managerPermissions);

        // 4. Cashier - Fokus ke penjualan
        $cashier = Role::findOrCreate($cashier = 'Cashier');
        $cashierPermissions = [
            'view_dashboard',
            // Master Data - View only
            'view_products',
            'view_members',
            // Transactions - Sales focus
            'view_sales',
            'create_sales',
            // Shifts
            'view_shifts',
            'open_shifts',
            'close_shifts',
        ];
        $cashier->givePermissionTo($cashierPermissions);

        // 5. Inventory Staff - Fokus ke inventory
        $inventory = Role::findOrCreate($inventory = 'Inventory Staff');
        $inventoryPermissions = [
            'view_dashboard',
            // Master Data - Products focus
            'view_products',
            'create_products',
            'edit_products',
            'export_products',
            'import_products',
            'view_categories',
            'create_categories',
            'edit_categories',
            'view_racks',
            'create_racks',
            'edit_racks',
            'view_suppliers',
            // Transactions - Purchase focus
            'view_purchases',
            'create_purchases',
            'edit_purchases',
        ];
        $inventory->givePermissionTo($inventoryPermissions);

        // Assign Super Admin role to first user (assumed to be you)
        $user = User::first();
        if ($user) {
            $user->assignRole('Super Admin');
        }

        $this->command->info('Roles and permissions created successfully!');
        $this->command->info('Available roles: Super Admin, Admin, Manager, Cashier, Inventory Staff');
    }
}
