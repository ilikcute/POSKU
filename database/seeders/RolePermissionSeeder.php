<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create the 'view_promotions' permission if it doesn't exist
        $viewPromotionsPermission = Permission::firstOrCreate(['name' => 'view_promotions']);

        // Assign 'view_promotions' permission to Admin role
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        if (!$adminRole->hasPermissionTo($viewPromotionsPermission)) {
            $adminRole->givePermissionTo($viewPromotionsPermission);
        }

        // Optionally assign to other roles as needed
    }
}
