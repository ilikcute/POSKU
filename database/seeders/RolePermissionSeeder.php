<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view_dashboard',
            'view_shifts',
            'open_shifts',
            'close_shifts',
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'manage_roles',
            'view_purchases',
            'create_purchases',
            'edit_purchases',
            'delete_purchases',
            'view_purchase_returns',
            'create_purchase_returns',
            'edit_purchase_returns',
            'delete_purchase_returns',
            'view_sales',
            'create_sales',
            'edit_sales',
            'delete_sales',
            'view_sales_returns',
            'create_sales_returns',
            'edit_sales_returns',
            'delete_sales_returns',
            'view_stock',
            'stock_opname',
            'adjust_stock',
            'view_stock_entries',
            'view_stock_adjustments',
            'view_reports',
            'export_reports',
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
            'view_promotions',
            'create_promotions',
            'edit_promotions',
            'delete_promotions',
            'view_stations',
            'create_stations',
            'edit_stations',
            'delete_stations',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $supervisorRole = Role::firstOrCreate(['name' => 'Supervisor']);
        $kasirRole = Role::firstOrCreate(['name' => 'Kasir']);
        $adminRole->givePermissionTo(Permission::all());
        $supervisorRole->givePermissionTo([
            'view_dashboard',
            'view_shifts',
            'open_shifts',
            'close_shifts',
            'view_purchases',
            'view_purchase_returns',
            'view_sales',
            'view_sales_returns',
            'view_stock',
            'view_stock_entries',
            'view_stock_adjustments',
            'view_reports',
            'view_products',
            'view_categories',
            'view_divisions',
            'view_racks',
            'view_suppliers',
            'view_members',
            'view_salesmen',
            'view_promotions',
            'view_stations',
        ]);
        $kasirRole->givePermissionTo([
            'view_dashboard',
            'view_shifts',
            'open_shifts',
            'close_shifts',
            'view_sales',
            'create_sales',
            'edit_sales',
            'delete_sales',
            'view_sales_returns',
            'create_sales_returns',
            'edit_sales_returns',
            'delete_sales_returns',
            'view_stock',
            'stock_opname',
            'view_products',
            'view_members',
            'view_promotions',
            'view_reports',
        ]);
    }
}
