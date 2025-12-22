<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Product;
use App\Models\Store;
use App\Models\Stock;
use App\Models\Customer;
use App\Models\Promotion;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\StationResolver;

class SaleController extends Controller
{
    /**
     * Display a listing of the sales.
     */
    public function index(Request $request)
    {
        $query = Sale::with(['user', 'store', 'member', 'saleDetails.product'])
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

        $sales = $query->orderBy('transaction_date', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Sales/Index', [
            'sales' => $sales,
            'filters' => $request->only(['start_date', 'end_date', 'search']),
        ]);
    }

    /**
     * Show the form for creating a new sale.
     */
    public function create()
    {
        $storeId = $this->currentStoreId();

        $products = Product::with(['supplier', 'category', 'stocks' => function ($query) use ($storeId) {
            $query->where('store_id', $storeId);
        }, 'promotions' => function ($query) {
            $query->active()->with(['tiers', 'bundles.getProduct', 'bundles.buyProduct']);
        }])
            ->whereHas('stocks', function ($query) use ($storeId) {
                $query->where('store_id', $storeId)
                    ->where('quantity', '>', 0);
            })
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                // Add promotion info to each product
                $activePromotion = $product->promotions->first();
                $product->active_promotion = $activePromotion;
                return $product;
            });

        $promotions = Promotion::where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->orderBy('name')
            ->get();

        $customers = Customer::orderBy('name')->get();

        // Get active shift for current user and store
        $station = StationResolver::resolve();
        $activeShift = Shift::query()
            ->where('store_id', $storeId)
            ->where('station_id', $station->id)
            ->where('status', 'open')
            ->latest('start_time')
            ->first();

        return Inertia::render('Sales/Create', [
            'products' => $products,
            'promotions' => $promotions,
            'customers' => $customers,
            'activeShift' => $activeShift,
            'auth' => [
                'user' => auth()->user(),
            ],
            'shift_id' => $shift->id,
            'station_id' => $station->id,

        ]);
    }

    /**
     * Store a newly created sale in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'nullable|numeric|min:0', // Harga akhir tetap dihitung di server
            'payment_method' => ['required', Rule::in(['cash', 'debit', 'credit', 'transfer'])],
            'amount_paid' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'customer_id' => 'nullable|exists:customers,id',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
        ]);

        $storeId = $this->currentStoreId();
        $itemsInput = collect($validated['items']);

        // Check stock availability
        foreach ($itemsInput as $item) {
            $stock = Stock::where('product_id', $item['product_id'])
                ->where('store_id', $storeId)
                ->first();

            if (! $stock || $stock->quantity < $item['quantity']) {
                $product = Product::find($item['product_id']);
                return back()->withErrors([
                    'items' => "Insufficient stock for product: {$product->name}. Available: " . ($stock->quantity ?? 0)
                ]);
            }
        }

        $customer = $validated['customer_id'] ? Customer::find($validated['customer_id']) : null;
        $station = StationResolver::resolve();
        $shift = Shift::query()
            ->where('store_id', $storeId)
            ->where('station_id', $station->id)
            ->where('status', 'open')
            ->latest('start_time')
            ->firstOrFail();

        DB::transaction(function () use ($validated, $itemsInput, $customer, $storeId, $station) {
            // Get customer for promotion calculation
            // Calculate totals with promotions
            $totalAmount = 0;
            $totalDiscount = 0;
            $bundledItems = [];
            $items = collect();

            foreach ($itemsInput as $item) {
                $product = Product::find($item['product_id']);
                $promotionData = $product->getPriceWithPromotion($customer, $item['quantity']);

                $unitPrice = $promotionData['final_price'];
                $discountPerItem = max(0, $promotionData['original_price'] - $promotionData['final_price']);
                $subtotal = $item['quantity'] * $unitPrice;

                $items->push([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'discount_per_item' => $discountPerItem,
                    'promotion_name' => $promotionData['promotion_name'],
                    'subtotal' => $subtotal,
                ]);

                $totalAmount += $subtotal;
                $totalDiscount += $discountPerItem * $item['quantity'];

                // Add bundled products if any
                if (! empty($promotionData['bundled_products'])) {
                    foreach ($promotionData['bundled_products'] as $bundled) {
                        $bundledSubtotal = $bundled['quantity'] * ($bundled['price'] ?? 0);
                        $items->push([
                            'product_id' => $bundled['product_id'],
                            'quantity' => $bundled['quantity'],
                            'unit_price' => $bundled['price'] ?? 0,
                            'discount_per_item' => 0,
                            'promotion_name' => $promotionData['promotion_name'],
                            'subtotal' => $bundledSubtotal,
                            'is_bundled' => true,
                            'bundled_from' => $product->id,
                        ]);
                        $totalAmount += $bundledSubtotal;
                    }
                }
            }

            $discount = $validated['discount'] ?? 0;
            $tax = $validated['tax'] ?? 0;
            $finalAmount = $totalAmount - $discount + $tax;
            $changeDue = max(0, $validated['amount_paid'] - $finalAmount);

            // Create sale
            $sale = Sale::create([
                'user_id' => Auth::id(),
                'store_id' => $storeId,
                'station_id' => $station->id,
                'customer_id' => $validated['customer_id'],
                'total_amount' => $totalAmount,
                'discount' => $discount + $totalDiscount, // Add promotion discounts
                'tax' => $tax,
                'final_amount' => $finalAmount,
                'payment_method' => $validated['payment_method'],
                'amount_paid' => $validated['amount_paid'],
                'change_due' => $changeDue,
                'transaction_date' => now(),
                'notes' => $validated['notes'],
            ]);

            // Create sale details and update stock
            foreach ($items as $item) {
                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price_at_sale' => $item['unit_price'],
                    'discount_per_item' => $item['discount_per_item'] ?? 0,
                    'subtotal' => $item['subtotal'],
                ]);

                // Update stock
                $stock = Stock::where('product_id', $item['product_id'])
                    ->where('store_id', $sale->store_id)
                    ->first();

                if ($stock) {
                    $stock->reduceStock(
                        $item['quantity'],
                        'Sale',
                        'sale',
                        $sale->id,
                        Auth::id()
                    );
                }
            }
        });

        return redirect()->route('sales.create')
            ->with('success', 'Sale created successfully.');
    }

    /**
     * Display the specified sale.
     */
    public function show(Sale $sale)
    {
        $sale->load(['user', 'store', 'member', 'saleDetails.product', 'salesReturns']);

        return Inertia::render('Sales/Show', [
            'sale' => $sale,
        ]);
    }

    /**
     * Show the form for editing the specified sale.
     */
    public function edit(Sale $sale)
    {
        $sale->load(['saleDetails.product']);

        $products = Product::with(['supplier', 'category'])
            ->orderBy('name')
            ->get();

        $customers = Customer::orderBy('name')->get();

        return Inertia::render('Sales/Edit', [
            'sale' => $sale,
            'products' => $products,
            'customers' => $customers,
        ]);
    }

    /**
     * Update the specified sale in storage.
     */
    public function update(Request $request, Sale $sale)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'payment_method' => ['required', Rule::in(['cash', 'debit', 'credit', 'transfer'])],
            'amount_paid' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        DB::transaction(function () use ($request, $sale) {
            // Revert previous stock changes
            foreach ($sale->saleDetails as $detail) {
                $stock = Stock::firstOrCreate(
                    [
                        'product_id' => $detail->product_id,
                        'store_id' => $sale->store_id,
                    ],
                    ['quantity' => 0]
                );

                $stock->addStock(
                    $detail->quantity,
                    'Sale Update - Revert',
                    'sale_revert',
                    $sale->id,
                    Auth::id()
                );
            }

            // Check stock availability for new quantities
            foreach ($request->items as $item) {
                $stock = Stock::where('product_id', $item['product_id'])
                    ->where('store_id', $sale->store_id)
                    ->first();

                if (!$stock || $stock->quantity < $item['quantity']) {
                    $product = Product::find($item['product_id']);
                    throw new \Exception("Insufficient stock for product: {$product->name}. Available: " . ($stock->quantity ?? 0));
                }
            }

            // Delete old sale details
            $sale->saleDetails()->delete();

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

            // Update sale
            $sale->update([
                'customer_id' => $request->customer_id,
                'total_amount' => $totalAmount,
                'discount' => $discount,
                'tax' => $tax,
                'final_amount' => $finalAmount,
                'payment_method' => $request->payment_method,
                'amount_paid' => $request->amount_paid,
                'change_due' => $changeDue,
                'notes' => $request->notes,
            ]);

            // Create new sale details and update stock
            foreach ($items as $item) {
                $subtotal = $item['quantity'] * $item['price'];

                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price_at_sale' => $item['price'],
                    'discount_per_item' => $item['discount_per_item'] ?? 0,
                    'subtotal' => $subtotal,
                ]);

                // Update stock
                $stock = Stock::where('product_id', $item['product_id'])
                    ->where('store_id', $sale->store_id)
                    ->first();

                if ($stock) {
                    $stock->reduceStock(
                        $item['quantity'],
                        'Sale Update',
                        'sale',
                        $sale->id,
                        Auth::id()
                    );
                }
            }
        });

        return redirect()->route('sales.show', $sale)
            ->with('success', 'Sale updated successfully.');
    }

    /**
     * Remove the specified sale from storage.
     */
    public function destroy(Sale $sale)
    {
        DB::transaction(function () use ($sale) {
            // Revert stock changes
            foreach ($sale->saleDetails as $detail) {
                $stock = Stock::firstOrCreate(
                    [
                        'product_id' => $detail->product_id,
                        'store_id' => $sale->store_id,
                    ],
                    ['quantity' => 0]
                );

                $stock->addStock(
                    $detail->quantity,
                    'Sale Deleted',
                    'sale_delete',
                    $sale->id,
                    Auth::id()
                );
            }

            $sale->delete();
        });

        return redirect()->route('sales.index')
            ->with('success', 'Sale deleted successfully.');
    }

    /**
     * Generate PDF receipt for sale
     */
    public function generatePDF(Sale $sale)
    {
        $sale->load(['user', 'store', 'member', 'saleDetails.product']);

        $pdf = Pdf::loadView('sales.pdf', compact('sale'));
        return $pdf->download('sale-' . $sale->invoice_number . '.pdf');
    }

    /**
     * Print sale receipt
     */
    public function print(Sale $sale)
    {
        $sale->load(['user', 'store', 'member', 'saleDetails.product']);

        return Inertia::render('Sales/Print', [
            'sale' => $sale,
        ]);
    }

    /**
     * Send receipt via WhatsApp
     */
    public function sendWhatsApp(Sale $sale, Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'nullable|string'
        ]);

        $sale->load(['user', 'store', 'member', 'saleDetails.product']);

        // Here you would integrate with WhatsApp API
        // For now, return JSON response
        return response()->json([
            'message' => 'WhatsApp integration not implemented yet',
            'phone' => $request->phone,
            'sale' => $sale
        ]);
    }

    /**
     * Get product stock information
     */
    public function getProductStock(Product $product)
    {
        $stock = Stock::where('product_id', $product->id)
            ->where('store_id', $this->currentStoreId())
            ->first();

        return response()->json([
            'product' => $product,
            'stock' => $stock ? $stock->quantity : 0,
            'is_low_stock' => $stock ? $stock->isLowStock() : true,
        ]);
    }
}
