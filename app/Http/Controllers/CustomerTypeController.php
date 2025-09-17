<?php

namespace App\Http\Controllers;

use App\Models\CustomerType;
use Illuminate\Http\Request;

class CustomerTypeController extends Controller
{
    public function index()
    {
        $customerTypes = CustomerType::with(['customers'])->paginate(10);

        return response()->json($customerTypes);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:customer_types,code',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $customerType = CustomerType::create($validated);

        return response()->json($customerType, 201);
    }

    public function show(CustomerType $customerType)
    {
        return response()->json($customerType->load(['customers', 'promotions']));
    }

    public function update(Request $request, CustomerType $customerType)
    {
        $validated = $request->validate([
            'name' => 'string|max:100',
            'code' => 'string|max:20|unique:customer_types,code,'.$customerType->id,
            'discount_percentage' => 'numeric|min:0|max:100',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $customerType->update($validated);

        return response()->json($customerType);
    }

    public function destroy(CustomerType $customerType)
    {
        $customerType->delete();

        return response()->json(['message' => 'Customer type deleted successfully']);
    }
}
