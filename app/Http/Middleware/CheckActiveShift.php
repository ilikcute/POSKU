<?php

namespace App\Http\Middleware;

use App\Models\Shift;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveShift
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Jika user tidak login, middleware auth akan handle
        if (! $user) {
            return $next($request);
        }

        // Rute yang diizinkan diakses meskipun belum ada shift aktif
        $allowedRoutes = [
            'shifts.open.form',
            'shifts.open.store',
            'shifts.close.form',
            'shifts.close.store',
            'logout',
            'profile.edit',
            'profile.update',
            'profile.destroy',
        ];

        // Cek apakah route saat ini diizinkan
        $currentRoute = $request->route()->getName();

        // Jika route diizinkan, lanjutkan tanpa pengecekan shift
        if (in_array($currentRoute, $allowedRoutes)) {
            return $next($request);
        }

        // Cek apakah ada shift yang sedang 'open' di toko user ini
        $activeShift = Shift::where('store_id', $user->store_id)
            ->where('status', 'open')
            ->where('user_id', auth()->id())

            ->exists();

        // Jika tidak ada shift aktif, redirect ke halaman buka shift
        if (! $activeShift) {
            // Jika request mengharapkan JSON (AJAX), kembalikan error JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Tidak ada shift aktif',
                    'redirect' => route('shifts.open.form'),
                ], 403);
            }
            // Redirect ke halaman buka shift dengan pesan
            return redirect()->route('shifts.open.form')
                ->with('warning', 'Anda perlu membuka shift terlebih dahulu sebelum mengakses halaman ini.');
        }

        return $next($request);
    }
}
