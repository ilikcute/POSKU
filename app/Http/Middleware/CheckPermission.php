<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        // Check if user has any of the required permissions
        if (! auth()->user()->hasAnyPermission($permissions)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            abort(403, 'Anda tidak memiliki akses untuk halaman ini.');
        }

        return $next($request);
    }
}
