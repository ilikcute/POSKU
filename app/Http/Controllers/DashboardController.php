<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    // app/Http/Controllers/DashboardController.php

    public function index()
    {
        $store = Store::where('is_main_store', true)->first()
            ?? Store::first();

        // Ambil store_id dari user yang sedang login
        $storeId = Auth::user()->store_id;

        // Metrik untuk Kartu (sekarang difilter per toko)
        $totalSalesToday = Sale::where('store_id', $storeId)
            ->whereDate('created_at', Carbon::today())->sum('final_amount');
        $transactionCountToday = Sale::where('store_id', $storeId)
            ->whereDate('created_at', Carbon::today())->count();
        $newCustomersThisMonth = \App\Models\Customer::whereMonth('created_at', Carbon::now()->month)->count();

        // Produk Terlaris Bulan Ini (per toko)
        $topProductThisMonth = DB::table('sale_details')
            ->join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->join('products', 'sale_details.product_id', '=', 'products.id')
            ->where('sales.store_id', $storeId)
            ->whereMonth('sales.created_at', Carbon::now()->month)
            ->select('products.name', DB::raw('SUM(sale_details.quantity) as total_quantity'))
            ->groupBy('products.name')
            ->orderByDesc('total_quantity')
            ->first();

        // Data untuk Grafik Penjualan 7 Hari Terakhir (per toko)
        $salesLast7Days = Sale::where('store_id', $storeId)
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get([
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(final_amount) as total'),
            ])
            ->pluck('total', 'date');

        $chartLabels = [];
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $chartLabels[] = Carbon::parse($date)->format('d M');
            $chartData[] = $salesLast7Days->get($date, 0);
        }

        // --- PERBAIKAN QUERY STOK MENIPIS ---
        $lowStockProducts = Product::join('stocks', 'products.id', '=', 'stocks.product_id')
            ->where('stocks.store_id', $storeId)
            ->whereColumn('stocks.quantity', '<=', 'products.min_stock_alert')
            ->orderBy('stocks.quantity', 'asc')
            ->select('products.name', 'stocks.quantity', 'products.min_stock_alert')
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'totalSalesToday' => (int) $totalSalesToday,
            'transactionCountToday' => $transactionCountToday,
            'newCustomersThisMonth' => $newCustomersThisMonth,
            'topProduct' => $topProductThisMonth ? $topProductThisMonth->name : 'N/A',
            'salesChart' => [
                'labels' => $chartLabels,
                'data' => $chartData,
            ],
            'lowStockProducts' => $lowStockProducts,
            'store' => $store,
        ]);

        $storeId = Auth::user()->store_id;

        // Metrik untuk Kartu (sekarang difilter per toko)
        $totalSalesToday = Sale::where('store_id', $storeId)
            ->whereDate('created_at', Carbon::today())->sum('final_amount');
        $transactionCountToday = Sale::where('store_id', $storeId)
            ->whereDate('created_at', Carbon::today())->count();
        $newCustomersThisMonth = \App\Models\Customer::whereMonth('created_at', Carbon::now()->month)->count();

        // Produk Terlaris Bulan Ini (per toko)
        $topProductThisMonth = DB::table('sale_details')
            ->join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->join('products', 'sale_details.product_id', '=', 'products.id')
            ->where('sales.store_id', $storeId)
            ->whereMonth('sales.created_at', Carbon::now()->month)
            ->select('products.name', DB::raw('SUM(sale_details.quantity) as total_quantity'))
            ->groupBy('products.name')
            ->orderByDesc('total_quantity')
            ->first();

        // Data untuk Grafik Penjualan 7 Hari Terakhir (per toko)
        $salesLast7Days = Sale::where('store_id', $storeId)
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get([
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(final_amount) as total'),
            ])
            ->pluck('total', 'date');

        $chartLabels = [];
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $chartLabels[] = Carbon::parse($date)->format('d M');
            $chartData[] = $salesLast7Days->get($date, 0);
        }

        // --- PERBAIKAN QUERY STOK MENIPIS ---
        $lowStockProducts = Product::join('stocks', 'products.id', '=', 'stocks.product_id')
            ->where('stocks.store_id', $storeId)
            ->whereColumn('stocks.quantity', '<=', 'products.min_stock_alert')
            ->orderBy('stocks.quantity', 'asc')
            ->select('products.name', 'stocks.quantity', 'products.min_stock_alert')
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'totalSalesToday' => (int) $totalSalesToday,
            'transactionCountToday' => $transactionCountToday,
            'newCustomersThisMonth' => $newCustomersThisMonth,
            'topProduct' => $topProductThisMonth ? $topProductThisMonth->name : 'N/A',
            'salesChart' => [
                'labels' => $chartLabels,
                'data' => $chartData,
            ],
            'lowStockProducts' => $lowStockProducts,
        ]);
    }
}
