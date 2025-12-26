<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Shift;
use App\Models\Store;
use App\Services\StationResolver;
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

        $station = StationResolver::resolve();
        $activeShift = Shift::query()
            ->where('station_id', $station->id)
            ->where('status', 'open')
            ->latest('start_time')
            ->first();

        // Metrik untuk Kartu (sekarang difilter per toko)
        $totalSalesToday = Sale::whereDate('created_at', Carbon::today())->sum('final_amount');
        $transactionCountToday = Sale::whereDate('created_at', Carbon::today())->count();
        $newCustomersThisMonth = \App\Models\Customer::whereMonth('created_at', Carbon::now()->month)->count();

        // Top 5 Produk Terlaris Bulan Ini (per toko)
        $topProductsThisMonth = DB::table('sale_details')
            ->join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->join('products', 'sale_details.product_id', '=', 'products.id')
            ->whereMonth('sales.created_at', Carbon::now()->month)
            ->select('products.name', DB::raw('SUM(sale_details.quantity) as total_quantity'))
            ->groupBy('products.name')
            ->orderByDesc('total_quantity')
            ->take(5)
            ->get();

        $topPurchasedProducts = DB::table('purchase_details')
            ->join('purchases', 'purchase_details.purchase_id', '=', 'purchases.id')
            ->join('products', 'purchase_details.product_id', '=', 'products.id')
            ->whereMonth('purchases.created_at', Carbon::now()->month)
            ->select('products.name', DB::raw('SUM(purchase_details.quantity) as total_quantity'))
            ->groupBy('products.name')
            ->orderByDesc('total_quantity')
            ->take(5)
            ->get();

        $topSuppliers = DB::table('purchases')
            ->join('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
            ->whereMonth('purchases.created_at', Carbon::now()->month)
            ->select('suppliers.name', DB::raw('SUM(purchases.final_amount) as total_amount'))
            ->groupBy('suppliers.name')
            ->orderByDesc('total_amount')
            ->take(5)
            ->get();

        // Data untuk Grafik Penjualan 30 Hari Terakhir (per toko)
        $salesLast30Days = Sale::where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get([
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(final_amount) as total'),
            ])
            ->pluck('total', 'date');

        $chartLabels = [];
        $chartData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $chartLabels[] = Carbon::parse($date)->format('d M');
            $chartData[] = $salesLast30Days->get($date, 0);
        }

        // --- PERBAIKAN QUERY STOK MENIPIS ---
        $lowStockProducts = Product::join('stocks', 'products.id', '=', 'stocks.product_id')
            ->whereColumn('stocks.quantity', '<=', 'products.min_stock_alert')
            ->orderBy('stocks.quantity', 'asc')
            ->select('products.name', 'stocks.quantity', 'products.min_stock_alert')
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'totalSalesToday' => (int) $totalSalesToday,
            'transactionCountToday' => $transactionCountToday,
            'newCustomersThisMonth' => $newCustomersThisMonth,
            'topProducts' => $topProductsThisMonth,
            'salesChart' => [
                'labels' => $chartLabels,
                'data' => $chartData,
            ],
            'topPurchasedProducts' => $topPurchasedProducts,
            'topSuppliers' => $topSuppliers,
            'lowStockProducts' => $lowStockProducts,
            'store' => $store,
            'station' => $station->only(['id', 'name', 'device_identifier']),
            'activeShift' => $activeShift,
        ]);
    }
}
