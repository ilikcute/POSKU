<?php

namespace Database\Seeders;

use App\Models\Authorization;
use App\Models\Store;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AuthorizationSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil toko utama
        $mainStore = Store::where('is_main_store', true)->first();

        if ($mainStore) {
            Authorization::create([
                'name' => 'Tutup Shift',
                'password' => Hash::make('123456'), // Password default adalah 123456
                'store_id' => $mainStore->id,
            ]);
        }
    }
}
