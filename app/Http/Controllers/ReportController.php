<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Stock;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Show reports dashboard
     */
    public function index()
    {
        return Inertia::render('Reports/Index');
    }

    /**
     * Sales report
     */
    public function sales(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());
        $storeId = auth()->user()->store_id ?? 1;

        $query = Sale::with(['user', 'member', 'saleDetails.product'])
            ->byStore($storeId)
            ->whereBetween('transaction_date', [$startDate, $endDate]);

        $sales = $query->orderBy('transaction_date', 'desc')->get();

        // Calculate summary
        $totalSales = $sales->sum('final_amount');
        $totalTransactions = $sales->count();
        $totalItems = $sales->sum(function ($sale) {
            return $sale->saleDetails->sum('quantity');
        });
        $totalProfit = $sales->sum(function ($sale) {
            return $sale->getTotalProfit();
        });

        // Group by date
        $dailySales = $sales->groupBy(function ($sale) {
            return $sale->transaction_date->format('Y-m-d');
        })->map(function ($group) {
            return [
                'date' => $group->first()->transaction_date->format('Y-m-d'),
                'total_amount' => $group->sum('final_amount'),
                'total_transactions' => $group->count(),
                'total_items' => $group->sum(function ($sale) {
                    return $sale->saleDetails->sum('quantity');
                }),
            ];
        })->values();

        // Top selling products
        $topProducts = DB::table('sale_details')
            ->join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->join('products', 'sale_details.product_id', '=', 'products.id')
            ->where('sales.store_id', $storeId)
            ->whereBetween('sales.transaction_date', [$startDate, $endDate])
            ->select(
                'products.name',
                'products.product_code',
                DB::raw('SUM(sale_details.quantity) as total_quantity'),
                DB::raw('SUM(sale_details.subtotal) as total_amount')
            )
            ->groupBy('products.id', 'products.name', 'products.product_code')
            ->orderBy('total_quantity', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('Reports/Sales', [
            'sales' => $sales,
            'summary' => [
                'total_sales' => $totalSales,
                'total_transactions' => $totalTransactions,
                'total_items' => $totalItems,
                'total_profit' => $totalProfit,
                'average_transaction' => $totalTransactions > 0 ? $totalSales / $totalTransactions : 0,
            ],
            'daily_sales' => $dailySales,
            'top_products' => $topProducts,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    /**
     * Purchase report
     */
    public function purchases(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());
        $storeId = auth()->user()->store_id ?? 1;

        $query = Purchase::with(['user', 'member', 'purchaseDetails.product'])
            ->byStore($storeId)
            ->whereBetween('transaction_date', [$startDate, $endDate]);

        $purchases = $query->orderBy('transaction_date', 'desc')->get();

        // Calculate summary
        $totalPurchases = $purchases->sum('final_amount');
        $totalTransactions = $purchases->count();
        $totalItems = $purchases->sum(function ($purchase) {
            return $purchase->purchaseDetails->sum('quantity');
        });

        // Group by date
        $dailyPurchases = $purchases->groupBy(function ($purchase) {
            return $purchase->transaction_date->format('Y-m-d');
        })->map(function ($group) {
            return [
                'date' => $group->first()->transaction_date->format('Y-m-d'),
                'total_amount' => $group->sum('final_amount'),
                'total_transactions' => $group->count(),
                'total_items' => $group->sum(function ($purchase) {
                    return $purchase->purchaseDetails->sum('quantity');
                }),
            ];
        })->values();

        return Inertia::render('Reports/Purchases', [
            'purchases' => $purchases,
            'summary' => [
                'total_purchases' => $totalPurchases,
                'total_transactions' => $totalTransactions,
                'total_items' => $totalItems,
                'average_transaction' => $totalTransactions > 0 ? $totalPurchases / $totalTransactions : 0,
            ],
            'daily_purchases' => $dailyPurchases,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    /**
     * Stock report
     */
    public function stock(Request $request)
    {
        $storeId = auth()->user()->store_id ?? 1;
        
        $query = Stock::with(['product.category', 'product.supplier'])
            ->byStore($storeId);

        // Filter by category
        if ($request->has('category_id') && $request->category_id) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        $stocks = $query->orderBy('quantity', 'asc')->get();

        // Calculate summary
        $totalProducts = $stocks->count();
        $lowStockCount = $stocks->filter(function ($stock) {
            return $stock->isLowStock();
        })->count();
        $outOfStockCount = $stocks->where('quantity', 0)->count();
        $totalStockValue = $stocks->sum(function ($stock) {
            return $stock->getStockValue();
        });
        $totalSellingValue = $stocks->sum(function ($stock) {
            return $stock->getSellingValue();
        });

        // Low stock items
        $lowStockItems = $stocks->filter(function ($stock) {
            return $stock->isLowStock();
        })->take(20);

        // Top value products
        $topValueProducts = $stocks->sortByDesc(function ($stock) {
            return $stock->getStockValue();
        })->take(20);

        // Get categories for filter
        $categories = \App\Models\Category::orderBy('name')->get();

        return Inertia::render('Reports/Stock', [
            'stocks' => $stocks,
            'summary' => [
                'total_products' => $totalProducts,
                'low_stock_count' => $lowStockCount,
                'out_of_stock_count' => $outOfStockCount,
                'total_stock_value' => $totalStockValue,
                'total_selling_value' => $totalSellingValue,
                'potential_profit' => $totalSellingValue - $totalStockValue,
            ],
            'low_stock_items' => $lowStockItems->values(),
            'top_value_products' => $topValueProducts->values(),
            'categories' => $categories,
            'filters' => $request->only(['category_id']),
        ]);
    }

    /**
     * Stock movement report
     */
    public function stockMovements(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());
        $storeId = auth()->user()->store_id ?? 1;

        $query = StockMovement::with(['stock.product', 'user'])
            ->whereHas('stock', function ($q) use ($storeId) {
                $q->where('store_id', $storeId);
            })
            ->whereBetween('created_at', [$startDate, $endDate]);

        // Filter by movement type
        if ($request->has('movement_type') && $request->movement_type) {
            $query->where('movement_type', $request->movement_type);
        }

        // Filter by product
        if ($request->has('product_id') && $request->product_id) {
            $query->whereHas('stock', function ($q) use ($request) {
                $q->where('product_id', $request->product_id);
            });
        }

        $movements = $query->orderBy('created_at', 'desc')->get();

        // Calculate summary
        $totalMovements = $movements->count();
        $totalInbound = $movements->where('movement_type', 'in')->sum('quantity');
        $totalOutbound = $movements->where('movement_type', 'out')->sum('quantity');

        // Group by reason
        $movementsByReason = $movements->groupBy('reason')->map(function ($group, $reason) {
            return [
                'reason' => $reason,
                'count' => $group->count(),
                'total_inbound' => $group->where('movement_type', 'in')->sum('quantity'),
                'total_outbound' => $group->where('movement_type', 'out')->sum('quantity'),
            ];
        })->values();

        $products = Product::orderBy('name')->get();

        return Inertia::render('Reports/StockMovements', [
            'movements' => $movements,
            'summary' => [
                'total_movements' => $totalMovements,
                'total_inbound' => $totalInbound,
                'total_outbound' => $totalOutbound,
                'net_movement' => $totalInbound - $totalOutbound,
            ],
            'movements_by_reason' => $movementsByReason,
            'products' => $products,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'movement_type' => $request->get('movement_type'),
                'product_id' => $request->get('product_id'),
            ],
        ]);
    }

    /**
     * Profit and loss report
     */
    public function profitLoss(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());
        $storeId = auth()->user()->store_id ?? 1;

        // Sales data
        $sales = Sale::byStore($storeId)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->get();

        // Purchase data
        $purchases = Purchase::byStore($storeId)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->get();

        // Calculate totals
        $totalRevenue = $sales->sum('final_amount');
        $totalCOGS = $purchases->sum('final_amount');
        $grossProfit = $totalRevenue - $totalCOGS;

        // Calculate by product
        $productProfits = DB::table('sale_details')
            ->join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->join('products', 'sale_details.product_id', '=', 'products.id')
            ->where('sales.store_id', $storeId)
            ->whereBetween('sales.transaction_date', [$startDate, $endDate])
            ->select(
                'products.name',
                'products.product_code',
                'products.purchase_price',
                DB::raw('SUM(sale_details.quantity) as total_quantity'),
                DB::raw('SUM(sale_details.subtotal) as total_revenue'),
                DB::raw('SUM(sale_details.quantity * products.purchase_price) as total_cogs')
            )
            ->groupBy('products.id', 'products.name', 'products.product_code', 'products.purchase_price')
            ->get()
            ->map(function ($item) {
                $item->gross_profit = $item->total_revenue - $item->total_cogs;
                $item->profit_margin = $item->total_revenue > 0 ? ($item->gross_profit / $item->total_revenue) * 100 : 0;
                return $item;
            });

        return Inertia::render('Reports/ProfitLoss', [
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_cogs' => $totalCOGS,
                'gross_profit' => $grossProfit,
                'profit_margin' => $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0,
            ],
            'product_profits' => $productProfits,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    /**
     * Export report data
     */
    public function export(Request $request)
    {
        $reportType = $request->get('type');
        
        // Here you would implement actual export functionality using Maatwebsite\Excel
        // For now, return JSON response
        return response()->json([
            'message' => 'Export functionality not implemented yet',
            'type' => $reportType,
            'filters' => $request->all()
        ]);
    }
}