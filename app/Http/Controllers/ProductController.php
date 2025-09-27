<?php

namespace App\Http\Controllers;

use App\Exports\ProductsExport;
use App\Exports\ProductTemplateExport;
use App\Imports\ProductsImport;
use App\Models\Product;
use App\Models\ProductImportHistory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // Ambil semua produk, tapi siapkan untuk difilter
        $products = Product::query()
            // Gunakan 'when' untuk menjalankan query pencarian HANYA JIKA ada input 'search'
            ->when($request->input('search'), function ($query, $search) {
                // Cari di kolom 'name' ATAU 'product_code'
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%");
            })
            // Urutkan dari yang terbaru, dan paginasi hasilnya
            ->latest()
            ->paginate(10)
            // Penting: agar link pagination tetap membawa query pencarian
            ->withQueryString();

        // Ambil data relasi untuk dropdown
        $categories = \App\Models\Category::select('id', 'name')->orderBy('name')->get();
        $divisions = \App\Models\Division::select('id', 'name')->orderBy('name')->get();
        $suppliers = \App\Models\Supplier::select('id', 'name')->orderBy('name')->get();
        $racks = \App\Models\Rack::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Master/Products/Index', [
            'products' => $products,
            'filters' => $request->only(['search']),
            'categories' => $categories,
            'divisions' => $divisions,
            'suppliers' => $suppliers,
            'racks' => $racks,
        ]);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_code' => 'required|string|max:50|unique:products',
            'barcode' => 'nullable|string|max:100|unique:products',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'member_price' => 'nullable|numeric|min:0',
            'vip_price' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'min_wholesale_qty' => 'nullable|integer|min:1',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'division_id' => 'nullable|exists:divisions,id',
            'rack_id' => 'nullable|exists:racks,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'unit' => 'required|string|max:50',
            'weight' => 'nullable|numeric|min:0',
            'min_stock_alert' => 'nullable|integer|min:0',
        ]);

        Product::create($validated);

        return redirect()->back()->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Product $product)
    {
        //
    }

    public function edit(Product $product)
    {
        //
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'product_code' => 'required|string|max:50|unique:products,product_code,' . $product->id,
            'barcode' => 'nullable|string|max:100|unique:products,barcode,' . $product->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'member_price' => 'nullable|numeric|min:0',
            'vip_price' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'min_wholesale_qty' => 'nullable|integer|min:1',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'division_id' => 'nullable|exists:divisions,id',
            'rack_id' => 'nullable|exists:racks,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'unit' => 'required|string|max:50',
            'weight' => 'nullable|numeric|min:0',
            'min_stock_alert' => 'nullable|integer|min:0',
        ]);

        $product->update($validated);

        return redirect()->back()->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->back()->with('success', 'Produk berhasil dihapus.');
    }

    public function export()
    {
        return Excel::download(new ProductsExport, 'products.xlsx');
    }

    public function showImportForm()
    {
        return Inertia::render('Master/Products/Import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            // Get all current products for history
            $currentProducts = Product::all();

            // Save to history
            $historyRecords = [];
            foreach ($currentProducts as $product) {
                $historyRecords[] = [
                    'user_id' => auth()->id(),
                    'product_data' => $product->toArray(),
                    'imported_at' => now(),
                ];
            }
            ProductImportHistory::insert($historyRecords);

            // Delete all existing products
            Product::query()->truncate();

            // Import new products
            Excel::import(new ProductsImport, $request->file('import_file'));

            return redirect()->route('master.products.index')
                ->with('success', 'Data produk berhasil diimpor! Data lama telah dicatat dalam history.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new ProductTemplateExport, 'template_import_products.xlsx');
    }

    public function checkPrice(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'customer_id' => 'nullable|exists:customers,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);
        $customer = $request->customer_id ? \App\Models\Customer::find($request->customer_id) : null;

        $pricing = $product->getPriceWithPromotion($customer, $request->quantity);

        return response()->json([
            'product' => $product,
            'customer' => $customer,
            'customer_type' => $customer?->customerType,
            'quantity' => $request->quantity,
            'pricing' => $pricing,
        ]);
    }
}
