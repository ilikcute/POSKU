<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;

class PriceController extends Controller
{
    public function getProductPrice(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'customer_id' => 'nullable|exists:customers,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $customer = isset($validated['customer_id'])
            ? Customer::with('customerType')->findOrFail($validated['customer_id'])
            : null;

        $pricing = $product->getPriceWithPromotion($customer, $validated['quantity']);

        return response()->json([
            'product' => $product->only(['id', 'name', 'product_code']),
            'customer' => $customer ? $customer->only(['id', 'name', 'customer_code']) : null,
            'customer_type' => $customer?->customerType?->only(['id', 'name', 'code']),
            'quantity' => $validated['quantity'],
            'pricing' => $pricing,
        ]);
    }

    public function bulkPriceCheck(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        $customer = isset($validated['customer_id'])
            ? Customer::with('customerType')->findOrFail($validated['customer_id'])
            : null;

        $results = collect($validated['items'])->map(function ($item) use ($customer) {
            $product = Product::findOrFail($item['product_id']);
            $pricing = $product->getPriceWithPromotion($customer, $item['quantity']);

            return [
                'product' => $product->only(['id', 'name', 'product_code']),
                'quantity' => $item['quantity'],
                'pricing' => $pricing,
            ];
        });

        $totalOriginal = $results->sum('pricing.original_price');
        $totalFinal = $results->sum('pricing.final_price');
        $totalDiscount = $totalOriginal - $totalFinal;

        return response()->json([
            'customer' => $customer ? $customer->only(['id', 'name', 'customer_code']) : null,
            'customer_type' => $customer?->customerType?->only(['id', 'name', 'code']),
            'items' => $results,
            'summary' => [
                'total_original_price' => $totalOriginal,
                'total_final_price' => $totalFinal,
                'total_discount' => $totalDiscount,
                'items_count' => count($validated['items']),
            ],
        ]);
    }
}
