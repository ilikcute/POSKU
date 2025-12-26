<?php

namespace App\Http\Controllers;

use App\Models\Authorization;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SalesReturn;
use App\Models\Shift;
use App\Models\StockMovement;
use App\Services\StationResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::query()
            ->with(['user:id,name', 'station:id,name,device_identifier'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Shifts/Index', [
            'shifts' => $shifts,
        ]);
    }

    /**
     * GET /shifts/open
     * Rule:
     * - Kalau ada shift hari sebelumnya (siapa pun) yang masih open => paksa ke close.
     * - Kalau device/station ini sudah ada shift open (hari ini) => paksa ke close.
     */
    public function showOpenShiftForm()
    {
        $user = Auth::user();
        $todayStart = now()->startOfDay();

        $hasUnclosedPrevShift = Shift::query()
            
            ->where('status', 'open')
            ->where('start_time', '<', $todayStart)
            ->exists();

        if ($hasUnclosedPrevShift) {
            return redirect()
                ->route('shifts.close.form')
                ->with('warning', 'Ada shift hari sebelumnya yang belum ditutup. Tutup shift tersebut terlebih dahulu.');
        }

        $station = StationResolver::resolve();

        $openShiftOnThisStation = Shift::query()
            
            ->where('station_id', $station->id)
            ->where('status', 'open')
            ->first();

        if ($openShiftOnThisStation) {
            return redirect()
                ->route('shifts.close.form')
                ->with('warning', 'Device ini sudah memiliki shift terbuka. Silakan tutup shift terlebih dahulu.');
        }

        return Inertia::render('Shifts/Open');
    }

    /**
     * POST /shifts/open
     * Membuka shift baru untuk user login pada station/device saat ini.
     * Tidak boleh membuka shift jika masih ada shift hari sebelumnya yang open.
     */
    public function storeOpenShift(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'initial_cash' => ['required', 'numeric', 'min:0'],
            'authorization_password' => ['required', 'string'],
        ]);

        $user = Auth::user();
        $todayStart = now()->startOfDay();

        // Guard: harus bersih dari shift hari sebelumnya
        $hasUnclosedPrevShift = Shift::query()
            
            ->where('status', 'open')
            ->where('start_time', '<', $todayStart)
            ->exists();

        if ($hasUnclosedPrevShift) {
            return back()->with('warning', 'Masih ada shift hari sebelumnya yang belum ditutup.');
        }

        // Verifikasi otorisasi "Buka Shift"
        $auth = Authorization::query()
            ->where('name', 'Buka Shift')
            
            ->first();

        if (! $auth || ! Hash::check($validated['authorization_password'], $auth->password)) {
            throw ValidationException::withMessages([
                'authorization_password' => 'Password verifikasi salah.',
            ]);
        }

        $station = StationResolver::resolve();

        // Guard: 1 shift open per station/device (sesuai implementasi Anda)
        $alreadyOpenOnStation = Shift::query()
            
            ->where('station_id', $station->id)
            ->where('status', 'open')
            ->exists();

        if ($alreadyOpenOnStation) {
            return redirect()
                ->route('shifts.close.form')
                ->with('warning', 'Device ini sudah memiliki shift terbuka. Silakan tutup shift terlebih dahulu.');
        }

        // Generate shift_code sederhana per hari (lebih aman: gunakan increment/uuid jika perlu)
        $todayCount = Shift::query()
            
            ->where('start_time', '>=', $todayStart)
            ->count();

        $shiftCode = 'S-' . ($todayCount + 1);

        Shift::create([
            'user_id' => $user->id,
            'shift_code' => $shiftCode,
            'name' => $validated['name'],
                        'station_id' => $station->id,
            'device_id' => $station->device_identifier,

            'start_time' => now(),
            'end_time' => null,

            'total_struk' => 0,
            'initial_cash' => $validated['initial_cash'],
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
        ]);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Shift berhasil dibuka!');
    }

    /**
     * GET /shifts/close
     * Menampilkan daftar shift hari sebelumnya yang masih open (store-level).
     * User yang login memilih shift mana yang ditutup.
     */
    public function showCloseShiftForm(Request $request)
    {
        $user = Auth::user();
        $todayStart = now()->startOfDay();

        // SHIFT KEMARIN (store-level)
        $unclosedShifts = Shift::query()
            
            ->where('status', 'open')
            ->where('start_time', '<', $todayStart)
            ->with(['user:id,name', 'station:id,name,device_identifier'])
            ->orderBy('start_time')
            ->get();

        // MODE A: jika ada shift kemarin yang open -> force close
        if ($unclosedShifts->isNotEmpty()) {
            $selectedShiftId = $request->integer('shift_id');

            $activeShift = $selectedShiftId
                ? $unclosedShifts->firstWhere('id', $selectedShiftId)
                : $unclosedShifts->first();

            if (! $activeShift) {
                $activeShift = $unclosedShifts->first();
            }

            $summary = $this->buildShiftSummary($activeShift);

            return Inertia::render('Shifts/Close', [
                'mode' => 'force_previous',
                'unclosedShifts' => $unclosedShifts,
                'activeShift' => $activeShift,
                'summary' => $summary,
            ]);
        }

        // MODE B: tidak ada shift kemarin open -> close shift hari ini (station current)
        $station = StationResolver::resolve();

        $activeShiftToday = Shift::query()
            
            ->where('station_id', $station->id)
            ->where('status', 'open')
            ->with(['user:id,name', 'station:id,name,device_identifier'])
            ->first();

        if (! $activeShiftToday) {
            return redirect()
                ->route('dashboard')
                ->with('warning', 'Tidak ada shift yang sedang aktif.');
        }

        $summary = $this->buildShiftSummary($activeShiftToday);

        return Inertia::render('Shifts/Close', [
            'mode' => 'normal_today',
            'unclosedShifts' => [],
            'activeShift' => $activeShiftToday,
            'summary' => $summary,
        ]);
    }


    /**
     * POST /shifts/close
     * Menutup shift hari sebelumnya (store-level gate), tapi perhitungan transaksi memakai pemilik shift (user B).
     */
    public function storeCloseShift(Request $request)
    {
        $validated = $request->validate([
            'shift_id' => ['required', 'integer', 'exists:shifts,id'],
            'final_cash' => ['required', 'numeric', 'min:0'],
            'authorization_password' => ['required', 'string'],
        ]);

        $user = Auth::user();
        $todayStart = now()->startOfDay();

        // Verifikasi otorisasi "Tutup Shift"
        $auth = Authorization::query()
            ->where('name', 'Tutup Shift')
            
            ->first();

        if (! $auth || ! Hash::check($validated['authorization_password'], $auth->password)) {
            throw ValidationException::withMessages([
                'authorization_password' => 'Password verifikasi salah.',
            ]);
        }

        // Shift yang ditutup harus:
        // - satu store
        // - masih open
        // - hari sebelumnya (start_time < today)
        $activeShift = Shift::query()
            ->where('id', $validated['shift_id'])
            
            ->where('status', 'open')
            ->firstOrFail();

        $todayStart = now()->startOfDay();

        $isPreviousDayShift = $activeShift->start_time < $todayStart;

        // Jika shift hari ini, batasi hanya pemilik shift yang boleh close
        if (! $isPreviousDayShift && $activeShift->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'shift_id' => 'Anda tidak berwenang menutup shift user lain untuk hari ini.',
            ]);
        }

        $ownerUserId = $activeShift->user_id;
        $startTime = $activeShift->start_time;

        $totalStruk = Sale::query()
            
            ->where('user_id', $ownerUserId)
            ->where('transaction_date', '>=', $startTime)
            ->count();

        $totalSales = Sale::query()
            
            ->where('user_id', $ownerUserId)
            ->where('transaction_date', '>=', $startTime)
            ->sum('final_amount');

        $cashSales = Sale::query()
            
            ->where('user_id', $ownerUserId)
            ->where('transaction_date', '>=', $startTime)
            ->where('payment_method', 'cash')
            ->sum('final_amount');

        $totalSalesReturn = SalesReturn::query()
            
            ->where('user_id', $ownerUserId)
            ->where('return_date', '>=', $startTime)
            ->sum('final_amount');

        $totalPurchase = Purchase::query()
            
            ->where('user_id', $ownerUserId)
            ->where('transaction_date', '>=', $startTime)
            ->sum('final_amount');

        $cashPurchase = Purchase::query()
            
            ->where('user_id', $ownerUserId)
            ->where('transaction_date', '>=', $startTime)
            ->where('payment_method', 'cash')
            ->sum('final_amount');

        $totalPurchaseReturn = PurchaseReturn::query()
            
            ->where('user_id', $ownerUserId)
            ->where('return_date', '>=', $startTime)
            ->sum('final_amount');

        $totalDiscount = Sale::query()
            
            ->where('user_id', $ownerUserId)
            ->where('transaction_date', '>=', $startTime)
            ->sum('discount') ?? 0;

        $totalTax = Sale::query()
            
            ->where('user_id', $ownerUserId)
            ->where('transaction_date', '>=', $startTime)
            ->sum('tax') ?? 0;

        $totalStockMovements = StockMovement::query()
            ->whereHas('stock', function ($q) use ($user) {
                $q;
            })
            ->where('created_at', '>=', $startTime)
            ->count();

        $cashSalesReturn = SalesReturn::query()
            
            ->where('user_id', $ownerUserId)
            ->where('return_date', '>=', $startTime)
            ->where(function ($q) {
                $q->where('payment_method', 'cash')
                    ->orWhereNull('payment_method');
            })
            ->sum('final_amount');

        $cashPurchaseReturn = PurchaseReturn::query()
            
            ->where('user_id', $ownerUserId)
            ->where('return_date', '>=', $startTime)
            ->where(function ($q) {
                $q->where('payment_method', 'cash')
                    ->orWhereNull('payment_method');
            })
            ->sum('final_amount');

        $expectedCash = $activeShift->initial_cash
            + $cashSales
            - $cashSalesReturn
            - $cashPurchase
            + $cashPurchaseReturn;

        $variance = $validated['final_cash'] - $expectedCash;

        $activeShift->update([
            'end_time' => now(),
            'total_struk' => (string) $totalStruk,
            'final_cash' => $validated['final_cash'],
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

        // Setelah menutup shift kemarin, arahkan ke EOD pada tanggal shift tersebut.
        if ($isPreviousDayShift) {
            $businessDate = $activeShift->start_time?->toDateString() ?? now()->toDateString();
            return redirect()
                ->route('eod.station-close.form', ['date' => $businessDate])
                ->with('success', 'Shift hari sebelumnya berhasil ditutup. Silakan lakukan tutup harian station untuk tanggal tersebut.');
        }

        return redirect()->route('dashboard')
            ->with('success', 'Shift hari ini berhasil ditutup.');
    }

    private function buildShiftSummary(Shift $shift): array
    {
        $ownerUserId = $shift->user_id;
        $startTime = $shift->start_time;

        $totalSales = Sale::query()
            
            ->where('user_id', $ownerUserId)
            ->where('transaction_date', '>=', $startTime)
            ->sum('final_amount');

        $cashSales = Sale::query()
            
            ->where('user_id', $ownerUserId)
            ->where('transaction_date', '>=', $startTime)
            ->where('payment_method', 'cash')
            ->sum('final_amount');

        $totalSalesReturn = SalesReturn::query()
            
            ->where('user_id', $ownerUserId)
            ->where('return_date', '>=', $startTime)
            ->sum('final_amount');

        $totalPurchase = Purchase::query()
            
            ->where('user_id', $ownerUserId)
            ->where('transaction_date', '>=', $startTime)
            ->sum('final_amount');

        $cashPurchase = Purchase::query()
            
            ->where('user_id', $ownerUserId)
            ->where('transaction_date', '>=', $startTime)
            ->where('payment_method', 'cash')
            ->sum('final_amount');

        $totalPurchaseReturn = PurchaseReturn::query()
            
            ->where('user_id', $ownerUserId)
            ->where('return_date', '>=', $startTime)
            ->sum('final_amount');

        $cashSalesReturn = SalesReturn::query()
            
            ->where('user_id', $ownerUserId)
            ->where('return_date', '>=', $startTime)
            ->where(function ($q) {
                $q->where('payment_method', 'cash')
                    ->orWhereNull('payment_method');
            })
            ->sum('final_amount');

        $cashPurchaseReturn = PurchaseReturn::query()
            
            ->where('user_id', $ownerUserId)
            ->where('return_date', '>=', $startTime)
            ->where(function ($q) {
                $q->where('payment_method', 'cash')
                    ->orWhereNull('payment_method');
            })
            ->sum('final_amount');

        $expectedCash = $shift->initial_cash
            + $cashSales
            - $cashSalesReturn
            - $cashPurchase
            + $cashPurchaseReturn;

        return [
            'totalSales' => (float) $totalSales,
            'expectedCash' => (float) $expectedCash,
        ];
    }
}

