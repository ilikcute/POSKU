<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Purchase;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\SalesReturn;
use App\Models\StockMonthClosing;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

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

    public function salesDetails(Request $request)
    {
        $storeId = $this->currentStoreId();
        $query = SaleDetail::query()
            ->with(['sale.user', 'sale.customer', 'product'])
            ->whereHas('sale', function ($q) use ($storeId, $request) {
                $q->byStore($storeId);
                if ($request->start_date) {
                    $q->whereDate('transaction_date', '>=', $request->start_date);
                }
                if ($request->end_date) {
                    $q->whereDate('transaction_date', '<=', $request->end_date);
                }
            });

        $summary = (clone $query)
            ->selectRaw('COALESCE(SUM(quantity),0) as total_qty')
            ->selectRaw('COALESCE(SUM(subtotal),0) as total_subtotal')
            ->selectRaw('COALESCE(SUM(discount_per_item * quantity),0) as total_discount')
            ->selectRaw('COALESCE(SUM(tax_amount),0) as total_tax')
            ->first();

        $details = $query->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('Reports/SalesDetails', [
            'details' => $details,
            'summary' => [
                'total_qty' => (int) $summary->total_qty,
                'total_subtotal' => (float) $summary->total_subtotal,
                'total_discount' => (float) $summary->total_discount,
                'total_tax' => (float) $summary->total_tax,
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

    public function salesReturns(Request $request)
    {
        $storeId = $this->currentStoreId();
        $query = SalesReturn::query()
            ->with(['sale', 'user', 'station'])
            ->where('store_id', $storeId);

        if ($request->start_date) {
            $query->whereDate('return_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('return_date', '<=', $request->end_date);
        }

        $summary = (clone $query)
            ->selectRaw('COALESCE(SUM(final_amount),0) as total_returns')
            ->selectRaw('COUNT(*) as total_count')
            ->first();

        $returns = $query->orderByDesc('return_date')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('Reports/SalesReturns', [
            'returns' => $returns,
            'summary' => [
                'total_returns' => (float) $summary->total_returns,
                'total_count' => (int) $summary->total_count,
            ],
            'filters' => $request->only(['start_date', 'end_date']),
        ]);
    }

    public function salesReprint(Request $request)
    {
        $storeId = $this->currentStoreId();
        $query = Sale::with(['user', 'customer'])
            ->byStore($storeId);

        if ($request->start_date) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        if ($request->search) {
            $query->where('invoice_number', 'like', '%' . $request->search . '%');
        }

        $sales = $query->orderByDesc('transaction_date')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('Reports/SalesReprint', [
            'sales' => $sales,
            'filters' => $request->only(['start_date', 'end_date', 'search']),
        ]);
    }

    public function stock(Request $request)
    {
        $stocks = Stock::with(['product', 'store'])
            ->where('store_id', $this->currentStoreId())
            ->get();

        $totalValue = $stocks->sum(function ($stock) {
            $price = $stock->product->purchase_price ?? 0;
            return $stock->quantity * $price;
        });

        return Inertia::render('Reports/Stock', [
            'stocks' => $stocks,
            'summary' => [
                'total_items' => $stocks->count(),
                'total_value' => $totalValue,
            ],
        ]);
    }

    public function lowStock(Request $request)
    {
        $storeId = $this->currentStoreId();
        $query = Stock::with(['product.category', 'product.supplier'])
            ->byStore($storeId)
            ->lowStock();

        if ($request->search) {
            $search = $request->search;
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('product_code', 'like', '%' . $search . '%')
                    ->orWhere('barcode', 'like', '%' . $search . '%');
            });
        }

        $stocks = $query->orderBy('quantity', 'asc')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('Reports/LowStock', [
            'stocks' => $stocks,
            'filters' => $request->only(['search']),
        ]);
    }

    public function stockMutation(Request $request)
    {
        $storeId = $this->currentStoreId();
        $year = (int) ($request->input('year') ?? now()->year);
        $month = (int) ($request->input('month') ?? now()->month);

        $query = StockMonthClosing::query()
            ->with(['product.category', 'product.supplier'])
            ->where('store_id', $storeId)
            ->where('year', $year)
            ->where('month', $month);

        if ($request->filled('category_id')) {
            $categoryId = $request->input('category_id');
            $query->whereHas('product', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('product_code', 'like', '%' . $search . '%')
                    ->orWhere('barcode', 'like', '%' . $search . '%');
            });
        }

        $closings = $query->orderBy('id')
            ->paginate(50)
            ->withQueryString();

        $summary = [
            'opening_qty' => (int) $query->clone()->sum('opening_qty'),
            'closing_qty' => (int) $query->clone()->sum('closing_qty'),
            'in_qty_purchase' => (int) $query->clone()->sum('in_qty_purchase'),
            'in_qty_sales_return' => (int) $query->clone()->sum('in_qty_sales_return'),
            'out_qty_sale' => (int) $query->clone()->sum('out_qty_sale'),
            'out_qty_purchase_return' => (int) $query->clone()->sum('out_qty_purchase_return'),
            'adjustment_qty' => (int) $query->clone()->sum('adjustment_qty'),
            'opening_value_dpp' => (float) $query->clone()->sum('opening_value_dpp'),
            'opening_value_final' => (float) $query->clone()->sum('opening_value_final'),
            'closing_value_dpp' => (float) $query->clone()->sum('closing_value_dpp'),
            'closing_value_final' => (float) $query->clone()->sum('closing_value_final'),
        ];

        $categories = \App\Models\Category::orderBy('name')->get();

        $alreadyClosed = StockMonthClosing::query()
            ->where('store_id', $storeId)
            ->where('year', $year)
            ->where('month', $month)
            ->exists();

        return Inertia::render('Reports/StockMutation', [
            'closings' => $closings,
            'summary' => $summary,
            'filters' => $request->only(['year', 'month', 'category_id', 'search']),
            'categories' => $categories,
            'alreadyClosed' => $alreadyClosed,
        ]);
    }

    public function closeStockMonth(Request $request)
    {
        $storeId = $this->currentStoreId();

        $validated = $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $year = (int) $validated['year'];
        $month = (int) $validated['month'];

        $exists = StockMonthClosing::query()
            ->where('store_id', $storeId)
            ->where('year', $year)
            ->where('month', $month)
            ->exists();

        if ($exists) {
            return back()->with('warning', 'Closing bulan tersebut sudah dilakukan.');
        }

        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = Carbon::create($year, $month, 1)->endOfMonth();

        $stocks = Stock::with('product')
            ->where('store_id', $storeId)
            ->get();

        $stockIds = $stocks->pluck('id')->all();

        $periodMovements = StockMovement::query()
            ->whereIn('stock_id', $stockIds)
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->selectRaw("
                stock_id,
                SUM(CASE WHEN movement_type = 'in' THEN quantity ELSE 0 END) as total_in,
                SUM(CASE WHEN movement_type = 'out' THEN quantity ELSE 0 END) as total_out,
                SUM(CASE WHEN movement_type = 'in' AND reference_type = 'purchase' THEN quantity ELSE 0 END) as in_purchase,
                SUM(CASE WHEN movement_type = 'in' AND reference_type = 'sales_return' THEN quantity ELSE 0 END) as in_sales_return,
                SUM(CASE WHEN movement_type = 'out' AND reference_type = 'sale' THEN quantity ELSE 0 END) as out_sale,
                SUM(CASE WHEN movement_type = 'out' AND reference_type = 'purchase_return' THEN quantity ELSE 0 END) as out_purchase_return,
                SUM(CASE
                    WHEN reference_type NOT IN ('purchase', 'sales_return', 'sale', 'purchase_return')
                        THEN CASE WHEN movement_type = 'in' THEN quantity ELSE -quantity END
                    ELSE 0
                END) as adjustment_qty
            ")
            ->groupBy('stock_id')
            ->get()
            ->keyBy('stock_id');

        $beforeMovements = StockMovement::query()
            ->whereIn('stock_id', $stockIds)
            ->where('created_at', '<', $periodStart)
            ->selectRaw("stock_id, SUM(CASE WHEN movement_type = 'in' THEN quantity ELSE -quantity END) as net_qty")
            ->groupBy('stock_id')
            ->get()
            ->keyBy('stock_id');

        $prevMonth = $periodStart->copy()->subMonth();
        $prevClosings = StockMonthClosing::query()
            ->where('store_id', $storeId)
            ->where('year', $prevMonth->year)
            ->where('month', $prevMonth->month)
            ->get()
            ->keyBy('product_id');

        DB::transaction(function () use (
            $stocks,
            $periodMovements,
            $beforeMovements,
            $prevClosings,
            $storeId,
            $year,
            $month,
            $validated
        ) {
            foreach ($stocks as $stock) {
                $product = $stock->product;
                if (! $product) {
                    continue;
                }

                $period = $periodMovements->get($stock->id);
                $inPurchase = (int) ($period->in_purchase ?? 0);
                $inSalesReturn = (int) ($period->in_sales_return ?? 0);
                $outSale = (int) ($period->out_sale ?? 0);
                $outPurchaseReturn = (int) ($period->out_purchase_return ?? 0);
                $adjustmentQty = (int) ($period->adjustment_qty ?? 0);
                $netPeriod = (int) (($period->total_in ?? 0) - ($period->total_out ?? 0));

                $prevClosing = $prevClosings->get($product->id);
                if ($prevClosing) {
                    $openingQty = (int) $prevClosing->closing_qty;
                } else {
                    $before = $beforeMovements->get($stock->id);
                    $openingQty = $before ? (int) $before->net_qty : (int) max(0, $stock->quantity - $netPeriod);
                }

                $closingQty = $openingQty + $netPeriod;

                $priceDpp = (float) ($product->purchase_price ?? 0);
                $priceFinal = (float) ($product->final_price ?? $product->selling_price ?? 0);

                StockMonthClosing::create([
                    'store_id' => $storeId,
                    'product_id' => $product->id,
                    'year' => $year,
                    'month' => $month,
                    'opening_qty' => $openingQty,
                    'opening_value_dpp' => $openingQty * $priceDpp,
                    'opening_value_final' => $openingQty * $priceFinal,
                    'in_qty_purchase' => $inPurchase,
                    'in_qty_sales_return' => $inSalesReturn,
                    'out_qty_sale' => $outSale,
                    'out_qty_purchase_return' => $outPurchaseReturn,
                    'adjustment_qty' => $adjustmentQty,
                    'closing_qty' => $closingQty,
                    'closing_value_dpp' => $closingQty * $priceDpp,
                    'closing_value_final' => $closingQty * $priceFinal,
                    'closed_by' => auth()->id(),
                    'closed_at' => now(),
                    'notes' => $validated['notes'] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('reports.stock-mutation', ['year' => $year, 'month' => $month])
            ->with('success', 'Closing mutasi stok berhasil dibuat.');
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
        if ($request->search) {
            $search = $request->search;
            $query->whereHas('stock.product', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('product_code', 'like', '%' . $search . '%')
                    ->orWhere('barcode', 'like', '%' . $search . '%');
            });
        }

        $movements = $query->orderByDesc('created_at')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('Reports/StockMovements', [
            'movements' => $movements,
            'filters' => $request->only(['start_date', 'end_date', 'search']),
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
