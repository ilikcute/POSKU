<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        // Buat toko utama (jika belum ada)
        Store::updateOrCreate(
            ['is_main_store' => true],
            [
                'name' => 'Toko Utama POS',
                'address' => 'Jl. Jend. Sudirman No. 1, Jakarta Pusat',
                'phone' => '021-555-1234',
            ]
        );

        // Buat 2 toko cabang
        Store::factory(2)->create();
    }
}
