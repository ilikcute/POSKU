<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    protected function currentStoreId(): int
    {
        $store = Store::where('is_main_store', true)->first();

        if (! $store) {
            abort(403, 'Toko utama belum terdaftar.');
        }

        return $store->id;
    }

    protected function currentStore()
    {
        $store = Store::where('is_main_store', true)->first();

        if (! $store) {
            abort(403, 'Toko utama belum terdaftar.');
        }

        return $store;
    }
}
