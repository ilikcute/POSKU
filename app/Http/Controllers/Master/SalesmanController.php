<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Salesman;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SalesmanController extends Controller
{
    public function index(Request $request)
    {
        $salesmen = Salesman::query()
            ->when($request->input('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('salesman_code', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Debug - tambahkan ini sementara untuk cek data
        // dd($salesmen); // Uncomment jika perlu debug

        // Pastikan Anda mengirimkan prop dengan nama 'salesman'
        return Inertia::render('Master/Salesmen/Index', [
            'salesmen' => $salesmen,
            'filters' => $request->only(['search']),
            'can' => [
                'create_salesmen' => auth()->user()->can('create_salesmen'),
                'edit_salesmen' => auth()->user()->can('edit_salesmen'),
                'delete_salesmen' => auth()->user()->can('delete_salesmen'),
                'view_salesmen' => auth()->user()->can('view_salesmen'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'salesman_code' => 'required|string|max:50|unique:salesmen',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:salesmen',
        ]);
        Salesman::create($validated);

        return redirect()->back()->with('success', 'Salesman berhasil ditambahkan.');
    }

    public function update(Request $request, Salesman $salesman)
    {
        $validated = $request->validate([
            'salesman_code' => 'required|string|max:50|unique:salesmen,salesman_code,'.$salesman->id,
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:salesmen,email,'.$salesman->id,
        ]);
        $salesman->update($validated);

        return redirect()->back()->with('success', 'Salesman berhasil diperbarui.');
    }

    public function destroy(Salesman $salesman)
    {
        $salesman->delete();

        return redirect()->back()->with('success', 'Salesman berhasil dihapus.');
    }
}
