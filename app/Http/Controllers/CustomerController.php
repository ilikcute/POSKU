<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Store;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $store = $this->currentStore();
        $query = Customer::query()->where('store_id', $store->id);

        // Search by name, code, phone
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('customer_code', 'like', '%' . $request->search . '%')
                    ->orWhere('member_code', 'like', '%' . $request->search . '%')
                    ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        $customers = $query->with(['customerType', 'store'])->latest()->paginate(10)->withQueryString();

        // Selalu render ke halaman Members/Index untuk route /master/members
        return Inertia::render('Master/Members/Index', [
            'customers' => $customers,
            'filters' => $request->only(['search']),
            'store' => $store,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_code' => 'nullable|string|max:50|unique:customers,customer_code',
            'member_code' => 'nullable|string|unique:customers,member_code',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'customer_type_id' => 'nullable|exists:customer_types,id',
            'is_active' => 'boolean',
            'store_id' => 'nullable|exists:stores,id',
            'points' => 'nullable|integer|min:0',
            'photo' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
            'joined_at' => 'nullable|date',
        ]);

        $validated['store_id'] = $this->currentStoreId();
        $validated['points'] = $validated['points'] ?? 0;
        $validated['status'] = $validated['status'] ?? 'active';


        // Auto generate customer_code if not provided, and ensure unique
        if (empty($validated['customer_code'])) {
            if (!empty($validated['member_code'])) {
                $baseCode = $validated['member_code'];
            } else {
                $baseCode = 'CUST' . strtoupper(substr(uniqid(), -6));
            }
        } else {
            $baseCode = $validated['customer_code'];
        }

        // Ensure unique customer_code
        $customerCode = $baseCode;
        $i = 1;
        while (\App\Models\Customer::where('customer_code', $customerCode)->exists()) {
            $customerCode = $baseCode . '-' . $i;
            $i++;
        }
        $validated['customer_code'] = $customerCode;

        $customer = Customer::create($validated);

        return redirect()->back()->with('success', 'Data berhasil disimpan.');
    }

    public function show(Customer $customer)
    {
        return response()->json($customer->load('customerType'));
    }

    public function update(Request $request, Customer $customer)
    {
        if ($customer->store_id !== $this->currentStoreId()) {
            abort(403, 'Unauthorized');
        }
        $validated = $request->validate([
            'customer_code' => 'nullable|string|max:50|unique:customers,customer_code,' . $customer->id,
            'member_code' => 'nullable|string|unique:customers,member_code,' . $customer->id,
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'customer_type_id' => 'nullable|exists:customer_types,id',
            'is_active' => 'boolean',
            'store_id' => 'nullable|exists:stores,id',
            'points' => 'nullable|integer|min:0',
            'photo' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
            'joined_at' => 'nullable|date',
        ]);
        $customer->update($validated);
        return redirect()->back()->with('success', 'Data berhasil diupdate.');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->store_id !== $this->currentStoreId()) {
            abort(403, 'Unauthorized');
        }
        $customer->delete();
        return redirect()->back()->with('success', 'Data berhasil dihapus.');
    }

    // Tambahkan fitur generateCard jika perlu
    public function generateCard(Customer $customer)
    {
        $storeId = $this->currentStoreId();
        if ($customer->store_id !== $storeId) {
            abort(403, 'Unauthorized');
        }
        $store = Store::find($storeId);
        return response()->json([
            'member' => $customer,
            'store' => $store,
        ]);
    }
}
