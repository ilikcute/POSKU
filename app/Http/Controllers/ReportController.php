<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        return Inertia::render('Reports/Index');
    }

    public function sales(Request $request)
    {
        $storeId = $this->currentStoreId();
        $query = Sale::with(['user', 'store', 'member'])
            ->byStore($storeId);

        if ($request->start_date) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        $totals = (clone $query)
            ->selectRaw('COALESCE(SUM(final_amount),0) as total_sales, COALESCE(SUM(discount),0) as total_discount')
            ->first();

        $sales = $query->orderByDesc('transaction_date')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('Reports/Sales', [
            'sales' => $sales,
            'summary' => [
                'total_sales' => (float) $totals->total_sales,
                'total_discount' => (float) $totals->total_discount,
                'net_sales' => (float) $totals->total_sales - (float) $totals->total_discount,
            ],
            'filters' => $request->only(['start_date', 'end_date']),
        ]);
    }

    public function purchases(Request $request)
    {
        $storeId = $this->currentStoreId();
        $query = Purchase::with(['user', 'store', 'supplier'])
            ->byStore($storeId);

        if ($request->start_date) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        $totalPurchases = (clone $query)->sum('final_amount');

        $purchases = $query->orderByDesc('transaction_date')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('Reports/Purchases', [
            'purchases' => $purchases,
            'summary' => [
                'total_purchases' => (float) $totalPurchases,
            ],
            'filters' => $request->only(['start_date', 'end_date']),
        ]);
    }

    public function stock(Request $request)
    {
        $stocks = Stock::with(['product', 'store'])
            ->where('store_id', $this->currentStoreId())
            ->get();

        $totalValue = $stocks->sum(function ($stock) {
            return $stock->quantity * $stock->product->selling_price;
        });

        return Inertia::render('Reports/Stock', [
            'stocks' => $stocks,
            'summary' => [
                'total_items' => $stocks->count(),
                'total_value' => $totalValue,
            ],
        ]);
    }

    public function stockMovements(Request $request)
    {
        $storeId = $this->currentStoreId();
        $query = StockMovement::with(['stock.product', 'stock.store', 'user'])
            ->whereHas('stock', function ($q) use ($storeId) {
                $q->where('store_id', $storeId);
            });

        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $movements = $query->orderByDesc('created_at')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('Reports/StockMovements', [
            'movements' => $movements,
            'filters' => $request->only(['start_date', 'end_date']),
        ]);
    }

    public function profitLoss(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth();
        $endDate = $request->end_date ?? now()->endOfMonth();

        $sales = Sale::whereBetween('transaction_date', [$startDate, $endDate])
            ->byStore($this->currentStoreId())
            ->sum('final_amount');

        $purchases = Purchase::whereBetween('transaction_date', [$startDate, $endDate])
            ->byStore($this->currentStoreId())
            ->sum('final_amount');

        $profit = $sales - $purchases;

        return Inertia::render('Reports/ProfitLoss', [
            'summary' => [
                'total_sales' => $sales,
                'total_purchases' => $purchases,
                'profit_loss' => $profit,
            ],
            'filters' => compact('startDate', 'endDate'),
        ]);
    }

    public function export(Request $request)
    {
        // Implement export functionality
        return response()->json(['message' => 'Export not implemented yet']);
    }
}
