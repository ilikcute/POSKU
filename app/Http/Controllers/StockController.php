<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    /**
     * Display a listing of stock.
     */
    public function index(Request $request)
    {
        $storeId = $this->currentStoreId();
        $query = Stock::with(['product.category', 'product.supplier', 'store'])
            ->byStore($storeId);

        // Filter by category
        if ($request->has('category_id') && $request->category_id) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        // Filter by low stock
        if ($request->has('low_stock') && $request->low_stock) {
            $query->lowStock();
        }

        // Search by product name
        if ($request->has('search') && $request->search) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('product_code', 'like', '%' . $request->search . '%')
                    ->orWhere('barcode', 'like', '%' . $request->search . '%');
            });
        }

        $stocks = $query->orderBy('quantity', 'asc')
            ->paginate(20)
            ->withQueryString();

        // Get categories for filter
        $categories = \App\Models\Category::orderBy('name')->get();

        return Inertia::render('Stock/Index', [
            'stocks' => $stocks,
            'categories' => $categories,
            'filters' => $request->only(['category_id', 'low_stock', 'search']),
            'store' => $this->currentStore(),
        ]);
    }

    /**
     * Show stock movements for a product
     */
    public function movements(Request $request)
    {
        $storeId = $this->currentStoreId();
        $query = StockMovement::with(['stock.product', 'user'])
            ->whereHas('stock', function ($q) use ($storeId) {
                $q->where('store_id', $storeId);
            });

        // Filter by product
        if ($request->has('product_id') && $request->product_id) {
            $query->whereHas('stock', function ($q) use ($request) {
                $q->where('product_id', $request->product_id);
            });
        }

        // Filter by movement type
        if ($request->has('movement_type') && $request->movement_type) {
            $query->where('movement_type', $request->movement_type);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $movements = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $products = Product::orderBy('name')->get();

        return Inertia::render('Stock/Movements', [
            'movements' => $movements,
            'products' => $products,
            'filters' => $request->only(['product_id', 'movement_type', 'start_date', 'end_date']),
        ]);
    }

    /**
     * Show stock opname form
     */
    public function opname(Request $request)
    {
        $storeId = $this->currentStoreId();
        $query = Stock::with(['product.category', 'product.supplier', 'store'])
            ->byStore($storeId);

        // Filter by category
        if ($request->has('category_id') && $request->category_id) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        // Search by product name
        if ($request->has('search') && $request->search) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        $stocks = $query->paginate(15)
            ->withQueryString();

        $categories = \App\Models\Category::orderBy('name')->get();

        return Inertia::render('Stock/Opname', [
            'stocks' => $stocks,
            'categories' => $categories,
            'filters' => $request->only(['category_id', 'search']),
        ]);
    }

    /**
     * Process stock opname adjustments
     */
    public function processOpname(Request $request)
    {
        $request->validate([
            'adjustments' => 'required|array|min:1',
            'adjustments.*.stock_id' => 'required|exists:stocks,id',
            'adjustments.*.physical_count' => 'required|integer|min:0',
            'adjustments.*.reason' => 'nullable|string|max:255',
        ]);

        $storeId = $this->currentStoreId();

        DB::transaction(function () use ($request, $storeId) {
            foreach ($request->adjustments as $adjustment) {
                $stock = Stock::findOrFail($adjustment['stock_id']);

                // Verify stock belongs to current store
                if ($stock->store_id !== $storeId) {
                    continue;
                }

                $physicalCount = $adjustment['physical_count'];
                $systemCount = $stock->quantity;

                if ($physicalCount !== $systemCount) {
                    $reason = $adjustment['reason'] ?? 'Stock Opname Adjustment';

                    $stock->adjustStock(
                        $physicalCount,
                        $reason,
                        Auth::id()
                    );
                }
            }
        });

        return back()->with('success', 'Stock opname completed successfully.');
    }

    /**
     * Adjust individual stock
     */
    public function adjust(Request $request, Stock $stock)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0',
            'reason' => 'required|string|max:255',
        ]);

        // Verify stock belongs to current store
        if ($stock->store_id !== $this->currentStoreId()) {
            return back()->withErrors(['stock' => 'Invalid stock selected.']);
        }

        $stock->adjustStock(
            $request->quantity,
            $request->reason,
            Auth::id()
        );

        return back()->with('success', 'Stock adjusted successfully.');
    }

    /**
     * Show individual stock details
     */
    public function show(Stock $stock)
    {
        $stock->load(['product.category', 'product.supplier', 'store']);

        // Get recent stock movements
        $movements = $stock->stockMovements()
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return Inertia::render('Stock/Show', [
            'stock' => $stock,
            'movements' => $movements,
        ]);
    }

    /**
     * Get low stock alerts
     */
    public function lowStockAlerts()
    {
        $lowStocks = Stock::lowStock()
            ->with(['product.category', 'product.supplier'])
            ->byStore($this->currentStoreId())
            ->orderBy('quantity', 'asc')
            ->get();

        return response()->json($lowStocks);
    }

    /**
     * Export stock data
     */
    public function export(Request $request)
    {
        $query = Stock::with(['product.category', 'product.supplier'])
            ->byStore($this->currentStoreId());

        // Apply same filters as index
        if ($request->has('category_id') && $request->category_id) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        if ($request->has('low_stock') && $request->low_stock) {
            $query->lowStock();
        }

        if ($request->has('search') && $request->search) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('product_code', 'like', '%' . $request->search . '%')
                    ->orWhere('barcode', 'like', '%' . $request->search . '%');
            });
        }

        $stocks = $query->orderBy('quantity', 'asc')->get();

        // Here you would use Maatwebsite\Excel to export
        // For now, return JSON
        return response()->json([
            'message' => 'Export functionality not implemented yet',
            'data' => $stocks->map(function ($stock) {
                return [
                    'product_code' => $stock->product->product_code,
                    'product_name' => $stock->product->name,
                    'category' => $stock->product->category->name ?? '',
                    'quantity' => $stock->quantity,
                    'purchase_price' => $stock->product->purchase_price,
                    'selling_price' => $stock->product->selling_price,
                    'stock_value' => $stock->getStockValue(),
                    'is_low_stock' => $stock->isLowStock(),
                ];
            })
        ]);
    }

    /**
     * Bulk stock adjustment
     */
    public function bulkAdjust(Request $request)
    {
        $request->validate([
            'adjustments' => 'required|array|min:1',
            'adjustments.*.product_id' => 'required|exists:products,id',
            'adjustments.*.quantity' => 'required|integer|min:0',
            'adjustments.*.reason' => 'required|string|max:255',
        ]);

        $storeId = $this->currentStoreId();

        DB::transaction(function () use ($request, $storeId) {
            foreach ($request->adjustments as $adjustment) {
                $stock = Stock::firstOrCreate(
                    [
                        'product_id' => $adjustment['product_id'],
                        'store_id' => $storeId,
                    ],
                    ['quantity' => 0]
                );

                $stock->adjustStock(
                    $adjustment['quantity'],
                    $adjustment['reason'],
                    Auth::id()
                );
            }
        });

        return back()->with('success', 'Bulk stock adjustment completed successfully.');
    }

    /**
     * Get stock summary statistics
     */
    public function summary()
    {
        $storeId = $this->currentStoreId();

        $totalProducts = Stock::byStore($storeId)->count();
        $lowStockCount = Stock::lowStock()->byStore($storeId)->count();
        $outOfStockCount = Stock::byStore($storeId)->where('quantity', 0)->count();

        $totalStockValue = Stock::byStore($storeId)
            ->with('product')
            ->get()
            ->sum(function ($stock) {
                return $stock->getStockValue();
            });

        return response()->json([
            'total_products' => $totalProducts,
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
            'total_stock_value' => $totalStockValue,
        ]);
    }
}
