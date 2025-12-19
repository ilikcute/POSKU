<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    protected function currentStoreId(): int
    {
        $storeId = Auth::user()?->store_id;

        if (! $storeId) {
            abort(403, 'Anda belum terhubung dengan toko manapun.');
        }

        return $storeId;
    }

    protected function currentStore()
    {
        $store = Auth::user()?->store;

        if (! $store) {
            abort(403, 'Data toko tidak ditemukan.');
        }

        return $store;
    }
}
