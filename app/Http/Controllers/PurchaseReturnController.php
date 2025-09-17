<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnDetail;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PurchaseReturnController extends Controller
{
    /**
     * Display a listing of purchase returns.
     */
    public function index(Request $request)
    {
        $query = PurchaseReturn::with(['purchase', 'user', 'store', 'purchaseReturnDetails.product'])
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

        return Inertia::render('PurchaseReturns/Index', [
            'returns' => $returns,
            'filters' => $request->only(['start_date', 'end_date']),
        ]);
    }

    /**
     * Show the form for creating a new purchase return.
     */
    public function create(Request $request)
    {
        $purchaseId = $request->get('purchase_id');
        $purchase = null;
        
        if ($purchaseId) {
            $purchase = Purchase::with(['purchaseDetails.product'])
                ->where('id', $purchaseId)
                ->where('store_id', auth()->user()->store_id ?? 1)
                ->first();
        }

        $purchases = Purchase::with(['purchaseDetails'])
            ->where('store_id', auth()->user()->store_id ?? 1)
            ->orderBy('transaction_date', 'desc')
            ->limit(50)
            ->get();

        return Inertia::render('PurchaseReturns/Create', [
            'purchase' => $purchase,
            'purchases' => $purchases,
        ]);
    }

    /**
     * Store a newly created purchase return in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $purchase = Purchase::findOrFail($request->purchase_id);
        
        // Validate that purchase belongs to current store
        if ($purchase->store_id !== (auth()->user()->store_id ?? 1)) {
            return back()->withErrors(['purchase_id' => 'Invalid purchase selected.']);
        }

        DB::transaction(function () use ($request, $purchase) {
            // Calculate totals
            $totalAmount = 0;
            $items = collect($request->items);
            
            foreach ($items as $item) {
                $subtotal = $item['quantity'] * $item['price'];
                $totalAmount += $subtotal;
            }

            $finalAmount = $totalAmount;

            // Create purchase return
            $purchaseReturn = PurchaseReturn::create([
                'purchase_id' => $purchase->id,
                'user_id' => Auth::id(),
                'store_id' => $purchase->store_id,
                'total_amount' => $totalAmount,
                'final_amount' => $finalAmount,
                'notes' => $request->notes,
                'return_date' => now(),
            ]);

            // Create purchase return details and update stock
            foreach ($items as $item) {
                $subtotal = $item['quantity'] * $item['price'];
                
                PurchaseReturnDetail::create([
                    'purchase_return_id' => $purchaseReturn->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price_at_return' => $item['price'],
                    'subtotal' => $subtotal,
                ]);

                // Update stock (reduce because we're returning items)
                $stock = Stock::where('product_id', $item['product_id'])
                    ->where('store_id', $purchase->store_id)
                    ->first();
                    
                if ($stock) {
                    $stock->reduceStock(
                        $item['quantity'],
                        'Purchase Return',
                        'purchase_return',
                        $purchaseReturn->id,
                        Auth::id()
                    );
                }
            }
        });

        return redirect()->route('purchase-returns.index')
            ->with('success', 'Purchase return created successfully.');
    }

    /**
     * Display the specified purchase return.
     */
    public function show(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load([
            'purchase',
            'user', 
            'store', 
            'purchaseReturnDetails.product'
        ]);

        return Inertia::render('PurchaseReturns/Show', [
            'purchaseReturn' => $purchaseReturn,
        ]);
    }

    /**
     * Show the form for editing the specified purchase return.
     */
    public function edit(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load([
            'purchase.purchaseDetails.product',
            'purchaseReturnDetails.product'
        ]);

        return Inertia::render('PurchaseReturns/Edit', [
            'purchaseReturn' => $purchaseReturn,
        ]);
    }

    /**
     * Update the specified purchase return in storage.
     */
    public function update(Request $request, PurchaseReturn $purchaseReturn)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($request, $purchaseReturn) {
            // Revert previous stock changes (add back the returned items)
            foreach ($purchaseReturn->purchaseReturnDetails as $detail) {
                $stock = Stock::firstOrCreate(
                    [
                        'product_id' => $detail->product_id,
                        'store_id' => $purchaseReturn->store_id,
                    ],
                    ['quantity' => 0]
                );
                
                $stock->addStock(
                    $detail->quantity,
                    'Purchase Return Update - Revert',
                    'purchase_return_revert',
                    $purchaseReturn->id,
                    Auth::id()
                );
            }

            // Delete old purchase return details
            $purchaseReturn->purchaseReturnDetails()->delete();

            // Calculate new totals
            $totalAmount = 0;
            $items = collect($request->items);
            
            foreach ($items as $item) {
                $subtotal = $item['quantity'] * $item['price'];
                $totalAmount += $subtotal;
            }

            $finalAmount = $totalAmount;

            // Update purchase return
            $purchaseReturn->update([
                'total_amount' => $totalAmount,
                'final_amount' => $finalAmount,
                'notes' => $request->notes,
            ]);

            // Create new purchase return details and update stock
            foreach ($items as $item) {
                $subtotal = $item['quantity'] * $item['price'];
                
                PurchaseReturnDetail::create([
                    'purchase_return_id' => $purchaseReturn->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price_at_return' => $item['price'],
                    'subtotal' => $subtotal,
                ]);

                // Update stock (reduce because we're returning items)
                $stock = Stock::where('product_id', $item['product_id'])
                    ->where('store_id', $purchaseReturn->store_id)
                    ->first();
                    
                if ($stock) {
                    $stock->reduceStock(
                        $item['quantity'],
                        'Purchase Return Update',
                        'purchase_return',
                        $purchaseReturn->id,
                        Auth::id()
                    );
                }
            }
        });

        return redirect()->route('purchase-returns.show', $purchaseReturn)
            ->with('success', 'Purchase return updated successfully.');
    }

    /**
     * Remove the specified purchase return from storage.
     */
    public function destroy(PurchaseReturn $purchaseReturn)
    {
        DB::transaction(function () use ($purchaseReturn) {
            // Revert stock changes (add back the returned items)
            foreach ($purchaseReturn->purchaseReturnDetails as $detail) {
                $stock = Stock::firstOrCreate(
                    [
                        'product_id' => $detail->product_id,
                        'store_id' => $purchaseReturn->store_id,
                    ],
                    ['quantity' => 0]
                );
                
                $stock->addStock(
                    $detail->quantity,
                    'Purchase Return Deleted',
                    'purchase_return_delete',
                    $purchaseReturn->id,
                    Auth::id()
                );
            }

            $purchaseReturn->delete();
        });

        return redirect()->route('purchase-returns.index')
            ->with('success', 'Purchase return deleted successfully.');
    }

    /**
     * Get returnable items for a purchase
     */
    public function getReturnableItems(Purchase $purchase)
    {
        $purchase->load(['purchaseDetails.product']);
        
        // Get already returned quantities
        $returnedQuantities = [];
        $purchaseReturns = PurchaseReturn::where('purchase_id', $purchase->id)->get();
        
        foreach ($purchaseReturns as $return) {
            foreach ($return->purchaseReturnDetails as $detail) {
                $key = $detail->product_id;
                $returnedQuantities[$key] = ($returnedQuantities[$key] ?? 0) + $detail->quantity;
            }
        }

        // Calculate returnable quantities
        $returnableItems = [];
        foreach ($purchase->purchaseDetails as $detail) {
            $returned = $returnedQuantities[$detail->product_id] ?? 0;
            $returnable = $detail->quantity - $returned;
            
            if ($returnable > 0) {
                $returnableItems[] = [
                    'product_id' => $detail->product_id,
                    'product' => $detail->product,
                    'original_quantity' => $detail->quantity,
                    'returned_quantity' => $returned,
                    'returnable_quantity' => $returnable,
                    'price_at_purchase' => $detail->price_at_sale,
                ];
            }
        }

        return response()->json($returnableItems);
    }
}