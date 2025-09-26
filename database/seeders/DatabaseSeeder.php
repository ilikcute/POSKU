<?php

namespace Database\Seeders;

use App\Models\Product;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

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
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $adminRole = Role::findOrCreate($adminRole = 'Admin');
        $supervisorRole = Role::findOrCreate($supervisorRole = 'Supervisor');
        $kasirRole = Role::findOrCreate($kasirRole = 'Kasir');
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
            'view_products',
            'view_members',
            'view_promotions',
        ]);
        $this->call([
            StoreSeeder::class,
            CategorySeeder::class,
            SupplierSeeder::class,
            SalesmanSeeder::class,
            RackSeeder::class,
            DivisionSeeder::class,
            AuthorizationSeeder::class,
            RolePermissionSeeder::class,
            ProductSeeder::class,
            CustomerSeeder::class,
            CustomerTypeSeeder::class,
            PromotionSeeder::class,
            PurchaseSeeder::class,
            SaleSeeder::class,
            ShiftSeeder::class,
        ]);
        $mainStore = Store::create([
            'name' => 'Toko Utama',
            'address' => 'Jl. Jend. Sudirman No. 1',
            'is_main_store' => true,
        ])->first();
        $mainStore = \App\Models\Store::where('is_main_store', true)->first();
        if ($mainStore) {
            $adminUser = User::firstOrCreate([
                'email' => 'admin@example.com',
            ], [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'store_id' => $mainStore->id,
            ]);
            $adminUser->assignRole($adminRole);

            $kasirUser = User::firstOrCreate([
                'email' => 'kasir@example.com',
            ], [
                'name' => 'Kasir User',
                'password' => bcrypt('password'),
                'store_id' => $mainStore->id,
            ]);
            $kasirUser->assignRole($kasirRole);

            $superadminUser = User::firstOrCreate([
                'email' => 'superadmin@example.com',
            ], [
                'name' => 'Superadmin User',
                'password' => bcrypt('password'),
                'store_id' => $mainStore->id,
            ]);
            $superadminUser->assignRole($supervisorRole);
        }

        // Buat data dummy produk
        Product::factory(50)->create();
        $this->call(StockSeeder::class);
        $this->call(PricingSystemSeeder::class);
    }
}
