<?php

namespace App\Http\Controllers;

use App\Models\Authorization;
use App\Models\Sale;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

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
        $openShiftToday = Shift::where('store_id', $user->store_id)
            ->whereDate('start_time', Carbon::today())
            ->where('status', 'open')
            ->first();

        if ($openShiftToday) {
            return redirect()->route('shifts.close.form')
                ->with('warning', 'Anda sudah memiliki shift terbuka hari ini. Silakan tutup shift terlebih dahulu.');
        }
        return Inertia::render('Shifts/Open');
    }

    // Menyimpan data shift baru
    public function storeOpenShift(Request $request)
    {
        $request->validate([
            'initial_cash' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();
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
            'start_time' => Carbon::now(),
            'end_time' => null,
            'total_struk' => 0,
            'initial_cash' => $request->initial_cash,
            'final_cash' => 0,
            'total_sales' => 0,
            'total_discount' => 0,
            'total_tax' => 0,
            'total_purchase' => 0,
            'variance' => 0,
            'status' => 'open',
        ]);

        return redirect()->route('dashboard')->with('success', 'Shift berhasil dibuka!');
    }

    public function showCloseShiftForm()
    {
        $user = Auth::user();
        $activeShift = Shift::where('store_id', $user->store_id)
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
        $activeShift = Shift::where('store_id', $user->store_id)->where('status', 'open')->firstOrFail();

        // 3. Lakukan Perhitungan
        $totalSales = Sale::where('user_id', $user->id)
            ->where('created_at', '>=', $activeShift->start_time)
            ->sum('final_amount');

        $expectedCash = $activeShift->initial_cash + $totalSales;
        $variance = $request->final_cash - $expectedCash;

        // 4. Update data shift
        $activeShift->update([
            'end_time' => now(),
            'final_cash' => $request->final_cash,
            'total_sales' => $totalSales,
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
