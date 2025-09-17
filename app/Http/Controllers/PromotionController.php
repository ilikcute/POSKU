<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PromotionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $promotions = Promotion::query()
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', '%' . $search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends($request->query());

        // Cek apakah ini request API (bukan Inertia)
        if ($request->expectsJson() && !$request->header('X-Inertia')) {
            return response()->json($promotions);
        }

        // Untuk Inertia request (web interface)
        return Inertia::render('Master/Promotions/Index', [
            'promotions' => $promotions,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'promotion_type' => 'nullable|in:product_discount,buy_get,cashback,shipping_discount',
            'discount_type' => 'required|in:percentage,fixed_amount',
            'discount_value' => 'required|numeric|min:0',
            'min_purchase_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'min_quantity' => 'nullable|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_stackable' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'usage_limit' => 'nullable|integer|min:1',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
            'customer_type_ids' => 'nullable|array',
            'customer_type_ids.*' => 'exists:customer_types,id',
        ]);

        // Set default values
        $validated['promotion_type'] = $validated['promotion_type'] ?? 'product_discount';
        $validated['min_quantity'] = $validated['min_quantity'] ?? 1;
        $validated['is_stackable'] = $validated['is_stackable'] ?? false;
        $validated['is_active'] = $validated['is_active'] ?? true;

        $promotion = Promotion::create($validated);

        // Sync relationships jika ada
        if (isset($validated['product_ids']) && method_exists($promotion, 'products')) {
            $promotion->products()->sync($validated['product_ids']);
        }

        if (isset($validated['customer_type_ids']) && method_exists($promotion, 'customerTypes')) {
            $promotion->customerTypes()->sync($validated['customer_type_ids']);
        }

        // Cek apakah ini request API (bukan Inertia)
        if ($request->expectsJson() && !$request->header('X-Inertia')) {
            return response()->json($promotion->load(['products', 'customerTypes']), 201);
        }

        return redirect()->route('promotions.index')
            ->with('success', 'Promotion created successfully.');
    }

    public function show(Promotion $promotion)
    {
        $promotionData = method_exists($promotion, 'products') && method_exists($promotion, 'customerTypes')
            ? $promotion->load(['products', 'customerTypes'])
            : $promotion;

        // Cek apakah ini request API (bukan Inertia)
        if (request()->expectsJson() && !request()->header('X-Inertia')) {
            return response()->json($promotionData);
        }

        // Untuk web interface, redirect ke index atau render detail page
        return redirect()->route('promotions.index');
    }

    public function update(Request $request, Promotion $promotion)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'promotion_type' => 'nullable|in:product_discount,buy_get,cashback,shipping_discount',
            'discount_type' => 'nullable|in:percentage,fixed_amount',
            'discount_value' => 'nullable|numeric|min:0',
            'min_purchase_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'min_quantity' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_stackable' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'usage_limit' => 'nullable|integer|min:1',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
            'customer_type_ids' => 'nullable|array',
            'customer_type_ids.*' => 'exists:customer_types,id',
        ]);

        $promotion->update($validated);

        // Sync relationships jika ada
        if (isset($validated['product_ids']) && method_exists($promotion, 'products')) {
            $promotion->products()->sync($validated['product_ids']);
        }

        if (isset($validated['customer_type_ids']) && method_exists($promotion, 'customerTypes')) {
            $promotion->customerTypes()->sync($validated['customer_type_ids']);
        }

        // Cek apakah ini request API (bukan Inertia)
        if ($request->expectsJson() && !$request->header('X-Inertia')) {
            return response()->json($promotion->load(['products', 'customerTypes']));
        }

        return redirect()->route('promotions.index')
            ->with('success', 'Promotion updated successfully.');
    }

    public function destroy(Promotion $promotion)
    {
        $promotion->delete();

        // Cek apakah ini request API (bukan Inertia)
        if (request()->expectsJson() && !request()->header('X-Inertia')) {
            return response()->json(['message' => 'Promotion deleted successfully']);
        }

        return redirect()->route('promotions.index')
            ->with('success', 'Promotion deleted successfully.');
    }

    public function activePromotions()
    {
        // Method khusus untuk API - selalu return JSON
        $promotions = method_exists(Promotion::class, 'active')
            ? Promotion::active()->with(['products', 'customerTypes'])->get()
            : Promotion::where('is_active', true)->get();

        return response()->json($promotions);
    }
}
