<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Product;
use App\Models\Store;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the purchases.
     */
    public function index(Request $request)
    {
        $query = Purchase::with(['user', 'store', 'member', 'purchaseDetails.product'])
            ->byStore(auth()->user()->store_id ?? 1);

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        // Search by invoice number
        if ($request->has('search') && $request->search) {
            $query->where('invoice_number', 'like', '%' . $request->search . '%');
        }

        $purchases = $query->orderBy('transaction_date', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Purchases/Index', [
            'purchases' => $purchases,
            'filters' => $request->only(['start_date', 'end_date', 'search']),
        ]);
    }

    /**
     * Show the form for creating a new purchase.
     */
    public function create()
    {
        $products = Product::with(['supplier', 'category'])
            ->orderBy('name')
            ->get();
            
        $suppliers = Supplier::orderBy('name')->get();
        $customers = Customer::orderBy('name')->get();

        return Inertia::render('Purchases/Create', [
            'products' => $products,
            'suppliers' => $suppliers,
            'customers' => $customers,
        ]);
    }

    /**
     * Store a newly created purchase in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'payment_method' => ['required', Rule::in(['cash', 'card', 'transfer'])],
            'amount_paid' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'member_id' => 'nullable|exists:customers,id',
        ]);

        DB::transaction(function () use ($request) {
            // Calculate totals
            $totalAmount = 0;
            $items = collect($request->items);
            
            foreach ($items as $item) {
                $subtotal = $item['quantity'] * $item['price'];
                $totalAmount += $subtotal;
            }

            $discount = $request->discount ?? 0;
            $tax = $request->tax ?? 0;
            $finalAmount = $totalAmount - $discount + $tax;
            $changeDue = max(0, $request->amount_paid - $finalAmount);

            // Create purchase
            $purchase = Purchase::create([
                'user_id' => Auth::id(),
                'store_id' => auth()->user()->store_id ?? 1,
                'member_id' => $request->member_id,
                'total_amount' => $totalAmount,
                'discount' => $discount,
                'tax' => $tax,
                'final_amount' => $finalAmount,
                'payment_method' => $request->payment_method,
                'amount_paid' => $request->amount_paid,
                'change_due' => $changeDue,
                'transaction_date' => now(),
                'notes' => $request->notes,
            ]);

            // Create purchase details and update stock
            foreach ($items as $item) {
                $subtotal = $item['quantity'] * $item['price'];
                
                PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price_at_sale' => $item['price'],
                    'discount_per_item' => $item['discount_per_item'] ?? 0,
                    'subtotal' => $subtotal,
                ]);

                // Update stock
                $stock = Stock::firstOrCreate(
                    [
                        'product_id' => $item['product_id'],
                        'store_id' => $purchase->store_id,
                    ],
                    ['quantity' => 0]
                );
                
                $stock->addStock(
                    $item['quantity'],
                    'Purchase',
                    'purchase',
                    $purchase->id,
                    Auth::id()
                );
            }
        });

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase created successfully.');
    }

    /**
     * Display the specified purchase.
     */
    public function show(Purchase $purchase)
    {
        $purchase->load(['user', 'store', 'member', 'purchaseDetails.product', 'purchaseReturns']);

        return Inertia::render('Purchases/Show', [
            'purchase' => $purchase,
        ]);
    }

    /**
     * Show the form for editing the specified purchase.
     */
    public function edit(Purchase $purchase)
    {
        $purchase->load(['purchaseDetails.product']);
        
        $products = Product::with(['supplier', 'category'])
            ->orderBy('name')
            ->get();
            
        $suppliers = Supplier::orderBy('name')->get();
        $customers = Customer::orderBy('name')->get();

        return Inertia::render('Purchases/Edit', [
            'purchase' => $purchase,
            'products' => $products,
            'suppliers' => $suppliers,
            'customers' => $customers,
        ]);
    }

    /**
     * Update the specified purchase in storage.
     */
    public function update(Request $request, Purchase $purchase)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'payment_method' => ['required', Rule::in(['cash', 'card', 'transfer'])],
            'amount_paid' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'member_id' => 'nullable|exists:customers,id',
        ]);

        DB::transaction(function () use ($request, $purchase) {
            // Revert previous stock changes
            foreach ($purchase->purchaseDetails as $detail) {
                $stock = Stock::where('product_id', $detail->product_id)
                    ->where('store_id', $purchase->store_id)
                    ->first();
                    
                if ($stock) {
                    $stock->reduceStock(
                        $detail->quantity,
                        'Purchase Update - Revert',
                        'purchase_revert',
                        $purchase->id,
                        Auth::id()
                    );
                }
            }

            // Delete old purchase details
            $purchase->purchaseDetails()->delete();

            // Calculate new totals
            $totalAmount = 0;
            $items = collect($request->items);
            
            foreach ($items as $item) {
                $subtotal = $item['quantity'] * $item['price'];
                $totalAmount += $subtotal;
            }

            $discount = $request->discount ?? 0;
            $tax = $request->tax ?? 0;
            $finalAmount = $totalAmount - $discount + $tax;
            $changeDue = max(0, $request->amount_paid - $finalAmount);

            // Update purchase
            $purchase->update([
                'member_id' => $request->member_id,
                'total_amount' => $totalAmount,
                'discount' => $discount,
                'tax' => $tax,
                'final_amount' => $finalAmount,
                'payment_method' => $request->payment_method,
                'amount_paid' => $request->amount_paid,
                'change_due' => $changeDue,
                'notes' => $request->notes,
            ]);

            // Create new purchase details and update stock
            foreach ($items as $item) {
                $subtotal = $item['quantity'] * $item['price'];
                
                PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price_at_sale' => $item['price'],
                    'discount_per_item' => $item['discount_per_item'] ?? 0,
                    'subtotal' => $subtotal,
                ]);

                // Update stock
                $stock = Stock::firstOrCreate(
                    [
                        'product_id' => $item['product_id'],
                        'store_id' => $purchase->store_id,
                    ],
                    ['quantity' => 0]
                );
                
                $stock->addStock(
                    $item['quantity'],
                    'Purchase Update',
                    'purchase',
                    $purchase->id,
                    Auth::id()
                );
            }
        });

        return redirect()->route('purchases.show', $purchase)
            ->with('success', 'Purchase updated successfully.');
    }

    /**
     * Remove the specified purchase from storage.
     */
    public function destroy(Purchase $purchase)
    {
        DB::transaction(function () use ($purchase) {
            // Revert stock changes
            foreach ($purchase->purchaseDetails as $detail) {
                $stock = Stock::where('product_id', $detail->product_id)
                    ->where('store_id', $purchase->store_id)
                    ->first();
                    
                if ($stock) {
                    $stock->reduceStock(
                        $detail->quantity,
                        'Purchase Deleted',
                        'purchase_delete',
                        $purchase->id,
                        Auth::id()
                    );
                }
            }

            $purchase->delete();
        });

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase deleted successfully.');
    }

    /**
     * Generate PDF receipt for purchase
     */
    public function generatePDF(Purchase $purchase)
    {
        $purchase->load(['user', 'store', 'member', 'purchaseDetails.product']);
        
        // Here you would use a PDF generation library like DomPDF or TCPDF
        // For now, return JSON response
        return response()->json([
            'message' => 'PDF generation not implemented yet',
            'purchase' => $purchase
        ]);
    }

    /**
     * Print purchase receipt
     */
    public function print(Purchase $purchase)
    {
        $purchase->load(['user', 'store', 'member', 'purchaseDetails.product']);
        
        return Inertia::render('Purchases/Print', [
            'purchase' => $purchase,
        ]);
    }
}