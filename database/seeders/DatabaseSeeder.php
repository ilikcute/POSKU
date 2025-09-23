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
            'view_products',
            'view_categories',
            'view_divisions',
            'view_racks',
            'view_suppliers',
            'view_members',
            'view_salesmen',
        ]);
        $kasirRole->givePermissionTo([
            'view_dashboard',
            'view_shifts',
            'open_shifts',
            'close_shifts',
        ]);
        $this->call([
            StoreSeeder::class,
            CategorySeeder::class,
            SupplierSeeder::class,
            SalesmanSeeder::class,
            SupplierSeeder::class,
            CategorySeeder::class,
            StockSeeder::class,
            rackSeeder::class,
            DivisionSeeder::class,
            AuthorizationSeeder::class,
            RolePermissionSeeder::class,
        ]);
        $mainStore = Store::create([
            'name' => 'Toko Utama',
            'address' => 'Jl. Jend. Sudirman No. 1',
            'is_main_store' => true,
        ])->first();
        $mainStore = \App\Models\Store::where('is_main_store', true)->first();
        if ($mainStore) {
            $adminUser = User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'store_id' => $mainStore->id,
            ]);
            $adminUser->assignRole($adminRole);

            $kasirUser = User::factory()->create([
                'name' => 'Kasir User',
                'email' => 'kasir@example.com',
                'store_id' => $mainStore->id,
            ]);
            $kasirUser->assignRole($kasirRole);
            $superadminUser = User::factory()->create([
                'name' => 'Superadmin User',
                'email' => 'superadmin@example.com',
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
