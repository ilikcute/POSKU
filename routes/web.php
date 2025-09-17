<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerTypeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Master\CategoryController;
use App\Http\Controllers\Master\DivisionController;
use App\Http\Controllers\Master\RackController;
use App\Http\Controllers\Master\SalesmanController;
use App\Http\Controllers\Master\SupplierController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\StoreProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Routes yang membutuhkan authentication tapi tidak memerlukan shift
Route::middleware(['auth', 'verified'])->group(function () {

    // Store Profile routes - Tidak perlu shift aktif
    Route::get('/store/profile', [StoreProfileController::class, 'edit'])->name('store.profile.edit');
    Route::patch('/store/profile', [StoreProfileController::class, 'update'])->name('store.profile.update');

    // Profile routes - Tidak perlu shift aktif
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Shift management routes - Tidak perlu shift aktif (karena untuk membuka/tutup shift)
    Route::get('/shifts/open', [ShiftController::class, 'showOpenShiftForm'])
        ->name('shifts.open.form')
        ->middleware('check.permission:open_shifts');
    Route::post('/shifts/open', [ShiftController::class, 'storeOpenShift'])
        ->name('shifts.open.store')
        ->middleware('check.permission:open_shifts');
    Route::get('/shifts/close', [ShiftController::class, 'showCloseShiftForm'])
        ->name('shifts.close.form')
        ->middleware('check.permission:close_shifts');
    Route::post('/shifts/close', [ShiftController::class, 'storeCloseShift'])
        ->name('shifts.close.store')
        ->middleware('check.permission:close_shifts');

    // Customer Types Routes
    Route::resource('customer-types', CustomerTypeController::class);

    // Customers Routes
    Route::resource('customers', CustomerController::class);

    // Promotions Routes
    Route::resource('promotions', PromotionController::class);
    Route::get('promotions-active', [PromotionController::class, 'activePromotions']);

    // Pricing Routes
    Route::post('price/check', [PriceController::class, 'getProductPrice']);
    Route::post('price/bulk-check', [PriceController::class, 'bulkPriceCheck']);

    // User Management routes - Tidak perlu shift aktif
    Route::middleware('check.permission:view_users,manage_roles')->prefix('admin')->name('admin.')->group(function () {
        Route::get('users', [App\Http\Controllers\UserController::class, 'index'])->name('users.index');
        Route::post('users', [App\Http\Controllers\UserController::class, 'store'])
            ->name('users.store')
            ->middleware('check.permission:create_users');
        Route::patch('users/{user}', [App\Http\Controllers\UserController::class, 'update'])
            ->name('users.update')
            ->middleware('check.permission:edit_users');
        Route::delete('users/{user}', [App\Http\Controllers\UserController::class, 'destroy'])
            ->name('users.destroy')
            ->middleware('check.permission:delete_users');
        Route::post('users/{user}/assign-role', [App\Http\Controllers\UserController::class, 'assignRole'])
            ->name('users.assign-role')
            ->middleware('check.permission:manage_roles');
        Route::post('users/{user}/revoke-role', [App\Http\Controllers\UserController::class, 'revokeRole'])
            ->name('users.revoke-role')
            ->middleware('check.permission:manage_roles');

        // Role & Permission Management
        Route::resource('roles', App\Http\Controllers\Admin\RolePermissionController::class)->except(['show', 'edit', 'create']);
    });
});

// Routes yang membutuhkan authentication + shift aktif
Route::middleware('shift_required')->group(function () {

    // Dashboard - Requires active shift
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard')
        ->middleware('check.permission:view_dashboard');

    // Master data routes - Semua memerlukan shift aktif
    Route::prefix('master')->name('master.')->group(function () {

        // Products routes
        Route::middleware('check.permission:view_products')->group(function () {
            Route::get('products', [ProductController::class, 'index'])->name('products.index');
            Route::get('products/export', [ProductController::class, 'export'])
                ->name('products.export')
                ->middleware('check.permission:export_products');
            Route::get('products/import', [ProductController::class, 'showImportForm'])
                ->name('products.import.form')
                ->middleware('check.permission:import_products');
            Route::get('products/import/template', [ProductController::class, 'downloadTemplate'])
                ->name('products.import.template')
                ->middleware('check.permission:import_products');
        });

        Route::post('products', [ProductController::class, 'store'])
            ->name('products.store')
            ->middleware('check.permission:create_products');
        Route::patch('products/{product}', [ProductController::class, 'update'])
            ->name('products.update')
            ->middleware('check.permission:edit_products');
        Route::delete('products/{product}', [ProductController::class, 'destroy'])
            ->name('products.destroy')
            ->middleware('check.permission:delete_products');
        Route::post('products/import', [ProductController::class, 'import'])
            ->name('products.import.store')
            ->middleware('check.permission:import_products');

        // Categories routes
        Route::middleware('check.permission:view_categories')->group(function () {
            Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        });
        Route::post('categories', [CategoryController::class, 'store'])
            ->name('categories.store')
            ->middleware('check.permission:create_categories');
        Route::patch('categories/{category}', [CategoryController::class, 'update'])
            ->name('categories.update')
            ->middleware('check.permission:edit_categories');
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])
            ->name('categories.destroy')
            ->middleware('check.permission:delete_categories');

        // Divisions routes
        Route::middleware('check.permission:view_divisions')->group(function () {
            Route::get('divisions', [DivisionController::class, 'index'])->name('divisions.index');
        });
        Route::post('divisions', [DivisionController::class, 'store'])
            ->name('divisions.store')
            ->middleware('check.permission:create_divisions');
        Route::patch('divisions/{division}', [DivisionController::class, 'update'])
            ->name('divisions.update')
            ->middleware('check.permission:edit_divisions');
        Route::delete('divisions/{division}', [DivisionController::class, 'destroy'])
            ->name('divisions.destroy')
            ->middleware('check.permission:delete_divisions');

        // Racks routes
        Route::middleware('check.permission:view_racks')->group(function () {
            Route::get('racks', [RackController::class, 'index'])->name('racks.index');
        });
        Route::post('racks', [RackController::class, 'store'])
            ->name('racks.store')
            ->middleware('check.permission:create_racks');
        Route::patch('racks/{rack}', [RackController::class, 'update'])
            ->name('racks.update')
            ->middleware('check.permission:edit_racks');
        Route::delete('racks/{rack}', [RackController::class, 'destroy'])
            ->name('racks.destroy')
            ->middleware('check.permission:delete_racks');

        // Suppliers routes
        Route::middleware('check.permission:view_suppliers')->group(function () {
            Route::get('suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
        });
        Route::post('suppliers', [SupplierController::class, 'store'])
            ->name('suppliers.store')
            ->middleware('check.permission:create_suppliers');
        Route::patch('suppliers/{supplier}', [SupplierController::class, 'update'])
            ->name('suppliers.update')
            ->middleware('check.permission:edit_suppliers');
        Route::delete('suppliers/{supplier}', [SupplierController::class, 'destroy'])
            ->name('suppliers.destroy')
            ->middleware('check.permission:delete_suppliers');

        // Members routes (now handled by CustomerController)
        Route::middleware('check.permission:view_members')->group(function () {
            Route::get('members', [CustomerController::class, 'index'])->name('members.index');
            Route::get('members/{customer}/card', [CustomerController::class, 'generateCard'])
                ->name('members.generate-card')
                ->middleware('check.permission:view_members');
        });
        Route::post('members', [CustomerController::class, 'store'])
            ->name('members.store')
            ->middleware('check.permission:create_members');
        Route::patch('members/{customer}', [CustomerController::class, 'update'])
            ->name('members.update')
            ->middleware('check.permission:edit_members');
        Route::delete('members/{customer}', [CustomerController::class, 'destroy'])
            ->name('members.destroy')
            ->middleware('check.permission:delete_members');

        // Salesmen routes
        Route::middleware('check.permission:view_salesmen')->group(function () {
            Route::get('salesmen', [SalesmanController::class, 'index'])->name('salesmen.index');
        });
        Route::post('salesmen', [SalesmanController::class, 'store'])
            ->name('salesmen.store')
            ->middleware('check.permission:create_salesmen');
        Route::patch('salesmen/{salesman}', [SalesmanController::class, 'update'])
            ->name('salesmen.update')
            ->middleware('check.permission:edit_salesmen');
        Route::delete('salesmen/{salesman}', [SalesmanController::class, 'destroy'])
            ->name('salesmen.destroy')
            ->middleware('check.permission:delete_salesmen');
    });
});

require __DIR__ . '/auth.php';
