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

        $this->call([
            StoreSeeder::class,
            DivisionSeeder::class,
            CategorySeeder::class,
            SupplierSeeder::class,
            SalesmanSeeder::class,
            RackSeeder::class,
            AuthorizationSeeder::class,
            RolePermissionSeeder::class,
            CustomerTypeSeeder::class,
            CustomerSeeder::class,
            ProductSeeder::class,
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
            $adminRole = Role::firstOrCreate(['name' => 'Admin']);
            $supervisorRole = Role::firstOrCreate(['name' => 'Supervisor']);
            $kasirRole = Role::firstOrCreate(['name' => 'Kasir']);
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
