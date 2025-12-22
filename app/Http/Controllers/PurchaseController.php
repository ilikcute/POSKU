<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Product;
use App\Models\Store;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\StationResolver;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the purchases.
     */
    public function index(Request $request)
    {
        $query = Purchase::with(['user', 'store', 'supplier', 'purchaseDetails.product'])
            ->byStore($this->currentStoreId());

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
        $storeId = $this->currentStoreId();
        $station = StationResolver::resolve();
        $activeShift = Shift::query()
            ->where('store_id', $storeId)
            ->where('station_id', $station->id)
            ->where('status', 'open')
            ->latest('start_time')
            ->first();

        return Inertia::render('Purchases/Create', [
            'products' => $products,
            'suppliers' => $suppliers,
            'shift_id' => $activeShift?->id,
            'station_id' => $station->id,
            'invoice_number' => Purchase::generateInvoiceNumber(),
            'current_user' => Auth::user()?->only(['id', 'name']),
            'current_date' => now()->toDateString(),
        ]);
    }

    /**
     * Store a newly created purchase in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.tax_per_item' => 'nullable|numeric|min:0',
            'items.*.discount_per_item' => 'nullable|numeric|min:0',
            'supplier_id' => 'required|exists:suppliers,id',
            'payment_method' => ['required', Rule::in(['cash', 'card', 'transfer', 'credit'])],
            'payment_term_days' => 'required_if:payment_method,credit|nullable|integer|min:1',
            'amount_paid' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $storeId = $this->currentStoreId();
        $station = StationResolver::resolve();
        $items = collect($validated['items']);

        $station = StationResolver::resolve();

        $shift = Shift::query()
            ->where('store_id', $storeId)
            ->where('station_id', $station->id)
            ->where('status', 'open')
            ->latest('start_time')
            ->firstOrFail();


        DB::transaction(function () use ($validated, $items, $storeId, $station) {
            // Calculate totals
            $totalAmount = 0;
            $totalDiscount = 0;
            $totalTax = 0;

            foreach ($items as $item) {
                $subtotal = $item['quantity'] * $item['price'];
                $totalAmount += $subtotal;
                $totalDiscount += ($item['discount_per_item'] ?? 0) * $item['quantity'];
                $totalTax += ($item['tax_per_item'] ?? 0) * $item['quantity'];
            }

            $finalAmount = $totalAmount - $totalDiscount + $totalTax;
            $changeDue = max(0, $validated['amount_paid'] - $finalAmount);
            $dueDate = null;
            $paymentTermDays = null;

            if ($validated['payment_method'] === 'credit') {
                $paymentTermDays = (int) ($validated['payment_term_days'] ?? 0);
                $dueDate = $paymentTermDays > 0 ? now()->addDays($paymentTermDays)->toDateString() : null;
            }

            // Create purchase
            $purchase = Purchase::create([
                'user_id' => Auth::id(),
                'store_id' => $storeId,
                'station_id' => $station->id,
                'supplier_id' => $validated['supplier_id'],
                'total_amount' => $totalAmount,
                'discount' => $totalDiscount,
                'tax' => $totalTax,
                'final_amount' => $finalAmount,
                'payment_method' => $validated['payment_method'],
                'payment_term_days' => $paymentTermDays,
                'due_date' => $dueDate,
                'amount_paid' => $validated['amount_paid'],
                'change_due' => $changeDue,
                'transaction_date' => now(),
                'notes' => $validated['notes'],
            ]);

            // Create purchase details and update stock
            foreach ($items as $item) {
                $subtotal = $item['quantity'] * $item['price'];

                PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price_at_sale' => $item['price'],
                    'tax_per_item' => $item['tax_per_item'] ?? 0,
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
        $purchase->load(['user', 'store', 'supplier', 'purchaseDetails.product', 'purchaseReturns']);

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

        return Inertia::render('Purchases/Edit', [
            'purchase' => $purchase,
            'products' => $products,
            'suppliers' => $suppliers,
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
            'items.*.tax_per_item' => 'nullable|numeric|min:0',
            'items.*.discount_per_item' => 'nullable|numeric|min:0',
            'supplier_id' => 'required|exists:suppliers,id',
            'payment_method' => ['required', Rule::in(['cash', 'card', 'transfer', 'credit'])],
            'payment_term_days' => 'required_if:payment_method,credit|nullable|integer|min:1',
            'amount_paid' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
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
            $totalDiscount = 0;
            $totalTax = 0;
            $items = collect($request->items);

            foreach ($items as $item) {
                $subtotal = $item['quantity'] * $item['price'];
                $totalAmount += $subtotal;
                $totalDiscount += ($item['discount_per_item'] ?? 0) * $item['quantity'];
                $totalTax += ($item['tax_per_item'] ?? 0) * $item['quantity'];
            }

            $finalAmount = $totalAmount - $totalDiscount + $totalTax;
            $changeDue = max(0, $request->amount_paid - $finalAmount);
            $dueDate = null;
            $paymentTermDays = null;

            if ($request->payment_method === 'credit') {
                $paymentTermDays = (int) ($request->payment_term_days ?? 0);
                $dueDate = $paymentTermDays > 0 ? now()->addDays($paymentTermDays)->toDateString() : null;
            }

            // Update purchase
            $purchase->update([
                'supplier_id' => $request->supplier_id,
                'total_amount' => $totalAmount,
                'discount' => $totalDiscount,
                'tax' => $totalTax,
                'final_amount' => $finalAmount,
                'payment_method' => $request->payment_method,
                'payment_term_days' => $paymentTermDays,
                'due_date' => $dueDate,
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
                    'tax_per_item' => $item['tax_per_item'] ?? 0,
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
        $purchase->load(['user', 'store', 'supplier', 'purchaseDetails.product']);

        $pdf = Pdf::loadView('purchases.pdf', compact('purchase'));
        return $pdf->download('purchase-' . $purchase->invoice_number . '.pdf');
    }

    /**
     * Print purchase receipt
     */
    public function print(Purchase $purchase)
    {
        $purchase->load(['user', 'store', 'supplier', 'purchaseDetails.product']);

        return Inertia::render('Purchases/Print', [
            'purchase' => $purchase,
        ]);
    }
}
