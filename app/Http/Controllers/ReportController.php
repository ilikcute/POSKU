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
        $query = Sale::with(['user', 'store', 'member'])
            ->byStore(auth()->user()->store_id ?? 1);

        if ($request->start_date) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        $sales = $query->get();

        $totalSales = $sales->sum('final_amount');
        $totalDiscount = $sales->sum('discount');

        return Inertia::render('Reports/Sales', [
            'sales' => $sales,
            'summary' => [
                'total_sales' => $totalSales,
                'total_discount' => $totalDiscount,
                'net_sales' => $totalSales - $totalDiscount,
            ],
            'filters' => $request->only(['start_date', 'end_date']),
        ]);
    }

    public function purchases(Request $request)
    {
        $query = Purchase::with(['user', 'store', 'supplier'])
            ->byStore(auth()->user()->store_id ?? 1);

        if ($request->start_date) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        $purchases = $query->get();

        $totalPurchases = $purchases->sum('final_amount');

        return Inertia::render('Reports/Purchases', [
            'purchases' => $purchases,
            'summary' => [
                'total_purchases' => $totalPurchases,
            ],
            'filters' => $request->only(['start_date', 'end_date']),
        ]);
    }

    public function stock(Request $request)
    {
        $stocks = Stock::with(['product', 'store'])
            ->where('store_id', auth()->user()->store_id ?? 1)
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
        $query = StockMovement::with(['product', 'user', 'store'])
            ->where('store_id', auth()->user()->store_id ?? 1);

        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $movements = $query->orderBy('created_at', 'desc')->get();

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
            ->byStore(auth()->user()->store_id ?? 1)
            ->sum('final_amount');

        $purchases = Purchase::whereBetween('transaction_date', [$startDate, $endDate])
            ->byStore(auth()->user()->store_id ?? 1)
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
