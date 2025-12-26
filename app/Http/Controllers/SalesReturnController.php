<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SalesReturn;
use App\Models\SalesReturnDetail;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;
use App\Services\StationResolver;
use Illuminate\Validation\Rule;

class SalesReturnController extends Controller
{
    /**
     * Display a listing of sales returns.
     */
    public function index(Request $request)
    {
        $storeId = $this->currentStoreId();
        $query = SalesReturn::with(['sale', 'user', 'store', 'salesReturnDetails.product'])
            ->where('store_id', $storeId);

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('return_date', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('return_date', '<=', $request->end_date);
        }

        $returns = $query->orderBy('return_date', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('SalesReturns/Index', [
            'returns' => $returns,
            'filters' => $request->only(['start_date', 'end_date']),
        ]);
    }

    /**
     * Show the form for creating a new sales return.
     */
    public function create(Request $request)
    {
        $storeId = $this->currentStoreId();
        $saleId = $request->get('sale_id');
        $sale = null;
        
        if ($saleId) {
            $sale = Sale::with(['saleDetails.product'])
                ->where('id', $saleId)
                ->where('store_id', $storeId)
                ->first();
        }

        $sales = Sale::with(['saleDetails'])
            ->where('store_id', $storeId)
            ->orderBy('transaction_date', 'desc')
            ->limit(50)
            ->get();

        return Inertia::render('SalesReturns/Create', [
            'sale' => $sale,
            'sales' => $sales,
            'shift_id' => $shift->id,
            'station_id' => $station->id,

        ]);
    }

    /**
     * Store a newly created sales return in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'nullable|numeric|min:0',
            'payment_method' => ['nullable', Rule::in(['cash', 'debit', 'credit', 'transfer'])],
            'notes' => 'nullable|string|max:1000',
        ]);

        $storeId = $this->currentStoreId();
        $station = StationResolver::resolve();
        $sale = Sale::findOrFail($validated['sale_id']);

        $station = StationResolver::resolve();

        $shift = Shift::query()
            ->where('store_id', $storeId)
            ->where('station_id', $station->id)
            ->where('status', 'open')
            ->latest('start_time')
            ->firstOrFail();


        // Validate that sale belongs to current store
        if ($sale->store_id !== $storeId) {
            return back()->withErrors(['sale_id' => 'Invalid sale selected.']);
        }

        $returnables = $this->calculateSaleReturnables($sale);

        DB::transaction(function () use ($validated, $sale, $returnables, $station) {
            // Calculate totals
            $totalAmount = 0;
            $items = collect($validated['items'])->map(function ($item) use ($returnables) {
                $info = $returnables[$item['product_id']] ?? null;
                if (! $info || $info['returnable_quantity'] <= 0) {
                    throw ValidationException::withMessages(['items' => 'Produk tidak valid untuk retur.']);
                }
                if ($item['quantity'] > $info['returnable_quantity']) {
                    throw ValidationException::withMessages([
                        'items' => "Kuantitas retur melebihi sisa yang dapat diretur untuk {$info['product']->name}.",
                    ]);
                }

                $unitPrice = $info['price_at_sale'];

                return [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'subtotal' => $unitPrice * $item['quantity'],
                ];
            });

            $totalAmount = $items->sum('subtotal');

            $finalAmount = $totalAmount;

            // Create sales return
            $paymentMethod = $validated['payment_method'] ?? $sale->payment_method ?? 'cash';
            $salesReturn = SalesReturn::create([
                'sale_id' => $sale->id,
                'user_id' => Auth::id(),
                'store_id' => $sale->store_id,
                'station_id' => $station->id,
                'total_amount' => $totalAmount,
                'final_amount' => $finalAmount,
                'payment_method' => $paymentMethod,
                'notes' => $validated['notes'],
                'return_date' => now(),
            ]);

            // Create sales return details and update stock
            foreach ($items as $item) {
                SalesReturnDetail::create([
                    'sales_return_id' => $salesReturn->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price_at_return' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                ]);

                // Update stock (add back because items are returned)
                $stock = Stock::firstOrCreate(
                    [
                        'product_id' => $item['product_id'],
                        'store_id' => $sale->store_id,
                    ],
                    ['quantity' => 0]
                );
                
                $stock->addStock(
                    $item['quantity'],
                    'Sales Return',
                    'sales_return',
                    $salesReturn->id,
                    Auth::id()
                );
            }
        });

        return redirect()->route('sales-returns.index')
            ->with('success', 'Sales return created successfully.');
    }

    /**
     * Display the specified sales return.
     */
    public function show(SalesReturn $salesReturn)
    {
        $salesReturn->load([
            'sale',
            'user', 
            'store', 
            'salesReturnDetails.product'
        ]);

        return Inertia::render('SalesReturns/Show', [
            'salesReturn' => $salesReturn,
        ]);
    }

    /**
     * Show the form for editing the specified sales return.
     */
    public function edit(SalesReturn $salesReturn)
    {
        $salesReturn->load([
            'sale.saleDetails.product',
            'salesReturnDetails.product'
        ]);

        return Inertia::render('SalesReturns/Edit', [
            'salesReturn' => $salesReturn,
        ]);
    }

    /**
     * Update the specified sales return in storage.
     */
    public function update(Request $request, SalesReturn $salesReturn)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'nullable|numeric|min:0',
            'payment_method' => ['nullable', Rule::in(['cash', 'debit', 'credit', 'transfer'])],
            'notes' => 'nullable|string|max:1000',
        ]);

        $returnables = $this->calculateSaleReturnables($salesReturn->sale, $salesReturn->id);

        DB::transaction(function () use ($validated, $salesReturn, $returnables) {
            // Revert previous stock changes (remove the returned items)
            foreach ($salesReturn->salesReturnDetails as $detail) {
                $stock = Stock::where('product_id', $detail->product_id)
                    ->where('store_id', $salesReturn->store_id)
                    ->first();
                    
                if ($stock) {
                    $stock->reduceStock(
                        $detail->quantity,
                        'Sales Return Update - Revert',
                        'sales_return_revert',
                        $salesReturn->id,
                        Auth::id()
                    );
                }
            }

            // Delete old sales return details
            $salesReturn->salesReturnDetails()->delete();

            // Calculate new totals
            $items = collect($validated['items'])->map(function ($item) use ($returnables) {
                $info = $returnables[$item['product_id']] ?? null;
                if (! $info || $info['returnable_quantity'] <= 0) {
                    throw ValidationException::withMessages(['items' => 'Produk tidak valid untuk retur.']);
                }
                if ($item['quantity'] > $info['returnable_quantity']) {
                    throw ValidationException::withMessages([
                        'items' => "Kuantitas retur melebihi batas untuk {$info['product']->name}.",
                    ]);
                }

                $unitPrice = $info['price_at_sale'];

                return [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'subtotal' => $unitPrice * $item['quantity'],
                ];
            });

            $totalAmount = $items->sum('subtotal');

            $finalAmount = $totalAmount;

            // Update sales return
            $paymentMethod = $validated['payment_method'] ?? $salesReturn->payment_method ?? 'cash';
            $salesReturn->update([
                'total_amount' => $totalAmount,
                'final_amount' => $finalAmount,
                'payment_method' => $paymentMethod,
                'notes' => $validated['notes'],
            ]);

            // Create new sales return details and update stock
            foreach ($items as $item) {
                SalesReturnDetail::create([
                    'sales_return_id' => $salesReturn->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price_at_return' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                ]);

                // Update stock (add back because items are returned)
                $stock = Stock::firstOrCreate(
                    [
                        'product_id' => $item['product_id'],
                        'store_id' => $salesReturn->store_id,
                    ],
                    ['quantity' => 0]
                );
                
                $stock->addStock(
                    $item['quantity'],
                    'Sales Return Update',
                    'sales_return',
                    $salesReturn->id,
                    Auth::id()
                );
            }
        });

        return redirect()->route('sales-returns.show', $salesReturn)
            ->with('success', 'Sales return updated successfully.');
    }

    /**
     * Remove the specified sales return from storage.
     */
    public function destroy(SalesReturn $salesReturn)
    {
        DB::transaction(function () use ($salesReturn) {
            // Revert stock changes (remove the returned items)
            foreach ($salesReturn->salesReturnDetails as $detail) {
                $stock = Stock::where('product_id', $detail->product_id)
                    ->where('store_id', $salesReturn->store_id)
                    ->first();
                    
                if ($stock) {
                    $stock->reduceStock(
                        $detail->quantity,
                        'Sales Return Deleted',
                        'sales_return_delete',
                        $salesReturn->id,
                        Auth::id()
                    );
                }
            }

            $salesReturn->delete();
        });

        return redirect()->route('sales-returns.index')
            ->with('success', 'Sales return deleted successfully.');
    }

    /**
     * Get returnable items for a sale
     */
    public function getReturnableItems(Sale $sale)
    {
        $returnableItems = $this->calculateSaleReturnables($sale);

        return response()->json(array_values($returnableItems));
    }

    /**
     * Hitung sisa kuantitas yang dapat diretur per produk.
     */
    protected function calculateSaleReturnables(Sale $sale, ?int $excludeReturnId = null): array
    {
        $sale->loadMissing(['saleDetails.product']);

        $returnedQuantities = [];
        $salesReturns = SalesReturn::where('sale_id', $sale->id)
            ->with('salesReturnDetails')
            ->get();
        
        foreach ($salesReturns as $return) {
            if ($excludeReturnId && $return->id === $excludeReturnId) {
                continue;
            }

            foreach ($return->salesReturnDetails as $detail) {
                $key = $detail->product_id;
                $returnedQuantities[$key] = ($returnedQuantities[$key] ?? 0) + $detail->quantity;
            }
        }

        $returnableItems = [];
        foreach ($sale->saleDetails as $detail) {
            $returned = $returnedQuantities[$detail->product_id] ?? 0;
            $returnable = max(0, $detail->quantity - $returned);

            if ($returnable > 0) {
                $returnableItems[$detail->product_id] = [
                    'product_id' => $detail->product_id,
                    'product' => $detail->product,
                    'original_quantity' => $detail->quantity,
                    'returned_quantity' => $returned,
                    'returnable_quantity' => $returnable,
                    'price_at_sale' => $detail->price_at_sale,
                ];
            }
        }

        return $returnableItems;
    }
}
