<?php

namespace App\Http\Controllers;

use App\Models\Authorization;
use App\Models\DailyClosing;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SalesReturn;
use App\Models\Shift;
use App\Models\Station;
use App\Models\StationDailyClosing;
use App\Services\StationResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class EodController extends Controller
{
    public function index(Request $request)
    {
        $storeId = $this->currentStoreId();

        $closings = DailyClosing::query()
            ->where('store_id', $storeId)
            ->orderByDesc('business_date')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('EOD/Index', [
            'closings' => $closings,
        ]);
    }

    /**
     * GET /eod/station-close?date=YYYY-MM-DD
     * Station melakukan tutup harian untuk device/station ini.
     */
    public function showStationCloseForm(Request $request)
    {
        $user = Auth::user();
        $storeId = $user->store_id;

        $businessDate = $request->date('date')?->toDateString() ?? now()->toDateString();
        $dayStart = now()->parse($businessDate)->startOfDay();
        $dayEnd = now()->parse($businessDate)->endOfDay();

        $station = StationResolver::resolve();

        // jika sudah ada store-level EOD, station tidak boleh close lagi
        $storeClosed = DailyClosing::query()
            ->where('store_id', $storeId)
            ->where('business_date', $businessDate)
            ->exists();

        if ($storeClosed) {
            return redirect()
                ->route('eod.index')
                ->with('warning', 'EOD tanggal ini sudah difinalize. Tidak bisa tutup harian station lagi.');
        }

        // Pastikan tidak ada shift OPEN di station pada business date
        $hasOpenShift = Shift::query()
            ->where('store_id', $storeId)
            ->where('station_id', $station->id)
            ->where('status', 'open')
            ->whereBetween('start_time', [$dayStart, $dayEnd])
            ->exists();

        if ($hasOpenShift) {
            return redirect()
                ->route('shifts.close.form')
                ->with('warning', 'Masih ada shift yang open pada station ini. Tutup shift terlebih dahulu sebelum EOD.');
        }

        $existing = StationDailyClosing::query()
            ->where('store_id', $storeId)
            ->where('station_id', $station->id)
            ->where('business_date', $businessDate)
            ->first();

        $summary = $this->buildStationDaySummary($storeId, $station->id, $dayStart, $dayEnd);

        return Inertia::render('EOD/StationClose', [
            'businessDate' => $businessDate,
            'station' => $station->only(['id', 'name', 'device_identifier']),
            'summary' => $summary,
            'alreadyClosed' => $existing ? true : false,
            'existing' => $existing,
        ]);
    }

    /**
     * POST /eod/station-close
     */
    public function storeStationClose(Request $request)
    {
        $validated = $request->validate([
            'business_date' => ['required', 'date'],
            'cash_counted' => ['required', 'numeric', 'min:0'],
            'authorization_password' => ['required', 'string'],
        ]);

        $user = Auth::user();
        $storeId = $user->store_id;
        $businessDate = $validated['business_date'];
        $dayStart = now()->parse($businessDate)->startOfDay();
        $dayEnd = now()->parse($businessDate)->endOfDay();

        $station = StationResolver::resolve();

        // verifikasi otorisasi
        $auth = Authorization::query()
            ->where('name', 'Tutup Harian')
            ->where('store_id', $storeId)
            ->first();

        if (! $auth || ! Hash::check($validated['authorization_password'], $auth->password)) {
            throw ValidationException::withMessages([
                'authorization_password' => 'Password verifikasi salah.',
            ]);
        }

        // tidak boleh jika store sudah finalize EOD
        $storeClosed = DailyClosing::query()
            ->where('store_id', $storeId)
            ->where('business_date', $businessDate)
            ->exists();

        if ($storeClosed) {
            throw ValidationException::withMessages([
                'business_date' => 'EOD tanggal ini sudah difinalize.',
            ]);
        }

        // tidak boleh ada shift open di station tanggal itu
        $hasOpenShift = Shift::query()
            ->where('store_id', $storeId)
            ->where('station_id', $station->id)
            ->where('status', 'open')
            ->whereBetween('start_time', [$dayStart, $dayEnd])
            ->exists();

        if ($hasOpenShift) {
            throw ValidationException::withMessages([
                'business_date' => 'Masih ada shift open pada station ini. Tutup shift dulu.',
            ]);
        }

        // unique per (store, station, date)
        $existing = StationDailyClosing::query()
            ->where('store_id', $storeId)
            ->where('station_id', $station->id)
            ->where('business_date', $businessDate)
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'business_date' => 'Station sudah ditutup harian untuk tanggal ini.',
            ]);
        }

        $summary = $this->buildStationDaySummary($storeId, $station->id, $dayStart, $dayEnd);

        $variance = (float) $validated['cash_counted'] - (float) $summary['expectedCash'];

        StationDailyClosing::create([
            'store_id' => $storeId,
            'station_id' => $station->id,
            'business_date' => $businessDate,
            'closed_by' => $user->id,
            'closed_at' => now(),
            'cash_counted' => $validated['cash_counted'],
            'expected_cash' => $summary['expectedCash'],
            'variance' => $variance,

            'total_sales' => $summary['totalSales'],
            'total_sales_return' => $summary['totalSalesReturn'],
            'total_purchase' => $summary['totalPurchase'],
            'total_purchase_return' => $summary['totalPurchaseReturn'],
            'total_discount' => $summary['totalDiscount'],
            'total_tax' => $summary['totalTax'],
            'sales_count' => $summary['salesCount'],
            'shift_count' => $summary['shiftCount'],

            'meta' => [
                'payments' => $summary['payments'],
                'cash_sales' => $summary['cashSales'],
            ],
        ]);

        return redirect()
            ->route('eod.finalize.form', ['date' => $businessDate])
            ->with('success', 'Tutup harian station berhasil. Silakan finalize EOD toko jika semua station sudah selesai.');
    }

    /**
     * GET /eod/finalize?date=YYYY-MM-DD
     * Manager finalize 1 toko, mensyaratkan semua station yang punya aktivitas hari itu sudah station-close.
     */
    public function showFinalizeForm(Request $request)
    {
        $user = Auth::user();
        $storeId = $user->store_id;

        $businessDate = $request->date('date')?->toDateString() ?? now()->toDateString();
        $dayStart = now()->parse($businessDate)->startOfDay();
        $dayEnd = now()->parse($businessDate)->endOfDay();

        $alreadyFinal = DailyClosing::query()
            ->where('store_id', $storeId)
            ->where('business_date', $businessDate)
            ->first();

        // station yang dianggap "harus ikut EOD" = yang punya shift atau transaksi hari itu
        $stationsInvolved = $this->stationsInvolved($storeId, $dayStart, $dayEnd);

        $stationClosings = StationDailyClosing::query()
            ->where('store_id', $storeId)
            ->where('business_date', $businessDate)
            ->with(['station:id,name,device_identifier', 'closedByUser:id,name'])
            ->get()
            ->keyBy('station_id');

        $stations = Station::query()
            ->where('store_id', $storeId)
            ->whereIn('id', $stationsInvolved)
            ->orderBy('name')
            ->get()
            ->map(function ($st) use ($stationClosings) {
                $close = $stationClosings->get($st->id);
                return [
                    'id' => $st->id,
                    'name' => $st->name,
                    'device_identifier' => $st->device_identifier,
                    'is_closed' => $close ? true : false,
                    'closing' => $close ? [
                        'closed_at' => $close->closed_at,
                        'closed_by' => $close->closedByUser?->name,
                        'cash_counted' => (float) $close->cash_counted,
                        'expected_cash' => (float) $close->expected_cash,
                        'variance' => (float) $close->variance,
                        'total_sales' => (float) $close->total_sales,
                    ] : null,
                ];
            });

        $canFinalize = $stations->count() > 0 && $stations->every(fn($s) => $s['is_closed'] === true);

        // summary store (jika semua closed, ambil dari station closings sum)
        $storeSummary = $this->buildStoreSummaryFromStationClosings($storeId, $businessDate);

        return Inertia::render('EOD/Finalize', [
            'businessDate' => $businessDate,
            'alreadyFinalized' => $alreadyFinal ? true : false,
            'final' => $alreadyFinal,
            'stations' => $stations,
            'canFinalize' => $canFinalize,
            'storeSummary' => $storeSummary,
        ]);
    }

    /**
     * POST /eod/finalize
     */
    public function storeFinalize(Request $request)
    {
        $validated = $request->validate([
            'business_date' => ['required', 'date'],
            'authorization_password' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $user = Auth::user();
        $storeId = $user->store_id;
        $businessDate = $validated['business_date'];
        $dayStart = now()->parse($businessDate)->startOfDay();
        $dayEnd = now()->parse($businessDate)->endOfDay();

        // verifikasi otorisasi
        $auth = Authorization::query()
            ->where('name', 'Tutup Harian')
            ->where('store_id', $storeId)
            ->first();

        if (! $auth || ! Hash::check($validated['authorization_password'], $auth->password)) {
            throw ValidationException::withMessages([
                'authorization_password' => 'Password verifikasi salah.',
            ]);
        }

        // prevent double finalize
        $exists = DailyClosing::query()
            ->where('store_id', $storeId)
            ->where('business_date', $businessDate)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'business_date' => 'EOD tanggal ini sudah difinalize.',
            ]);
        }

        // pastikan tidak ada shift open pada store di business date
        $hasOpenShift = Shift::query()
            ->where('store_id', $storeId)
            ->where('status', 'open')
            ->whereBetween('start_time', [$dayStart, $dayEnd])
            ->exists();

        if ($hasOpenShift) {
            throw ValidationException::withMessages([
                'business_date' => 'Masih ada shift open di tanggal ini. Tutup semua shift dulu sebelum finalize EOD.',
            ]);
        }

        // station yang wajib ikut
        $stationsInvolved = $this->stationsInvolved($storeId, $dayStart, $dayEnd);

        $stationClosings = StationDailyClosing::query()
            ->where('store_id', $storeId)
            ->where('business_date', $businessDate)
            ->whereIn('station_id', $stationsInvolved)
            ->get();

        if (count($stationsInvolved) > 0 && $stationClosings->count() !== count($stationsInvolved)) {
            throw ValidationException::withMessages([
                'business_date' => 'Belum semua station melakukan tutup harian.',
            ]);
        }

        $storeSummary = $this->buildStoreSummaryFromStationClosings($storeId, $businessDate);

        $final = DailyClosing::create([
            'store_id' => $storeId,
            'business_date' => $businessDate,
            'finalized_by' => $user->id,
            'finalized_at' => now(),

            'total_sales' => $storeSummary['totalSales'],
            'total_sales_return' => $storeSummary['totalSalesReturn'],
            'total_purchase' => $storeSummary['totalPurchase'],
            'total_purchase_return' => $storeSummary['totalPurchaseReturn'],
            'total_discount' => $storeSummary['totalDiscount'],
            'total_tax' => $storeSummary['totalTax'],

            'cash_counted_total' => $storeSummary['cashCountedTotal'],
            'expected_cash_total' => $storeSummary['expectedCashTotal'],
            'variance_total' => $storeSummary['varianceTotal'],

            'sales_count' => $storeSummary['salesCount'],
            'station_count' => $storeSummary['stationCount'],
            'shift_count' => $storeSummary['shiftCount'],

            'meta' => [
                'stations' => $storeSummary['stations'],
            ],
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('eod.index')
            ->with('success', 'EOD toko berhasil difinalize.');
    }

    /**
     * Hitung station summary pada 1 business date.
     * expectedCash dihitung dengan logika cash drawer (default):
     * expectedCash = sum(initial_cash shifts) + cash_sales - sales_return - cash_purchase + purchase_return
     *
     * Catatan: SalesReturn/PurchaseReturn Anda tidak punya payment_method, jadi di sini dianggap cash impact.
     * Kalau nanti Anda tambah payment_method di return, formula bisa dibuat lebih presisi.
     */
    private function buildStationDaySummary(int $storeId, int $stationId, $dayStart, $dayEnd): array
    {
        $salesQ = Sale::query()
            ->where('store_id', $storeId)
            ->where('station_id', $stationId)
            ->whereBetween('transaction_date', [$dayStart, $dayEnd]);

        $totalSales = (float) $salesQ->sum('final_amount');
        $totalDiscount = (float) $salesQ->sum('discount');
        $totalTax = (float) $salesQ->sum('tax');
        $salesCount = (int) $salesQ->count();

        $payments = $salesQ->clone()
            ->selectRaw('payment_method, SUM(final_amount) as total')
            ->groupBy('payment_method')
            ->get()
            ->map(fn($r) => ['method' => $r->payment_method, 'total' => (float) $r->total])
            ->values();

        $cashSales = (float) $salesQ->clone()
            ->where('payment_method', 'cash')
            ->sum('final_amount');

        $totalSalesReturn = (float) SalesReturn::query()
            ->where('store_id', $storeId)
            ->where('station_id', $stationId)
            ->whereBetween('return_date', [$dayStart, $dayEnd])
            ->sum('final_amount');

        $purchaseQ = Purchase::query()
            ->where('store_id', $storeId)
            ->where('station_id', $stationId)
            ->whereBetween('transaction_date', [$dayStart, $dayEnd]);

        $totalPurchase = (float) $purchaseQ->sum('final_amount');

        $cashPurchase = (float) $purchaseQ->clone()
            ->where('payment_method', 'cash')
            ->sum('final_amount');

        $totalPurchaseReturn = (float) PurchaseReturn::query()
            ->where('store_id', $storeId)
            ->where('station_id', $stationId)
            ->whereBetween('return_date', [$dayStart, $dayEnd])
            ->sum('final_amount');

        $shiftCount = (int) Shift::query()
            ->where('store_id', $storeId)
            ->where('station_id', $stationId)
            ->whereBetween('start_time', [$dayStart, $dayEnd])
            ->where('status', 'closed') // EOD mensyaratkan shift sudah ditutup
            ->count();

        $initialCashSum = (float) Shift::query()
            ->where('store_id', $storeId)
            ->where('station_id', $stationId)
            ->whereBetween('start_time', [$dayStart, $dayEnd])
            ->sum('initial_cash');

        $expectedCash = $initialCashSum
            + $cashSales
            - $totalSalesReturn
            - $cashPurchase
            + $totalPurchaseReturn;

        return [
            'totalSales' => $totalSales,
            'totalSalesReturn' => $totalSalesReturn,
            'totalPurchase' => $totalPurchase,
            'totalPurchaseReturn' => $totalPurchaseReturn,
            'totalDiscount' => $totalDiscount,
            'totalTax' => $totalTax,
            'salesCount' => $salesCount,
            'shiftCount' => $shiftCount,
            'cashSales' => $cashSales,
            'payments' => $payments,
            'initialCashSum' => $initialCashSum,
            'expectedCash' => (float) $expectedCash,
        ];
    }

    private function stationsInvolved(int $storeId, $dayStart, $dayEnd): array
    {
        $shiftStations = Shift::query()
            ->where('store_id', $storeId)
            ->whereBetween('start_time', [$dayStart, $dayEnd])
            ->pluck('station_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $saleStations = Sale::query()
            ->where('store_id', $storeId)
            ->whereBetween('transaction_date', [$dayStart, $dayEnd])
            ->pluck('station_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $purchaseStations = Purchase::query()
            ->where('store_id', $storeId)
            ->whereBetween('transaction_date', [$dayStart, $dayEnd])
            ->pluck('station_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $returnStations = SalesReturn::query()
            ->where('store_id', $storeId)
            ->whereBetween('return_date', [$dayStart, $dayEnd])
            ->pluck('station_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $purchaseReturnStations = PurchaseReturn::query()
            ->where('store_id', $storeId)
            ->whereBetween('return_date', [$dayStart, $dayEnd])
            ->pluck('station_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        return collect([
            ...$shiftStations,
            ...$saleStations,
            ...$purchaseStations,
            ...$returnStations,
            ...$purchaseReturnStations,
        ])->unique()->values()->all();
    }

    private function buildStoreSummaryFromStationClosings(int $storeId, string $businessDate): array
    {
        $closings = StationDailyClosing::query()
            ->where('store_id', $storeId)
            ->where('business_date', $businessDate)
            ->with('station:id,name,device_identifier')
            ->get();

        $stations = $closings->map(function ($c) {
            return [
                'station_id' => $c->station_id,
                'station_name' => $c->station?->name,
                'cash_counted' => (float) $c->cash_counted,
                'expected_cash' => (float) $c->expected_cash,
                'variance' => (float) $c->variance,
                'total_sales' => (float) $c->total_sales,
                'total_sales_return' => (float) $c->total_sales_return,
                'total_purchase' => (float) $c->total_purchase,
                'total_purchase_return' => (float) $c->total_purchase_return,
                'sales_count' => (int) $c->sales_count,
                'shift_count' => (int) $c->shift_count,
            ];
        })->values();

        return [
            'totalSales' => (float) $closings->sum('total_sales'),
            'totalSalesReturn' => (float) $closings->sum('total_sales_return'),
            'totalPurchase' => (float) $closings->sum('total_purchase'),
            'totalPurchaseReturn' => (float) $closings->sum('total_purchase_return'),
            'totalDiscount' => (float) $closings->sum('total_discount'),
            'totalTax' => (float) $closings->sum('total_tax'),

            'cashCountedTotal' => (float) $closings->sum('cash_counted'),
            'expectedCashTotal' => (float) $closings->sum('expected_cash'),
            'varianceTotal' => (float) $closings->sum('variance'),

            'salesCount' => (int) $closings->sum('sales_count'),
            'stationCount' => (int) $closings->count(),
            'shiftCount' => (int) $closings->sum('shift_count'),

            'stations' => $stations,
        ];
    }
}
