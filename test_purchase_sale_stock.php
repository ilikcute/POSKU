<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

echo "Testing Purchase and Sale Stock Adjustments...\n";

// Get or create test data
$userId = \App\Models\User::first()->id ?? 1;
$storeId = 1;
$product = Product::where('product_code', 'TEST003')->first();
if (!$product) {
    $product = Product::create([
        'product_code' => 'TEST003',
        'name' => 'Test Product for Purchase/Sale',
        'purchase_price' => 10000,
        'selling_price' => 15000,
        'unit' => 'pcs'
    ]);
    $product->syncStock();
}
$productId = $product->id;

$supplier = Supplier::first();
if (!$supplier) {
    $supplier = Supplier::create(['name' => 'Test Supplier', 'phone' => '123456789']);
}

$customer = Customer::first();
if (!$customer) {
    $customer = Customer::create(['name' => 'Test Customer', 'phone' => '987654321']);
}

echo "Test Product ID: $productId\n";
echo "Initial Stock: " . $product->fresh()->stock . "\n";

// Test Purchase Creation
echo "\n--- Testing Purchase Creation ---\n";
$initialMovements = StockMovement::count();

$purchase = Purchase::create([
    'user_id' => $userId,
    'store_id' => $storeId,
    'total_amount' => 10000,
    'discount' => 0,
    'tax' => 0,
    'final_amount' => 10000,
    'payment_method' => 'cash',
    'amount_paid' => 10000,
    'change_due' => 0,
    'transaction_date' => now(),
]);

PurchaseDetail::create([
    'purchase_id' => $purchase->id,
    'product_id' => $productId,
    'quantity' => 5,
    'price_at_sale' => 10000,
    'subtotal' => 50000,
]);

// Simulate stock update like in controller
$stock = Stock::firstOrCreate(
    ['product_id' => $productId, 'store_id' => $storeId],
    ['quantity' => 0]
);
$stock->addStock(5, 'Purchase', 'purchase', $purchase->id, $userId);

echo "After Purchase: Product Stock = " . $product->fresh()->stock . "\n";
echo "Stock Movements Added: " . (StockMovement::count() - $initialMovements) . "\n";

$latestMovement = StockMovement::latest()->first();
echo "Latest Movement: {$latestMovement->movement_type}, Qty: {$latestMovement->quantity}, Reason: {$latestMovement->reason}\n";

// Test Sale Creation
echo "\n--- Testing Sale Creation ---\n";
$initialMovements = StockMovement::count();

$sale = Sale::create([
    'user_id' => $userId,
    'store_id' => $storeId,
    'total_amount' => 15000,
    'discount' => 0,
    'tax' => 0,
    'final_amount' => 15000,
    'payment_method' => 'cash',
    'amount_paid' => 15000,
    'change_due' => 0,
    'transaction_date' => now(),
]);

SaleDetail::create([
    'sale_id' => $sale->id,
    'product_id' => $productId,
    'quantity' => 2,
    'price_at_sale' => 15000,
    'subtotal' => 30000,
]);

// Simulate stock update like in controller
$stock = Stock::where('product_id', $productId)->where('store_id', $storeId)->first();
if ($stock) {
    $stock->reduceStock(2, 'Sale', 'sale', $sale->id, $userId);
}

echo "After Sale: Product Stock = " . $product->fresh()->stock . "\n";
echo "Stock Movements Added: " . (StockMovement::count() - $initialMovements) . "\n";

$latestMovement = StockMovement::latest()->first();
echo "Latest Movement: {$latestMovement->movement_type}, Qty: {$latestMovement->quantity}, Reason: {$latestMovement->reason}\n";

// Test Purchase Update (Revert)
echo "\n--- Testing Purchase Update (Revert) ---\n";
$initialMovements = StockMovement::count();

// Simulate revert like in controller
$stock = Stock::where('product_id', $productId)->where('store_id', $storeId)->first();
if ($stock) {
    $stock->reduceStock(5, 'Purchase Update - Revert', 'purchase_revert', $purchase->id, $userId);
}

echo "After Purchase Revert: Product Stock = " . $product->fresh()->stock . "\n";
echo "Stock Movements Added: " . (StockMovement::count() - $initialMovements) . "\n";

$latestMovement = StockMovement::latest()->first();
echo "Latest Movement: {$latestMovement->movement_type}, Qty: {$latestMovement->quantity}, Reason: {$latestMovement->reason}\n";

// Test Sale Delete (Revert)
echo "\n--- Testing Sale Delete (Revert) ---\n";
$initialMovements = StockMovement::count();

// Simulate revert like in controller
$stock = Stock::firstOrCreate(
    ['product_id' => $productId, 'store_id' => $storeId],
    ['quantity' => 0]
);
$stock->addStock(2, 'Sale Deleted', 'sale_delete', $sale->id, $userId);

echo "After Sale Revert: Product Stock = " . $product->fresh()->stock . "\n";
echo "Stock Movements Added: " . (StockMovement::count() - $initialMovements) . "\n";

$latestMovement = StockMovement::latest()->first();
echo "Latest Movement: {$latestMovement->movement_type}, Qty: {$latestMovement->quantity}, Reason: {$latestMovement->reason}\n";

// Summary
echo "\n--- Summary ---\n";
echo "Final Product Stock: " . $product->fresh()->stock . "\n";
echo "Total Stock Movements: " . StockMovement::count() . "\n";

$movements = StockMovement::whereHas('stock.product', function ($q) use ($productId) {
    $q->where('id', $productId);
})->orderBy('created_at')->get();

echo "All Movements for Product:\n";
foreach ($movements as $m) {
    echo "- {$m->created_at}: {$m->movement_type} {$m->quantity} ({$m->reason}) - Old: {$m->old_quantity}, New: {$m->new_quantity}\n";
}

echo "\nTest completed.\n";
