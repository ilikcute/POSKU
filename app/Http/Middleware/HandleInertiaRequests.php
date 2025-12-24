<?php

namespace App\Http\Middleware;

use App\Models\Store;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),

            // jika Anda memang butuh update token via Inertia props:
            'csrf_token' => fn () => $request->session()->token(),

            // Lazy auth props (lebih ringan)
            'auth' => fn () => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,

                    // Jika ini berat, jadikan lazy juga (atau batasi)
                    'roles' => $request->user()->getRoleNames()->toArray(),
                    'permissions' => $request->user()->getAllPermissions()->pluck('name')->toArray(),
                ] : null,
            ],

            // Store info (minimal fields)
            'store' => fn () => optional(
                Store::query()
                    ->select(['id', 'name', 'logo_path', 'is_main_store', 'address', 'phone', 'receipt_paper_size'])
                    ->where('is_main_store', true)
                    ->first()
                ?? Store::query()->select(['id', 'name', 'logo_path', 'is_main_store', 'address', 'phone', 'receipt_paper_size'])->first()
            )->only(['id', 'name', 'logo_path', 'is_main_store', 'address', 'phone', 'receipt_paper_size']),

            // Flash (lengkap, termasuk warning)
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'message' => fn () => $request->session()->get('message'),
                'invoice_number' => fn () => $request->session()->get('invoice_number'),
            ],
        ];
    }
}
