<?php

use Inertia\Inertia;
use App\Models\Store;

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\EodController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\ShiftController;

use App\Http\Controllers\StockController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PurchaseController;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PromotionController;

use App\Http\Controllers\Master\RackController;
use App\Http\Controllers\SalesReturnController;

use App\Http\Controllers\CustomerTypeController;
use App\Http\Controllers\StoreProfileController;

use App\Http\Controllers\AuthorizationController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\Master\CategoryController;
use App\Http\Controllers\Master\DivisionController;
use App\Http\Controllers\Master\SalesmanController;

use App\Http\Controllers\Master\SupplierController;
use App\Http\Controllers\Admin\RolePermissionController;

// --- AKSES PUBLIK ---
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    $mainStore = \App\Models\Store::where('is_main_store', true)->first();
    $logoUrl = $mainStore && $mainStore->logo_path ? '/storage/' . $mainStore->logo_path : '/logo-posku.svg';
    $heroImageUrl = $mainStore && $mainStore->heroimage_path ? '/storage/' . $mainStore->heroimage_path : '/hero-modern-pos.svg';

    return Inertia::render('Welcome', [
        'canLogin' => \Illuminate\Support\Facades\Route::has('login'),
        'canRegister' => \Illuminate\Support\Facades\Route::has('register'),
        'logoUrl' => $logoUrl,
        'heroImageUrl' => $heroImageUrl,
    ]);
});


// --- LEVEL 1: LOGIN + VERIFIED (SHIFT BELUM WAJIB) ---
Route::middleware(['auth', 'verified'])->group(function () {

    // Profile user
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });

    // Profile store
    Route::controller(StoreProfileController::class)->group(function () {
        Route::get('/store/profile', 'edit')->name('store.profile.edit');
        Route::patch('/store/profile', 'update')->name('store.profile.update');
    });

    Route::prefix('eod')->name('eod.')->group(function () {
        Route::get('/', [\App\Http\Controllers\EodController::class, 'index'])->name('index');

        Route::get('/station-close', [\App\Http\Controllers\EodController::class, 'showStationCloseForm'])->name('station-close.form');
        Route::post('/station-close', [\App\Http\Controllers\EodController::class, 'storeStationClose'])->name('station-close.store');

        Route::get('/finalize', [\App\Http\Controllers\EodController::class, 'showFinalizeForm'])->name('finalize.form');
        Route::post('/finalize', [\App\Http\Controllers\EodController::class, 'storeFinalize'])->name('finalize.store');
    });


    // Shift (harus di luar check.shift agar tidak loop)
    Route::prefix('shifts')->name('shifts.')->group(function () {
        Route::controller(ShiftController::class)->group(function () {
            Route::get('/open', 'showOpenShiftForm')->name('open.form');
            Route::post('/open', 'storeOpenShift')->name('open.store');

            Route::get('/close', 'showCloseShiftForm')->name('close.form');
            Route::post('/close', 'storeCloseShift')->name('close.store');

            Route::get('/', 'index')->name('index');
        });

        Route::resource('authorizations', AuthorizationController::class);
    });

    // --- LEVEL 2: LOGIN + SHIFT AKTIF ---
    Route::middleware(['check.shift'])->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard')
            ->middleware('check.permission:view_dashboard');

        // ADMIN
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::resource('users', UserController::class);
            Route::post('users/{user}/assign-role', [UserController::class, 'assignRole'])->name('users.assign-role');
            Route::post('users/{user}/revoke-role', [UserController::class, 'revokeRole'])->name('users.revoke-role');

            Route::resource('roles', RolePermissionController::class)
                ->except(['show', 'edit', 'create']);
        });

        // PURCHASES
        Route::prefix('purchases')->name('purchases.')->controller(PurchaseController::class)->group(function () {

            Route::middleware('check.permission:view_purchases')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')
                    ->name('create')
                    ->middleware('check.permission:create_purchases');

                Route::get('/{purchase}', 'show')->name('show');
                Route::get('/{purchase}/print', 'print')->name('print');
                Route::get('/{purchase}/pdf', 'generatePDF')->name('pdf');
            });

            Route::post('/', 'store')
                ->name('store')
                ->middleware('check.permission:create_purchases');

            Route::get('/{purchase}/edit', 'edit')
                ->name('edit')
                ->middleware('check.permission:edit_purchases');

            Route::patch('/{purchase}', 'update')
                ->name('update')
                ->middleware('check.permission:edit_purchases');

            Route::delete('/{purchase}', 'destroy')
                ->name('destroy')
                ->middleware('check.permission:delete_purchases');
        });

        // PURCHASE RETURNS (pisah prefix supaya bersih)
        Route::prefix('purchase-returns')->name('purchase-returns.')->controller(PurchaseReturnController::class)->group(function () {

            Route::middleware('check.permission:view_purchase_returns')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')
                    ->name('create')
                    ->middleware('check.permission:create_purchase_returns');

                Route::get('/{purchaseReturn}', 'show')->name('show');
            });

            // endpoint tambahan terkait purchase
            Route::get('/purchases/{purchase}/returnable-items', 'getReturnableItems')
                ->name('purchases.returnable-items')
                ->middleware('check.permission:view_purchase_returns');

            Route::post('/', 'store')
                ->name('store')
                ->middleware('check.permission:create_purchase_returns');

            Route::get('/{purchaseReturn}/edit', 'edit')
                ->name('edit')
                ->middleware('check.permission:edit_purchase_returns');

            Route::patch('/{purchaseReturn}', 'update')
                ->name('update')
                ->middleware('check.permission:edit_purchase_returns');

            Route::delete('/{purchaseReturn}', 'destroy')
                ->name('destroy')
                ->middleware('check.permission:delete_purchase_returns');
        });

        // SALES
        Route::prefix('sales')->name('sales.')->controller(SaleController::class)->group(function () {

            Route::middleware('check.permission:view_sales')->group(function () {
                Route::get('/', 'index')->name('index');

                Route::get('/create', 'create')
                    ->name('create')
                    ->middleware('check.permission:create_sales');

                Route::get('/{sale}', 'show')->name('show');
                Route::get('/{sale}/print', 'print')->name('print');
                Route::get('/{sale}/pdf', 'generatePDF')->name('pdf');
                Route::post('/{sale}/whatsapp', 'sendWhatsApp')->name('whatsapp');

                // jika memang mau berada di bawah /sales
                Route::get('/products/{product}/stock', 'getProductStock')->name('products.stock');
            });

            Route::post('/', 'store')
                ->name('store')
                ->middleware('check.permission:create_sales');

            Route::get('/{sale}/edit', 'edit')
                ->name('edit')
                ->middleware('check.permission:edit_sales');

            Route::patch('/{sale}', 'update')
                ->name('update')
                ->middleware('check.permission:edit_sales');

            Route::delete('/{sale}', 'destroy')
                ->name('destroy')
                ->middleware('check.permission:delete_sales');
        });

        // SALES RETURNS
        Route::prefix('sales-returns')->name('sales-returns.')->controller(SalesReturnController::class)->group(function () {

            Route::middleware('check.permission:view_sales_returns')->group(function () {
                Route::get('/', 'index')->name('index');

                Route::get('/create', 'create')
                    ->name('create')
                    ->middleware('check.permission:create_sales_returns');

                Route::get('/{salesReturn}', 'show')->name('show');
                Route::get('/sales/{sale}/returnable-items', 'getReturnableItems')->name('sales.returnable-items');
            });

            Route::post('/', 'store')
                ->name('store')
                ->middleware('check.permission:create_sales_returns');

            Route::get('/{salesReturn}/edit', 'edit')
                ->name('edit')
                ->middleware('check.permission:edit_sales_returns');

            Route::patch('/{salesReturn}', 'update')
                ->name('update')
                ->middleware('check.permission:edit_sales_returns');

            Route::delete('/{salesReturn}', 'destroy')
                ->name('destroy')
                ->middleware('check.permission:delete_sales_returns');
        });

        // STOCK
        Route::prefix('stock')->name('stock.')->controller(StockController::class)->group(function () {

            Route::middleware('check.permission:view_stock')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/movements', 'movements')->name('movements');

                Route::get('/opname', 'opname')
                    ->name('opname')
                    ->middleware('check.permission:stock_opname');

                Route::get('/{stock}', 'show')->name('show');

                Route::get('/alerts/low-stock', 'lowStockAlerts')->name('low-stock-alerts');
                Route::get('/summary', 'summary')->name('summary');

                Route::get('/export', 'export')
                    ->name('export')
                    ->middleware('check.permission:export_stock');
            });

            Route::post('/opname', 'processOpname')
                ->name('opname.process')
                ->middleware('check.permission:stock_opname');
            Route::post('/opname/create', 'createOpname')
                ->name('opname.create')
                ->middleware('check.permission:stock_opname');
            Route::post('/opname/add-item', 'addOpnameItem')
                ->name('opname.add-item')
                ->middleware('check.permission:stock_opname');
            Route::post('/opname/upload', 'uploadOpnameItems')
                ->name('opname.upload')
                ->middleware('check.permission:stock_opname');
            Route::get('/opname/history', 'opnameHistory')
                ->name('opname.history')
                ->middleware('check.permission:stock_opname');
            Route::get('/opname/template', 'downloadOpnameTemplate')
                ->name('opname.template')
                ->middleware('check.permission:stock_opname');
            Route::patch('/opname/item/{item}', 'updateOpnameItem')
                ->name('opname.item.update')
                ->middleware('check.permission:stock_opname');
            Route::delete('/opname/item/{item}', 'deleteOpnameItem')
                ->name('opname.item.delete')
                ->middleware('check.permission:stock_opname');

            Route::patch('/{stock}/adjust', 'adjust')
                ->name('adjust')
                ->middleware('check.permission:adjust_stock');

            Route::post('/bulk-adjust', 'bulkAdjust')
                ->name('bulk-adjust')
                ->middleware('check.permission:adjust_stock');
        });

        // REPORTS
        Route::prefix('reports')->name('reports.')->controller(ReportController::class)->group(function () {

            Route::middleware('check.permission:view_reports')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/sales', 'sales')->name('sales');
                Route::get('/sales-details', 'salesDetails')->name('sales-details');
                Route::get('/sales-returns', 'salesReturns')->name('sales-returns');
                Route::get('/sales-reprint', 'salesReprint')->name('sales-reprint');
                Route::get('/purchases', 'purchases')->name('purchases');
                Route::get('/stock', 'stock')->name('stock');
                Route::get('/low-stock', 'lowStock')->name('low-stock');
                Route::get('/stock-mutation', 'stockMutation')->name('stock-mutation');
                Route::post('/stock-mutation/close', 'closeStockMonth')->name('stock-mutation.close');
                Route::get('/stock-movements', 'stockMovements')->name('stock-movements');
                Route::get('/profit-loss', 'profitLoss')->name('profit-loss');

                Route::get('/export', 'export')
                    ->name('export')
                    ->middleware('check.permission:export_reports');
            });
        });

        // CUSTOMER + CUSTOMER TYPES
        Route::resource('customer-types', CustomerTypeController::class);
        Route::resource('customers', CustomerController::class);

        // PROMOTIONS
    Route::resource('promotions', PromotionController::class);
    Route::patch('promotions/{promotion}/clear', [PromotionController::class, 'clear'])->name('promotions.clear');
    Route::get('promotions-active', [PromotionController::class, 'activePromotions'])->name('promotions.active');

    Route::prefix('stations')->name('stations.')->controller(\App\Http\Controllers\StationController::class)->group(function () {
        Route::middleware('check.permission:view_stations')->group(function () {
            Route::get('/', 'index')->name('index');
        });
        Route::post('/', 'store')->name('store')->middleware('check.permission:create_stations');
        Route::patch('/{station}', 'update')->name('update')->middleware('check.permission:edit_stations');
        Route::delete('/{station}', 'destroy')->name('destroy')->middleware('check.permission:delete_stations');
    });

        // PRICING
        Route::post('price/check', [PriceController::class, 'getProductPrice'])->name('price.check');
        Route::post('price/bulk-check', [PriceController::class, 'bulkPriceCheck'])->name('price.bulk-check');

        // MASTER
    Route::prefix('master')->name('master.')->group(function () {

            // PRODUCTS
            Route::prefix('products')->name('products.')->controller(ProductController::class)->group(function () {

                Route::middleware('check.permission:view_products')->group(function () {
                    Route::get('/', 'index')->name('index');

                    Route::get('/export', 'export')
                        ->name('export')
                        ->middleware('check.permission:export_products');

                    Route::get('/import', 'showImportForm')
                        ->name('import.form')
                        ->middleware('check.permission:import_products');

                    Route::get('/import/template', 'downloadTemplate')
                        ->name('import.template')
                        ->middleware('check.permission:import_products');
                });

                Route::get('/create', 'create')
                    ->name('create')
                    ->middleware('check.permission:create_products');

                Route::post('/', 'store')
                    ->name('store')
                    ->middleware('check.permission:create_products');

                Route::patch('/{product}', 'update')
                    ->name('update')
                    ->middleware('check.permission:edit_products');

                Route::delete('/{product}', 'destroy')
                    ->name('destroy')
                    ->middleware('check.permission:delete_products');

                Route::post('/import', 'import')
                    ->name('import.store')
                    ->middleware('check.permission:import_products');
            });

            // CATEGORIES
            Route::prefix('categories')->name('categories.')->controller(CategoryController::class)->group(function () {
                Route::middleware('check.permission:view_categories')->group(function () {
                    Route::get('/', 'index')->name('index');
                });

                Route::post('/', 'store')->name('store')->middleware('check.permission:create_categories');
                Route::patch('/{category}', 'update')->name('update')->middleware('check.permission:edit_categories');
                Route::delete('/{category}', 'destroy')->name('destroy')->middleware('check.permission:delete_categories');
            });

            // DIVISIONS
            Route::prefix('divisions')->name('divisions.')->controller(DivisionController::class)->group(function () {
                Route::middleware('check.permission:view_divisions')->group(function () {
                    Route::get('/', 'index')->name('index');
                });

                Route::post('/', 'store')->name('store')->middleware('check.permission:create_divisions');
                Route::patch('/{division}', 'update')->name('update')->middleware('check.permission:edit_divisions');
                Route::delete('/{division}', 'destroy')->name('destroy')->middleware('check.permission:delete_divisions');
            });

            // RACKS
            Route::prefix('racks')->name('racks.')->controller(RackController::class)->group(function () {
                Route::middleware('check.permission:view_racks')->group(function () {
                    Route::get('/', 'index')->name('index');
                });

                Route::post('/', 'store')->name('store')->middleware('check.permission:create_racks');
                Route::patch('/{rack}', 'update')->name('update')->middleware('check.permission:edit_racks');
                Route::delete('/{rack}', 'destroy')->name('destroy')->middleware('check.permission:delete_racks');
            });

            // SUPPLIERS
            Route::prefix('suppliers')->name('suppliers.')->controller(SupplierController::class)->group(function () {
                Route::middleware('check.permission:view_suppliers')->group(function () {
                    Route::get('/', 'index')->name('index');
                });

                Route::post('/', 'store')->name('store')->middleware('check.permission:create_suppliers');
                Route::patch('/{supplier}', 'update')->name('update')->middleware('check.permission:edit_suppliers');
                Route::delete('/{supplier}', 'destroy')->name('destroy')->middleware('check.permission:delete_suppliers');
            });

            // SALESMEN
            Route::prefix('salesmen')->name('salesmen.')->controller(SalesmanController::class)->group(function () {
                Route::middleware('check.permission:view_salesmen')->group(function () {
                    Route::get('/', 'index')->name('index');
                });

                Route::post('/', 'store')->name('store')->middleware('check.permission:create_salesmen');
                Route::patch('/{salesman}', 'update')->name('update')->middleware('check.permission:edit_salesmen');
                Route::delete('/{salesman}', 'destroy')->name('destroy')->middleware('check.permission:delete_salesmen');
            });

            // MEMBERS (mengarah ke CustomerController, dipertahankan sesuai desain Anda)
            Route::prefix('members')->name('members.')->controller(CustomerController::class)->group(function () {
                Route::middleware('check.permission:view_members')->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('/{customer}/card', 'generateCard')
                        ->name('generate-card')
                        ->middleware('check.permission:view_members');
                });

                Route::post('/', 'store')->name('store')->middleware('check.permission:create_members');
                Route::patch('/{customer}', 'update')->name('update')->middleware('check.permission:edit_members');
                Route::delete('/{customer}', 'destroy')->name('destroy')->middleware('check.permission:delete_members');
            });

            // BARCODES
            Route::prefix('barcodes')->name('barcodes.')->controller(BarcodeController::class)->group(function () {
                Route::middleware('check.permission:view_products')->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::post('/generate', 'generate')->name('generate');
                    Route::post('/print', 'print')->name('print');

                    Route::post('/generate-price-tags', 'generatePriceTags')->name('generate-price-tags');
                    Route::post('/print-price-tags', 'printPriceTags')->name('print-price-tags');

                    Route::post('/generate-customer-cards', 'generateCustomerCards')->name('generate-customer-cards');
                    Route::post('/print-customer-cards', 'printCustomerCards')->name('print-customer-cards');
                });
            });
        });
    });
});

require __DIR__ . '/auth.php';
