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
        $user = $request->user();
        $store = Store::where('is_main_store', true)->first() ?? Store::first();

        return [
            ...parent::share($request),
            'csrf_token' => $request->session()->token(),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames()->toArray(),
                    'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
                ] : null,
            ],
            'store' => $store ? [
                'id' => $store->id,
                'name' => $store->name,
                'logo_path' => $store->logo_path,
                'is_main_store' => $store->is_main_store,
                // tambahkan field lain yang dibutuhkan
            ] : null,
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'message' => fn () => $request->session()->get('message'),
            ],
        ];
    }

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }
}
