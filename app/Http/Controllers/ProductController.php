<?php

namespace App\Http\Controllers;

use App\Exports\ProductsExport;
use App\Exports\ProductTemplateExport;
use App\Imports\ProductsImport;
use App\Models\Product;
use App\Models\ProductImportHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            'min_wholesale_qty' => 'required|integer|min:1',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'division_id' => 'nullable|exists:divisions,id',
            'rack_id' => 'nullable|exists:racks,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'unit' => 'required|string|max:50',
            'weight' => 'nullable|numeric|min:0',
            'min_stock_alert' => 'nullable|integer|min:0',
            'max_stock_alert' => 'nullable|integer|min:0',
            'reorder' => 'nullable|string',
        ]);

        $product = Product::create($validated);
        $product->syncStock();

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
            'min_wholesale_qty' => 'required|integer|min:1',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'division_id' => 'nullable|exists:divisions,id',
            'rack_id' => 'nullable|exists:racks,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'unit' => 'required|string|max:50',
            'weight' => 'nullable|numeric|min:0',
            'min_stock_alert' => 'nullable|integer|min:0',
            'max_stock_alert' => 'nullable|integer|min:0',
            'reorder' => 'nullable|string',
        ]);

        $product->update($validated);
        $product->syncStock();

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
        Log::info('Starting product import process');

        $request->validate([
            'import_file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            // Get all current products indexed by product_code
            $currentProducts = Product::all()->keyBy('product_code');
            Log::info('Current products count: ' . $currentProducts->count());

            // Import using the custom importer
            $import = new ProductsImport;
            Excel::import($import, $request->file('import_file'));

            // Get the processed data
            $newProductsData = $import->getProcessedData();
            Log::info('Imported data count: ' . $newProductsData->count());

            if ($newProductsData->isEmpty()) {
                Log::warning('No valid product data found in Excel file');
                return redirect()->back()->with('error', 'File Excel tidak mengandung data produk yang valid.');
            }

            // Process the import in transaction
            $result = DB::transaction(function () use ($newProductsData, $currentProducts) {
                $historyRecords = [];
                $updatedCount = 0;
                $createdCount = 0;

                foreach ($newProductsData as $data) {
                    // Ensure $data is array
                    $data = is_array($data) ? $data : $data->toArray();

                    Log::info('Processing product: ' . $data['product_code']);

                    // Remove stock from data to prevent manual intervention
                    unset($data['stock']);

                    // Check if product exists
                    $existingProduct = $currentProducts->get($data['product_code']);

                    // Update or create the product
                    $product = Product::updateOrCreate(
                        ['product_code' => $data['product_code']],
                        $data
                    );

                    // Sync stock from stocks table
                    $product->syncStock();

                    if ($product->wasRecentlyCreated) {
                        $createdCount++;
                        Log::info('Created new product: ' . $data['product_code']);
                    } else {
                        $updatedCount++;
                        // Add to history if it was existing
                        if ($existingProduct) {
                            $historyRecords[] = [
                                'user_id' => auth()->id(),
                                'product_data' => json_encode($existingProduct->toArray()), // Convert to JSON if column is JSON type
                                'imported_at' => now(),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                        Log::info('Updated existing product: ' . $data['product_code']);
                    }
                }

                // Insert history records if any
                if (!empty($historyRecords)) {
                    ProductImportHistory::insert($historyRecords);
                    Log::info('History records inserted: ' . count($historyRecords));
                }

                return ['updated' => $updatedCount, 'created' => $createdCount, 'history_count' => count($historyRecords)];
            });

            $updatedCount = $result['updated'];
            $createdCount = $result['created'];
            $historyCount = $result['history_count'];

            Log::info('Import completed: ' . $updatedCount . ' updated, ' . $createdCount . ' created, ' . $historyCount . ' history records');

            return redirect()->route('master.products.index')
                ->with('success', 'Data produk berhasil diimpor! ' . $updatedCount . ' produk diganti dan ' . $createdCount . ' produk baru ditambahkan. Riwayat perubahan telah dicatat (' . $historyCount . ' record).');
        } catch (\Exception $e) {
            Log::error('Import failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return redirect()->back()
                ->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
        }
    }
    // public function import(Request $request)
    // {
    //     Log::info('Starting product import process');

    //     $request->validate([
    //         'import_file' => 'required|mimes:xlsx,xls',
    //     ]);

    //     try {
    //         // Get all current products indexed by product_code
    //         $currentProducts = Product::all()->keyBy('product_code');
    //         Log::info('Current products count: ' . $currentProducts->count());

    //         // Import new products data
    //         $import = new ProductsImport;
    //         $newProductsData = Excel::toCollection($import, $request->file('import_file'))->first();
    //         Log::info('Imported data count: ' . $newProductsData->count());

    //         if ($newProductsData->isEmpty()) {
    //             Log::warning('No valid product data found in Excel file');
    //             return redirect()->back()->with('error', 'File Excel tidak mengandung data produk yang valid.');
    //         }

    //         // Process the import in transaction
    //         $result = DB::transaction(function () use ($newProductsData, $currentProducts) {
    //             $historyRecords = [];
    //             $updatedCount = 0;
    //             $createdCount = 0;

    //             foreach ($newProductsData as $data) {
    //                 // Ensure $data is array
    //                 $data = is_array($data) ? $data : $data->toArray();

    //                 // Update or create the product (validation already done in ProductsImport)
    //                 $product = Product::updateOrCreate(
    //                     ['product_code' => $data['product_code']],
    //                     $data
    //                 );

    //                 if ($product->wasRecentlyCreated) {
    //                     $createdCount++;
    //                     Log::info('Created new product: ' . $data['product_code']);
    //                 } else {
    //                     $updatedCount++;
    //                     // Add to history if it was existing
    //                     if (isset($currentProducts[$data['product_code']])) {
    //                         $historyRecords[] = [
    //                             'user_id' => auth()->id(),
    //                             'product_data' => $currentProducts[$data['product_code']]->toArray(),
    //                             'imported_at' => now(),
    //                         ];
    //                     }
    //                     Log::info('Updated existing product: ' . $data['product_code']);
    //                 }
    //             }

    //             // Insert history records if any
    //             if (!empty($historyRecords)) {
    //                 ProductImportHistory::insert($historyRecords);
    //                 Log::info('History records inserted: ' . count($historyRecords));
    //             }

    //             return ['updated' => $updatedCount, 'created' => $createdCount];
    //         });

    //         $updatedCount = $result['updated'];
    //         $createdCount = $result['created'];

    //         Log::info('Import completed: ' . $updatedCount . ' updated, ' . $createdCount . ' created');

    //         return redirect()->route('master.products.index')
    //             ->with('success', 'Data produk berhasil diimpor! ' . $updatedCount . ' produk diganti dan ' . $createdCount . ' produk baru ditambahkan. Riwayat perubahan telah dicatat.');
    //     } catch (\Exception $e) {
    //         Log::error('Import failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
    //         return redirect()->back()
    //             ->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
    //     }
    // }

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
