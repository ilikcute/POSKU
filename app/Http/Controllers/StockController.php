<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StockOpnameTemplateExport;

class StockController extends Controller
{
    /**
     * Display a listing of stock.
     */
    public function index(Request $request)
    {
        $query = Stock::with(['product.category', 'product.supplier'])
            ->latest();

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
        ]);
    }

    /**
     * Show stock movements for a product
     */
    public function movements(Request $request)
    {
        $query = StockMovement::with(['stock.product', 'user'])
            ->latest();

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
     * Show stock opname form (document based).
     */
    public function opname(Request $request)
    {
        $docno = $request->string('docno')->toString();

        $opname = null;
        $items = collect();

        if ($docno !== '') {
            $opname = \App\Models\StockOpname::query()
                ->where('docno', $docno)
                ->first();

            if ($opname) {
                $items = $opname->items()
                    ->with('product')
                    ->orderBy('id')
                    ->get();
            }
        }

        return Inertia::render('Stock/Opname', [
            'docno' => $docno,
            'opname' => $opname,
            'items' => $items,
        ]);
    }

    public function opnameHistory(Request $request)
    {
        $opnames = StockOpname::query()
            ->withCount('items')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Stock/OpnameHistory', [
            'opnames' => $opnames,
        ]);
    }

    /**
     * Process stock opname adjustments (finalize docno).
     */
    public function processOpname(Request $request)
    {
        $validated = $request->validate([
            'docno' => ['required', 'string'],
        ]);

        $opname = StockOpname::query()
            ->where('docno', $validated['docno'])
            ->firstOrFail();

        if ($opname->status === 'A') {
            return back()->with('warning', 'Opname sudah di-adjust.');
        }

        $items = $opname->items()
            ->with('product')
            ->get();

        if ($items->where('status', 'I')->count() > 0) {
            return back()->with('warning', 'Masih ada item status I. Lengkapi fisik terlebih dahulu sebelum finalize.');
        }

        DB::transaction(function () use ($opname, $items) {
            foreach ($items as $item) {
                if ($item->status !== 'E') {
                    continue;
                }

                $stock = Stock::firstOrCreate(
                    [
                        'product_id' => $item->product_id,
                    ],
                    ['quantity' => 0]
                );

                $physicalCount = (int) $item->physical_qty;
                $systemCount = (int) $stock->quantity;

                if ($physicalCount !== $systemCount) {
                    $stock->adjustStock(
                        $physicalCount,
                        'Stock Opname Adjustment',
                        Auth::id()
                    );
                }

                $item->update(['status' => 'A']);
            }

            $opname->update([
                'status' => 'A',
                'adjusted_at' => now(),
            ]);
        });

        return redirect()->route('stock.opname', ['docno' => $opname->docno])
            ->with('success', 'Stock opname berhasil di-adjust.');
    }

    public function createOpname(Request $request)
    {
        $maxDocno = StockOpname::query()
            ->max('docno');

        $nextDocno = $maxDocno ? (string) ((int) $maxDocno + 1) : '1';

        $opname = StockOpname::create([
            'docno' => $nextDocno,
            'created_by' => Auth::id(),
            'status' => 'I',
            'notes' => $request->input('notes'),
        ]);

        return redirect()->route('stock.opname', ['docno' => $opname->docno])
            ->with('success', 'Dokumen opname dibuat.');
    }

    public function addOpnameItem(Request $request)
    {
        $validated = $request->validate([
            'docno' => ['required', 'string'],
            'product_code' => ['required', 'string'],
        ]);

        $opname = StockOpname::query()
            ->where('docno', $validated['docno'])
            ->firstOrFail();

        if ($opname->status === 'A') {
            return back()->with('warning', 'Opname sudah di-adjust.');
        }

        $product = Product::query()
            ->where('product_code', $validated['product_code'])
            ->first();

        if (! $product) {
            return back()->with('warning', 'Product code tidak ditemukan.');
        }

        $exists = $opname->items()
            ->where('product_id', $product->id)
            ->exists();

        if (! $exists) {
            $systemQty = $product->stocks()->value('quantity') ?? 0;

            $opname->items()->create([
                'product_id' => $product->id,
                'system_qty' => (int) $systemQty,
                'physical_qty' => 0,
                'status' => 'I',
            ]);
        }

        return back()->with('success', 'Item ditambahkan.');
    }

    public function updateOpnameItem(Request $request, StockOpnameItem $item)
    {
        $validated = $request->validate([
            'physical_qty' => ['required', 'integer', 'min:0'],
        ]);

        $opname = $item->opname;
        if (! $opname) {
            return back()->with('error', 'Item opname tidak valid.');
        }

        if ($item->status === 'A') {
            return back()->with('warning', 'Item sudah di-adjust.');
        }

        $item->update([
            'physical_qty' => (int) $validated['physical_qty'],
            'status' => 'E',
        ]);

        $opname->update(['status' => 'E']);

        return back()->with('success', 'Item diperbarui.');
    }

    public function deleteOpnameItem(StockOpnameItem $item)
    {
        $opname = $item->opname;
        if (! $opname) {
            return back()->with('error', 'Item opname tidak valid.');
        }

        if ($opname->status === 'A' || $item->status === 'A') {
            return back()->with('warning', 'Item sudah di-adjust.');
        }

        $item->delete();

        return back()->with('success', 'Item dihapus.');
    }

    public function uploadOpnameItems(Request $request)
    {
        $validated = $request->validate([
            'docno' => ['required', 'string'],
            'file' => ['required', 'file', 'mimes:xlsx,csv,txt'],
        ]);

        $opname = StockOpname::query()
            ->where('docno', $validated['docno'])
            ->firstOrFail();

        if ($opname->status === 'A') {
            return back()->with('warning', 'Opname sudah di-adjust.');
        }

        $rows = Excel::toArray([], $validated['file']);
        $codes = collect($rows[0] ?? [])
            ->map(fn($row) => $row[0] ?? null)
            ->filter()
            ->map(fn($code) => trim((string) $code))
            ->unique()
            ->values();

        $products = Product::query()
            ->whereIn('product_code', $codes)
            ->get()
            ->keyBy('product_code');

        $inserted = 0;
        $skipped = 0;

        foreach ($codes as $code) {
            $product = $products->get($code);
            if (! $product) {
                $skipped++;
                continue;
            }

            $exists = $opname->items()
                ->where('product_id', $product->id)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            $systemQty = $product->stocks()->value('quantity') ?? 0;

            $opname->items()->create([
                'product_id' => $product->id,
                'system_qty' => (int) $systemQty,
                'physical_qty' => 0,
                'status' => 'I',
            ]);
            $inserted++;
        }

        $message = "Upload berhasil. Ditambahkan: {$inserted}, dilewati: {$skipped}.";

        return back()->with('success', $message);
    }

    public function downloadOpnameTemplate()
    {
        return Excel::download(new StockOpnameTemplateExport(), 'stock_opname_template.xlsx');
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
        $stock->load(['product.category', 'product.supplier']);

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
            ->latest();

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

        DB::transaction(function () use ($request) {
            foreach ($request->adjustments as $adjustment) {
                $stock = Stock::firstOrCreate(
                    [
                        'product_id' => $adjustment['product_id'],
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
        $totalProducts = Stock::count();
        $lowStockCount = Stock::lowStock()->count();
        $outOfStockCount = Stock::where('quantity', 0)->count();

        $totalStockValue = Stock::with('product')
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
