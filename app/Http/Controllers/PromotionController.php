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
            ->select('name', 'start_date', 'end_date', 'promotion_type', 'id')
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
        return Inertia::render('Promotions/Index', [
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
            'promotion_type' => 'nullable|in:product_discount,buy_get,cashback,shipping_discount,tiered_pricing,bundling',
            'discount_type' => 'required_unless:promotion_type,tiered_pricing,bundling|in:percentage,fixed_amount',
            'discount_value' => 'required_unless:promotion_type,tiered_pricing,bundling|numeric|min:0',
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
            'tiers' => 'required_if:promotion_type,tiered_pricing|array',
            'tiers.*.min_quantity' => 'required_with:tiers|integer|min:1',
            'tiers.*.discount_type' => 'required_with:tiers|in:percentage,fixed_amount',
            'tiers.*.discount_value' => 'required_with:tiers|numeric|min:0',
            'bundles' => 'required_if:promotion_type,bundling|array',
            'bundles.*.buy_product_id' => 'required_with:bundles|exists:products,id',
            'bundles.*.buy_quantity' => 'required_with:bundles|integer|min:1',
            'bundles.*.get_product_id' => 'required_with:bundles|exists:products,id',
            'bundles.*.get_quantity' => 'required_with:bundles|integer|min:1',
            'bundles.*.get_price' => 'nullable|numeric|min:0',
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

        // Sync tiers if any
        if (isset($validated['tiers'])) {
            $promotion->tiers()->delete();
            foreach ($validated['tiers'] as $tier) {
                $promotion->tiers()->create($tier);
            }
        }

        // Sync bundles if any
        if (isset($validated['bundles'])) {
            $promotion->bundles()->delete();
            foreach ($validated['bundles'] as $bundle) {
                $promotion->bundles()->create($bundle);
            }
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
            ? $promotion->load(['products', 'customerTypes', 'tiers', 'bundles'])
            : $promotion;

        // Cek apakah ini request API (bukan Inertia)
        if (request()->expectsJson() && !request()->header('X-Inertia')) {
            return response()->json($promotionData);
        }

        // Untuk web interface, redirect ke index atau render detail page
        return redirect()->route('promotions.index');
    }

    public function create()
    {
        $products = \App\Models\Product::select('id', 'name')->orderBy('name')->get();
        $customerTypes = \App\Models\CustomerType::select('id', 'name')->orderBy('name')->get();

        // Untuk Inertia request (web interface)
        return Inertia::render('Promotions/Create', [
            'products' => $products,
            'customerTypes' => $customerTypes,
        ]);
    }

    public function edit(Promotion $promotion)
    {
        $promotionData = method_exists($promotion, 'products') && method_exists($promotion, 'customerTypes')
            ? $promotion->load(['products', 'customerTypes', 'tiers', 'bundles'])
            : $promotion;

        $products = \App\Models\Product::select('id', 'name')->orderBy('name')->get();
        $customerTypes = \App\Models\CustomerType::select('id', 'name')->orderBy('name')->get();

        // Untuk Inertia request (web interface)
        return Inertia::render('Promotions/Edit', [
            'promotion' => $promotionData,
            'products' => $products,
            'customerTypes' => $customerTypes,
        ]);
    }

    public function update(Request $request, Promotion $promotion)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'promotion_type' => 'nullable|in:product_discount,buy_get,cashback,shipping_discount,tiered_pricing,bundling',
            'discount_type' => 'nullable_unless:promotion_type,tiered_pricing,bundling|in:percentage,fixed_amount',
            'discount_value' => 'nullable_unless:promotion_type,tiered_pricing,bundling|numeric|min:0',
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
            'tiers' => 'nullable|array',
            'tiers.*.min_quantity' => 'required_with:tiers|integer|min:1',
            'tiers.*.discount_type' => 'required_with:tiers|in:percentage,fixed_amount',
            'tiers.*.discount_value' => 'required_with:tiers|numeric|min:0',
            'bundles' => 'nullable|array',
            'bundles.*.buy_product_id' => 'required_with:bundles|exists:products,id',
            'bundles.*.buy_quantity' => 'required_with:bundles|integer|min:1',
            'bundles.*.get_product_id' => 'required_with:bundles|exists:products,id',
            'bundles.*.get_quantity' => 'required_with:bundles|integer|min:1',
            'bundles.*.get_price' => 'nullable|numeric|min:0',
        ]);

        $promotion->update($validated);

        // Sync relationships jika ada
        if (isset($validated['product_ids']) && method_exists($promotion, 'products')) {
            $promotion->products()->sync($validated['product_ids']);
        }

        if (isset($validated['customer_type_ids']) && method_exists($promotion, 'customerTypes')) {
            $promotion->customerTypes()->sync($validated['customer_type_ids']);
        }

        // Sync tiers if any
        if (isset($validated['tiers'])) {
            $promotion->tiers()->delete();
            foreach ($validated['tiers'] as $tier) {
                $promotion->tiers()->create($tier);
            }
        }

        // Sync bundles if any
        if (isset($validated['bundles'])) {
            $promotion->bundles()->delete();
            foreach ($validated['bundles'] as $bundle) {
                $promotion->bundles()->create($bundle);
            }
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
            ? Promotion::active()->with(['products', 'customerTypes', 'tiers', 'bundles'])->get()
            : Promotion::where('is_active', true)->get();

        return response()->json($promotions);
    }
}
