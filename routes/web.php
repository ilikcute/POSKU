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
        'canRegister' => Route::has('register')
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

    // Purchase Management Routes
    Route::middleware('check.permission:view_purchases')->group(function () {
        Route::get('purchases', [App\Http\Controllers\PurchaseController::class, 'index'])->name('purchases.index');
        Route::get('purchases/create', [App\Http\Controllers\PurchaseController::class, 'create'])
            ->name('purchases.create')
            ->middleware('check.permission:create_purchases');
        Route::get('purchases/{purchase}', [App\Http\Controllers\PurchaseController::class, 'show'])->name('purchases.show');
        Route::get('purchases/{purchase}/print', [App\Http\Controllers\PurchaseController::class, 'print'])->name('purchases.print');
        Route::get('purchases/{purchase}/pdf', [App\Http\Controllers\PurchaseController::class, 'generatePDF'])->name('purchases.pdf');
    });

    Route::post('purchases', [App\Http\Controllers\PurchaseController::class, 'store'])
        ->name('purchases.store')
        ->middleware('check.permission:create_purchases');
    Route::get('purchases/{purchase}/edit', [App\Http\Controllers\PurchaseController::class, 'edit'])
        ->name('purchases.edit')
        ->middleware('check.permission:edit_purchases');
    Route::patch('purchases/{purchase}', [App\Http\Controllers\PurchaseController::class, 'update'])
        ->name('purchases.update')
        ->middleware('check.permission:edit_purchases');
    Route::delete('purchases/{purchase}', [App\Http\Controllers\PurchaseController::class, 'destroy'])
        ->name('purchases.destroy')
        ->middleware('check.permission:delete_purchases');

    // Purchase Return Management Routes
    Route::middleware('check.permission:view_purchase_returns')->group(function () {
        Route::get('purchase-returns', [App\Http\Controllers\PurchaseReturnController::class, 'index'])->name('purchase-returns.index');
        Route::get('purchase-returns/create', [App\Http\Controllers\PurchaseReturnController::class, 'create'])
            ->name('purchase-returns.create')
            ->middleware('check.permission:create_purchase_returns');
        Route::get('purchase-returns/{purchaseReturn}', [App\Http\Controllers\PurchaseReturnController::class, 'show'])->name('purchase-returns.show');
        Route::get('purchases/{purchase}/returnable-items', [App\Http\Controllers\PurchaseReturnController::class, 'getReturnableItems'])
            ->name('purchases.returnable-items');
    });

    Route::post('purchase-returns', [App\Http\Controllers\PurchaseReturnController::class, 'store'])
        ->name('purchase-returns.store')
        ->middleware('check.permission:create_purchase_returns');
    Route::get('purchase-returns/{purchaseReturn}/edit', [App\Http\Controllers\PurchaseReturnController::class, 'edit'])
        ->name('purchase-returns.edit')
        ->middleware('check.permission:edit_purchase_returns');
    Route::patch('purchase-returns/{purchaseReturn}', [App\Http\Controllers\PurchaseReturnController::class, 'update'])
        ->name('purchase-returns.update')
        ->middleware('check.permission:edit_purchase_returns');
    Route::delete('purchase-returns/{purchaseReturn}', [App\Http\Controllers\PurchaseReturnController::class, 'destroy'])
        ->name('purchase-returns.destroy')
        ->middleware('check.permission:delete_purchase_returns');

    // Sales Management Routes
    Route::middleware('check.permission:view_sales')->group(function () {
        Route::get('sales', [App\Http\Controllers\SaleController::class, 'index'])->name('sales.index');
        Route::get('sales/create', [App\Http\Controllers\SaleController::class, 'create'])
            ->name('sales.create')
            ->middleware('check.permission:create_sales');
        Route::get('sales/{sale}', [App\Http\Controllers\SaleController::class, 'show'])->name('sales.show');
        Route::get('sales/{sale}/print', [App\Http\Controllers\SaleController::class, 'print'])->name('sales.print');
        Route::get('sales/{sale}/pdf', [App\Http\Controllers\SaleController::class, 'generatePDF'])->name('sales.pdf');
        Route::post('sales/{sale}/whatsapp', [App\Http\Controllers\SaleController::class, 'sendWhatsApp'])->name('sales.whatsapp');
        Route::get('products/{product}/stock', [App\Http\Controllers\SaleController::class, 'getProductStock'])->name('products.stock');
    });

    Route::post('sales', [App\Http\Controllers\SaleController::class, 'store'])
        ->name('sales.store')
        ->middleware('check.permission:create_sales');
    Route::get('sales/{sale}/edit', [App\Http\Controllers\SaleController::class, 'edit'])
        ->name('sales.edit')
        ->middleware('check.permission:edit_sales');
    Route::patch('sales/{sale}', [App\Http\Controllers\SaleController::class, 'update'])
        ->name('sales.update')
        ->middleware('check.permission:edit_sales');
    Route::delete('sales/{sale}', [App\Http\Controllers\SaleController::class, 'destroy'])
        ->name('sales.destroy')
        ->middleware('check.permission:delete_sales');

    // Sales Return Management Routes
    Route::middleware('check.permission:view_sales_returns')->group(function () {
        Route::get('sales-returns', [App\Http\Controllers\SalesReturnController::class, 'index'])->name('sales-returns.index');
        Route::get('sales-returns/create', [App\Http\Controllers\SalesReturnController::class, 'create'])
            ->name('sales-returns.create')
            ->middleware('check.permission:create_sales_returns');
        Route::get('sales-returns/{salesReturn}', [App\Http\Controllers\SalesReturnController::class, 'show'])->name('sales-returns.show');
        Route::get('sales/{sale}/returnable-items', [App\Http\Controllers\SalesReturnController::class, 'getReturnableItems'])
            ->name('sales.returnable-items');
    });

    Route::post('sales-returns', [App\Http\Controllers\SalesReturnController::class, 'store'])
        ->name('sales-returns.store')
        ->middleware('check.permission:create_sales_returns');
    Route::get('sales-returns/{salesReturn}/edit', [App\Http\Controllers\SalesReturnController::class, 'edit'])
        ->name('sales-returns.edit')
        ->middleware('check.permission:edit_sales_returns');
    Route::patch('sales-returns/{salesReturn}', [App\Http\Controllers\SalesReturnController::class, 'update'])
        ->name('sales-returns.update')
        ->middleware('check.permission:edit_sales_returns');
    Route::delete('sales-returns/{salesReturn}', [App\Http\Controllers\SalesReturnController::class, 'destroy'])
        ->name('sales-returns.destroy')
        ->middleware('check.permission:delete_sales_returns');

    // Stock Management Routes
    Route::middleware('check.permission:view_stock')->group(function () {
        Route::get('stock', [App\Http\Controllers\StockController::class, 'index'])->name('stock.index');
        Route::get('stock/movements', [App\Http\Controllers\StockController::class, 'movements'])->name('stock.movements');
        Route::get('stock/opname', [App\Http\Controllers\StockController::class, 'opname'])
            ->name('stock.opname')
            ->middleware('check.permission:stock_opname');
        Route::get('stock/{stock}', [App\Http\Controllers\StockController::class, 'show'])->name('stock.show');
        Route::get('stock/alerts/low-stock', [App\Http\Controllers\StockController::class, 'lowStockAlerts'])->name('stock.low-stock-alerts');
        Route::get('stock/summary', [App\Http\Controllers\StockController::class, 'summary'])->name('stock.summary');
        Route::get('stock/export', [App\Http\Controllers\StockController::class, 'export'])
            ->name('stock.export')
            ->middleware('check.permission:export_stock');
    });

    Route::post('stock/opname', [App\Http\Controllers\StockController::class, 'processOpname'])
        ->name('stock.opname.process')
        ->middleware('check.permission:stock_opname');
    Route::patch('stock/{stock}/adjust', [App\Http\Controllers\StockController::class, 'adjust'])
        ->name('stock.adjust')
        ->middleware('check.permission:adjust_stock');
    Route::post('stock/bulk-adjust', [App\Http\Controllers\StockController::class, 'bulkAdjust'])
        ->name('stock.bulk-adjust')
        ->middleware('check.permission:adjust_stock');

    // Reports Routes
    Route::middleware('check.permission:view_reports')->group(function () {
        Route::get('reports', [App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/sales', [App\Http\Controllers\ReportController::class, 'sales'])->name('reports.sales');
        Route::get('reports/purchases', [App\Http\Controllers\ReportController::class, 'purchases'])->name('reports.purchases');
        Route::get('reports/stock', [App\Http\Controllers\ReportController::class, 'stock'])->name('reports.stock');
        Route::get('reports/stock-movements', [App\Http\Controllers\ReportController::class, 'stockMovements'])->name('reports.stock-movements');
        Route::get('reports/profit-loss', [App\Http\Controllers\ReportController::class, 'profitLoss'])->name('reports.profit-loss');
        Route::get('reports/export', [App\Http\Controllers\ReportController::class, 'export'])
            ->name('reports.export')
            ->middleware('check.permission:export_reports');
    });

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
