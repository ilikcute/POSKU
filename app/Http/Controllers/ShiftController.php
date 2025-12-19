<?php

namespace App\Http\Controllers;

use App\Models\Authorization;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SalesReturn;
use App\Models\Shift;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use App\Services\StationResolver;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::with('user', 'store')->latest()->paginate(10);

        return Inertia::render('Shifts/Index', [
            'shifts' => $shifts,
        ]);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(Shift $shift)
    {
        //
    }

    public function edit(Shift $shift)
    {
        //
    }

    public function update(Request $request, Shift $shift)
    {
        //
    }

    public function destroy(Shift $shift)
    {
        //
    }

    public function showOpenShiftForm()
    {
        $user = Auth::user();
        $station = StationResolver::resolve();

        $openShiftToday = Shift::where('store_id', $user->store_id)
            ->where('station_id', $station->id)
            ->where('status', 'open')
            ->first();

        if ($openShiftToday) {
            return redirect()->route('shifts.close.form')
                ->with('warning', 'Device ini sudah memiliki shift terbuka. Silakan tutup shift terlebih dahulu.');
        }
        return Inertia::render('Shifts/Open');
    }

    // Menyimpan data shift baru
    public function storeOpenShift(Request $request)
    {
        $request->validate([
            'initial_cash' => 'required|numeric|min:0',
            'authorization_password' => 'required|string',
        ]);

        $user = Auth::user();

        // Verifikasi Password
        $auth = Authorization::where('name', 'Buka Shift')->where('store_id', $user->store_id)->first();
        if (! $auth || ! Hash::check($request->authorization_password, $auth->password)) {
            throw ValidationException::withMessages([
                'authorization_password' => 'Password verifikasi salah.',
            ]);
        }

        $station = StationResolver::resolve();
        $todayCount = Shift::where('store_id', $user->store_id)
            ->whereDate('start_time', Carbon::today())
            ->count();
        $count = $todayCount + 1;
        $nextNumber = ($count % 10) + 1;
        $shiftCode  = 'SHIFT-' . $nextNumber;

        Shift::create([
            'user_id' => $user->id,
            'shift_code' => $shiftCode,
            'name' => $request->name,
            'store_id' => $user->store_id,
            'station_id' => $station->id,
            'start_time' => Carbon::now(),
            'end_time' => null,
            'total_struk' => 0,
            'initial_cash' => $request->initial_cash,
            'final_cash' => 0,
            'total_sales' => 0,
            'total_discount' => 0,
            'total_tax' => 0,
            'total_purchase' => 0,
            'total_sales_return' => 0,
            'total_purchase_return' => 0,
            'total_stock_movements' => 0,
            'variance' => 0,
            'status' => 'open',
            'device_id' => $station->device_identifier,
        ]);

        return redirect()->route('dashboard')->with('success', 'Shift berhasil dibuka!');
    }

    public function showCloseShiftForm()
    {
        $user = Auth::user();
        $station = StationResolver::resolve();

        $activeShift = Shift::where('store_id', $user->store_id)
            ->where('station_id', $station->id)
            ->where('status', 'open')
            ->first();

        // Jika tidak ada shift aktif, kembalikan ke dashboard
        if (! $activeShift) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Tidak ada shift yang sedang aktif.']);
        }

        // Hitung total penjualan selama shift ini berlangsung
        $totalSales = Sale::where('user_id', $user->id)
            ->where('created_at', '>=', $activeShift->start_time)
            ->sum('final_amount');

        // Kirim data ke frontend
        return Inertia::render('Shifts/Close', [
            'activeShift' => $activeShift,
            'totalSales' => (float) $totalSales,
        ]);
    }

    public function storeCloseShift(Request $request)
    {
        $request->validate([
            'final_cash' => 'required|numeric|min:0',
            'authorization_password' => 'required|string',
        ]);

        $user = Auth::user();

        // 1. Verifikasi Password
        $auth = Authorization::where('name', 'Tutup Shift')->where('store_id', $user->store_id)->first();
        if (! $auth || ! Hash::check($request->authorization_password, $auth->password)) {
            throw ValidationException::withMessages([
                'authorization_password' => 'Password verifikasi salah.',
            ]);
        }

        // 2. Ambil data shift aktif
        $station = StationResolver::resolve();
        $activeShift = Shift::where('store_id', $user->store_id)
            ->where('station_id', $station->id)
            ->where('status', 'open')->firstOrFail();

        $startTime = $activeShift->start_time;

        // 3. Lakukan Perhitungan untuk semua transaksi selama shift
        // Total struk (jumlah penjualan)
        $totalStruk = Sale::where('user_id', $user->id)
            ->where('created_at', '>=', $startTime)
            ->count();

        // Total sales
        $totalSales = Sale::where('user_id', $user->id)
            ->where('created_at', '>=', $startTime)
            ->sum('final_amount');

        // Total sales return
        $totalSalesReturn = SalesReturn::where('user_id', $user->id)
            ->where('created_at', '>=', $startTime)
            ->sum('final_amount');

        // Total purchase
        $totalPurchase = Purchase::where('user_id', $user->id)
            ->where('created_at', '>=', $startTime)
            ->sum('final_amount');

        // Total purchase return
        $totalPurchaseReturn = PurchaseReturn::where('user_id', $user->id)
            ->where('created_at', '>=', $startTime)
            ->sum('final_amount');

        // Total stock movements (jumlah gerakan stok, misalnya opname, adjustment)
        $totalStockMovements = StockMovement::whereHas('stock', function ($q) use ($user) {
            $q->where('store_id', $user->store_id);
        })->where('created_at', '>=', $startTime)->count();

        // Total discount (dari sales)
        $totalDiscount = Sale::where('user_id', $user->id)
            ->where('created_at', '>=', $startTime)
            ->sum('discount') ?? 0;

        // Total tax (dari sales)
        $totalTax = Sale::where('user_id', $user->id)
            ->where('created_at', '>=', $startTime)
            ->sum('tax') ?? 0;

        // Expected cash sederhana: initial + sales - sales_return + purchase - purchase_return
        $expectedCash = $activeShift->initial_cash + $totalSales - $totalSalesReturn + $totalPurchase - $totalPurchaseReturn;
        $variance = $request->final_cash - $expectedCash;

        // 4. Update data shift dengan semua totalan
        $activeShift->update([
            'end_time' => now(),
            'total_struk' => (string) $totalStruk, // Karena field string
            'final_cash' => $request->final_cash,
            'total_sales' => $totalSales,
            'total_discount' => $totalDiscount,
            'total_tax' => $totalTax,
            'total_purchase' => $totalPurchase,
            'total_sales_return' => $totalSalesReturn,
            'total_purchase_return' => $totalPurchaseReturn,
            'total_stock_movements' => $totalStockMovements,
            'variance' => $variance,
            'status' => 'closed',
        ]);

        // 5. Logout user
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Shift berhasil ditutup. Silakan login kembali untuk memulai shift baru.');
    }
}
