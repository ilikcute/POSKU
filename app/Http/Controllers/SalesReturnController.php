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

class SalesReturnController extends Controller
{
    /**
     * Display a listing of sales returns.
     */
    public function index(Request $request)
    {
        $query = SalesReturn::with(['sale', 'user', 'store', 'salesReturnDetails.product'])
            ->where('store_id', auth()->user()->store_id ?? 1);

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
        $saleId = $request->get('sale_id');
        $sale = null;
        
        if ($saleId) {
            $sale = Sale::with(['saleDetails.product'])
                ->where('id', $saleId)
                ->where('store_id', auth()->user()->store_id ?? 1)
                ->first();
        }

        $sales = Sale::with(['saleDetails'])
            ->where('store_id', auth()->user()->store_id ?? 1)
            ->orderBy('transaction_date', 'desc')
            ->limit(50)
            ->get();

        return Inertia::render('SalesReturns/Create', [
            'sale' => $sale,
            'sales' => $sales,
        ]);
    }

    /**
     * Store a newly created sales return in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $sale = Sale::findOrFail($request->sale_id);
        
        // Validate that sale belongs to current store
        if ($sale->store_id !== (auth()->user()->store_id ?? 1)) {
            return back()->withErrors(['sale_id' => 'Invalid sale selected.']);
        }

        DB::transaction(function () use ($request, $sale) {
            // Calculate totals
            $totalAmount = 0;
            $items = collect($request->items);
            
            foreach ($items as $item) {
                $subtotal = $item['quantity'] * $item['price'];
                $totalAmount += $subtotal;
            }

            $finalAmount = $totalAmount;

            // Create sales return
            $salesReturn = SalesReturn::create([
                'sale_id' => $sale->id,
                'user_id' => Auth::id(),
                'store_id' => $sale->store_id,
                'total_amount' => $totalAmount,
                'final_amount' => $finalAmount,
                'notes' => $request->notes,
                'return_date' => now(),
            ]);

            // Create sales return details and update stock
            foreach ($items as $item) {
                $subtotal = $item['quantity'] * $item['price'];
                
                SalesReturnDetail::create([
                    'sales_return_id' => $salesReturn->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price_at_return' => $item['price'],
                    'subtotal' => $subtotal,
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
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($request, $salesReturn) {
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
            $totalAmount = 0;
            $items = collect($request->items);
            
            foreach ($items as $item) {
                $subtotal = $item['quantity'] * $item['price'];
                $totalAmount += $subtotal;
            }

            $finalAmount = $totalAmount;

            // Update sales return
            $salesReturn->update([
                'total_amount' => $totalAmount,
                'final_amount' => $finalAmount,
                'notes' => $request->notes,
            ]);

            // Create new sales return details and update stock
            foreach ($items as $item) {
                $subtotal = $item['quantity'] * $item['price'];
                
                SalesReturnDetail::create([
                    'sales_return_id' => $salesReturn->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price_at_return' => $item['price'],
                    'subtotal' => $subtotal,
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
        $sale->load(['saleDetails.product']);
        
        // Get already returned quantities
        $returnedQuantities = [];
        $salesReturns = SalesReturn::where('sale_id', $sale->id)->get();
        
        foreach ($salesReturns as $return) {
            foreach ($return->salesReturnDetails as $detail) {
                $key = $detail->product_id;
                $returnedQuantities[$key] = ($returnedQuantities[$key] ?? 0) + $detail->quantity;
            }
        }

        // Calculate returnable quantities
        $returnableItems = [];
        foreach ($sale->saleDetails as $detail) {
            $returned = $returnedQuantities[$detail->product_id] ?? 0;
            $returnable = $detail->quantity - $returned;
            
            if ($returnable > 0) {
                $returnableItems[] = [
                    'product_id' => $detail->product_id,
                    'product' => $detail->product,
                    'original_quantity' => $detail->quantity,
                    'returned_quantity' => $returned,
                    'returnable_quantity' => $returnable,
                    'price_at_sale' => $detail->price_at_sale,
                ];
            }
        }

        return response()->json($returnableItems);
    }
}