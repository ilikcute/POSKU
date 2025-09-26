<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SyncPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync permissions from routes and assign to Admin role';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Scanning routes for permissions...');

        $permissions = collect();

        foreach (Route::getRoutes() as $route) {
            $middlewares = $route->middleware();
            foreach ($middlewares as $middleware) {
                if (str_starts_with($middleware, 'check.permission:')) {
                    $permission = str_replace('check.permission:', '', $middleware);
                    $permissions->push($permission);
                }
            }
        }

        $uniquePermissions = $permissions->unique()->values();

        $this->info('Found ' . $uniquePermissions->count() . ' permissions.');

        foreach ($uniquePermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
            $this->line('Ensured permission: ' . $perm);
        }

        // Assign all permissions to Admin role
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminRole->givePermissionTo(Permission::all());

        $this->info('All permissions assigned to Admin role.');
        $this->info('Sync completed.');
    }
}
