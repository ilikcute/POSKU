<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Shift;
use Illuminate\Http\Request;
use App\Services\StationResolver;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveShift
{
    // App\Http\Middleware\CheckActiveShift.php

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) return $next($request);

        // route yang boleh lewat apa pun kondisinya
        if ($request->routeIs('logout', 'profile.*', 'store.profile.*')) {
            return $next($request);
        }

        $todayStart = now()->startOfDay();

        // GATE 1: ada shift kemarin/before today yang masih open di store?
        $hasUnclosedPrevShift = Shift::query()
            ->where('status', 'open')
            ->where('start_time', '<', $todayStart)
            ->exists();

        if ($hasUnclosedPrevShift) {
            // hanya izinkan close shift pages agar tidak loop
            if (! $request->routeIs('shifts.close.*')) {
                return redirect()
                    ->route('shifts.close.form')
                    ->with('warning', 'Ada shift hari sebelumnya yang belum ditutup. Harus ditutup terlebih dahulu.');
            }
            return $next($request);
        }

        // Kalau store bersih, shift open/close boleh diakses normal
        if ($request->routeIs('shifts.open.*', 'shifts.close.*')) {
            return $next($request);
        }

        if ($request->routeIs('logout', 'profile.*', 'store.profile.*', 'eod.*')) {
            return $next($request);
        }

        // GATE 2: shift aktif hari ini tetap wajib per user (sesuai requirement awal)
        $station = StationResolver::resolve();
        $hasActiveShiftTodayForUser = Shift::query()
            ->where('station_id', $station->id)
            ->where('status', 'open')
            ->where('start_time', '>=', $todayStart)
            ->exists();

        if (! $hasActiveShiftTodayForUser) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Tidak ada shift aktif',
                    'redirect' => route('shifts.open.form'),
                ], 403);
            }

            return redirect()
                ->route('shifts.open.form')
                ->with('warning', 'Anda perlu membuka shift terlebih dahulu sebelum mengakses halaman ini.');
        }

        return $next($request);
    }
}
